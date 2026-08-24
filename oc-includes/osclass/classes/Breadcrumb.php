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


/**
 * Class Breadcrumb
 */
class Breadcrumb {
  private $location;
  private $section;
  private $title;
  protected $aLevel;

  /**
   * Breadcrumb constructor.
   *
   * @param array $lang
   */
  public function __construct($lang = array ()) {
    $this->location = Rewrite::newInstance()->get_location();
    $this->section = Rewrite::newInstance()->get_section();
    $this->aLevel = array();
    $this->setTitles($lang);
  }

  public function init() {
    // first check if breadcrumbs should be generated
    if(osc_breadcrumbs_hide($this->getLocation(), $this->getSection()) || osc_breadcrumbs_hide_custom($this->getLocation(), $this->getSection())) {
      return false;
    }
    
    if(in_array($this->getLocation(), array('item', 'page', 'search', 'login', 'register', 'user', 'contact', 'custom'))) {
      if(!(osc_is_ad_page() && !osc_item_breadcrumbs_page_title())) {
        $l = array(
          'url' => osc_base_url(),
          'title' => osc_apply_filter('breadcrumbs_page_title_filter', osc_page_title())
        );
        
        $this->addLevel($l);
      }
    }

    switch($this->getLocation()) {
      case('item'):
        if($this->getSection() === 'item_add') {
          $l = array('title' => $this->title['item_add']);
          $this->addLevel($l);
          break;
        }

        // Item country
        if(osc_item_breadcrumbs_country()) {
          if(osc_item_country_code() !== null && trim(osc_item_country_code()) != '') {
            $l = array (
              'url' => osc_search_url(array('page' => 'search', 'sCountry' => osc_item_country_code())),
              'title' => osc_item_country()
            );
            
            $this->addLevel($l);
          }
        }


        // Item region
        if(osc_item_breadcrumbs_region()) {
          if(osc_item_region_id() !== null && (int)osc_item_region_id() > 0) {
            $l = array (
              'url' => osc_search_url(array('page' => 'search', 'sRegion' => osc_item_region_id())),
              'title' => osc_item_region()
            );
            
            $this->addLevel($l);
          }
        }

        
        // Item city
        if(osc_item_breadcrumbs_city()) {
          if(osc_item_city_id() !== null && (int)osc_item_city_id() > 0) {
            $l = array (
              'url' => osc_search_url(array('page' => 'search', 'sCity' => osc_item_city_id())),
              'title' => osc_item_city()
            );
            
            $this->addLevel($l);
          }
        }

        
        // New category block - update 440
        View::newInstance()->_erase('categories');
        View::newInstance()->_erase('subcategories');
        
        if(osc_item_breadcrumbs_category()) {
          if(osc_item_breadcrumbs_parent_categories()) {
            $hierarchy = Category::newInstance()->toRootTree(osc_item_category_id());

            if(is_array($hierarchy) && count($hierarchy) > 0) {
              foreach($hierarchy as $h) {
                if(isset($h['pk_i_id'])) {
                  $hCategory = osc_get_category('id', $h['pk_i_id']);
                  View::newInstance()->_exportVariableToView('category', $hCategory);

                  $l = array (
                    'url' => osc_search_category_url() ,
                    'title' => osc_category_name()
                  );
                  
                  $this->addLevel($l);
                }
              }
            }
            
          } else {
            $aCategory = osc_get_category('id', osc_item_category_id());
            View::newInstance()->_exportVariableToView('category', $aCategory);

            $l = array (
              'url' => osc_search_category_url() ,
              'title' => osc_category_name()
            );
            
            $this->addLevel($l);
          }
        }


        switch ($this->getSection()) {
          case('item_edit'):
            $l = array('url' => osc_item_url(), 'title' => osc_item_title());
            $this->addLevel($l);
            
            $l = array('title' => $this->title['item_edit']);
            $this->addLevel($l);
            break;
            
          case('send_friend'):
            $l = array('url' => osc_item_url(), 'title' => osc_item_title());
            $this->addLevel($l);
            
            $l = array('title' => $this->title['item_send_friend']);
            $this->addLevel($l);
            break;
            
          case('contact'):
            $l = array('url' => osc_item_url(), 'title' => osc_item_title());
            $this->addLevel($l);
            
            $l = array('title' => $this->title['item_contact']);
            $this->addLevel($l);
            break;
            
          case(''):
            $l = array('title' => osc_item_title());
            $this->addLevel($l);
            break;
            
          default:
            $l = array('title' => Rewrite::newInstance()->get_title());
            $this->addLevel($l);
            break;
        }
        break;
        
      case('search'):
        $country_code = osc_search_country_code();
        $country = osc_search_country();
        $region_id = osc_search_region_id();
        $region = osc_search_region();
        $city_id = osc_search_city_id();
        $city = osc_search_city();
        $pattern = osc_search_pattern();
        $category = osc_search_category_id();
        
        $category = ((count($category) == 1) ? $category[0] : '');

        $b_show_all = ($pattern == '' && $category == '' && $region == '' && $city == '');
        $b_category = ($category != '');
        $b_pattern = ($pattern != '');
        $b_country = ($country != '' || $country_code != '');
        $b_region = ($region != '' || $region_id > 0);
        $b_city = ($city != '' || $city_id > 0);
        $b_location = ($b_country || $b_region || $b_city);

        // Show all
        if($b_show_all) {
          $l = array('title' => $this->title['search']);
          $this->addLevel($l);
          break; 
        }

        // Category
        if($b_category) {
          $aCategories = Category::newInstance()->toRootTree($category);
          
          foreach($aCategories as $c) {
            View::newInstance()->_erase('categories');
            View::newInstance()->_erase('subcategories');
            View::newInstance()->_exportVariableToView('category', $c);

            $l = array(
              'url' => osc_search_category_url(),
              'title' => osc_category_name()
            );

            $this->addLevel($l);
          }
        }

        // Location
        if($b_location) {
          $params = array();
          
          $country_row = osc_get_country_row($country_code);
          $region_row = osc_get_region_row($region_id);
          $city_row = osc_get_city_row($city_id);
          
          if($b_category) {
            $params['sCategory'] = $category;
          }

          // Get city row
          if($b_city && ($city_row === false || !isset($city_row['pk_i_id']))) {
            if($b_region) {
              $_region = Region::newInstance()->findByName($region);
              
              if(isset($_region['pk_i_id'])) {
                $city_row = City::newInstance()->findByName($city, $_region['pk_i_id']);
              }
            } else {
              $city_row = City::newInstance()->findByName($city);
            }
          }
          
          // Found city, get it's country & region
          if($city_row !== false && isset($city_row['pk_i_id'])) {
            $country_row = osc_get_country_row($city_row['fk_c_country_code']);
            $region_row = osc_get_region_row($city_row['fk_i_region_id']);
          }
          
          
          // Get region row
          if($b_region && ($region_row === false || !isset($region_row['pk_i_id']))) {
            if($b_country) {
              $_country = Country::newInstance()->findByName($country);
              
              if(isset($_country['pk_c_code'])) {
                $region_row = Region::newInstance()->findByName($region, $_country['pk_c_code']);
              }
            } else {
              $region_row = Region::newInstance()->findByName($region);
            }
          }
          
          // Found region, get it's country
          if($region_row !== false && isset($region_row['pk_i_id'])) {
            $country_row = osc_get_country_row($region_row['fk_c_country_code']);
          }


          // Get country row
          if($b_country && ($country_row === false || !isset($country_row['pk_c_code']))) {
            $country_row = Country::newInstance()->findByName($country);
          }


          // Now let's add what we found
          $p_country = $country;
          $p_country_name = $country;
          
          if($country_row !== false && isset($country_row['pk_c_code'])) {
            $p_country = $country_row['pk_c_code'];
            $p_country_name = osc_location_native_name_selector($country_row, 's_name');;
          }
          
          $p_region = $region;
          $p_region_name = $region;
          
          if($region_row !== false && isset($region_row['pk_i_id'])) {
            $p_region = $region_row['pk_i_id'];
            $p_region_name = osc_location_native_name_selector($region_row, 's_name');
          }
          
          $p_city = $city;
          $p_city_name = $city;
          
          if($city_row !== false && isset($city_row['pk_i_id'])) {
            $p_city = $city_row['pk_i_id'];
            $p_city_name = osc_location_native_name_selector($city_row, 's_name');
          }


          // Add it to breadcrumbs
          if($p_country != '') {
            $params['sCountry'] = $p_country;
            
            $l = array(
              'url' => osc_search_url($params),
              'title' => $p_country_name
            );

            $this->addLevel($l);
          }
          
          if($p_region != '') {
            unset($params['sCountry']);   // Unset to get canonical URL
            $params['sRegion'] = $p_region;
            
            $l = array(
              'url' => osc_search_url($params),
              'title' => $p_region_name
            );

            $this->addLevel($l);
          }
          
          if($p_city != '') {
            unset($params['sCountry']);   // Unset to get canonical URL
            unset($params['sRegion']);
            $params['sCity'] = $p_city;
            
            $l = array(
              'url' => osc_search_url($params),
              'title' => $p_city_name
            );

            $this->addLevel($l);
          }
        }

        // Pattern
        if($b_pattern) {
          $l = array('title' => sprintf($this->title['search_pattern'], $pattern));
          $this->addLevel($l);
        }

        // remove url from the last node
        $nodes = $this->getaLevel();
        if($nodes > 0) {
          if(array_key_exists('url', $nodes[count($nodes) - 1])) {
            unset($nodes[count($nodes) - 1]['url']);
          }
        }
        
        $this->setaLevel($nodes);
        break;
        
      case('user'):
        // use dashboard without url if you're in the dashboards
        if($this->getSection() === 'dashboard') {
          $l = array('title' => $this->title['user_dashboard']);
          $this->addLevel($l);
          break;
        }

        // use dashboard without url if you're in the dashboards
        if($this->getSection() === 'pub_profile') {
          $l = array('title' => sprintf($this->title['user_dashboard_profile'], osc_user_name()));
          $this->addLevel($l);
          break;
        }

        $l = array(
          'url' => osc_user_dashboard_url(),
          'title' => $this->title['user_account']
        );
        
        $this->addLevel($l);

        switch($this->getSection()) {
          case('items'):
            $l = array('title' => $this->title['user_items']);
            $this->addLevel($l);
            break;

          case('alerts'):
            $l = array('title' => $this->title['user_alerts']);
            $this->addLevel($l);
            break;

          case('profile'):
            $l = array('title' => $this->title['user_profile']);
            $this->addLevel($l);
            break;

          case('change_email'):
            $l = array('title' => $this->title['user_change_email']);
            $this->addLevel($l);
            break;

          case('change_password'):
            $l = array('title' => $this->title['user_change_password']);
            $this->addLevel($l);
            break;

          case('change_username'):
            $l = array('title' => $this->title['user_change_username']);
            $this->addLevel($l);
            break;

          default:
            $l = array('title' => Rewrite::newInstance()->get_title());
            $this->addLevel($l);
            break;

        }
        break;
        
      case('login'):
        switch($this->getSection()) {
          case('recover'):
            $l = array('title' => $this->title['login_recover']);
            $this->addLevel($l);
            break;
            
          case('forgot'):
            $l = array('title' => $this->title['login_forgot']);
            $this->addLevel($l);
            break;
            
          case(''):
            $l = array('title' => $this->title['login']);
            $this->addLevel($l);
            break;
        }
        break;
        
      case('register'):
        $l = array('title' => $this->title['register']);
        $this->addLevel($l);
        break;
        
      case('page'):
        $l = array('title' => osc_static_page_title());
        $this->addLevel($l);
        break;
        
      case('contact'):
        $l = array('title' => $this->title['contact']);
        $this->addLevel($l);
        break;
        
      case('custom'):
        $l = array('title' => Rewrite::newInstance()->get_title());
        $this->addLevel($l);
        break;
        
    }
  }

