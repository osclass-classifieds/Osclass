<?php
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
 * Helper functions for CustomDataTable (plugin admin lists).
 *
 * @package Osclass
 * @subpackage Helpers
 */

/*
USAGE — full plugin admin list (controller + view)
==================================================

CONTROLLER (list action):

  require_once osc_lib_path() . 'osclass/helpers/hCustomDataTable.php';

  osc_custom_datatable_hook_admin_head(array('datatable_id' => 'my_plugin_list'));

  $tableOptions = array(
    'id' => 'my_plugin_list',
    'default_sort' => array('key' => 'date', 'direction' => 'desc'),
    'status_columns' => true,
    'bulk_actions' => array('input_name' => 'id[]', 'value_key' => 'pk_i_id'),
    'columns' => array(
      array('id' => 'title', 'label' => __('Title'), 'sortable' => true, 'sort_column' => 's_title', 'source' => 's_title', 'priority' => 4),
      array('id' => 'date', 'label' => __('Date'), 'sortable' => true, 'sort_column' => 'dt_date', 'source' => 'dt_date', 'priority' => 5),
    ),
    'callbacks' => array(
      'actions_column' => 'title',
      'fetch' => function($table, $list) {
        return MyPluginModel::newInstance()->search(
          $list['start'],
          $list['limit'],
          $list['order_by']['column_name'],
          $list['order_by']['type'],
          $list['search']
        );
      },
      'process_row' => function($raw, $table) {
        return array_merge(
          osc_custom_datatable_status_cells(__('Active')),
          array(
            'title' => osc_esc_html($raw['s_title']),
            'date' => osc_format_date($raw['dt_date']),
          )
        );
      },
      'row_actions' => function($raw, $table) {
        return array('<a href="#">' . __('Edit') . '</a>');
      },
    ),
  );

  $bulkOptions = osc_custom_datatable_bulk_options(array(
    'delete' => __('Delete'),
  ), array('confirm' => __('Are you sure you want to delete selected records?')));

  $result = osc_custom_datatable_admin_prepare(array(
    'options' => $tableOptions,
    'admin' => array(
      'cookie_key' => 'my_plugin_list_iDisplayLength',
      'default_sort' => 'date',
      'default_direction' => 'desc',
    ),
    'bulk_options' => $bulkOptions,
  ));

  osc_custom_datatable_redirect_empty_page($result['aData'], $this);

  if(Params::getParam('action') != '' && Params::getParam('action') != 'list') {
    osc_run_hook('my_plugin_bulk_' . Params::getParam('action'), Params::getParam('id'));
    $this->redirectTo(osc_route_admin_url('my_plugin_list'));
  }

  osc_custom_datatable_export_admin_view($this, $result);
  $this->doView('admin/list.php');


VIEW (admin/list.php) — toolbar, search, bulk, table, pagination:

  $aData = __get('aData');
  $aRawRows = __get('aRawRows');

  <div class="table-toolbar float-right">
    <?php osc_custom_datatable_toolbar_per_page(array('sizes' => array(10, 25, 50, 100))); ?>
    <?php osc_custom_datatable_toolbar_search(array(
      'hidden_params' => array('page' => 'plugins', 'action' => 'list'),
      'placeholder' => __('Search records'),
      'with_filters' => __get('withFilters'),
      'reset_url' => osc_admin_base_url(true) . '?page=plugins&action=list',
    )); ?>
  </div>

  <?php osc_custom_datatable_print_form_open(array('page' => 'plugins', 'hidden' => array('action' => 'list'))); ?>
  <?php osc_custom_datatable_print_bulk_bar(); ?>
  <?php osc_custom_datatable_print_table($aData, $aRawRows); ?>
  </form>

  <?php osc_custom_datatable_print_pagination_block($aData); ?>

BULK POST handler (controller, before prepare):

  if(Params::getParam('action') != '' && Params::getParam('action') != 'list') {
    $ids = Params::getParam('id');
    osc_run_hook('my_plugin_bulk_' . Params::getParam('action'), $ids);
    osc_add_flash_ok_message(__('Bulk action completed'));
    $this->redirectTo($listUrl);
  }

Fetch callback must return:
  array('rows' => array(), 'total' => (int), 'total_filtered' => (int));

$list keys in fetch: start, limit, search, sort, order_by, iPage, with_filters.
*/


/**
 * Load DataTable base class and CustomDataTable.
 */
function osc_custom_datatable_require() {
  if(!class_exists('DataTable', false)) {
    require_once osc_lib_path() . 'osclass/classes/datatables/DataTable.php';
  }

  if(!class_exists('CustomDataTable', false)) {
    require_once osc_lib_path() . 'osclass/classes/datatables/CustomDataTable.php';
  }
}


