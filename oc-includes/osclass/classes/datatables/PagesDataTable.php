<?php
if(!defined('ABS_PATH')) exit('ABS_PATH is not loaded. Direct access is not allowed.');

/*
 * Copyright 2014 Osclass
 * Copyright 2026 Osclass by OsclassPoint.com
 *
 * Osclass maintained & developed by OsclassPoint.com
 * You may not use this file except in compliance with the License.
 * You may download copy of Osclass at
 *
 *     https://osclass-classifieds.com/download
 *
 * Do not edit or add to this file if you wish to upgrade Osclass to newer
 * versions in the future. Software is distributed on an "AS IS" basis, without
 * warranties or conditions of any kind, either express or implied. Do not remove
 * this NOTICE section as it contains license information and copyrights.
 */


/**
 * PagesDataTable class
 * 
 * @since 3.1
 * @package Osclass
 * @subpackage classes
 * @author Osclass
 */
class PagesDataTable extends DataTable
{

  private $pages;
  private $total_filtered;

  /**
   * @param $params
   *
   * @return array
   * @throws \Exception
   */
  public function table( $params )
  {
    
    $this->addTableHeader();

    $start = ((int)$params['iPage']-1) * $params['iDisplayLength'];

    $this->start = (int) $start;
    $this->limit = (int) $params[ 'iDisplayLength' ];
    
    $pages = Page::newInstance()->listAll(0, null, null, $this->start, $this->limit);
    $this->processData($pages);
    
    $this->total = Page::newInstance()->count(0);
    $this->total_filtered = $this->total;
    
    return $this->getData();
  }

  private function addTableHeader()
  {

    $this->addColumn('bulkactions', '<input id="check_all" type="checkbox" />');
    $this->addColumn('title', __('Title'));
    $this->addColumn('internal_name', __('Internal name'));
    $this->addColumn('visibility', __('Visibility'));
    $this->addColumn('link', __('Add to footer'));
    $this->addColumn('index', __('Can be indexed'));
    $this->addColumn('pub_date', __('Publish Date'));
    $this->addColumn('order', __('Order'));

    $dummy = &$this;
    osc_run_hook( 'admin_pages_table' , $dummy);
  }

  /**
   * @param $pages
   *
   * @throws \Exception
   */
  private function processData( $pages )
  {
    if(!empty($pages)) {
    
      $prefLocale = osc_current_user_locale();
      foreach($pages as $aRow) {
        $row   = array();
        $content = array();

        if( isset($aRow['locale'][$prefLocale]) && !empty($aRow['locale'][$prefLocale]['s_title']) ) {
          $content = $aRow['locale'][$prefLocale];
        } else {
          $content = current($aRow['locale']);
        }

        // -- options --
        $options   = array();
        View::newInstance()->_exportVariableToView('page', $aRow );
        $options[] = '<a href="' . osc_static_page_url() . '" target="_blank">' . __('View page') . '</a>';
        $options[] = '<a href="' . osc_admin_base_url(true) . '?page=pages&amp;action=edit&amp;id=' . $aRow['pk_i_id'] . '">' . __('Edit') . '</a>';
        if( !$aRow['b_indelible'] ) {
          $options[] = '<a onclick="return delete_dialog(\'' . $aRow['pk_i_id'] . '\');" href="' . osc_admin_base_url(true) . '?page=pages&amp;action=delete&amp;id=' . $aRow['pk_i_id'] . '&amp;' . osc_csrf_token_url() . '">' . __('Delete') . '</a>';
        }

        $auxOptions = '<ul>'.PHP_EOL;
        foreach( $options as $actual ) {
          $auxOptions .= '<li>'.$actual.'</li>'.PHP_EOL;
        }
        $actions = '<div class="actions">'.$auxOptions.'</div>'.PHP_EOL;

        $row['id'] = $aRow['pk_i_id'];
        $row['bulkactions'] = '<input type="checkbox" name="id[]"" value="' . $aRow['pk_i_id'] . '"" />';
        $row['title'] = $content['s_title'] . $actions;
        $row['internal_name'] = $aRow['s_internal_name'];
        $row['visibility'] = osc_static_page_visibility_name($aRow['i_visibility']);
        $row['link'] = ($aRow['b_link'] == 1 ? __('Yes') : __('No'));
        $row['index'] = ($aRow['b_index'] == 1 ? __('Yes') : __('No'));
        $row['pub_date'] = ($aRow['dt_pub_date'] <> '' ? $aRow['dt_pub_date'] : '-');
        $row['order'] = '<div class="order-box">' . $aRow['i_order'] . ' <img class="up" onclick="order_up(' . $aRow['pk_i_id'] . ');" src="' . osc_current_admin_theme_url('images/arrow_up.png') . '" alt="' . __('Up') . '" title="' . __('Up') . '" />  <img class="down" onclick="order_down(' . $aRow['pk_i_id'] . ');" src="' . osc_current_admin_theme_url('images/arrow_down.png') .'" alt="' . __('Down') . '" title="' . __('Down') . '" /></div>';

        $row = osc_apply_filter('pages_processing_row', $row, $aRow);

        $this->addRow($row);
        $this->rawRows[] = $aRow;
      }

    }
  }
}