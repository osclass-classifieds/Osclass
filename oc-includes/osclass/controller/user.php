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
 * Class CWebUser
 */
class CWebUser extends WebSecBaseModel {
  public $uri;
  
  public function __construct() {
    parent::__construct();
    
    if(!osc_users_enabled()) {
      osc_add_flash_error_message(_m('Users not enabled'));
      $this->redirectTo(osc_base_url());
    }
    
    osc_run_hook('init_user');

    // Get only params in form: /sCategory,1/sRegion,2/...
    if(osc_rewrite_enabled() && $this->action == 'items') {
      $this->uri = preg_replace('|^' . REL_WEB_URL . '|', '', Params::getServerParam('REQUEST_URI', false, false));

      // User items URL is in raw form: /index.php?page=user&action=items&sPattern=&sCategory=...
      if(preg_match('/^index\.php/', $this->uri)>0) {
        $canonical_url = osc_user_items_url(Params::getParamsAsArray());
        $this->redirectTo($canonical_url, 301);
      }

      $parts = explode(osc_get_preference('rewrite_user_items'), $this->uri);
      $this->uri = str_replace('//', '/', '/' . end($parts));
      
      $this->uri = rtrim((string)$this->uri, '/');
    }
  }

  //Business Layer...
  public function doModel() {
    switch($this->action) {
      case('dashboard'):    //dashboard...
        $max_items = (Params::getParam('max_items')!='')?Params::getParam('max_items'):20;
        $aItems = Item::newInstance()->findByUserIDEnabled(osc_logged_user_id(), 0, $max_items);
        
        //calling the view...
        $this->_exportVariableToView('items', $aItems);
        $this->_exportVariableToView('max_items', $max_items);
        $this->doView('user-dashboard.php');
        break;
        
      case('profile'):    //profile...
        $aUser = User::newInstance()->findByPrimaryKey(osc_logged_user_id());
        $aCountries = Country::newInstance()->listAll();
        $aRegions = array();
        
        if($aUser['fk_c_country_code'] != '') {
          $aRegions = Region::newInstance()->findByCountry($aUser['fk_c_country_code']);
        } elseif(count($aCountries) > 0) {
          $aRegions = Region::newInstance()->findByCountry($aCountries[0]['pk_c_code']);
        }
        
        $aCities = array();
        if($aUser['fk_i_region_id'] > 0) {
          $aCities = City::newInstance()->findByRegion($aUser['fk_i_region_id']);
        } else if(count($aRegions) > 0) {
          $aCities = City::newInstance()->findByRegion($aRegions[0]['pk_i_id']);
        }

        // user profile info description | user-profile.php @ frontend
        $aLocale = $aUser['locale'];
        foreach ($aLocale as $locale => $aInfo) {
          $aUser['locale'][$locale]['s_info'] = osc_apply_filter('user_profile_info', $aInfo['s_info'], $aUser['pk_i_id'], $aInfo['fk_c_locale_code']);
        }

        //calling the view...
        $this->_exportVariableToView('user', $aUser);
        $this->_exportVariableToView('countries', $aCountries);
        $this->_exportVariableToView('regions', $aRegions);
        $this->_exportVariableToView('cities', $aCities);
        $this->_exportVariableToView('locales', osc_get_locales());

        $this->doView('user-profile.php');
        break;
        
      case('profile_post'):   //profile post...
        osc_csrf_check();
        $userId = Session::newInstance()->_get('userId');

        require_once LIB_PATH . 'osclass/UserActions.php';
        $userActions = new UserActions(false);
        $success = $userActions->edit($userId);
        
        if($success==1 || $success==2) {
          osc_add_flash_ok_message(_m('Your profile has been updated successfully'));
        } else {
          osc_add_flash_error_message($success);
        }

        if(Params::getParam('pp_blob') <> '') {
          $user = User::newInstance()->findByPrimaryKey($userId);
          
          if($user['s_profile_img'] <> '') {
            @unlink(osc_content_path() . 'uploads/user-images/' . $user['s_profile_img']);
          }

          // This function save blob into oc-content/uploads/user-images/ folder and update user record
          osc_base64_to_image(Params::getParam('pp_blob'), $userId);
        }

        $this->redirectTo(osc_user_profile_url());
        break;
        
      case('alerts'):     //alerts
        $aAlerts = Alerts::newInstance()->findByUser(Session::newInstance()->_get('userId'));
        $user = osc_get_user_row(Session::newInstance()->_get('userId'));
        
        foreach($aAlerts as $k => $a) {
          $array_conditions = (array)json_decode($a['s_search'], true);

          // Check if alert structure is OK
          if(isset($array_conditions['price_min'])) {
            $search = new Search();
            $search->setJsonAlert($array_conditions, $a['s_email'], $a['fk_i_user_id']);
            //$search->notFromUser(Session::newInstance()->_get('userId'));
            $search->limit(0, osc_apply_filter('limit_alert_items', 12));

            $aAlerts[$k]['items'] = $search->doSearch();
            $aAlerts[$k]['i_num_items'] = $search->count();
          }
        }

        $this->_exportVariableToView('alerts', $aAlerts);
        View::newInstance()->_reset('alerts');
        $this->_exportVariableToView('user', $user);
        $this->doView('user-alerts.php');
        break;
        
      case('change_email'):       //change email
        $this->doView('user-change_email.php');
        break;
        
      case('change_email_post'):    //change email post
        osc_csrf_check();
        
        if(!osc_validate_email(Params::getParam('new_email'))) {
          osc_add_flash_error_message(_m('The specified e-mail is not valid'));
          $this->redirectTo(osc_change_user_email_url());
          
        } else {
          $user = User::newInstance()->findByEmail(Params::getParam('new_email'));
          if(!isset($user['pk_i_id'])) {
            $userEmailTmp = array();
            $userEmailTmp['fk_i_user_id'] = Session::newInstance()->_get('userId');
            $userEmailTmp['s_new_email'] = Params::getParam('new_email');

            UserEmailTmp::newInstance()->insertOrUpdate($userEmailTmp);

            $code = osc_genRandomPassword(30);
            $date = date('Y-m-d H:i:s');

            $userManager = new User();
            $userManager->update(
              array('s_pass_code' => $code, 's_pass_date' => $date, 's_pass_ip' => osc_get_ip()),
              array('pk_i_id' => Session::newInstance()->_get('userId'))
           );

            $validation_url = osc_change_user_email_confirm_url(Session::newInstance()->_get('userId'), $code);
            osc_run_hook('hook_email_new_email', Params::getParam('new_email'), $validation_url);
            $this->redirectTo(osc_user_profile_url());
          } else {
            osc_add_flash_error_message(_m('The specified e-mail is already in use'));
            $this->redirectTo(osc_change_user_email_url());
          }
        }
        
        break;
        
      case('change_username'):    //change username
        $this->doView('user-change_username.php');
        break;
        
      case('change_username_post'):   //change username
        osc_csrf_check();
        $username = osc_sanitize_username(Params::getParam('s_username'));
        osc_run_hook('before_username_change', Session::newInstance()->_get('userId'), $username);
        
        if($username!='') {
          $user = User::newInstance()->findByUsername($username);
          if(isset($user['s_username'])) {
            osc_add_flash_error_message(_m('The specified username is already in use'));
          } else {
            if(!osc_is_username_blacklisted($username)) {
              User::newInstance()->update(array('s_username' => $username), array('pk_i_id' => Session::newInstance()->_get('userId')));
              osc_add_flash_ok_message(_m('The username was updated'));
              osc_run_hook('after_username_change', Session::newInstance()->_get('userId'), Params::getParam('s_username'));
              
              $this->redirectTo(osc_user_profile_url());
              
            } else {
              osc_add_flash_error_message(_m('The specified username is not valid, it contains some invalid words'));
            }
          }
        } else {
          osc_add_flash_error_message(_m('The specified username could not be empty'));
        }
        
        $this->redirectTo(osc_change_user_username_url());
        break;
        
      case('change_password'):    //change password
        $this->doView('user-change_password.php');
        break;
        
      case 'change_password_post':  //change password post
        osc_csrf_check();
        
        $user = User::newInstance()->findByPrimaryKey(Session::newInstance()->_get('userId'));

        if((Params::getParam('password', false, false) == '') || (Params::getParam('new_password', false, false) == '') || (Params::getParam('new_password2', false, false) == '')) {
          osc_add_flash_warning_message(_m('Password cannot be blank'));
          $this->redirectTo(osc_change_user_password_url());
        }

        if(!osc_verify_password(Params::getParam('password', false, false), $user['s_password'])) {
          osc_add_flash_error_message(_m("Current password doesn't match"));
          $this->redirectTo(osc_change_user_password_url());
        }

        if(!Params::getParam('new_password', false, false)) {
          osc_add_flash_error_message(_m("Passwords can't be empty"));
          $this->redirectTo(osc_change_user_password_url());
        }

        if(Params::getParam('new_password', false, false) != Params::getParam('new_password2', false, false)) {
          osc_add_flash_error_message(_m("Passwords don't match"));
          $this->redirectTo(osc_change_user_password_url());
        }

        User::newInstance()->update(array('s_password' => osc_hash_password(Params::getParam ('new_password', false, false))), array('pk_i_id' => Session::newInstance()->_get('userId')));

        osc_add_flash_ok_message(_m('Password has been changed'));
        $this->redirectTo(osc_user_profile_url());
        break;
        
      case 'items':           // User listings page
        osc_run_hook('before_user_items');
        
        $url_params = '';

        if(osc_rewrite_enabled()) {
          $url_params = '/' . Params::getParam('sParams', false, false);
          /*
            Let's have sParam = /sCountry,US/sRegion,Alabama/sCategory,ForSale/
            - $matched[0] has raw array of matches: [0] => /sCountry,US  [1] => /sRegion,Alabama  [2] => /sCategory,ForSale
            - $matched[1] has param names: [0] => sCountry  [1] => sRegion  [2] => sCategory
            - $matched[1] has param values: [0] => US  [1] => Alabama  [2] => ForSale
          */
          if(preg_match_all('|\/([^,]+),([^\/]*)|', $url_params, $matched)) {
            $l = count($matched[0]);
            
            for($k = 0; $k<count($matched[0]); $k++) {
              switch($matched[1][$k]) {
                case osc_get_preference('rewrite_search_country'):
                  $matched[1][$k] = 'sCountry';
                  break;
                  
                case osc_get_preference('rewrite_search_region'):
                  $matched[1][$k] = 'sRegion';
                  break;
                  
                case osc_get_preference('rewrite_search_city'):
                  $matched[1][$k] = 'sCity';
                  break;
                  
                case osc_get_preference('rewrite_search_city_area'):
                  $matched[1][$k] = 'sCityArea';
                  break;
                  
                case osc_get_preference('rewrite_search_category'):
                  $matched[1][$k] = 'sCategory';
                  break;
                  
                case osc_get_preference('rewrite_search_user'):
                  $matched[1][$k] = 'sUser';
                  break;
                  
                case osc_get_preference('rewrite_search_pattern'):
                  $matched[1][$k] = 'sPattern';
                  break;
              }

              // Now define params from URL, so sParams get replaced with params itself like sCountry, sRegion, sCity...
              if($matched[1][$k] != 'lang') {
                Params::setParam($matched[1][$k], $matched[2][$k]);
              }
            }
            
            // Drop original params text
            Params::unsetParam('sParams');
          }
        }

        $params = Params::getParamsAsArray();

        // In case lang is subdomain type, drop it
        unset($params['lang']);

        
        // If URL is not in canonical "nice" format, do redirect
        if(osc_rewrite_enabled()) {
          $canonical_url = osc_user_items_url($params);                 // ../category,abc/region,efg/...
          $original_url = osc_user_items_url() . $this->uri;            // ../sCategory,abc/sRegion,efg/...
          
          if($this->uri != '' && urlencode(strip_tags(strtolower(str_replace(array('+', ' '), '', urldecode(trim($original_url)))))) != urlencode(strip_tags(strtolower(str_replace(array('+', ' '), '', urldecode(trim($canonical_url))))))) {
            $referer_url = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');

            // Avoid possible loop redirects
            if($referer_url != $canonical_url) {
              $this->redirectTo($canonical_url, 301);
            }
          }
        }


        $options = array();
        
        if(isset($params['sItemType'])) { 
          $options['item_type'] = $params['sItemType'];
          
        } else if(isset($params['itemType'])) {             // old form of param
          $options['item_type'] = $params['itemType'];
          
        } else {
          $options['item_type'] = '';
        }

        
        if(isset($params['sPattern'])) { 
          $options['pattern'] = $params['sPattern'];
        }
        
        if(isset($params['iItemId']) && $params['iItemId'] > 0) { 
          $options['item_id'] = $params['iItemId'];
        }
        
        if(isset($params['sCategory'])) { 
          // $options['category'] = is_array($params['sCategory']) ? $params['sCategory'] : array($params['sCategory']);
          $options['category'] = $params['sCategory'];
        }
        
        if(isset($params['sCountry'])) { 
          $options['country'] = $params['sCountry'];
        }
        
        if(isset($params['sRegion'])) { 
          $options['region'] = $params['sRegion'];
        }
        
        if(isset($params['sCity'])) { 
          $options['city'] = $params['sCity'];
        }
        
        if(isset($params['sPriceMin']) && $params['sPriceMin'] > 0) { 
          $options['price_min'] = $params['sPriceMin'];
        }
        
        if(isset($params['sPriceMax']) && $params['sPriceMax'] > 0) { 
          $options['price_max'] = $params['sPriceMax'];
        }
        
        if(isset($params['bWithPicture']) && $params['bWithPicture'] == 1) { 
          $options['with_picture'] = $params['bWithPicture'];
        }
        
        if(isset($params['sOnlyPremium'])) { 
          $options['only_premium'] = $params['sOnlyPremium'];
        }
        
        if(isset($params['sOrder'])) { 
          $options['order_column'] = $params['sOrder'];
        }
        
        if(isset($params['sOrderType'])) { 
          $options['order_direction'] = $params['sOrderType'];
        }
        
        if(isset($params['iPage']) && $params['iPage'] > 1) { 
          $options['page'] = $params['iPage'] - 1;
        } else {
          $options['page'] = 0;
        }
        
        if(isset($params['iPerPage']) && $params['iPerPage'] > 0 && $params['iPerPage'] < 100) { 
          $options['per_page'] = (int)$params['iPerPage'];
        } else {
          $options['per_page'] = 16;
        }
        
        $options['per_page'] = (int)osc_apply_filter('user_items_per_page', $options['per_page']);
        $options['per_page'] = ($options['per_page'] <= 0 ? 16 : $options['per_page']);
        
        
        $custom_conditions_and = osc_apply_filter('user_items_custom_conditions_and', '', $params, $url_params);
        
        if($custom_conditions_and) {
          $options['custom_conditions_and'] = $custom_conditions_and;
        }
        
        $custom_conditions_or = osc_apply_filter('user_items_custom_conditions_or', '', $params, $url_params);
        
        if($custom_conditions_or != '') {
          $options['custom_conditions_or'] = $custom_conditions_or;
        }
        

        /*
        $itemsPerPage = (Params::getParam('itemsPerPage')!='')?Params::getParam('itemsPerPage'):20;
        $page = (Params::getParam('iPage') > 0) ? Params::getParam('iPage') -1 : 0;
        $itemType = Params::getParam('itemType');
        $total_items = Item::newInstance()->countItemTypesByUserID(osc_logged_user_id(), $itemType);

        $total_items = Item::newInstance()->countUserItems(osc_logged_user_id(), '', array(
          'item_type' => $itemType
        ));
        */

        $items = Item::newInstance()->findUserItems(osc_logged_user_id(), '', $options);
        $total_items = Item::newInstance()->countUserItems(osc_logged_user_id(), '', $options);


        $total_pages = ceil($total_items / $options['per_page']);
        
        // $items = Item::newInstance()->findItemTypesByUserID(osc_logged_user_id(), $page*$itemsPerPage, $itemsPerPage, $itemType);

        /*
        $items = Item::newInstance()->findUserItems(osc_logged_user_id(), '', array(
          'page' => $page,
          'per_page' => $itemsPerPage,
          'item_type' => $itemType
        ));
        */
        
        $this->_exportVariableToView('items', $items);
        $this->_exportVariableToView('search_page', $options['page']);
        $this->_exportVariableToView('search_total_pages', $total_pages);
        $this->_exportVariableToView('search_total_items', $total_items);
        $this->_exportVariableToView('items_per_page', $options['per_page']);
        $this->_exportVariableToView('items_type', $options['item_type']);
        
        // Custom params to session
        $possible_params = array('item_type','pattern','item_id','category','country','region','city','price_min','price_max','with_picture','only_premium','order_column','order_direction');
        
        foreach($possible_params as $p) {
          if(isset($options[$p])) { 
            $this->_exportVariableToView('search_' . $p, $options[$p]);
            
          } else {
            $this->_exportVariableToView('search_' . $p, NULL);
          }
        }

        $this->_exportVariableToView('search_options', $options);

        $this->doView('user-items.php');
        break;
        
      case 'activate_alert':
        $email = Params::getParam('email');
        $secret = Params::getParam('secret');

        $result = 0;
        if($email!='' && $secret!='') {
          $result = Alerts::newInstance()->activate($email);
        }

        if($result == 1) {
          osc_add_flash_ok_message(_m('Alert activated'));
        }else{
          osc_add_flash_error_message(_m('Oops! There was a problem trying to activate your alert. Please contact an administrator'));
        }

        $this->redirectTo(osc_base_url());
        break;
        
      case 'unsub_alert':
        $email = Params::getParam('email');
        $secret = Params::getParam('secret');
        $id = Params::getParam('id');

        $alert = Alerts::newInstance()->findByPrimaryKey($id);
        $result = 0;
        if (! empty($alert) && $email == $alert[ 's_email' ] && $secret == $alert[ 's_secret' ]) {
          $result = Alerts::newInstance()->unsub($id);
        }

        if($result == 1) {
          osc_add_flash_ok_message(_m('Unsubscribed correctly'));
        } else {
          osc_add_flash_error_message(_m('Oops! There was a problem trying to unsubscribe you. Please contact an administrator'));
        }

        $this->redirectTo(osc_user_alerts_url());
        break;

      case 'alert_change_freq':
        $type = strtoupper(osc_esc_html(Params::getParam('type')));
        $secret = osc_esc_html(Params::getParam('secret'));
        $id = osc_esc_html(Params::getParam('id'));

        $alert = Alerts::newInstance()->findByPrimaryKey($id);
        $result = 0;
        
        // Check if type is supported
        if(!in_array($type, osc_alert_types(true))) {
          osc_add_flash_error_message(_m('Oops! There was a problem trying to change alert frequency - invalid type. Please contact an administrator'));
          $this->redirectTo(osc_user_alerts_url());
        }

        if(isset($alert['pk_i_id']) && $secret == $alert['s_secret']) {
          $result = Alerts::newInstance()->updateAlertType($id, $type);
        }

        if($result == 1) {
          osc_add_flash_ok_message(_m('Alert frequency changed successfully'));
        } else {
          osc_add_flash_error_message(_m('Oops! There was a problem trying to change alert frequency. Please contact an administrator'));
        }

        $this->redirectTo(osc_user_alerts_url());
        break;
        
      case 'delete':
        $id = Params::getParam('id');
        $secret = Params::getParam('secret');
        
        if(osc_is_web_user_logged_in()) {
          $user = User::newInstance()->findByPrimaryKey(osc_logged_user_id());
          osc_run_hook('before_user_delete', $user);
          View::newInstance()->_exportVariableToView('user', $user);
          
          if(!empty($user) && osc_logged_user_id()==$id && $secret==$user['s_secret']) {
            try {
              User::newInstance()->deleteUser(osc_logged_user_id());
            } catch (Exception $e) {
            }

            Session::newInstance()->_drop('userId');
            Session::newInstance()->_drop('userName');
            Session::newInstance()->_drop('userEmail');
            Session::newInstance()->_drop('userPhone');

            Cookie::newInstance()->pop('oc_userId');
            Cookie::newInstance()->pop('oc_userSecret');
            Cookie::newInstance()->set();

            osc_add_flash_ok_message(_m('Your account have been deleted'));
            $this->redirectTo(osc_base_url());
          } else {
            osc_add_flash_error_message(_m('Oops! you can not do that'));
            $this->redirectTo(osc_user_dashboard_url());
          }
        } else {
          osc_add_flash_error_message(_m('Oops! you can not do that'));
          $this->redirectTo(osc_base_url());
        }
        
        break;
    }
  }

  //hopefully generic...

  /**
   * @param $file
   *
   * @return mixed|void
   */
  public function doView($file) {
    osc_run_hook('before_html');
    osc_current_web_theme_path($file);
    Session::newInstance()->_clearVariables();
    osc_run_hook('after_html');
  }
}

/* file end: ./user.php */