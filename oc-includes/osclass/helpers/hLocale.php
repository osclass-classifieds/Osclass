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
* Helper Locales
* @package Osclass
* @subpackage Helpers
* @author Osclass
*/



/**
 * Gets details about locale to be added into base URL
 * Supports "xx", "xx-yy", "xx-YY"
 *
 * @return array()
 */
function osc_base_url_locale_regex() {
  $output = array();
  
  if(osc_locale_to_base_url_type() == '') {
    $output['regex'] = '([a-z]{2})';
    $output['params_count'] = 1;
    $output['lang_param'] = '$1';
    
  } else if(osc_locale_to_base_url_type() == 'LONG') {
    $output['regex'] = '([a-z]{2})-([a-zA-Z]{2})';
    $output['params_count'] = 2;
    $output['lang_param'] = '$1-$2';
    
  // } else if(osc_locale_to_base_url_type() == 'xx_YY') {      // we do not want this, not SEO friendly
    // $output['regex'] = '([a-z]{2})_([A-Z]{2})';
    // $output['params_count'] = 2;
    // $output['lang_param'] = '$1_$2';
  } else {
    $output['regex'] = '';
    $output['params_count'] = 0;
    $output['lang_param'] = ''; 
  }
  
  return $output;
}

/**
 * Gets locale slug into base URL
 * Supports "en" or "en-US" formats
 *
 * @param $locale
 * @return string
 */
function osc_base_url_locale_slug($locale = '') {
  if($locale == '') {
    $locale = osc_current_user_locale();
  }
  
  if(osc_locale_to_base_url_type() == '') {
    return strtolower(substr($locale, 0, 2));
    
  } else {
    //return str_replace('_', '-', $locale);
    return strtolower(str_replace('_', '-', $locale));
  }
}

/**
 * Gets locale slug based on subdomain settings
 * Supports "en" or "en-US" formats
 *
 * @param $locale
 * @return string
 */
function osc_subdomain_locale_slug($locale) {
  if(osc_subdomain_language_slug_type() == '') {
    return substr($locale, 0, 2);
    
  } else {
    //return str_replace('_', '-', $locale);
    return strtolower(str_replace('_', '-', $locale));
  }
}

/**
 * Gets locale generic field
 *
 * @param $field
 * @param $locale
 * @return string
 */
function osc_locale_field($field, $locale = '') {
  return osc_field(osc_locale(), $field, $locale);
}

/**
 * Gets locale object
 *
 * @return array
 */
function osc_locale() {
  $locale = null;
  if (View::newInstance()->_exists('locales')) {
    $locale = View::newInstance()->_current('locales');
    
  } else if (View::newInstance()->_exists('locale')) {
    $locale = View::newInstance()->_get('locale');
  }

  return $locale;
}

/**
 * Gets list of enabled locales
 *
 * @return array
 */
function osc_get_locales() {
  if(!View::newInstance()->_exists('locales')) {
    // $locales = OSCLocale::newInstance()->listAllEnabled(false, true);
    $locales = osc_get_locales_all('FRONT', true);
    View::newInstance()->_exportVariableToView('locales', $locales);
    
  } else {
    $locales = View::newInstance()->_get('locales');
  }
  
  return $locales;
}


/**
 * Gets list of locales by param
 * $enabled: ALL/FRONT/BACK
 * @return array
 */
function osc_get_locales_all($enabled = 'ALL', $by_pk = false) {
  $key = 'locales_' . strtolower((string)$enabled) . '_' . (string)$by_pk;
  
  if(!View::newInstance()->_exists($key)) {
    $locales_all = OSCLocale::newInstance()->listAllRaw();
    $locales = array();
    
    if(is_array($locales_all) && count($locales_all) > 0) {
      foreach($locales_all as $locale) {
        if($enabled == 'ALL' || $enabled == '' || ($enabled == 'FRONT' && $locale['b_enabled'] == 1)  || ($enabled == 'BACK' && $locale['b_enabled_bo'] == 1)) {
          if($by_pk) {
            $locales[$locale['pk_c_code']] = $locale;
          } else {
            $locales[] = $locale;
          }
        }
      }
    }
    
    View::newInstance()->_exportVariableToView($key, $locales);
  }
  
  return View::newInstance()->_get($key);
}


