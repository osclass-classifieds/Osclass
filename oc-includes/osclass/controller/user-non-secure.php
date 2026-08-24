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
 * Class CWebUserNonSecure
 */
class CWebUserNonSecure extends BaseModel {
  public $uri;

  public function __construct() {
    parent::__construct();
    
    if(!osc_users_enabled() && ($this->action != 'activate_alert' && $this->action != 'unsub_alert')) {
      osc_add_flash_error_message(_m('Users not enabled'));
      $this->redirectTo(osc_base_url());
    }
    
    osc_run_hook('init_user_non_secure');
    
    // Get only params in form: /sCategory,1/sRegion,2/...
    if(osc_rewrite_enabled() && $this->action == 'pub_profile') {
      $this->uri = preg_replace('|^' . REL_WEB_URL . '|', '', Params::getServerParam('REQUEST_URI', false, false));

      // User items URL is in raw form: /index.php?page=user&action=items&sPattern=&sCategory=...
      if(preg_match('/^index\.php/', $this->uri)>0) {
        $canonical_url = osc_user_public_profile_url(osc_esc_html(Params::getParam('id')), false, 'username', Params::getParamsAsArray());
        $this->redirectTo($canonical_url, 301);
      }

      $parts = explode(osc_get_preference('rewrite_user_profile'), $this->uri);
      $last_part = ltrim(end($parts), '/');
      
      // Get rid of user name that is first part of param values
      $value_parts = explode('/', $last_part);
      unset($value_parts[0]);

      $this->uri = '/' . implode('/', $value_parts);
      $this->uri = rtrim((string)$this->uri, '/');
    }
  }

