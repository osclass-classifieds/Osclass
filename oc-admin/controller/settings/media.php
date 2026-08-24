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
        $use_imagick_before = osc_use_imagick();
        $type_watermark = Params::getParam('watermark_type');
        $watermark_color = Params::getParam('watermark_text_color');
        $watermark_text = Params::getParam('watermark_text');
        $canvas_background = Params::getParam('canvas_background');
        $allowedExt = Params::getParam('allowedExt');
        $media_regen_batch = Params::getParam('media_regen_batch');
        $media_refresh_batch = Params::getParam('media_refresh_batch');

        switch($type_watermark) {
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
        $media_regen_batch = (int)trim(strip_tags($media_regen_batch));
        $media_refresh_batch = (int)trim(strip_tags($media_refresh_batch));

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

        if($media_regen_batch <= 0) {
          if($status != 'error') {
            $status = 'warning';
          }

          $media_regen_batch = 10;
          $error .= _m('Regenerate batch must be greater than 0. Default value 10 has been applied.') . "<br />";
        }

        if($media_refresh_batch <= 0) {
          if($status != 'error') {
            $status = 'warning';
          }

          $media_refresh_batch = 10;
          $error .= _m('Refresh batch must be greater than 0. Default value 10 has been applied.') . "<br />";
        }

        // Save active image library first, then normalize extensions against selected library capabilities.
        $iUpdated += osc_set_preference('use_imagick', $use_imagick);

        $allowedExtRequested = osc_parse_allowed_image_extensions($allowedExt);
        $allowedExtPrepared = osc_prepare_allowed_image_extensions($allowedExt);
        $allowedExtParsed = osc_parse_allowed_image_extensions($allowedExtPrepared);

        if(trim((string)$allowedExtPrepared) == '' || count($allowedExtParsed) <= 0) {
          $status = 'error';
          $error .= _m('At least one image extension must be selected') . "<br />";
          $allowedExtPrepared = osc_prepare_allowed_image_extensions(osc_allowed_extension_preference());

          if(trim((string)$allowedExtPrepared) == '') {
            $allowedExtPrepared = 'png,gif,jpg,jpeg';
          }
        }

        if($use_imagick_before != $use_imagick) {
          $removed = array_diff($allowedExtRequested, $allowedExtParsed);

          if(!empty($removed)) {
            $status = 'warning';
            $error .= sprintf(_m('Some image extensions were removed because they are not supported by currently active image library: %s'), implode(', ', $removed)) . "<br />";
          }
        }

        $iUpdated += osc_set_preference('allowedExt', $allowedExtPrepared);
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
        $iUpdated += osc_set_preference('canvas_background', $canvas_background);
        $iUpdated += osc_set_preference('media_regen_batch', $media_regen_batch, 'osclass', 'INTEGER');
        $iUpdated += osc_set_preference('media_refresh_batch', $media_refresh_batch, 'osclass', 'INTEGER');

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
        osc_csrf_check();
        osc_set_preference('media_regen_running', 0, 'osclass', 'BOOLEAN');
        osc_set_preference('media_regen_last_id', 0, 'osclass', 'INTEGER');
        osc_set_preference('media_regen_done', 0, 'osclass', 'INTEGER');
        osc_set_preference('media_regen_total', 0, 'osclass', 'INTEGER');
        osc_set_preference('media_regen_skip_refresh', 0, 'osclass', 'BOOLEAN');
        osc_set_preference('media_regen_batch_id', 0, 'osclass', 'INTEGER');
        osc_set_preference('media_regen_start_date', '', 'osclass', 'STRING');
        osc_add_flash_ok_message(__("Image processing has been cancelled"), 'admin');
        $this->redirectTo(osc_admin_base_url(true).'?page=settings&action=media#regenerate');
        break;

      case('images_post'):
        if(defined('DEMO')) {
          osc_add_flash_warning_message(_m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true).'?page=settings&action=media');
        }

        osc_csrf_check();

        if((int)osc_get_preference('media_regen_running') == 1) {
          $this->redirectTo(osc_admin_base_url(true).'?page=settings&action=media#regenerate');
          break;
        }

        $regen_action = strtolower(trim((string)Params::getParam('regenerateAction')));
        if($regen_action != 'refresh' && $regen_action != 'regenerate') {
          $regen_action = 'regenerate';
        }
        $skip_refresh = ($regen_action == 'refresh' ? 1 : 0);

        osc_set_preference('media_regen_running', 1, 'osclass', 'BOOLEAN');
        osc_set_preference('media_regen_last_id', 0, 'osclass', 'INTEGER');
        osc_set_preference('media_regen_done', 0, 'osclass', 'INTEGER');
        osc_set_preference('media_regen_total', 0, 'osclass', 'INTEGER');
        osc_set_preference('media_regen_skip_refresh', $skip_refresh, 'osclass', 'BOOLEAN');
        osc_set_preference('media_regen_batch_id', (int)time(), 'osclass', 'INTEGER');
        osc_set_preference('media_regen_start_date', date('Y-m-d H:i:s'), 'osclass', 'STRING');

        $this->redirectTo(osc_admin_base_url(true).'?page=settings&action=media#regenerate');
        break;
    }
  }

  function _sizeToKB($sSize) {
    $sSuffix = strtoupper(substr($sSize, -1));
    if(!in_array($sSuffix,array('P','T','G','M','K'))){
      return (int)$sSize;
    }

    $iValue = substr($sSize, 0, -1);

    switch($sSuffix) {
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
