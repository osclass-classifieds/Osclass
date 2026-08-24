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
* Helper Location
* @package Osclass
* @subpackage Helpers
* @author Osclass
*/


/**
 * Gets country array from view and cache it if not exists
 *
 * @return array
 */
function osc_get_country_row($code, $cache = true) {
  if(trim((string)$code) == '') {
    return false;
  }
  
  $code = strtoupper(trim((string)$code));

  if($cache === true && View::newInstance()->_exists('country_' . $code)) {
    return View::newInstance()->_get('country_' . $code);
  }
  
  if(OPTIMIZE_COUNTRIES === true) { 
    $countries = osc_get_countries();
    
    // Search country in session data
    if(is_array($countries) && count($countries) > 0) {
      $search_index = array_search((string)$code, array_column($countries, 'pk_c_code'), true);
      
      if($search_index !== false) {
        $country = $countries[$search_index];
        View::newInstance()->_exportVariableToView('country_' . $code, $country);
        return $country;
      }
    }
  }

  // Search in database
  $country = Country::newInstance()->findByCode($code);
  View::newInstance()->_exportVariableToView('country_' . $code, $country);
  
  return $country;
}


/**
 * Gets country array from view and cache it if not exists
 *
 * @return array
 */
function osc_get_country_row_by_slug($slug, $cache = true) {
  if(trim((string)$slug) == '') {
    return false;
  }
  
  $slug = strtolower(trim((string)$slug));

  if($cache === true && View::newInstance()->_exists('country_' . $slug)) {
    return View::newInstance()->_get('country_' . $slug);
  }
  
  if(OPTIMIZE_COUNTRIES === true) { 
    $countries = osc_get_countries();

    // Search country in session data
    if(is_array($countries) && count($countries) > 0) {
      $search_index = array_search((string)$slug, array_column($countries, 's_slug'), true);
      
      if($search_index !== false) {
        $country = $countries[$search_index];
        View::newInstance()->_exportVariableToView('country_' . $slug, $country);
        return $country;
      }
    }
  }

  // Search in database
  $country = Country::newInstance()->findBySlug($slug);
  View::newInstance()->_exportVariableToView('country_' . $slug, $country);
  
  return $country;
}


/**
 * Count all countries
 *
 * @return array
 */
function osc_count_countries_all() {
  if(View::newInstance()->_exists('count_countries')) {
    return View::newInstance()->_get('count_countries');
  }

  $count = Country::newInstance()->count();
  View::newInstance()->_exportVariableToView('count_countries', (int)$count);
  
  return (int)$count;
}


/**
 * Gets region array from view and cache it if not exists
 *
 * @return array
 */
function osc_get_region_row($id, $cache = true) {
  if($id <= 0) {
    return false;
  }
  
  $id = (int)$id;

  if($cache === true && View::newInstance()->_exists('region_' . $id)) {
    return View::newInstance()->_get('region_' . $id);
  }

  // If there is more regions in DB, it's not effective way
  if(OPTIMIZE_REGIONS === true && osc_count_regions_all() < OPTIMIZE_REGIONS_LIMIT) {
    $regions = osc_get_regions();

    // Search region in session data
    if(is_array($regions) && count($regions) > 0) {
      $search_index = array_search((string)$id, array_column($regions, 'pk_i_id'), true);

      if($search_index !== false) {
        $region = $regions[$search_index];
        View::newInstance()->_exportVariableToView('region_' . $id, $region);
        return $region;
      }
    }
  }

  // Search in database
  $region = Region::newInstance()->findByPrimaryKey($id);
  View::newInstance()->_exportVariableToView('region_' . $id, $region);
  
  return $region;
}



/**
 * Gets region array from view and cache it if not exists
 *
 * @return array
 */
function osc_get_region_row_by_slug($slug, $cache = true) {
  if(trim((string)$slug) == '') {
    return false;
  }
  
  $slug = strtolower(trim((string)$slug));

  if($cache === true && View::newInstance()->_exists('region_' . $slug)) {
    return View::newInstance()->_get('region_' . $slug);
  }
  
  // If there is more regions in DB, it's not effective way
  if(OPTIMIZE_REGIONS === true && osc_count_regions_all() < OPTIMIZE_REGIONS_LIMIT) {
    $regions = osc_get_regions();

    // Search region in session data
    if(is_array($regions) && count($regions) > 0) {
      $search_index = array_search((string)$slug, array_column($regions, 's_slug'), true);
      
      if($search_index !== false) {
        $region = $regions[$search_index];
        View::newInstance()->_exportVariableToView('region_' . $slug, $region);
        return $region;
      }
    }
  }

  // Search in database
  $region = Region::newInstance()->findBySlug($slug);
  View::newInstance()->_exportVariableToView('region_' . $slug, $region);
  
  return $region;
}


