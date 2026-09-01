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


class CAdminSettingsBreadcrumbs extends AdminSecBaseModel
{
  //Business Layer...
  function doModel()
  {
    switch($this->action) {
      case('breadcrumbs'):
        //calling the comments settings view
        $this->doView('settings/breadcrumbs.php');
      break;
      case('breadcrumbs_post'):
        // updating settings
        osc_csrf_check();

        $iUpdated     = 0;
        $itemCountry  = Params::getParam('breadcrumbs_item_country');
        $itemCountry  = (($itemCountry != '') ? true : false);
        $itemRegion  = Params::getParam('breadcrumbs_item_region');
        $itemRegion  = (($itemRegion != '') ? true : false);
        $itemCity  = Params::getParam('breadcrumbs_item_city');
        $itemCity  = (($itemCity != '') ? true : false);
        $itemCategory  = Params::getParam('breadcrumbs_item_category');
        $itemCategory  = (($itemCategory != '') ? true : false);
        $itemParentCategories  = Params::getParam('breadcrumbs_item_parent_categories');
        $itemParentCategories  = (($itemParentCategories != '') ? true : false);
        $itemPageTitle  = Params::getParam('breadcrumbs_item_page_title');
        $itemPageTitle  = (($itemPageTitle != '') ? true : false);
        $hideCustom  = trim(Params::getParam('breadcrumbs_hide_custom'));
        $hideCustom = implode(',', array_filter(array_unique(array_map('trim', explode(',', $hideCustom)))));

        $params = Params::getParamsAsArray();
        
        $hide_list = array();
        if(count($params) > 0) {
          foreach($params as $name => $value) {
            $key = explode('-', $name);
            
            if(isset($key[0]) && $key[0] == 'bchide') {
              if($value == 'on' || $value == '1') {
                $location = trim(isset($key[1]) ? $key[1] : '');
                $section = trim(isset($key[2]) ? $key[2] : '');
                
                $loc_sec = $location . '-' . $section;
                $hide_list[] = $loc_sec;
              }
            }
          }
        }
        
        $hide_list = array_filter(array_unique($hide_list));
        $hide_string = implode(',', $hide_list);
     
        $iUpdated += osc_set_preference('breadcrumbs_item_country', $itemCountry);
        $iUpdated += osc_set_preference('breadcrumbs_item_region', $itemRegion);
        $iUpdated += osc_set_preference('breadcrumbs_item_city', $itemCity);
        $iUpdated += osc_set_preference('breadcrumbs_item_category', $itemCategory);
        $iUpdated += osc_set_preference('breadcrumbs_item_parent_categories', $itemParentCategories);
        $iUpdated += osc_set_preference('breadcrumbs_item_page_title', $itemPageTitle);
        $iUpdated += osc_set_preference('breadcrumbs_hide', $hide_string);
        $iUpdated += osc_set_preference('breadcrumbs_hide_custom', $hideCustom);

        if($iUpdated > 0) {
          osc_add_flash_ok_message( _m("Breadcrumbs settings have been updated"), 'admin');
        } else {
          osc_add_flash_ok_message( _m("No changes has been done"), 'admin');
        }
        
        $this->redirectTo(osc_admin_base_url(true) . '?page=settings&action=breadcrumbs');
      break;
    }
  }
}

// EOF: ./oc-admin/controller/settings/Breadcrumbs.php