/**
 * Create a configured CustomDataTable instance.
 *
 * @param array $options  See CustomDataTable class docblock
 *
 * @return CustomDataTable
 */
function osc_custom_datatable_create($options = array()) {
  osc_custom_datatable_require();
  return new CustomDataTable($options);
}


/**
 * Run datatable and return payload array from table()->
 *
 * @param array $options  CustomDataTable configuration
 * @param array $params   Request params (default Params::getParamsAsArray())
 *
 * @return array
 */
function osc_custom_datatable_run($options = array(), $params = null) {
  if($params === null) {
    $params = Params::getParamsAsArray();
  }

  $table = osc_custom_datatable_create($options);
  return $table->table((is_array($params) ? $params : array()));
}


/**
 * Normalize datatable id for hooks and filters.
 *
 * @param string $id
 *
 * @return string
 */
function osc_custom_datatable_filter_key($id) {
  $id = strtolower(trim((string)$id));
  return preg_replace('/[^a-z0-9_]+/', '_', $id);
}


/**
 * Build sortable column header HTML (same as core datatables).
 *
 * @param string $columnId
 * @param string $label
 * @param array  $options {
 *   @type string $url_base   Sort URL without sort/direction (auto if empty)
 *   @type string $sort       Current sort key
 *   @type string $direction  Current direction
 * }
 *
 * @return string
 */
function osc_custom_datatable_sort_header($columnId, $label, $options = array()) {
  $options = (is_array($options) ? $options : array());
  $sort = (isset($options['sort']) ? $options['sort'] : Params::getParam('sort'));
  $direction = (isset($options['direction']) ? $options['direction'] : Params::getParam('direction'));

  if(!isset($options['url_base']) || $options['url_base'] == '') {
    Rewrite::newInstance()->init();
    $options['url_base'] = preg_replace('|&direction=([^&]*)|', '', preg_replace('|&sort=([^&]*)|', '', osc_base_url() . Rewrite::newInstance()->get_raw_request_uri()));
  }

  $table = osc_custom_datatable_create();
  $args = $table->buildSortArgs($columnId, $sort, $direction);

  return '<a href="' . osc_esc_html($options['url_base'] . $args) . '">' . $label . '</a>';
}


/**
 * Bulk action checkbox cell HTML.
 *
 * @param mixed $value   Checkbox value (row primary key)
 * @param array $options {
 *   @type string $input_name  name attribute (default id[])
 *   @type string $input_id    optional id attribute
 * }
 *
 * @return string
 */
function osc_custom_datatable_bulk_checkbox($value, $options = array()) {
  $options = (is_array($options) ? $options : array());
  $name = (isset($options['input_name']) ? (string)$options['input_name'] : 'id[]');
  $idAttr = (isset($options['input_id']) ? ' id="' . osc_esc_html($options['input_id']) . '"' : '');

  return '<input type="checkbox" name="' . osc_esc_html($name) . '"' . $idAttr . ' value="' . osc_esc_html($value) . '" />';
}


/**
 * Status column pair for datatable rows.
 *
 * @param string $statusLabel  Visible status text
 * @param array  $options {
 *   @type string $border  status-border cell content (default empty)
 * }
 *
 * @return array  Keys status-border and status
 */
function osc_custom_datatable_status_cells($statusLabel, $options = array()) {
  $options = (is_array($options) ? $options : array());
  $border = (isset($options['border']) ? $options['border'] : '');

  return array(
    'status-border' => $border,
    'status' => $statusLabel
  );
}


/**
 * Prepare common admin list params: per-page cookie, default sort, page index.
 *
 * Use in plugin admin controller before calling CustomDataTable::table().
 *
 * @param array $options {
 *   @type string $cookie_key         Cookie name for iDisplayLength (default listing_iDisplayLength)
 *   @type int    $default_per_page   Default rows per page (25)
 *   @type string $default_sort       Default sort column key
 *   @type string $default_direction  asc|desc
 * }
 *
 * @return array Params ready for table()
 */
