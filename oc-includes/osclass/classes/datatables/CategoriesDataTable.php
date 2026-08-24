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
 * CategoriesDataTable class
 *
 * @package Osclass
 * @subpackage classes
 */
class CategoriesDataTable extends DataTable {
  private $search;
  private $orderKey;
  private $orderDir;
  private $parentId = 0;
  private $withFilters = false;
  private $orderBoundsById = array();

  public function __construct() {
    parent::__construct();
    osc_add_filter('datatable_categories_class', array(&$this, 'row_class'));
  }

  /**
   * @param array $class
   * @param array $rawRow
   * @param array $row
   *
   * @return array
   */
  public function row_class($class, $rawRow, $row) {
    $enabled = (isset($rawRow['b_enabled']) && (int)$rawRow['b_enabled'] === 1);
    $class[] = ($enabled ? 'status-active' : 'status-inactive');
    return $class;
  }

  /**
   * @return bool
   */
  public function withFilters() {
    return $this->withFilters;
  }

  /**
   * @param array $params
   *
   * @return array
   */
  public function table($params) {
    $this->search = '';
    $this->orderKey = 'order';
    $this->orderDir = 'ASC';
    $this->parentId = (int)Params::getParam('parent');
    $this->addTableHeader();
    $this->getDBParams($params);

    $categoryManager = Category::newInstance(osc_current_admin_locale());
    $categoryManager->normalizeOrdersByParent($this->parentId);
    $this->buildOrderBounds($categoryManager);

    $list = $categoryManager->adminListByParent($this->parentId, $this->start, $this->limit, $this->orderKey, $this->orderDir, $this->search);

    $this->processData($list['categories']);
    $this->total = (int)$list['total'];
    $this->totalFiltered = (int)$list['total_results'];

    return $this->getData();
  }

