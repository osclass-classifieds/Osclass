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
 * ItemsDataTable class
 *
 * @since 3.1
 * @package Osclass
 * @subpackage classes
 * @author Osclass
 */
class ItemsDataTable extends DataTable {
  private $mSearch;
  private $withFilters = false;

  public function __construct() {
    parent::__construct();
    osc_add_filter('datatable_listing_class', array(&$this, 'row_class'));
  }

  /**
   * @param $params
   *
   * @return array
   * @throws \Exception
   */
  public function table($params) {
    $this->addTableHeader();
    $this->mSearch = new Search(true);
    $this->getDBParams($params);

    // add more conditions here
    osc_run_hook('manage_item_search_conditions', $this->mSearch);

    // do Search
    $this->processData(Item::newInstance()->extendCategoryName($this->mSearch->doSearch()));
    $this->total = $this->mSearch->countAll();
    $this->totalFiltered = $this->mSearch->count();

    return $this->getData();
  }

  private function addTableHeader() {
    Rewrite::newInstance()->init();
    $page = (int)Params::getParam('iPage');
    if($page==0) { $page = 1; }
    Params::setParam('iPage', $page);
    $url_base = preg_replace('|&direction=([^&]*)|', '', preg_replace('|&sort=([^&]*)|', '', osc_base_url().Rewrite::newInstance()->get_raw_request_uri()));
    $sort = Params::getParam('sort');
    $direction = Params::getParam('direction');

    $this->clearSortColumns();
    $this->clearSourceColumns();
    $this->setDefaultSort('date', 'desc');
    // List of sortable columns in datatable
    $this->registerMainSortColumns();
    // Source columns used by data-source-col in table header
    $this->registerMainSourceColumns();
    osc_run_hook('admin_items_sort_columns', $this);

    // Table header columns rendered in admin
    $this->addColumn('status-border', '');
    $this->addColumn('status', __('Status'));
    $this->addColumn('bulkactions', '<input id="check_all" type="checkbox" />');
    $this->addColumn('title', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('title', $sort, $direction)) . '">' . __('Title') . '</a>');
    $this->addColumn('user', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('user', $sort, $direction)) . '">' . __('User') . '</a>');
    $this->addColumn('category', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('category', $sort, $direction)) . '">' . __('Category') . '</a>');
    $this->addColumn('location', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('location', $sort, $direction)) . '">' . __('Location') . '</a>');
    $this->addColumn('date', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('date', $sort, $direction)) . '">' . __('Publish date') . '</a>');
    $this->addColumn('expiration', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('expiration', $sort, $direction)) . '">' . __('Expiration date') . '</a>');
    $this->addColumn('views', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('views', $sort, $direction)) . '">' . __('Views') . '</a>');

    $dummy = &$this;
    osc_run_hook( 'admin_items_table' , $dummy);
  }

  /**
   * @param $items
   *
   * @throws \Exception
   */
  private function processData($items) {
    if(!empty($items)) {

      $csrf_token_url = osc_csrf_token_url();
      foreach($items as $aRow) {
        View::newInstance()->_exportVariableToView('item', $aRow);
        $row   = array();
        $options = array();
        // -- prepare data --
        // prepare item title
        $title = osc_substr($aRow['s_title'], 0, 30);
        if($title != $aRow['s_title']) {
          $title .= '...';
        }


        // icon open add new window
        $title .= '<span class="icon-new-window"></span>';

        // Options of each row
        $options_more = array();
        if($aRow['b_active']) {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=items&amp;action=status&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;value=INACTIVE">' . __('Deactivate') .'</a>';
        } else {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=items&amp;action=status&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;value=ACTIVE">' . __('Activate') .'</a>';
        }
        if($aRow['b_enabled']) {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=items&amp;action=status&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;value=DISABLE">' . __('Block') .'</a>';
        } else {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=items&amp;action=status&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;value=ENABLE">' . __('Unblock') .'</a>';
        }
        if($aRow['b_premium']) {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=items&amp;action=status_premium&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;value=0">' . __('Unmark as premium') .'</a>';
        } else {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=items&amp;action=status_premium&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;value=1">' . __('Mark as premium') .'</a>';
        }
        if($aRow['b_spam']) {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=items&amp;action=status_spam&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;value=0">' . __('Unmark as spam') .'</a>';
        } else {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=items&amp;action=status_spam&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;value=1">' . __('Mark as spam') .'</a>';
        }

        if(osc_renewal_items_enabled()) {
          if($aRow['b_premium'] == 1 || osc_isExpired($aRow['dt_expiration'])) {
            $renewed_count = (int)$aRow['i_renewed'];

            if((osc_renewal_limit() > 0 && $renewed_count < osc_renewal_limit()) || osc_renewal_limit() <= 0) {
              $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=items&amp;action=renew&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;value=1">' . __('Renew') .'</a>';
            }
          }
        }

        // general options
        $options[] = '<a href="' . osc_admin_base_url(true) . '?page=items&amp;action=item_edit&amp;id=' . $aRow['pk_i_id'] . '">' . __('Edit') . '</a>';
        $options[] = '<a onclick="return delete_dialog(\'' . $aRow['pk_i_id'] . '\');" href="' . osc_admin_base_url(true) . '?page=items&amp;action=delete&amp;id[]=' . $aRow['pk_i_id'] . '">' . __('Delete') . '</a>';
        if(isset($aRow['fk_i_user_id']) && (int)$aRow['fk_i_user_id'] > 0) {
          $options[] = '<a href="' . osc_admin_base_url(true) . '?page=users&amp;action=edit&amp;id=' . (int)$aRow['fk_i_user_id'] . '">' . __('Edit user') . '</a>';
        }

        $options_force_more = array();
        $options_force_more[] = '<a href="' . osc_admin_base_url(true) . '?page=comments&amp;itemId=' . $aRow['pk_i_id'] . '">' . __('View comments') . '</a>';
        $options_force_more[] = '<a href="' . osc_admin_base_url(true) . '?page=media&amp;itemId=' . $aRow['pk_i_id'] . '">' . __('View media') . '</a>';

        $options_more = osc_apply_filter('more_actions_manage_items', $options_more, $aRow);
        $options = osc_apply_filter('actions_manage_items', $options, $aRow);
        $actions = $this->buildRowActions($options, $options_more, 8, $options_force_more);

        // fill a row
        $row['id'] = $aRow['pk_i_id'];
        $row['bulkactions'] = '<input type="checkbox" name="id[]" value="' . $aRow['pk_i_id'] . '" active="' . $aRow['b_active'] . '" blocked="' . $aRow['b_enabled'] . '"/>';
        $status = $this->get_row_status();
        $row['status-border'] = '';
        $row['status'] = $status['text'];
        $row['title'] = '<a href="' . osc_item_url() . '" target="_blank">' . $title. '</a>'. $actions;
        if(isset($aRow['fk_i_user_id']) && (int)$aRow['fk_i_user_id'] > 0) {
          $userFilterName = '';
          if(isset($aRow['s_contact_email']) && trim((string)$aRow['s_contact_email']) != '') {
            $userFilterName = (string)$aRow['s_contact_email'];
          } else if(isset($aRow['s_user_name']) && trim((string)$aRow['s_user_name']) != '') {
            $userFilterName = (string)$aRow['s_user_name'];
          }

          $row['user'] = '<a href="' . osc_esc_html($this->build_item_filter_url(array('page' => 'items', 'userId' => (int)$aRow['fk_i_user_id'], 'user' => $userFilterName))) . '">' . osc_esc_html($aRow['s_user_name']) . '</a>';
        } else {
          if(isset($aRow['s_contact_email']) && trim((string)$aRow['s_contact_email']) != '') {
            $row['user'] = '<a href="' . osc_esc_html($this->build_item_filter_url(array('page' => 'items', 'user' => (string)$aRow['s_contact_email']))) . '">' . osc_esc_html($aRow['s_user_name']) . '</a>';
          } else {
            $row['user'] = osc_esc_html($aRow['s_user_name']);
          }
        }
        $row['category'] = $this->get_row_category($aRow);
        $row['location'] = $this->get_row_location($aRow);
        $row['date'] = osc_format_date($aRow['dt_pub_date'], osc_date_format() . ' ' . osc_time_format() );
        $row['expiration'] = ( $aRow['dt_expiration'] !== '9999-12-31 23:59:59') ? osc_format_date( $aRow['dt_expiration'], osc_date_format() . ' ' . osc_time_format() ) : __( 'Never expires');
        $row['views'] = (isset($aRow['i_num_views']) ? $aRow['i_num_views'] : 0) . 'x';

        if(isset($aRow['b_premium']) && $aRow['b_premium'] == 1) {
          $row['views'] .= (isset($aRow['i_num_premium_views']) ? ' / ' . $aRow['i_num_premium_views'] : 0) . 'x';
        }

        $row = osc_apply_filter('items_processing_row', $row, $aRow);

        $this->addRow($row);
        $this->rawRows[] = $aRow;
      }

    }
  }

  /**
   * @param $params
   */
  private function getDBParams($params, $withSort = true) {
    if(!isset($params['iDisplayStart'])) {
      $params['iDisplayStart'] = 0;
    }
    if(!isset($params['iDisplayLength'])) {
      $params['iDisplayLength'] = 10;
    }

    if(!isset($params['iPage']) || !is_numeric($params['iPage']) || $params['iPage'] < 1) {
      Params::setParam('iPage', 1);
      $this->iPage = 1;
    } else {
      $this->iPage = $params['iPage'];
    }

    $withUserId   = false;
    $no_user_email  = '';
    $shortcutFilter = (isset($params['shortcut-filter']) ? (string)$params['shortcut-filter'] : 'oPattern');
    // get & set values
    foreach($params as $k => $v) {
      if($k === 'sSearch' && $v != '') {
        $v = trim((string)$v);
        if($v == '') {
          continue;
        }
        if($shortcutFilter == 'oCategory') {
          $cat = Search::newInstance()->dao->escapeStr(str_replace('*', '%', $v));
          if(strpos($cat, '%') === false) {
            $cat = '%' . $cat . '%';
          }
          $this->mSearch->addItemConditions(DB_TABLE_PREFIX . "t_item.fk_i_category_id IN (SELECT cd.fk_i_category_id FROM " . DB_TABLE_PREFIX . "t_category_description cd WHERE cd.s_name LIKE '" . $cat . "')");
        } else if($shortcutFilter == 'oLocation') {
          $location = Search::newInstance()->dao->escapeStr(str_replace('*', '%', $v));
          if(strpos($location, '%') === false) {
            $location = '%' . $location . '%';
          }
          $this->mSearch->addItemConditions("(" . DB_TABLE_PREFIX . "t_item_location.s_city LIKE '" . $location . "' OR " . DB_TABLE_PREFIX . "t_item_location.s_region LIKE '" . $location . "' OR " . DB_TABLE_PREFIX . "t_item_location.s_country LIKE '" . $location . "' OR " . DB_TABLE_PREFIX . "t_item_location.s_zip LIKE '" . $location . "' OR " . DB_TABLE_PREFIX . "t_item_location.s_address LIKE '" . $location . "')");
        } else if($shortcutFilter == 'oPublishDate') {
          $publish = Search::newInstance()->dao->escapeStr(str_replace('*', '%', $v));
          if(strpos($publish, '%') === false) {
            $publish = '%' . $publish . '%';
          }
          $this->mSearch->addItemConditions(DB_TABLE_PREFIX . "t_item.dt_pub_date LIKE '" . $publish . "'");
        } else if($shortcutFilter == 'oExpirationDate') {
          $exp = Search::newInstance()->dao->escapeStr(str_replace('*', '%', $v));
          if(strpos($exp, '%') === false) {
            $exp = '%' . $exp . '%';
          }
          $this->mSearch->addItemConditions(DB_TABLE_PREFIX . "t_item.dt_expiration LIKE '" . $exp . "'");
        } else {
          $this->mSearch->addPattern($v);
        }

        $this->withFilters = true;
      }

      // filters
      if($k === 'userId' && $v != '') {
        $this->mSearch->fromUser($v);
        $this->withFilters = true;
        $withUserId = true;
      }
      if($k === 'itemId' && $v != '') {
        $this->mSearch->addItemId($v);
        $this->withFilters = true;
      }
      if($k === 'countryId' && $v != '') {
        $this->mSearch->addCountry($v);
        $this->withFilters = true;
      }
      if($k === 'regionId' && $v != '') {
        $this->mSearch->addRegion($v);
        $this->withFilters = true;
      }
      if($k === 'cityId' && $v != '') {
        $this->mSearch->addCity($v);
        $this->withFilters = true;
      }
      if($k === 'country' && $v != '') {
        $this->mSearch->addCountry($v);
        $this->withFilters = true;
      }
      if($k === 'region' && $v != '') {
        $this->mSearch->addRegion($v);
        $this->withFilters = true;
      }

      if($k === 'city' && $v != '') {
        $this->mSearch->addCity($v);
        $this->withFilters = true;
      }
      if($k === 'catId' && $v != '') {
        $this->mSearch->addCategory($v);
        $this->withFilters = true;
      }
      if($k === 'b_premium' && $v != '') {
        $this->mSearch->addItemConditions(DB_TABLE_PREFIX.'t_item.b_premium = '.$v);
        $this->withFilters = true;
      }
      if($k === 'b_active' && $v != '') {
        $this->mSearch->addItemConditions(DB_TABLE_PREFIX.'t_item.b_active = '.$v);
        $this->withFilters = true;
      }
      if($k === 'b_enabled' && $v != '') {
        $this->mSearch->addItemConditions(DB_TABLE_PREFIX.'t_item.b_enabled = '.$v);
        $this->withFilters = true;
      }
      if($k === 'b_spam' && $v != '') {
        $this->mSearch->addItemConditions(DB_TABLE_PREFIX.'t_item.b_spam = '.$v);
        $this->withFilters = true;
      }
      if($k === 'contactName' && $v != '') {
        $contactName = Search::newInstance()->dao->escapeStr(str_replace('*', '%', $v));
        if(strpos($contactName, '%') === false) {
          $contactName = '%' . $contactName . '%';
        }
        $this->mSearch->addItemConditions(DB_TABLE_PREFIX . "t_item.s_contact_name LIKE '" . $contactName . "'");
        $this->withFilters = true;
      }

      if($k === 'user' && $v != '') {
        if($shortcutFilter == 'oContactName') {
          $contactName = Search::newInstance()->dao->escapeStr(str_replace('*', '%', $v));
          if(strpos($contactName, '%') === false) {
            $contactName = '%' . $contactName . '%';
          }
          $this->mSearch->addItemConditions(DB_TABLE_PREFIX . "t_item.s_contact_name LIKE '" . $contactName . "'");
          $this->withFilters = true;
        } else {
          $no_user_email = $v;
        }
      }
    }

    // add no registered user email if userId == '' and $no_user_email != ''
    if($no_user_email != '' && !$withUserId) {
      $this->mSearch->addContactEmail($no_user_email);
      $this->withFilters = true;
    }

    // set start and limit using iPage param
    $start = ($this->iPage - 1) * $params['iDisplayLength'];

    $this->start = (int) $start;
    $this->limit = (int)$params['iDisplayLength'];
    $this->mSearch->limit($this->start, $this->limit);

    if($withSort) {
      $sortData = $this->resolveSort($params);
      $sortData = osc_apply_filter('admin_items_sort_resolved', $sortData, $this, $params);
      Params::setParam('sort', $sortData['key']);
      Params::setParam('direction', $sortData['direction']);
      $this->mSearch->order($sortData['column'], $sortData['direction']);
    }
  }

  private function registerMainSortColumns() {
    $adminLocale = preg_replace('/[^a-zA-Z0-9_\-]/', '', osc_current_admin_locale());
    $titleCurrentLocale = '(SELECT td.s_title FROM ' . DB_TABLE_PREFIX . 't_item_description td WHERE td.fk_i_item_id = ' . DB_TABLE_PREFIX . 't_item.pk_i_id AND td.fk_c_locale_code = \'' . $adminLocale . '\' LIMIT 1)';
    $titleAnyLocale = '(SELECT td2.s_title FROM ' . DB_TABLE_PREFIX . 't_item_description td2 WHERE td2.fk_i_item_id = ' . DB_TABLE_PREFIX . 't_item.pk_i_id LIMIT 1)';
    $titleColumn = 'COALESCE(' . $titleCurrentLocale . ', ' . $titleAnyLocale . ')';
    $categoryCurrentLocale = '(SELECT cd.s_name FROM ' . DB_TABLE_PREFIX . 't_category_description cd WHERE cd.fk_i_category_id = ' . DB_TABLE_PREFIX . 't_item.fk_i_category_id AND cd.fk_c_locale_code = \'' . $adminLocale . '\' LIMIT 1)';
    $categoryAnyLocale = '(SELECT cd2.s_name FROM ' . DB_TABLE_PREFIX . 't_category_description cd2 WHERE cd2.fk_i_category_id = ' . DB_TABLE_PREFIX . 't_item.fk_i_category_id LIMIT 1)';
    $categoryColumn = 'COALESCE(' . $categoryCurrentLocale . ', ' . $categoryAnyLocale . ')';

    $sortColumns = array(
      'title' => array(
        'column' => $titleColumn,
        'coalesce' => false
      ),
      'user' => array(
        'column' => DB_TABLE_PREFIX . 't_item.s_contact_name',
        'coalesce' => true
      ),
      'category' => array(
        'column' => $categoryColumn,
        'coalesce' => false
      ),
      'location' => array(
        'column' => 'CONCAT_WS(\', \', COALESCE(' . DB_TABLE_PREFIX . 't_item_location.s_city, \'\'), COALESCE(' . DB_TABLE_PREFIX . 't_item_location.s_region, \'\'), COALESCE(' . DB_TABLE_PREFIX . 't_item_location.s_country, \'\'))',
        'coalesce' => false
      ),
      'date' => array(
        'column' => DB_TABLE_PREFIX . 't_item.dt_pub_date',
        'coalesce' => false
      ),
      'expiration' => array(
        'column' => DB_TABLE_PREFIX . 't_item.dt_expiration',
        'coalesce' => false
      ),
      'views' => array(
        'column' => '(SELECT COALESCE(SUM(st.i_num_views), 0) FROM ' . DB_TABLE_PREFIX . 't_item_stats st WHERE st.fk_i_item_id = ' . DB_TABLE_PREFIX . 't_item.pk_i_id)',
        'coalesce' => false
      )
    );

    $sortColumns = osc_apply_filter('admin_items_sort_columns', $sortColumns, $this);

    // List of sortable columns in datatable
    foreach($sortColumns as $key => $data) {
      if(is_array($data) && isset($data['column'])) {
        $this->addSortColumn($key, $data['column'], (isset($data['coalesce']) ? $data['coalesce'] : false));
      } else if(is_string($data)) {
        $this->addSortColumn($key, $data);
      }
    }
  }

  private function registerMainSourceColumns() {
    $sources = array(
      'title' => 's_title',
      'user' => 's_contact_name',
      'category' => 's_name',
      'location' => 's_country|s_region|s_city',
      'date' => 'dt_pub_date',
      'expiration' => 'dt_expiration',
      'views' => 'i_num_views'
    );
    $sources = osc_apply_filter('admin_items_source_columns', $sources, $this);

    // Source columns used by data-source-col in table header
    foreach($sources as $key => $source) {
      $this->addSourceColumn($key, $source);
    }
  }

  /**
   * @return bool
   */
  public function withFilters() {
    return $this->withFilters;
  }

  /**
   * @return array
   */
  public function rawRows() {
    return $this->rawRows;
  }

  /**
   * @param $class
   * @param $rawRow
   * @param $row
   *
   * @return array
   */
  public function row_class($class, $rawRow, $row) {
    View::newInstance()->_exportVariableToView('item', $rawRow);
    $status = $this->get_row_status();
    $class[] = $status['class'];
    View::newInstance()->_erase('item');
    return $class;
  }

  /**
   * Get the status of the row. There are five status:
   *   - spam
   *   - blocked
   *   - inactive
   *   - premium
   *   - active
   *   - expired
   *
   * @since 3.2 -> 3.4.x
   *
   * @return array Array with the class and text of the status of the listing in this row. Example:
   *   array(
   *     'class' => '',
   *     'text'  => ''
   *   )
   */
  private function get_row_status() {
    $data = array('class' => '', 'text' => '');

    if(osc_item_is_spam()) {
      $data = array(
        'class' => 'status-spam',
        'text'  => __('Spam')
      );

    } else if(!osc_item_is_enabled()) {
      $data = array(
        'class' => 'status-blocked',
        'text'  => __('Blocked')
      );

    } else if(osc_item_is_expired()) {
      $data = array(
        'class' => 'status-expired',
        'text'  => __('Expired')
      );

    } else if(!osc_item_is_active()) {
      $data = array(
        'class' => 'status-inactive',
        'text'  => __('Inactive')
      );

    } else if(osc_item_is_premium()) {
      $data = array(
        'class' => 'status-premium',
        'text'  => __('Premium')
      );
    } else {
      $data = array(
        'class' => 'status-active',
        'text'  => __('Active')
      );
    }

    return osc_apply_filter('item_table_row_status', $data);
  }

  /**
   * Get the location separated by commas of a row
   *
   * @since 3.2
   *
   * @return string Location separated by commas
   */
  private function get_row_location($item) {
    $location = array();
    if(isset($item['s_city']) && trim((string)$item['s_city']) !== '') {
      $params = array('page' => 'items', 'city' => $item['s_city']);
      if(isset($item['fk_i_city_id']) && (int)$item['fk_i_city_id'] > 0) {
        $params = array('page' => 'items', 'cityId' => (int)$item['fk_i_city_id']);
      }
      $location[] = '<a href="' . osc_esc_html($this->build_item_filter_url($params)) . '">' . osc_esc_html($item['s_city']) . '</a>';
    }

    if(isset($item['s_region']) && trim((string)$item['s_region']) !== '') {
      $params = array('page' => 'items', 'region' => $item['s_region']);
      if(isset($item['fk_i_region_id']) && (int)$item['fk_i_region_id'] > 0) {
        $params = array('page' => 'items', 'regionId' => (int)$item['fk_i_region_id']);
      }
      $location[] = '<a href="' . osc_esc_html($this->build_item_filter_url($params)) . '">' . osc_esc_html($item['s_region']) . '</a>';
    }

    if(isset($item['s_country']) && trim((string)$item['s_country']) !== '') {
      $params = array('page' => 'items', 'country' => $item['s_country']);
      if(isset($item['fk_c_country_code']) && trim((string)$item['fk_c_country_code']) !== '') {
        $params = array('page' => 'items', 'countryId' => $item['fk_c_country_code']);
      }
      $location[] = '<a href="' . osc_esc_html($this->build_item_filter_url($params)) . '">' . osc_esc_html($item['s_country']) . '</a>';
    }

    return implode(', ', $location);
  }

  private function get_row_category($item) {
    $name = (isset($item['s_category_name']) ? (string)$item['s_category_name'] : '');
    if($name == '') {
      return '';
    }

    if(isset($item['fk_i_category_id']) && (int)$item['fk_i_category_id'] > 0) {
      $url = $this->build_item_filter_url(array('page' => 'items', 'catId' => (int)$item['fk_i_category_id']));
      return '<a href="' . osc_esc_html($url) . '">' . osc_esc_html($name) . '</a>';
    }

    return osc_esc_html($name);
  }

  private function build_item_filter_url($params = array()) {
    $url = osc_admin_base_url(true);
    if(!is_array($params) || count($params) == 0) {
      return $url;
    }

    return $url . '?' . http_build_query($params);
  }
}
