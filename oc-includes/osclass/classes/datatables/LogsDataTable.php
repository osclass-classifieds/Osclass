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
 * BanRulesDataTable class
 * 
 * @since 3.1
 * @package Osclass
 * @subpackage classes
 * @author Osclass
 */
class LogsDataTable extends DataTable {
  private $order_by;
  private $keyword;

  /**
   * @param $params
   *
   * @return array
   */
  public function table($params) {
    
    $this->addTableHeader();
    $this->getDBParams($params);

    $list_logs = Log::newInstance()->search(array(
      'start' => $this->start, 
      'limit' => $this->limit, 
      'order_column' => $this->order_by['column_name'], 
      'order_direction' => $this->order_by['type'], 
      'keyword' => $this->keyword
    ));
    
    $this->processData($list_logs['logs']);
    $this->totalFiltered = $list_logs['rows'];
    $this->total = $list_logs['total_results'];
    
    return $this->getData();
  }


  private function addTableHeader() {
    $this->addColumn('bulkactions', '<input id="check_all" type="checkbox" />');
    $this->addColumn('section', __('Section'));
    $this->addColumn('action', __('Action'));
    $this->addColumn('id', __('ID'));
    $this->addColumn('data', __('Data'));
    $this->addColumn('comment', __('Comment'));
    $this->addColumn('ip', __('IP'));
    $this->addColumn('who', __('Who'));
    $this->addColumn('date', __('Date'));

    $dummy = &$this;
    osc_run_hook('admin_logs_table', $dummy);
  }