function osc_custom_datatable_admin_params($options = array()) {
  $options = (is_array($options) ? $options : array());
  $cookieKey = (isset($options['cookie_key']) ? $options['cookie_key'] : 'listing_iDisplayLength');
  $defaultPerPage = (isset($options['default_per_page']) ? (int)$options['default_per_page'] : 25);

  if(Params::getParam('iDisplayLength') != '') {
    Cookie::newInstance()->push($cookieKey, Params::getParam('iDisplayLength'));
    Cookie::newInstance()->set();
  } else if(Cookie::newInstance()->get_value($cookieKey) != '') {
    Params::setParam('iDisplayLength', Cookie::newInstance()->get_value($cookieKey));
  } else {
    Params::setParam('iDisplayLength', $defaultPerPage);
  }

  if(isset($options['default_sort']) && Params::getParam('sort') == '') {
    Params::setParam('sort', $options['default_sort']);
  }

  if(isset($options['default_direction']) && Params::getParam('direction') == '') {
    Params::setParam('direction', $options['default_direction']);
  }

  $page = (int)Params::getParam('iPage');
  if($page == 0) {
    $page = 1;
  }
  Params::setParam('iPage', $page);

  return Params::getParamsAsArray();
}


/**
 * Run datatable and return table instance + view data for admin templates.
 *
 * Example in plugin:
 *   $result = osc_custom_datatable_admin_prepare(array(
 *     'options' => $tableOptions,
 *     'admin' => array('default_sort' => 'date', 'default_direction' => 'desc'),
 *   ));
 *   $this->_exportVariableToView('aData', $result['aData']);
 *   $this->_exportVariableToView('aRawRows', $result['aRawRows']);
 *
 * @param array $options {
 *   @type array $options  CustomDataTable configuration (required)
 *   @type array $admin    Options for osc_custom_datatable_admin_params()
 *   @type array $params   Override request params
 * }
 *
 * @type array $bulk_options  Bulk dropdown options for osc_print_bulk_actions()
 *
 * @return array  keys: table, aData, aRawRows, withFilters, iDisplayLength, datatable_id, bulk_options
 */
function osc_custom_datatable_admin_prepare($options = array()) {
  $options = (is_array($options) ? $options : array());
  $tableOptions = (isset($options['options']) && is_array($options['options']) ? $options['options'] : array());
  $adminOptions = (isset($options['admin']) && is_array($options['admin']) ? $options['admin'] : array());
  $bulkOptions = (isset($options['bulk_options']) && is_array($options['bulk_options']) ? $options['bulk_options'] : array());

  $params = null;
  if(isset($options['params']) && is_array($options['params'])) {
    $params = $options['params'];
  } else {
    $params = osc_custom_datatable_admin_params($adminOptions);
  }

  $table = osc_custom_datatable_create($tableOptions);
  $aData = $table->table($params);
  $config = $table->getConfig();
  $datatableId = (isset($config['id']) ? osc_custom_datatable_filter_key($config['id']) : 'custom');

  return array(
    'table' => $table,
    'aData' => $aData,
    'aRawRows' => $table->rawRows(),
    'withFilters' => $table->withFilters(),
    'iDisplayLength' => (isset($aData['iDisplayLength']) ? $aData['iDisplayLength'] : Params::getParam('iDisplayLength')),
    'datatable_id' => $datatableId,
    'datatable_row_class_filter' => 'datatable_' . $datatableId . '_class',
    'bulk_options' => $bulkOptions
  );
}


/**
 * Export standard view variables to admin controller.
 *
 * @param object $controller  Admin controller ($this)
 * @param array  $result      Result from osc_custom_datatable_admin_prepare()
 * @param array  $options {
 *   @type array $extra  Additional key => value pairs for _exportVariableToView
 * }
 */
function osc_custom_datatable_export_admin_view($controller, $result, $options = array()) {
  $options = (is_array($options) ? $options : array());
  $result = (is_array($result) ? $result : array());

  $controller->_exportVariableToView('aData', (isset($result['aData']) ? $result['aData'] : array()));
  $controller->_exportVariableToView('aRawRows', (isset($result['aRawRows']) ? $result['aRawRows'] : array()));
  $controller->_exportVariableToView('withFilters', (isset($result['withFilters']) ? $result['withFilters'] : false));
  $controller->_exportVariableToView('iDisplayLength', (isset($result['iDisplayLength']) ? $result['iDisplayLength'] : 25));
  $controller->_exportVariableToView('datatable_id', (isset($result['datatable_id']) ? $result['datatable_id'] : 'custom'));
  $controller->_exportVariableToView('datatable_row_class_filter', (isset($result['datatable_row_class_filter']) ? $result['datatable_row_class_filter'] : 'datatable_custom_class'));
  $controller->_exportVariableToView('bulk_options', (isset($result['bulk_options']) ? $result['bulk_options'] : array()));

  if(isset($options['extra']) && is_array($options['extra'])) {
    foreach($options['extra'] as $k => $v) {
      $controller->_exportVariableToView($k, $v);
    }
  }
}


