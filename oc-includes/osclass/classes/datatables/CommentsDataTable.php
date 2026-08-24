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
 * CommentsDataTable class
 *
 * @since 3.1
 * @package Osclass
 * @subpackage classes
 * @author Osclass
 */
class CommentsDataTable extends DataTable {
  private $itemId;
  private $order_by;
  private $showAll;
  private $replyId;
  private $keyword;
  private $userId;
  private $commentRatingEnabled;

  public function __construct() {
    parent::__construct();
    $this->commentRatingEnabled = osc_enable_comment_rating();
    osc_add_filter('datatable_comment_class', array(&$this, 'row_class'));
  }

  /**
   * @param $params
   *
   * @return array
   * @throws \Exception
   */
  public function table($params) {
    $this->addTableHeader();
    $this->getDBParams($params);

    $comments = ItemComment::newInstance()->search(
      $this->itemId,
      $this->start,
      $this->limit,
      ($this->order_by['column_name'] ?: 'pk_i_id'),
      ($this->order_by['type'] ?: 'desc'),
      $this->showAll,
      $this->replyId,
      $this->keyword,
      $this->userId
    );

    $this->processData($comments);

    $this->total = ItemComment::newInstance()->count($this->itemId, $this->replyId, $this->showAll, '', $this->userId);
    $this->totalFiltered = ItemComment::newInstance()->count($this->itemId, $this->replyId, $this->showAll, $this->keyword, $this->userId);

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
    $this->setDefaultSort('date', 'desc');
    // List of sortable columns in datatable
    $this->addSortColumn('item', 'c.fk_i_item_id', true);
    $this->addSortColumn('author', 'c.s_author_name', true);
    $this->addSortColumn('comment', 'c.s_title', true);
    $this->addSortColumn('is_reply', 'c.fk_i_reply_id', true);
    $this->addSortColumn('has_reply', 'h.i_reply_count', true);
    if($this->commentRatingEnabled) {
      $this->addSortColumn('rating', 'c.i_rating', true);
    }
    $this->addSortColumn('date', 'c.dt_pub_date');

    // Source columns used by data-source-col in table header
    $this->addSourceColumn('item', 'fk_i_item_id');
    $this->addSourceColumn('author', 's_author_name');
    $this->addSourceColumn('comment', 's_title|s_body');
    $this->addSourceColumn('is_reply', 'fk_i_reply_id');
    $this->addSourceColumn('has_reply', 'i_reply_count');
    if($this->commentRatingEnabled) {
      $this->addSourceColumn('rating', 'i_rating');
    }
    $this->addSourceColumn('date', 'dt_pub_date');

    // Table header columns rendered in admin
    $this->addColumn('status-border', '');
    $this->addColumn('status', __('Status'));
    $this->addColumn('bulkactions', '<input id="check_all" type="checkbox" />');
    $this->addColumn('item', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('item', $sort, $direction)) . '">' . __('Item') . '</a>');
    $this->addColumn('author', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('author', $sort, $direction)) . '">' . __('Author') . '</a>');
    $this->addColumn('comment', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('comment', $sort, $direction)) . '">' . __('Comment') . '</a>');
    $this->addColumn('is_reply', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('is_reply', $sort, $direction)) . '">' . __('Is reply to comment') . '</a>');
    $this->addColumn('has_reply', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('has_reply', $sort, $direction)) . '">' . __('Has replies') . '</a>');
    if($this->commentRatingEnabled) {
      $this->addColumn('rating', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('rating', $sort, $direction)) . '">' . __('Rating') . '</a>');
    }
    $this->addColumn('date', '<a href="' . osc_esc_html($url_base . $this->buildSortArgs('date', $sort, $direction)) . '">' . __('Date') . '</a>');

    $dummy = &$this;
    osc_run_hook('admin_comments_table' , $dummy);
  }

  /**
   * @param $comments
   *
   * @throws \Exception
   */
  private function processData($comments) {
    if(!empty($comments)) {
      $csrf_token_url = osc_csrf_token_url();
      foreach($comments as $aRow) {
        $row = array();
        $options = array();
        $options_more = array();

        View::newInstance()->_exportVariableToView('item', osc_get_item_row($aRow['fk_i_item_id']));

        if($aRow['b_enabled']) {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=comments&amp;action=status&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;value=DISABLE">' . __('Block') . '</a>';
        } else {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=comments&amp;action=status&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;value=ENABLE">' . __('Unblock') . '</a>';
        }

        if($aRow['fk_i_reply_id'] !== null) {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=comments&amp;replyId=' . $aRow['fk_i_reply_id'] . '">' . __('View parent comment') . '</a>';
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=comments&amp;action=comment_edit&amp;id=' . $aRow['fk_i_reply_id'] . '">' . __('Edit parent comment') . '</a>';
        }

        if($aRow['i_reply_count'] > 0) {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=comments&amp;replyId=' . $aRow['pk_i_id'] . '">' . __('View replies') . '</a>';
        }

        $options_more[] = '<a onclick="return delete_dialog(\'' . $aRow['pk_i_id'] . '\');" href="' . osc_admin_base_url(true) . '?page=comments&amp;action=delete&amp;id=' . $aRow['pk_i_id'] .'" id="dt_link_delete">' . __('Delete') . '</a>';

        $options[] = '<a href="' . osc_admin_base_url(true) . '?page=comments&amp;action=comment_edit&amp;id=' . $aRow['pk_i_id'] . '" id="dt_link_edit">' . __('Edit') . '</a>';
        if($aRow['b_active']) {
          $options[] = '<a href="' . osc_admin_base_url(true) . '?page=comments&amp;action=status&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;value=INACTIVE">' . __('Deactivate') . '</a>';
        } else {
          $options[] = '<a href="' . osc_admin_base_url(true) . '?page=comments&amp;action=status&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url .'&amp;value=ACTIVE">' . __('Activate') . '</a>';
        }

        $options[] = '<a href="' . osc_admin_base_url(true) . '?page=items&action=item_edit&id=' . (int)$aRow['fk_i_item_id'] . '">' . __('Edit item') . '</a>';
        $options[] = '<a href="' . osc_item_url() . '" target="_blank">' . __('View item') . '</a>';

        if(isset($aRow['fk_i_user_id']) && (int)$aRow['fk_i_user_id'] > 0) {
          $options[] = '<a href="' . osc_admin_base_url(true) . '?page=users&amp;action=edit&amp;id=' . (int)$aRow['fk_i_user_id'] . '">' . __('Edit user') . '</a>';
        }

        $actions = $this->buildRowActions($options, $options_more, 8);

        $status = $this->get_row_status($aRow);

        $row['id'] = $aRow['pk_i_id'];
        $row['status-border'] = '';
        $row['status'] = $status['text'];
        $row['bulkactions'] = '<input type="checkbox" name="id[]" value="' . $aRow['pk_i_id']  . '" />';
        $row['item'] = '<a href="' . osc_admin_base_url(true) . '?page=comments&amp;itemId=' . (int)$aRow['fk_i_item_id'] . '">' . osc_item_title() . '</a>' . $actions;

        if($aRow['fk_i_user_id'] !== null) {
          $user = osc_get_user_row($aRow['fk_i_user_id']);

          if(isset($user['pk_i_id'])) {
            $aRow['s_author_name'] = '<a href="' . osc_admin_base_url(true) . '?page=comments&userId=' . (int)$user['pk_i_id'] . '">' . $user['s_name'] . '</a>';
          }
        }

        $row['author'] = $aRow['s_author_name'];
        $row['comment'] = '<strong class="comment-title">' . $aRow['s_title'] . '</strong><br/>';
        $row['comment'] .= $aRow['s_body'];

        $row['is_reply'] = '-';
        if($aRow['fk_i_reply_id'] !== null) {
          $row['is_reply'] = '<a target="_blank" href="' . osc_admin_base_url(true) . '?page=comments&replyId=' . $aRow['fk_i_reply_id'] . '">' . $aRow['reply_title'] . '</a>';
        }

        $row['has_reply'] = '-';
        if($aRow['i_reply_count'] !== null && $aRow['i_reply_count'] > 0) {
          $row['has_reply'] = '<a target="_blank" href="' . osc_admin_base_url(true) . '?page=comments&replyId=' . $aRow['pk_i_id'] . '">' . ($aRow['i_reply_count'] == 1 ? __('1 reply') : sprintf(__('%d replies'), $aRow['i_reply_count'])) . '</a>';
        }

        if($this->commentRatingEnabled) {
          $row['rating'] = ($aRow['i_rating'] !== null && $aRow['i_rating'] !== '' ? (int)$aRow['i_rating'] : '-');
        }

        $row['date'] = osc_format_date($aRow['dt_pub_date']);

        $row = osc_apply_filter('comments_processing_row', $row, $aRow);

        $this->addRow($row);
        $this->rawRows[] = $aRow;
      }
    }
  }

  /**
   * @param array $params
   */
  private function getDBParams($params) {
    $sortData = $this->resolveSort($params);
    $this->order_by['column_name'] = ($sortData['column'] != '' ? $sortData['column'] : 'c.dt_pub_date');
    $this->order_by['type'] = $sortData['direction'];
    Params::setParam('sort', $sortData['key']);
    Params::setParam('direction', $sortData['direction']);
    $this->showAll = Params::getParam('showAll') != 'off';
    $this->keyword = (isset($params['sSearch']) ? trim((string)$params['sSearch']) : '');

    foreach($params as $k => $v) {
      if(($k === 'itemId') && !empty($v)) {
        $this->itemId = (int) $v;
      }

      if(($k === 'replyId') && !empty($v)) {
        $this->replyId = (int) $v;
      }

      if(($k === 'userId') && !empty($v)) {
        $this->userId = (int) $v;
      }

      if($k === 'iDisplayStart') {
        $this->start = (int) $v;
      }

      if($k === 'iDisplayLength') {
        $this->limit = (int) $v;
      }
    }

    // set start and limit using iPage param
    $start = ((int)Params::getParam('iPage')-1) * $params['iDisplayLength'];

    $this->start = (int) $start;
    $this->limit = (int) $params['iDisplayLength'];
  }

  /**
   * @param $class
   * @param $rawRow
   * @param $row
   *
   * @return array
   */
  public function row_class($class, $rawRow, $row) {
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
   *     'text' => ''
   *  )
   */
  private function get_row_status($user) {
    if($user['b_enabled'] == 0) {
      return array(
        'class' => 'status-blocked',
        'text' => __('Blocked')
      );
    }

    if($user['b_active'] == 0) {
      return array(
        'class' => 'status-inactive',
        'text' => __('Inactive')
      );
    }

    return array(
      'class' => 'status-active',
      'text' => __('Active')
    );
  }
}