  /**
   * @param $rules
   */
  private function processData($rules) {
    if(!empty($rules)) {
      $csrf_token_url = osc_csrf_token_url();
      
      foreach($rules as $aRow) {
        $unique_id = implode('|', array((string)$aRow['dt_date'], (string)$aRow['s_section'], (string)$aRow['s_action'], (string)$aRow['fk_i_id']));

        $row = array();
        $options = array();
        $options_more = array();

        $options[] = '<a onclick="return delete_dialog(\'' . $unique_id . '\');" href="' . osc_admin_base_url(true) . '?page=tools&action=logs_delete&amp;' . $csrf_token_url . '&amp;id[]=' . urlencode($unique_id) . '">' . __('Delete') . '</a>';
        $options[] = '<a onclick="return show_hide_log_details(this, \'' . $unique_id . '\');" href="#" data-alt="' . osc_esc_html(__('Hide details')) . '">' . __('Show details') . '</a>';

        $options_more = osc_apply_filter('more_actions_manage_logs', $options_more, $aRow);
        
        // more actions
        $moreOptions = '';
        if(count($options_more) > 0) {
          $moreOptions .= '<li class="show-more">'.PHP_EOL.'<a href="#" class="show-more-trigger">'. __('Show more') .'...</a>'. PHP_EOL .'<ul>'. PHP_EOL;
          foreach($options_more as $actual) { 
            $moreOptions .= '<li>'.$actual . '</li>' . PHP_EOL;
          }
          
          $moreOptions .= '</ul>'. PHP_EOL .'</li>'.PHP_EOL;
        }
        
        $options = osc_apply_filter('actions_manage_logs', $options, $aRow);
        
        // create list of actions
        $auxOptions = '<ul>'.PHP_EOL;
        
        foreach($options as $actual) {
          $auxOptions .= '<li>'.$actual.'</li>'.PHP_EOL;
        }
        
        $auxOptions .= $moreOptions;
        $auxOptions .= '</ul>'.PHP_EOL;

        $actions = '<div class="actions">'.$auxOptions.'</div>'.PHP_EOL;
        $details = '<div id="details-' . $unique_id . '" class="log-details" style="display:none;"><code>'. ($aRow['s_detail'] <> '' ? $aRow['s_detail'] : '- ' . __('No details found') . ' -') .'</code></div>'.PHP_EOL;

        $row['bulkactions'] = '<input type="checkbox" name="id[]" value="' . urlencode($unique_id) . '" /></div>';
        
        $row['section'] = ($aRow['s_section'] <> '' ? $aRow['s_section'] : '-');
        $row['section'] .= $actions;

        $row['action'] = ($aRow['s_action'] <> '' ? $aRow['s_action'] : '-');
        $row['id'] = ($aRow['fk_i_id'] <> '' ? $aRow['fk_i_id'] : '-');

        $section_type = ($aRow['s_section'] <> '' ? $aRow['s_section'] : '-');
        $section_id = (int)($aRow['fk_i_id'] <> '' ? $aRow['fk_i_id'] : 0);
        
        if($section_type === 'user' && $section_id > 0) {
          $user = osc_get_user_row($section_id);
          
          if($user !== false) {
            $name = $user['s_name'];
            $url = osc_admin_base_url(true) . '?page=users&action=edit&id=' . $section_id;

            $row['id'] = $section_id . ' (<a href="' . osc_esc_html($url) . '" target="_blank">' . $name . '</a>)';
          }
          
        } else if($section_type === 'item' && $section_id > 0) {
          $item = osc_get_item_row($section_id);
          
          if($item !== false) {
            $title = osc_highlight($item['s_title'], 40);
            $url = osc_admin_base_url(true) . '?page=items&action=item_edit&id=' . $section_id;

            $row['id'] = $section_id . ' (<a href="' . osc_esc_html($url) . '" target="_blank">' . $title . '</a>)';
          }
        }
        
        $row['data'] = ($aRow['s_data'] <> '' ? $aRow['s_data'] : '-');
        $row['data'] .= $details;
        
        $row['comment'] = ($aRow['s_comment'] <> '' ? $aRow['s_comment'] : '-');
        $row['ip'] = ($aRow['s_ip'] <> '' ? $aRow['s_ip'] : '-');
        $row['who'] = ($aRow['s_who'] <> '' ? $aRow['s_who'] : '-');
        
        $who_type = ($aRow['s_who'] ?: '-');
        $who_id = (int)($aRow['fk_i_who_id'] ?? 0);
        
        if($who_type === 'user' && $who_id > 0) {
          $user = osc_get_user_row($who_id);
          
          if($user !== false) {
            $name = $user['s_name'];
            $url = osc_admin_base_url(true) . '?page=users&action=edit&id=' . $who_id;

            $row['who'] = $who_type . ' (<a href="' . osc_esc_html($url) . '" target="_blank">' . $name . '</a>)';
          }
        }
        
        
        $row['date'] = '<span title="' . osc_esc_html($aRow['dt_date']) . '">' . ($aRow['dt_date'] <> '' ? osc_format_date($aRow['dt_date']) : '-') . '</span>';

        $row = osc_apply_filter('rules_processing_row', $row, $aRow);

        $this->addRow($row);
        $this->rawRows[] = $aRow;
      }

    }
  }

  /**
   * @param $_get
   */
  private function getDBParams($_get) {
    if(!isset($_get['iDisplayStart'])) {
      $_get['iDisplayStart'] = 0;
    }
    
    $p_iPage = 1;
    
    if(!is_numeric(Params::getParam('iPage')) || Params::getParam('iPage') < 1) {
      Params::setParam('iPage', $p_iPage);
      $this->iPage = $p_iPage;
    } else {
      $this->iPage = Params::getParam('iPage');
    }
    
    $this->order_by['column_name'] = 'dt_date';
    $this->order_by['type'] = 'DESC';
    
    foreach($_get as $k=>$v) {
      if($k === 'user') {
        $this->search = $v;
      }
      
      /* for sorting */
      if($k === 'iSortCol_0') {
        $this->order_by['column_name'] = $this->column_names[$v];
      }
      
      if($k === 'sSortDir_0') {
        $this->order_by['type'] = $v;
      }
    }
    // set start and limit using iPage param
    $start = ($this->iPage - 1) * $_get['iDisplayLength'];

    $this->start = (int) $start;
    $this->limit = (int) $_get['iDisplayLength'];

    $this->keyword = isset($_get['sSearch']) ? $_get['sSearch'] : '';
  }
}