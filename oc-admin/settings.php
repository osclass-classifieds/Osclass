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


class CAdminSettings extends AdminSecBaseModel {
  function doModel() {
    switch($this->action) {
      case('advanced'):
      case('advanced_post'):
      case('advanced_cache_flush'):
        require_once(osc_admin_base_path() . 'controller/settings/advanced.php');
        $do = new CAdminSettingsAdvanced();
        break;

      case('optimization'):
      case('optimization_post'):
      case('optimization_clean'):
        require_once(osc_admin_base_path() . 'controller/settings/optimization.php');
        $do = new CAdminSettingsOptimization();
        break;
        
      case('comments'):
      case('comments_post'):
        require_once(osc_admin_base_path() . 'controller/settings/comments.php');
        $do = new CAdminSettingsComments();
        break;

      case('permalinks'):
      case('permalinks_post'):
        require_once(osc_admin_base_path() . 'controller/settings/permalinks.php');
        $do = new CAdminSettingsPermalinks();
        break;

      case('spamNbots'):
      case('akismet_post'):
      case('recaptcha_post'):
        require_once(osc_admin_base_path() . 'controller/settings/spamnbots.php');
        $do = new CAdminSettingsSpamnBots();
        break;

      case('mailserver'):
      case('mailserver_post'):
        require_once(osc_admin_base_path() . 'controller/settings/mailserver.php');
        $do = new CAdminSettingsMailserver();
        break;

      case('media'):
      case('media_post'):
      case('images_post_reset'):
        require_once(osc_admin_base_path() . 'controller/settings/media.php');
        $do = new CAdminSettingsMedia();
      case('images_post'):
        require_once(osc_admin_base_path() . 'controller/settings/media.php');
        $do = new CAdminSettingsMedia();
        break;

      case('latestsearches'):
      case('latestsearches_post'):
      case('latestsearches_clean'):
        require_once(osc_admin_base_path() . 'controller/settings/latestsearches.php');
        $do = new CAdminSettingsLatestSearches();
        break;
        
      case('breadcrumbs'):
      case('breadcrumbs_post'):
        require_once(osc_admin_base_path() . 'controller/settings/breadcrumbs.php');
        $do = new CAdminSettingsBreadcrumbs();
        break;

      case('update'):
      case('check_updates'):
      default:
        require_once(osc_admin_base_path() . 'controller/settings/main.php');
        $do = new CAdminSettingsMain();
        break;
    }

    $do->doModel();
  }
}

/* file end: ./oc-admin/settings.php */