/**
 * Count all regions
 *
 * @return array
 */
function osc_count_regions_all() {
  if(View::newInstance()->_exists('count_regions')) {
    return View::newInstance()->_get('count_regions');
  }

  $count = Region::newInstance()->count();
  View::newInstance()->_exportVariableToView('count_regions', (int)$count);
  
  return (int)$count;
}



/**
 * Gets city array from view and cache it if not exists
 *
 * @return array
 */
function osc_get_city_row($id, $cache = true) {
  if($id <= 0) {
    return false;
  }
  
  $id = (int)$id;

  if($cache === true && View::newInstance()->_exists('city_' . $id)) {
    return View::newInstance()->_get('city_' . $id);
  }

  // If there is more cities in DB, it's not effective way
  if(OPTIMIZE_CITIES === true && osc_count_cities_all() < OPTIMIZE_CITIES_LIMIT) {
    $cities = osc_get_cities();

    // Search city in session data
    if(is_array($cities) && count($cities) > 0) {
      $search_index = array_search((string)$id, array_column($cities, 'pk_i_id'), true);
      
      if($search_index !== false) {
        $city = $cities[$search_index];
        View::newInstance()->_exportVariableToView('city_' . $id, $city);
        return $city;
      }
    }
  }

  // Search in database
  $city = City::newInstance()->findByPrimaryKey($id);
  View::newInstance()->_exportVariableToView('city_' . $id, $city);
  
  return $city;
}


/**
 * Gets city array from view and cache it if not exists
 *
 * @return array
 */
function osc_get_city_row_by_slug($slug, $cache = true) {
  if(trim((string)$slug) == '') {
    return false;
  }
  
  $slug = strtolower(trim((string)$slug));

  if($cache === true && View::newInstance()->_exists('city_' . $slug)) {
    return View::newInstance()->_get('city_' . $slug);
  }
  
  // If there is more cities in DB, it's not effective way
  if(OPTIMIZE_CITIES === true && osc_count_cities_all() < OPTIMIZE_CITIES_LIMIT) {
    $cities = osc_get_cities();

    // Search city in session data
    if(is_array($cities) && count($cities) > 0) {
      $search_index = array_search((string)$slug, array_column($cities, 's_slug'), true);
      
      if($search_index !== false) {
        $city = $cities[$search_index];
        View::newInstance()->_exportVariableToView('city_' . $slug, $city);
        return $city;
      }
    }
  }

  // Search in database
  $city = City::newInstance()->findBySlug($slug);
  View::newInstance()->_exportVariableToView('city_' . $slug, $city);
  
  return $city;
}


/**
 * Count all cities
 *
 * @return array
 */
function osc_count_cities_all() {
  if(View::newInstance()->_exists('count_cities')) {
    return View::newInstance()->_get('count_cities');
  }

  $count = City::newInstance()->count();
  View::newInstance()->_exportVariableToView('count_cities', (int)$count);
  
  return (int)$count;
}



/**
 * Gets list of countries
 *
 * @return array
 */
function osc_get_countries() {
  if(!View::newInstance()->_exists('countries')) {
    $countries = Country::newInstance()->listAll();
    View::newInstance()->_exportVariableToView('countries', $countries);
    return $countries;
  }
  
  return View::newInstance()->_get('countries');
}

/**
 * Gets list of regions (from a country)
 *
 * @param string $country
 *
 * @return array|string
 */
function osc_get_regions($country = '') {
  if(!View::newInstance()->_exists('regions' . $country)) {
    if($country == '') {
      $regions = Region::newInstance()->listAll();
    } else {
      $regions = Region::newInstance()->findByCountry($country);
    }
    
    View::newInstance()->_exportVariableToView('regions' . $country, $regions);
    return $regions;
  }
  
  
  return View::newInstance()->_get('regions' . $country);
}

/**
 * Gets list of cities (from a region)
 *
 * @param string $region
 *
 * @return array|string
 */
