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
 * Model database for Admin table
 *
 * @package Osclass
 * @subpackage Model
 * @since unknown
 */
class Admin extends DAO {
  /**
   * It references to self object: Admin.
   * It is used as a singleton
   *
   * @access private
   * @since unknown
   * @var Admin
   */
  private static $instance;

  /**
   * array for save currencies
   * @var array
   */
  private $cachedAdmin;

  /**
   * @return \Admin
   */
  public static function newInstance() {
    if(!self::$instance instanceof self){
      self::$instance = new self;
    }
    return self::$instance;
  }

  /**
   * Set data from t_admin table
   */
  public function __construct() {
    parent::__construct();
    $this->setTableName('t_admin');
    $this->setPrimaryKey('pk_i_id');

    $return = $this->dao->query('SHOW COLUMNS FROM ' . $this->getTableName() . ' where Field = "b_moderator"');

    if(osc_version() < 420) {
      $this->setFields(array('pk_i_id', 's_name', 's_username', 's_password', 's_email', 's_secret'));
      
    } else if($return !== false && $return->numRows() > 0) {
      $this->setFields(array('pk_i_id', 's_name', 's_username', 's_password', 's_email', 's_secret', 'i_login_fails', 'dt_login_fail_date', 'b_moderator', 's_moderator_access'));

    } else {
      $this->setFields(array('pk_i_id', 's_name', 's_username', 's_password', 's_email', 's_secret', 'i_login_fails', 'dt_login_fail_date'));
    }
  }

  /**
   * @param string $id
   * @param null   $locale
   *
   * @return mixed|string
   */
  public function findByPrimaryKey($id, $locale = null) {
    if($id == ''){
      return '';
    }
    
    if(isset($this->cachedAdmin[$id])) {
      return $this->cachedAdmin[$id];
    }
    
    $this->cachedAdmin[$id] = parent::findByPrimaryKey($id);
    return $this->cachedAdmin[$id];
  }

  /**
   * Searches for admin information, given an email address.
   * If email not exist return false.
   *
   * @access public
   * @since unknown
   *
   * @param string $email
   *
   * @return array|bool
   */
  public function findByEmail($email) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('s_email', $email);
    $result = $this->dao->get();

    if($result->numRows == 0){
      return false;
    }

    return $result->row();
  }

  /**
   * Searches for admin information, given a username.
   * If admin not exist return false.
   *
   * @access public
   * @since unknown
   *
   * @param string $username
   *
   * @return array|bool
   */
  public function findByusername($username) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $this->dao->where('s_username', $username);
    $result = $this->dao->get();

    if($result->numRows == 0){
      return false;
    }

    return $result->row();
  }

  /**
   * Searches for admin information, given a username and password
   * If credential don't match return false.
   *
   * @access public
   * @since unknown
   *
   * @param string $username
   * @param string $password
   *
   * @return array|bool
   */
  public function findByCredentials($username, $password) {
    $user = $this->findByusername($username);
    
    if($user !== false && isset($user['s_password']) && osc_verify_password($password, $user['s_password'])) {
      return $user;
    }
    
    return false;
  }

  /**
   * Update admin login fail fields
   *
   * @access public
   * @since unknown
   * @param integer $id
   * @param integer $login_fails_count
   * @return false on failed insert, true on successful insert
   */
  public function updateLoginFailed($id, $login_fails_count, $date = '') {
    if($date == '') {
      $date = date('Y-m-d H:i:s');
    }
    
    if($login_fails_count <= 0) {
      $date = null;
    }

    return $this->dao->update($this->tableName, array('i_login_fails' => $login_fails_count, 'dt_login_fail_date' => $date), array('pk_i_id' => $id));
  }

  /**
   * Searches for admin information, given a admin id and secret.
   * If credential don't match return false.
   *
   * @access public
   * @since unknown
   *
   * @param integer $id
   * @param string  $secret
   *
   * @return array|bool
   */
  public function findByIdSecret($id, $secret) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $conditions = array('pk_i_id' => $id, 's_secret' => $secret);
    $this->dao->where($conditions);
    $result = $this->dao->get();

    if($result->numRows == 0){
      return false;
    }

    return $result->row();
  }

  /**
   * Searches for admin information, given a admin id and password.
   * If credential don't match return false.
   *
   * @access public
   * @since unknown
   *
   * @param integer $id
   * @param string  $password
   *
   * @return array|bool
   */
  public function findByIdPassword($id, $password) {
    $this->dao->select();
    $this->dao->from($this->getTableName());
    $conditions = array('pk_i_id' => $id, 's_password' => $password);
    $this->dao->where($conditions);
    $result = $this->dao->get();

    if($result->numRows == 0) {
      return false;
    }

    return $result->row();
  }

  /**
   * Perform a batch delete (for more than one admin ID)
   *
   * @access public
   * @since 2.3.4
   * @param array $id
   * @return boolean
   */
  public function deleteBatch($id) {
    $this->dao->from($this->getTableName());
    $this->dao->whereIn('pk_i_id', $id);
    
    return $this->dao->delete();
  }
}

/* file end: ./oc-includes/osclass/model/Admin.php */