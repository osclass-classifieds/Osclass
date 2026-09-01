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


class CAdminLocations extends AdminSecBaseModel {
  //Business Layer...
  function doModel() {
    // calling the locations view
    $action = Params::getParam('action');
    $mCountries = new Country();

    switch ($action) {
      case('add_country'):  // add country
        if (defined('DEMO')) {
          osc_add_flash_warning_message(_m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=locations');
        }
        
        osc_csrf_check();
        
        $countryCode = strtoupper(trim((string)Params::getParam('c_country')));
        $countryName = trim((string)Params::getParam('country'));
        $countryNameNative = Params::getParam('countryNative');
        $countryNameNative = trim($countryNameNative) <> '' ? $countryNameNative : null;
        $countryPhoneCode = Params::getParam('countryPhoneCode');
        $countryCurrency = Params::getParam('countryCurrency');
        
        // Check if data are valid
        if(strlen($countryCode) != 2) {
          osc_add_flash_error_message(_m('Invalid country code! It must be exactly 2 letters.'), 'admin');
          
        } else if ($countryName == '') {
          osc_add_flash_error_message(_m('Country name is missing!'), 'admin');
          
        } else {
          $exists = $mCountries->findByCode($countryCode);
          
          if(isset($exists['s_name'])) {
            osc_add_flash_error_message(sprintf(_m('%s already was in the database'), $countryName), 'admin');
            
          } else {
            $mCountries->insert(array(
              'pk_c_code' => $countryCode,
              's_name' => $countryName,
              's_name_native' => $countryNameNative,
              's_phone_code' => $countryPhoneCode,
              's_currency' => $countryCurrency
            ));
            
            osc_add_flash_ok_message(sprintf(_m('%s has been added as a new country'), $countryName), 'admin');
          }
          
          osc_calculate_location_slug('country');
          osc_calculate_location_slug('region');
          osc_calculate_location_slug('city');
        }
        
        $this->redirectTo(osc_admin_base_url(true) . '?page=locations');
        break;
        
      case('edit_country'):   // edit country
        if (defined('DEMO')) {
          osc_add_flash_warning_message(_m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=locations');
        }
        
        osc_csrf_check();
        
        if (!osc_validate_min(Params::getParam('e_country'), 1)) {
          osc_add_flash_error_message(_m('Country name cannot be blank'), 'admin');
          
        } else {
          $countryCode = strtoupper(trim((string)Params::getParam('country_code')));
          $name = trim((string)Params::getParam('e_country'));
          $nameNative = Params::getParam('e_country_native');
          $nameNative = trim($nameNative) <> '' ? $nameNative : null;
          $slug = Params::getParam('e_country_slug');
          $phoneCode = Params::getParam('e_country_phone_code');
          $currency = Params::getParam('e_country_currency');

          // Check if data are valid
          if(strlen($countryCode) != 2) {
            osc_add_flash_error_message(_m('Invalid country code! It must be exactly 2 letters.'), 'admin');
            $this->redirectTo(osc_admin_base_url(true) . '?page=locations');
            
          } else if ($name == '') {
            osc_add_flash_error_message(_m('Country name is missing!'), 'admin');
            $this->redirectTo(osc_admin_base_url(true) . '?page=locations');
          } 
          
          
          if ($slug == '') {
            $slug_tmp = $slug = osc_sanitizeString($name);
            
          } else {
            $exists = $mCountries->findBySlug($slug);
            
            if(isset($exists['s_slug']) && $exists['pk_c_code'] != $countryCode) {
              $slug_tmp = $slug = osc_sanitizeString($name);
            } else {
              $slug_tmp = $slug = osc_sanitizeString($slug);
            }
          }
          
          $slug_unique = 1;
          while (true) {
            $location_slug = $mCountries->findBySlug($slug);
            if (isset($location_slug['s_slug']) && $location_slug['pk_c_code'] != $countryCode) {
              $slug = $slug_tmp . '-' . $slug_unique;
              $slug_unique++;
              
            } else {
              break;
            }
          }

          $ok = $mCountries->update(
            array('s_name' => $name, 's_name_native' => $nameNative, 's_slug' => $slug, 's_phone_code' => $phoneCode, 's_currency' => $currency),
            array('pk_c_code' => $countryCode)
          );

          if ($ok) {
            // Update country on existing items
            ItemLocation::newInstance()->update(
              array('s_country' => $name, 's_country_native' => $nameNative),
              array('fk_c_country_code' => $countryCode)
            );

            // Update country on existing users
            User::newInstance()->update(
              array('s_country' => $name, 's_country_native' => $nameNative),
              array('fk_c_country_code' => $countryCode)
            );

            osc_add_flash_ok_message(_m('Country has been edited'), 'admin');
            
          } else {
            osc_add_flash_warning_message(_m('Country has not been modified'), 'admin');
          }
        }

        $this->redirectTo(osc_admin_base_url(true) . '?page=locations');
        break;
        
      case('delete_country'): // delete country
        if (defined('DEMO')) {
          osc_add_flash_warning_message(_m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=locations');
        }
        
        osc_csrf_check();
        
        $countryIds = Params::getParam('id');

        if (is_array($countryIds)) {
          $locations = 0;
          $del_locations = 0;
          
          foreach ($countryIds as $countryId) {
            $ok = $mCountries->deleteByPrimaryKey($countryId);
          }
          
          if ($ok == 0) {
            $del_locations++;
          } else {
            $locations += $ok;
          }
          
          if ($locations == 0) {
            osc_add_flash_ok_message(sprintf(_n('One location has been deleted', '%s locations have been deleted', $del_locations), $del_locations), 'admin');
          } else {
            osc_add_flash_error_message(_m('There was a problem deleting locations'), 'admin');
          }
        } else {
          osc_add_flash_error_message(_m('No country was selected'), 'admin');
        }
        
        $this->redirectTo(osc_admin_base_url(true) . '?page=locations');
        break;
        
      case('add_region'):   // add region
        if (defined('DEMO')) {
          osc_add_flash_warning_message(_m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=locations');
        }
        
        osc_csrf_check();
        
        $mRegions = new Region();
        $regionName = trim((string)Params::getParam('region'));
        $regionNameNative = Params::getParam('region_native');
        $regionNameNative = trim($regionNameNative) <> '' ? $regionNameNative : null;
        $countryCode = trim((string)Params::getParam('country_c_parent'));
        $country = Country::newInstance()->findByCode($countryCode);

        if (!osc_validate_min($regionName, 1)) {
          osc_add_flash_error_message(_m('Region name cannot be blank'), 'admin');

        } else if(strlen($countryCode) != 2) {
          osc_add_flash_error_message(_m('Invalid country code'), 'admin');
          
        } else {
          $exists = $mRegions->findByName($regionName, $countryCode);
          
          if (!isset($exists['s_name'])) {
            $data = array(
              'fk_c_country_code' => $countryCode,
              's_name' => $regionName,
              's_name_native' => $regionNameNative
            );
            
            $mRegions->insert($data);
            $id = $mRegions->dao->insertedId();
            RegionStats::newInstance()->setNumItems($id, 0);

            osc_add_flash_ok_message(sprintf(_m('%s has been added as a new region'), $regionName), 'admin');
          } else {
            osc_add_flash_error_message(sprintf(_m('%s already was in the database'), $regionName), 'admin');
          }
        }
        osc_calculate_location_slug('region');
        osc_calculate_location_slug('city');
        $this->redirectTo(osc_admin_base_url(true) . '?page=locations&country_code=' . @$countryCode . "&country=" . @$country['s_name']);
        break;
        
      case('edit_region'):  // edit region
        if (defined('DEMO')) {
          osc_add_flash_warning_message(_m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=locations');
        }
        
        osc_csrf_check();
        
        $mRegions = new Region();
        $newRegion = trim((string)Params::getParam('e_region'));
        $newRegionNative = Params::getParam('e_region_native');
        $newRegionNative = trim($newRegionNative) <> '' ? $newRegionNative : null;
        $regionId = Params::getParam('region_id');

        if (!osc_validate_min($newRegion, 1)) {
          osc_add_flash_error_message(_m('Region name cannot be blank'), 'admin');
          
        } else {
          $aRegion = $mRegions->findByPrimaryKey($regionId);
          $exists = $mRegions->findByName($newRegion, $aRegion['fk_c_country_code']);
          if (!isset($exists['pk_i_id']) || $exists['pk_i_id'] == $regionId) {
            if ($regionId != '') {
              $country = Country::newInstance()->findByCode($aRegion['fk_c_country_code']);

              $name = $newRegion;
              $slug = Params::getParam('e_region_slug');
              if ($slug == '') {
                $slug_tmp = $slug = osc_sanitizeString($name);
              } else {
                $exists = $mRegions->findBySlug($slug);
                if (isset($exists['s_slug']) && $exists['pk_i_id'] != $regionId) {
                  $slug_tmp = $slug = osc_sanitizeString($name);
                } else {
                  $slug_tmp = $slug = osc_sanitizeString($slug);
                }
              }
              
              $slug_unique = 1;
              while (true) {
                $location_slug = $mRegions->findBySlug($slug);
                if (isset($location_slug['s_slug']) && $location_slug['pk_i_id'] != $regionId) {
                  $slug = $slug_tmp . '-' . $slug_unique;
                  $slug_unique++;
                } else {
                  break;
                }
              }

              $mRegions->update(array('s_name' => $newRegion, 's_name_native' => $newRegionNative, 's_slug' => $slug), array('pk_i_id' => $regionId));
              // Update region on existing items
              ItemLocation::newInstance()->update(
                array('s_region' => $newRegion, 's_region_native' => $newRegionNative),
                array('fk_i_region_id' => $regionId)
              );

              // Update region on existing users
              User::newInstance()->update(
                array('s_region' => $newRegion, 's_region_native' => $newRegionNative),
                array('fk_i_region_id' => $regionId)
              );

              osc_add_flash_ok_message(sprintf(_m('%s has been edited'), $newRegion), 'admin');
            }
          } else {
            osc_add_flash_error_message(sprintf(_m('%s already was in the database'), $newRegion), 'admin');
          }
        }

        $this->redirectTo(osc_admin_base_url(true) . '?page=locations&country_code=' . @$country['pk_c_code'] . "&country=" . @$country['s_name']);
        break;
        
      case('delete_region'):  // delete region
        if (defined('DEMO')) {
          osc_add_flash_warning_message(_m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=locations');
        }
        
        osc_csrf_check();
        
        $mRegion = new Region();
        $regionIds = Params::getParam('id');

        if (is_array($regionIds)) {
          $locations = 0;
          $del_locations = 0;
          if (count($regionIds) > 0) {
            $region = $mRegion->findByPrimaryKey($regionIds[0]);
            $country = Country::newInstance()->findByCode($region['fk_c_country_code']);
            foreach ($regionIds as $regionId) {
              if ($regionId != '') {
                $ok = $mRegion->deleteByPrimaryKey($regionId);
                
                if ($ok == 0) {
                  $del_locations++;
                } else {
                  $locations += $ok;
                }
              }
            }
          }
          
          if ($locations == 0) {
            osc_add_flash_ok_message(sprintf(_n('One location has been deleted', '%s locations have been deleted', $del_locations), $del_locations), 'admin');
          } else {
            osc_add_flash_error_message(_m('There was a problem deleting locations'), 'admin');
          }
          
        } else {
          osc_add_flash_error_message(_m('No region was selected'), 'admin');
        }

        $this->redirectTo(osc_admin_base_url(true) . '?page=locations&country_code=' . @$country['pk_c_code'] . "&country=" . @$country['s_name']);
        break;
        
      case('add_city'):     // add city
        if (defined('DEMO')) {
          osc_add_flash_warning_message(_m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=locations');
        }
        
        osc_csrf_check();
        
        $regionId = (int)Params::getParam('region_parent');
        $countryCode = trim((string)Params::getParam('country_c_parent'));
        $mRegion = new Region();
        $region = $mRegion->findByPrimaryKey($regionId);
        $country = Country::newInstance()->findByCode($region['fk_c_country_code']);
        $mCities = new City();
        $newCity = Params::getParam('city');
        $newCityNative = Params::getParam('city_native');
        $newCityNative = trim($newCityNative) <> '' ? $newCityNative : null;

        if (!osc_validate_min($newCity, 1)) {
          osc_add_flash_error_message(_m('City name cannot be blank'), 'admin');
          
        } else if($regionId <= 0) {
          osc_add_flash_error_message(_m('Region ID is missing'), 'admin');
          
        } else if(strlen($countryCode) != 2) {
          osc_add_flash_error_message(_m('Invalid country code'), 'admin');
          
        } else {
          $exists = $mCities->findByName($newCity, $regionId);
          if (!isset($exists['s_name'])) {
            $mCities->insert(array(
              'fk_i_region_id' => $regionId,
              's_name' => $newCity,
              's_name_native' => $newCityNative,
              'fk_c_country_code' => $countryCode
            ));
            $id = $mCities->dao->insertedId();
            CityStats::newInstance()->setNumItems($id, 0);

            osc_add_flash_ok_message(sprintf(_m('%s has been added as a new city'), $newCity), 'admin');
          } else {
            osc_add_flash_error_message(sprintf(_m('%s already was in the database'), $newCity), 'admin');
          }
        }
        
        osc_calculate_location_slug('city');
        $this->redirectTo(osc_admin_base_url(true) . '?page=locations&country_code=' . @$country['pk_c_code'] . "&country=" . @$country['s_name'] . "&region=" . $regionId);
        break;
        
      case('edit_city'):    // edit city
        if (defined('DEMO')) {
          osc_add_flash_warning_message(_m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=locations');
        }
        
        osc_csrf_check();
        
        $mRegion = new Region();
        $mCities = new City();
        $newCity = Params::getParam('e_city');
        $newCityNative = Params::getParam('e_city_native');
        $newCityNative = trim($newCityNative) <> '' ? $newCityNative : null;
        $newCityLat = Params::getParam('e_city_lat');
        $newCityLong = Params::getParam('e_city_long');

        if($newCityLat == '' || $newCityLat == 0 || $newCityLat == 'null') {
          $newCityLat = null;
        }

        if($newCityLong == '' || $newCityLong == 0 || $newCityLong == 'null') {
          $newCityLong = null;
        }
        
        $cityId = Params::getParam('city_id');

        if (!osc_validate_min($newCity, 1)) {
          osc_add_flash_error_message(_m('City name cannot be blank'), 'admin');
          
        } else {
          $city = $mCities->findByPrimaryKey($cityId);
          $exists = $mCities->findByName($newCity, $city['fk_i_region_id']);
          if (!isset($exists['pk_i_id']) || $exists['pk_i_id'] == $cityId) {
            $region = $mRegion->findByPrimaryKey($city['fk_i_region_id']);
            $country = Country::newInstance()->findByCode($region['fk_c_country_code']);

            $name = $newCity;
            $slug = Params::getParam('e_country_slug');
            if ($slug == '') {
              $slug_tmp = $slug = osc_sanitizeString($name);
            } else {
              $exists = $mCities->findBySlug($slug);
              if (isset($exists['s_slug']) && $exists['pk_i_id'] != $cityId) {
                $slug_tmp = $slug = osc_sanitizeString($name);
              } else {
                $slug_tmp = $slug = osc_sanitizeString($slug);
              }
            }
            
            $slug_unique = 1;
            while (true) {
              $location_slug = $mCities->findBySlug($slug);
              if (isset($location_slug['s_slug']) && $location_slug['pk_i_id'] != $cityId) {
                $slug = $slug_tmp . '-' . $slug_unique;
                $slug_unique++;
              } else {
                break;
              }
            }

            $mCities->update(array('s_name' => $newCity, 's_name_native' => $newCityNative, 'd_coord_lat' => $newCityLat, 'd_coord_long' => $newCityLong, 's_slug' => $slug), array('pk_i_id' => $cityId));
            // Update city on existing items
            ItemLocation::newInstance()->update(
              array('s_city' => $newCity, 's_city_native' => $newCityNative),
              array('fk_i_city_id' => $cityId)
            );

            // Update city on existing users
            User::newInstance()->update(
              array('s_city' => $newCity, 's_city_native' => $newCityNative),
              array('fk_i_city_id' => $cityId)
            );

            osc_add_flash_ok_message(sprintf(_m('%s has been edited'), $newCity), 'admin');
          } else {
            osc_add_flash_error_message(sprintf(_m('%s already was in the database'), $newCity), 'admin');
          }
        }

        $this->redirectTo(osc_admin_base_url(true) . '?page=locations&country_code=' . @$country['pk_c_code'] . "&country=" . @$country['s_name'] . "&region=" . @$region['pk_i_id']);
        break;
        
      case('delete_city'):  // delete city
        if (defined('DEMO')) {
          osc_add_flash_warning_message(_m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=locations');
        }
        
        osc_csrf_check();
        
        $mCities = new City();
        $cityIds = Params::getParam('id');
        if (is_array($cityIds)) {
          $locations = 0;
          $del_locations = 0;
          $cCity = end($cityIds);
          $cCity = $mCities->findByPrimaryKey($cCity);
          $region =  Region::newInstance()->findByPrimaryKey($cCity['fk_i_region_id']);
          $country = Country::newInstance()->findByCode($cCity['fk_c_country_code']);
          
          foreach ($cityIds as $cityId) {
            $ok = $mCities->deleteByPrimaryKey($cityId);
            if ($ok == 0) {
              $del_locations++;
            } else {
              $locations += $ok;
            }
          }
          
          if ($locations == 0) {
            osc_add_flash_ok_message(sprintf(_n('One location has been deleted', '%d locations have been deleted', $del_locations), $del_locations), 'admin');
          } else {
            osc_add_flash_error_message(_m('There was a problem deleting locations'), 'admin');
          }
        } else {
          osc_add_flash_error_message(_m('No city was selected'), 'admin');
        }


        $this->redirectTo(osc_admin_base_url(true) . '?page=locations&country_code=' . @$country['pk_c_code'] . "&country=" . @$country['s_name'] . "&region=" . @$region['pk_i_id']);
        break;
        
      case('locations_import'): // import locations
        if (defined('DEMO')) {
          osc_add_flash_warning_message(_m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=locations');
        }
        
        osc_csrf_check();

        $location = Params::getParam('location');
        
        if($location != '') {
          $sql = osc_file_get_contents(osc_get_locations_sql_url($location));
          
          if($sql != '') {
            $conn = DBConnectionClass::newInstance();
            $c_db = $conn->getOsclassDb();
            $comm = new DBCommandClass($c_db);
            
            $comm->query('SET FOREIGN_KEY_CHECKS = 0');
            $imported = $comm->importSQL($sql);
            $comm->query('SET FOREIGN_KEY_CHECKS = 1');

            osc_add_flash_ok_message(_m('Location imported successfully'), 'admin');
            $this->redirectTo(osc_admin_base_url(true) . '?page=locations');

            return true;
          }
        }

        osc_add_flash_error_message(_m('There was a problem importing the selected location'), 'admin');
        $this->redirectTo(osc_admin_base_url(true) . '?page=locations');

        return false;
        break;
        
      default:
        $aCountries = $mCountries->listAll();
        $this->_exportVariableToView('aCountries', $aCountries);

        $existing_locations = $mCountries->listNames();
        $a_external_locations_list = osc_file_get_contents_json(osc_get_locations_json_url());
        $a_external_locations_list = $a_external_locations_list['children'];
        
        // IDEA: This probably can be improved.
        foreach ($a_external_locations_list as $key => $location) {
          if (in_array($location['name'], $existing_locations, false)) {
            unset($a_external_locations_list[$key]);
          }
        }
        
        if (is_array($a_external_locations_list) && count($a_external_locations_list) > 0) {
          $this->_exportVariableToView('aLocations', $a_external_locations_list);
        }

        $this->doView('locations/index.php');
        break;
    }
  }
  
  //hopefully generic...
  function doView($file) {
    osc_run_hook("before_admin_html");
    osc_current_admin_theme_path($file);
    Session::newInstance()->_clearVariables();
    osc_run_hook("after_admin_html");
  }
}

/* file end: ./oc-admin/locations.php */