/**
 * Build bulk action dropdown options for osc_print_bulk_actions().
 *
 * @param array $actions  action_value => label, e.g. array('delete' => __('Delete'))
 * @param array $options {
 *   @type string $confirm  Default data-dialog-content for all actions (sprintf %s with action label if needed)
 *   @type array  $dialogs  Per-action dialog text: action_value => string
 * }
 *
 * @return array
 */
function osc_custom_datatable_bulk_options($actions, $options = array()) {
  $options = (is_array($options) ? $options : array());
  $actions = (is_array($actions) ? $actions : array());
  $defaultConfirm = (isset($options['confirm']) ? $options['confirm'] : '');
  $dialogs = (isset($options['dialogs']) && is_array($options['dialogs']) ? $options['dialogs'] : array());

  $bulk = array(
    array('value' => '', 'data-dialog-content' => '', 'label' => __('Bulk actions'))
  );

  foreach($actions as $value => $label) {
    $dialog = (isset($dialogs[$value]) ? $dialogs[$value] : $defaultConfirm);
    if($dialog == '' && $label != '') {
      $dialog = sprintf(__('Are you sure you want to %s the selected items?'), strtolower($label));
    }

    $bulk[] = array(
      'value' => $value,
      'data-dialog-content' => $dialog,
      'label' => $label
    );
  }

  return $bulk;
}


/**
 * Return admin_header JS for check-all and bulk confirmation dialog.
 *
 * @param array $options {
 *   @type string $form_id  Default datatablesForm
 * }
 *
 * @return string
 */
function osc_custom_datatable_admin_head_scripts($options = array()) {
  $options = (is_array($options) ? $options : array());
  $formId = (isset($options['form_id']) ? $options['form_id'] : 'datatablesForm');

  ob_start();
  ?>
  <script type="text/javascript">
  $(document).ready(function(){
    $("#check_all").change(function(){
      var isChecked = $(this).prop("checked");
      $('.col-bulkactions input').each(function() {
        this.checked = (isChecked == 1);
      });
    });

    $("#dialog-bulk-actions").dialog({
      autoOpen: false,
      modal: true
    });

    $("#bulk-actions-submit").click(function() {
      $("#<?php echo osc_esc_js($formId); ?>").submit();
    });

    $("#bulk-actions-cancel").click(function() {
      $("#<?php echo osc_esc_js($formId); ?>").attr('data-dialog-open', 'false');
      $('#dialog-bulk-actions').dialog('close');
    });

    $("#<?php echo osc_esc_js($formId); ?>").submit(function() {
      if($("#bulk_actions option:selected").val() == "") {
        return false;
      }

      if($("#<?php echo osc_esc_js($formId); ?>").attr('data-dialog-open') == "true") {
        return true;
      }

      $("#dialog-bulk-actions .form-row").html($("#bulk_actions option:selected").attr('data-dialog-content'));
      $("#bulk-actions-submit").html($("#bulk_actions option:selected").text());
      $("#<?php echo osc_esc_js($formId); ?>").attr('data-dialog-open', 'true');
      $("#dialog-bulk-actions").dialog('open');
      return false;
    });
  });
  </script>
  <div id="dialog-bulk-actions" class="has-form-actions hide">
    <div class="form-horizontal">
      <div class="form-row"></div>
      <div class="form-actions">
        <div class="wrapper">
          <a id="bulk-actions-cancel" href="#" class="btn btn-red"><?php _e('Cancel'); ?></a>
          <a id="bulk-actions-submit" href="#" class="btn btn-submit"><?php _e('Apply'); ?></a>
        </div>
      </div>
    </div>
  </div>
  <?php
  return ob_get_clean();
}


/**
 * Register admin_header hook with datatable JS (call once from plugin bootstrap or controller).
 *
 * @param array $options  Passed to osc_custom_datatable_admin_head_scripts()
 */
function osc_custom_datatable_hook_admin_head($options = array()) {
  $options = (is_array($options) ? $options : array());
  $priority = (isset($options['priority']) ? (int)$options['priority'] : 10);
  unset($options['priority']);

  osc_add_hook('admin_header', function() use ($options) {
    echo osc_custom_datatable_admin_head_scripts($options);
  }, $priority);
}


/**
 * Hidden GET fields for toolbar forms (preserves sort, page, filters).
 *
 * @param array $exclude  Param names to skip
 *
 * @return string
 */
