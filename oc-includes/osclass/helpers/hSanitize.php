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
 * Helper Sanitize
 * @package  Osclass
 * @subpackage Helpers
 * @author   Osclass
 */

/**
 * Sanitize a website URL.
 *
 * @param string $value value to sanitize
 *
 * @return string sanitized
 */
function osc_sanitize_url($value) {
  if ($value === '' || $value === null) {
    return '';
  }
  
  return filter_var($value, FILTER_SANITIZE_URL);
}


/**
 * Sanitize a string.
 *
 * @param string $value value to sanitize
 *
 * @return string sanitized
 */
function osc_sanitize_string($value) {
  return osc_sanitizeString($value);
}


/**
 * Sanitize capitalization for a string.
 * Capitalize first letter of each name.
 * If all-caps, remove all-caps.
 *
 * @param string $value value to sanitize
 *
 * @return string sanitized
 */
function osc_sanitize_name($value) {
  if ($value === '' || $value === null) {
    return '';
  }
  
  return ucwords(osc_sanitize_allcaps(trim($value)));
}


/**
 * Sanitize string that's all-caps
 *
 * @param string $value value to sanitize
 *
 * @return string sanitized
 */
function osc_sanitize_allcaps($value) {
  if ($value === '' || $value === null) {
    return '';
  }
  
  if (preg_match('/^([A-Z][^A-Z]*)+$/', $value) && !preg_match('/[a-z]+/', $value)) {
    $value = ucfirst(strtolower($value));
  }

  return $value;
}


/**
 * Sanitize a username
 *
 * @param string $value
 *
 * @return string sanitized
 */
function osc_sanitize_username($value) {
  if ($value === '' || $value === null) {
    return '';
  }
  
  $value = preg_replace('/(_+)/', '-', preg_replace('/([^0-9A-Za-z_]*)/', '', str_replace(' ', '-', trim((string)$value))));
  $value = preg_replace('/-{2,}/','-', $value);
  return strtolower($value);
}


/**
 * Sanitize number (with no periods)
 *
 * @param string $value value to sanitize
 *
 * @return string sanitized
 */
function osc_sanitize_int($value) {
  if (!preg_match('/^[0-9]*$/', $value)) {
    return (int) $value;
  }

  return $value;
}


/**
 * Format phone number. Supports 10-digit with extensions,
 * and defaults to international if cannot match US number.
 *
 * @param string $value value to sanitize
 *
 * @return string sanitized
 */
function osc_sanitize_phone($value) {
  if ($value === '' || $value === null) {
    return '';
  }
  
  if (empty($value)) {
    return '';
  }

  // Remove strings that aren't letter and number.
  $value = preg_replace('/[^a-z0-9]/', '', strtolower($value));

  // Remove 1 from front of number.
  if (preg_match('/^([0-9]{11})/', $value) && $value[ 0 ] == 1) {
    $value = substr($value, 1);
  }

  // Check for phone ext.
  if (! preg_match('/^[0-9]$/', $value)) {
    $value = preg_replace('/^([0-9]{10})([a-z]+)([0-9]+)/', '$1ext$3', $value); // Replace 'x|ext|extension' with 'ext'.
    list($value, $ext) = explode('ext', $value); // Split number & ext.
  }

  // Add dashes: ___-___-____
  if (strlen($value) == 7) {
    $value = preg_replace('/([0-9]{3})([0-9]{4})/', '$1-$2', $value);
  } else if (strlen($value) == 10) {
    $value = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '$1-$2-$3', $value);
  }

  return $ext ? $value . ' x' . $ext : $value;
}


/**
 * Escape html
 *
 * Formats text so that it can be safely placed in a form field in the event it has HTML tags.
 *
 * @access  public
 *
 * @param string
 *
 * @return  string
 * @version 2.4
 */
function osc_esc_html($str = '') {
  if ($str === '' || $str === null) {
    return '';
  }

  $temp = '__TEMP_AMPERSANDS__';

  // Replace entities to temporary markers so that
  // htmlspecialchars won't mess them up
  $str = preg_replace("/&#(\d+);/", "$temp\\1;", $str);
  $str = preg_replace("/&(\w+);/", "$temp\\1;", $str);

  $str = htmlspecialchars($str);

  // In case htmlspecialchars misses these.
  $str = str_replace(array("'", '"'), array('&#39;', '&quot;'), $str);

  // Decode the temp markers back to entities
  $str = preg_replace("/$temp(\d+);/", "&#\\1;", $str);
  $str = preg_replace("/$temp(\w+);/", "&\\1;", $str);

  return $str;
}


/**
 * Escape single quotes, double quotes, <, >, & and line endings
 *
 * @access  public
 *
 * @param string $str
 *
 * @return string
 * @version 2.4
 */
function osc_esc_js($str) {
  if ($str === '' || $str === null) {
    return '';
  }
  
  static $sNewLines = '<br><br/><br />';
  static $aNewLines = array ('<br>', '<br/>', '<br />');

  $str = strip_tags($str, $sNewLines);
  $str = str_replace("\r", '', $str);
  $str = addslashes($str);
  $str = str_replace("\n", '\n', $str);
  $str = str_replace($aNewLines, '\n', $str);

  return $str;
}