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
 * Model database for ItemComment table
 *
 * @package Osclass
 * @subpackage Model
 * @since unknown
 */
class ItemComment extends DAO {
  /**
   * It references to self object: ItemComment.
   * It is used as a singleton
   *
   * @access private
   * @since unknown
   * @var Item
   */
  private static $instance;

  /**
   * It creates a new ItemComment object class ir if it has been created
   * before, it return the previous object
   *
   * @access public
   * @since unknown
   * @return ItemComment
   */
  public static function newInstance() {
    if(!self::$instance instanceof self) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  /**
   * Set data related to t_item_comment table
   */
  public function __construct() {
    parent::__construct();
    $this->setTableName('t_item_comment');
    $this->setPrimaryKey('pk_i_id');
    $array_fields = array(
      'pk_i_id',
      'fk_i_item_id',
      'dt_pub_date',
      's_title',
      's_author_name',
      's_author_email',
      's_body',
      'i_rating',
      'b_enabled',
      'b_active',
      'b_spam',
      'fk_i_user_id',
      'fk_i_reply_id'
   );
    
    $this->setFields($array_fields);
  }


  /**
   * Get the result match of the primary key passed by parameter
   *
   * @access public
   * @since  unknown
   *
   * @param int $id Comment id
   *
   * @return array|bool
   * @throws \Exception
   */
  public function findByPrimaryKey($id) {
    if(!is_numeric($id) || $id == null || $id <= 0) {
      return false;
    }
    
    $this->dao->select('c.*, h.i_reply_count');
    $this->dao->from($this->getTableName().' c');
    $this->dao->join('(SELECT count(pk_i_id) as i_reply_count FROM ' . DB_TABLE_PREFIX.'t_item_comment WHERE fk_i_reply_id = ' . $id . ') h', '1=1', 'INNER');

    $this->dao->where('c.pk_i_id', $id);
    $result = $this->dao->get();

    if($result === false) {
      return false;
    }

    if($result->numRows() == 0) {
      return false;
    }

    $comment = $result->row();

    if ($comment !== null) {
      return $comment;
    }
    
    return false;
  }
  
  /**
   * Searches for comments information, given an item id.
   *
   * @access public
   * @since unknown
   * @param integer $id
   * @return array
   */
  public function findByItemIDAll($id) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('fk_i_item_id', $id);
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->result();
  }

  /**
   * Searches for comments information, given an item id, page and comments per page.
   *
   * @access public
   * @since  unknown
   *
   * @param integer $id
   * @param integer $page
   * @param null  $commentsPerPage
   *
   * @return array
   */
  public function findByItemID($id, $page = null, $commentsPerPage = null) {
    $result = array();
    if($page == null) { 
      $page = osc_item_comments_page();
    }
    
    if($page == '') {
      $page = 0;
    }

    if($commentsPerPage == null) {
      $commentsPerPage = osc_comments_per_page();
    }

    $this->dao->select();
    $this->dao->from($this->getTableName());
    $conditions = array('fk_i_item_id' => $id, 'b_active' => 1, 'b_enabled' => 1);
    $this->dao->where($conditions);
    
    if(osc_enable_comment_reply()) {
      $this->dao->where('fk_i_reply_id is null');
    }

    if($page !== 'all' && $commentsPerPage > 0) {
      $this->dao->limit($page * $commentsPerPage , $commentsPerPage);
    }

    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    $data = $result->result();
    $output = array();
    
    if(osc_enable_comment_reply()) {
      if(is_array($data) && count($data) > 0) {
        foreach($data as $d) {
          $replies = $this->findByReplyId($d['pk_i_id']);
          $d['replies'] = $replies;
          $d['i_reply_count'] = (is_array($replies) ? count($replies) : 0);
          
          $output[] = $d;
        }
      }
      
      return $output;
    }
    
    return $data;
  }

  /**
   * Return total of comments, given an item id. (active & enabled)
   *
   * @access public
   * @since unknown
   * @deprecated since 2.3
   * @see ItemComment::totalComments
   * @param integer $id
   * @return integer
   */
  public function total_comments($id) {
    return $this->totalComments($id);
  }

