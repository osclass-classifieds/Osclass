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
 * Class ItemActions
 */
class ItemActions {
  public $is_admin;
  public $data;
  private $manager;

  /**
   * ItemActions constructor.
   *
   * @param bool $is_admin
   */
  public function __construct($is_admin = false) {
    $this->is_admin = $is_admin;
    $this->manager = Item::newInstance();
  }

  /**
   * Delete resources from the hard drive
   *
   * @param int  $itemId
   * @param bool $is_admin
   *
   * @throws \Exception
   */
  public static function deleteResourcesFromHD($itemId, $is_admin = false) {
    $resources = ItemResource::newInstance()->getAllResourcesFromItem($itemId);
    
    Log::newInstance()->insertLog(
      'itemActions',
      'deleteResourcesFromHD',
      $itemId,
      $itemId,
      $is_admin ? 'admin' : 'user',
      $is_admin ? osc_logged_admin_id() : osc_logged_user_id()
    );
    
    $log_ids = '';
    
    foreach($resources as $resource) {
      osc_deleteResource($resource['pk_i_id'], $is_admin);
      $log_ids .= $resource['pk_i_id'] . ',';
    }
    
    Log::newInstance()->insertLog(
      'itemActions',
      'deleteResourcesFromHD',
      $itemId,
      substr($log_ids, 0, 250),
      $is_admin ? 'admin' : 'user',
      $is_admin ? osc_logged_admin_id() : osc_logged_user_id()
    );
  }