function osc_custom_datatable_hidden_get_params($exclude = array()) {
  $exclude = (is_array($exclude) ? $exclude : array());
  $html = '';

  foreach(Params::getParamsAsArray('get') as $key => $value) {
    if(in_array($key, $exclude, true)) {
      continue;
    }

    if(is_array($value)) {
      continue;
    }

    $html .= '<input type="hidden" name="' . osc_esc_html(strip_tags($key)) . '" value="' . osc_esc_html(strip_tags((string)$value)) . '" />' . PHP_EOL;
  }

  return $html;
}


/**
 * Per-page selector toolbar (top). Submits GET and keeps other query params.
 *
 * @param array $options {
 *   @type string   $action_url
 *   @type array    $sizes           Default 10,25,50,100
 *   @type callable $size_label      function($size) return label
 *   @type array    $exclude_params  Hidden field exclusions
 * }
 */
function osc_custom_datatable_toolbar_per_page($options = array()) {
  $options = (is_array($options) ? $options : array());
  $actionUrl = (isset($options['action_url']) ? $options['action_url'] : osc_admin_base_url(true));
  $sizes = (isset($options['sizes']) && is_array($options['sizes']) ? $options['sizes'] : array(10, 25, 50, 100));
  $exclude = (isset($options['exclude_params']) && is_array($options['exclude_params']) ? $options['exclude_params'] : array('iDisplayLength'));
  $current = (int)Params::getParam('iDisplayLength');
  if($current <= 0) {
    $current = 25;
  }

  echo '<form method="get" action="' . osc_esc_html($actionUrl) . '" class="inline nocsrf">' . PHP_EOL;
  echo osc_custom_datatable_hidden_get_params($exclude);

  echo '<select name="iDisplayLength" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();">' . PHP_EOL;
  foreach($sizes as $size) {
    $size = (int)$size;
    if($size <= 0) {
      continue;
    }

    $label = (isset($options['size_label']) && is_callable($options['size_label']) ? call_user_func($options['size_label'], $size) : sprintf(__('%d records'), $size));
    echo '<option value="' . $size . '"' . ($current == $size ? ' selected' : '') . '>' . osc_esc_html($label) . '</option>' . PHP_EOL;
  }
  echo '</select></form>' . PHP_EOL;
}


/**
 * Search toolbar form (GET). Uses sSearch param read by CustomDataTable.
 *
 * @param array $options {
 *   @type array  $hidden_params   Fixed hidden fields page, file, route, ...
 *   @type array  $exclude_hidden  Exclude from auto hidden GET params
 *   @type bool   $with_filters
 *   @type string $reset_url
 *   @type string $placeholder
 *   @type string $action_url
 * }
 */
function osc_custom_datatable_toolbar_search($options = array()) {
  $options = (is_array($options) ? $options : array());
  $actionUrl = (isset($options['action_url']) ? $options['action_url'] : osc_admin_base_url(true));
  $exclude = (isset($options['exclude_hidden']) && is_array($options['exclude_hidden']) ? $options['exclude_hidden'] : array('page', 'sSearch', 'action', 'file'));
  $withFilters = (isset($options['with_filters']) ? (bool)$options['with_filters'] : (bool)__get('withFilters'));
  $placeholder = (isset($options['placeholder']) ? $options['placeholder'] : __('Search'));
  $searchValue = Params::getParam('sSearch');

  echo '<form method="get" action="' . osc_esc_html($actionUrl) . '" class="inline nocsrf">' . PHP_EOL;

  if(isset($options['hidden_params']) && is_array($options['hidden_params'])) {
    foreach($options['hidden_params'] as $key => $value) {
      echo '<input type="hidden" name="' . osc_esc_html(strip_tags($key)) . '" value="' . osc_esc_html(strip_tags((string)$value)) . '" />' . PHP_EOL;
    }
  }

  echo osc_custom_datatable_hidden_get_params($exclude);

  if($withFilters && isset($options['reset_url']) && $options['reset_url'] != '') {
    echo '<a href="' . osc_esc_html($options['reset_url']) . '" class="btn">' . __('Reset search') . '</a>' . PHP_EOL;
  }

  echo '<input name="sSearch" type="text" class="input-text input-actions" value="' . osc_esc_html($searchValue) . '" placeholder="' . osc_esc_html($placeholder) . '" />' . PHP_EOL;
  echo '<input type="submit" class="btn submit-right" value="' . osc_esc_html(__('Find')) . '" />' . PHP_EOL;
  echo '</form>' . PHP_EOL;
}