  private function addTableHeader() {
    Rewrite::newInstance()->init();
    $page = (int)Params::getParam('iPage');
    if($page == 0) {
      $page = 1;
    }
    Params::setParam('iPage', $page);

    $url_base = preg_replace('|&direction=([^&]*)|', '', preg_replace('|&sort=([^&]*)|', '', osc_base_url() . Rewrite::newInstance()->get_raw_request_uri()));
    $sort = Params::getParam('sort');
    $direction = Params::getParam('direction');

    $this->clearSortColumns();
    $this->clearSourceColumns();
    $this->setDefaultSort('order', 'asc');

    $this->addSortColumn('name', 's_name', true);
    $this->addSortColumn('children', 'i_children_count', true);
    $this->addSortColumn('items', 'i_num_items', true);
    $this->addSortColumn('expiration', 'i_expiration_days', true);
    $this->addSortColumn('price', 'b_price_enabled', true);
    $this->addSortColumn('icon', 's_icon', true);
    $this->addSortColumn('color', 's_color', true);
    $this->addSortColumn('order', 'i_position', true);
    $this->addSortColumn('status', 'b_enabled', true);

    $this->addSourceColumn('status-border', '');
    $this->addSourceColumn('status', 'b_enabled');
    $this->addSourceColumn('name', 's_name');
    $this->addSourceColumn('children', 'i_children_count');
    $this->addSourceColumn('items', 'i_num_items');
    $this->addSourceColumn('expiration', 'i_expiration_days');
    $this->addSourceColumn('price', 'b_price_enabled');
    $this->addSourceColumn('icon', 's_icon');
    $this->addSourceColumn('color', 's_color');
    $this->addSourceColumn('order', 'i_position');

    $this->addColumn('status-border', '', 1);
    $this->addColumn('status', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('status', $sort, $direction)) . '">' . __('Status') . '</a>', 2);
    $this->addColumn('bulkactions', '<input id="check_all" type="checkbox" />', 3);
    $this->addColumn('name', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('name', $sort, $direction)) . '">' . __('Name') . '</a>', 4);
    $this->addColumn('children', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('children', $sort, $direction)) . '">' . __('Subcategories') . '</a>', 5);
    $this->addColumn('items', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('items', $sort, $direction)) . '">' . __('Items') . '</a>', 6);
    $this->addColumn('expiration', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('expiration', $sort, $direction)) . '">' . __('Expiration') . '</a>', 7);
    $this->addColumn('price', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('price', $sort, $direction)) . '">' . __('Price field') . '</a>', 8);
    $this->addColumn('icon', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('icon', $sort, $direction)) . '">' . __('Icon') . '</a>', 9);
    $this->addColumn('color', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('color', $sort, $direction)) . '">' . __('Color') . '</a>', 10);
    $this->addColumn('order', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('order', $sort, $direction)) . '">' . __('Position') . '</a>', 11);

    $dummy = &$this;
    osc_run_hook('admin_categories_table', $dummy);
  }

  private function getDBParams($params) {
    if(!isset($params['iDisplayLength']) || (int)$params['iDisplayLength'] <= 0) {
      $params['iDisplayLength'] = 25;
    }

    if(!isset($params['iPage']) || (int)$params['iPage'] < 1) {
      $params['iPage'] = 1;
    }

    $this->iPage = (int)$params['iPage'];
    $this->limit = (int)$params['iDisplayLength'];
    $this->start = (int)(($this->iPage - 1) * $this->limit);

    $this->search = (isset($params['sSearch']) ? trim((string)$params['sSearch']) : '');
    $this->withFilters = ($this->search != '' || $this->parentId > 0);

    $sortData = $this->resolveSort($params, 'order', 'asc');
    Params::setParam('sort', $sortData['key']);
    Params::setParam('direction', $sortData['direction']);

    $this->orderKey = ($sortData['key'] != '' ? $sortData['key'] : 'order');
    if($this->orderKey == 'position') {
      $this->orderKey = 'order';
    }
    $this->orderDir = strtoupper($sortData['direction']);
    if($this->orderDir != 'ASC' && $this->orderDir != 'DESC') {
      $this->orderDir = 'ASC';
    }
  }

  // Map category ids to position and move bounds (siblings under current parent, by i_position)
  private function buildOrderBounds($categoryManager = null) {
    if($categoryManager === null) {
      $categoryManager = Category::newInstance(osc_current_admin_locale());
    }
    $orderRows = $categoryManager->listOrderRowsByParent($this->parentId);
    $total = count($orderRows);
    $this->orderBoundsById = array();

    foreach($orderRows as $i => $row) {
      $id = (int)$row['pk_i_id'];
      $this->orderBoundsById[$id] = array(
        'position' => max(1, (int)$row['i_position']),
        'can_up' => ($i > 0),
        'can_down' => ($i < $total - 1)
      );
    }
  }

  // Render position controls for one row
  private function renderOrderBox($id) {
    $bounds = (isset($this->orderBoundsById[$id]) ? $this->orderBoundsById[$id] : array('position' => 1, 'can_up' => false, 'can_down' => false));
    $position = (int)$bounds['position'];
    $canUp = !empty($bounds['can_up']);
    $canDown = !empty($bounds['can_down']);
    $upClass = 'order-up' . ($canUp ? '' : ' is-disabled');
    $downClass = 'order-down' . ($canDown ? '' : ' is-disabled');
    $upClick = ($canUp ? 'order_up(' . (int)$id . ');' : 'return false;');
    $downClick = ($canDown ? 'order_down(' . (int)$id . ');' : 'return false;');

    $html = '<div class="order-box">';
    $html .= '<div class="order-box-value">' . (int)$position . '</div>';
    $html .= '<div class="order-box-arrows">';
    $html .= '<span class="' . $upClass . '" onclick="' . $upClick . '" title="' . osc_esc_html(__('Up')) . '">&#9650;</span>';
    $html .= '<span class="' . $downClass . '" onclick="' . $downClick . '" title="' . osc_esc_html(__('Down')) . '">&#9660;</span>';
    $html .= '</div>';
    $html .= '</div>';

    return $html;
  }

  private function buildActionUrl($action, $id, $withCsrf = false) {
    $url = osc_categories_admin_list_url(array('action' => $action, 'id' => (int)$id));
    if($withCsrf) {
      $url .= '&' . osc_csrf_token_url();
    }
    return $url;
  }

  // Resolve safe CSS color for admin list swatch background
  private function resolveCategoryColorStyle($color) {
    if(preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $color)) {
      return $color;
    }
    if(preg_match('/^rgba?\(\s*[0-9.]+%?\s*,\s*[0-9.]+%?\s*,\s*[0-9.]+%?\s*(,\s*[0-9.]+%?)?\s*\)$/i', $color)) {
      return $color;
    }
    if(preg_match('/^hsla?\(\s*[0-9.]+\s*,\s*[0-9.]+%\s*,\s*[0-9.]+%\s*(,\s*[0-9.]+%?)?\s*\)$/i', $color)) {
      return $color;
    }
    if(preg_match('/^[a-zA-Z]{3,20}$/', $color)) {
      return strtolower($color);
    }
    return '';
  }

  private function renderColorCell($color) {
    if($color === null || trim((string)$color) === '') {
      return '-';
    }

    $color = trim((string)$color);
    $escaped = osc_esc_html($color);
    $styleColor = $this->resolveCategoryColorStyle($color);

    $swatch = '<span class="color-value-swatch"' . ($styleColor !== '' ? ' style="background-color:' . osc_esc_html($styleColor) . ';"' : '') . '></span>';
    return '<span class="color-value">' . $swatch . '<span>' . $escaped . '</span></span>';
  }

  private function processData($rows) {
    if(empty($rows)) {
      return;
    }

    foreach($rows as $aRow) {
      $id = (int)$aRow['pk_i_id'];
      $enabled = (isset($aRow['b_enabled']) && (int)$aRow['b_enabled'] === 1);
      $priceOn = (isset($aRow['b_price_enabled']) && (int)$aRow['b_price_enabled'] === 1);
      $childrenCount = (isset($aRow['i_children_count']) ? (int)$aRow['i_children_count'] : 0);
      $numItems = (isset($aRow['i_num_items']) ? (int)$aRow['i_num_items'] : 0);

      $options = array();
      $options[] = '<a href="' . osc_esc_html($this->buildActionUrl('edit', $id)) . '">' . __('Edit') . '</a>';
      $options[] = '<a href="' . osc_esc_html(osc_search_category_url($id)) . '" target="_blank">' . __('View in search') . '</a>';
      if($enabled) {
        $options[] = '<a href="' . osc_esc_html($this->buildActionUrl('disable', $id, true)) . '">' . __('Disable') . '</a>';
      } else {
        $options[] = '<a href="' . osc_esc_html($this->buildActionUrl('enable', $id, true)) . '">' . __('Enable') . '</a>';
      }
      if($priceOn) {
        $options[] = '<a href="' . osc_esc_html($this->buildActionUrl('disable_price', $id, true)) . '">' . __('Disable price') . '</a>';
      } else {
        $options[] = '<a href="' . osc_esc_html($this->buildActionUrl('enable_price', $id, true)) . '">' . __('Enable price') . '</a>';
      }
      $options[] = '<a onclick="return delete_dialog(' . (int)$id . ');" href="' . osc_esc_html($this->buildActionUrl('delete', $id, true)) . '">' . __('Delete') . '</a>';
      $actions = $this->buildRowActions($options, array(), 11, array());

      $catName = osc_category_row_name($aRow);
      $nameHtml = '<a href="' . osc_esc_html(osc_categories_admin_list_url(array('parent' => $id, 'iPage' => 1))) . '">' . osc_esc_html($catName) . '</a>';

      $childrenHtml = (string)(int)$childrenCount;
      $itemsHtml = '<a href="' . osc_esc_html(osc_admin_base_url(true) . '?page=items&amp;catId=' . $id) . '" target="_blank">' . (int)$numItems . '</a>';

      $priceBadge = ($priceOn ? '<span class="enabled-value">✓ ' . __('Enabled') . '</span>' : '<span class="disabled-value">✗ ' . __('Disabled') . '</span>');
      $exp = (int)$aRow['i_expiration_days'];
      if($exp > 0) {
        $expHtml = sprintf(_n('%d day', '%d days', $exp), $exp);
      } else {
        $expHtml = '<span class="disabled-value">✗ ' . __('Non-Expiring') . '</span>';
      }

      $row = array();
      $row['status-border'] = '';
      $row['status'] = $enabled ? __('Active') : __('Inactive');
      $row['bulkactions'] = '<input type="checkbox" name="id[]" value="' . $id . '" />';
      $row['name'] = $nameHtml . $actions;
      $row['children'] = $childrenHtml;
      $row['items'] = $itemsHtml;
      $row['expiration'] = $expHtml;
      $row['price'] = $priceBadge;
      $row['icon'] = CategoryForm::admin_icon_display(isset($aRow['s_icon']) ? $aRow['s_icon'] : '');
      $row['color'] = $this->renderColorCell(isset($aRow['s_color']) ? $aRow['s_color'] : '');
      $row['order'] = $this->renderOrderBox($id);

      $row = osc_apply_filter('categories_processing_row', $row, $aRow);

      $this->addRow($row);
      $this->rawRows[] = $aRow;
    }
  }
}

/* file end: ./oc-includes/osclass/classes/datatables/CategoriesDataTable.php */