  /**
   * Return total of comments, given an item id. (active & enabled)
   *
   * @access public
   * @since 2.3
   * @param integer $id
   * @return integer
   */
  public function totalComments($id) {
    $this->dao->select('count(pk_i_id) as total');
    $this->dao->from($this->getTableName());
    $conditions = array('fk_i_item_id' => $id, 'b_active' => 1, 'b_enabled' => 1);
    
    if(osc_enable_comment_reply()) {
      $this->dao->where('fk_i_reply_id is null');
    }
    
    $this->dao->where($conditions);
    $this->dao->groupBy('fk_i_item_id');
    $result = $this->dao->get();

    if($result == false) {
      return false;
    } else if($result->numRows() === 0) {
      return 0;
    } else {
      $total = $result->row();
      return $total['total'];
    }
  }
  
  
  /**
   * Return average rating, given an item id. (active & enabled)
   *
   * @access public
   * @since 4.2
   * @param integer $id
   * @return integer
   */
  public function averageRating($id) {
    $this->dao->select('avg(i_rating) as rating');
    $this->dao->from($this->getTableName());
    $conditions = array('fk_i_item_id' => $id, 'b_active' => 1, 'b_enabled' => 1);
    $this->dao->where($conditions);
    $this->dao->where('i_rating is not null');
    $this->dao->groupBy('fk_i_item_id');
    $result = $this->dao->get();

    if($result == false) {
      return false;
    } else if($result->numRows() === 0) {
      return 0;
    } else {
      $total = $result->row();
      return $total['rating'];
    }
  }


  /**
   * Return number of ratings per user and item
   *
   * @access public
   * @since 8.1.0
   * @param integer $item_d
   * @param integer $user_id
   * @param string $user_email
   * @return integer
   */
  public function countItemUserRatings($item_id, $user_id = 0, $user_email = '') {
    $this->dao->select('count(i_rating) as rating');
    $this->dao->from($this->getTableName());
    //$conditions = array('fk_i_item_id' => $item_id, 'b_active' => 1, 'b_enabled' => 1);
    $conditions = array('fk_i_item_id' => $item_id);
    $this->dao->where($conditions);
    $this->dao->where('i_rating is not null');
    
    if($user_id > 0) {
      $this->dao->where('fk_i_user_id', $user_id);
    } else {
      $this->dao->where('s_author_email', $user_email);
    }
    
    $result = $this->dao->get();

    if($result == false) {
      return false;
    } else if($result->numRows() === 0) {
      return 0;
    } else {
      $total = $result->row();
      return $total['rating'];
    }
  }