function osc_custom_datatable_echo_showing_results($aData = null) {
  if($aData === null) {
    $aData = __get('aData');
  }

  if(!is_array($aData)) {
    return;
  }

  $perPage = (int)(isset($aData['iDisplayLength']) ? $aData['iDisplayLength'] : Params::getParam('iDisplayLength'));
  if($perPage <= 0) {
    $perPage = 25;
  }

  $page = (int)Params::getParam('iPage');
  if($page <= 0) {
    $page = 1;
  }

  $rows = (isset($aData['aRows']) && is_array($aData['aRows']) ? $aData['aRows'] : array());
  $from = (($page - 1) * $perPage) + 1;
  $to = (($page - 1) * $perPage) + count($rows);
  $filtered = (int)(isset($aData['iTotalDisplayRecords']) ? $aData['iTotalDisplayRecords'] : 0);
  $total = (int)(isset($aData['iTotalRecords']) ? $aData['iTotalRecords'] : 0);

  echo '<ul class="showing-results"><li><span>' . osc_pagination_showing($from, $to, $filtered, $total) . '</span></li></ul>';
}


/**
 * Pagination block: showing X–Y of Z + osc_show_pagination_admin().
 *
 * @param array $aData   Datatable payload (getData)
 * @param array $options {
 *   @type bool  $bottom_per_page  Print bottom per-page selector (default true)
 *   @type array $per_page         Options for osc_custom_datatable_toolbar_per_page()
 * }
 */
function osc_custom_datatable_print_pagination_block($aData, $options = array()) {
  $options = (is_array($options) ? $options : array());
  $aData = (is_array($aData) ? $aData : array());

  osc_add_hook('before_show_pagination_admin', 'osc_custom_datatable_echo_showing_results', 5);
  osc_show_pagination_admin($aData);

  if(!isset($options['bottom_per_page']) || $options['bottom_per_page'] !== false) {
    echo '<div class="display-select-bottom">' . PHP_EOL;
    osc_custom_datatable_toolbar_per_page(isset($options['per_page']) && is_array($options['per_page']) ? $options['per_page'] : array());
    echo '</div>' . PHP_EOL;
  }
}


/**
 * Bulk actions bar (dropdown + apply). Place inside #datatablesForm.
 *
 * @param array $options {
 *   @type array  $bulk_options  From controller
 *   @type string $select_id
 *   @type string $action_name
 * }
 */
function osc_custom_datatable_print_bulk_bar($options = array()) {
  $options = (is_array($options) ? $options : array());
  $bulkOptions = (isset($options['bulk_options']) && is_array($options['bulk_options']) ? $options['bulk_options'] : __get('bulk_options'));

  if(!is_array($bulkOptions) || count($bulkOptions) == 0) {
    return;
  }

  echo '<div id="bulk-actions"><label>';
  osc_print_bulk_actions(
    (isset($options['select_id']) ? $options['select_id'] : 'bulk_actions'),
    (isset($options['action_name']) ? $options['action_name'] : 'action'),
    $bulkOptions,
    (isset($options['select_class']) ? $options['select_class'] : 'select-box-extra')
  );
  echo '<input type="submit" id="' . osc_esc_html((isset($options['submit_id']) ? $options['submit_id'] : 'bulk_apply')) . '" class="btn" value="' . osc_esc_html(__('Apply')) . '" />';
  echo '</label></div>' . PHP_EOL;
}


/**
 * Print datatable thead + tbody. Place inside #datatablesForm after bulk bar.
 *
 * @param array $aData
 * @param array $aRawRows
 * @param array $options
 */
