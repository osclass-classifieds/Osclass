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
 * UsersDataTable class
 *
 * @since 3.1
 * @package Osclass
 * @subpackage classes
 * @author Osclass
 */
class UsersDataTable extends DataTable {
  private $withUserId;
  private $search;
  private $order_by;
  private $conditions;
  private $withFilters = false;

  public function __construct() {
    parent::__construct();
    osc_add_filter('datatable_user_class', array(&$this, 'row_class'));
  }

  /**
   * @param $params
   *
   * @return array
   */
  public function table($params) {
    $this->withUserId = false;
    $this->search = '';
    $this->addTableHeader();
    $this->getDBParams($params);

    $list_users = User::newInstance()->search($this->start, $this->limit, $this->order_by['column_name'], $this->order_by['type'], $this->conditions);

    $this->processData($list_users['users']);
    $this->total = $list_users['rows'];
    $this->totalFiltered = $list_users['total_results'];

    return $this->getData();
  }

  private function addTableHeader() {
    Rewrite::newInstance()->init();
    $page = (int)Params::getParam('iPage');
    if($page == 0) { $page = 1; }
    Params::setParam('iPage', $page);

    $url_base = preg_replace('|&direction=([^&]*)|', '', preg_replace('|&sort=([^&]*)|', '', osc_base_url() . Rewrite::newInstance()->get_raw_request_uri()));
    $sort = Params::getParam('sort');
    $direction = Params::getParam('direction');

    $this->clearSortColumns();
    $this->clearSourceColumns();
    $this->setDefaultSort('update_date', 'desc');

    // List of sortable columns in datatable
    $this->addSortColumn('name', 's_name', true);
    $this->addSortColumn('email', 's_email', true);
    $this->addSortColumn('phone', 'COALESCE(s_phone_mobile, s_phone_land)');
    $this->addSortColumn('username', 's_username', true);
    $this->addSortColumn('items', 'i_items', true);
    $this->addSortColumn('update_date', 'COALESCE(dt_mod_date, dt_reg_date)');
    $this->addSortColumn('access_date', 'dt_access_date');

    // Source columns used by data-source-col in table header
    $this->addSourceColumn('name', 's_name');
    $this->addSourceColumn('email', 's_email');
    $this->addSourceColumn('phone', 's_phone_mobile|s_phone_land');
    $this->addSourceColumn('username', 's_username');
    $this->addSourceColumn('items', 'i_items');
    $this->addSourceColumn('update_date', 'dt_mod_date|dt_reg_date');
    $this->addSourceColumn('access_date', 'dt_access_date');
    $this->addSourceColumn('location', 's_country|s_region|s_city|s_zip|s_address');

    // Table header columns rendered in admin
    $this->addColumn('status-border', '');
    $this->addColumn('status', __('Status'));
    $this->addColumn('bulkactions', '<input id="check_all" type="checkbox" />');
    $this->addColumn('name', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('name', $sort, $direction)) . '">' . __('Name') . '</a>');
    $this->addColumn('email', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('email', $sort, $direction)) . '">' . __('E-mail') . '</a>');
    $this->addColumn('phone', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('phone', $sort, $direction)) . '">' . __('Phone') . '</a>');
    $this->addColumn('username', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('username', $sort, $direction)) . '">' . __('Username') . '</a>');
    $this->addColumn('location', __('Location'));
    $this->addColumn('items', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('items', $sort, $direction)) . '">' . __('Items') . '</a>');
    $this->addColumn('update_date', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('update_date', $sort, $direction)) . '">' . __('Last update') . '</a>');
    $this->addColumn('access_date', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('access_date', $sort, $direction)) . '">' . __('Last access date') . '</a>');

    $dummy = &$this;
    osc_run_hook('admin_users_table' , $dummy);
  }

  /**
   * @param $users
   */
  private function processData($users) {
    if(!empty($users)) {
      $csrf_token_url = osc_csrf_token_url();
      foreach($users as $aRow) {
        $row = array();
        $options    = array();
        $options_more   = array();
        // first column

        $options[]  = '<a href="' . osc_admin_base_url(true) . '?page=users&action=edit&amp;id=' . $aRow['pk_i_id'] . '">' . __('Edit') . '</a>';
        $options[]  = '<a onclick="return delete_dialog(\'' . $aRow['pk_i_id'] . '\');" href="' . osc_admin_base_url(true) . '?page=users&action=delete&amp;id[]=' . $aRow['pk_i_id'] . '">' . __('Delete') . '</a>';
        $options[] = '<a href="' . osc_admin_base_url(true) . '?page=users&action=login&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '" target="_blank">' . sprintf(__('Log in as %s'), osc_highlight($aRow['s_name'], 20)) . '</a>';


        if($aRow['b_active'] == 1) {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=users&action=deactivate&amp;id[]=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '">' . __('Deactivate') . '</a>';
        } else {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=users&action=activate&amp;id[]=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url .'">' . __('Activate') . '</a>';
        }
        if($aRow['b_enabled'] == 1) {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=users&action=disable&amp;id[]=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '">' . __('Block') . '</a>';
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=users&action=disable_items&amp;id[]=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '">' . __('Block all items') . '</a>';
        } else {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=users&action=enable&amp;id[]=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '">' . __('Unblock') . '</a>';
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=users&action=enable_items&amp;id[]=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '">' . __('Unblock all items') . '</a>';
        }
        if(osc_user_validation_enabled() && ($aRow['b_active'] == 0)) {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=users&action=resend_activation&amp;id[]=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '">' . __('Re-send activation email') . '</a>';
        }

        $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=items&userId=' . (int)$aRow['pk_i_id'] . '&user=' . rawurlencode($aRow['s_name']) . '">' . __('View items') . '</a>';
        $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=users&action=edit&amp;id=' . $aRow['pk_i_id'] . '&amp;open_message=1">' . __('Send message') . '</a>';
        if(osc_alerts_enabled()) {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=users&action=alerts&amp;alertUserId=' . (int)$aRow['pk_i_id'] . '">' . __('View alerts') . '</a>';
        }

        $options_force_more = array();
        $options_force_more[] = '<a href="' . osc_admin_base_url(true) . '?page=items&userId=' . (int)$aRow['pk_i_id'] . '&user=' . rawurlencode($aRow['s_name']) . '">' . __('View items') . '</a>';
        $options_force_more[] = '<a href="' . osc_admin_base_url(true) . '?page=users&action=edit&amp;id=' . $aRow['pk_i_id'] . '&amp;open_message=1">' . __('Send message') . '</a>';
        if(osc_alerts_enabled()) {
          $options_force_more[] = '<a href="' . osc_admin_base_url(true) . '?page=users&action=alerts&amp;alertUserId=' . (int)$aRow['pk_i_id'] . '">' . __('View alerts') . '</a>';
        }

        $options_more = osc_apply_filter('more_actions_manage_users', $options_more, $aRow);
        // more actions
        $options = osc_apply_filter('actions_manage_users', $options, $aRow);
        $actions = $this->buildRowActions($options, $options_more, 8, $options_force_more);

        $status = $this->get_row_status($aRow);
        $row['id'] = $aRow['pk_i_id'];
        $row['status-border'] = '';
        $row['status'] = $status['text'];
        $row['bulkactions'] = '<input type="checkbox" name="id[]" value="' . $aRow['pk_i_id'] . '" /></div>';
        $userName = $aRow['s_name'];
        $profileUrl = trim((string)osc_user_public_profile_url($aRow['pk_i_id'], $aRow));
        if($aRow['b_active'] == 1 && $aRow['b_enabled'] == 1 && $profileUrl != '' && $profileUrl != '#') {
          $userName = '<a href="' . $profileUrl . '" target="_blank">' . osc_esc_html($aRow['s_name']) . '<span class="icon-new-window"></span></a>';
        } else {
          $userName = osc_esc_html($aRow['s_name']);
        }
        $row['name'] = $userName . $actions;
        $row['email'] = '<a href="' . osc_admin_base_url(true) . '?page=items&userId='. $aRow['pk_i_id'] .'&user='. $aRow['s_name'] .'">' . $aRow['s_email'] . '</a>';
        $phone = trim((string)($aRow['s_phone_mobile'] <> '' ? $aRow['s_phone_mobile'] : $aRow['s_phone_land']));
        if($phone != '') {
          $phoneFilter = ltrim($phone, '+');
          $row['phone'] = '<a href="' . osc_admin_base_url(true) . '?page=users&s_phone=' . rawurlencode($phoneFilter) . '">' . osc_esc_html($phone) . '</a>';
        } else {
          $row['phone'] = '';
        }
        $row['username'] = $aRow['s_username'];

        $locationParts = array();
        if(trim((string)$aRow['s_country']) != '') {
          $locationParts[] = '<a href="' . osc_admin_base_url(true) . '?page=users&countryName=' . rawurlencode($aRow['s_country']) . '">' . osc_esc_html($aRow['s_country']) . '</a>';
        }
        if(trim((string)$aRow['s_region']) != '') {
          $locationParts[] = '<a href="' . osc_admin_base_url(true) . '?page=users&region=' . rawurlencode($aRow['s_region']) . '">' . osc_esc_html($aRow['s_region']) . '</a>';
        }
        if(trim((string)$aRow['s_city']) != '') {
          $locationParts[] = '<a href="' . osc_admin_base_url(true) . '?page=users&city=' . rawurlencode($aRow['s_city']) . '">' . osc_esc_html($aRow['s_city']) . '</a>';
        }
        if(trim((string)$aRow['s_zip']) != '') {
          $locationParts[] = '<a href="' . osc_admin_base_url(true) . '?page=users&s_zip=' . rawurlencode($aRow['s_zip']) . '">' . osc_esc_html($aRow['s_zip']) . '</a>';
        }

        if(trim((string)$aRow['s_address']) != '') {
          $locationParts[] = '<a href="' . osc_admin_base_url(true) . '?page=users&address=' . rawurlencode($aRow['s_address']) . '">' . osc_esc_html($aRow['s_address']) . '</a>';
        }

        $location = implode(', ', $locationParts);
        $row['location'] = ($location != '' ? $location : '-');
        $item_count = (int)$aRow['i_items'];
        if($item_count > 0) {
          $row['items'] = '<a href="' . osc_admin_base_url(true) . '?page=items&userId=' . (int)$aRow['pk_i_id'] . '&user=' . rawurlencode($aRow['s_name']) . '">' . $item_count . '</a>';
        } else {
          $row['items'] = $item_count;
        }
        $row['update_date'] = osc_format_date($aRow['dt_mod_date'] != null ? $aRow['dt_mod_date'] : $aRow['dt_reg_date'], osc_date_format() . ' ' . osc_time_format());
        $row['access_date'] = ($aRow['dt_access_date'] != null) ? osc_format_date($aRow['dt_access_date'], osc_date_format() . ' ' . osc_time_format()) : '<em class="access-never">' . __('Never') . '</em>';

        $row = osc_apply_filter('users_processing_row', $row, $aRow);

        $this->addRow($row);
        $this->rawRows[] = $aRow;
      }
    }
  }

  /**
   * @param array $params
   */
  private function getDBParams($params) {
    if(!isset($params['iDisplayStart'])) {
      $params['iDisplayStart'] = 0;
    }

    $p_iPage = 1;

    if(!is_numeric(Params::getParam('iPage')) || Params::getParam('iPage') < 1) {
      Params::setParam('iPage', $p_iPage);
      $this->iPage = $p_iPage;
    } else {
      $this->iPage = Params::getParam('iPage');
    }

    $sortData = $this->resolveSort($params);
    $this->order_by['column_name'] = ($sortData['column'] != '' ? $sortData['column'] : 'pk_i_id');
    $this->order_by['type'] = strtoupper($sortData['direction']);
    Params::setParam('sort', $sortData['key']);
    Params::setParam('direction', $sortData['direction']);

    $this->conditions = array();
    if(@$params['userId'] != '') {
      if(substr($params['userId'], 0, 1) == '"' || substr($params['userId'], -1) == '"') {
        $this->conditions['pk_i_id'] = str_replace('"','', $params['userId']);
      } else {
        $this->conditions['pk_i_id'] = str_replace('*','%', $params['userId']);
      }

      $this->withFilters = true;
    }

    if(@$params['s_email'] != '') {
      if(substr($params['s_email'], 0, 1) == '"' || substr($params['s_email'], -1) == '"') {
        $esc_email = User::newInstance()->dao->escapeStr(str_replace('*','%', str_replace('"','', $params['s_email'])));
        $this->conditions['s_email'] = $esc_email;
      } else {
        $esc_email = User::newInstance()->dao->escapeStr(str_replace('%%', '%', str_replace('*','%', '%' . $params['s_email'] . '%')));
        $this->conditions["s_email LIKE '" . $esc_email . "'"] = null;
      }

      $this->withFilters = true;
    }

    if(@$params['s_name'] != '') {
      if(substr($params['s_name'], 0, 1) == '"' || substr($params['s_name'], -1) == '"') {
        $esc_email = User::newInstance()->dao->escapeStr(str_replace('*','%', str_replace('"','', $params['s_name'])));
        $this->conditions['s_name'] = $esc_email;
      } else {
        $esc_email = User::newInstance()->dao->escapeStr(str_replace('%%', '%', str_replace('*','%', '%' . $params['s_name'] . '%')));
        $this->conditions["s_name LIKE '" . $esc_email . "'"] = null;
      }

      $this->withFilters = true;

    } else if(@$params['user'] != '') {
      if(@$params['userId'] == '') {
        if(substr($params['user'], 0, 1) == '"' || substr($params['user'], -1) == '"') {
          $esc_user = User::newInstance()->dao->escapeStr(str_replace('*','%', str_replace('"','', $params['user'])));
          $this->conditions["(CAST(pk_i_id AS CHAR) = '" . $esc_user . "' OR s_email = '" . $esc_user . "' OR s_name = '" . $esc_user . "' OR s_username = '" . $esc_user . "' OR s_phone_mobile = '" . $esc_user . "' OR s_phone_land = '" . $esc_user . "' OR s_country = '" . $esc_user . "' OR s_region = '" . $esc_user . "' OR s_city = '" . $esc_user . "' OR s_address = '" . $esc_user . "' OR s_zip = '" . $esc_user . "')"] = null;
        } else {
          $esc_user = User::newInstance()->dao->escapeStr(str_replace('%%', '%', str_replace('*','%', '%' . $params['user'] . '%')));
          $this->conditions["(CAST(pk_i_id AS CHAR) LIKE '" . $esc_user . "' OR s_email LIKE '" . $esc_user . "' OR s_name LIKE '" . $esc_user . "' OR s_username LIKE '" . $esc_user . "' OR s_phone_mobile LIKE '" . $esc_user . "' OR s_phone_land LIKE '" . $esc_user . "' OR s_country LIKE '" . $esc_user . "' OR s_region LIKE '" . $esc_user . "' OR s_city LIKE '" . $esc_user . "' OR s_address LIKE '" . $esc_user . "' OR s_zip LIKE '" . $esc_user . "')"] = null;
        }
      } else {
        $this->conditions['s_name'] = str_replace('*', '%', $params['user']);
      }

      $this->withFilters = true;
    }

    if(@$params['s_username']!='') {
      if(substr($params['s_username'], 0, 1) == '"' || substr($params['s_username'], -1) == '"') {
        $esc_username = User::newInstance()->dao->escapeStr(str_replace('*','%', str_replace('"','', $params['s_username'])));
        $this->conditions['s_username'] = $esc_username;
      } else {
        $esc_username = User::newInstance()->dao->escapeStr(str_replace('%%', '%', str_replace('*','%', '%' . $params['s_username'] . '%')));
        $this->conditions["s_username LIKE '" . $esc_username . "'"] = null;
      }

      $this->withFilters = true;
    }


    if(@$params['s_phone']!='') {
      if(substr($params['s_phone'], 0, 1) == '"' || substr($params['s_phone'], -1) == '"') {
        $esc_phone = User::newInstance()->dao->escapeStr(str_replace('*','%', str_replace('"','', $params['s_phone'])));
        $this->conditions["(s_phone_mobile = '" . $esc_phone . "' OR s_phone_land = '" . $esc_phone . "')"] = null;
      } else {
        $esc_phone = User::newInstance()->dao->escapeStr(str_replace('%%', '%', str_replace('*','%', '%' . $params['s_phone'] . '%')));
        $this->conditions["(s_phone_mobile LIKE '" . $esc_phone . "' OR s_phone_land LIKE '" . $esc_phone . "')"] = null;
      }

      $this->withFilters = true;
    }

    if(@$params['s_zip']!='') {
      $zip_value = str_replace('"','', $params['s_zip']);
      $esc_zip = User::newInstance()->dao->escapeStr(str_replace('%%', '%', str_replace('*','%', '%' . $zip_value . '%')));
      $this->conditions["s_zip LIKE '" . $esc_zip . "'"] = null;

      $this->withFilters = true;
    }

    if(@$params['countryId']!='') {
      $this->conditions['fk_c_country_code'] = $params['countryId'];
      $this->withFilters = true;

    } else if(@$params['countryName']!='') {
      $this->conditions['s_country'] = $params['countryName'];
      $this->withFilters = true;
    }

    if(@$params['regionId']!='') {
      $this->conditions['fk_i_region_id'] = $params['regionId'];
      $this->withFilters = true;

    } else if(@$params['region']!='') {
      $this->conditions['s_region'] = $params['region'];
      $this->withFilters = true;
    }

    if(@$params['cityId']!='') {
      $this->conditions['fk_i_city_id'] = $params['cityId'];
      $this->withFilters = true;

    } else if(@$params['city']!='') {
      $this->conditions['s_city'] = $params['city'];
      $this->withFilters = true;
    }

    if(@$params['address']!='') {
      $address_value = str_replace('"','', $params['address']);
      $esc_address = User::newInstance()->dao->escapeStr(str_replace('%%', '%', str_replace('*','%', '%' . $address_value . '%')));
      $this->conditions["s_address LIKE '" . $esc_address . "'"] = null;
      $this->withFilters = true;
    }

    if(@$params['b_enabled']!='') {
      $this->conditions['b_enabled'] = $params['b_enabled'];
      $this->withFilters = true;
    }

    if(@$params['b_active']!='') {
      $this->conditions['b_active'] = $params['b_active'];
      $this->withFilters = true;
    }


    // set start and limit using iPage param
    $start = ($this->iPage - 1) * $params['iDisplayLength'];

    $this->start = (int)$start;
    $this->limit = (int)$params['iDisplayLength'];
  }

  /**
   * @return bool
   */
  public function withFilters() {
    return $this->withFilters;
  }

  /**
   * @param $class
   * @param $rawRow
   * @param $row
   *
   * @return array
   */
  public function row_class($class , $rawRow , $row) {
    $status = $this->get_row_status($rawRow);
    $class[] = $status['class'];
    return $class;
  }

  /**
   * Get the status of the row. There are three status:
   *   - blocked
   *   - inactive
   *   - active
   *
   * @since 3.3
   *
   * @param $user
   *
   * @return array Array with the class and text of the status of the listing in this row. Example:
   *   array(
   *     'class' => '',
   *     'text'  => ''
   *  )
   */
  private function get_row_status($user) {
    if($user['b_enabled']==0) {
      return array(
        'class' => 'status-blocked',
        'text'  => __('Blocked')
      );
    }

    if($user['b_active']==0) {
      return array(
        'class' => 'status-inactive',
        'text'  => __('Inactive')
      );
    }

    return array(
      'class' => 'status-active',
      'text'  => __('Active')
    );
  }
}
