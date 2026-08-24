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
 * Page DAO
 */
class Page extends DAO {
  /**
   *
   * @var type
   */
  private static $instance;

  /**
   * @return \Page|\type
   */
  public static function newInstance() {
    if(!self::$instance instanceof self) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  /**
   *
   */
  public function __construct() {
    parent::__construct();
    $this->setTableName('t_pages');
    $this->setPrimaryKey('pk_i_id');
    $array_fields = array(
      'pk_i_id',
      's_internal_name',
      'b_indelible',
      'b_link',
      'b_index',
      'i_visibility',
      'dt_pub_date',
      'dt_mod_date',
      'i_order',
      's_meta'
    );
    
    $this->setFields($array_fields);
  }

  /**
   * Find a page by page id.
   *
   * @access public
   * @since unknown
   * @param int $id Page id.
   * @param string $locale By default is null but you can specify locale code.
   * @return array Page information. If there's no information, return an empty array.
   */
  public function findByPrimaryKey($id, $locale = null) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('pk_i_id', $id);
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    if($result->numRows() == 0) {
      return array();
    }

    $row = $result->row();

    // page_description
    $this->dao->select();
    $this->dao->from($this->getDescriptionTableName());
    $this->dao->where('fk_i_pages_id', $id);
    if (null !== $locale) {
      $this->dao->where('fk_c_locale_code', $locale);
    }
    $result   = $this->dao->get();
    $aRows = $result->result();

    $row['locale'] = array();
    foreach($aRows as $r) {
      $row['locale'][$r['fk_c_locale_code']] = $r;
    }

    return $row;
  }

  /**
   * Find a page by internal name.
   *
   * @access public
   * @since unknown
   * @param string $intName Internal name of the page to find.
   * @param string $locale Locale string.
   * @return array It returns page fields. If it has no results, it returns an empty array.
   */
  public function findByInternalName($intName, $locale = null) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('s_internal_name', $intName);
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    if($result->numRows() == 0){
      return array();
    }