function osc_custom_datatable_print_table($aData, $aRawRows = null, $options = array()) {
  $options = (is_array($options) ? $options : array());
  $aData = (is_array($aData) ? $aData : array());

  if($aRawRows === null) {
    $aRawRows = __get('aRawRows');
  }

  $aRawRows = (is_array($aRawRows) ? $aRawRows : array());
  $columns = (isset($aData['aColumns']) ? $aData['aColumns'] : array());
  $columnSources = (isset($aData['aColumnSources']) ? $aData['aColumnSources'] : array());
  $sortableColumns = (isset($aData['aSortableColumns']) ? $aData['aSortableColumns'] : array());
  $rows = (isset($aData['aRows']) ? $aData['aRows'] : array());
  $sort = Params::getParam('sort');
  $direction = Params::getParam('direction');
  $rowClassFilter = (isset($options['row_class_filter']) ? $options['row_class_filter'] : __get('datatable_row_class_filter'));

  if($rowClassFilter == '') {
    $rowClassFilter = 'datatable_custom_class';
  }

  echo '<div class="table-contains-actions"><table class="table" cellpadding="0" cellspacing="0"><thead><tr>';
  foreach($columns as $k => $v) {
    $sourceCol = (isset($columnSources[$k]) ? $columnSources[$k] : '');
    $isSortable = ((in_array($k, $sortableColumns, true) || strpos((string)$v, 'sort=') !== false) ? 'is-sortable' : '');
    $sortClass = ($sort == $k ? ($direction == 'desc' ? 'sort-desc' : 'sort-asc') : '');
    echo '<th class="col-' . osc_esc_html($k) . ' ' . $isSortable . ' ' . $sortClass . '" data-source-col="' . osc_esc_html($sourceCol) . '">' . $v . '</th>';
  }
  echo '</tr></thead><tbody>';

  if(count($rows) > 0) {
    foreach($rows as $key => $row) {
      $raw = (isset($aRawRows[$key]) ? $aRawRows[$key] : array());
      $trClass = implode(' ', osc_apply_filter($rowClassFilter, array(), $raw, $row));
      echo '<tr class="' . osc_esc_html($trClass) . '">';
      foreach($row as $k => $v) {
        echo '<td class="col-' . osc_esc_html($k) . '">' . $v . '</td>';
      }
      echo '</tr>';
    }
  } else {
    echo '<tr><td colspan="' . max(1, count($columns)) . '" class="text-center"><p>' . __('No data available in table') . '</p></td></tr>';
  }

  echo '</tbody></table><div id="table-row-actions"></div></div>';
}


/**
 * Open datatables POST form (bulk actions). Close with </form> after table + pagination.
 *
 * @param array $options {
 *   @type string $action_url
 *   @type string $page        Hidden page param value
 *   @type array  $hidden      More hidden fields name => value
 * }
 */
function osc_custom_datatable_print_form_open($options = array()) {
  $options = (is_array($options) ? $options : array());
  $actionUrl = (isset($options['action_url']) ? $options['action_url'] : osc_admin_base_url(true));

  echo '<form id="datatablesForm" action="' . osc_esc_html($actionUrl) . '" method="post" data-dialog-open="false">' . PHP_EOL;
  echo osc_csrf_token_form();

  if(isset($options['page']) && $options['page'] != '') {
    echo '<input type="hidden" name="page" value="' . osc_esc_html($options['page']) . '" />' . PHP_EOL;
  }

  if(isset($options['hidden']) && is_array($options['hidden'])) {
    foreach($options['hidden'] as $key => $value) {
      echo '<input type="hidden" name="' . osc_esc_html(strip_tags($key)) . '" value="' . osc_esc_html(strip_tags((string)$value)) . '" />' . PHP_EOL;
    }
  }
}


/**
 * Redirect to last page when current page is empty (same as core admin lists).
 *
 * @param array  $aData     Datatable getData() result
 * @param object $controller Admin controller with redirectTo()
 * @param array  $options {
 *   @type string $base_url  Admin URL base (default current QUERY_STRING)
 * }
 */
function osc_custom_datatable_redirect_empty_page($aData, $controller, $options = array()) {
  $options = (is_array($options) ? $options : array());
  $page = (int)Params::getParam('iPage');

  if(!is_array($aData) || !isset($aData['aRows']) || count($aData['aRows']) > 0 || $page <= 1) {
    return;
  }

  $total = (int)(isset($aData['iTotalDisplayRecords']) ? $aData['iTotalDisplayRecords'] : 0);
  $perPage = (int)(isset($aData['iDisplayLength']) ? $aData['iDisplayLength'] : 25);
  if($perPage <= 0) {
    $perPage = 25;
  }

  $maxPage = (int)ceil($total / $perPage);
  $url = (isset($options['base_url']) ? $options['base_url'] : osc_admin_base_url(true) . '?' . Params::getServerParam('QUERY_STRING', false, false));

  if($maxPage == 0) {
    $url = preg_replace('/&iPage=(\d)+/', '&iPage=1', $url);
    $controller->redirectTo($url);
    return;
  }

  if($page > 1) {
    $url = preg_replace('/&iPage=(\d)+/', '&iPage=' . $maxPage, $url);
    $controller->redirectTo($url);
  }
}


/**
 * Example configuration: payments list (osclass pay style).
 *
 * Copy and adapt in your plugin; fetch/process_row must call your models.
 *
 * @param array $options  Override defaults (id, columns, callbacks, ...)
 *
 * @return array  CustomDataTable options array
 */