/**
 * Gets one locale row
 *
 * @return array
 */
function osc_get_locale_row($code, $cache = true) {
  $code = trim((string)$code);

  if($code == '' || strlen($code) < 2) {
    return false;
  }
  
  if($cache === true && View::newInstance()->_exists('locale_' . $code)) {
    return View::newInstance()->_get('locale_' . $code);
  }

  $locales = osc_get_locales_all('ALL', true);


  // Try to find locale in existing session
  if(isset($locales[$code])) {
    View::newInstance()->_exportVariableToView('locale_' . $code, $locales[$code]);
    return $locales[$code];
  
  } else {
    
    // Maybe code is not in form xx_YY
    if(strlen((string)$code) == 5) {
      $code5 = strtolower(substr($code, 0, 2)) . '_' . strtoupper(substr($code, 3, 2));
      
      if(isset($locales[$code5])) {
        View::newInstance()->_exportVariableToView('locale_' . $code, $locales[$code5]);
        return $locales[$code5];
      }
    }

    
    // Try to search by first 2 letters only
    $code2 = strtolower(substr($code, 0, 2));
    
    if(is_array($locales) && count($locales) > 0) {
      foreach($locales as $pk => $loc_row) {
        if($code2 == substr($pk, 0, 2)) {
          View::newInstance()->_exportVariableToView('locale_' . $code, $loc_row);
          return $loc_row;
        }
      }
    }
  }
  
  // Search in DB
  $locale = OSCLocale::newInstance()->findByCode($code);
  if($locale !== false && isset($locale['pk_c_code'])) {
    View::newInstance()->_exportVariableToView('locale_' . $code, $locale);
    return $locale;
  }
  
  return false;
}

/**
 * Private function to count locales
 *
 * @return boolean
 */
function osc_priv_count_locales() {
  return View::newInstance()->_count('locales');
}

/**
 * Reset iterator of locales
 *
 * @return void
 */
function osc_goto_first_locale() {
  View::newInstance()->_reset('locales');
}

/**
 * Gets number of enabled locales for website
 *
 * @return int
 */
function osc_count_web_enabled_locales() {
  if(!View::newInstance()->_exists('locales')) {
    View::newInstance()->_exportVariableToView('locales', osc_get_locales());
  }
  
  return osc_priv_count_locales();
}


/**
 * Iterator for enabled locales for website
 *
 * @return bool
 */
function osc_has_web_enabled_locales() {
  if(!View::newInstance()->_exists('locales')) {
    View::newInstance()->_exportVariableToView('locales', osc_get_locales());
  }

  return View::newInstance()->_next('locales');
}

/**
 * Gets current locale's code
 *
 * @return string
 */
function osc_locale_code() {
  return osc_locale_field('pk_c_code');
}

/**
 * Gets current locale's name
 *
 * @return string
 */
function osc_locale_name() {
  return osc_locale_field('s_name');
}

/**
 * Gets if locale is RTL
 *
 * @return boolean
 */
function osc_locale_is_rtl() {
  return (osc_locale_field('b_rtl') == 1 ? 1 : 0);
}

/**
 * Gets if locale should use native location names
 *
 * @return boolean
 */
function osc_locale_native_location_names() {
  return (osc_locale_field('b_locations_native') == 1 ? 1 : 0);
}

/**
 * Gets current locale's currency format
 *
 * @return string
 */
function osc_locale_currency_format() {
  $locale = osc_get_current_user_locale();
  return $locale['s_currency_format'];
}

/**
 * Gets current locale's decimal point
 *
 * @return string
 */
function osc_locale_dec_point() {
  $locale = osc_get_current_user_locale();
  return $locale['s_dec_point'];
}

/**
 * Gets current locale's thousands separator
 *
 * @return string
 */
function osc_locale_thousands_sep() {
  $locale = osc_get_current_user_locale();
  return $locale['s_thousands_sep'];
}

/**
 * Gets current locale's number of decimals
 *
 * @return string
 */
function osc_locale_num_dec() {
  $locale = osc_get_current_user_locale();
  return $locale['i_num_dec'];
}