  /**
   * @param string $separator
   *
   * @return string
   */
  public function render($separator = '&raquo;') {
    if(count($this->aLevel) == 0) {
      return '';
    }

    $node = array();
    
    for($i = 0, $iMax = count($this->aLevel); $i < $iMax; $i ++) {
      $text = '<li itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem" ';
      // set a class style for first and last <li>
      if($i == 0) {
        $text .= 'class="first-child" ';
      }
      if(($i == (count($this->aLevel) - 1)) && ($i != 0)) {
        $text .= 'class="last-child" ';
      }

      $text .= '>';

      // set separator
      if($i > 0) {
        $text .= ' ' . $separator . ' ';
      }
      // create span tag
      $title = '<span itemprop="name">' . $this->aLevel[$i]['title'] . '</span>';

      if(array_key_exists('url', $this->aLevel[$i])) {
        $title = '<a href="' . osc_esc_html($this->aLevel[$i]['url']) . '" itemprop="item">' . $title . '</a>';
      }

      $title .= '<meta itemprop="position" content="' . ($i + 1) . '" />';
      $node[] = $text . $title . '</li>' . PHP_EOL;
    }

    $result = '<ul class="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList"><meta itemprop="name" content="Breadcrumb"/>' . PHP_EOL;
    $result .= implode(PHP_EOL, $node);
    $result .= '</ul>' . PHP_EOL;

    return $result;
  }

