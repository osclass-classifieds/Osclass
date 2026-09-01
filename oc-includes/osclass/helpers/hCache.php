<?php
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
 * @param   $key
 * @param   $data
 * @param int $expire
 *
 * @return bool
 * @throws \Exception
 */
function osc_cache_add( $key , $data , $expire = 0 ) {
  $key .= osc_current_user_locale();
  return Object_Cache_Factory::newInstance()->add($key, $data, $expire);
}


/**
 * @return mixed
 * @throws \Exception
 */
function osc_cache_close() {
  return Object_Cache_Factory::newInstance()->close();
}


/**
 * @param $key
 *
 * @return bool
 * @throws \Exception
 */
function osc_cache_delete( $key ) {
  $key .= osc_current_user_locale();
  return Object_Cache_Factory::newInstance()->delete($key);
}


/**
 * @return bool
 * @throws \Exception
 */
function osc_cache_flush() {
  return Object_Cache_Factory::newInstance()->flush();
}

function osc_cache_init() {
  try {
  Object_Cache_Factory::newInstance();
  } catch ( Exception $e ) {
  }
}


/**
 * @param $key
 * @param $found
 *
 * @return bool|mixed
 * @throws \Exception
 */
function osc_cache_get( $key , &$found ) {
  $key .= osc_current_user_locale();
  
  // disable cache completely in backoffice
  if(defined('OC_ADMIN') && OC_ADMIN === true) {
    return false;
  }
  
  return Object_Cache_Factory::newInstance()->get($key, $found);
}


/**
 * @param   $key
 * @param   $data
 * @param int $expire
 *
 * @return bool
 * @throws \Exception
 */
function osc_cache_set( $key , $data , $expire = 0 ) {
  $key .= osc_current_user_locale();
  return Object_Cache_Factory::newInstance()->set($key, $data, $expire);
}
