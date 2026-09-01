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


class CWebItem extends BaseModel {
  private $itemManager;
  private $user;
  private $userId;

  function __construct() {
    parent::__construct();
    $this->itemManager = Item::newInstance();

    // here allways userId == ''
    if(osc_is_web_user_logged_in()) {
      $this->userId = osc_logged_user_id();
      $this->user = osc_get_user_row($this->userId);
    } else {
      $this->userId = null;
      $this->user = null;
    }
    
    osc_run_hook('init_item');
  }

  //Business Layer...
  function doModel() {
    //calling the view...

    $locales = osc_get_locales();
    $this->_exportVariableToView('locales', $locales);

    switch($this->action) {
      case 'item_add': // post
        if(osc_reg_user_post() && $this->user == null) {
          osc_add_flash_warning_message(_m('Only registered users are allowed to post listings'));
          Session::newInstance()->_setReferer(osc_item_post_url());
          $this->redirectTo(osc_user_login_url());
        }

        $countries = Country::newInstance()->listAll();
        $regions = array();
        if(isset($this->user['fk_c_country_code']) && $this->user['fk_c_country_code']!='') {
          $regions = Region::newInstance()->findByCountry($this->user['fk_c_country_code']);
        } else if(count($countries) > 0) {
          $regions = Region::newInstance()->findByCountry($countries[0]['pk_c_code']);
        }
        
        $cities = array();
        if(isset($this->user['fk_i_region_id']) && $this->user['fk_i_region_id']!='') {
          $cities = City::newInstance()->findByRegion($this->user['fk_i_region_id']);
        } else if(count($regions) > 0) {
          $cities = City::newInstance()->findByRegion($regions[0]['pk_i_id']);
        }

        $this->_exportVariableToView('countries', $countries);
        $this->_exportVariableToView('regions', $regions);
        $this->_exportVariableToView('cities', $cities);

        $form = count(Session::newInstance()->_getForm());
        $keepForm = count(Session::newInstance()->_getKeepForm());
        
        if($form==0 || $form==$keepForm) {
          Session::newInstance()->_dropKeepForm();
        }

        if(Session::newInstance()->_getForm('countryId') != "") {
          $countryId = Session::newInstance()->_getForm('countryId');
          $regions = Region::newInstance()->findByCountry($countryId);
          $this->_exportVariableToView('regions', $regions);
          
          if(Session::newInstance()->_getForm('regionId') != "") {
            $regionId = Session::newInstance()->_getForm('regionId');
            $cities = City::newInstance()->findByRegion($regionId);
            $this->_exportVariableToView('cities', $cities);
          }
        }

        $this->_exportVariableToView('user', $this->user);

        osc_run_hook('post_item');

        $this->doView('item-post.php');
        break;

      case 'item_add_post':
        // SAVE form data before CSRF CHECK
        $mItems = new ItemActions(false);
        $mItems->prepareData(true);
        
        foreach($mItems->data as $key => $value) {
          Session::newInstance()->_setForm($key, $value);
        }

        $meta = Params::getParam('meta');
        if(is_array($meta)) {
          foreach($meta as $key => $value) {
            Session::newInstance()->_setForm('meta_'.$key, $value);
            Session::newInstance()->_keepForm('meta_'.$key);
          }
        }

        osc_csrf_check();

        if(osc_reg_user_post() && $this->user == null) {
          osc_add_flash_warning_message(_m('Only registered users are allowed to post listings'));
          $this->redirectTo(osc_base_url(true));
        }

        if(osc_recaptcha_enabled() && osc_recaptcha_items_enabled() && osc_recaptcha_private_key() != '') {
          if(!osc_check_recaptcha()) {
            osc_add_flash_error_message(_m('Recaptcha validation has failed'));
            $this->redirectTo(osc_item_post_url());
            return false; // BREAK THE PROCESS, THE RECAPTCHA IS WRONG
          }
        }

        if(!osc_is_web_user_logged_in()) {
          $user = User::newInstance()->findByEmail($mItems->data['contactEmail']);
          // The user exists but it's not logged
          if(isset($user['pk_i_id'])) {
            foreach($mItems->data as $key => $value) {
              Session::newInstance()->_keepForm($key);
            }
            
            osc_add_flash_error_message(_m('A user with that email address already exists, if it is you, please log in'));
            $this->redirectTo(osc_user_login_url());
          }
        }

        $banned = osc_is_banned($mItems->data['contactEmail']);
        
        if($banned == 1) {
          osc_add_flash_error_message(_m('Your current email is not allowed'));
          $this->redirectTo(osc_item_post_url());
          
        } else if($banned == 2) {
          osc_add_flash_error_message(_m('Your current IP is not allowed'));
          $this->redirectTo(osc_item_post_url());
        }

        // POST ITEM (ADD ITEM)
        $success = $mItems->add();

        if($success != 1 && $success != 2) {
          osc_add_flash_error_message($success);
          $this->redirectTo(osc_item_post_url());
        } else {
          if(is_array($meta)) {
            foreach($meta as $key => $value) {
              Session::newInstance()->_dropKeepForm('meta_'.$key);
            }
          }
          
          Session::newInstance()->_clearVariables();
          
          if($success==1) {
            osc_add_flash_ok_message(_m('Check your inbox to validate your listing'));
          } else {
            osc_add_flash_ok_message(_m('Your listing has been published'));
          }

          $itemId = Params::getParam('itemId');

          $category = osc_get_category_row(Params::getParam('catId'));
          View::newInstance()->_exportVariableToView('category', $category);


          // 420 redirect after publish 
          $redirect_type = osc_get_redirect_after_publish();

          if($redirect_type == 'DASH-ITEM-CAT') {
            if(osc_is_web_user_logged_in()) {
              $this->redirectTo(osc_user_items_url());
              exit;
            }

            if($success != 1) {
              $this->redirectTo(osc_item_url_ns($itemId));
              exit;
            }

            $this->redirectTo(osc_search_category_url());
            exit;
          }

          if($redirect_type == 'ITEM-CAT') {
            if($success != 1) {
              $this->redirectTo(osc_item_url_ns($itemId));
              exit;
            }

            $this->redirectTo(osc_search_category_url());
            exit;
          }

          // Other redirect types
          $this->redirectTo(osc_search_category_url());
          exit;
        }
        break;

      case 'item_edit':   // edit item
        $secret = Params::getParam('secret');
        $id = Params::getParam('id');
        //$item = $this->itemManager->listWhere("i.pk_i_id = %d AND ((i.s_secret = %s AND i.fk_i_user_id IS NULL) OR (i.fk_i_user_id = %d))", (int)($id), $secret, (int)($this->userId));
        $item = $this->itemManager->listWhere("i.pk_i_id = %d AND (i.s_secret = %s OR i.fk_i_user_id = %d)", (int)($id), $secret, (int)($this->userId));

        if (count($item) == 1) {
          $item = Item::newInstance()->findByPrimaryKey($id);

          $form = count(Session::newInstance()->_getForm());
          $keepForm = count(Session::newInstance()->_getKeepForm());
          if($form == 0 || $form == $keepForm) {
            Session::newInstance()->_dropKeepForm();
          }

          $this->_exportVariableToView('item', $item);

          osc_run_hook("before_item_edit", $item);
          $this->doView('item-edit.php');
        } else {
          // add a flash message [ITEM NO EXISTE]
          osc_add_flash_error_message(_m("Sorry, we don't have any listings with that ID"));
          if($this->user != null) {
            $this->redirectTo(osc_user_items_url());
          } else {
            $this->redirectTo(osc_base_url());
          }
        }
        break;

      case 'item_edit_post':
        // SAVE form data before CSRF CHECK
        $mItems = new ItemActions(false);
        // prepare data for ADD ITEM
        $mItems->prepareData(false);
        // set all parameters into session
        foreach($mItems->data as $key => $value) {
          Session::newInstance()->_setForm($key, $value);
        }

        $meta = Params::getParam('meta');
        if(is_array($meta)) {
          foreach($meta as $key => $value) {
            Session::newInstance()->_setForm('meta_'.$key, $value);
            Session::newInstance()->_keepForm('meta_'.$key);
          }
        }

        osc_csrf_check();

        $secret = Params::getParam('secret');
        $id = Params::getParam('id');
        //$item = $this->itemManager->listWhere("i.pk_i_id = %d AND ((i.s_secret = %s AND i.fk_i_user_id IS NULL) OR (i.fk_i_user_id = %d))", (int)($id), $secret, (int)($this->userId));
        $item = $this->itemManager->listWhere("i.pk_i_id = %d AND (i.s_secret = %s OR i.fk_i_user_id = %d)", (int)($id), $secret, (int)($this->userId));

        if (count($item) == 1) {
          $this->_exportVariableToView('item', $item[0]);

          if(osc_recaptcha_enabled() && osc_recaptcha_items_enabled() && osc_recaptcha_private_key() != '') {
            if(!osc_check_recaptcha()) {
              osc_add_flash_error_message(_m('Recaptcha validation has failed'));
              $this->redirectTo(osc_item_edit_url($secret, $id));
              return false; // BREAK THE PROCESS, THE RECAPTCHA IS WRONG
            }
          }

          $success = $mItems->edit();

          if($success == 1) {
            if(is_array($meta)) {
              foreach($meta as $key => $value) {
                Session::newInstance()->_dropKeepForm('meta_'.$key);
              }
            }
            
            Session::newInstance()->_clearVariables();
            osc_add_flash_ok_message(_m("Great! We've just updated your listing"));
            View::newInstance()->_exportVariableToView("item", Item::newInstance()->findByPrimaryKey($id));
            $this->redirectTo(osc_item_url());
            
          } else {
            osc_add_flash_error_message($success);
            $this->redirectTo(osc_item_edit_url($secret, $id));
          }
        }
        break;

      case 'activate':
        $secret = Params::getParam('secret');
        $id = Params::getParam('id');
        $item = $this->itemManager->listWhere("i.pk_i_id = %d AND (i.s_secret = %s OR i.fk_i_user_id = %d)", (int)($id), $secret, (int)($this->userId));

        // item doesn't exist
        if(count($item) == 0) {
          $this->do404();
          return;
        }

        View::newInstance()->_exportVariableToView('item', $item[0]);
        
        if($item[0]['b_active'] == 0) {
          // ACTIVETE ITEM
          $mItems = new ItemActions(false);
          $success = $mItems->activate($item[0]['pk_i_id'], $item[0]['s_secret']);

          if($success) {
            osc_add_flash_ok_message(_m('The listing has been validated'));
          }else{
            osc_add_flash_error_message(_m("The listing can't be validated"));
          }
        } else {
          osc_add_flash_warning_message(_m('The listing has already been validated'));
        }

        $this->redirectTo(osc_item_url());
        break;
        

      case 'deactivate':
        $secret = Params::getParam('secret');
        $id = Params::getParam('id');
        $item = $this->itemManager->listWhere("i.pk_i_id = %d AND (i.s_secret = %s OR i.fk_i_user_id = %d)", (int)($id), $secret, (int)($this->userId));

        // item doesn't exist
        if(count($item) == 0) {
          $this->do404();
          return;
        }

        View::newInstance()->_exportVariableToView('item', $item[0]);
        if($item[0]['b_active'] == 1) {
          // DEACTIVETE ITEM
          $mItems = new ItemActions(false);
          $success = $mItems->deactivate($item[0]['pk_i_id'], $item[0]['s_secret']);

          if($success) {
            osc_add_flash_ok_message(_m('The listing has been deactivated'));
          }else{
            osc_add_flash_error_message(_m("The listing can't be deactivated"));
          }
        } else {
          osc_add_flash_warning_message(_m('The listing has already been deactivated'));
        }

        if(osc_is_web_user_logged_in()) {
          $this->redirectTo(osc_user_items_url());
        } else {
          $this->redirectTo(osc_base_url());
        }
        
        break;        

      case 'renew':
        $secret = Params::getParam('secret');
        $id = Params::getParam('id');
        $item = $this->itemManager->listWhere("i.pk_i_id = %d AND (i.s_secret = %s OR i.fk_i_user_id = %d)", (int)($id), $secret, (int)($this->userId));

        // item doesn't exist
        if(count($item) == 0) {
          $this->do404();
          return;
        }

        View::newInstance()->_exportVariableToView('item', $item[0]);
        
        if(osc_item_can_renew()) {
          // RENEW ITEM
          $mItems = new ItemActions(false);
          $success = $mItems->renew($item[0]['pk_i_id'], $item[0]['s_secret']);

          if($success == 1) {
            osc_add_flash_ok_message(_m('The listing has been renewed'));
          }else{  // error code 2, 3, 4, -1, yet problem will not be described
            osc_add_flash_error_message(_m('The listing can\'t be renewed'));
          }
        } else {
          osc_add_flash_warning_message(_m('The listing can\'t be renewed'));
        }

        $this->redirectTo(osc_item_url());
        break;

      case 'item_delete':
        $secret = Params::getParam('secret');
        $id = Params::getParam('id');
        $item = $this->itemManager->listWhere("i.pk_i_id = %d AND (i.s_secret = %s OR i.fk_i_user_id = %d)", (int)($id), $secret, (int)($this->userId));
        if (count($item) == 1) {
          $mItems = new ItemActions(false);
          $success = $mItems->delete($item[0]['s_secret'], $item[0]['pk_i_id']);
          if($success) {
            osc_add_flash_ok_message(_m('Your listing has been deleted'));
          } else {
            osc_add_flash_error_message(_m("The listing you are trying to delete couldn't be deleted"));
          }
          if($this->user!=null) {
            $this->redirectTo(osc_user_items_url());
          } else {
            $this->redirectTo(osc_base_url());
          }
        }else{
          osc_add_flash_error_message(_m("The listing you are trying to delete couldn't be deleted"));
          $this->redirectTo(osc_base_url());
        }
        break;

      case 'deleteResources': // Delete images via AJAX
        $id = Params::getParam('id');
        $item = Params::getParam('item');
        $code = Params::getParam('code');
        $secret = Params::getParam('secret');

        if(Session::newInstance()->_get('userId') > 0){
          $userId = Session::newInstance()->_get('userId');
          $user = osc_get_user_row($userId);
        }else{
          $userId = null;
          $user = null;
        }

        if (!(is_numeric($id) && is_numeric($item) && preg_match('/^([a-z0-9]+)$/i', $code))) {
          osc_add_flash_error_message(_m("The selected photo couldn't be deleted, the url doesn't exist"));
          $this->redirectTo(osc_item_edit_url($secret, $item));
        }

        $aItem = osc_get_item_row($item);
        
        if(count($aItem) == 0) {
          osc_add_flash_error_message(_m("The listing doesn't exist"));
          $this->redirectTo(osc_item_edit_url($secret, $item));
        }

        if(!osc_is_admin_user_logged_in()) {
          if($userId != null && $userId != $aItem['fk_i_user_id']) {
            osc_add_flash_error_message(_m("The listing doesn't belong to you"));
            $this->redirectTo(osc_item_edit_url($secret, $item));
          }

          if($userId == null && $aItem['fk_i_user_id']==null && $secret != $aItem['s_secret']) {
            osc_add_flash_error_message(_m("The listing doesn't belong to you"));
            $this->redirectTo(osc_item_edit_url($secret, $item));
          }
        }

        $result = ItemResource::newInstance()->existResource($id, $code);

        if ($result > 0) {
          $resource = ItemResource::newInstance()->findByPrimaryKey($id);

          if($resource['fk_i_item_id']==$item) {
            osc_deleteResource($id, false);
            Log::newInstance()->insertLog('item', 'deleteResource', $id, $id, 'user', osc_logged_user_id());
            ItemResource::newInstance()->delete(array('pk_i_id' => $id, 'fk_i_item_id' => $item, 's_name' => $code));
            osc_add_flash_ok_message(_m('The selected photo has been successfully deleted'));
          } else {
            osc_add_flash_error_message(_m("The selected photo does not belong to you"));
          }
        } else {
          osc_add_flash_error_message(_m("The selected photo couldn't be deleted"));
        }

        $this->redirectTo(osc_item_edit_url($secret, $item));
        break;

      case 'mark':
        $id = Params::getParam('id');
        $as = Params::getParam('as');

        $item = osc_get_item_row($id);
        View::newInstance()->_exportVariableToView('item', $item);

        if(osc_item_mark_disable()) {
          osc_add_flash_error_message(_m('This feature is disabled, you cannot mark or report listing'));
          $this->redirectTo(osc_item_url());
        }


        // Mark item if not bot
        if(osc_visitor_is_real_user()) {
          $mItem = new ItemActions(false);
          $mItem->mark($id, $as);
        }

        osc_add_flash_ok_message(_m("Thanks! That's very helpful"));
        $this->redirectTo(osc_item_url());
        break;

      case 'send_friend':
        // this one cannot be disabled as many themes use this page for custom forms management!!!
        // $item = $this->itemManager->findByPrimaryKey(Params::getParam('id'));
        $item = osc_get_item_row(Params::getParam('id'));

        $this->_exportVariableToView('item', $item);

        $this->doView('item-send-friend.php');
        break;

      case 'send_friend_post':
        if(osc_item_send_friend_form_disabled()) {
          osc_add_flash_warning_message(_m('Sorry, send to friend form is disabled.'));
          $this->redirectTo(osc_base_url());
        }
        
        osc_csrf_check();
        // $item = $this->itemManager->findByPrimaryKey(Params::getParam('id'));
        $item = osc_get_item_row(Params::getParam('id'));
        
        $this->_exportVariableToView('item', $item);

        Session::newInstance()->_setForm("yourEmail",   Params::getParam('yourEmail'));
        Session::newInstance()->_setForm("yourName",  Params::getParam('yourName'));
        Session::newInstance()->_setForm("friendName", Params::getParam('friendName'));
        Session::newInstance()->_setForm("friendEmail", Params::getParam('friendEmail'));
        Session::newInstance()->_setForm("message_body",Params::getParam('message'));

        if (osc_recaptcha_enabled() && osc_recaptcha_private_key() != '') {
          if(!osc_check_recaptcha()) {
            osc_add_flash_error_message(_m('Recaptcha validation has failed'));
            $this->redirectTo(osc_item_send_friend_url());
            return false; // BREAK THE PROCESS, THE RECAPTCHA IS WRONG
          }
        }

        osc_run_hook('pre_item_send_friend_post', $item);

        $mItem = new ItemActions(false);
        $success = $mItem->send_friend();

        osc_run_hook('post_item_send_friend_post', $item);

        if($success) {
          Session::newInstance()->_clearVariables();
          $this->redirectTo(osc_item_url());
        } else {
          $this->redirectTo(osc_item_send_friend_url());
        }
        break;

      case 'contact':
        if(osc_item_contact_form_disabled()) {
          osc_add_flash_warning_message(_m('Sorry, contact form is disabled.'));
          $this->redirectTo(osc_base_url());
        }
      
        // $item = $this->itemManager->findByPrimaryKey(Params::getParam('id'));
        $item = osc_get_item_row(Params::getParam('id'));
        
        if(empty($item)){
          osc_add_flash_error_message(_m("This listing doesn't exist"));
          $this->redirectTo(osc_base_url(true));
        } else {
          $this->_exportVariableToView('item', $item);

          if(osc_item_is_expired ()) {
            osc_add_flash_error_message(_m("We're sorry, but the listing has expired. You can't contact the seller"));
            $this->redirectTo(osc_item_url());
          }

          if(osc_reg_user_can_contact() && osc_is_web_user_logged_in() || !osc_reg_user_can_contact()){
            $this->doView('item-contact.php');
          } else {
            osc_add_flash_warning_message(_m("You can't contact the seller, only registered users can").'. <br />'.sprintf(_m("<a href=\"%s\">Click here to sign-in</a>"), osc_user_login_url()));
            $this->redirectTo(osc_item_url());
          }
        }
        break;

      case 'contact_post':
        if(osc_item_contact_form_disabled()) {
          osc_add_flash_warning_message(_m('Sorry, contact form is disabled.'));
          $this->redirectTo(osc_base_url());
        }
        
        osc_csrf_check();
        
        if(osc_reg_user_can_contact() && !osc_is_web_user_logged_in()){
          osc_add_flash_warning_message(_m("You can't contact the seller, only registered users can"));
          $this->redirectTo(osc_base_url(true));
        }

        // $item = $this->itemManager->findByPrimaryKey(Params::getParam('id'));
        $item = osc_get_item_row(Params::getParam('id'));
        
        $this->_exportVariableToView('item', $item);
        if (osc_recaptcha_enabled() && osc_recaptcha_private_key() != '') {
          if(!osc_check_recaptcha()) {
            osc_add_flash_error_message(_m('Recaptcha validation has failed'));
            Session::newInstance()->_setForm("yourEmail",   Params::getParam('yourEmail'));
            Session::newInstance()->_setForm("yourName",  Params::getParam('yourName'));
            Session::newInstance()->_setForm("phoneNumber", Params::getParam('phoneNumber'));
            Session::newInstance()->_setForm("message_body",Params::getParam('message'));
            $this->redirectTo(osc_item_url());
            return false; // BREAK THE PROCESS, THE RECAPTCHA IS WRONG
          }
        }

        $banned = osc_is_banned(Params::getParam('yourEmail'));
        if($banned==1) {
          osc_add_flash_error_message(_m('Your current email is not allowed'));
          $this->redirectTo(osc_item_url());
        } else if($banned==2) {
          osc_add_flash_error_message(_m('Your current IP is not allowed'));
          $this->redirectTo(osc_item_url());
        }

        if(osc_isExpired($item['dt_expiration'])) {
          osc_add_flash_error_message(_m("We're sorry, but the listing has expired. You can't contact the seller"));
          $this->redirectTo(osc_item_url());
        }

        osc_run_hook('pre_item_contact_post', $item);

        $mItem = new ItemActions(false);
        $result = $mItem->contact();

        osc_run_hook('post_item_contact_post', $item);
        
        if(is_string($result)){
          osc_add_flash_error_message($result);
        } else {
          osc_add_flash_ok_message(_m("We've just sent an e-mail to the seller"));
        }

        $this->redirectTo(osc_item_url());
        break;

      case 'add_comment':
        osc_csrf_check();

        $itemId = Params::getParam('id');
        $item = osc_get_item_row($itemId);

        osc_run_hook('pre_item_add_comment_post', $item);

        $mItem = new ItemActions(false);
        $status = $mItem->add_comment();

        switch ($status) {
          case -1:
            $msg = _m('Sorry, we could not save your comment. Try again later');
            osc_add_flash_error_message($msg);
            break;

          case 1:
            $msg = _m('Your comment is awaiting moderation');
            osc_add_flash_info_message($msg);
            break;

          case 2:
            $msg = _m('Your comment has been approved');
            osc_add_flash_ok_message($msg);
            break;

          case 3:
            $msg = _m('Please fill the required field (email)');
            osc_add_flash_warning_message($msg);
            break;

          case 4:
            $msg = _m('Please type a comment');
            osc_add_flash_warning_message($msg);
            break;

          case 5:
            $msg = _m('Your comment has been marked as spam');
            osc_add_flash_error_message($msg);
            break;

          case 6:
            $msg = _m('You need to be logged to comment');
            osc_add_flash_error_message($msg);
            break;

          case 7:
            $msg = _m('Sorry, comments are disabled');
            osc_add_flash_error_message($msg);
            break;
            
          case 8:
            $msg = _m('Parent comment does not exists');
            osc_add_flash_error_message($msg);
            break;
            
          case 9:
            $msg = _m('Parent comment is already reply. Only 1 level of replies are allowed, parent comment cannot be reply to other comment.');
            osc_add_flash_error_message($msg);
            break;
            
          case 10:
            $msg = _m('Parent comment belongs to different listing');
            osc_add_flash_error_message($msg);
            break;
            
          case 11:
            $msg = _m('Sorry, replies are disabled');
            osc_add_flash_error_message($msg);
            break;
            
          case 12:
            $msg = _m('You need to be logged to reply on comment');
            osc_add_flash_error_message($msg);
            break;
            
          case 13:
            $msg = _m('Only owner of listing can reply to comments');
            osc_add_flash_error_message($msg);
            break;
            
          case 14:
            $msg = _m('Only logged administrator can reply to comments');
            osc_add_flash_error_message($msg);
            break;   
            
        }

        // View::newInstance()->_exportVariableToView('item', Item::newInstance()->findByPrimaryKey(Params::getParam('id')));
        $this->redirectTo(osc_item_url());
        break;

      case 'delete_comment':
        osc_csrf_check();

        $commentId = Params::getParam('comment');
        $itemId = Params::getParam('id');
        $item = osc_get_item_row($itemId);

        osc_run_hook('pre_item_delete_comment_post', $item, $commentId);

        $mItem = new ItemActions(false);
        $status = $mItem->add_comment(); // @TOFIX @FIXME $status never used + ?? need to add_comment() before deleting it??

        if(count($item) == 0) {
          osc_add_flash_error_message(_m("This listing doesn't exist"));
          $this->redirectTo(osc_base_url(true));
        }

        View::newInstance()->_exportVariableToView('item', $item);

        if($this->userId == null) {
          osc_add_flash_error_message(_m('You must be logged in to delete a comment'));
          $this->redirectTo(osc_item_url());
        }

        $commentManager = ItemComment::newInstance();
        $aComment = $commentManager->findByPrimaryKey($commentId);

        if(count($aComment) == 0) {
          osc_add_flash_error_message(_m("The comment doesn't exist"));
          $this->redirectTo(osc_item_url());
        }

        if($aComment['b_active'] != 1) {
          osc_add_flash_error_message(_m('The comment is not active, you cannot delete it'));
          $this->redirectTo(osc_item_url());
        }

        if($aComment['fk_i_user_id'] != $this->userId) {
          osc_add_flash_error_message(_m('The comment was not added by you, you cannot delete it'));
          $this->redirectTo(osc_item_url());
        }

        $commentManager->deleteByPrimaryKey($commentId);
        osc_add_flash_ok_message(_m('The comment has been deleted'));
        $this->redirectTo(osc_item_url());
        break;

      default:
        // if there isn't ID, show an error 404
        if(Params::getParam('id') == '') {
          $this->do404();
          return;
        }

        // Update 8.0.2 - lang param handler moved to index.php
        // if(Params::getParam('lang') != '') {
        //   Session::newInstance()->_set('userLocale', Params::getParam('lang'));
        // }

        // $item = osc_apply_filter('pre_show_item', $this->itemManager->findByPrimaryKey(Params::getParam('id')));
        $item = osc_apply_filter('pre_show_item', osc_get_item_row(Params::getParam('id'), false));
        
        // if item doesn't exist show an error 410
        if(!is_array($item) || count($item) == 0 || !isset($item['pk_i_id'])) {
          $this->do410();
          return;
        }

        if(osc_isExpired($item['dt_expiration'])) {
          if(($this->userId == $item['fk_i_user_id']) && ($this->userId > 0) || osc_is_admin_user_logged_in()) {
            osc_add_flash_warning_message(_m("The listing has expired. Please renew it in order to make it public"));
          }
        }

        if($item['b_active'] != 1) {
          if(($this->userId == $item['fk_i_user_id']) && ($this->userId > 0) || osc_is_admin_user_logged_in()) {
            if(!osc_isExpired($item['dt_expiration'])) {
              osc_add_flash_warning_message(_m("The listing hasn't been validated. Please validate it in order to make it public"));
              
            } else {
              osc_add_flash_warning_message(_m("The listing is deactivated"));
            }
            
          } else {
            $this->do400();
            return;
          }
          
        } else if($item['b_enabled'] == 0) {
          if(osc_is_admin_user_logged_in()) {
            osc_add_flash_warning_message(_m("The listing hasn't been enabled. Please enable it in order to make it public"));
            
          } else if(osc_is_web_user_logged_in() && osc_logged_user_id() == $item['fk_i_user_id']) {
            osc_add_flash_warning_message(_m("The listing has been blocked or is awaiting moderation from the admin"));
            
          } else {
            $this->do400();
            return;
          }
        }

        if(!osc_is_admin_user_logged_in() && !(osc_is_web_user_logged_in() && $item['fk_i_user_id'] == osc_logged_user_id())) {
          if(osc_visitor_is_real_user()) {
            $mStats = new ItemStats();
            $mStats->increase('i_num_views', $item['pk_i_id']);
          }
        }

        foreach($item['locale'] as $k => $v) {
          $item['locale'][$k]['s_title'] = osc_apply_filter('item_title', $v['s_title']);

          if(osc_tinymce_items_enabled() == '1') {
            $item['locale'][$k]['s_description'] = osc_apply_filter('item_description', $v['s_description']);
          } else {
            $item['locale'][$k]['s_description'] = nl2br(osc_apply_filter('item_description', $v['s_description']));
          }
        }

        if($item['fk_i_user_id'] > 0) {
          $user = osc_get_user_row($item['fk_i_user_id']);
          $this->_exportVariableToView('user', $user);
        }

        $this->_exportVariableToView('item', $item);

        osc_run_hook('show_item', $item);

        // redirect to the correct url just in case it has changed
        $itemURI = str_replace(osc_base_url(), '', osc_item_url());
        $URI = preg_replace('|^' . REL_WEB_URL . '|', '', Params::getServerParam('REQUEST_URI', false, false));
        // do not clean QUERY_STRING if permalink is not enabled
        if(osc_rewrite_enabled ()) {
          $URI = str_replace('?' . Params::getServerParam('QUERY_STRING', false, false), '', $URI);
        } else {
          $params_keep = array('page', 'id');
          $params = array();
          
          foreach(Params::getParamsAsArray('get') as $k => $v) {
            if(in_array($k, $params_keep)) {
              $params[] = "$k=$v";
            }
          }
          
          $URI = 'index.php?' . implode('&', $params);
        }

        // redirect to the correct url
        // if($itemURI!=$URI) { 
        if(urlencode(strip_tags(strtolower(str_replace('+', '', str_replace(' ', '',urldecode($itemURI)))))) != urlencode(strip_tags(strtolower(str_replace(' ', '', urldecode($URI)))))) {  // update 420, adding strtolower
          $this->redirectTo(osc_base_url() . strtolower($itemURI), 301);
        }

        $this->doView('item.php');
        break;
    }
  }

  //hopefully generic...
  function doView($file) {
    osc_run_hook("before_html");
    osc_current_web_theme_path($file);
    Session::newInstance()->_clearVariables();
    osc_run_hook("after_html");
  }
}

/* file end: ./item.php */