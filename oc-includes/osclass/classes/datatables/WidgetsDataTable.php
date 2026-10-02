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
 * WidgetsDataTable class
 *
 * @package Osclass
 * @subpackage classes
 */
class WidgetsDataTable extends DataTable {
  private $keyword;
  private $orderBoundsById = array();

  /**
   * @param $params
   *
   * @return array
   */
  public function table($params) {
    $this->addTableHeader();
    $this->getDBParams($params);

    $widgets = Widget::newInstance()->listAll(false);
    $widgets = $this->filterWidgets($widgets);
    $widgets = $this->sortWidgets($widgets);
    $this->buildOrderBounds(Widget::newInstance()->listAll(false));

    $this->total = count(Widget::newInstance()->listAll(false));
    $this->totalFiltered = count($widgets);
    $this->processData(array_slice($widgets, $this->start, $this->limit));

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
    $this->setDefaultSort('section', 'asc');
    $this->addSortColumn('name', 's_description');
    $this->addSortColumn('internal_name', 's_internal_name');
    $this->addSortColumn('section', 's_location');
    $this->addSortColumn('device', 's_device_visibility');
    $this->addSortColumn('order', 'i_order');

    $this->addSourceColumn('name', 's_description');
    $this->addSourceColumn('internal_name', 's_internal_name');
    $this->addSourceColumn('section', 's_location');
    $this->addSourceColumn('device', 's_device_visibility');
    $this->addSourceColumn('order', 'i_order');

    $this->addColumn('bulkactions', '<input id="check_all" type="checkbox" />');
    $this->addColumn('name', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('name', $sort, $direction)) . '">' . __('Name') . '</a>');
    $this->addColumn('internal_name', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('internal_name', $sort, $direction)) . '">' . __('Internal name') . '</a>');
    $this->addColumn('section', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('section', $sort, $direction)) . '">' . __('Section') . '</a>');
    $this->addColumn('device', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('device', $sort, $direction)) . '">' . __('Device') . '</a>');
    $this->addColumn('preview', __('Preview'));
    $this->addColumn('order', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('order', $sort, $direction)) . '">' . __('Position') . '</a>');

    $dummy = &$this;
    osc_run_hook('admin_widgets_table', $dummy);
  }

  /**
   * @param $widgets
   */
  private function processData($widgets) {
    if(empty($widgets)) {
      return;
    }

    foreach($widgets as $aRow) {
      $options = array();
      $options[] = '<a href="' . osc_admin_base_url(true) . '?page=appearance&amp;action=edit_widget&amp;id=' . $aRow['pk_i_id'] . '">' . __('Edit') . '</a>';
      $options[] = '<a onclick="return delete_dialog(\'' . $aRow['pk_i_id'] . '\');" href="' . osc_admin_base_url(true) . '?page=appearance&amp;action=delete_widget&amp;id=' . $aRow['pk_i_id'] . '&amp;' . osc_csrf_token_url() . '">' . __('Delete') . '</a>';

      $auxOptions = '<ul>'.PHP_EOL;
      foreach($options as $actual) {
        $auxOptions .= '<li>'.$actual.'</li>'.PHP_EOL;
      }
      $actions = '<div class="actions">'.$auxOptions.'</div>'.PHP_EOL;

      $previewSource = (isset($aRow['s_content']) ? $aRow['s_content'] : '');
      if(trim(strip_tags((string)$previewSource)) == '' && isset($aRow['s_code'])) {
        $previewSource = $aRow['s_code'];
      }

      $row = array();
      $row['id'] = $aRow['pk_i_id'];
      $row['bulkactions'] = '<input type="checkbox" name="id[]" value="' . $aRow['pk_i_id'] . '" />';
      $row['name'] = osc_esc_html($aRow['s_description']) . $actions;
      $row['internal_name'] = osc_esc_html($aRow['s_internal_name']);
      $row['section'] = '<a href="' . osc_esc_html($this->buildAdminFilterUrl(array('s_location' => $aRow['s_location']))) . '">' . osc_esc_html($aRow['s_location']) . '</a>';
      $row['device'] = '<a href="' . osc_esc_html($this->buildAdminFilterUrl(array('s_device_visibility' => $aRow['s_device_visibility']))) . '">' . osc_esc_html($this->deviceLabel($aRow['s_device_visibility'])) . '</a>';
      $row['preview'] = osc_highlight(strip_tags((string)$previewSource), 160);
      $row['order'] = $this->renderOrderBox((int)$aRow['pk_i_id']);

      $row = osc_apply_filter('widgets_processing_row', $row, $aRow);

      $this->addRow($row);
      $this->rawRows[] = $aRow;
    }
  }

  // Build admin list filter URL preserving list state
  private function buildAdminFilterUrl($extra = array()) {
    $url = osc_admin_base_url(true) . '?page=appearance&action=widgets';
    $keep = array('iDisplayLength', 'sort', 'direction', 'iPage', 'sSearch', 's_location', 's_device_visibility');

    foreach($keep as $key) {
      if(array_key_exists($key, $extra)) {
        continue;
      }

      $value = Params::getParam($key);
      if($value !== '' && $value !== null) {
        $url .= '&' . rawurlencode($key) . '=' . rawurlencode((string)$value);
      }
    }

    if(is_array($extra)) {
      foreach($extra as $key => $value) {
        $url .= '&' . rawurlencode($key) . '=' . rawurlencode((string)$value);
      }
    }

    return $url;
  }

  // Map widget ids to position and move bounds within the same section
  private function buildOrderBounds($widgets) {
    $byLocation = array();
    foreach($widgets as $row) {
      $loc = (isset($row['s_location']) ? $row['s_location'] : '');
      if(!isset($byLocation[$loc])) {
        $byLocation[$loc] = array();
      }
      $byLocation[$loc][] = $row;
    }

    $this->orderBoundsById = array();
    foreach($byLocation as $list) {
      $total = count($list);
      foreach($list as $i => $row) {
        $id = (int)$row['pk_i_id'];
        $this->orderBoundsById[$id] = array(
          'position' => max(1, (int)$row['i_order']),
          'can_up' => ($i > 0),
          'can_down' => ($i < $total - 1)
        );
      }
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
    $this->keyword = (isset($params['sSearch']) ? trim((string)$params['sSearch']) : '');
  }

  private function filterWidgets($widgets) {
    $locationFilter = Params::getParam('s_location');
    $deviceFilter = Params::getParam('s_device_visibility');
    $hasColumnFilters = ($locationFilter !== '' && $locationFilter !== null) || ($deviceFilter !== '' && $deviceFilter !== null);

    if($this->keyword == '' && !$hasColumnFilters) {
      return $widgets;
    }

    $keyword = osc_strtolower($this->keyword);
    $filtered = array();

    foreach($widgets as $widget) {
      if($locationFilter !== '' && $locationFilter !== null && $widget['s_location'] !== $locationFilter) {
        continue;
      }

      if($deviceFilter !== '' && $deviceFilter !== null && $widget['s_device_visibility'] !== $deviceFilter) {
        continue;
      }

      if($this->keyword != '') {
        $haystack = osc_strtolower($widget['s_description'] . ' ' . $widget['s_internal_name'] . ' ' . $widget['s_location'] . ' ' . (isset($widget['s_content']) ? $widget['s_content'] : '') . ' ' . (isset($widget['s_code']) ? $widget['s_code'] : ''));
        if(strpos($haystack, $keyword) === false) {
          continue;
        }
      }

      $filtered[] = $widget;
    }

    return $filtered;
  }

  private function sortWidgets($widgets) {
    $sortData = $this->resolveSort(array(
      'sort' => Params::getParam('sort'),
      'direction' => Params::getParam('direction')
    ));

    Params::setParam('sort', $sortData['key']);
    Params::setParam('direction', $sortData['direction']);

    $sortKey = $sortData['key'];
    $direction = $sortData['direction'];

    usort($widgets, function($a, $b) use ($sortKey, $direction) {
      $left = '';
      $right = '';

      if($sortKey == 'name') {
        $left = $a['s_description'];
        $right = $b['s_description'];
      } else if($sortKey == 'internal_name') {
        $left = $a['s_internal_name'];
        $right = $b['s_internal_name'];
      } else if($sortKey == 'device') {
        $left = $a['s_device_visibility'];
        $right = $b['s_device_visibility'];
      } else if($sortKey == 'order') {
        $left = $a['s_location'] . '|' . sprintf('%010d', (int)$a['i_order']);
        $right = $b['s_location'] . '|' . sprintf('%010d', (int)$b['i_order']);
      } else {
        $left = $a['s_location'] . '|' . sprintf('%010d', (int)$a['i_order']);
        $right = $b['s_location'] . '|' . sprintf('%010d', (int)$b['i_order']);
      }

      if($left == $right) {
        return ((int)$a['pk_i_id'] < (int)$b['pk_i_id'] ? -1 : 1);
      }

      if($direction == 'asc') {
        return ($left < $right ? -1 : 1);
      }

      return ($left > $right ? -1 : 1);
    });

    return $widgets;
  }

  private function deviceLabel($device) {
    if($device === 'mobile') {
      return __('Mobile only');
    }
    if($device === 'desktop') {
      return __('Desktop only');
    }
    return __('All devices');
  }
}

/* file end: ./oc-includes/osclass/classes/datatables/WidgetsDataTable.php */
