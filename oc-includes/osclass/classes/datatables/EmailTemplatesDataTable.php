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
 * EmailTemplatesDataTable class
 *
 * @package Osclass
 * @subpackage classes
 */
class EmailTemplatesDataTable extends DataTable {
  private $keyword;

  public function table($params) {
    $this->addTableHeader();
    $this->getDBParams($params);

    $emails = Page::newInstance()->listAll(1, null, null, null, null);
    $emails = $this->filterEmails($emails);
    $emails = $this->sortEmails($emails);

    $this->total = Page::newInstance()->count(1);
    $this->totalFiltered = count($emails);
    $this->processData(array_slice($emails, $this->start, $this->limit));

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
    $this->setDefaultSort('internal_name', 'asc');
    // List of sortable columns in datatable
    $this->addSortColumn('title', 'title');
    $this->addSortColumn('internal_name', 's_internal_name');
    $this->addSortColumn('pub_date', 'dt_pub_date');
    $this->addSortColumn('mod_date', 'dt_mod_date');

    // Source columns used by data-source-col in table header
    $this->addSourceColumn('title', 's_title');
    $this->addSourceColumn('internal_name', 's_internal_name');
    $this->addSourceColumn('pub_date', 'dt_pub_date');
    $this->addSourceColumn('mod_date', 'dt_mod_date');

    // Table header columns rendered in admin
    $this->addColumn('title', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('title', $sort, $direction)) . '">' . __('Title') . '</a>');
    $this->addColumn('internal_name', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('internal_name', $sort, $direction)) . '">' . __('Internal name') . '</a>');
    $this->addColumn('pub_date', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('pub_date', $sort, $direction)) . '">' . __('Create date') . '</a>');
    $this->addColumn('mod_date', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('mod_date', $sort, $direction)) . '">' . __('Update date') . '</a>');
  }

  private function getDBParams($params) {
    $this->iPage = (isset($params['iPage']) && (int)$params['iPage'] > 0 ? (int)$params['iPage'] : 1);
    $this->limit = (isset($params['iDisplayLength']) && (int)$params['iDisplayLength'] > 0 ? (int)$params['iDisplayLength'] : 25);
    $this->start = (int)(($this->iPage - 1) * $this->limit);
    $this->keyword = (isset($params['sSearch']) ? trim((string)$params['sSearch']) : '');
  }

  private function processData($emails) {
    if(empty($emails)) {
      return;
    }

    $prefLocale = osc_current_admin_locale();

    foreach($emails as $email) {
      $title = '';
      if(isset($email['locale'][$prefLocale]['s_title']) && trim((string)$email['locale'][$prefLocale]['s_title']) != '') {
        $title = $email['locale'][$prefLocale]['s_title'];
      } else {
        $first = current($email['locale']);
        $title = (isset($first['s_title']) ? $first['s_title'] : '');
      }

      $options = array();
      $options[] = '<a href="' . osc_admin_base_url(true) . '?page=emails&amp;action=edit&amp;id=' . $email['pk_i_id'] . '">' . __('Edit') . '</a>';
      $options[] = '<a href="#" class="email-test-popup" data-id="' . (int)$email['pk_i_id'] . '">' . __('Test - Send email') . '</a>';
      $actions = $this->buildRowActions($options, array(), 8);

      $row = array();
      $row['id'] = $email['pk_i_id'];
      $row['title'] = $title . $actions;
      $row['internal_name'] = $email['s_internal_name'];
      $row['pub_date'] = osc_format_date_only($email['dt_pub_date']);
      $row['mod_date'] = osc_format_date_only($email['dt_mod_date']);
      $this->addRow($row);
      $this->rawRows[] = $email;
    }
  }

  private function filterEmails($emails) {
    if($this->keyword == '') {
      return $emails;
    }

    $keyword = osc_strtolower($this->keyword);
    $filtered = array();
    $prefLocale = osc_current_admin_locale();

    foreach($emails as $email) {
      $title = '';
      if(isset($email['locale'][$prefLocale]['s_title']) && trim((string)$email['locale'][$prefLocale]['s_title']) != '') {
        $title = $email['locale'][$prefLocale]['s_title'];
      } else {
        $first = current($email['locale']);
        $title = (isset($first['s_title']) ? $first['s_title'] : '');
      }

      $haystack = osc_strtolower($title . ' ' . $email['s_internal_name'] . ' ' . $email['dt_pub_date'] . ' ' . $email['dt_mod_date']);
      if(strpos($haystack, $keyword) !== false) {
        $filtered[] = $email;
      }
    }

    return $filtered;
  }

  private function sortEmails($emails) {
    $sortData = $this->resolveSort(array(
      'sort' => Params::getParam('sort'),
      'direction' => Params::getParam('direction')
    ));

    Params::setParam('sort', $sortData['key']);
    Params::setParam('direction', $sortData['direction']);
    $prefLocale = osc_current_admin_locale();

    usort($emails, function($a, $b) use ($sortData, $prefLocale) {
      $left = '';
      $right = '';

      if($sortData['key'] == 'title') {
        if(isset($a['locale'][$prefLocale]['s_title']) && trim((string)$a['locale'][$prefLocale]['s_title']) != '') {
          $left = $a['locale'][$prefLocale]['s_title'];
        } else {
          $leftTitle = current($a['locale']);
          $left = (isset($leftTitle['s_title']) ? $leftTitle['s_title'] : '');
        }

        if(isset($b['locale'][$prefLocale]['s_title']) && trim((string)$b['locale'][$prefLocale]['s_title']) != '') {
          $right = $b['locale'][$prefLocale]['s_title'];
        } else {
          $rightTitle = current($b['locale']);
          $right = (isset($rightTitle['s_title']) ? $rightTitle['s_title'] : '');
        }
      } else if($sortData['key'] == 'internal_name') {
        $left = $a['s_internal_name'];
        $right = $b['s_internal_name'];
      } else if($sortData['key'] == 'mod_date') {
        $left = $a['dt_mod_date'];
        $right = $b['dt_mod_date'];
      } else {
        $left = $a['dt_pub_date'];
        $right = $b['dt_pub_date'];
      }

      if($left == $right) {
        return 0;
      }

      if($sortData['direction'] == 'asc') {
        return ($left < $right ? -1 : 1);
      }

      return ($left > $right ? -1 : 1);
    });

    return $emails;
  }
}