function osc_get_cities($region = '') {
  if(!View::newInstance()->_exists('cities' . $region)) {
    if($region == '') {
      $cities = City::newInstance()->listAll();
    } else {
      $cities = City::newInstance()->findByRegion($region);
    }
    
    View::newInstance()->_exportVariableToView('cities' . $region, $cities);
    return $cities;
  }
  
  return View::newInstance()->_get('cities' . $region);
}


  
/**
 * Gets current country
 *
 * @return array|string
 */
function osc_country() {
  if (View::newInstance()->_exists('countries')) {
    return View::newInstance()->_current('countries');
  } else {
    return null;
  }
}


/**
 * Gets current region
 *
 * @return array|string
 */
function osc_region() {
  if (View::newInstance()->_exists('regions')) {
    return View::newInstance()->_current('regions');
  } else {
    return null;
  }
}


/**
 * Gets current city
 *
 * @return array|string
 */
function osc_city() {
  if (View::newInstance()->_exists('cities')) {
    return View::newInstance()->_current('cities');
  } else {
    return null;
  }
}


/**
 * Gets current city area
 *
 * @return array|string
 */
function osc_city_area() {
  if (View::newInstance()->_exists('city_areas')) {
    return View::newInstance()->_current('city_areas');
  } else {
    return null;
  }
}


/**
 * Iterator for countries, return null if there's no more countries
 *
 * @return bool
 */
function osc_has_countries() {
  if ( !View::newInstance()->_exists('countries') ) {
    View::newInstance()->_exportVariableToView('countries', Search::newInstance()->listCountries( '>=' , 'country_name ASC' ) );
  }
  return View::newInstance()->_next('countries');
}


/**
 * Iterator for regions, return null if there's no more regions
 *
 * @param string $country
 *
 * @return bool
 * @throws \Exception
 */
function osc_has_regions($country = '%%%%') {
  if ( !View::newInstance()->_exists('regions') ) {
    View::newInstance()->_exportVariableToView('regions', Search::newInstance()->listRegions( $country, '>=' , 'region_name ASC' ) );
  }
  return View::newInstance()->_next('regions');
}


/**
 * Iterator for cities, return null if there's no more cities
 *
 * @param string $region
 *
 * @return bool
 * @throws \Exception
 */
function osc_has_cities($region = '%%%%') {
  if ( !View::newInstance()->_exists('cities') ) {
    View::newInstance()->_exportVariableToView('cities', Search::newInstance()->listCities( $region, '>=' ) );
  }
  $result = View::newInstance()->_next('cities');

  if ( ! $result ) {
    View::newInstance()->_erase( 'cities' );
  }
  return $result;
}


/**
 * Iterator for city areas, return null if there's no more city areas
 *
 * @param string $city
 * @return bool
 */
function osc_has_city_areas($city = '%%%%') {
  if ( !View::newInstance()->_exists('city_areas') ) {
    View::newInstance()->_exportVariableToView('city_areas', Search::newInstance()->listCityAreas( $city, '>=' , 'city_area_name ASC' ) );
  }
  $result = View::newInstance()->_next('city_areas');

  if ( ! $result ) {
    View::newInstance()->_erase( 'city_areas' );
  }
  return $result;
}

/**
 * Gets number of countries
 *
 * @return int
 */
function osc_count_countries() {
  if ( !View::newInstance()->_exists('contries') ) {
    View::newInstance()->_exportVariableToView('countries', Search::newInstance()->listCountries( '>' , 'country_name ASC' ) );   // replacing >= with > to improve performance drastically
  }
  return View::newInstance()->_count('countries');
}


/**
 * Gets number of regions
 *
 * @param string $country
 *
 * @return int
 * @throws \Exception
 */
function osc_count_regions($country = '%%%%') {
  if ( !View::newInstance()->_exists('regions') ) {
    View::newInstance()->_exportVariableToView('regions', Search::newInstance()->listRegions( $country, '>' , 'region_name ASC' ) );   // replacing >= with > to improve performance drastically
  }
  return View::newInstance()->_count('regions');
}


/**
 * Gets number of cities
 *
 * @param string $region
 *
 * @return int
 * @throws \Exception
 */
function osc_count_cities($region = '%%%%') {
  if ( !View::newInstance()->_exists('cities') ) {
    View::newInstance()->_exportVariableToView('cities', Search::newInstance()->listCities( $region, '>' ) );   // replacing >= with > to improve performance drastically
  }
  return View::newInstance()->_count('cities');
}


/**
 * Gets number of city areas
 *
 * @param string $city
 * @return int
 */
