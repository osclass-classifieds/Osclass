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
class PagesDataTable extends DataTable {
  private $keyword;
  private $orderBoundsById = array();

  /**
   * @param $params
   *
   * @return array
   * @throws \Exception
   */
  public function table($params) {
    $this->addTableHeader();
    $this->getDBParams($params);

    $pageManager = Page::newInstance();
    $pageManager->normalizeOrders();
    $this->buildOrderBounds();

    $pages = $pageManager->listAll(0, null, null, null, null);
    $pages = $this->filterPages($pages);
    $pages = $this->sortPages($pages);

    $this->total = Page::newInstance()->count(0);
    $this->totalFiltered = count($pages);
    $this->processData(array_slice($pages, $this->start, $this->limit));

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
    $this->setDefaultSort('order', 'asc');
    $this->addSortColumn('title', 'title');
    $this->addSortColumn('internal_name', 's_internal_name');
    $this->addSortColumn('visibility', 'i_visibility');
    $this->addSortColumn('link', 'b_link');
    $this->addSortColumn('index', 'b_index');
    $this->addSortColumn('pub_date', 'dt_pub_date');
    $this->addSortColumn('order', 'i_order');

    $this->addSourceColumn('title', 's_title');
    $this->addSourceColumn('internal_name', 's_internal_name');
    $this->addSourceColumn('visibility', 'i_visibility');
    $this->addSourceColumn('link', 'b_link');
    $this->addSourceColumn('index', 'b_index');
    $this->addSourceColumn('pub_date', 'dt_pub_date');
    $this->addSourceColumn('order', 'i_order');

    $this->addColumn('bulkactions', '<input id="check_all" type="checkbox" />');
    $this->addColumn('title', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('title', $sort, $direction)) . '">' . __('Title') . '</a>');
    $this->addColumn('internal_name', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('internal_name', $sort, $direction)) . '">' . __('Internal name') . '</a>');
    $this->addColumn('visibility', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('visibility', $sort, $direction)) . '">' . __('Visibility') . '</a>');
    $this->addColumn('link', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('link', $sort, $direction)) . '">' . __('Footer') . '</a>');
    $this->addColumn('index', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('index', $sort, $direction)) . '">' . __('Indexable') . '</a>');
    $this->addColumn('pub_date', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('pub_date', $sort, $direction)) . '">' . __('Publish date') . '</a>');
    $this->addColumn('order', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('order', $sort, $direction)) . '">' . __('Position') . '</a>');

    $dummy = &$this;
    osc_run_hook( 'admin_pages_table' , $dummy);
  }

  /**
   * @param $pages
   *
   * @throws \Exception
   */
  private function processData($pages) {
    if(!empty($pages)) {
      require_once osc_lib_path() . 'osclass/classes/PositionOrder.php';

      $prefLocale = osc_current_user_locale();

      foreach($pages as $aRow) {
        $row   = array();
        $content = array();

        if(isset($aRow['locale'][$prefLocale]) && !empty($aRow['locale'][$prefLocale]['s_title']) ) {
          $content = $aRow['locale'][$prefLocale];
        } else {
          $content = current($aRow['locale']);
        }

        $options   = array();
        $options[] = '<a href="' . osc_admin_base_url(true) . '?page=pages&amp;action=edit&amp;id=' . $aRow['pk_i_id'] . '">' . __('Edit') . '</a>';
        if(!$aRow['b_indelible'] ) {
          $options[] = '<a onclick="return delete_dialog(\'' . $aRow['pk_i_id'] . '\');" href="' . osc_admin_base_url(true) . '?page=pages&amp;action=delete&amp;id=' . $aRow['pk_i_id'] . '&amp;' . osc_csrf_token_url() . '">' . __('Delete') . '</a>';
        }

        $auxOptions = '<ul>'.PHP_EOL;
        foreach($options as $actual) {
          $auxOptions .= '<li>'.$actual.'</li>'.PHP_EOL;
        }
        $actions = '<div class="actions">'.$auxOptions.'</div>'.PHP_EOL;

        View::newInstance()->_exportVariableToView('page', $aRow);
        $pageTitle = osc_esc_html($content['s_title']);
        $titleLink = '<a href="' . osc_esc_html(osc_static_page_url()) . '" target="_blank">' . $pageTitle . '<span class="icon-new-window"></span></a>';

        $visibilityId = (int)$aRow['i_visibility'];
        $visibilityName = osc_static_page_visibility_name($visibilityId);
        $linkOn = ((int)$aRow['b_link'] === 1);
        $indexOn = ((int)$aRow['b_index'] === 1);
        $linkBadge = ($linkOn ? '<span class="enabled-value">✓ ' . __('Yes') . '</span>' : '<span class="disabled-value">✗ ' . __('No') . '</span>');
        $indexBadge = ($indexOn ? '<span class="enabled-value">✓ ' . __('Yes') . '</span>' : '<span class="disabled-value">✗ ' . __('No') . '</span>');

        $row['id'] = $aRow['pk_i_id'];
        $row['bulkactions'] = '<input type="checkbox" name="id[]" value="' . $aRow['pk_i_id'] . '" />';
        $row['title'] = $titleLink . $actions;
        $row['internal_name'] = $aRow['s_internal_name'];
        $row['visibility'] = '<a href="' . osc_esc_html($this->buildAdminFilterUrl(array('i_visibility' => $visibilityId))) . '">' . osc_esc_html($visibilityName) . '</a>';
        $row['link'] = '<a href="' . osc_esc_html($this->buildAdminFilterUrl(array('b_link' => ($linkOn ? 1 : 0)))) . '">' . $linkBadge . '</a>';
        $row['index'] = '<a href="' . osc_esc_html($this->buildAdminFilterUrl(array('b_index' => ($indexOn ? 1 : 0)))) . '">' . $indexBadge . '</a>';
        $row['pub_date'] = osc_format_date_only($aRow['dt_pub_date']);
        if((int)$aRow['b_indelible'] === 0) {
          $row['order'] = $this->renderOrderBox((int)$aRow['pk_i_id']);
        } else {
          $row['order'] = '&mdash;';
        }

        $row = osc_apply_filter('pages_processing_row', $row, $aRow);

        $this->addRow($row);
        $this->rawRows[] = $aRow;
      }

    }
  }

  // Build admin list filter URL preserving list state
  private function buildAdminFilterUrl($extra = array()) {
    $url = osc_admin_base_url(true) . '?page=pages';
    $keep = array('iDisplayLength', 'sort', 'direction', 'iPage', 'sSearch', 'i_visibility', 'b_link', 'b_index');

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

  // Map page ids to position and move bounds (first/last in i_order sort)
  private function buildOrderBounds() {
    $orderRows = Page::newInstance()->listOrderRows();
    $total = count($orderRows);
    $this->orderBoundsById = array();

    foreach($orderRows as $i => $row) {
      $id = (int)$row['pk_i_id'];
      $this->orderBoundsById[$id] = array(
        'position' => max(1, (int)$row['i_order']),
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

  private function filterPages($pages) {
    $visibilityFilter = Params::getParam('i_visibility');
    $linkFilter = Params::getParam('b_link');
    $indexFilter = Params::getParam('b_index');
    $hasColumnFilters = ($visibilityFilter !== '' && $visibilityFilter !== null) || ($linkFilter !== '' && $linkFilter !== null) || ($indexFilter !== '' && $indexFilter !== null);

    if($this->keyword == '' && !$hasColumnFilters) {
      return $pages;
    }

    $keyword = osc_strtolower($this->keyword);
    $filtered = array();

    foreach($pages as $page) {
      if($visibilityFilter !== '' && $visibilityFilter !== null && (int)$page['i_visibility'] !== (int)$visibilityFilter) {
        continue;
      }

      if($linkFilter !== '' && $linkFilter !== null && (int)$page['b_link'] !== (int)$linkFilter) {
        continue;
      }

      if($indexFilter !== '' && $indexFilter !== null && (int)$page['b_index'] !== (int)$indexFilter) {
        continue;
      }

      if($this->keyword != '') {
        $title = $this->getPageTitle($page);
        $haystack = osc_strtolower($title . ' ' . $page['s_internal_name'] . ' ' . $page['dt_pub_date']);

        if(strpos($haystack, $keyword) === false) {
          continue;
        }
      }

      $filtered[] = $page;
    }

    return $filtered;
  }

  private function sortPages($pages) {
    $sortData = $this->resolveSort(array(
      'sort' => Params::getParam('sort'),
      'direction' => Params::getParam('direction')
    ));

    Params::setParam('sort', $sortData['key']);
    Params::setParam('direction', $sortData['direction']);

    $sortKey = $sortData['key'];
    $direction = $sortData['direction'];

    usort($pages, function($a, $b) use ($sortKey, $direction) {
      $left = '';
      $right = '';

      if($sortKey == 'title') {
        $left = $this->getPageTitle($a);
        $right = $this->getPageTitle($b);
      } else if($sortKey == 'visibility') {
        $left = (int)$a['i_visibility'];
        $right = (int)$b['i_visibility'];
      } else if($sortKey == 'link') {
        $left = (int)$a['b_link'];
        $right = (int)$b['b_link'];
      } else if($sortKey == 'index') {
        $left = (int)$a['b_index'];
        $right = (int)$b['b_index'];
      } else if($sortKey == 'order') {
        $left = max(1, (int)$a['i_order']);
        $right = max(1, (int)$b['i_order']);
      } else if($sortKey == 'pub_date') {
        $left = $a['dt_pub_date'];
        $right = $b['dt_pub_date'];
      } else if($sortKey == 'internal_name') {
        $left = $a['s_internal_name'];
        $right = $b['s_internal_name'];
      } else {
        $left = max(1, (int)$a['i_order']);
        $right = max(1, (int)$b['i_order']);
      }

      if($left == $right) {
        return 0;
      }

      if($direction == 'asc') {
        return ($left < $right ? -1 : 1);
      }

      return ($left > $right ? -1 : 1);
    });

    return $pages;
  }

  private function getPageTitle($page) {
    $prefLocale = osc_current_user_locale();

    if(isset($page['locale'][$prefLocale]['s_title']) && trim((string)$page['locale'][$prefLocale]['s_title']) != '') {
      return $page['locale'][$prefLocale]['s_title'];
    }

    $first = current($page['locale']);
    return (isset($first['s_title']) ? $first['s_title'] : '');
  }
}

/* file end: ./oc-includes/osclass/classes/datatables/PagesDataTable.php */
