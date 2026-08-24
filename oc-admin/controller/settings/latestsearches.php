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


class CAdminSettingsLatestSearches extends AdminSecBaseModel
{
  //Business Layer...
  function doModel()
  {
    switch($this->action) {
      case('latestsearches'):
        $this->doView('settings/searches.php');
        break;

      case('latestsearches_post'):
        osc_csrf_check();

        $iUpdated = 0;
        $saveLatestSearches = Params::getParam('save_latest_searches');
        $saveLatestSearches = ($saveLatestSearches != '' ? true : false);
        $latestSearchesRestriction = Params::getParam('latest_searches_restriction');
        $latestSearchesRestriction = (is_array($latestSearchesRestriction) ? 0 : (int)$latestSearchesRestriction);
        $latestSearchesWords = Params::getParam('latest_searches_words');
        $latestSearchesWords = (!is_array($latestSearchesWords) ? strtolower((string)$latestSearchesWords) : '');
        $latestSearchesWords = explode(',', $latestSearchesWords);
        $latestSearchesWords = array_filter(array_unique(array_map('strtolower', $latestSearchesWords)));
        $latestSearchesWords = implode(',', $latestSearchesWords);
        $latestSearchesMinLength = Params::getParam('latest_searches_min_length');
        $latestSearchesMinLength = (is_array($latestSearchesMinLength) ? 0 : (int)$latestSearchesMinLength);
        $latestSearchesMinLength = ($latestSearchesMinLength > 0 ? $latestSearchesMinLength : 3);
        $latestSearchesMaxLength = Params::getParam('latest_searches_max_length');
        $latestSearchesMaxLength = (is_array($latestSearchesMaxLength) ? 0 : (int)$latestSearchesMaxLength);
        $latestSearchesMaxLength = ($latestSearchesMaxLength > 0 ? $latestSearchesMaxLength : 15);
        $latestSearchesMaxLength = ($latestSearchesMaxLength < $latestSearchesMinLength ? $latestSearchesMinLength : $latestSearchesMaxLength);
        $customPurge = Params::getParam('customPurge');
        $customPurge = (is_array($customPurge) ? '' : trim((string)$customPurge));

        $iUpdated += osc_set_preference('save_latest_searches', $saveLatestSearches);
        $iUpdated += osc_set_preference('latest_searches_restriction', $latestSearchesRestriction);
        $iUpdated += osc_set_preference('latest_searches_words', $latestSearchesWords);
        $iUpdated += osc_set_preference('latest_searches_min_length', $latestSearchesMinLength);
        $iUpdated += osc_set_preference('latest_searches_max_length', $latestSearchesMaxLength);


        if($customPurge == '') {
          osc_add_flash_error_message(_m('Custom number could not be left empty'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=settings&action=latestsearches');
        } else if(!in_array($customPurge, array('hour', 'day', 'week', 'month', 'year', 'forever')) && (!ctype_digit($customPurge) || (int)$customPurge <= 0)) {
          osc_add_flash_error_message(_m('Invalid latest searches cleanup value'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=settings&action=latestsearches');
        } else {
          $iUpdated += osc_set_preference('purge_latest_searches', $customPurge);

          if($iUpdated > 0) {
            osc_add_flash_ok_message( _m('Latest searches settings have been updated'), 'admin');
          }

          $this->redirectTo(osc_admin_base_url(true) . '?page=settings&action=latestsearches');
        }
        break;

      case('latestsearches_clean'):
        osc_csrf_check();

        $purge = trim((string)osc_purge_latest_searches());

        if(in_array($purge, array('hour', 'day', 'week', 'month', 'year'))) {
          LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', strtotime("-1 $purge")));
          osc_add_flash_ok_message( _m('Latest searches cleanup has been executed using current retention settings'), 'admin');

        } else if(ctype_digit($purge) && (int)$purge > 0) {
          LatestSearches::newInstance()->purgeNumber((int)$purge);
          osc_add_flash_ok_message( _m('Latest searches cleanup has been executed using current retention settings'), 'admin');

        } else if($purge == 'forever') {
          osc_add_flash_warning_message( _m('Latest searches cleanup is disabled because queries are stored forever'), 'admin');

        } else {
          osc_add_flash_warning_message( _m('Latest searches cleanup could not be executed because cleanup setting is invalid'), 'admin');
        }

        $this->redirectTo(osc_admin_base_url(true) . '?page=settings&action=latestsearches');

        break;
    }
  }
}

// EOF: ./oc-admin/controller/settings/latestsearches.php