function osc_count_city_areas($city = '%%%%') {
  if ( !View::newInstance()->_exists('city_areas') ) {
    View::newInstance()->_exportVariableToView('city_areas', Search::newInstance()->listCityAreas( $city, '>' , 'city_area_name ASC' ) );   // replacing >= with > to improve performance drastically
  }
  return View::newInstance()->_count('city_areas');
}

/**
 * Gets country's name
 *
 * @return string
 */
function osc_country_name() {
  if(osc_get_current_user_locations_native() == 1) {
    return (osc_field(osc_country(), 'country_name_native', '') <> '' ? osc_field(osc_country(), 'country_name_native', '') : osc_field(osc_country(), 'country_name', ''));
  }
  return osc_field(osc_country(), 'country_name', '');
}

/**
 * Gets country's items
 *
 * @return int
 */
function osc_country_items() {
  return osc_field(osc_country(), 'items', '');
}

/**
 * Gets region's name
 *
 * @return array|string
 */
function osc_region_name() {
  if(osc_get_current_user_locations_native() == 1) {
    return (osc_field(osc_region(), 'region_name_native', '') <> '' ? osc_field(osc_region(), 'region_name_native', '') : osc_field(osc_region(), 'region_name', ''));
  }
  return osc_field(osc_region(), 'region_name', '');
}

/**
 * Gets region's items
 *
 * @return int
 */
function osc_region_items() {
  return osc_field(osc_region(), 'items', '');
}

/**
 * Gets city's name
 *
 * @return string
 */
function osc_city_name() {
  if(osc_get_current_user_locations_native() == 1) {
    return (osc_field(osc_city(), 'city_name_native', '') <> '' ? osc_field(osc_city(), 'city_name_native', '') : osc_field(osc_city(), 'city_name', ''));
  }
  return osc_field(osc_city(), 'city_name', '');
}

/**
 * Gets city's items
 *
 * @return int
 */
function osc_city_items() {
  return osc_field(osc_city(), 'items', '');
}

/**
 * Gets city area's name
 *
 * @return string
 */
function osc_city_area_name() {
  return osc_field(osc_city_area(), 'city_area_name', '');
}

/**
 * Gets city area's items
 *
 * @return int
 */
function osc_city_area_items() {
  return osc_field(osc_city_area(), 'items', '');
}


/**
 * Gets country's url
 *
 * @return string
 * @throws \Exception
 */
function osc_country_url() {
  return osc_search_url(array('sCountry' => osc_country_name()));
}


/**
 * Gets region's url
 *
 * @return string
 * @throws \Exception
 */
function osc_region_url() {
  return osc_search_url(array('sRegion' => osc_region_name()));
}


/**
 * Gets city's url
 *
 * @return string
 * @throws \Exception
 */
function osc_city_url() {
  return osc_search_url(array('sCity' => osc_city_name()));
}


/**
 * Gets city area's url
 *
 * @return string
 * @throws \Exception
 */
function osc_city_area_url() {
  return osc_search_url(array('sCityArea' => osc_city_area_name()));
}


/*
  GET USER RELATED COUNTRY/REGION/CITY
*/

/**
 * Gets list of countries
 *
 * @return array
 */
function osc_get_user_item_countries($user_id = null) {
  $user_id = (int)($user_id === null ? osc_logged_user_id() : $user_id);
  
  if(!View::newInstance()->_exists('user_item_countries')) {
    $countries = Country::newInstance()->listUser($user_id);
    View::newInstance()->_exportVariableToView('user_item_countries', $countries);
    return $countries;
  }
  
  return View::newInstance()->_get('user_item_countries');
}

/**
 * Gets list of regions
 *
 * @return array|string
 */
function osc_get_user_item_regions($user_id = null) {
  $user_id = (int)($user_id === null ? osc_logged_user_id() : $user_id);
  
  if(!View::newInstance()->_exists('user_item_regions')) {
    $regions = Region::newInstance()->listUser($user_id);
    View::newInstance()->_exportVariableToView('user_item_regions', $regions);
    return $regions;
  }
  
  return View::newInstance()->_get('user_item_regions');
}

/**
 * Gets list of cities
 *
 * @return array|string
 */
function osc_get_user_item_cities($user_id = null) {
  $user_id = (int)($user_id === null ? osc_logged_user_id() : $user_id);
  
  if(!View::newInstance()->_exists('user_item_cities')) {
    $cities = City::newInstance()->listUser($user_id);
    View::newInstance()->_exportVariableToView('user_item_cities', $cities);
    return $cities;
  }
  
  return View::newInstance()->_get('user_item_cities');
}