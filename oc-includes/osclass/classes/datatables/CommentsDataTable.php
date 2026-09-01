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
  private $total_filtered;

  public function __construct() {
    parent::__construct();
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
      $this->replyId
    );

    $this->processData($comments);

    $conditions = array();

    if($this->replyId > 0) {
      $conditions[] = 'c.fk_i_reply_id = ' . (int)$this->replyId;
    }
    
    if(!$this->showAll) {
      $conditions[] = '(c.b_active = 0 OR c.b_enabled = 0 OR c.b_spam = 1)';
    }
    
    if(count($conditions) > 0) {
      $this->total = ItemComment::newInstance()->countAll(implode(' AND ', $conditions));
    } else {
      $this->total = ItemComment::newInstance()->countAll();
    }
    
    if($this->itemId > 0) {
      $this->total_filtered = ItemComment::newInstance()->count($this->itemId, $this->replyId);
    } else {
      $this->total_filtered = $this->total;
    }

    return $this->getData();
  }

  private function addTableHeader() {
    $this->addColumn('status-border', '');
    $this->addColumn('status', __('Status'));
    $this->addColumn('bulkactions', '<input id="check_all" type="checkbox" />');
    $this->addColumn('item', __('Item'));
    $this->addColumn('author', __('Author'));
    $this->addColumn('comment', __('Comment'));
    $this->addColumn('is_reply', __('Is reply to comment'));
    $this->addColumn('has_reply', __('Has replies'));
    $this->addColumn('date', __('Date'));

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
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=comments&amp;replyId=' . $aRow['fk_i_reply_id'] . '">' . __('Show parent comment') . '</a>';
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=comments&amp;action=comment_edit&amp;id=' . $aRow['fk_i_reply_id'] . '">' . __('Edit parent comment') . '</a>';
        }

        if($aRow['i_reply_count'] > 0) {
          $options_more[] = '<a href="' . osc_admin_base_url(true) . '?page=comments&amp;replyId=' . $aRow['pk_i_id'] . '">' . __('Show replies') . '</a>';
        }
        
        $options_more[] = '<a onclick="return delete_dialog(\'' . $aRow['pk_i_id'] . '\');" href="' . osc_admin_base_url(true) . '?page=comments&amp;action=delete&amp;id=' . $aRow['pk_i_id'] .'" id="dt_link_delete">' . __('Delete') . '</a>';

        $options[] = '<a href="' . osc_admin_base_url(true) . '?page=comments&amp;action=comment_edit&amp;id=' . $aRow['pk_i_id'] . '" id="dt_link_edit">' . __('Edit') . '</a>';
        if($aRow['b_active']) {
          $options[] = '<a href="' . osc_admin_base_url(true) . '?page=comments&amp;action=status&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url . '&amp;value=INACTIVE">' . __('Deactivate') . '</a>';
        } else {
          $options[] = '<a href="' . osc_admin_base_url(true) . '?page=comments&amp;action=status&amp;id=' . $aRow['pk_i_id'] . '&amp;' . $csrf_token_url .'&amp;value=ACTIVE">' . __('Activate') . '</a>';
        }

        // more actions
        $moreOptions = '<li class="show-more">'.PHP_EOL.'<a href="#" class="show-more-trigger">'. __('Show more') .'...</a>'. PHP_EOL .'<ul>'. PHP_EOL;
        foreach($options_more as $actual) {
          $moreOptions .= '<li>'.$actual . '</li>' . PHP_EOL;
        }
        
        $moreOptions .= '</ul>'. PHP_EOL .'</li>'.PHP_EOL;

        // create list of actions
        $auxOptions = '<ul>'.PHP_EOL;
        foreach($options as $actual) {
          $auxOptions .= '<li>'.$actual.'</li>'.PHP_EOL;
        }
        
        $auxOptions  .= $moreOptions;
        $auxOptions  .= '</ul>'.PHP_EOL;

        $actions = '<div class="actions">'.$auxOptions.'</div>'.PHP_EOL;

        $status = $this->get_row_status($aRow);
        
        $row['id'] = $aRow['pk_i_id'];
        $row['status-border'] = '';
        $row['status'] = $status['text'];
        $row['bulkactions'] = '<input type="checkbox" name="id[]" value="' . $aRow['pk_i_id']  . '" />';
        $row['item'] = '<a target="_blank" href="' . osc_item_url() . '">' . osc_item_title() . '</a>'. $actions;

        if($aRow['fk_i_user_id'] !== null) {
          $user = osc_get_user_row($aRow['fk_i_user_id']);
          
          if(isset($user['pk_i_id'])) {
            $aRow['s_author_name'] = '<a target="_blank" href="' . osc_admin_base_url(true) . '?page=users&action=edit&id=' . $user['pk_i_id'] . '">' . $user['s_name'] . '</a>';
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
        
        $row['date'] = osc_format_date($aRow['dt_pub_date']);

        $row = osc_apply_filter('comments_processing_row', $row, $aRow);

        $this->addRow($row);
        $this->rawRows[] = $aRow;
      }
    }
  }

  /**
   * @param $_get
   */
  private function getDBParams($_get) {
    $this->order_by['column_name'] = 'c.dt_pub_date';
    $this->order_by['type'] = 'desc';
    $this->showAll = Params::getParam('showAll') != 'off';

    foreach($_get as $k => $v) {
      if(($k === 'itemId') && !empty($v)) {
        $this->itemId = (int) $v;
      }

      if(($k === 'replyId') && !empty($v)) {
        $this->replyId = (int) $v;
      }
      
      if($k === 'iDisplayStart') {
        $this->start = (int) $v;
      }
      
      if($k === 'iDisplayLength') {
        $this->limit = (int) $v;
      }
    }

    // set start and limit using iPage param
    $start = ((int)Params::getParam('iPage')-1) * $_get['iDisplayLength'];

    $this->start = (int) $start;
    $this->limit = (int) $_get['iDisplayLength'];
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