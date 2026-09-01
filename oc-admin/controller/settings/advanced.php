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


class CAdminSettingsAdvanced extends AdminSecBaseModel {
  //Business Layer...
  function doModel() {
    switch($this->action) {
      case('advanced'):
        //calling the advanced settings view
        $this->doView('settings/advanced.php');
        break;
        
      case('advanced_post'):
        // updating advanced settings
        if(defined('DEMO')) {
          osc_add_flash_warning_message( _m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=settings&action=advanced');
        }
        
        osc_csrf_check();
        
        $subdomain_type = Params::getParam('e_type');
        $subdomain_host = trim(strip_tags(Params::getParam('s_host')));
        $subdomain_landing = Params::getParam('b_landing');
        $subdomain_redirect = Params::getParam('b_redirect');
        $subdomain_restricted_ids = trim(strtolower(strip_tags(Params::getParam('s_restricted_ids'))));
        $subdomain_language_slug_type = trim(strip_tags(Params::getParam('s_language_slug_type')));
        
        if(!in_array($subdomain_type, array('category', 'country', 'region', 'city', 'user', 'language'))) {
          $subdomain_type = '';
        } else if ($subdomain_type == 'language' && osc_locale_to_base_url_enabled()) {
          osc_add_flash_error_message( _m('Subdomain type "Language" is not supported when option "Add language code into base URL" is enabled!'), 'admin');
          $subdomain_type = '';
        }
        
        
        // When subdomain type has changed, drop logged user as it's most probably going to cause issues with session and cookies
        // impossible to logout due to changed cookie domain (ie domain.com to .domain.com)
        if(osc_subdomain_type() != $subdomain_type) {
          Session::newInstance()->_drop('userId');
          Session::newInstance()->_drop('userName');
          Session::newInstance()->_drop('userEmail');
          Session::newInstance()->_drop('userPhone');

          Cookie::newInstance()->pop('oc_userId');
          Cookie::newInstance()->pop('oc_userSecret');
          Cookie::newInstance()->set();
        }


        $new_list = array();
        
        if($subdomain_restricted_ids != '') {
          $subdomain_restricted_ids_ = array_filter(array_unique(array_map('trim', array_map('strtolower', explode(',', $subdomain_restricted_ids)))));
          
          if($subdomain_restricted_ids == 'all') {
            $new_list = array('all');
            
          } else if(count($subdomain_restricted_ids_) > 0) {
            foreach($subdomain_restricted_ids_ as $sid) {
              $ctr = Country::newInstance()->findByCode($sid);
              
              if($sid == 'all') {
                $new_list = array('all');
                break;
              
              } else if($ctr !== false && isset($ctr['pk_c_code']) && $ctr['pk_c_code'] != '') {
                $new_list[] = strtolower($ctr['pk_c_code']);
                
              } else {
                $ctr = Country::newInstance()->findBySlug($sid);

                if($ctr !== false && isset($ctr['pk_c_code']) && $ctr['pk_c_code'] != '') {
                  $new_list[] = strtolower($ctr['pk_c_code']);
                } else {
                  $ctr = Country::newInstance()->findByName($sid);

                  if($ctr !== false && isset($ctr['pk_c_code']) && $ctr['pk_c_code'] != '') {
                    $new_list[] = strtolower($ctr['pk_c_code']);
                  }
                }
              }
            }
          }
        }

        $subdomain_landing = ($subdomain_landing != '' ? true : false);
        $subdomain_redirect = ($subdomain_redirect != '' ? true : false);

        $iUpdated = osc_set_preference('subdomain_type', $subdomain_type);
        $iUpdated += osc_set_preference('subdomain_host', $subdomain_host);
        $iUpdated += osc_set_preference('subdomain_language_slug_type', $subdomain_language_slug_type);
        $iUpdated += osc_set_preference('subdomain_landing', $subdomain_landing);
        $iUpdated += osc_set_preference('subdomain_redirect', $subdomain_redirect);
        $iUpdated += osc_set_preference('subdomain_restricted_ids', implode(',', $new_list));

        if($iUpdated > 0) {
          osc_add_flash_ok_message( _m("Advanced settings have been updated"), 'admin');
        }
        
        osc_calculate_location_slug(osc_subdomain_type());
        $this->redirectTo(osc_admin_base_url(true) . '?page=settings&action=advanced');
        break;
        
      case('advanced_cache_flush'):
        osc_cache_flush();
        osc_add_flash_ok_message( _m("Cache flushed correctly"), 'admin');
        $this->redirectTo(osc_admin_base_url(true) . '?page=settings&action=advanced');
        break;
    }
  }
}

// EOF: ./oc-admin/controller/settings/main.php