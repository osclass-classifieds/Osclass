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


interface iObject_Cache {
  /**
   * @param     $key
   * @param     $data
   * @param int $expire
   *
   * @return mixed
   */
  public function add( $key , $data , $expire = 0 );

  /**
   * @param     $key
   * @param     $data
   * @param int $expire
   *
   * @return mixed
   */
  public function set( $key , $data , $expire = 0 );

  /**
   * @param      $key
   * @param null $found
   *
   * @return mixed
   */
  public function get( $key , &$found = null );

  /**
   * @param $key
   *
   * @return mixed
   */
  public function delete( $key );
    public function flush();
    public function stats();
    public function _get_cache(); // return string 
    public static function is_supported();


    public function __destruct();
}

/* file end: ./oc-includes/osclass/core/iObject_Cache.php */