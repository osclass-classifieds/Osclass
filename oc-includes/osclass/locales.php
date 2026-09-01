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
 * @return array
 */
function osc_listLocales() {
  $languages = array();

  $codes = osc_listLanguageCodes();
  foreach($codes as $code) {
    $path = sprintf('%s%s/index.php', osc_translations_path(), $code);
    $fxName = sprintf('locale_%s_info', $code);
    if (file_exists($path)) {
      require_once $path;
      if (function_exists($fxName)) {
        $languages[$code]     = $fxName();
        $languages[$code]['code'] = $code;
      }
    }
  }

  return $languages;
}


/**
 * @return bool
 */
function osc_checkLocales($loop_all = false) {
  // if it's a demo, we don't import any sql
  if(defined('DEMO')) {
    return true;
  }

  $locales = osc_listLocales();

  foreach($locales as $locale) {
    $data = OSCLocale::newInstance()->findByPrimaryKey($locale['code']);

    if(!is_array($data)) {
      $values = array(
        'pk_c_code' => $locale['code'],
        's_name' => $locale['name'],
        's_short_name' => $locale['short_name'],
        's_description' => $locale['description'],
        's_version' => $locale['version'],
        's_author_name' => $locale['author_name'],
        's_author_url' => $locale['author_url'],
        's_currency_format' => $locale['currency_format'],
        's_date_format' => $locale['date_format'],
        's_stop_words' => $locale['stop_words'],
        'b_locations_native' => (isset($locale['native_locations']) ? ($locale['native_locations'] == 1 ? 1 : 0) : 0),
        'b_rtl' => (isset($locale['direction']) ? (trim(strtolower($locale['direction'])) == 'rtl' ? 1 : 0) : 0),
        'b_enabled' => 0,
        'b_enabled_bo' => 1
      );
      
      $result = OSCLocale::newInstance()->insert($values);

      // Unsuccessful
      if(!$result) {
        if($loop_all === true) {
          continue;
        } else {
          return false;
        }
      }

      // inserting e-mail translations
      $path = sprintf('%s%s/mail.sql', osc_translations_path(), $locale['code']);
      
      if (file_exists($path)) {
        $sql = file_get_contents($path);
        $conn = DBConnectionClass::newInstance();
        $c_db = $conn->getOsclassDb();
        $comm = new DBCommandClass($c_db);
        $result = $comm->importSQL($sql);
        
        // Unsuccessful
        if(!$result) {
          if($loop_all === true) {
            continue;
          } else {
            return false;
          }
        }
      }
    } else {
      // update language version
      OSCLocale::newInstance()->update(
        array('s_version' => $locale['version']),
        array('pk_c_code' => $locale['code'])
      );
    }
  }

  return true;
}


/**
 * @return array
 */
function osc_listLanguageCodes() {
  $codes = array();
  $dir = opendir(osc_translations_path());
  
  while ($file = readdir($dir)) {
    if (preg_match('/^[a-z_]+$/i', $file)) {
      $codes[] = $file;
    }
  }
  
  closedir($dir);

  return $codes;
}