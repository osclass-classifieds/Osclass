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
 * Helper Translation
 * @package Osclass
 * @subpackage Helpers
 * @author Osclass
 */

/**
 * Translate strings
 *
 * @since unknown
 *
 * @param string $key
 * @param string $domain
 * @return string
 */
function __($key, $domain = 'core') {
  $gt = Translation::newInstance()->_get();
  $string = $gt->dgettext((string)$domain, (string)$key);
  return osc_apply_filter('gettext', (string)$string);
}

/**
 * Translate strings and echo them
 *
 * @since unknown
 *
 * @param string $key
 * @param string $domain
 */
function _e($key, $domain = 'core') {
  echo __((string)$key, (string)$domain);
}

/**
 * Translate string (flash messages)
 *
 * @since unknown
 *
 * @param string $key
 * @return string
 */
function _m($key) {
  return __((string)$key, 'messages');
}

/**
 * Retrieve the singular or plural translation of the string.
 *
 * @since 2.2
 *
 * @param string $single_key
 * @param string $plural_key
 * @param int $count
 * @param string $domain
 * @return string
 */
function _n($single_key, $plural_key, $count, $domain = 'core') {
  $gt = Translation::newInstance()->_get();
  $string = $gt->dngettext((string)$domain, (string)$single_key, (string)$plural_key, (int)$count);
  return osc_apply_filter('ngettext', (string)$string);
}

/**
 * Retrieve the singular or plural translation of the string.
 *
 * @since 2.2
 *
 * @param string $single_key
 * @param string $plural_key
 * @param int $count
 * @return string
 */
function _mn($single_key, $plural_key, $count) {
  return _n((string)$single_key, (string)$plural_key, (int)$count, 'messages');
}

/* file end: ./oc-includes/osclass/helpers/hTranslations.php */