  /**
   * Searches for comments information, given an user id.
   *
   * @access public
   * @since unknown
   * @param integer $id
   * @return array
   */
  public function findByAuthorID($id) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $conditions = array('fk_i_user_id' => $id, 'b_active' => 1, 'b_enabled' => 1);
    $this->dao->where($conditions);
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->result();
  }


  /**
   * Searches for comments information, given a reply id.
   *
   * @access public
   * @since unknown
   * @param integer $id
   * @return array
   */
  public function findByReplyId($id) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $conditions = array('fk_i_reply_id' => $id, 'b_active' => 1, 'b_enabled' => 1);
    $this->dao->where($conditions);
    
    $this->dao->orderBy('dt_pub_date', 'ASC');

    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    return $result->result();
  }
  
  /**
   * Searches for comments information, given an user id.
   *
   * @access public
   * @since unknown
   * @param integer $itemId
   * @return array
   */
  public function getAllComments($itemId = null) {
    $this->dao->select('c.*');
    $this->dao->from($this->getTableName().' c');
    $this->dao->from(DB_TABLE_PREFIX.'t_item i');

    $conditions = array('i.pk_i_id' => $itemId, 'c.fk_i_item_id' => $itemId);
    if ($itemId === NULL) {
      $conditions = 'c.fk_i_item_id = i.pk_i_id';
    }

    $this->dao->where($conditions);
    $this->dao->orderBy('c.dt_pub_date','DESC');
    $aux = $this->dao->get();
    
    if($aux == false) {
      return array();
    }
    
    $comments = $aux->result();

    return $this->extendData($comments);
  }

  /**
   * Searches for last comments information, given a limit of comments.
   *
   * @access public
   * @since unknown
   *
   * @param integer $num
   *
   * @return array|bool
   */
  public function getLastComments($num) {
    if (!(int) $num) {
      return false;
    }

    $lang = osc_current_user_locale();

    $this->dao->select('c.*,c.s_title as comment_title, d.s_title');
    $this->dao->from($this->getTableName().' c');
    $this->dao->join(DB_TABLE_PREFIX.'t_item i', 'i.pk_i_id = c.fk_i_item_id');
    $this->dao->join(DB_TABLE_PREFIX.'t_item_description d', 'd.fk_i_item_id = c.fk_i_item_id');
    $this->dao->orderBy('c.pk_i_id', 'DESC');
    $this->dao->limit(0,$num);

    $result = $this->dao->get();
    if($result == false) {
      return array();
    }
    
    return $result->result();
  }

  /**
   * Extends an array of comments with title / description
   *
   * @access private
   * @since unknown
   * @param array $items
   * @return array
   */
  private function extendData($items) {
    $prefLocale = osc_current_user_locale();

    $results = array();
    foreach($items as $item) {
      $this->dao->select();
      $this->dao->from(DB_TABLE_PREFIX.'t_item_description');
      $this->dao->where('fk_i_item_id', $item['fk_i_item_id']);
      $aux = $this->dao->get();
      if($aux == false) {
        $descriptions = array();
      } else {
        $descriptions = $aux->result();
      }

      $item['locale'] = array();
      foreach($descriptions as $desc) {
        $item['locale'][$desc['fk_c_locale_code']] = $desc;
      }
      if(isset($item['locale'][$prefLocale])) {
        $item['s_title'] = $item['locale'][$prefLocale]['s_title'];
        $item['s_description'] = $item['locale'][$prefLocale]['s_description'];
      } else {
        $data = current($item['locale']);
        $item['s_title'] = $data['s_title'];
        $item['s_description'] = $data['s_description'];
        unset($data);
      }
      $results[] = $item;
    }
    return $results;
  }

  /**
   * Return comments on command
   *
   * @access public
   * @since 2.4
   * @param int item's ID or null
   * @param int start
   * @param int limit
   * @param string order by
   * @param string order
   * @param bool $all true returns all comments, false, returns comments
   *    which not display at frontend
   * @return array
   */
  public function search($itemId = null, $start = 0, $limit = 10, $order_by = 'c.pk_i_id', $order = 'DESC', $all = true, $replyId = null) {
    $this->dao->select('c.*, r.s_title as reply_title, h.i_reply_count');
    $this->dao->from($this->getTableName().' c');
    $this->dao->join(DB_TABLE_PREFIX.'t_item i', 'i.pk_i_id = c.fk_i_item_id');
    $this->dao->join(DB_TABLE_PREFIX.'t_item_comment r', 'r.pk_i_id = c.fk_i_reply_id', 'LEFT OUTER');
    $this->dao->join('(SELECT fk_i_reply_id, count(pk_i_id) as i_reply_count FROM ' . DB_TABLE_PREFIX.'t_item_comment WHERE fk_i_reply_id IS NOT NULL GROUP BY fk_i_reply_id) h', 'h.fk_i_reply_id = c.pk_i_id', 'LEFT OUTER');

    $conditions = array('i.pk_i_id' => $itemId, 'c.fk_i_item_id' => $itemId);
    if ($itemId === null) {
      $conditions = 'c.fk_i_item_id = i.pk_i_id';
    }

    $this->dao->where($conditions);

    if($replyId !== null) {
      $this->dao->where('c.fk_i_reply_id', $replyId);
    }
    
    if(!$all) {
      $auxCond = '(c.b_enabled = 0 OR c.b_active = 0 OR c.b_spam = 1)';
      $this->dao->where($auxCond);
    }

    $this->dao->orderBy($order_by, $order);
    $this->dao->limit($start, $limit);

    $aux = $this->dao->get();
    
    if($aux == false) {
      return array();
    }
    return $aux->result();
  }

  /**
   * Count the number of comments
   *
   * @param int item's ID or null
   *
   * @return array|int
   */
  public function count($itemId = null, $replyId = null) {
    $this->dao->select('COUNT(*) AS numrows');
    $this->dao->from($this->getTableName().' c');
    $this->dao->from(DB_TABLE_PREFIX.'t_item i');

    $conditions = array('i.pk_i_id' => $itemId, 'c.fk_i_item_id' => $itemId);
    if ($itemId === null) {
      $conditions = 'c.fk_i_item_id = i.pk_i_id';
    }
    
    if ($replyId !== null) {
      $this->dao->where('c.fk_i_reply_id', $replyId);
    }

    $this->dao->where($conditions);
    $aux = $this->dao->get();
    
    if($aux == false) {
      return array();
    }
    
    $row = $aux->row();
    return $row['numrows'];
  }

  /**
   * @param null $aConditions
   *
   * @return bool|int
   */
  public function countAll($aConditions = null) {
    $this->dao->select('count(*) as total');
    $this->dao->from($this->getTableName().' c');
    $this->dao->from(DB_TABLE_PREFIX.'t_item i');

    $this->dao->where('c.fk_i_item_id = i.pk_i_id');
    if ($aConditions !== null) {
      $this->dao->where($aConditions);
    }
    
    $result = $this->dao->get();

    if($result == false) {
      return false;
    } else if($result->numRows() === 0) {
      return 0;
    } else {
      $total = $result->row();
      return $total['total'];
    }
  }
}

/* file end: ./oc-includes/osclass/model/ItemComment.php */