  /**
   * Set the texts for the breadcrumb
   *
   * @since 3.1
   *
   * @param $lang
   */
  public function setTitles($lang) {
    // default titles
    $this->title['item_add'] = __('Publish a listing');
    $this->title['item_edit'] = __('Edit your listing');
    $this->title['item_send_friend'] = __('Send to a friend');
    $this->title['item_contact'] = __('Contact publisher');
    $this->title['search'] = __('Search results');
    $this->title['search_pattern'] = __('Search results: %s');
    $this->title['user_dashboard'] = __('Dashboard');
    $this->title['user_dashboard_profile'] = __("%s's profile");
    $this->title['user_account'] = __('Account');
    $this->title['user_items'] = __('My listings');
    $this->title['user_alerts'] = __('My alerts');
    $this->title['user_profile'] = __('Update my profile');
    $this->title['user_change_email'] = __('Change my email');
    $this->title['user_change_username'] = __('Change my username');
    $this->title['user_change_password'] = __('Change my password');
    $this->title['login'] = __('Login');
    $this->title['login_recover'] = __('Recover your password');
    $this->title['login_forgot'] = __('Change your password');
    $this->title['register'] = __('Create a new account');
    $this->title['contact'] = __('Contact');


    if(!is_array($lang)) {
      return;
    }

    foreach($lang as $k => $v) {
      if(array_key_exists($k, $this->title)) {
        $this->title[$k] = $v;
      }
    }
  }

  /**
   * @return array
   */
  public function getaLevel() {
    return $this->aLevel;
  }

  /**
   * @param $aLevel
   */
  public function setaLevel($aLevel) {
    $this->aLevel = $aLevel;
  }

  /**
   * @param $location
   */
  public function setLocation($location) {
    $this->location = $location;
  }

  /**
   * @return string
   */
  public function getLocation() {
    return $this->location;
  }

  /**
   * @param $section
   */
  public function setSection($section) {
    $this->section = $section;
  }

  /**
   * @return string
   */
  public function getSection() {
    return $this->section;
  }

  /**
   * @param $level
   */
  public function addLevel($level) {
    if(!is_array($level)) {
      return;
    }
    
    $this->aLevel[] = $level;
  }
}

/* file end: ./oc-includes/osclass/classes/Breadcrumb.php */