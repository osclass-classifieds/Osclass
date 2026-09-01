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
 * AlertsDataTable class
 * 
 * @since 3.1
 * @package Osclass
 * @subpackage classes
 * @author Osclass
 */
class AlertsDataTable extends DataTable
{
  
  private $search;
  private $order_by;
  private $total_filtered;

  public function __construct()
  {
    parent::__construct();
    osc_add_filter('datatable_alert_class', array(&$this, 'row_class'));
  }
  
  /**
   * @param $params
   *
   * @return array
   */
  public function table( $params )
  {
    
    $this->addTableHeader();
    $this->getDBParams($params);

    $alerts = Alerts::newInstance()->search($this->start, $this->limit, $this->order_by['column_name'], $this->order_by['type'], $this->search);
    $this->processData($alerts);
    $this->total = $alerts['total_results'];
    $this->total_filtered = $alerts['rows'];

    return $this->getData();
  }

  private function addTableHeader()
  {

    $this->addColumn('status-border', '');
    $this->addColumn('status', __('Status'));
    $this->addColumn('bulkactions', '<input id="check_all" type="checkbox"/>');
    $this->addColumn('email', __('E-mail'));
    $this->addColumn('name', __('Name'));
    $this->addColumn('alert', __('Details'));
    $this->addColumn('trigger', __('Triggered'));
    $this->addColumn('create_date', __('Create date'));
    $this->addColumn('unsub_date', __('Unsubscribe date'));

    $dummy = &$this;
    osc_run_hook( 'admin_alerts_table' , $dummy);
  }

  /**
   * @param $alerts
   */
  private function processData( $alerts )
  {
    if(!empty($alerts) && !empty($alerts['alerts'])) {

      $csrf_token_url = osc_csrf_token_url();
      foreach($alerts['alerts'] as $aRow) {
        $row = array();
        $options = array();

        $row['id'] = $aRow['pk_i_id'];
        $row['status-border'] = '';
        $row['status'] = ($aRow['b_active'] == 1 ? __('Active') : __('Inactive'));
        $row['bulkactions'] = '<input type="checkbox" name="alert_id[]" value="' . $aRow['pk_i_id'] . '" /></div>';

        $options[] = '<a onclick="return delete_alert(\'' . $aRow['pk_i_id'] . '\');" href="#">' . __('Delete') . '</a>';

        if( $aRow['b_active'] == 1 ) {
          $options[] = '<a href="' . osc_admin_base_url(true) . '?page=users&action=status_alerts&amp;alert_id[]=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;status=0" >' . __('Deactivate') . '</a>';
        } else {
          $options[] = '<a href="' . osc_admin_base_url(true) . '?page=users&action=status_alerts&amp;alert_id[]=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;status=1" >' . __('Activate') . '</a>';
        }

        $options[] = '<a href="#" class="alert-popup" data-id="' . osc_esc_html($aRow['pk_i_id']) . '" data-secret="' . osc_esc_html($aRow['s_secret']) . '" data-conditions="' . osc_esc_html($aRow['s_search']) . '" data-params="' . osc_esc_html($aRow['s_param']) . '" data-sql="' . osc_esc_html($aRow['s_sql']) . '">' . __('Details') . '</a>';
        $options[] = '<a href="' . osc_search_alert_url($aRow['pk_i_id'], $aRow['s_secret']) . '" target="_blank">' . __('Open in search') . '</a>';

        $options = osc_apply_filter('actions_manage_alerts', $options, $aRow);
        
        // create list of actions
        $auxOptions = '<ul>'.PHP_EOL;
        foreach( $options as $actual ) {
          $auxOptions .= '<li>'.$actual.'</li>'.PHP_EOL;
        }
        $auxOptions  .= '</ul>'.PHP_EOL;

        $actions = '<div class="actions">'.$auxOptions.'</div>'.PHP_EOL;

        $row['name'] = ($aRow['s_name'] <> '' ? $aRow['s_name'] : '-');
        $row['email'] = '<a href="' . osc_admin_base_url(true) . '?page=items&userId=">' . $aRow['s_email'] . '</a>'. $actions;



        $pieces = array();
        $conditions = osc_get_raw_search((array)json_decode($aRow['s_search'], true));
        
        if(isset($conditions['sPattern']) && $conditions['sPattern']!='') {
          $pieces[] = sprintf( __( '<b>Pattern:</b> %s' ), $conditions['sPattern']);
        }
        
        if(isset($conditions['aCategories']) && !empty($conditions['aCategories'])) {
          $l = min(count($conditions['aCategories']), 4);
          $cat_array = array();
          for($c=0;$c<$l;$c++) {
            $cat_array[] = $conditions['aCategories'][$c];
          }
          if(count($conditions['aCategories'])>$l) {
            $cat_array[] = '<a href="#" class="more-tooltip" categories="'.osc_esc_html(implode( ', ' , $conditions['aCategories'])) . '" >' . __( '...More' ) . '</a>';
          }

          $pieces[] = sprintf( __( '<b>Categories:</b> %s' ), implode(', ', $cat_array));
        }

        $details = osc_generate_alert_name($aRow['s_search'], 1000, true);
        
        if(strlen($details) > 240) {
          $row['alert'] = substr($details, 0, 200);
          $row['alert'] .= '<a href="#" class="more-tooltip" details="' . osc_esc_html($details) . '">' . __( '...More' ) . '</a>';
          
        } else {
          $row['alert'] = $details;
        }

        $row['trigger'] = $aRow['i_num_trigger'] . 'x';
        $row['create_date'] = osc_format_date($aRow['dt_date']);
        $row['unsub_date'] = ($aRow['dt_unsub_date'] <> '' ? osc_format_date($aRow['dt_unsub_date']) : '-');

        $row = osc_apply_filter('alerts_processing_row', $row, $aRow);

        $this->addRow($row);
        $this->rawRows[] = $aRow;
      }

    }
  }

  /**
   * @param $_get
   */
  private function getDBParams( $_get )
  {
    
    
    $column_names  = array(
      0 => 'dt_date',
      1 => 's_email',
      2 => 's_search',
      3 => 'dt_date'
    );
    
    $this->order_by['column_name'] = 'c.dt_pub_date';
    $this->order_by['type'] = 'desc';

    if( !isset($_get['iDisplayStart']) ) {
      $_get['iDisplayStart'] = 0;
    }
    $p_iPage    = 1;
    if( !is_numeric(Params::getParam('iPage')) || Params::getParam('iPage') < 1 ) {
      Params::setParam('iPage', $p_iPage );
      $this->iPage = $p_iPage;
    } else {
      $this->iPage = Params::getParam('iPage');
    }
    
    $this->order_by['column_name'] = 'dt_date';
    $this->order_by['type'] = 'DESC';
    foreach($_get as $k=>$v) {
      if( $k === 'sSearch' ) {
        $this->search = $v;
      }

      /* for sorting */
      if( $k === 'iSortCol_0' ) {
        $this->order_by['column_name'] = $column_names[$v];
      }
      if( $k === 'sSortDir_0' ) {
        $this->order_by['type'] = $v;
      }
    }
    // set start and limit using iPage param
    $start = ($this->iPage - 1) * $_get['iDisplayLength'];

    $this->start = (int) $start;
    $this->limit = (int) $_get[ 'iDisplayLength' ];

  }  
  
  /**
   * @param $class
   * @param $rawRow
   * @param $row
   *
   * @return array
   */
  public function row_class($class, $rawRow, $row)
  {
    $class[] = ($rawRow['b_active'] == 1 ? 'status-active' : 'status-inactive');
    return $class;
  }
}