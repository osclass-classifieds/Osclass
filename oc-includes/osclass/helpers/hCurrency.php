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
 * Gets category array from view and cache it if not exists
 *
 * @return array
 */
function osc_get_currency_row($code, $cache = true) {
  $code = strtoupper(trim((string)$code));
  
  if($code == '' || strlen($code) != 3) {
    return false;
  }

  if($cache === true && View::newInstance()->_exists('currency_' . $code)) {
    return View::newInstance()->_get('currency_' . $code);
  }
  
  // If there is more categories in DB, it's not effective way
  $currencies = osc_get_currencies_all(true);
  
  // Search in session array with flat categories
  if(is_array($currencies) && isset($currencies[$code])) {
    View::newInstance()->_exportVariableToView('currency_' . $code, $currencies[$code]);
    return $currencies[$code];
  }

  // Search in database
  $currency = Currency::newInstance()->findByPrimaryKey($code);
  View::newInstance()->_exportVariableToView('currency_' . $code, $currency);
  
  return $currency;
}


/**
 * Gets list of currencies
 *
 * @return string
 */
function osc_get_currencies() {
  if(!View::newInstance()->_exists('currencies')) {
    $currencies = osc_get_currencies_all();
    View::newInstance()->_exportVariableToView('currencies', $currencies);
    return $currencies;
  }
  
  return View::newInstance()->_get('currencies');
}


/**
 * Gets list of currencies
 *
 * @return string
 */
function osc_get_currencies_all($by_pk = false) {
  $key = 'currencies_' . (string)$by_pk;

  if(!View::newInstance()->_exists($key)) {
    $currencies = Currency::newInstance()->listAllRaw();
    $output = array();
    
    if(is_array($currencies) && count($currencies) > 0) {
      foreach($currencies as $cur_row) {
        if($by_pk) {
          $output[$cur_row['pk_c_code']] = $cur_row;
        } else {
          $output[] = $cur_row;
        }
      }
    }
    
    View::newInstance()->_exportVariableToView($key, $output);
    return $output;
  }
  
  return View::newInstance()->_get($key);
}


