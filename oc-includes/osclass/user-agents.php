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

/*
// Old list
$user_agents = array(
  'MSIE ([0-9].*)$',
  '^Mozilla/[0-9].([^c][^o][^m].)$',
  '^Mozilla/5.*Gecko',
  '^Mozilla/5.(PLAYSTATION.)',
  'Safari',
  'Omni',
  'iCab',
  'Opera',
  'Konqueror',
  'AOL',
  'MSN',
  'WebTV',
  'BlackBerry.*'
);
*/

// Defined in utils.php, kept for retro-compatibility
$user_agents = osc_user_agents();


