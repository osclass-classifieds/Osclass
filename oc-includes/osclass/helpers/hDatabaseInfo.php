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
* Helper Database Info
* @package Osclass
* @subpackage Helpers
* @author Osclass
*/

/**
 * Gets database name
 *
 * @return string
 */
function osc_db_name() {
  return DB_NAME;
}

/**
 * Gets database host
 *
 * @return string
 */
function osc_db_host() {
  return DB_HOST;
}

/**
 * Gets database user
 *
 * @return string
 */
function osc_db_user() {
  return DB_USER;
}

/**
 * Gets database password
 *
 * @return string
 */
function osc_db_password() {
  return DB_PASSWORD;
}
