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


class CAdminSettingsMedia extends AdminSecBaseModel {
  function __construct() {
    parent::__construct();
  }

  //Business Layer...
  function doModel() {
    switch($this->action) {
      case('media'):
        // calling the media view
        $max_upload = $this->_sizeToKB(ini_get('upload_max_filesize'));
        $max_post = $this->_sizeToKB(ini_get('post_max_size'));
        $memory_limit = $this->_sizeToKB(ini_get('memory_limit'));
        $upload_mb = min($max_upload, $max_post, $memory_limit);

        $this->_exportVariableToView('max_size_upload', $upload_mb);
        $this->doView('settings/media.php');
        break;
        
      case('media_post'):
        // updating the media config
        osc_csrf_check();
        $status = 'ok';
        $error = '';

        $iUpdated = 0;
        $maxSizeKb = Params::getParam('maxSizeKb');
        $dimThumbnail = strtolower(Params::getParam('dimThumbnail'));
        $dimPreview = strtolower(Params::getParam('dimPreview'));
        $dimNormal = strtolower(Params::getParam('dimNormal'));
        $imageUploadLibrary = Params::getParam('image_upload_library');
        $imageUploadReorder = Params::getParam('image_upload_reorder');
        $imageUploadLibForceReplace = Params::getParam('image_upload_lib_force_replace');
        $optimizeUploadedImages = Params::getParam('optimize_uploaded_images');
        $keepOriginalImage = Params::getParam('keep_original_image');
        $forceAspectImage = Params::getParam('force_aspect_image');
        $bestFitImage = Params::getParam('best_fit_image');
        $forceJPEG = Params::getParam('force_jpeg');
        $use_imagick = Params::getParam('use_imagick');
        $type_watermark = Params::getParam('watermark_type');
        $watermark_color = Params::getParam('watermark_text_color');
        $watermark_text = Params::getParam('watermark_text');
        $canvas_background = Params::getParam('canvas_background');
        
        switch ($type_watermark) {
          case 'none':
            $iUpdated += osc_set_preference('watermark_text_color', '');
            $iUpdated += osc_set_preference('watermark_text', '');
            $iUpdated += osc_set_preference('watermark_image', '');
          break;
          case 'text':
            $iUpdated += osc_set_preference('watermark_text_color', $watermark_color);
            $iUpdated += osc_set_preference('watermark_text', $watermark_text);
            $iUpdated += osc_set_preference('watermark_image', '');
            $iUpdated += osc_set_preference('watermark_place', Params::getParam('watermark_text_place'));
          break;
          case 'image':
            // upload image & move to path
            $watermark_file = Params::getFiles('watermark_image');
            if($watermark_file['tmp_name']!='' && $watermark_file['size']>0) {
              if($watermark_file['error'] == UPLOAD_ERR_OK) {
                if($watermark_file['type']=='image/png') {
                  $tmpName = $watermark_file['tmp_name'];
                  $path = osc_uploads_path().'/watermark.png';
                  if(move_uploaded_file($tmpName, $path)){
                    $iUpdated += osc_set_preference('watermark_image', $path);
                  } else {
                    $status = 'error';
                    $error .= _m('There was a problem uploading the watermark image')."<br />";
                  }
                } else {
                  $status = 'error';
                  $error .= _m('The watermark image has to be a .PNG file')."<br />";
                }
              } else {
                $status = 'error';
                $error .= _m('There was a problem uploading the watermark image')."<br />";
              }
            }
            $iUpdated += osc_set_preference('watermark_text_color', '');
            $iUpdated += osc_set_preference('watermark_text', '');
            $iUpdated += osc_set_preference('watermark_place', Params::getParam('watermark_image_place'));
          break;
          default:
          break;
        }

        // format parameters
        $maxSizeKb = trim(strip_tags($maxSizeKb));
        $dimThumbnail = trim(strip_tags($dimThumbnail));
        $dimPreview = trim(strip_tags($dimPreview));
        $dimNormal = trim(strip_tags($dimNormal));
        
        $imageUploadLibrary = trim(strip_tags(strtoupper($imageUploadLibrary)));
        $imageUploadReorder = ($imageUploadReorder != '' ? true : false);
        $imageUploadLibForceReplace = ($imageUploadLibForceReplace != '' ? true : false);
        $keepOriginalImage = ($keepOriginalImage != '' ? true : false);
        $forceAspectImage = ($forceAspectImage != '' ? true : false);
        $bestFitImage = ($bestFitImage != '' ? true : false);
        $forceJPEG = ($forceJPEG != '' ? true : false);
        $use_imagick = ($use_imagick != '' ? true : false);
        $canvas_background = trim(strip_tags(strtolower($canvas_background)));

        if(!preg_match('|([0-9]+)x([0-9]+)|', $dimThumbnail, $match)) {
          $dimThumbnail = is_numeric($dimThumbnail) ? $dimThumbnail."x".$dimThumbnail : "240x200";
        }
        
        if(!preg_match('|([0-9]+)x([0-9]+)|', $dimPreview, $match)) {
          $dimPreview = is_numeric($dimPreview) ? $dimPreview."x".$dimPreview : "480x360";
        }
        
        if(!preg_match('|([0-9]+)x([0-9]+)|', $dimNormal, $match)) {
          $dimNormal = is_numeric($dimNormal) ? $dimNormal."x".$dimNormal : "1024x768";
        }

        // is imagick extension loaded?
        if(!@extension_loaded('imagick')) {
          $use_imagick = false;
        }

        // max size allowed by PHP configuration?
        $max_upload = (int)(ini_get('upload_max_filesize'));
        $max_post = (int)(ini_get('post_max_size'));
        $memory_limit = (int)(ini_get('memory_limit'));
        $upload_mb = min($max_upload, $max_post, $memory_limit) * 1024;

        // set maxSizeKB equals to PHP configuration if it's bigger
        if($maxSizeKb > $upload_mb) {
          $status = 'warning';
          $maxSizeKb = $upload_mb;
          // flash message text warning
          $error   .= sprintf(_m("You cannot set a maximum file size higher than the one allowed in the PHP configuration: <b>%d KB</b>"), $upload_mb);
        }

        $iUpdated += osc_set_preference('maxSizeKb', $maxSizeKb);
        $iUpdated += osc_set_preference('dimThumbnail', $dimThumbnail);
        $iUpdated += osc_set_preference('dimPreview', $dimPreview);
        $iUpdated += osc_set_preference('dimNormal', $dimNormal);
        $iUpdated += osc_set_preference('image_upload_library', $imageUploadLibrary);
        $iUpdated += osc_set_preference('image_upload_reorder', $imageUploadReorder);
        $iUpdated += osc_set_preference('image_upload_lib_force_replace', $imageUploadLibForceReplace);
        $iUpdated += osc_set_preference('keep_original_image', $keepOriginalImage);
        $iUpdated += osc_set_preference('optimize_uploaded_images', $optimizeUploadedImages);
        $iUpdated += osc_set_preference('force_aspect_image', $forceAspectImage);
        $iUpdated += osc_set_preference('best_fit_image', $bestFitImage);
        $iUpdated += osc_set_preference('force_jpeg', $forceJPEG);
        $iUpdated += osc_set_preference('use_imagick', $use_imagick);
        $iUpdated += osc_set_preference('canvas_background', $canvas_background);

        if($error != '') {
          switch($status) {
            case('error'):
              osc_add_flash_error_message($error, 'admin');
            break;
            case('warning'):
              osc_add_flash_warning_message($error, 'admin');
            break;
            default:
              osc_add_flash_ok_message($error, 'admin');
            break;
          }
        } else {
          osc_add_flash_ok_message(_m('Media config has been updated'), 'admin');
        }

        $this->redirectTo(osc_admin_base_url(true).'?page=settings&action=media');
        break;
      
      case('images_post_reset'):
        osc_set_preference('regenerate_image_data', '');
        osc_add_flash_ok_message(__("Regenerate process has been restarted"), 'admin');
        $this->redirectTo(osc_admin_base_url(true).'?page=settings&action=media');
        break;

      case('images_post'):
        if(defined('DEMO')) {
          osc_add_flash_warning_message(_m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true).'?page=settings&action=media');
        }

        osc_csrf_check();

        $data = json_decode(osc_get_preference('regenerate_image_data'), true);

        if(isset($data['batch_id']) && $data['batch_id'] > 0) {
          $aResources = ItemResource::newInstance()->getAllResourcesFromId($data['last_id']);

        } else {
          $aResources = ItemResource::newInstance()->getAllResourcesFromId();

          $data = array(
            'batch_id' => date('z') + 1,
            'last_id' => 0,
            'image_done' => 0,
            'count_all' => ItemResource::newInstance()->countResources(),
            'skip_refresh' => (Params::getParam('skip_refresh') == 1 ? 1 : 0),
            'date' => date('Y-m-d')
          );
        }


        $max_exec_time = ini_get('max_execution_time') - 8;   // seconds
        $limit_time = time() + $max_exec_time;

        if(is_array($aResources) && count($aResources) > 0) {
          foreach($aResources as $resource) {
            // break script, it's running too long
            if(time() >= $limit_time) {
              osc_add_flash_warning_message(__('Process has been paused as it was running too long. Click on "Continue regeneration" button to finish process.'), 'admin');
              $this->redirectTo(osc_admin_base_url(true).'?page=settings&action=media');
            }

            osc_run_hook('regenerate_image', $resource);
            
            if(strpos($resource['s_content_type'], 'image')!==false) {
              if(file_exists(osc_base_path().$resource['s_path'].$resource['pk_i_id']."_original.".$resource['s_extension'])) {
                $image_tmp = osc_base_path().$resource['s_path'].$resource['pk_i_id']."_original.".$resource['s_extension'];
                $use_original = true;
              } else if(file_exists(osc_base_path().$resource['s_path'].$resource['pk_i_id'].".".$resource['s_extension'])) {
                $image_tmp = osc_base_path().$resource['s_path'].$resource['pk_i_id'].".".$resource['s_extension'];
                $use_original = false;
              } else if(file_exists(osc_base_path().$resource['s_path'].$resource['pk_i_id']."_preview.".$resource['s_extension'])) {
                $image_tmp = osc_base_path().$resource['s_path'].$resource['pk_i_id']."_preview.".$resource['s_extension'];
                $use_original = false;
              } else {
                $use_original = false;
                continue;
              }


              // Create normal size
              $path_normal = $path = osc_base_path().$resource['s_path'].$resource['pk_i_id'].'.'.$resource['s_extension'];
              $size = explode('x', osc_normal_dimensions());
              
              if($data['skip_refresh'] != 1) {
                $img = ImageProcessing::fromFile($image_tmp)->resizeTo($size[0], $size[1]);
                if($use_original) {
                  if(osc_is_watermark_text()) {
                    $img->doWatermarkText(osc_watermark_text(), osc_watermark_text_color());
                  } elseif (osc_is_watermark_image()){
                    $img->doWatermarkImage();
                  }
                }
                $img->saveToFile($path);

                // Create preview
                $path = osc_base_path().$resource['s_path'].$resource['pk_i_id'].'_preview.'.$resource['s_extension'];
                $size = explode('x', osc_preview_dimensions());
                ImageProcessing::fromFile($path_normal)->resizeTo($size[0], $size[1])->saveToFile($path);

                // Create thumbnail
                $path = osc_base_path().$resource['s_path'].$resource['pk_i_id'].'_thumbnail.'.$resource['s_extension'];
                $size = explode('x', osc_thumbnail_dimensions());
                ImageProcessing::fromFile($path_normal)->resizeTo($size[0], $size[1])->saveToFile($path);
              }
              
              osc_run_hook('regenerated_image', ItemResource::newInstance()->findByPrimaryKey($resource['pk_i_id']));

              $data['last_id'] = $resource['pk_i_id'];
              $data['image_done']++;
              osc_set_preference('regenerate_image_data', json_encode($data));
            } else {
              // not supported extension
            }

          }
        }

        osc_set_preference('regenerate_image_data', '');

        osc_add_flash_ok_message(_m('Image regeneration has succesfully completed and all images has been refreshed. Make sure to clear your browser cache to see updated images.'), 'admin');
        $this->redirectTo(osc_admin_base_url(true).'?page=settings&action=media');
        break;
    }
  }

  function _sizeToKB($sSize) {
    $sSuffix = strtoupper(substr($sSize, -1));
    if (!in_array($sSuffix,array('P','T','G','M','K'))){
      return (int)$sSize;
    }
    
    $iValue = substr($sSize, 0, -1);
    
    switch ($sSuffix) {
      case 'P':
        $iValue *= 1024;
      case 'T':
        $iValue *= 1024;
      case 'G':
        $iValue *= 1024;
      case 'M':
        $iValue *= 1024;
        break;
    }
    
    return (int)$iValue;
  }
}

// EOF: ./oc-admin/controller/settings/media.php