  /**
   * @return boolean
   * @throws \Exception
   */
  public function add() {
    $aItem = $this->data;
    $aItem = osc_apply_filter('item_add_prepare_data', $aItem);
    $is_spam = 0;
    $enabled = 1;
    $code = osc_genRandomPassword();
    $flash_error = '';

    // Requires email validation?
    //$has_to_validate = osc_moderate_items() !== -1;

    // Check status
    $active = $aItem['active'];

    // Sanitize
    foreach($aItem['title'] as $key => $value) {
      $aItem['title'][$key] = strip_tags(trim((string)$value));
    }

    $aItem['price'] = $aItem['price'] !== null ? strip_tags(trim((string)$aItem['price'])) : $aItem['price'];
    $contactName = strip_tags(trim((string)$aItem['contactName']));
    $contactEmail = strip_tags(trim((string)$aItem['contactEmail']));
    $contactPhone = strip_tags(trim((string)$aItem['contactPhone']));
    $contactOther = strip_tags(trim((string)$aItem['contactOther']));
    $aItem['cityArea'] = osc_sanitize_name(strip_tags(trim((string)$aItem['cityArea'])));
    $aItem['address'] = osc_sanitize_name(strip_tags(trim((string)$aItem['address'])));

    // Anonymous
    $contactName = osc_validate_text($contactName, 3) ? $contactName : __('Anonymous');

    // Validate
    if(!$this->checkAllowedExt($aItem['photos'])) {
      $flash_error .= _m('Image with an incorrect extension.') . PHP_EOL;
    }
    if(!$this->checkSize($aItem['photos'])) {
      $flash_error .= _m('Image is too big. Max. size') . osc_max_size_kb() . ' Kb' . PHP_EOL;
    }

    $title_message = '';
    foreach(@$aItem['title'] as $key => $value) {
      if(osc_validate_text($value) && osc_validate_max($value, osc_max_characters_per_title())) {
        $title_message = '';
        break;
      }

      $title_message .= (!osc_validate_text($value) ? sprintf(_m('Title too short (%s).'), $key) . PHP_EOL : '');
      $title_message .= (!osc_validate_max($value, osc_max_characters_per_title()) ? sprintf(_m('Title too long (%s).'), $key) . PHP_EOL : '');
    }
    
    $flash_error .= $title_message;

    $desc_message = '';
    foreach(@$aItem['description'] as $key => $value) {
      if(osc_validate_text($value, 3) && osc_validate_max($value, osc_max_characters_per_description())) {
        $desc_message = '';
        break;
      }

      $desc_message .= (!osc_validate_text($value, 3) ? sprintf(_m('Description too short (%s).'), $key) . PHP_EOL : '');
      $desc_message .= (!osc_validate_max($value, osc_max_characters_per_description()) ? sprintf(_m('Description too long (%s).'), $key) . PHP_EOL : '');
    }
    $flash_error .= $desc_message;

    // akismet check spam ...
    if($this->_akismet_text($aItem['title'], $aItem['description'], $contactName, $contactEmail)) {
      $is_spam = 1;
    }

    $flash_error .= ((!osc_validate_category($aItem['catId'])) ? _m('Category invalid.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_number($aItem['price'])) ? _m('Price must be a number.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_max(number_format((float)$aItem['price'], 0, '', ''), 25)) ? _m('Price too long.') . PHP_EOL : '');
    $flash_error .= (($aItem['price'] !== null && (int)$aItem['price'] < 0) ? _m('Price must be positive number.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_max($contactName, 35)) ? _m('Name too long.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_max($contactPhone, 100)) ? _m('Phone too long.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_max($contactOther, 100)) ? _m('Other contact information too long.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_email($contactEmail)) ? _m('Email invalid.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_text($aItem['countryName'], 2, false)) ? _m('Country too short.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_max($aItem['countryName'], 50)) ? _m('Country too long.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_text($aItem['regionName'], 2, false)) ? _m('Region too short.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_max($aItem['regionName'], 50)) ? _m('Region too long.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_text($aItem['cityName'], 2, false)) ? _m('City too short.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_max($aItem['cityName'], 50)) ? _m('City too long.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_text($aItem['cityArea'], 2, false)) ? _m('Municipality too short.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_max($aItem['cityArea'], 50)) ? _m('Municipality too long.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_text($aItem['address'], 3, false)) ? _m('Address too short.') . PHP_EOL  : '');
    $flash_error .= ((!osc_validate_max($aItem['address'], 100)) ? _m('Address too long.') . PHP_EOL : '');
    $flash_error .= ((((time() - (int)Session::newInstance()->_get('last_submit_item')) < osc_items_wait_time()) && !$this->is_admin) ? _m('Too fast. You should wait a little to publish your ad.') . PHP_EOL : '');

    $_meta = Field::newInstance()->findByCategory($aItem['catId']);
    $meta = (is_array(Params::getParam('meta')) ? Params::getParam('meta') : array());

    if(is_array($_meta) && count($_meta) > 0) {
      foreach($_meta as $_m) {
        if(isset($_m['pk_i_id']) && trim((string)$_m['pk_i_id']) <> '') {
          $meta[$_m['pk_i_id']] = isset($meta[$_m['pk_i_id']]) ? $meta[$_m['pk_i_id']] : '';
        }
      }
    }

    if($meta != '' && is_array($meta) && count($meta) > 0) {
      $mField = Field::newInstance();
      
      foreach($meta as $k => $v) {
        if($v == '') {
          $field = $mField->findByPrimaryKey($k);
          if($field['b_required'] == 1) {
            $flash_error .= sprintf(_m('%s field is required.'), $field['s_name']) . PHP_EOL;
          }
        }
      }
    }

    // hook pre add or edit
    // DEPRECATED: pre_item_post will be removed in 3.4
    osc_run_hook('pre_item_post');
    osc_run_hook('pre_item_add', $aItem, $flash_error);
    $flash_error = osc_apply_filter('pre_item_add_error', $flash_error, $aItem);

    // Handle error
    if($flash_error) {
      $success = $flash_error;
    } else {
      if(empty($aItem['price'])) {
        $aItem['currency'] = null;
      }

      $aInsert = array(
        'fk_i_user_id' => $aItem['userId'],
        'fk_i_category_id' => $aItem['catId'],
        'i_price' => $aItem['price'],
        'fk_c_currency_code' => $aItem['currency'],
        's_contact_name' => $contactName,
        's_contact_email' => $contactEmail,
        's_contact_phone' => $contactPhone,
        's_contact_other' => $contactOther,
        's_secret' => $code,
        'b_active' => $active == 'ACTIVE' ? 1 : 0,
        'b_enabled' => $enabled,
        'b_show_email' => $aItem['showEmail'],
        'b_show_phone' => $aItem['showPhone'],
        'b_spam' => $is_spam,
        's_ip' => $aItem['s_ip']
      );

      if($this->is_admin && $aItem['dt_pub_date'] != '') {
        $aInsert['dt_pub_date'] = $aItem['dt_pub_date'];
      } else {
        $aInsert['dt_pub_date'] = date('Y-m-d H:i:s');
      }
      
      $aInsert = osc_apply_filter('item_post_data', $aInsert);

      $this->manager->insert($aInsert);

      if(!$this->is_admin) {
        // Track spam delay: Session
        Session::newInstance()->_set('last_submit_item', time());
        // Track spam delay: Cookie
        //Cookie::newInstance()->set_expires(osc_time_cookie());
        Cookie::newInstance()->push('last_submit_item', time());
        Cookie::newInstance()->set();
      }

      $itemId = $this->manager->dao->insertedId();
      
      Log::newInstance()->insertLog(
        'item',
        'add',
        $itemId,
        current(array_values($aItem['title'])),
        $this->is_admin ? 'admin' : 'user',
        $this->is_admin ? osc_logged_admin_id() : osc_logged_user_id()
      );

      Params::setParam('itemId', $itemId);

      // INSERT title and description locales
      $this->insertItemLocales('ADD', $aItem['title'], $aItem['description'], $itemId);

      $location = array(
        'fk_i_item_id' => $itemId,
        'fk_c_country_code' => $aItem['countryId'],
        's_country' => $aItem['countryName'],
        's_country_native' => (isset($aItem['countryNameNative']) ? $aItem['countryNameNative'] : null),
        'fk_i_region_id' => $aItem['regionId'],
        's_region' => $aItem['regionName'],
        's_region_native' => (isset($aItem['regionNameNative']) ? $aItem['regionNameNative'] : null),
        'fk_i_city_id' => $aItem['cityId'],
        's_city' => $aItem['cityName'],
        's_city_native' => (isset($aItem['cityNameNative']) ? $aItem['cityNameNative'] : null),
        's_city_area' => $aItem['cityArea'],
        's_address' => $aItem['address'],
        'd_coord_lat' => ($aItem['d_coord_lat'] <> '' ? $aItem['d_coord_lat'] : (isset($aItem['cityLat']) ? $aItem['cityLat'] : null)),
        'd_coord_long' => ($aItem['d_coord_long'] <> '' ? $aItem['d_coord_long'] : (isset($aItem['cityLong']) ? $aItem['cityLong'] : null)),
        's_zip' => $aItem['s_zip']
      );

      $location = osc_apply_filter('item_post_location_data', $location);
      $locationManager = ItemLocation::newInstance();
      $locationManager->insert($location);

      $photos = osc_apply_filter('item_post_image_data', $aItem['photos']);
      $this->uploadItemResources($photos, $itemId);

      // update dt_expiration at t_item
      Item::newInstance()->updateExpirationDate($itemId, $aItem['dt_expiration']);

      // Custom fields
      $meta = osc_apply_filter('item_post_meta_data', $meta);
      
      if($meta != '' && is_array($meta) && count($meta) > 0) {
        $mField = Field::newInstance();
        foreach($meta as $k => $v) {
          // if dateinterval
          if(is_array($v) && !isset($v['from']) && !isset($v['to'])) {
            $v = implode(',', $v);
          }
          $mField->replace($itemId, $k, $v);
        }
      }

      // We need at least one record in t_item_stats
      $mStats = new ItemStats();
      $mStats->emptyRow($itemId);

      $item = $this->manager->findByPrimaryKey($itemId);
      $aItem['item'] = $item;


      Session::newInstance()->_set('last_publish_time', time());
      
      if(!$this->is_admin) {
        $this->sendEmails(osc_apply_filter('item_post_email_data', $aItem));
      }

      if($active === 'INACTIVE') {
        $success = 1;
      } else {
        $aAux = array(
          'fk_i_user_id' => $aItem['userId'],
          'fk_i_category_id' => $aItem['catId'],
          'fk_c_country_code' => $location['fk_c_country_code'],
          'fk_i_region_id' => $location['fk_i_region_id'],
          'fk_i_city_id' => $location['fk_i_city_id']
        );
        
        // if is_spam not increase stats
        if($is_spam == 0) {
          $this->_increaseStats($aAux);
        }
        
        $success = 2;
      }

      // THIS HOOK IS FINE, YAY!
      osc_run_hook('posted_item', $item);
    }

    return $success;
  }

  /**
   * @param $aResources
   *
   * @return bool
   */
  /**
   * @param $aResources
   *
   * @return bool
   */
  private function checkAllowedExt($aResources) {
    $success = true;

    require LIB_PATH . 'osclass/mimes.php';
    if(!empty($aResources)) {
      // get allowedExt
      $aMimesAllowed = array();
      $aExt = explode(',', osc_allowed_extension());
      foreach($aExt as $ext) {
        if(isset($mimes[$ext])) {
          /** @var array $mimes */
          $mime = $mimes[$ext];
          if(is_array($mime)) {
            foreach($mime as $aux) {
              if(!in_array($aux, $aMimesAllowed, false)) {
                $aMimesAllowed[] = $aux;
              }
            }
          } elseif(!in_array($mime, $aMimesAllowed, false)) {
            $aMimesAllowed[] = $mime;
          }
        }
      }
      foreach($aResources['error'] as $key => $error) {
        $bool_img = false;
        if($error == UPLOAD_ERR_OK) {
          // check mime file
          $fileMime = $aResources['type'][$key];
          if(stripos($fileMime, 'image/') !== false) {
            if(function_exists('getimagesize')) {
              $info = @getimagesize($aResources['tmp_name'][$key]);
              
              if(isset($info['mime'])) {
                $fileMime = $info['mime'];
              } else {
                $fileMime = '';
              }
            }
          }


          if(in_array($fileMime, $aMimesAllowed, false)) {
            $bool_img = true;
          }
          if(!$bool_img && $success) {
            $success = false;
          }
        }
      }

      if(!$success) {
        osc_add_flash_error_message(_m('The file you tried to upload does not have a valid extension'));
      }
    }

    return $success;
  }

  /**
   * @param $aResources
   *
   * @return bool
   */
  private function checkSize($aResources) {
    $success = true;

    if(!empty($aResources)) {
      // get allowedExt
      $maxSize = osc_max_size_kb() * 1024;
      foreach($aResources['error'] as $key => $error) {
        if($error == UPLOAD_ERR_OK) {
          $size = $aResources['size'][$key];
          if($size >= $maxSize) {
            $success = false;
          }
        }
      }
      if(!$success) {
        osc_add_flash_error_message(_m('One of the files you tried to upload exceeds the maximum size'));
      }
    }

    return $success;
  }

  /**
   * @param array  $title
   * @param array  $description
   * @param string $author
   * @param string $email
   *
   * @return bool
   * @throws \exception
   */
  /**
   * @param array  $title
   * @param array  $description
   * @param string $author
   * @param string $email
   *
   * @return bool
   * @throws \exception
   */
  private function _akismet_text($title, $description, $author, $email) {
    $spam = false;
    foreach($title as $k => $_data) {
      $_title = $title[$k];
      $_description = $description[$k];
      $content = $_title . ' ' . $_description;
      if(osc_akismet_key()) {
        require_once LIB_PATH . 'Akismet.class.php';
        $akismet = new Akismet(osc_base_url(), osc_akismet_key());

        $akismet->setCommentContent($content);
        $akismet->setCommentAuthor($author);
        $akismet->setCommentAuthorEmail($email);
        $akismet->setUserIP(osc_get_ip());

        $status = '';
        $status = $akismet->isCommentSpam() ? 'SPAM' : $status;
        if($status === 'SPAM') {
          $spam = true;
          break;
        }
      }
    }

    return $spam;
  }

  /**
   * @param $type
   * @param $title
   * @param $description
   * @param $itemId
   */
  /**
   * @param $type
   * @param $title
   * @param $description
   * @param $itemId
   */
  public function insertItemLocales($type, $title, $description, $itemId) {
    foreach($title as $k => $_data) {
      $_title = $title[$k];
      $_description = $description[$k];
      if($type === 'ADD') {
        $this->manager->insertLocale($itemId, $k, $_title, $_description);
      } elseif($type === 'EDIT') {
        $this->manager->updateLocaleForce($itemId, $k, $_title, $_description);
      }
    }
  }

  /**
   * @param $aResources
   * @param $itemId
   *
   * @return int
   * @throws \Exception
   */
  /**
   * @param $aResources
   * @param $itemId
   *
   * @return int
   * @throws \Exception
   */
  public function uploadItemResources($aResources, $itemId) {
    if(is_array($aResources) && !empty($aResources)) {
      $limit_reached = false;
      $itemResourceManager = ItemResource::newInstance();
      $folder = osc_uploads_path() . floor($itemId / 100) . '/';
      $maxImagesPerItem = osc_max_images_per_item();
      $totalItemImages = $itemResourceManager->countResources($itemId);
      $newUploadImages = 0;
      
      foreach($aResources['error'] as $key => $error) {
        if($maxImagesPerItem == 0 || ($maxImagesPerItem > 0 && ($totalItemImages + $newUploadImages) < $maxImagesPerItem)) {
          if($error == UPLOAD_ERR_OK) {
            $tmpName = $aResources['tmp_name'][$key];
            $order = (isset($aResources['order'][$key]) ? $aResources['order'][$key] : 1);
            $imgres = ImageProcessing::fromFile($tmpName);
            $extension = osc_apply_filter('upload_image_extension', $imgres->getExt());
            $mime = osc_apply_filter('upload_image_mime', $imgres->getMime());

            // Create normal size
            $normal_path = $path = $tmpName . '_normal';
            $size = explode('x', osc_normal_dimensions());
            $img = $imgres->autoRotate();

            $img = $img->resizeTo($size[0], $size[1]);
            
            if(osc_is_watermark_text()) {
              $img->doWatermarkText(osc_watermark_text(), osc_watermark_text_color());
            } elseif(osc_is_watermark_image()) {
              $img->doWatermarkImage();
            }
            
            $img->saveToFile($path, $extension);

            // Create preview
            $path = $tmpName . '_preview';
            $size = explode('x', osc_preview_dimensions());
            ImageProcessing::fromFile($normal_path)->resizeTo($size[0], $size[1])->saveToFile($path, $extension);

            // Create thumbnail
            $path = $tmpName . '_thumbnail';
            $size = explode('x', osc_thumbnail_dimensions());
            ImageProcessing::fromFile($normal_path)->resizeTo($size[0], $size[1])->saveToFile($path, $extension);

            $newUploadImages++;
            
            $itemResourceManager->insert(array('fk_i_item_id' => $itemId));
            $resourceId = $itemResourceManager->dao->insertedId();

            if(!is_dir($folder)) {
              if(!@mkdir($folder, 0755, true)) {
                return 3; // PATH CAN NOT BE CREATED
              }
            }
            
            $copy_options = array('folderPermission' => 0755, 'filePermission' => 0644);
            
            osc_copy($tmpName . '_normal', $folder . $resourceId . '.' . $extension, $copy_options);
            osc_copy($tmpName . '_preview', $folder . $resourceId . '_preview.' . $extension, $copy_options);
            osc_copy($tmpName . '_thumbnail', $folder . $resourceId . '_thumbnail.' . $extension, $copy_options);
            
            if(osc_keep_original_image()) {
              $path = $folder . $resourceId . '_original.' . $extension;
              osc_copy($tmpName, $path, $copy_options);
            }
            
            @unlink($tmpName . '_normal');
            @unlink($tmpName . '_preview');
            @unlink($tmpName . '_thumbnail');
            @unlink($tmpName);

            $s_path = str_replace(osc_base_path(), '', $folder);
            $itemResourceManager->update(
              array(
                's_path' => $s_path,
                's_name' => osc_genRandomPassword(),
                's_extension' => $extension,
                's_content_type' => $mime,
                'i_order' => $order
              ),
              array(
                'pk_i_id' => $resourceId,
                'fk_i_item_id' => $itemId
              )
            );
            
            osc_run_hook('uploaded_file', ItemResource::newInstance()->findByPrimaryKey($resourceId));
          }
        } else {
          // images per item limit reached 
          $limit_reached = true;
        }
      }
      
      if($limit_reached) {
        osc_add_flash_warning_message(_m('Image limit reached, some images were not uploaded!'));
      }
      
      unset($itemResourceManager);
    }

    return 0; // NO PROBLEMS
  }


  public function reorderItemResources($ajax_order_list, $item_id) {
    $ajax_order_list = explode(',', $ajax_order_list);

    if(osc_image_upload_reorder() && is_array($ajax_order_list) && !empty($ajax_order_list)) {
      $itemResourceManager = ItemResource::newInstance();
      //$aResources = $itemResourceManager->getAllResourcesFromItem($itemId);
        
      foreach($ajax_order_list as $key => $val) {
        $val_ = explode('-', $val);
        
        // order related to existing resources is in format "res-{resourceId}"
        if($val_[0] == 'res' && isset($val_[1]) && (int)$val_[1] > 0) {
          $resource_id = (int)$val_[1];
          $order = $key+1;
        
          $itemResourceManager->update(array('i_order' => $order), array('pk_i_id' => $resource_id, 'fk_i_item_id' => $item_id));
        }
      }
    }

    return 0; // NO PROBLEMS
  }




  /**
   * @param $aItem
   */
  public function sendEmails($aItem) {
    $item = $aItem['item'];
    View::newInstance()->_exportVariableToView('item', $item);
    
    // Modify via filter to send no emails at all
    if(isset($aItem['send_no_emails']) && $aItem['send_no_emails'] == 1) {
      return false;
    }

    /**
     * Send email to non-reg user requesting item activation
     */
    if($aItem['active'] === 'INACTIVE' && Session::newInstance()->_get('userId') === '') {
      osc_run_hook('hook_email_item_validation_non_register_user', $item);
    } elseif($aItem['active'] === 'INACTIVE') { //  USER IS REGISTERED
      osc_run_hook('hook_email_item_validation', $item);
    } elseif(Session::newInstance()->_get('userId') === '') { // USER IS NOT REGISTERED
      osc_run_hook('hook_email_new_item_non_register_user', $item);
    }

    /**
     * Send email to admin about the new item
     */
    if(osc_notify_new_item()) {
      osc_run_hook('hook_email_admin_new_item', $item);
    }
  }

  /**
   * Private function for increment stats.
   * tables: t_user/t_category_stats/t_country_stats/t_region_stats/t_city_stats
   *
   * @param array item
   *
   * @throws \Exception
   */
  private function _increaseStats($item) {
    if($item['fk_i_user_id'] !== null) {
      User::newInstance()->increaseNumItems($item['fk_i_user_id']);
    }
    
    CategoryStats::newInstance()->increaseNumItems($item['fk_i_category_id']);
    CountryStats::newInstance()->increaseNumItems($item['fk_c_country_code']);
    RegionStats::newInstance()->increaseNumItems($item['fk_i_region_id']);
    CityStats::newInstance()->increaseNumItems($item['fk_i_city_id']);
    osc_run_hook('item_increase_stat', $item);
  }

  /**
   * @return bool|mixed
   * @throws \Exception
   */
  /**
   * @return bool|mixed
   * @throws \Exception
   */
  public function edit() {
    $aItem = $this->data;
    $aItem = osc_apply_filter('item_edit_prepare_data', $aItem);
    $flash_error = '';

    // Sanitize
    foreach($aItem['title'] as $key => $value) {
      $aItem['title'][$key] = strip_tags(trim((string)$value));
    }

    $aItem['price'] = $aItem['price'] !== null ? strip_tags(trim((string) $aItem['price'])) : $aItem['price'];
    $aItem['cityArea'] = osc_sanitize_name(strip_tags(trim((string) $aItem['cityArea'])));
    $aItem['address'] = osc_sanitize_name(strip_tags(trim((string) $aItem['address'])));

    // Validate
    if(!$this->checkAllowedExt($aItem['photos'])) {
      $flash_error .= _m('Image with an incorrect extension.') . PHP_EOL;
    }
    
    if(!$this->checkSize($aItem['photos'])) {
      $flash_error .= _m('Image is too big. Max. size') . osc_max_size_kb() . ' Kb' . PHP_EOL;
    }

    $td_message = '';
    foreach(@$aItem['title'] as $key => $value) {
      if(osc_validate_text($value) && osc_validate_max($value, osc_max_characters_per_title())) {
        $td_message = '';
        break;
      }

      $td_message .= (!osc_validate_text($value) ? _m('Title too short.') . PHP_EOL : '') . (!osc_validate_max($value, osc_max_characters_per_title()) ? _m('Title too long.')                                           . PHP_EOL : '');
    }
    
    $flash_error .= $td_message;

    $desc_message = '';
    foreach(@$aItem['description'] as $key => $value) {
      if(osc_validate_text($value, 3) && osc_validate_max($value, osc_max_characters_per_description())) {
        $desc_message = '';
        break;
      }

      $desc_message .= (!osc_validate_text($value, 3) ? _m('Description too short.') . PHP_EOL : '') . (!osc_validate_max($value, osc_max_characters_per_description()) ? _m('Description too long.') . PHP_EOL : '');
    }
    
    $flash_error .= $desc_message;

    $flash_error .= ((!osc_validate_category($aItem['catId'])) ? _m('Category invalid.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_number($aItem['price'])) ? _m('Price must be a number.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_max(number_format((float)$aItem['price'], 0, '', ''), 15)) ? _m('Price too long.') . PHP_EOL : '');
    $flash_error .= (($aItem['price'] !== null && (int)$aItem['price'] < 0) ? _m('Price must be positive number.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_text($aItem['countryName'], 3, false)) ? _m('Country too short.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_max($aItem['countryName'], 50)) ? _m('Country too long.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_text($aItem['regionName'], 2, false)) ? _m('Region too short.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_max($aItem['regionName'], 50)) ? _m('Region too long.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_text($aItem['cityName'], 2, false)) ? _m('City too short.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_max($aItem['cityName'], 50)) ? _m('City too long.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_text($aItem['cityArea'], 3, false)) ? _m('Municipality too short.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_max($aItem['cityArea'], 50)) ? _m('Municipality too long.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_text($aItem['address'], 3, false)) ? _m('Address too short.') . PHP_EOL : '');
    $flash_error .= ((!osc_validate_max($aItem['address'], 100)) ? _m('Address too long.') . PHP_EOL : '');

    $_meta = Field::newInstance()->findByCategory($aItem['catId']);
    $meta = Params::getParam('meta');
    
    if(is_array($meta)) {
      foreach($_meta as $_m) {
        if(isset($_m['pk_i_id']) && $_m['pk_i_id'] > 0) {
          $meta[$_m['pk_i_id']] = isset($meta[$_m['pk_i_id']]) ? $meta[$_m['pk_i_id']] : '';
        }
      }
    }
    
    if($meta != '' && is_array($meta) && count($meta) > 0) {
      $mField = Field::newInstance();
      
      foreach($meta as $k => $v) {
        if($v == '') {
          $field = $mField->findByPrimaryKey($k);
          if($field['b_required'] == 1) {
            $flash_error .= sprintf(_m('%s field is required.'), $field['s_name']) . PHP_EOL;
          }
        }
      }
    }

    // hook pre add or edit
    // DEPRECATED : preitem_psot will be removed in 3.4
    osc_run_hook('pre_item_post');
    osc_run_hook('pre_item_edit', $aItem, $flash_error);
    $flash_error = osc_apply_filter('pre_item_edit_error', $flash_error, $aItem);

    // Handle error
    if($flash_error) {
      $success = $flash_error;
    } else {
      $location = array(
        'fk_c_country_code' => $aItem['countryId'],
        's_country' => $aItem['countryName'],
        's_country_native' => (isset($aItem['countryNameNative']) ? $aItem['countryNameNative'] : null),
        'fk_i_region_id' => $aItem['regionId'],
        's_region' => $aItem['regionName'],
        's_region_native' => (isset($aItem['regionNameNative']) ? $aItem['regionNameNative'] : null),
        'fk_i_city_id' => $aItem['cityId'],
        's_city' => $aItem['cityName'],
        's_city_native' => (isset($aItem['cityNameNative']) ? $aItem['cityNameNative'] : null),
        's_city_area' => $aItem['cityArea'],
        's_address' => $aItem['address'],
        'd_coord_lat' => ($aItem['d_coord_lat'] <> '' ? $aItem['d_coord_lat'] : (isset($aItem['cityLat']) ? $aItem['cityLat'] : null)),
        'd_coord_long' => ($aItem['d_coord_long'] <> '' ? $aItem['d_coord_long'] : (isset($aItem['cityLong']) ? $aItem['cityLong'] : null)),
        's_zip' => $aItem['s_zip']
      );

      $location = osc_apply_filter('item_edit_location_data', $location);
      $locationManager = ItemLocation::newInstance();
      $old_item_location = $locationManager->findByPrimaryKey($aItem['idItem']);

      $locationManager->update($location, array('fk_i_item_id' => $aItem['idItem']));

      $old_item = $this->manager->findByPrimaryKey($aItem['idItem']);

      if($aItem['userId'] != '') {
        $user = osc_get_user_row($aItem['userId']);
        $aItem['contactName'] = $user['s_name'];
        $aItem['contactEmail'] = $user['s_email'];
      } else {
        $aItem['userId'] = null;
      }

      if(empty($aItem['price'])) {
        $aItem['currency'] = null;
      }

      $aUpdate = array(
        'dt_mod_date' => date('Y-m-d H:i:s'),
        'fk_i_category_id' => $aItem['catId'],
        'i_price' => $aItem['price'],
        'fk_c_currency_code' => $aItem['currency'],
        'b_show_email' => $aItem['showEmail'],
        'b_show_phone' => $aItem['showPhone'],
        's_contact_phone' => $aItem['contactPhone'],
        's_contact_other' => $aItem['contactOther']
      );
      
      // only can change the user if you're an admin
      if($this->is_admin) {
        $aUpdate['fk_i_user_id'] = $aItem['userId'];
        $aUpdate['s_contact_name'] = $aItem['contactName'];
        $aUpdate['s_contact_email'] = $aItem['contactEmail'];
      } else {
        $aUpdate['s_ip'] = $aItem['s_ip'];
      }
      
      // Check pub date
      if($this->is_admin && $aItem['dt_pub_date'] != '') {
        $aUpdate['dt_pub_date'] = $aItem['dt_pub_date'];
      }

      $aUpdate = osc_apply_filter('item_edit_data', $aUpdate);

      $result = $this->manager->update($aUpdate, array(
        'pk_i_id' => $aItem['idItem'],
        's_secret' => $aItem['secret']
      ));
      
     
      // UPDATE title and description locales
      $this->insertItemLocales('EDIT', $aItem['title'], $aItem['description'], $aItem['idItem']);
      
      // UPLOAD item resources
      $photos = osc_apply_filter('item_edit_image_data', $aItem['photos']);
      $this->uploadItemResources($photos, $aItem['idItem']);
      
      // REORDER item resources
      $this->reorderItemResources($aItem['ajax_order_list'], $aItem['idItem']);
      
      Log::newInstance()->insertLog(
        'item',
        'edit',
        $aItem['idItem'],
        current(array_values($aItem['title'])),
        $this->is_admin ? 'admin' : 'user',
        $this->is_admin ? osc_logged_admin_id() : osc_logged_user_id()
      );
      
      // Custom fields
      $meta = osc_apply_filter('item_edit_meta_data', $meta);
      if($meta != '' && is_array($meta) && count($meta) > 0) {
        $mField = Field::newInstance();
        
        foreach($meta as $k => $v) {
          // if dateinterval
          if(is_array($v) && !isset($v['from']) && !isset($v['to'])) {
            $v = implode(',', $v);
          }
          $mField->replace($aItem['idItem'], $k, $v);
        }
      }

      $oldIsExpired = osc_isExpired($old_item['dt_expiration']);
      $dt_expiration = Item::newInstance()->updateExpirationDate($aItem['idItem'], $aItem['dt_expiration'], false);

      if($dt_expiration === false) {
        $dt_expiration = $old_item['dt_expiration'];
        $aItem['dt_expiration'] = $old_item['dt_expiration'];
      }
      
      $newIsExpired = osc_isExpired($dt_expiration);

      // Recalculate stats related with items
      $this->_updateStats(
        $result,
        $old_item,
        $oldIsExpired,
        $old_item_location,
        $aItem,
        $newIsExpired,
        $location
      );

      unset($old_item);

      // THIS HOOK IS FINE, YAY!
      osc_run_hook('edited_item', Item::newInstance()->findByPrimaryKey($aItem['idItem']));
      $success = $result;
    }

    return $success;
  }

  /**
   * Increment or decrement stats related with items.
   *
   * User item stats, Category item stats,
   *  country item stats, region item stats, city item stats
   *
   * @param bool | array $result
   * @param array    $old_item
   * @param bool     $oldIsExpired
   * @param array    $old_item_location
   * @param array    $aItem
   * @param bool     $newIsExpired
   * @param array    $location
   *
   * @throws \Exception
   */
  private function _updateStats($result, $old_item, $oldIsExpired, $old_item_location, $aItem, $newIsExpired, $location) {
    if($result == 1 && $old_item['b_enabled'] == 1 && $old_item['b_active'] == 1 && $old_item['b_spam'] == 0) {
      // if old item is expired and new item is not expired.
      if($oldIsExpired && !$newIsExpired) {
        // increment new item stats (user, category, location_stats)
        if($aItem['userId'] > 0) {
          User::newInstance()->increaseNumItems($aItem['userId']);
        }
        
        CategoryStats::newInstance()->increaseNumItems($aItem['catId']);
        CountryStats::newInstance()->increaseNumItems($location['fk_c_country_code']);
        RegionStats::newInstance()->increaseNumItems($location['fk_i_region_id']);
        CityStats::newInstance()->increaseNumItems($location['fk_i_city_id']);
      }
      
      // if old is not expired and new is expired
      if(!$oldIsExpired && $newIsExpired) {
        // decrement new item stats (user, category, location_stats)
        if($old_item['fk_i_user_id'] > 0) {
          User::newInstance()->decreaseNumItems($old_item['fk_i_user_id']);
        }
        
        CategoryStats::newInstance()->decreaseNumItems($aItem['catId']);
        CountryStats::newInstance()->decreaseNumItems($location['fk_c_country_code']);
        RegionStats::newInstance()->decreaseNumItems($location['fk_i_region_id']);
        CityStats::newInstance()->decreaseNumItems($location['fk_i_city_id']);
      }
      
      // if old item is not expired and new item is not expired
      if(!$oldIsExpired && !$newIsExpired) {
        // Update user stats - if old user diferent to actual user, update user stats
        if($old_item['fk_i_user_id'] != $aItem['userId']) {
          if($old_item['fk_i_user_id'] > 0) {
            User::newInstance()->decreaseNumItems($old_item['fk_i_user_id']);
          }
          if($aItem['userId'] > 0) {
            User::newInstance()->increaseNumItems($aItem['userId']);
          }
        }
        // Update category numbers
        if($old_item['fk_i_category_id'] != $aItem['catId']) {
          CategoryStats::newInstance()->increaseNumItems($aItem['catId']);
          CategoryStats::newInstance()->decreaseNumItems($old_item['fk_i_category_id']);
        }
        // Update location stats
        if($old_item_location['fk_c_country_code'] != $location['fk_c_country_code']) {
          CountryStats::newInstance()->decreaseNumItems($old_item_location['fk_c_country_code']);
          CountryStats::newInstance()->increaseNumItems($location['fk_c_country_code']);
        }
        if($old_item_location['fk_i_region_id'] != $location['fk_i_region_id']) {
          RegionStats::newInstance()->decreaseNumItems($old_item_location['fk_i_region_id']);
          RegionStats::newInstance()->increaseNumItems($location['fk_i_region_id']);
        }
        if($old_item_location['fk_i_city_id'] != $location['fk_i_city_id']) {
          CityStats::newInstance()->decreaseNumItems($old_item_location['fk_i_city_id']);
          CityStats::newInstance()->increaseNumItems($location['fk_i_city_id']);
        }
      }
      // if old and new items are expired [nothing to do]
      // if($oldIsExpired && $newIsExpired) { }
    }
  }


  /**
   * Activates an item.
   * Set b_active value to 1, for a given item id
   *
   * @param int       $id
   *
   * @param string | null $secret
   *
   * @return bool
   * @throws \Exception
   */
  public function activate($id, $secret = null) {
    if($secret === null) {
      $item[0] = $this->manager->findByPrimaryKey($id);
      $aWhere = array('pk_i_id' => $id);
    } else {
      $item = $this->manager->listWhere('i.s_secret = %s AND i.pk_i_id = %d ', $secret, (int)$id);
      $aWhere = array('s_secret' => $secret, 'pk_i_id' => $id);
    }

    if(isset($item[0]['b_enabled'], $item[0]['b_active']) && $item[0]['b_enabled'] == 1 && $item[0]['b_active'] == 0) {
      $result = $this->manager->update(array('b_active' => 1), $aWhere);

      // updated correctly
      if($result == 1) {
        osc_run_hook('activate_item', $id);
        // b_enabled == 1 && b_active == 1
        if($item[0]['b_spam'] == 0 && !osc_isExpired($item[0]['dt_expiration'])) {
          $this->_increaseStats($item[0]);
        }

        return true;
      }

      return false;
    }

    return -1;
  }
  
  

  /**
   * Renew an item.
   * Update dt_expiration value for a given item id
   *
   * @param int       $id
   *
   * @param string | null $secret
   *
   * @return bool
   * @throws \Exception
   */
  public function renew($id, $secret = null) {
    if($secret === null) {
      $item[0] = $this->manager->findByPrimaryKey($id);
      $aWhere = array('pk_i_id' => $id);
    } else {
      $item = $this->manager->listWhere('i.s_secret = %s AND i.pk_i_id = %d ', $secret, (int)$id);
      $aWhere = array('s_secret' => $secret, 'pk_i_id' => $id);
    }

    $_category = osc_get_category_row($item[0]['fk_i_category_id']);

    if(isset($item[0]['b_enabled']) && $item[0]['b_enabled'] == 1) {
      if(osc_isExpired($item[0]['dt_expiration'])) {
        if((osc_renewal_limit() > 0 && (int)$item[0]['i_renewed'] < osc_renewal_limit()) || osc_renewal_limit() <= 0) {
          if(isset($_category['i_expiration_days']) && (int)$_category['i_expiration_days'] > 0) {
            $xpiration_days = (int)$_category['i_expiration_days'];
          } else {
            $xpiration_days = 30;   // in case category does not have it defined, but it should never happen
          }
          
          $expire_on = date('Y-m-d H:i:s', strtotime(' + ' . $xpiration_days . ' days'));
          $renewal_count = (int)$item[0]['i_renewed'] + 1;

          if(osc_renewal_update_publish_date()) {
            $aFields = array(
              'dt_expiration' => $expire_on,
              'dt_pub_date' => date('Y-m-d H:i:s'),
              'dt_mod_date' => null,
              'i_renewed' => $renewal_count
            );
          } else {
            $aFields = array(
              'dt_expiration' => $expire_on,
              'i_renewed' => $renewal_count
            );
          }
          
          $result = $this->manager->update($aFields, $aWhere);

          
          // updated correctly
          if($result == 1) {
            osc_run_hook('renew_item', $id);
            // b_enabled == 1 && b_active == 1
            if($item[0]['b_spam'] == 0 && $item[0]['b_active'] == 1) {
              $this->_increaseStats($item[0]);
            }

            return 1;
          }
          return 2;
        }
        return 3;
      }
      return 4;
    }
    return -1;
  }
  

  /**
   * Deactivates an item
   * Set s_active value to 0, for a given item id
   *
   * @param int $id
   *
   * @return bool
   * @throws \Exception
   */
  public function deactivate_by_id($id) {
    $result = $this->manager->update(
      array('b_active' => 0),
      array('pk_i_id' => $id)
    );

    // updated correctly
    if($result == 1) {
      osc_run_hook('deactivate_item', $id);
      $item = $this->manager->findByPrimaryKey($id);
      
      if($item['b_enabled'] == 1 && $item['b_spam'] == 0 && !osc_isExpired($item['dt_expiration'])) {
        $this->_decreaseStats($item);
      }

      return true;
    }

    return false;
  }
  
  

  /**
   * Activates an item.
   * Set b_active value to 1, for a given item id
   *
   * @param int       $id
   *
   * @param string | null $secret
   *
   * @return bool
   * @throws \Exception
   */
  public function deactivate($id, $secret = null) {
    if($secret === null) {
      $item[0] = $this->manager->findByPrimaryKey($id);
      $aWhere = array('pk_i_id' => $id);
    } else {
      $item = $this->manager->listWhere('i.s_secret = %s AND i.pk_i_id = %d ', $secret, (int)$id);
      $aWhere = array('s_secret' => $secret, 'pk_i_id' => $id);
    }
    
    $result = $this->manager->update(array('b_active' => 0), $aWhere);

    // updated correctly
    if($result == 1) {
      osc_run_hook('deactivate_item', $id);
      $item = $this->manager->findByPrimaryKey($id);
      
      if($item['b_enabled'] == 1 && $item['b_spam'] == 0 && !osc_isExpired($item['dt_expiration'])) {
        $this->_decreaseStats($item);
      }
      
      return true;
    }

    return false;
  }
  

  /**
   * Private function for decrease stats.
   * tables: t_user/t_category_stats/t_country_stats/t_region_stats/t_city_stats
   *
   * @param array item
   *
   * @throws \Exception
   */
  private function _decreaseStats($item) {
    if($item['fk_i_user_id'] != null) {
      User::newInstance()->decreaseNumItems($item['fk_i_user_id']);
    }
    
    CategoryStats::newInstance()->decreaseNumItems($item['fk_i_category_id']);
    CountryStats::newInstance()->decreaseNumItems($item['fk_c_country_code']);
    RegionStats::newInstance()->decreaseNumItems($item['fk_i_region_id']);
    CityStats::newInstance()->decreaseNumItems($item['fk_i_city_id']);
    
    osc_run_hook('item_decrease_stat', $item);
  }

  /**
   * Enable an item
   * Set b_enabled value to 1, for a given item id
   *
   * @param int $id
   *
   * @return bool
   * @throws \Exception
   */
  public function enable($id) {
    $result = $this->manager->update(
      array('b_enabled' => 1),
      array('pk_i_id' => $id)
    );

    // updated correctly
    if($result == 1) {
      osc_run_hook('enable_item', $id);
      $item = $this->manager->findByPrimaryKey($id);
      if($item['b_active'] == 1 && $item['b_spam'] == 0 && !osc_isExpired($item['dt_expiration'])) {
        $this->_increaseStats($item);
      }

      return true;
    }

    return false;
  }

  /**
   * Disable an item.
   * Set b_enabled value to 0, for a given item id
   *
   * @param int $id
   *
   * @return bool
   * @throws \Exception
   */
  public function disable($id) {
    $result = $this->manager->update(
      array('b_enabled' => 0),
      array('pk_i_id' => $id)
    );

    // updated correctly
    if($result == 1) {
      osc_run_hook('disable_item', $id);
      $item = $this->manager->findByPrimaryKey($id);
      if($item['b_active'] == 1 && $item['b_spam'] == 0 && !osc_isExpired($item['dt_expiration'])) {
        $this->_decreaseStats($item);
      }

      return true;
    }

    return false;
  }

  /**
   * Set premium value depending on $on, for a given item id
   *
   * @param int  $id
   * @param bool $on
   *
   * @return bool
   */
  public function premium($id, $on = true) {
    $value = 0;
    if($on) {
      $value = 1;
    }

    $result = $this->manager->update(
      array('b_premium' => $value),
      array('pk_i_id' => $id)
    );
    
    // updated corretcly
    if($result == 1) {
      if($on) {
        osc_run_hook('item_premium_on', $id);
      } else {
        osc_run_hook('item_premium_off', $id);
      }

      return true;
    }

    return false;
  }

  /*
   *
   */

  /**
   * Set spam value depending on $on, for a given item id
   *
   * @param int  $id
   * @param bool $on
   *
   * @return bool
   * @throws \Exception
   */
  public function spam($id, $on = true) {
    $item = $this->manager->findByPrimaryKey($id);
    if($on) {
      $result = $this->manager->update(
        array('b_spam' => '1'),
        array('pk_i_id' => $id)
      );
    } else {
      $result = $this->manager->update(
        array('b_spam' => '0'),
        array('pk_i_id' => $id)
      );
    }

    // updated corretcly
    if($result == 1) {
      if($on) {
        osc_run_hook('item_spam_on', $id);
      } else {
        osc_run_hook('item_spam_off', $id);
      }

      if($item['b_active'] == 1 && $item['b_enabled'] == 1 && $item['b_spam'] == 0 && !osc_isExpired($item['dt_expiration'])) {
        $this->_decreaseStats($item);
        
      } elseif($item['b_active'] == 1 && $item['b_enabled'] == 1 && $item['b_spam'] == 1 && !osc_isExpired($item['dt_expiration'])) {
        $this->_increaseStats($item);
      }

      return true;
    }

    return false;
  }

  /**
   * Delete an item, given s_secret and item id.
   *
   * @param string $secret
   * @param int  $itemId
   *
   * @return bool
   * @throws \Exception
   */
  public function delete($secret, $itemId) {
    $item = $this->manager->findByPrimaryKey($itemId);

    // osc_run_hook('before_delete_item', $itemId);     // is in Item model directly

    if($item['s_secret'] == $secret) {
      Log::newInstance()->insertLog(
        'item',
        'delete',
        $itemId,
        $item['s_title'],
        $this->is_admin ? 'admin' : 'user',
        $this->is_admin ? osc_logged_admin_id() : osc_logged_user_id()
      );
      
      $result = $this->manager->deleteByPrimaryKey($itemId);
      
      if($result !== false) {
        osc_run_hook('after_delete_item', $itemId, $item);
      }

      return $result;
    }

    return false;
  }

  /**
   * Mark an item
   *
   * @param int  $id
   * @param string $as
   */
  public function mark($id, $as) {
    switch ($as) {
      case 'spam':
        $column = 'i_num_spam';
        break;
      case 'badcat':
        $column = 'i_num_bad_classified';
        break;
      case 'offensive':
        $column = 'i_num_offensive';
        break;
      case 'repeated':
        $column = 'i_num_repeated';
        break;
      case 'expired':
        $column = 'i_num_expired';
        break;
    }

    if(isset($column)) {
      ItemStats::newInstance()->increase($column, $id);
    }
  }

  /**
   * Send listed item details to friend
   *
   * @return bool
   * @throws \Exception
   */
  public function send_friend() {
    // get data for this function
    $aItem = $this->prepareDataForFunction('send_friend');

    $item = $aItem['item'];
    
    $item = osc_apply_filter('before_send_friend', $item);
    
    View::newInstance()->_exportVariableToView('item', $item);

    osc_run_hook('hook_email_send_friend', $aItem);
    
    $item_url = osc_item_url();
    $item_url = '<a href="' . $item_url . '" >' . $item_url . '</a>';
    Params::setParam('item_url', $item_url);
    osc_add_flash_ok_message(sprintf(_m('We just sent your message to %s'), $aItem['friendName']));

    return true;
  }

  /**
   * Return an array with all data necessary for do the action
   *
   * @param string $action
   *
   * @return array
   * @throws \Exception
   */
  private function prepareDataForFunction($action) {
    $aItem = array();

    switch ($action) {
      case 'send_friend':
        // $item = $this->manager->findByPrimaryKey(Params::getParam('id'));
        $item = osc_get_item_row(Params::getParam('id'));
        
        if($item === false || !is_array($item) || count($item) == 0) {
          break;
        }

        $aItem['item'] = $item;
        View::newInstance()->_exportVariableToView('item', $aItem['item']);
        $aItem['yourName'] = Params::getParam('yourName');
        $aItem['yourEmail'] = Params::getParam('yourEmail');

        $aItem['friendName'] = Params::getParam('friendName');
        $aItem['friendEmail'] = Params::getParam('friendEmail');

        $aItem['s_title'] = $item['s_title'];
        $aItem['message'] = Params::getParam('message');
        break;
        
      case 'contact':
        // $item = $this->manager->findByPrimaryKey(Params::getParam('id'));
        $item = osc_get_item_row(Params::getParam('id'));
        
        if($item === false || !is_array($item) || count($item) == 0) {
          break;
        }

        $aItem['item'] = $item;
        View::newInstance()->_exportVariableToView('item', $aItem['item']);
        $aItem['id'] = Params::getParam('id');
        $aItem['yourEmail'] = Params::getParam('yourEmail');
        $aItem['yourName'] = Params::getParam('yourName');
        $aItem['message'] = Params::getParam('message');
        $aItem['phoneNumber'] = Params::getParam('phoneNumber');
        break;
        
      case 'add_comment':
        // $item = $this->manager->findByPrimaryKey(Params::getParam('id'));
        $item = osc_get_item_row(Params::getParam('id'));
        
        if($item === false || !is_array($item) || count($item) == 0) {
          break;
        }

        $aItem['item'] = $item;
        View::newInstance()->_exportVariableToView('item', $aItem['item']);
        $aItem['authorName'] = (osc_is_web_user_logged_in() ? osc_logged_user_name() : Params::getParam('authorName'));      // os831 name & email of logged user cannot be changed
        $aItem['authorEmail'] = (osc_is_web_user_logged_in() ? osc_logged_user_email() : Params::getParam('authorEmail'));
        $aItem['body'] = Params::getParam('body');
        $aItem['title'] = Params::getParam('title');
        $aItem['rating'] = Params::getParam('rating');
        $aItem['replyId'] = (Params::getParam('replyId') > 0 ? Params::getParam('replyId') : NULL);
        $aItem['reply'] = ($aItem['replyId'] > 0 ? ItemComment::newInstance()->findByPrimaryKey($aItem['replyId']) : array());
        $aItem['id'] = Params::getParam('id');
        $aItem['userId'] = Session::newInstance()->_get('userId');
        
        if($aItem['userId'] == '') {
          $aItem['userId'] = null;
        }

        break;
        
      default:
    }

    return $aItem;
  }

  /**
   * @return string | void
   * @throws \Exception
   */
  /**
   * @return string | void
   * @throws \Exception
   */
  public function contact() {
    $aItem = $this->prepareDataForFunction('contact');
    $aItem = osc_apply_filter('before_validate_contact', $aItem);
    
    $flash_error = '';
    // check parameters
    if(!osc_validate_text($aItem['yourName'])) {
      $flash_error = __('Your name: this field is required') . PHP_EOL;
    }
    if(!osc_validate_email($aItem['yourEmail'])) {
      $flash_error .= __('Invalid email address') . PHP_EOL;
    }
    if(!osc_validate_text($aItem['message'])) {
      $flash_error .= __('Message: this field is required') . PHP_EOL;
    }


    if(!empty($flash_error)) {
      return $flash_error;
    }

    $aItem = osc_apply_filter('before_contact', $aItem);

    osc_run_hook('hook_email_item_inquiry', $aItem);
  }

  /**
   * @return int
   * @throws \Exception
   */
  /**
   * @return int
   * @throws \Exception
   */
  public function add_comment() {
    if(!osc_comments_enabled()) {
      return 7;
    }

    $aItem = $this->prepareDataForFunction('add_comment');

    $authorName = trim(strip_tags((string)$aItem['authorName']));
    $authorEmail = trim(strip_tags((string)$aItem['authorEmail']));
    $body = trim(strip_tags((string)$aItem['body']));
    $rating = (int)trim(strip_tags($aItem['rating'] > 0 ? $aItem['rating'] : 0));
    $replyId = (int)trim(strip_tags($aItem['replyId'] > 0 ? $aItem['replyId'] : 0));
    $reply = $aItem['reply'];
    $title = trim(strip_tags((string)$aItem['title']));
    $itemId = $aItem['id'];
    $userId = $aItem['userId'];

    $item = $this->manager->findByPrimaryKey($itemId);
    $mComments = ItemComment::newInstance();

    if($rating > 5) {
      $rating = 5;
    } else if($rating <= 0) {
      $rating = null;
    }
    
    if(osc_comment_rating_limit() > 0 && $mComments->countItemUserRatings($itemId, $userId, $authorEmail) >= osc_comment_rating_limit()) {
      if($rating != null) {
        osc_add_flash_info_message(sprintf(_m('Your rating has been removed as you already rated this listing %d time(s)'), osc_comment_rating_limit()));
      }
      
      $rating = null;
    }

    if($replyId > 0) {
      // $reply = $mComments->findByPrimaryKey($replyId);

      if(!osc_enable_comment_reply()) {
        return 11;
      } else if(osc_comment_reply_user_type() == 'LOGGED' && $userId <= 0) {
        return 12;
      } else if(osc_comment_reply_user_type() == 'OWNER' && $userId != $item['fk_i_user_id'] && $authorEmail != $item['s_contact_email']) {
        return 13;
      } else if(osc_comment_reply_user_type() == 'ADMIN' && !osc_is_admin_user_logged_in()) {
        return 14;
      } 
     
      if($reply === false || !isset($reply['pk_i_id'])) {
        return 8;
      } else if(isset($reply['fk_i_reply_id']) && ($reply['fk_i_reply_id'] !== null || $reply['fk_i_reply_id'] > 0)) {
        return 9;
      } else if(isset($reply['fk_i_item_id']) && $reply['fk_i_item_id'] != $itemId) {
        return 10;
      }
      
      if(!osc_enable_comment_reply_rating()) {
        $rating = null;
      }
    } else {
      $replyId = null; 
    }

    $banned = osc_is_banned(trim(strip_tags((string)$aItem['authorEmail'])));
    if($banned === 1 || $banned === 2) {
      Session::newInstance()->_setForm('commentAuthorName', $authorName);
      Session::newInstance()->_setForm('commentTitle', $title);
      Session::newInstance()->_setForm('commentRating', $rating);
      Session::newInstance()->_setForm('commentReplyId', $replyId);
      Session::newInstance()->_setForm('commentBody', $body);
      Session::newInstance()->_setForm('commentAuthorEmail', $authorEmail);

      return 5;
    }

    View::newInstance()->_exportVariableToView('item', $item);
    $itemURL = osc_item_url();
    $itemURL = '<a href="' . $itemURL . '" >' . $itemURL . '</a>';

    Params::setParam('itemURL', $itemURL);

    if(osc_reg_user_post_comments() && !osc_is_web_user_logged_in()) {
      Session::newInstance()->_setForm('commentAuthorName', $authorName);
      Session::newInstance()->_setForm('commentTitle', $title);
      Session::newInstance()->_setForm('commentRating', $rating);
      Session::newInstance()->_setForm('commentReplyId', $replyId);
      Session::newInstance()->_setForm('commentBody', $body);

      return 6;
    }

    if(!preg_match('|^.*?@.{2,}\..{2,3}$|', $authorEmail)) {
      Session::newInstance()->_setForm('commentAuthorName', $authorName);
      Session::newInstance()->_setForm('commentTitle', $title);
      Session::newInstance()->_setForm('commentRating', $rating);
      Session::newInstance()->_setForm('commentReplyId', $replyId);
      Session::newInstance()->_setForm('commentBody', $body);

      return 3;
    }

    if($body == '') {
      Session::newInstance()->_setForm('commentAuthorName', $authorName);
      Session::newInstance()->_setForm('commentAuthorEmail', $authorEmail);
      Session::newInstance()->_setForm('commentTitle', $title);
      Session::newInstance()->_setForm('commentRating', $rating);
      Session::newInstance()->_setForm('commentReplyId', $replyId);

      return 4;
    }

    $num_moderate_comments = osc_moderate_comments();
    
    if($userId == null) {
      $num_comments = 0;
    } else {
      $user = osc_get_user_row($userId);
      $num_comments = $user['i_comments'];
    }

    if($num_moderate_comments == -1 || ($num_moderate_comments != 0 && $num_comments >= $num_moderate_comments)) {
      $status = 'ACTIVE';
      $status_num = 2;
    } else {
      $status = 'INACTIVE';
      $status_num = 1;
    }

    if(osc_akismet_key()) {
      require_once LIB_PATH . 'Akismet.class.php';
      $akismet = new Akismet(osc_base_url(), osc_akismet_key());
      $akismet->setCommentAuthor($authorName);
      $akismet->setCommentAuthorEmail($authorEmail);
      $akismet->setCommentContent($body);
      $akismet->setPermalink($itemURL);

      $status = $akismet->isCommentSpam() ? 'SPAM' : $status;
      
      if($status == 'SPAM') {
        $status_num = 5;
      }
    }

    $aComment = array(
      'dt_pub_date' => date('Y-m-d H:i:s'),
      'fk_i_item_id' => $itemId,
      's_author_name' => $authorName,
      's_author_email' => $authorEmail,
      's_title' => $title,
      'i_rating' => $rating,
      's_body' => $body,
      'b_active' => $status === 'ACTIVE' ? 1 : 0,
      'b_enabled' => 1,
      'fk_i_user_id' => $userId,
      'fk_i_reply_id' => ($replyId > 0 ? $replyId : NULL)
    );

    osc_run_hook('before_add_comment', $aComment);

    $aComment = osc_apply_filter('comment_insert_data', $aComment);
    
    if($mComments->insert($aComment)) {
      $commentID = $mComments->dao->insertedId();
      
      if($status_num == 2 && $userId != null) { // COMMENT IS ACTIVE
        $user = osc_get_user_row($userId);
        
        if($user) {
          User::newInstance()->update(array('i_comments' => $user['i_comments'] + 1), array('pk_i_id' => $user['pk_i_id']));
        }
        
        // Notify user - owner of listing (only if comment is active)
        if(osc_notify_new_comment_user() && (!osc_enable_comment_reply() || $replyId <= 0)) {
          osc_run_hook('hook_email_new_comment_user', $aItem);
          
        } else if(osc_notify_new_comment_reply_user() && osc_enable_comment_reply() && $replyId > 0) { 
          osc_run_hook('hook_email_new_comment_reply_user', $aItem);
        }
      }

      // Notify admin
      if(osc_notify_new_comment_user() && (!osc_enable_comment_reply() || $replyId <= 0)) {
        osc_run_hook('hook_email_new_comment_admin', $aItem);
      } else if(osc_notify_new_comment_reply() && osc_enable_comment_reply() && $replyId > 0) { 
        osc_run_hook('hook_email_new_comment_reply_admin', $aItem);

      }

      osc_run_hook('add_comment', $commentID);

      return $status_num;
    }

    return -1;
  }

  /**
   * Return an array with all data necessary for do the action (ADD OR EDIT)
   *
   * @param bool $is_add
   *
   * @return void
   */
  public function prepareData($is_add) {
    $aItem = array();
    $data = array();

    $userId = null;
    if($this->is_admin) {
      // user
      $data = User::newInstance()->findByEmail(Params::getParam('contactEmail'));
      if(isset($data['pk_i_id']) && $data['pk_i_id'] > 0) {
        $userId = $data['pk_i_id'];
      }
    } else {
      $userId = Session::newInstance()->_get('userId');
      
      if($userId == '') {
        $userId = null;
        
      } elseif($userId != null) {
        $data = osc_get_user_row($userId);
        
        if(isset($data['pk_i_id']) && $data['pk_i_id'] > 0) {
          $userId = $data['pk_i_id'];
        } else {
          $userId = null;
        }
      }
    }

    if($userId != null) {
      $aItem['contactName'] = $data['s_name'];
      $aItem['contactEmail'] = $data['s_email'];
      Params::setParam('contactName', $data['s_name']);
      Params::setParam('contactEmail', $data['s_email']);
    } else {
      $aItem['contactName'] = Params::getParam('contactName');
      $aItem['contactEmail'] = Params::getParam('contactEmail');
    }
    $aItem['userId'] = $userId;

    if($is_add) {   // ADD
      if($this->is_admin) {
        $active = 'ACTIVE';
      } elseif(osc_moderate_items() > 0) { // HAS TO VALIDATE
        if(!osc_is_web_user_logged_in()) { // NO USER IS LOGGED, VALIDATE
          $active = 'INACTIVE';
        } else { // USER IS LOGGED
          if(osc_logged_user_item_validation()) { //USER IS LOGGED, BUT NO NEED TO VALIDATE
            $active = 'ACTIVE';
          } else { // USER IS LOGGED, NEED TO VALIDATE, CHECK NUMBER OF PREVIOUS ITEMS
            $user = osc_get_user_row(osc_logged_user_id());
            
            if($user['i_items'] < osc_moderate_items()) {
              $active = 'INACTIVE';
            } else {
              $active = 'ACTIVE';
            }
          }
        }
      } elseif(osc_moderate_items() == 0) {
        if(osc_is_web_user_logged_in() && osc_logged_user_item_validation()) {
          $active = 'ACTIVE';
        } else {
          $active = 'INACTIVE';
        }
      } else {
        $active = 'ACTIVE';
      }
      $aItem['active'] = $active;
    } else {      // EDIT
      $aItem['secret'] = Params::getParam('secret');
      $aItem['idItem'] = Params::getParam('id');
    }

    // get params
    $aItem['catId'] = Params::getParam('catId');
    $aItem['countryId'] = Params::getParam('countryId');
    $aItem['country'] = Params::getParam('country');
    $aItem['region'] = Params::getParam('region');
    $aItem['regionId'] = Params::getParam('regionId');
    $aItem['city'] = Params::getParam('city');
    $aItem['cityId'] = Params::getParam('cityId');
    $aItem['price'] = Params::getParam('price') === '' ? null : Params::getParam('price');   // update 420, Params::getParam('price') ? : null;
    $aItem['cityArea'] = Params::getParam('cityArea');
    $aItem['address'] = Params::getParam('address');
    $aItem['currency'] = Params::getParam('currency');
    $aItem['contactPhone'] = Params::getParam('contactPhone');
    $aItem['contactOther'] = Params::getParam('contactOther');
    $aItem['showEmail'] = Params::getParam('showEmail') ? 1 : 0;
    $aItem['showPhone'] = Params::getParam('showPhone') ? 1 : 0;
    $aItem['title'] = Params::getParam('title');
    $aItem['description'] = (osc_tinymce_items_enabled() == '1' ? Params::getParam('description', false, false) : Params::getParam('description'));
    $aItem['photos'] = Params::getFiles('photos');
    $aItem['s_ip'] = osc_get_ip();
    $aItem['d_coord_lat'] = (Params::getParam('d_coord_lat') <> '' ? Params::getParam('d_coord_lat') : (Params::getParam('latitude') <> '' ? Params::getParam('latitude') : null));
    $aItem['d_coord_long'] = (Params::getParam('d_coord_long')  <> '' ? Params::getParam('d_coord_long') : (Params::getParam('longitude') <> '' ? Params::getParam('longitude') : null));
    $aItem['s_zip'] = Params::getParam('zip') ?: null;

    $ajax_photos = Params::getParam('ajax_photos');
    $ajax_order_list = Params::getParam('ajax_order_list');
    
    $aItem['ajax_photos'] = $ajax_photos;
    $aItem['ajax_order_list'] = $ajax_order_list;
    

    // $ajax_photos is an array of filenames of the photos uploaded by ajax to a temporary folder
    // fake insert them into the array of the form-uploaded photos
    $ajax_order_list = explode(',', $ajax_order_list);
    
    if(is_array($ajax_photos) && !empty($ajax_photos)) {
      foreach($ajax_photos as $photo) {
        if(trim((string)$photo) != '' && file_exists(osc_content_path() . 'uploads/temp/' . $photo)) {
          $order = 0;
          
          if(osc_image_upload_reorder()) {
            if(is_array($ajax_order_list) && $photo != '') {
              $order = array_search($photo, $ajax_order_list, true);
              
              if($order === false) {      // not found
                $order = 999;
              } else {
                $order = $order + 1;
              }
            } else {
              $order = 999;
            }
          }

          $aItem['photos']['name'][] = $photo;
          $aItem['photos']['order'][] = (int)$order;
          $aItem['photos']['type'][] = 'image/*';
          $aItem['photos']['tmp_name'][] = osc_content_path() . 'uploads/temp/' . $photo;
          $aItem['photos']['error'][] = UPLOAD_ERR_OK;
          $aItem['photos']['size'][] = 0;
        }
      }
    }

    // Manage publish date
    if($this->is_admin) {
      $dt_pub_date = Params::getParam('dt_pub_date');

      if($dt_pub_date != '') {
        if(preg_match('|^([0-9]{4})-([0-9]{2})-([0-9]{2})$|', $dt_pub_date, $match)) {
          $aItem['dt_pub_date'] = $dt_pub_date . ' 00:00:00';
        }

        if(preg_match('|^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$|', $dt_pub_date, $match)) {
          $aItem['dt_pub_date'] = $dt_pub_date;
        }
      }
      
      if($is_add && !isset($aItem['dt_pub_date'])) {
        $aItem['dt_pub_date'] = date('Y-m-d H:i:s');
      }
    }


    // Manage expiration date
    if($is_add || $this->is_admin) {
      $category = osc_get_category_row($aItem['catId']);
      $category_expiration_days = (isset($category['i_expiration_days']) && $category['i_expiration_days'] > 0) ? $category['i_expiration_days'] : null;
      $dt_expiration = osc_esc_html(Params::getParam('dt_expiration'));

      // Non-expiring listing
      if($dt_expiration == -1 && $this->is_admin) {
        $aItem['dt_expiration'] = '';
        
      // Expiration set as number of days
      } else if(
        $dt_expiration != ''
        && (
          ctype_digit($dt_expiration)
          || preg_match('|^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$|', $dt_expiration, $match)
          || preg_match('|^([0-9]{4})-([0-9]{2})-([0-9]{2})$|', $dt_expiration, $match)
        )
      ) {
        $aItem['dt_expiration'] = $dt_expiration;

        if(ctype_digit($dt_expiration)) {
          // Expiration defined by user is beyond category expiration days
          if(!$this->is_admin && $dt_expiration > $category_expiration_days && $category_expiration_days > 0) {
            $aItem['dt_expiration'] = $category_expiration_days;
          }
          
        } else {
          if(preg_match('|^([0-9]{4})-([0-9]{2})-([0-9]{2})$|', $dt_expiration, $match)) {
            $aItem['dt_expiration'] .= ' 23:59:59';
          }
          
          // Expiration defined by user is beyond category expiration date
          if(!$this->is_admin && strtotime($dt_expiration) > (time() + $category_expiration_days * 24 * 3600) && $category_expiration_days > 0) {
            $aItem['dt_expiration'] = $category_expiration_days;
          }
        }
        
      } else {
        $aItem['dt_expiration'] = ($category_expiration_days > 0 ? $category_expiration_days : '');
        
      }
      
      unset($dt_expiration);
      
    } else {
      $aItem['dt_expiration'] = '';
    }


    // Check params
    $country = osc_get_country_row($aItem['countryId']);
    
    if(isset($country['pk_c_code'])) {
      $countryId = $country['pk_c_code'];
      $countryName = $country['s_name'];
      $countryNameNative = $country['s_name_native'];
      
    } else {
      $countryId = null;
      $countryName = $aItem['country'];
      $countryNameNative = '';
    }
    
    $aItem['countryId'] = $countryId;
    $aItem['countryName'] = $countryName;
    $aItem['countryNameNative'] = $countryNameNative;

    if($aItem['regionId'] > 0) {
      if((int)$aItem['regionId']) {
        $region = osc_get_region_row($aItem['regionId']);
        
        if(isset($region['pk_i_id'])) {
          $regionId = $region['pk_i_id'];
          $regionName = $region['s_name'];
          $regionNameNative = $region['s_name_native'];
        }
      }
      
    } else {
      $regionId = null;
      $regionName = $aItem['region'];
      
      if($aItem['countryId'] != '') {
        $auxRegion = Region::newInstance()->findByName($aItem['region'], $aItem['countryId']);
        
        if(isset($auxRegion['pk_i_id'])) {
          $regionId = $auxRegion['pk_i_id'];
          $regionName = $auxRegion['s_name'];
          $regionNameNative = $auxRegion['s_name_native'];
        }
      }
    }

    if(isset($regionId)) {
      $aItem['regionId'] = $regionId;
    } else {
      $aItem['regionId'] = null;
    }
    
    if(isset($regionName)) {
      $aItem['regionName'] = $regionName;
    }

    if(isset($regionNameNative)) {
      $aItem['regionNameNative'] = $regionNameNative;
    }

    if($aItem['cityId'] > 0) {
      if((int)$aItem['cityId']) {
        $city = osc_get_city_row($aItem['cityId']);
        
        if(isset($city['pk_i_id'])) {
          $cityId = $city['pk_i_id'];
          $cityName = $city['s_name'];
          $cityNameNative = $city['s_name_native'];
          $cityLat = $city['d_coord_lat'];
          $cityLong = $city['d_coord_long'];
        }
      }
    } else {
      $cityId = null;
      $cityName = $aItem['city'];
      
      if($aItem['countryId'] != '') {
        $auxCity = City::newInstance()->findByName($aItem['city'], $aItem['regionId']);
        
        if(isset($auxCity['pk_i_id'])) {
          $cityId = $auxCity['pk_i_id'];
          $cityName = $auxCity['s_name'];
          $cityNameNative = $auxCity['s_name_native'];
          $cityLat = $auxCity['d_coord_lat'];
          $cityLong = $auxCity['d_coord_long'];
        }
      }
    }

    if(isset($cityId)) {
      $aItem['cityId'] = $cityId;
    } else {
      $aItem['cityId'] = null;
    }
    
    if(isset($cityName)) {
      $aItem['cityName'] = $cityName;
    }

    if(isset($cityNameNative)) {
      $aItem['cityNameNative'] = $cityNameNative;
    }

    if(isset($cityLat)) {
      $aItem['cityLat'] = $cityLat;
    }

    if(isset($cityLong)) {
      $aItem['cityLong'] = $cityLong;
    }

    if($aItem['cityArea'] == '') {
      $aItem['cityArea'] = null;
    }

    if($aItem['address'] == '') {
      $aItem['address'] = null;
    }

    if($aItem['price'] !== null) {
      $price = str_replace(array(osc_locale_thousands_sep(), osc_locale_dec_point()), array('', '.'), trim((string)$aItem['price']));
      
      // update 440
      // $aItem['price'] = $price * 1000000; 
      $comma_split = explode(',', (string)$price);
      if(is_array($comma_split) && count($comma_split) <= 2) {  // only one comma
        $price = str_replace(',', '.', $price);  // comma to decimal point
      } else {
        $price = str_replace(',', '', $price);   // remove comma
      }
      
      $dot_split = explode('.', (string)$price);
      if(is_array($dot_split) && count($dot_split) > 2) {  // more than one dot
        $decimal_part = end($dot_split);
        $integer_part = implode('', array_slice($dot_split, 0, -1));
        
        $price = $integer_part . '.' . $decimal_part;
      }
      
      $price = str_replace(' ', '', $price);     // remove white spaces inside price

      if(is_numeric($price)) {
        $aItem['price'] = (float)$price * 1000000;
      } else {
        $aItem['price'] = $price;   // will throw error on number validation
      }
    }

    if($aItem['catId'] == '') {
      $aItem['catId'] = 0;
    }

    if($aItem['currency'] == '') {
      $aItem['currency'] = null;
    }

    $aItem = osc_apply_filter('item_prepare_data', $aItem);
    $this->data = $aItem;
  }
}

if(osc_force_jpeg()) {
  /**
   * @param $content
   *
   * @return string
   */
  /**
   * @param $content
   *
   * @return string
   */
  function osc_force_jpeg_extension($content) {
    return 'jpg';
  }


  /**
   * @param $content
   *
   * @return string
   */
  /**
   * @param $content
   *
   * @return string
   */
  function osc_force_jpeg_mime($content) {
    return 'image/jpeg';
  }


  osc_add_filter('upload_image_extension', 'osc_force_jpeg_extension');
  osc_add_filter('upload_image_mime', 'osc_force_jpeg_mime');
}