function osc_custom_datatable_example_payments_config($options = array()) {
  $base = array(
    'id' => 'osp_pay_payments',
    'default_sort' => array('key' => 'date', 'direction' => 'desc'),
    'status_columns' => true,
    'bulk_actions' => array('input_name' => 'payment_id[]', 'value_key' => 'pk_i_id'),
    'columns' => array(
      array('id' => 'user', 'label' => __('User'), 'sortable' => true, 'sort_column' => 's_user_name', 'source' => 's_user_name|fk_i_user_id', 'priority' => 4),
      array('id' => 'product', 'label' => __('Product'), 'sortable' => true, 'sort_column' => 's_product_name', 'source' => 's_product_name', 'priority' => 5),
      array('id' => 'amount', 'label' => __('Amount'), 'sortable' => true, 'sort_column' => 'f_amount', 'source' => 'f_amount', 'priority' => 6),
      array('id' => 'date', 'label' => __('Date'), 'sortable' => true, 'sort_column' => 'dt_date', 'source' => 'dt_date', 'priority' => 7),
    ),
    'callbacks' => array(
      'actions_column' => 'user',
      'fetch' => function($table, $list) {
        return array('rows' => array(), 'total' => 0, 'total_filtered' => 0);
      },
      'process_row' => function($raw, $table) {
        $status = osc_custom_datatable_status_cells(($raw['b_paid'] == 1 ? __('Paid') : __('Pending')));
        return array_merge($status, array(
          'user' => (isset($raw['s_user_name']) ? osc_esc_html($raw['s_user_name']) : '-'),
          'product' => (isset($raw['s_product_name']) ? osc_esc_html($raw['s_product_name']) : '-'),
          'amount' => (isset($raw['f_amount']) ? osc_esc_html($raw['f_amount']) : '-'),
          'date' => (isset($raw['dt_date']) ? osc_format_date($raw['dt_date']) : '-'),
        ));
      },
      'row_class' => function($class, $raw, $row) {
        $class[] = (isset($raw['b_paid']) && (int)$raw['b_paid'] === 1 ? 'status-active' : 'status-pending');
        return $class;
      },
      'row_actions' => function($raw, $table) {
        return array(
          '<a href="#">' . __('View') . '</a>',
          '<a href="#">' . __('Refund') . '</a>',
        );
      },
    ),
  );

  return array_merge($base, (is_array($options) ? $options : array()));
}


/**
 * Example configuration: items pending validation.
 *
 * @param array $options
 *
 * @return array
 */
function osc_custom_datatable_example_items_validation_config($options = array()) {
  $base = array(
    'id' => 'items_validation',
    'default_sort' => array('key' => 'date', 'direction' => 'desc'),
    'status_columns' => true,
    'bulk_actions' => array('input_name' => 'id[]', 'value_key' => 'pk_i_id'),
    'columns' => array(
      array('id' => 'title', 'label' => __('Title'), 'sortable' => true, 'sort_column' => 's_title', 'source' => 's_title', 'priority' => 4),
      array('id' => 'user', 'label' => __('User'), 'sortable' => true, 'sort_column' => 's_contact_name', 'source' => 's_contact_name', 'priority' => 5),
      array('id' => 'date', 'label' => __('Publish date'), 'sortable' => true, 'sort_column' => 'dt_pub_date', 'source' => 'dt_pub_date', 'priority' => 6),
    ),
    'callbacks' => array(
      'actions_column' => 'title',
      'fetch' => function($table, $list) {
        return array('rows' => array(), 'total' => 0, 'total_filtered' => 0);
      },
      'process_row' => function($raw, $table) {
        $status = osc_custom_datatable_status_cells(__('Pending validation'));
        return array_merge($status, array(
          'title' => (isset($raw['s_title']) ? osc_esc_html($raw['s_title']) : '-'),
          'user' => (isset($raw['s_contact_name']) ? osc_esc_html($raw['s_contact_name']) : '-'),
          'date' => (isset($raw['dt_pub_date']) ? osc_format_date($raw['dt_pub_date']) : '-'),
        ));
      },
      'row_class' => function($class, $raw, $row) {
        $class[] = 'status-pending';
        return $class;
      },
      'row_actions' => function($raw, $table) {
        $id = (int)(isset($raw['pk_i_id']) ? $raw['pk_i_id'] : 0);
        $csrf = osc_csrf_token_url();
        return array(
          '<a href="' . osc_admin_base_url(true) . '?page=items&amp;action=items_edit&amp;id=' . $id . '">' . __('Edit') . '</a>',
          '<a href="' . osc_admin_base_url(true) . '?page=items&amp;action=activate&amp;id[]=' . $id . '&amp;' . $csrf . '">' . __('Validate') . '</a>',
        );
      },
    ),
  );

  return array_merge($base, (is_array($options) ? $options : array()));
}

/* file end: ./oc-includes/osclass/helpers/hCustomDataTable.php */
