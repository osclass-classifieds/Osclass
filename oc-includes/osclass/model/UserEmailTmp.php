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
 *
 */
class UserEmailTmp extends DAO
{
  /**
   *
   * @var
   */
  private static $instance;

  /**
   * @return \type|\UserEmailTmp
   */
  public static function newInstance()
  {
    if( !self::$instance instanceof self ) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  /**
   *
   */
  public function __construct()
  {
    parent::__construct();
    $this->setTableName('t_user_email_tmp');
    $this->setPrimaryKey('fk_i_user_id');
    $this->setFields( array('fk_i_user_id','s_new_email','dt_date') );
  }

  /**
   *
   * @access public
   * @since unknown
   *
   * @param $userEmailTmp
   *
   * @return array|bool
   */
  public function insertOrUpdate($userEmailTmp) {

    $status = $this->dao->insert($this->getTableName(), array('fk_i_user_id' => $userEmailTmp['fk_i_user_id'], 's_new_email' => $userEmailTmp['s_new_email'], 'dt_date' => date('Y-m-d H:i:s')));
    if (!$status) {
      return $this->dao->update($this->getTableName(), array('s_new_email' => $userEmailTmp['s_new_email'], 'dt_date' => date('Y-m-d H:i:s')), array('fk_i_user_id' => $userEmailTmp['fk_i_user_id']));
    }
    return false;
  }
}

/* file end: ./oc-includes/osclass/model/UserEmailTmp.php */