    $row = $result->row();
    return $this->extendDescription($row, $locale);
  }

  /**
   * Find a page by order.
   *
   * @access public
   * @since  unknown
   *
   * @param    $order
   * @param null $locale
   *
   * @return array It returns page fields. If it has no results, it returns an empty array.
   */
  public function findByOrder($order, $locale = null) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    
    $array_where = array(
      'i_order' => $order,
      'b_indelible' => 0
    );
    
    $this->dao->where($array_where);
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    if($result->numRows() == 0) {
      return array();
    }

    $row  = $result->row();
    $result = $this->extendDescription($row, $locale);
    return $result;
  }

  /**
   * Get all the pages with the parameters you choose.
   *
   * @access public
   * @since  unknown
   *
   * @param bool   $indelible true if the page is indelible
   * @param null   $b_link
   * @param null   $b_index
   * @param string $locale
   * @param int  $start
   * @param int  $limit
   *
   * @return array Return all the pages that have been found with the criteria selected. If there's no pages, the
   *              result is an empty array.
   */
  public function listAll($indelible = null, $b_link = null, $locale = null, $start = null, $limit = null, $b_index = null, $cache_enabled = true) {
    $key = md5(osc_base_url().'Page::listAll' . (string)$indelible . (string)$b_link . (string)$locale . (string)$start . (string)$limit . (string)$b_index . (string)osc_current_user_locale());
    $found = null;
    $cache = (OC_ADMIN ? false : osc_cache_get($key, $found));
    
    if(OC_ADMIN || $cache_enabled === false || $cache === false) {
      $this->dao->select();
      $this->dao->from($this->getTableName());
      
      if($indelible !== null) {
        $this->dao->where('b_indelible', $indelible);
      }
      
      if($b_link !== null) {
        $this->dao->where('b_link', $b_link);
      }
      
      if($b_index !== null) {
        $this->dao->where('b_index', $b_index);
      }
      
      $this->dao->orderBy('i_order', 'ASC');
      
      if($limit !== null) {
        $this->dao->limit($limit, $start);
      }

      osc_run_hook('static_pages_list_all', $this);
      
      $result = $this->dao->get();
      
      if($result) {
        $aPages = $result->result();

        if(count($aPages) == 0) {
          return array();
        }

        $resultPages = array();
        
        foreach($aPages as $aPage) {
          if(!osc_is_backoffice()) {
            $visibility = (isset($aPage['i_visibility']) ? $aPage['i_visibility'] : 0);
            
            if($visibility > 0) {
              $logged_user = osc_logged_user();
              
              if($aPage['i_visibility'] == 1 && !osc_is_web_user_logged_in()) {
                continue;
              } else if ($aPage['i_visibility'] == 2 && (!osc_is_web_user_logged_in() || !isset($logged_user['b_company']) || $logged_user['b_company'] == 1)) {
                continue;
              } else if ($aPage['i_visibility'] == 3 && (!osc_is_web_user_logged_in() || !isset($logged_user['b_company']) || $logged_user['b_company'] == 0)) {
                continue;
              } else if ($aPage['i_visibility'] == 4 && !osc_is_admin_user_logged_in()) {
                continue;
              } else if ($aPage['i_visibility'] == 5) {
                continue;
              }
            }
          }
          
          $data = $this->extendDescription($aPage, $locale);
          
          if(count($data) > 0) {
            $resultPages[] = $data;
          }
          
          unset($data);
        }

        $output = $resultPages;
        
      } else {
        $output = array();
      }
      
      if(!OC_ADMIN && $cache_enabled === true) {
        osc_cache_set($key, $output, OSC_CACHE_TTL);
      }
      
      return $output;
    }
    
    return $cache;
  }

  /**
   * Return number of all pages, or only number of indelible pages
   *
   * @access public
   * @since 3.0
   * @param int $indelible
   * @return int
   */
  public function count($indelible = null) {
    $this->dao->select('count(*) as total');
    $this->dao->from($this->getTableName());
    
    if($indelible !== null) {
      $this->dao->where('b_indelible', $indelible);
    }

    $result = $this->dao->get();
    
    if($result) {
      $aPages = $result->result();
      return $aPages[0]['total'];
    } else {
      return 0;
    }

  }

  /**
   * An array with data of some page, returns the title and description in every language available
   *
   * @access public
   * @since  unknown
   * @param array $aPage
   * @param null  $locale
   * @return array Page information, title and description in every language available
   */
  public function extendDescription($aPage, $locale = null) {
    $this->dao->select();
    $this->dao->from($this->getDescriptionTableName());
    $this->dao->where('fk_i_pages_id', $aPage['pk_i_id']);
    
    if ($locale !== null) {
      $this->dao->where('fk_c_locale_code', $locale);
    }
    
    $results = $this->dao->get();
    
    if($results === false) {
      return array(); 
    }
    
    $aDescriptions = $results->result();

    if(count($aDescriptions) == 0) {
      return array();
    }

    $aPage['locale'] = array();
    foreach($aDescriptions as $description) {
      if(!empty($description['s_title']) || !empty($description['s_text'])) {
        $aPage['locale'][$description['fk_c_locale_code']] = $description;
      }
    }

    return $aPage;
  }

  /**
   * Delete a page by id number.
   *
   * @access public
   * @since  unknown
   * @param int $id Page id which is going to be deleted
   * @return mixed @return mixed It return the number of affected rows if the delete has been  correct or false if nothing has been modified
   */
  public function deleteByPrimaryKey($id) {
    $row = $this->findByPrimaryKey($id);
    $order = $row['i_order'];

    $this->reOrderPages($order);

    $this->dao->delete($this->getDescriptionTableName(), array('fk_i_pages_id' => $id));
    return $this->dao->delete($this->tableName, array('pk_i_id' => $id));
  }

  /**
   * Delete a page by internal name.
   *
   * @access public
   * @since unknown
   * @param string $intName Page internal name which is going to be deleted
   * @return bool True on successful removal, false on failure
   */
  public function deleteByInternalName($intName) {
    $row = $this->findByInternalName($intName);
    return $this->deleteByPrimaryKey($row['pk_i_id']);
  }

  /**
   * Order pages from $order
   *
   * @access private
   * @since  unknown
   *
   * @param int $order
   *
   * @return int|mixed
   */
  private function reOrderPages($order) {
    $aPages = $this->listAll(false);
    $arows = 0;
    
    foreach($aPages as $page){
      if($page['i_order'] > $order){
        $new_order = $page['i_order']-1;
        $arows += $this->dao->update($this->tableName, array('i_order' => $new_order), array('pk_i_id' => $page['pk_i_id']));
      }
    }
    return $arows;
  }

  /**
   * Find previous page
   *
   * @access public
   * @since  2.4
   *
   * @param int $order
   *
   * @return array
   */
  public function findPrevPage($order) {
    $this->dao->select();
    $this->dao->from($this->tableName);
    $this->dao->where('b_indelible', 0);
    $this->dao->where('i_order < ' . $order);
    $this->dao->orderBy('i_order', 'DESC');
    $this->dao->limit(1);
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    if($result->numRows() == 0) {
      return array();
    }
    return $result->row();
  }

  /**
   * Find next page
   *
   * @access public
   * @since  2.4
   *
   * @param int $order
   *
   * @return array
   */
  public function findNextPage($order) {
    $this->dao->select();
    $this->dao->from($this->tableName);
    $this->dao->where('b_indelible', 0);
    $this->dao->where('i_order > ' . $order);
    $this->dao->orderBy('i_order', 'ASC');
    $this->dao->limit(1);
    $result = $this->dao->get();

    if($result == false) {
      return array();
    }

    if($result->numRows() == 0) {
      return array();
    }
    return $result->row();
  }

  /**
   * Insert a new page. You have to pass all the parameters
   *
   * @access public
   * @since unknown
   * @param array $aFields Fields to be inserted in pages table
   * @param array $aFieldsDescription An array with the titles and descriptions in every language.
   * @return bool True if the insert has been done well and false if not.
   */
  public function insert($aFields, $aFieldsDescription = null) {
    $this->dao->select('MAX(i_order) as o');
    $this->dao->from($this->tableName);
    $results = $this->dao->get();
    $lastPage = $results->row();

    $order = $lastPage['o'];
    if (null === $order) {
      $order = -1;
    }

    if(!isset($aFields['b_link'])) {
      $aFields['b_link'] = 0;
    }
    
    if(!isset($aFields['i_visibility'])) {
      $aFields['i_visibility'] = 0;
    }

    if($aFields['b_indelible'] == 1) {
      $aFields['i_visibility'] = 0;
    }
    
    if($aFields['b_link'] == '') {
      if($aFields['b_indelible'] == 1) {
        $aFields['b_link'] = 0;
      }
    }

    if(!isset($aFields['b_index'])) {
      $aFields['b_index'] = 1;
    }

    if($aFields['b_index'] == '') {
      if($aFields['b_indelible'] == 1) {
        $aFields['b_index'] = 0;
      }
    }

    $this->dao->insert($this->tableName, array(
      's_internal_name' => $aFields['s_internal_name']
      ,'b_indelible' => $aFields['b_indelible']
      ,'dt_pub_date' => date('Y-m-d H:i:s')
      ,'dt_mod_date' => date('Y-m-d H:i:s')
      ,'i_order' => $order + 1
      ,'s_meta' => @$aFields['s_meta']
      ,'b_link' => $aFields['b_link']
      ,'b_index' => $aFields['b_index']
      ,'i_visibility' => $aFields['i_visibility']
   ));


    $id = $this->dao->insertedId();

    if($this->dao->affectedRows() == 0) {
      return false;
    }

    foreach($aFieldsDescription as $k => $v) {
      $affected_rows = $this->insertDescription($id, $k, $v['s_title'], $v['s_text']);
      if(!$affected_rows) {
        return false;
      }
    }

    //return true;
    return $id;
  }

  /**
   * Insert the content (title and description) of a page.
   *
   * @access private
   * @since unknown
   * @param int $id Id of the page, it would be the foreign key
   * @param string $locale Locale code of the language
   * @param string $title Text to be inserted in s_title
   * @param string $text Text to be inserted in s_text
   * @return bool True if the insert has been done well and false if not.
   */
  private function insertDescription($id, $locale, $title, $text) {

    $this->dao->insert($this->getDescriptionTableName() ,array(
      'fk_i_pages_id' => $id
      ,'fk_c_locale_code' => $locale
      ,'s_title' => $title
      ,'s_text' => $text
    ));

    return !($this->dao->affectedRows() == 0);
  }

  /**
   * Update the content (title and description) of a page
   *
   * @access public
   * @since unknown
   * @param int $id Id of the page id is going to be modified
   * @param string $locale Locale code of the language
   * @param string $title Text to be updated in s_title
   * @param string $text Text to be updated in s_text
   * @return int Number of affected rows.
   */
  public function updateDescription($id, $locale, $title, $text) {
    $conditions = array('fk_c_locale_code' => $locale, 'fk_i_pages_id' => $id);
    $exist= $this->existDescription($conditions);

    if(!$exist) {
      return $this->insertDescription($id , $locale , $title , $text);
    }

    return $this->dao->update(
      $this->getDescriptionTableName(),
      array(
        's_title' => $title
        ,'s_text' => $text
      ), array(
        'fk_c_locale_code' => $locale
        ,'fk_i_pages_id' => $id
      )
    );
  }

  /**
   * Check if depending the conditions, the row exists in de DB.
   *
   * @access public
   * @since unknown
   * @param array $conditions
   * @return bool Return true if exists and false if not.
   */
  public function existDescription($conditions){
    $this->dao->select('COUNT(*) as total');
    $this->dao->from($this->getDescriptionTableName());
    
    foreach($conditions as $key => $value) {
      $this->dao->where($key, $value);
    }

    $result = $this->dao->get();
    $count = $result->row();

    return $count['total'] > 0;
  }

  /**
   * It change the internal name of a page. Here you don't check if in indelible or not the page.
   *
   * @access public
   * @since unknown
   * @param int $id The id of the page to be changed.
   * @param string $intName The new internal name.
   * @return int Number of affected rows.
   */
  public function updateInternalName($id, $intName) {
    $fields = array('s_internal_name' => $intName, 'dt_mod_date'  => date('Y-m-d H:i:s'));
    $where = array('pk_i_id' => $id);

    return $this->dao->update($this->tableName, $fields, $where);
  }

  /**
   * It changes the b_link of a page. Here you don't check if in indelible or not the page.
   *
   * @access public
   * @since unknown
   * @param int $id The id of the page to be changed.
   * @param string $bLink The show link status.
   * @return int Number of affected rows.
   */
  public function updateLink($id, $bLink) {
    $fields = array('b_link' => $bLink, 'dt_mod_date' => date('Y-m-d H:i:s'));
    $where = array('pk_i_id' => $id);

    return $this->dao->update($this->tableName, $fields, $where);
  }

  /**
   * It changes the b_index of a page. Here you don't check if in indelible or not the page.
   *
   * @access public
   * @since unknown
   * @param int $id The id of the page to be changed.
   * @param string $bIndex The show link status.
   * @return int Number of affected rows.
   */
  public function updateIndex($id, $bIndex) {
    $fields = array('b_index' => $bIndex, 'dt_mod_date' => date('Y-m-d H:i:s'));
    $where  = array('pk_i_id' => $id);

    return $this->dao->update($this->tableName, $fields, $where);
  }

  /**
   * It changes the i_visibility of a page. Here you don't check if in indelible or not the page.
   *
   * @access public
   * @since unknown
   * @param int $id The id of the page to be changed.
   * @param string $iVisibility The show link status.
   * @return int Number of affected rows.
   */
  public function updateVisibility($id, $iVisibility) {
    $fields = array('i_visibility' => $iVisibility, 'dt_mod_date' => date('Y-m-d H:i:s'));
    $where = array('pk_i_id' => $id);

    return $this->dao->update($this->tableName, $fields, $where);
  }
  
  /**
   * It change the meta field of a page.
   *
   * @access public
   * @since 3.1
   * @param int $id The id of the page to be changed.
   * @param string $meta The meta field
   * @return int Number of affected rows.
   */
  public function updateMeta($id, $meta) {
    $fields = array('s_meta' => $meta, 'dt_mod_date' => date('Y-m-d H:i:s'));
    $where = array('pk_i_id' => $id);

    return $this->dao->update($this->tableName, $fields, $where);
  }

  /**
   * Check if a page id is indelible
   *
   * @access public
   * @since unknown
   * @param int $id Page id
   * @return true if it's indelible, false in case not
   */
  public function isIndelible($id) {
    $page = $this->findByPrimaryKey($id);

    return $page['b_indelible'] == 1;
  }

  /**
   * Check if Internal Name exists with another id
   *
   * @access public
   * @since unknown
   * @param int $id page id
   * @param string $internalName page internal name
   * @return true if internal name exists, false if not
   */
  public function internalNameExists($id, $internalName) {
    $this->dao->select();
    $this->dao->from($this->tableName);
    $this->dao->where('s_internal_name', $internalName);
    $this->dao->where('pk_i_id <> '.$id);
    $result = $this->dao->get();

    return $result->numRows() > 0;
  }

  /**
   * @return string
   */
  public function getDescriptionTableName() {
    return $this->getTablePrefix() . 't_pages_description';
  }
  
  
  // Delete duplicated templates (by internal name)
  /*
  public function deleteDuplicatedEmailTemplates() {
    $query1 = "DELETE FROM {$this->getDescriptionTableName()} WHERE fk_i_pages_id in (SELECT t1.pk_i_id FROM {$this->tableName} t1 JOIN {$this->tableName} t2 ON t1.s_internal_name = t2.s_internal_name AND t1.pk_i_id > t2.pk_i_id AND t1.b_indelible = 1 AND t2.b_indelible = 1 AND t1.s_internal_name <> '')";
    $query2 = "DELETE t1 FROM {$this->tableName} t1 JOIN {$this->tableName} t2 ON t1.s_internal_name = t2.s_internal_name AND t1.pk_i_id > t2.pk_i_id AND t1.b_indelible = 1 AND t2.b_indelible = 1 AND t1.s_internal_name <> ''";

    $res1 = $this->dao->query($query1);
    $res2 = $this->dao->query($query2);
    
    return $res2;
  }
  */


  // Get all duplicated email templates
  public function getDuplicatedInternalNames() {
    $this->dao->select();
    $this->dao->from($this->tableName);
    $this->dao->where("s_internal_name IN (SELECT s_internal_name FROM {$this->tableName} WHERE s_internal_name <> '' GROUP BY s_internal_name HAVING COUNT(*) > 1)");
    $this->dao->where("s_internal_name <> ''");
    $this->dao->orderBy('s_internal_name ASC, pk_i_id DESC');

    $results = $this->dao->get();
    
    if($results == false) {
      return array();
    }

    if($results->numRows() == 0) {
      return array();
    }
    
    return $results->result();
  }

  
  // Smart delete for duplicated email templates
  public function deleteDuplicatedInternalNames() {
    $data = $this->getDuplicatedInternalNames();
    $ok_data = array();
    
    if(is_array($data) && count($data) > 0) {
      // Identify OK pages
      foreach($data as $i => $row) {
        if($this->existDescription(array('fk_i_pages_id' => $row['pk_i_id']))) {
          if(!isset($ok_data[$row['s_internal_name']]) || $ok_data[$row['s_internal_name']] <= 0) {
            $ok_data[$row['s_internal_name']] = $row['pk_i_id'];
          }
        }
      }
      
      // Remove other pages
      foreach($data as $i => $row) {
        if(!in_array($row['pk_i_id'], $ok_data)) {
          $this->deleteByPrimaryKey($row['pk_i_id']);
        }
      }
    }
  }

}

/* file end: ./oc-includes/osclass/model/Page.php */