/**
 * Gets list of enabled locales
 *
 * @param bool $indexed_by_pk
 *
 * @return array
 */
function osc_all_enabled_locales_for_admin($indexed_by_pk = false) {
  // return OSCLocale::newInstance()->listAllEnabled(true, $indexed_by_pk);
  return osc_get_locales_all('BACK', $indexed_by_pk);
}

/**
 * Gets current locale object
 *
 * @return array
 */
function osc_get_current_user_locale() {
  $locale = osc_get_locale_row(osc_current_user_locale());
  
  if(!View::newInstance()->_exists('locale')) {
    $locale = osc_get_locale_row(osc_current_user_locale());

    if($locale !== false && isset($locale['pk_c_code'])) {
      View::newInstance()->_exportVariableToView('locale', $locale);
      return $locale;
    }
    
    // Try to search in DB
    $locale = OSCLocale::newInstance()->findByPrimaryKey(osc_current_user_locale());
    
    if($locale !== false && isset($locale['pk_c_code'])) {
      View::newInstance()->_exportVariableToView('locale', $locale);
      return $locale;
    }
    
    // Get first enabled locale from session
    $locale = osc_get_first_locale();
    View::newInstance()->_exportVariableToView('locale', $locale);
    return $locale;
    
  } else {
    return View::newInstance()->_get('locale');
  }
}


/**
 * Gets first available locale
 *
 * @return array
 */
function osc_get_first_locale() {
  $locales = osc_get_locales();
  return reset($locales);
}


/**
 * Gets if current user locale should use native location names
 *
 * @return boolean
 */
function osc_get_current_user_locations_native() {
  if(osc_is_backoffice()) {
    return 0;  // disable this function for oc-admin to make sure correct data are shown
  }

  if(osc_get_current_user_locale() !== false && is_array(osc_get_current_user_locale()) && isset(osc_get_current_user_locale()['b_locations_native'])) {
    return (osc_get_current_user_locale()['b_locations_native'] == 1 ? 1 : 0);
  }
  
  return 0;
}


/**
 * Gets if current user locale is RTL
 *
 * @return boolean
 */
function osc_current_user_locale_is_rtl() {
  if(osc_is_backoffice()) {
    return 0;  // disable this function for oc-admin to make sure correct data are shown
  }

  if(osc_get_current_user_locale() !== false && is_array(osc_get_current_user_locale())) {
    if(isset(osc_get_current_user_locale()['b_rtl'])) {
      return (osc_get_current_user_locale()['b_rtl'] == 1 ? 1 : 0);
    } else {
      // compare on first 2 chars only. Could be also 5.
      $rtl_codes = osc_rtl_lang_codes(false);
      
      $code = substr(0, 2, osc_current_user_locale_code());
      if($code != '' && in_array($code, $rtl_codes)) {
        return 1;
      }
    }
  } 
  
  return 0;
}

/**
 * Gets direction shortcut for current user locale. Returns "rtl" or "ltr"
 *
 * @return string
 */
function osc_current_user_locale_dir() {
  return osc_current_user_locale_is_rtl() ? 'rtl' : 'ltr';
}


/**
 * Get the actual locale of the user.
 *
 * You get the right locale code. If an user is using the website in another language different of the default one, or
 * the user uses the default one, you'll get it.
 *
 * @return string Locale Code
 */
function osc_current_user_locale() {
  if(Session::newInstance()->_get('userLocale') != '') {
    return Session::newInstance()->_get('userLocale');
  }

  return osc_language();
}

/**
 * Get the actual locale code of the user.
 *
 * @return string Locale Code
 */
function osc_current_user_locale_code() {
  return osc_current_user_locale();
}

/**
 * Get the actual locale of the admin.
 *
 * You get the right locale code. If an admin is using the website in another language different of the default one, or
 * the admin uses the default one, you'll get it.
 *
 * @return string OSCLocale Code
 */
function osc_current_admin_locale() {
  if(Session::newInstance()->_get('adminLocale') != '') {
    return Session::newInstance()->_get('adminLocale');
  }

  return osc_admin_language();
}

/**
 * Get the actual locale code of the admin.
 *
 * @return string Locale Code
 */
function osc_current_admin_locale_code() {
  return osc_current_admin_locale();
}
