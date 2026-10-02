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
    $only_enabled = (!defined('OC_ADMIN') || !OC_ADMIN);
    $currencies = Currency::newInstance()->listAllRaw(true, $only_enabled);
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


/**
 * 3-letter currency code: explicit optional $code, else item fk_c_currency_code, else site default.
 *
 * @param string|null $code
 *
 * @return string|false
 */
function osc_currency_exchange_get_code($code = null) {
  if($code !== null && trim((string)$code) !== '') {
    $c = strtoupper(trim((string)$code));
    return (strlen($c) == 3 ? $c : false);
  }
  if(function_exists('osc_item_field')) {
    $itemCur = trim((string)osc_item_field('fk_c_currency_code'));
    if($itemCur !== '') {
      $c = strtoupper($itemCur);
      if(strlen($c) == 3) {
        return $c;
      }
    }
  }
  $c = strtoupper(trim((string)osc_currency()));
  return (strlen($c) == 3 ? $c : false);
}


/**
 * Stored exchange rate for the code (same basis as admin currency rates). Optional $code; null uses item currency then site default.
 *
 * @param string|null $code
 *
 * @return float|false
 */
function osc_get_currency_exchange_rate($code = null) {
  $codeU = osc_currency_exchange_get_code($code);
  if($codeU === false) {
    return false;
  }
  $row = osc_get_currency_row($codeU);
  if(!$row || !is_array($row)) {
    return false;
  }
  if(!isset($row['d_exchange_rate']) || $row['d_exchange_rate'] === null || $row['d_exchange_rate'] === '') {
    return false;
  }
  if(!is_numeric($row['d_exchange_rate'])) {
    return false;
  }
  $v = (float)$row['d_exchange_rate'];
  if($v <= 0) {
    return false;
  }
  return $v;
}


/**
 * Stored exchange rate for the site default currency (preference "currency").
 *
 * @return float|false
 */
function osc_get_currency_exchange_rate_default() {
  return osc_get_currency_exchange_rate(osc_currency());
}


/**
 * Cross rate: units of $to_code per one unit of $from_code (uses stored rates from t_currency). Null $from_code/$to_code: item fk_c_currency_code if set, else site default.
 *
 * @param string|null $from_code
 * @param string|null $to_code
 *
 * @return float|false
 */
function osc_get_currency_exchange_rate_cross($from_code = null, $to_code = null) {
  $fromU = osc_currency_exchange_get_code($from_code);
  if($to_code !== null && trim((string)$to_code) !== '') {
    $toU = strtoupper(trim((string)$to_code));
    if(strlen($toU) != 3) {
      $toU = false;
    }
  } else {
    $toU = osc_currency_exchange_get_code(null);
  }
  if($fromU === false || $toU === false) {
    return false;
  }
  if($fromU == $toU) {
    return 1.0;
  }
  $rFrom = osc_get_currency_exchange_rate($fromU);
  $rTo = osc_get_currency_exchange_rate($toU);
  if($rFrom === false || $rTo === false) {
    return false;
  }
  return (float)($rTo / $rFrom);
}


/**
 * Units of $currency_code per 1 unit of site default (e.g. CZK per EUR when default is EUR: rate_CZK / rate_EUR). $currency_code null uses item currency then site default.
 *
 * @param string|null $currency_code
 *
 * @return float|false
 */
function osc_get_currency_exchange_rate_to_default($currency_code = null) {
  $def = strtoupper(trim((string)osc_currency()));
  if(strlen($def) != 3) {
    return false;
  }
  $cur = osc_currency_exchange_get_code($currency_code);
  if($cur === false) {
    return false;
  }
  return osc_get_currency_exchange_rate_cross($def, $cur);
}


/**
 * Units of site default per 1 unit of $currency_code (inverse of osc_get_currency_exchange_rate_to_default when both rates exist).
 *
 * @param string|null $currency_code
 *
 * @return float|false
 */
function osc_get_currency_exchange_rate_default_to_currency($currency_code = null) {
  $def = strtoupper(trim((string)osc_currency()));
  if(strlen($def) != 3) {
    return false;
  }
  $cur = osc_currency_exchange_get_code($currency_code);
  if($cur === false) {
    return false;
  }
  return osc_get_currency_exchange_rate_cross($cur, $def);
}


