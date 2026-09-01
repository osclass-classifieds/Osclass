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


define('IS_AJAX', true);

/**
 * Class CWebAjax
 */
class CWebAjax extends BaseModel {
  public function __construct() {
    parent::__construct();
    $this->ajax = true;
    osc_run_hook('init_ajax');
  }

  //Business Layer...
  public function doModel() {
    //specific things for this class
    switch ($this->action){
      case 'bulk_actions':
        break;
        
      case 'regions': //Return regions given a countryId
        $regions = Region::newInstance()->findByCountry(Params::getParam('countryId'));
        echo json_encode($regions);
        break;
      
      case 'cities': //Returns cities given a regionId
        $cities = City::newInstance()->findByRegion(Params::getParam('regionId'));
        echo json_encode($cities);
        break;
        
      case 'location': // This is the autocomplete AJAX
        $cities = City::newInstance()->ajax(Params::getParam('term'));
        foreach($cities as $k => $city) {
          $cities[$k]['label'] = $city['label'] . ' (' . $city['region'] . ')';
        }
        
        echo json_encode($cities);
        break;
        
      case 'location_countries': // This is the autocomplete AJAX
        $countries = Country::newInstance()->ajax(Params::getParam('term'));
        echo json_encode($countries);
        break;
        
      case 'location_regions': // This is the autocomplete AJAX
        $regions = Region::newInstance()->ajax(Params::getParam('term'), Params::getParam('country'));
        echo json_encode($regions);
        break;
        
      case 'location_cities': // This is the autocomplete AJAX
        $cities = City::newInstance()->ajax(Params::getParam('term'), Params::getParam('region'));
        echo json_encode($cities);
        break;
        
      case 'rotate_image': // Rotate image via AJAX
        header('Content-type: image/jpeg');

        $degrees = -90; 
        $filename = Params::getParam('file_name');
        $filesrc = osc_content_path().'uploads/temp/'.$filename;

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $success = false;

        if(strtolower($ext) == 'jpg' || strtolower($ext) == 'jpeg') {
          $source = imagecreatefromjpeg($filesrc);
          $rotate = imagerotate($source, $degrees, 0);

          // save
          $success = imagejpeg($rotate, $filesrc); 

        } else if(strtolower($ext) == 'png') {
          $source = imagecreatefrompng($filesrc);
          $rotate = imagerotate($source, $degrees, 0);

          // save
          $success = imagepng($rotate, $filesrc); 

        } else if(strtolower($ext) == 'gif') {
          $source = imagecreatefromgif($filesrc);
          $rotate = imagerotate($source, $degrees, 0);

          // save
          $success = imagegif($rotate, $filesrc); 
        }

        if(PHP_VERSION_ID < 80500) {
          @imagedestroy($source);
          @imagedestroy($rotate);
        } else {
          unset($source);
          unset($rotate);
        }
        
        echo json_encode(
          array(
            'success' => $success ,
            'msg' => _m($success ? 'The selected photo has been successfully rotated' : "The selected photo couldn't be rotated")
          )
        );

        return false;
        break;
      
      case 'delete_image': // Delete images via AJAX
        $ajax_photo = Params::getParam('ajax_photo');
        $id = Params::getParam('id');
        $item = Params::getParam('item');
        $code = Params::getParam('code');
        $secret = Params::getParam('secret');
        $json = array();

        if($ajax_photo!='') {
          $files = Session::newInstance()->_get('ajax_files');
          $success = false;

          foreach($files as $uuid => $file) {
            if($file==$ajax_photo) {
              $filename = $files[$uuid];
              unset($files[$uuid]);
              Session::newInstance()->_set('ajax_files', $files);
              $success = @unlink(osc_content_path().'uploads/temp/'.$filename);
              break;
            }
          }

          echo json_encode(
            array(
              'success' => $success ,
              'msg' => _m($success ? 'The selected photo has been successfully deleted' : "The selected photo couldn't be deleted")
            )
          );

          return false;
        }

        if(Session::newInstance()->_get('userId') > 0){
          $userId = Session::newInstance()->_get('userId');
          $user = osc_get_user_row($userId);
        } else {
          $userId = null;
          $user = null;
        }

        // Check for required fields
        if(!(is_numeric($id) && is_numeric($item) && preg_match('/^([a-z0-9]+)$/i', $code))) {
          $json['success'] = false;
          $json['msg'] = _m("The selected photo couldn't be deleted, the url doesn't exist");
          echo json_encode($json);
          return false;
        }

        try {
          $aItem = osc_get_item_row($item);
        } catch (Exception $e) {
        }

        // Check if the item exists
        if(count($aItem) == 0) {
          $json['success'] = false;
          $json['msg'] = _m("The listing doesn't exist");
          echo json_encode($json);
          return false;
        }

        if(!osc_is_admin_user_logged_in()) {
          // Check if the item belong to the user
          if($userId != null && $userId != $aItem['fk_i_user_id']) {
            $json['success'] = false;
            $json['msg'] = _m("The listing doesn't belong to you");
            echo json_encode($json);
            return false;
          }

          // Check if the secret passphrase match with the item
          if($userId == null && $aItem['fk_i_user_id']==null && $secret != $aItem['s_secret']) {
            $json['success'] = false;
            $json['msg'] = _m("The listing doesn't belong to you");
            echo json_encode($json);
            return false;
          }
        }

        // Does id & code combination exist?
        $result = ItemResource::newInstance()->existResource($id, $code);

        if ($result > 0) {
          $resource = ItemResource::newInstance()->findByPrimaryKey($id);

          if($resource['fk_i_item_id']==$item) {
            // Delete: file, db table entry
            if(defined(OC_ADMIN)) {
              osc_deleteResource($id, true);
              Log::newInstance()->insertLog('ajax', 'deleteimage', $id, $id, 'admin', osc_logged_admin_id());
            } else {
              osc_deleteResource($id, false);
              Log::newInstance()->insertLog('ajax', 'deleteimage', $id, $id, 'user', osc_logged_user_id());
            }
            
            ItemResource::newInstance()->delete(array('pk_i_id' => $id, 'fk_i_item_id' => $item, 's_name' => $code));

            $json['msg'] =  _m('The selected photo has been successfully deleted');
            $json['success'] = 'true';
          } else {
            $json['msg'] = _m('The selected photo does not belong to you');
            $json['success'] = 'false';
          }
        } else {
          $json['msg'] = _m("The selected photo couldn't be deleted");
          $json['success'] = 'false';
        }

        echo json_encode($json);
        return true;
        break;
        
      case 'alerts': // Allow to register to an alert given (not sure it's used on admin)
        $encoded_alert = Params::getParam('alert');
        $alert_json = osc_decrypt_alert(base64_decode($encoded_alert));
        $alert_arr = @json_decode($alert_json, true);
        $alert_params = (isset($alert_arr['params']) && is_array($alert_arr['params'])) ? $alert_arr['params'] : array();
        $alert_sql = isset($alert_arr['sql']) ? $alert_arr['sql'] : '';
        
        // Remove sql and params from original array
        unset($alert_arr['params']);
        unset($alert_arr['sql']);
        
        $alert_json = json_encode($alert_arr);
        $alert_json_params = json_encode($alert_params);

        // check alert integrity / signature
        $stringToSign = osc_get_alert_public_key() . $encoded_alert;
        $signature = hex2b64(hmacsha1(osc_get_alert_private_key(), $stringToSign));
        $server_signature = Session::newInstance()->_get('alert_signature');

        if($server_signature != $signature) {
          echo '-2';
          return false;
        }

        $email = osc_esc_html(Params::getParam('email'));
        $userid = osc_esc_html(Params::getParam('userid'));

        if(osc_is_web_user_logged_in()) {
          $userid = osc_logged_user_id();
          //$user = User::newInstance()->findByPrimaryKey($userid);
          $user = osc_logged_user();
          $email = $user['s_email'];
          
        } else {
          $user = User::newInstance()->findByEmail($email);
          $userid = (@$user['pk_i_id'] > 0 ? $user['pk_i_id'] : $userid);
        }

        if($alert_json != '' && $email != '') {
          if(osc_validate_email($email)) {
            $secret = osc_genRandomPassword();
            $alert = osc_apply_filter('alert_pre_save', $alert_json, $alert_json, $userid, $email);
            $params = osc_apply_filter('alert_pre_save_params', $alert_json_params, $alert_json, $userid, $email);
            $sql = osc_apply_filter('alert_pre_save_sql', $alert_sql, $alert_json, $userid, $email);
            
            $type = 'DAILY';
            $type = osc_apply_filter('alert_pre_save_type', $type, $alert_json, $userid, $email);

            $name = osc_generate_alert_name($alert_json, 3, false);
            $name = osc_apply_filter('alert_pre_save_name', $name, $alert_json, $userid, $email, $type);
            
            $alertID = Alerts::newInstance()->createAlert($userid, $email, $name, $alert, $secret, $type, $params, $sql);
            
            osc_run_hook('alert_created', $alertID, $alert, $userid, $email, $secret);
            
            if($alertID !== false) {
              if((int)$userid > 0 && isset($user['pk_i_id'])) {
                //$user = User::newInstance()->findByPrimaryKey($userid);

                if($user['b_active'] == 1 && $user['b_enabled'] == 1) {
                  Alerts::newInstance()->activate($alertID);
                  echo '1';     // alert created
                  return true;
                  
                } else {
                  echo '-1';   // alert not created, reason: user blocked or not validated
                  return false;
                }
                
              } else {
                $aAlert = Alerts::newInstance()->findByPrimaryKey($alertID);
                osc_run_hook('hook_email_alert_validation', $aAlert, $email, $secret);      // user not found (not registered)
              }

              echo '1';  // alert created, but require email validation
              
            } else {
              echo '0';  // alert not created, same alert most probably already exists with same user ID or email
            }
            
            return true;
            
          } else {
            echo '-1';   // alert not created, email is invalid
            return false;
          }
        }
        
        echo '-3';  // alert not created, missing email or search pattern
        return false;
        break;
      
      case 'runhook': // run hooks
        $hook = Params::getParam('hook');

        if($hook == '') {
          echo json_encode(array('error' => 'hook parameter not defined'));
          break;
        }

        switch($hook) {
          case 'item_form':
            osc_run_hook('item_form', Params::getParam('catId'));
            break;
            
          case (substr($hook, 0, 10) == 'item_form_'):          // new versatile publish/edit hooks (v820)
            osc_run_hook($hook, Params::getParam('catId'));
            break;
            
          case 'item_edit':
            $catId = Params::getParam('catId');
            $itemId = Params::getParam('itemId');
            osc_run_hook('item_edit', $catId, $itemId);
            break;

          case (substr($hook, 0, 10) == 'item_edit_'):          // new versatile publish/edit hooks (v820)
            $catId = Params::getParam('catId');
            $itemId = Params::getParam('itemId');
            osc_run_hook($hook, $catId, $itemId);
            break;
            
          default:
            osc_run_hook('ajax_' . $hook);
            break;
        }
        
        break;
        
      case 'custom': // Execute via AJAX custom file
        if(Params::existParam('route')) {
          $routes = Rewrite::newInstance()->getRoutes();
          $rid = osc_esc_html(Params::getParam('route'));
          $file = '../';
          
          if(isset($routes[$rid]) && isset($routes[$rid]['file'])) {
            $file = $routes[$rid]['file'];
          }
        } else {
          // DEPRECATED: Disclosed path in URL is deprecated, use routes instead
          $file = Params::getParam('ajaxfile');
        }

        if($file == '') {
          echo json_encode(array('error' => 'no action defined'));
          break;
        }

        // valid file?
        if(strpos($file, '../') !== false  || strpos($file, '..\\') !== false || stripos($file, '/admin/') !== false) { //If the file is inside an "admin" folder, it should NOT be opened in frontend
          echo json_encode(array('error' => 'no valid ajaxFile'));
          break;
        }

        if(file_exists(osc_plugins_path() . $file)) {
          require_once osc_plugins_path() . $file;
        } else if(file_exists(osc_themes_path() . $file)) {
          require_once osc_themes_path() . $file;
        } else {
          echo json_encode(array('error' => "ajaxFile doesn't exist"));
          break;
        }

        break;
        
      case 'check_username_availability':
        $username = osc_sanitize_username(Params::getParam('s_username'));
        if(!osc_is_username_blacklisted($username)) {
          $user = User::newInstance()->findByUsername($username);
          if(isset($user['s_username'])) {
            echo json_encode(array('exists' => 1, 's_username' => $username));
          } else {
            echo json_encode(array('exists' => 0, 's_username' => $username));
          }
        } else {
          echo json_encode(array('exists' => 1, 's_username' => $username));
        }
        
        break;
        
      case 'ajax_upload':
        // Include the uploader class
        require_once LIB_PATH . 'AjaxUploader.php';
        $uploader = new AjaxUploader();
        $original = pathinfo($uploader->getOriginalName());
        
        if(osc_image_upload_library() == '') {
          $filename = uniqid('qqfile_', false) . '.' . $original['extension'];
        } else {
          $filename = uniqid('uppyfile_', false) . '.' . $original['extension'];
        }
        
        $result = $uploader->handleUpload(osc_content_path().'uploads/temp/'.$filename);
        
        if(isset($result['error'])) {
          
          if(OSC_DEBUG) {
            if(osc_image_upload_library() == 'UPPY') {
              http_response_code(401);
              echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);

            } else {
              echo $result['error'];
            }
          }
          
          error_log($result['error']);
          exit;
        }

        // auto rotate
        try {
          $img = ImageProcessing::fromFile(osc_content_path() . 'uploads/temp/' . $filename);
          $img->autoRotate();
          $img->resetOrientation();
          $img->saveToFile(osc_content_path() . 'uploads/temp/auto_' . $filename, $original['extension']);
          $img->saveToFile(osc_content_path() . 'uploads/temp/' . $filename, $original['extension']);

          $result['uploadName'] = 'auto_' . $filename;
          $result['uploadUrl'] = osc_content_url() . 'uploads/temp/auto_' . $filename;
          
          echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
          
        } catch (Exception $e) {
          if(OSC_DEBUG) {
            echo $e->getMessage();
          } else {
            echo '';
          }
        }
        
        break;
        
      case 'ajax_validate':
        $id = Params::getParam('id');
        
        if(!is_numeric($id)) { 
          echo json_encode(array('success' => false)); 
          die();
        }
        
        $secret = Params::getParam('secret');
        
        if($id > 0) {
          try {
            $item = Item::newInstance()->findByPrimaryKey($id);
          } catch (Exception $e) {
          }
          
          if(!isset($item['s_secret']) || $item['s_secret'] != $secret) {
            echo json_encode(array('success' => false));
            die();
          }
        
          $nResources = ItemResource::newInstance()->countResources($id);
          $result = array('success' => $nResources < osc_max_images_per_item() , 'count' => $nResources);
          echo json_encode($result);
        } else {
          $result = array('success' => true , 'count' => 0);
          echo json_encode($result);
        }
        
        break;
        
      case 'delete_ajax_upload':
        $files = Session::newInstance()->_get('ajax_files');
        $success = false;
        $filename = '';
        
        if(isset($files[Params::getParam('qquuid')]) && $files[Params::getParam('qquuid')]!='') {
          $filename = $files[Params::getParam('qquuid')];
          unset($files[Params::getParam('qquuid')]);
          Session::newInstance()->_set('ajax_files', $files);
          $success = @unlink(osc_content_path().'uploads/temp/'.$filename);
        }
        
        echo json_encode(array('success' => $success, 'uploadName' => $filename));
        break;



      case 'upload_profile_img': 
        $user_id = osc_logged_user_id();

        if($user_id > 0) { 
          $user = osc_get_user_row($user_id);

          if($user['s_profile_img'] <> '') {
            @unlink(osc_content_path() . 'uploads/user-images/' . $user['s_profile_img']);
          }

          $file = Params::getFiles('uppyfile');
          $allowed_extensions = explode(',', osc_allowed_extension());

          $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
          $max_file_size = 8096 * 1024;  //(in bytes)
          $file_size = (int)$file['size'];

          if(!isset($file['name']) || trim((string)$file['name'] == '')) {
            http_response_code(401);
            echo htmlspecialchars(json_encode(array('error' => __('File name is empty'))), ENT_NOQUOTES);
            exit;
          }

          if($file_size == 0) {
            http_response_code(401);
            echo htmlspecialchars(json_encode(array('error' => __('File is empty'))), ENT_NOQUOTES);
            exit;
          }
          
          if($file_size > $max_file_size) {
            http_response_code(401);
            echo htmlspecialchars(json_encode(array('error' => __('File is too large'))), ENT_NOQUOTES);
            exit;
          }

          if(!in_array($extension, $allowed_extensions)) {
            http_response_code(401);
            echo htmlspecialchars(json_encode(array('error' => __('File extension not allowed'))), ENT_NOQUOTES);
            exit;
          }
          
          if($file['error'] != UPLOAD_ERR_OK) {
            http_response_code(401);
            echo htmlspecialchars(json_encode(array('error' => __('File upload error'))), ENT_NOQUOTES);
            exit;
          }

          // Validate that user refined image to required size
          list($img_width, $img_height) = getimagesize($file['tmp_name']);
        
          $dim = osc_profile_img_dimensions();
          $dim = ($dim == '' ? '240x240' : $dim);
          $dim_ = explode('x', $dim);

          $def_width = (int)$dim_[0];
          $def_height = (int)$dim_[1];
        
          if(($img_width > 0 && $def_width > 0 && $img_width > $def_width) || ($img_height > 0 && $def_height > 0 && $img_height > $def_height)) {
            http_response_code(401);
            echo htmlspecialchars(json_encode(array('error' => sprintf(__('Image dimension does not match. Refine/crop your image before uploading. Expected image dimension is %spx.'), $dim))), ENT_NOQUOTES);
            exit;
          }
          

          $image_name = osc_logged_user_id() . '_' . osc_generate_rand_string(5) . '_' . date('Ymd') . '.' . $extension; 
          $image_url = osc_content_url() . 'uploads/user-images/' . $image_name;
          $image_path = osc_content_path() . 'uploads/user-images/' . $image_name;
          
          $res = move_uploaded_file($file['tmp_name'], $image_path);
          
          if(!$res) {
            http_response_code(401);
            echo htmlspecialchars(json_encode(array('error' => __('File could not be processed'))), ENT_NOQUOTES);
            exit;
          }
          
          User::newInstance()->updateProfileImg(osc_logged_user_id(), $image_name);
          
          echo htmlspecialchars(json_encode(array('success' => true, 'uploadName' => $image_name, 'uploadUrl' => $image_url)), ENT_NOQUOTES);
          exit;          
        }
        
        http_response_code(401);
        echo htmlspecialchars(json_encode(array('error' => __('You must be logged in'))), ENT_NOQUOTES);
        break;

      case 'remove_profile_img': 
        $user_id = osc_logged_user_id();

        if($user_id > 0) { 
          $user = osc_get_user_row($user_id);

          if($user['s_profile_img'] <> '') {
            @unlink(osc_content_path() . 'uploads/user-images/' . $user['s_profile_img']);
            User::newInstance()->updateProfileImg($user_id, '');
          }
        } else {
          echo json_encode(array('error' => 1, 'message' => __('You must be logged in')));
          exit;
        }

        echo json_encode(array('error' => 0, 'message' => osc_user_profile_img_url($user_id)));
        break;
        
        
      case 'custom_hook':   // Custom code execution via hook
        osc_run_hook('ajax_custom');
        break;
      
      default:
        echo json_encode(array('error' => __('no action defined')));
        break;
    }
  }


  /**
   * @param $file
   *
   * @return mixed|void
   */
  public function doView($file) {
    osc_run_hook('before_html');
    osc_current_web_theme_path($file);
    osc_run_hook('after_html');
  }
}

/* file end: ./ajax.php */