  //Business Layer...
  public function doModel() {
    switch($this->action) {
      case 'change_email_confirm':  //change email confirm
        if (Params::getParam('userId') && Params::getParam('code')) {
          $userManager = new User();
          $user = $userManager->findByPrimaryKey(Params::getParam('userId'));

          if($user['s_pass_code'] == Params::getParam('code') && $user['b_enabled']==1) {
            $userOldEmail = $user['s_email'];
            $userEmailTmp = UserEmailTmp::newInstance()->findByPrimaryKey(Params::getParam('userId'));
            $code = osc_genRandomPassword(50);
            $userManager->update(array('s_email' => $userEmailTmp['s_new_email']), array('pk_i_id' => $userEmailTmp['fk_i_user_id']));
            
            Item::newInstance()->update(array('s_contact_email' => $userEmailTmp['s_new_email']), array('fk_i_user_id' => $userEmailTmp['fk_i_user_id']));
            ItemComment::newInstance()->update(array('s_author_email' => $userEmailTmp['s_new_email']), array('fk_i_user_id' => $userEmailTmp['fk_i_user_id']));
            Alerts::newInstance()->update(array('s_email' => $userEmailTmp['s_new_email']), array('fk_i_user_id' => $userEmailTmp['fk_i_user_id']));
            Session::newInstance()->_set('userEmail', $userEmailTmp['s_new_email']);
            UserEmailTmp::newInstance()->delete(array('s_new_email' => $userEmailTmp['s_new_email']));
            
            osc_run_hook('change_email_confirm', Params::getParam('userId'), $userOldEmail, $userEmailTmp['s_new_email']);
            
            osc_add_flash_ok_message(_m('Your email has been changed successfully'));
            $this->redirectTo(osc_user_profile_url());
            
          } else {
            osc_add_flash_error_message(_m('Sorry, the link is not valid'));
            $this->redirectTo(osc_base_url());
          }
          
        } else {
          osc_add_flash_error_message(_m('Sorry, the link is not valid'));
          $this->redirectTo(osc_base_url());
        }
        
        break;
        
      case 'activate_alert':
        $email = Params::getParam('email');
        $secret = Params::getParam('secret');
        $id = Params::getParam('id');

        $alert = Alerts::newInstance()->findByPrimaryKey($id);
        $result = 0;
        
        if (!empty($alert) && $email == $alert['s_email'] && $secret == $alert['s_secret']) {
          $user = User::newInstance()->findByEmail($alert['s_email']);
          
          if (isset($user['pk_i_id'])) {
            Alerts::newInstance()->update(array ('fk_i_user_id' => $user['pk_i_id']) , array ('pk_i_id' => $id));
          }
          
          $result = Alerts::newInstance()->activate($id);
        }

        if($result == 1) {
          osc_add_flash_ok_message(_m('Alert activated'));
        }else{
          osc_add_flash_error_message(_m('Oops!There was a problem trying to activate your alert. Please contact an administrator'));
        }

        $this->redirectTo(osc_base_url());
        break;
        
      case 'unsub_alert':
        $email = Params::getParam('email');
        $secret = Params::getParam('secret');
        $id = Params::getParam('id');

        $alert = Alerts::newInstance()->findByPrimaryKey($id);
        $result = 0;
        
        if (!empty($alert) && $email == $alert['s_email'] && $secret == $alert['s_secret']) {
          $result = Alerts::newInstance()->unsub($id);
        }

        if($result == 1) {
          osc_add_flash_ok_message(_m('Unsubscribed correctly'));
        }else{
          osc_add_flash_error_message(_m('Oops!There was a problem trying to unsubscribe you. Please contact an administrator'));
        }

        $this->redirectTo(osc_base_url());
        break;
        
      case 'pub_profile':
        osc_run_hook('before_public_profile');
        
        $url_params = '';

        if(Params::getParam('username') != '') {
          $user = osc_get_user_row_by_username(osc_esc_html(Params::getParam('username')));
          
        } else if((int)Params::getParam('id') > 0) {
          $user = osc_get_user_row(osc_esc_html(Params::getParam('id')));
          
        } else {
          $this->do404();
          return;
        }
        
        // User doesn't exist, not enabled or not validated, show 404 error
        if($user === false || !isset($user['pk_i_id']) || $user['b_enabled'] == 0 || $user['b_active'] == 0) {
          $this->do404();
          return;
        }
        
        $user_id = (int)$user['pk_i_id'];
        
        // Public profile not enabled
        if(osc_user_public_profile_is_enabled($user) === false) {
          $this->do404();
          return;
        }


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
          $canonical_url = osc_user_public_profile_url($user_id, $user, 'username', $params);       // ../category,abc/region,efg/...
          $original_url = osc_user_public_profile_url($user_id, $user, 'username') . $this->uri;    // ../sCategory,abc/sRegion,efg/...
          
          if($this->uri != '' && urlencode(strip_tags(strtolower(str_replace(array('+', ' '), '', urldecode(trim($original_url)))))) != urlencode(strip_tags(strtolower(str_replace(array('+', ' '), '', urldecode(trim($canonical_url))))))) {
            $referer_url = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');

            // Avoid possible loop redirects
            if($referer_url != $canonical_url) {
              $this->redirectTo($canonical_url, 301);
            }
          }
        }


        $options = array();
        $options['item_type'] = 'active';
        
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
        
        $options['per_page'] = (int)osc_apply_filter('user_public_profile_items_per_page', $options['per_page']);
        $options['per_page'] = ($options['per_page'] <= 0 ? 16 : $options['per_page']);
        
        $custom_conditions_and = osc_apply_filter('user_public_profile_custom_conditions_and', '', $params, $url_params);
        
        if($custom_conditions_and) {
          $options['custom_conditions_and'] = $custom_conditions_and;
        }
        
        $custom_conditions_or = osc_apply_filter('user_public_profile_custom_conditions_or', '', $params, $url_params);
        
        if($custom_conditions_or != '') {
          $options['custom_conditions_or'] = $custom_conditions_or;
        }


        $items = Item::newInstance()->findUserItems($user_id, '', $options);
        $total_items = Item::newInstance()->countUserItems($user_id, '', $options);

        $total_pages = ceil($total_items / $options['per_page']);
        

        // View::newInstance()->_exportVariableToView('user', $user);
        $this->_exportVariableToView('user', $user);
        $this->_exportVariableToView('canonical', osc_apply_filter('canonical_url_public_profile', osc_user_public_profile_url($user_id, $user, 'username', $options)));
        $this->_exportVariableToView('items', $items);
        $this->_exportVariableToView('search_page', $options['page']);
        $this->_exportVariableToView('search_total_pages', $total_pages);
        $this->_exportVariableToView('search_total_items', $total_items);
        $this->_exportVariableToView('items_per_page', $options['per_page']);
        
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

        $this->doView('user-public-profile.php');
        break;




        /*
        $itemsPerPage = osc_apply_filter('public_items_per_page', Params::getParam('itemsPerPage'));
        if (is_numeric($itemsPerPage) && (int) $itemsPerPage > 0) {
          $itemsPerPage = (int) $itemsPerPage;
        } else {
          $itemsPerPage = 10;
        }

        $page = Params::getParam('iPage');
        if (is_numeric($page) && (int) $page > 0) {
          $page = (int) $page - 1;
        } else {
          $page = 0;
        }

        // $total_items = Item::newInstance()->countItemTypesByUserID($user['pk_i_id'], 'active');
        $total_items = Item::newInstance()->countUserItems($user['pk_i_id'], '', array(
          'item_type' => 'active'
        ));

        if($itemsPerPage == 'all') {
          $total_pages = 1;
          //$items = Item::newInstance()->findItemTypesByUserID($user['pk_i_id'], 0, null, 'active');
          $items = Item::newInstance()->findUserItems($user['pk_i_id'], '', array(
            'item_type' => 'active'
          ));
          
        } else {
          $total_pages = ceil($total_items/$itemsPerPage);
          
          // $items = Item::newInstance()->findItemTypesByUserID($user['pk_i_id'], $page*$itemsPerPage, $itemsPerPage, 'active');
          $items = Item::newInstance()->findUserItems($user['pk_i_id'], '', array(
            'page' => $page,
            'per_page' => $itemsPerPage,
            'item_type' => 'active'
          ));
        }

        View::newInstance()->_exportVariableToView('user', $user);
        $this->_exportVariableToView('items', $items);
        $this->_exportVariableToView('search_total_pages', $total_pages);
        $this->_exportVariableToView('search_total_items', $total_items);
        $this->_exportVariableToView('items_per_page', $itemsPerPage);
        $this->_exportVariableToView('search_page', $page);
        $this->_exportVariableToView('canonical', osc_apply_filter('canonical_url_public_profile', osc_user_public_profile_url()));

        $this->doView('user-public-profile.php');
        break;
      */
        
      case 'contact_post':
        $user = osc_get_user(Params::getParam('id'));
        View::newInstance()->_exportVariableToView('user', $user);
        
        if (osc_recaptcha_enabled() && osc_recaptcha_private_key() != '') {
          if(!osc_check_recaptcha()) {
            osc_add_flash_error_message(_m('Recaptcha validation has failed'));
            Session::newInstance()->_setForm('yourEmail' , Params::getParam('yourEmail'));
            Session::newInstance()->_setForm('yourName' , Params::getParam('yourName'));
            Session::newInstance()->_setForm('phoneNumber' , Params::getParam('phoneNumber'));
            Session::newInstance()->_setForm('message_body' , Params::getParam('message'));
            $this->redirectTo(osc_user_public_profile_url());
            return false; // BREAK THE PROCESS, THE RECAPTCHA IS WRONG
          }
        }
        
        $banned = osc_is_banned(Params::getParam('yourEmail'));
        
        if($banned==1) {
          osc_add_flash_error_message(_m('Your current email is not allowed'));
          $this->redirectTo(osc_user_public_profile_url());
          
        } else if($banned==2) {
          osc_add_flash_error_message(_m('Your current IP is not allowed'));
          $this->redirectTo(osc_user_public_profile_url());
        }

        osc_run_hook('hook_email_contact_user', Params::getParam('id'), Params::getParam('yourEmail'), Params::getParam('yourName'), Params::getParam('phoneNumber'), Params::getParam('message'));
        osc_add_flash_ok_message(_m('Your email has been sent properly.'));
        
        $this->redirectTo(osc_user_public_profile_url());
        break;
        
      default:
        $this->redirectTo(osc_user_login_url());
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

/* file end: ./user-non-secure.php */