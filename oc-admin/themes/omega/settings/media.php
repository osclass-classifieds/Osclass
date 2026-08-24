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


osc_enqueue_script('jquery-validate');
osc_enqueue_script('colorpicker');
osc_enqueue_style('colorpicker', osc_assets_url('js/colorpicker/css/colorpicker.css'));

$maxPHPsize  = View::newInstance()->_get('max_size_upload');
$imagickLoaded = extension_loaded('imagick');
$aGD = function_exists('gd_info') ? gd_info() : array();
$freeType = array_key_exists('FreeType Support', $aGD);
$aAvailableExtensions = osc_available_image_upload_extensions();
$aAllowedSelected = osc_parse_allowed_image_extensions(osc_allowed_extension_preference());
$media_regen_running = (int)osc_get_preference('media_regen_running');
$media_regen_done = (int)osc_get_preference('media_regen_done');
$media_regen_total = (int)osc_get_preference('media_regen_total');
$media_regen_skip_refresh = (int)osc_get_preference('media_regen_skip_refresh');
$media_regen_batch_id = (int)osc_get_preference('media_regen_batch_id');
$media_regen_start_date = (string)osc_get_preference('media_regen_start_date');
$media_regen_last_recalc = (int)osc_get_preference('media_regen_last_recalc');
$media_regen_batch = (int)osc_get_preference('media_regen_batch');
if($media_regen_batch <= 0) {
  $media_regen_batch = 10;
}
$media_refresh_batch = (int)osc_get_preference('media_refresh_batch');
if($media_refresh_batch <= 0) {
  $media_refresh_batch = 10;
}

//customize Head
function customHead() {
  $media_regen_running = (int)osc_get_preference('media_regen_running');
  $media_regen_skip_refresh = (int)osc_get_preference('media_regen_skip_refresh');
  $media_regen_batch_id = (int)osc_get_preference('media_regen_batch_id');
  ?>
  <link rel="stylesheet" media="screen" type="text/css" href="<?php echo osc_assets_url('js/colorpicker/css/colorpicker.css'); ?>" />
  <style>
    #general-settings .stats-last-recalc {margin:0 0 10px 0;color:#646970;}
    #general-settings .form-horizontal .reg-img .form-controls {width:100%;max-width:100%;}
    #general-settings .regen-progress-wrap {margin:10px 0 15px 0;}
    #general-settings .regen-progress-bar {height:10px;border-radius:3px;background:#dcdcde;border:1px solid #c3c4c7;overflow:hidden;}
    #general-settings .regen-progress-val {height:100%;width:0;background:#007cba;transition:width 0.2s ease;}
    #general-settings .regen-progress-val.regen-progress-done {background:#46b450;}
    #general-settings .regen-progress-label {margin:8px 0 0 0;}
    #media_regen_percent {margin-right:10px;}
    #general-settings .flashmessage.flashmessage-info.regen-info {margin:12px 0 15px 0;}
  </style>
  <script type="text/javascript">
    $(document).ready(function(){
      // Code for form validation
      $.validator.addMethod('regexp', function(value, element, param) {
        return this.optional(element) || value.match(param);
      }, '<?php echo osc_esc_js(__('Size is not in the correct format')); ?>');

      $("form[name=media_form]").validate({
        rules: {
          dimThumbnail: {
            required: true,
            regexp: /^[0-9]+x[0-9]+$/i
          },
          dimPreview: {
            required: true,
            regexp: /^[0-9]+x[0-9]+$/i
          },
          dimNormal: {
            required: true,
            regexp: /^[0-9]+x[0-9]+$/i
          },
          maxSizeKb: {
            required: true,
            digits: true
          }
        },
        messages: {
          dimThumbnail: {
            required: '<?php echo osc_esc_js(__("Thumbnail size: this field is required")); ?>',
            regexp: '<?php echo osc_esc_js(__("Thumbnail size: is not in the correct format")); ?>'
          },
          dimPreview: {
            required: '<?php echo osc_esc_js(__("Preview size: this field is required")); ?>',
            regexp: '<?php echo osc_esc_js(__("Preview size: is not in the correct format")); ?>'
          },
          dimNormal: {
            required: '<?php echo osc_esc_js(__("Normal size: this field is required")); ?>',
            regexp: '<?php echo osc_esc_js(__("Normal size: is not in the correct format")); ?>'
          },
          maxSizeKb: {
            required: '<?php echo osc_esc_js(__("Maximum size: this field is required")); ?>',
            digits: '<?php echo osc_esc_js(__("Maximum size: this field must only contain numeric characters")); ?>'
          }
        },
        wrapper: "li",
        errorLabelContainer: "#error_list",
        invalidHandler: function(form, validator) {
          $('html,body').animate({ scrollTop: $('h1').offset().top }, { duration: 250, easing: 'swing'});
        },
        submitHandler: function(form){
          if($('select[name="allowedExt[]"] option:selected').length <= 0) {
            $('#error_list').html('<li><?php echo osc_esc_js(__("At least one image extension must be selected")); ?></li>');
            $('html,body').animate({ scrollTop: $('h1').offset().top }, { duration: 250, easing: 'swing'});
            return false;
          }

          $('button[type=submit], input[type=submit]').attr('disabled', 'disabled');
          form.submit();
        }
      });

      $('#colorpickerField').ColorPicker({
        onSubmit: function(hsb, hex, rgb, el) { },
        onChange: function (hsb, hex, rgb) {
          $('#colorpickerField').val(hex);
        }
      });

      $('#watermark_none').bind('change', function() {
        if($(this).prop('checked')) {
          $('#watermark_text_box').hide();
          $('#watermark_image_box').hide();
          resetLayout();
        }
      });

      // dialog bulk actions
      $("#dialog-watermark-warning").dialog({
        autoOpen: false,
        modal: true
      });

      $('#watermark_text').on('change', function() {
        if($(this).prop('checked')) {
          $('#watermark_text_box').show();
          $('#watermark_image_box').hide();
          if(!$('input[name="keep_original_image"]').prop('checked')) {
            $("#dialog-watermark-warning").dialog('open');
          }
          resetLayout();
        }
      });

      $('#watermark_image').on('change', function() {
        if($(this).prop('checked')) {
          $('#watermark_text_box').hide();
          $('#watermark_image_box').show();
          if(!$('input[name="keep_original_image"]').prop('checked')) {
            $("#dialog-watermark-warning").dialog('open');
          }
          resetLayout();
        }
      });

      $('input[name="keep_original_image"]').on("change",function() {
        if(!$(this).prop('checked')) {
          if(!$('#watermark_none').prop('checked')) {
            $("#dialog-watermark-warning").dialog('open');
          }
          resetLayout();
        }
      });

      function updateMediaRegenerationProgress(data) {
        var percent = parseInt(data.percent, 10);
        var pending = parseInt(data.pending, 10);
        var processed = parseInt(data.processed, 10);
        var total = parseInt(data.total, 10);

        if(isNaN(percent)) { percent = 0; }
        if(isNaN(pending)) { pending = 0; }
        if(isNaN(processed)) { processed = 0; }
        if(isNaN(total)) { total = 0; }
        if(percent < 0) { percent = 0; }
        if(percent > 100) { percent = 100; }

        $('#media_regen_percent').text(percent + '%');
        $('#media_regen_progress_val').css('width', percent + '%');

        if(percent < 100) {
          $('#media_regen_progress_val').removeClass('regen-progress-done');
        }

        $('#media_regen_progress_text').text(processed + ' / ' + total + ' processed, ' + pending + ' remaining');
      }

      function setMediaRegenerationDone(text) {
        $('#media_regen_percent').text('100%');
        $('#media_regen_progress_val').css('width', '100%').addClass('regen-progress-done');
        $('#media_regen_progress_text').text(text);
      }

      function setMediaRegenerationError(text) {
        $('#media_regen_error_text').text(text);
        $('#media_regen_error_box').show();
      }

      function setMediaRegenerationCancelled(text) {
        $('#media_regen_error_text').text(text);
        $('#media_regen_error_box').show();
      }

      function pollMediaRegeneration() {
        var pollDelay = 10000;

        $.ajax({
          type: "POST",
          url: '<?php echo osc_admin_base_url(true)?>?page=ajax&action=media_regen_recalc&batch_id=<?php echo (int)$media_regen_batch_id; ?>&<?php echo osc_csrf_token_url(); ?>',
          dataType: 'json',
          success: function(data) {
            if(typeof data !== 'object' || data === null) {
              setMediaRegenerationError('<?php echo osc_esc_js(__('Unexpected response from regeneration process.')); ?>');
              return;
            }

            if(data.status == 'error') {
              setMediaRegenerationError((data.message ? data.message : '<?php echo osc_esc_js(__('Image regeneration failed.')); ?>'));
              return;
            }

            if(data.status == 'cancelled') {
              setMediaRegenerationCancelled('<?php echo osc_esc_js(__('Current processing has been cancelled.')); ?>');
              return;
            }

            updateMediaRegenerationProgress(data);

            if(data.status == 'done') {
              if(data.skip_refresh == 1) {
                setMediaRegenerationDone('<?php echo osc_esc_js(__('Image refresh completed.')); ?>');
              } else {
                setMediaRegenerationDone('<?php echo osc_esc_js(__('Image regeneration completed.')); ?>');
              }

              setTimeout(function() {
                window.location = '<?php echo osc_admin_base_url(true) . '?page=settings&action=media#regenerate'; ?>';
              }, 900);
            } else {
              setTimeout(pollMediaRegeneration, pollDelay);
            }
          },
          error: function(xhr) {
            var responseText = (xhr && xhr.responseText ? xhr.responseText : '');
            var messageText = $('<div/>').html(responseText).text();

            if(messageText == '') {
              messageText = '<?php echo osc_esc_js(__('Image regeneration failed.')); ?>';
            }

            setMediaRegenerationError(messageText);
          }
        });
      }

      if(<?php echo $media_regen_running; ?> == 1) {
        if(<?php echo $media_regen_skip_refresh; ?> == 1) {
          $('#media_regen_progress_text').text('<?php echo osc_esc_js(__('Image refresh is running. Please wait until it reaches 100%.')); ?>');
        } else {
          $('#media_regen_progress_text').text('<?php echo osc_esc_js(__('Image regeneration is running. Please wait until it reaches 100%.')); ?>');
        }
        pollMediaRegeneration();
      }
    });
  </script>
  <?php
}

osc_add_hook('admin_header','customHead', 10);


function render_offset(){
  return 'row-offset';
}

function media_format_last_recalc($time) {
  $time = (int)$time;
  if($time <= 0) {
    return __('Never');
  }

  return date(osc_date_format() . ' ' . osc_time_format(), $time);
}

function media_format_estimated_time($seconds) {
  $seconds = (int)$seconds;

  if($seconds < 60) {
    return sprintf(__('%s seconds'), $seconds);
  }

  $minutes = floor($seconds / 60);
  $rest = $seconds % 60;

  if($rest <= 0) {
    return sprintf(__('%s minutes'), $minutes);
  }

  return sprintf(__('%s minutes %s seconds'), $minutes, $rest);
}

function addHelp() {
  echo '<p>' . __('Manage the options for the images users can upload along with their listings. You can limit their size, the number of images per ad, include a watermark, etc.') . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader(){
  ?>
  <h1><?php _e('Settings'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Media settings - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php');
?>

<!--los input tienen una class para el tamaño ...-->
<div id="general-settings">
  <h2 class="render-title"><?php _e('Media settings'); ?></h2>
  <ul id="error_list"></ul>
  <form name="media_form" action="<?php echo osc_admin_base_url(true); ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="page" value="settings" />
    <input type="hidden" name="action" value="media_post" />
    <fieldset>
      <div class="form-horizontal">
        <h2 class="render-title"><?php _e('General settings'); ?></h2>

        <div class="form-row">
          <div class="form-label"><?php _e('Image uploader library'); ?></div>
          <div class="form-controls">
            <select name="image_upload_library">
              <option value="UPPY" <?php echo (osc_image_upload_library() == 'UPPY') ? 'selected="true"' : ''; ?>><?php _e('Uppy.io'); ?></option>
              <option value="" <?php echo (osc_image_upload_library() == '') ? 'selected="true"' : ''; ?>><?php _e('FineUploader'); ?></option>
              <option value="LEGACY" <?php echo (osc_image_upload_library() == 'LEGACY') ? 'selected="true"' : ''; ?>><?php _e('Legacy (no uploader)'); ?></option>
            </select>

            <div class="help-box"><?php _e('Test image upload functionality on publish page precisely after changing this setting!'); ?></div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Force replace libraries'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <input type="checkbox" id="image_upload_lib_force_replace" name="image_upload_lib_force_replace" value="1" <?php echo (osc_image_upload_lib_force_replace() ? 'checked="checked"' : ''); ?> />
              <label for="image_upload_lib_force_replace"><?php _e('Automatically replace image uploader in theme'); ?></label>
              <span class="help-box"><?php _e('Only works with Uppy.io uploader! Enabled by default. Automatically replace fineuploader image uploader library with selected one. Disable if your theme provides native support to selected image uploader library.'); ?></span>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Reorder uploaded images'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <input type="checkbox" id="image_upload_reorder" name="image_upload_reorder" value="1" <?php echo (osc_image_upload_reorder() ? 'checked="checked"' : ''); ?> />
              <label for="image_upload_reorder"><?php _e('Uploaded images can be reordered on item publish & edit pages'); ?></label>
              <span class="help-box"><?php _e('Only works with Uppy.io uploader! Require jQuery UI (must be embedded in theme).'); ?></span>
            </div>
          </div>
        </div>


        <h2 class="render-title separate-top"><?php _e('Image sizes'); ?></h2>
        <div class="form-row">
          <p><?php _e('The sizes listed below determine the maximum dimensions in pixels to use when uploading a image. Format: <b>Width</b> x <b>Height</b>.'); ?></p>
          <div class="form-label"><?php _e('Thumbnail size'); ?></div>
          <div class="form-controls"><input type="text" class="input-medium" name="dimThumbnail" value="<?php echo osc_esc_html(osc_thumbnail_dimensions()); ?>" /></div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Preview size'); ?></div>
          <div class="form-controls"><input type="text" class="input-medium" name="dimPreview" value="<?php echo osc_esc_html(osc_preview_dimensions()); ?>" /></div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Normal size'); ?></div>
          <div class="form-controls"><input type="text" class="input-medium"  name="dimNormal" value="<?php echo osc_esc_html(osc_normal_dimensions()); ?>" /></div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Original size'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <input type="checkbox" id="keep_original_image" name="keep_original_image" value="1" <?php echo (osc_keep_original_image() ? 'checked="checked"' : ''); ?> />
              <label for="keep_original_image"><?php _e('Keep original image, unaltered after uploading.'); ?></label>
              <span class="help-box"><?php _e('Image may occupy more space than usual.'); ?></span>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Optimize uploaded images'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <input type="checkbox" id="optimize_uploaded_images" name="optimize_uploaded_images" value="1" <?php echo (osc_optimize_uploaded_images() ? 'checked="checked"' : ''); ?> />
              <label for="optimize_uploaded_images"><?php _e('Optimize images during upload.'); ?></label>
              <span class="help-box">
                <?php _e('Image will be compressed based on your media settings, quality will be optimized and size reduced. Apply to original image as well.'); ?><br/>

                <?php if(osc_uploader_max_image_size() !== false) { ?>
                  <?php echo sprintf(__('Max image size is estimated based on your normal image dimension settings and upscaled: %sx%spx'), osc_uploader_max_image_size()['w'], osc_uploader_max_image_size()['h']); ?>
                <?php } ?>
              </span>
            </div>
          </div>
        </div>


        <h2 class="render-title separate-top"><?php _e('Restrictions'); ?></h2>
        <div class="form-row">
          <div class="form-label"><?php _e('Allowed image extensions'); ?></div>
          <div class="form-controls">
            <select name="allowedExt[]" multiple="multiple" style="min-width:360px;height:170px;">
              <?php foreach($aAvailableExtensions as $ext) {
                $is_selected = in_array($ext, $aAllowedSelected, true);
                $is_supported = osc_server_supports_image_extension($ext);
                ?>
                <option value="<?php echo osc_esc_html($ext); ?>" <?php echo ($is_selected ? 'selected="selected"' : ''); ?> <?php echo (!$is_supported ? 'disabled="disabled"' : ''); ?>><?php echo osc_esc_html(strtoupper($ext)); ?><?php echo (!$is_supported ? ' - ' . osc_esc_html(__('unavailable')) : ''); ?></option>
              <?php } ?>
            </select>
            <span class="help-box"><?php _e('Hold Ctrl/Cmd to select multiple extensions. PNG, GIF, JPG and JPEG are always available. WebP, HEIF and AVIF depend on GD or ImageMagick on your server.'); ?></span>
            <?php foreach($aAvailableExtensions as $ext) {
              if(osc_server_supports_image_extension($ext)) {
                continue;
              }
              ?>
              <div class="help-box"><?php echo sprintf(__('%s is not available: %s'), strtoupper($ext), osc_esc_html(osc_server_unsupported_image_extension_reason($ext))); ?></div>
            <?php } ?>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Force JPEG'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <input type="checkbox" id="force_jpeg" name="force_jpeg" value="1" <?php echo (osc_force_jpeg() ? 'checked="checked"' : ''); ?> />
              <label for="force_jpeg"><?php _e('Force JPEG extension.'); ?></label>
              <span class="help-box"><?php _e('Uploaded images will be saved in JPG/JPEG format, it saves space but images will not have transparent background.'); ?></span>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Crop image'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <input type="checkbox" id="best_fit_image" name="best_fit_image" value="1" <?php echo (osc_best_fit_image() ? 'checked="checked"' : ''); ?> />
              <label for="best_fit_image"><?php _e('Best image crop'); ?></label>
              <span class="help-box"><?php _e('Image is cropped in best possible way from center, maximizing cropped area.'); ?> <?php _e('No white background will be added to keep the size.'); ?></span>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Force aspect'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <input type="checkbox" id="force_aspect_image" name="force_aspect_image" value="1" <?php echo (osc_force_aspect_image() ? 'checked="checked"' : ''); ?> />
              <label for="force_aspect_image"><?php _e('Force image aspect.'); ?></label>
              <span class="help-box"><?php _e('No white background will be added to keep the size.'); ?> <?php _e('No effect when "Best image crop" is enabled.'); ?></span>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Maximum size'); ?></div>
          <div class="form-controls">
            <input type="text" class="input-medium" name="maxSizeKb" value="<?php echo osc_esc_html(osc_max_size_kb()); ?>" />
            <span class="help-box"><?php _e('Size in KB'); ?></span>
            <div class="flashmessage flashmessage-warning flashmessage-inline">
              <p><?php printf(__('Maximum size PHP configuration allows: %d KB'), $maxPHPsize); ?></p>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('ImageMagick'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <input type="checkbox" name="use_imagick" value="1" <?php echo (($imagickLoaded && osc_use_imagick())?'checked="checked"':''); ?> <?php if(!$imagickLoaded) echo 'disabled="disabled"'; ?> />
              <label for="use_imagick"><?php _e('Use ImageMagick instead of GD library'); ?></label>
            </div>

            <?php if(!$imagickLoaded) { ?>
              <div class="flashmessage flashmessage-error flashmessage-inline">
                <p><?php _e('ImageMagick library is not loaded'); ?></p>
              </div>
            <?php } ?>

            <div class="help-box"><?php _e("It's faster and consumes less resources than GD library."); ?></div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Canvas background'); ?></div>
          <div class="form-controls">
            <select name="canvas_background">
              <option value="white" <?php echo (osc_canvas_background() == 'white') ? 'selected="true"' : ''; ?>><?php _e('White'); ?></option>
              <option value="black" <?php echo (osc_canvas_background() == 'black') ? 'selected="true"' : ''; ?>><?php _e('Black'); ?></option>
            </select>

            <div class="help-box"><?php _e('No effect when "Best crop" and/or "Force aspect" is enabled.'); ?></div>
          </div>
        </div>

        <h2 class="render-title separate-top"><?php _e('Watermark'); ?></h2>
        <div class="form-row">
          <div class="form-label"><?php _e('Watermark type'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <input type="radio" id="watermark_none" name="watermark_type" value="none" <?php echo ((!osc_is_watermark_image() && !osc_is_watermark_text()) ? 'checked="checked"' : ''); ?> />
              <label for="watermark_none"><?php _e('None'); ?></label>
            </div>

            <div class="form-label-checkbox">
              <input type="radio" id="watermark_text" name="watermark_type" value="text" <?php echo (osc_is_watermark_text() ? 'checked="checked"' : ''); ?> <?php echo ($freeType ? '' : 'disabled="disabled"'); ?> />
              <label for="watermark_text"><?php _e('Text'); ?></label>
              <?php if(!$freeType) { ?>
              <div class="flashmessage flashmessage-inline error">
                <p><?php printf(__('Freetype library is required. How to <a target="_blank" href="%s">install/configure</a>') , 'http://www.php.net/manual/en/image.installation.php'); ?></p>
              </div>
              <?php } ?>
            </div>

            <div class="form-label-checkbox">
              <input type="radio" id="watermark_image" name="watermark_type" value="image" <?php echo (osc_is_watermark_image() ? 'checked="checked"' : ''); ?> />
              <label for="watermark_image"><?php _e('Image'); ?></label>
            </div>
          </div>
        </div>

        <div id="watermark_text_box" class="table-backoffice-form" <?php echo (osc_is_watermark_text() ? '' : 'style="display:none;"'); ?>>
          <h2 class="render-title"><?php _e('Watermark text settings'); ?></h2>
          <div class="form-row">
            <div class="form-label"><?php _e('Text'); ?></div>
            <div class="form-controls">
              <input type="text" class="large" name="watermark_text" value="<?php echo osc_esc_html(osc_watermark_text()); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Color'); ?></div>
            <div class="form-controls">
              <input type="text" maxlength="6" id="colorpickerField" class="small" name="watermark_text_color" value="<?php echo osc_esc_html(osc_watermark_text_color()); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Position'); ?></div>
            <div class="form-controls">
              <select name="watermark_text_place" id="watermark_text_place">
                <option value="centre" <?php echo (osc_watermark_place() == 'centre') ? 'selected="true"' : ''; ?>><?php _e('Centre'); ?></option>
                <option value="tl" <?php echo (osc_watermark_place() == 'tl') ? 'selected="true"' : ''; ?>><?php _e('Top Left'); ?></option>
                <option value="tr" <?php echo (osc_watermark_place() == 'tr') ? 'selected="true"' : ''; ?>><?php _e('Top Right'); ?></option>
                <option value="bl" <?php echo (osc_watermark_place() == 'bl') ? 'selected="true"' : ''; ?>><?php _e('Bottom Left'); ?></option>
                <option value="br" <?php echo (osc_watermark_place() == 'br') ? 'selected="true"' : ''; ?>><?php _e('Bottom Right'); ?></option>
              </select>
            </div>
          </div>
        </div>

        <div id="watermark_image_box" <?php echo (osc_is_watermark_image() ? '' : 'style="display:none;"'); ?>>
          <h2 class="render-title separate-top"><?php _e('Watermark image settings'); ?></h2>
          <div class="form-row">
            <div class="form-label"><?php _e('Image'); ?></div>
            <div class="form-controls">
              <input type="file" name="watermark_image" id="watermark_image_file"/>
              <?php if(osc_is_watermark_image()!='') { ?>
                <div class="help-box"><img width="100px" src="<?php echo osc_base_url() . str_replace(osc_base_path(), '', osc_uploads_path()) . "watermark.png" ?>" /></div>
              <?php } ?>
              <div class="help-box"><?php _e("It has to be a .PNG image"); ?></div>
              <div class="help-box"><?php _e("Osclass doesn't check the watermark image size"); ?></div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Position'); ?></div>
            <div class="form-controls">
              <select name="watermark_image_place" id="watermark_image_place" >
                <option value="centre" <?php echo (osc_watermark_place() == 'centre') ? 'selected="true"' : ''; ?>><?php _e('Centre'); ?></option>
                <option value="tl" <?php echo (osc_watermark_place() == 'tl') ? 'selected="true"' : ''; ?>><?php _e('Top Left'); ?></option>
                <option value="tr" <?php echo (osc_watermark_place() == 'tr') ? 'selected="true"' : ''; ?>><?php _e('Top Right'); ?></option>
                <option value="bl" <?php echo (osc_watermark_place() == 'bl') ? 'selected="true"' : ''; ?>><?php _e('Bottom Left'); ?></option>
                <option value="br" <?php echo (osc_watermark_place() == 'br') ? 'selected="true"' : ''; ?>><?php _e('Bottom Right'); ?></option>
              </select>
            </div>
          </div>
        </div>

        <h2 class="render-title separate-top"><?php _e('Regenerate images settings'); ?></h2>
        <div class="form-row">
          <div class="form-label"><?php _e('Regenerate batch'); ?></div>
          <div class="form-controls">
            <input type="text" class="input-medium" name="media_regen_batch" value="<?php echo osc_esc_html($media_regen_batch); ?>" />
            <div class="inpt-desc"><?php _e('resources'); ?></div>
            <div class="help-box"><?php _e('Amount of resources to process in one step for regenerate action (in 10 seconds interval). Recommended range is 10-100.'); ?></div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Refresh batch'); ?></div>
          <div class="form-controls">
            <input type="text" class="input-medium" name="media_refresh_batch" value="<?php echo osc_esc_html($media_refresh_batch); ?>" />
            <div class="inpt-desc"><?php _e('resources'); ?></div>
            <div class="help-box"><?php _e('Amount of resources to process in one step for refresh action (in 10 seconds interval). Recommended range is 10-100.'); ?></div>
          </div>
        </div>

        <div class="clear"></div>
        <div class="form-actions">
          <input type="submit" id="save_changes" value="<?php echo osc_esc_html(__('Save changes')); ?>" class="btn btn-submit" />
        </div>

        <h2 class="render-title separate-top" id="regenerate"><?php _e('Regenerate images'); ?></h2>
        <div class="form-row reg-img">
          <div class="form-controls">
          <?php
            $itemResource = ItemResource::newInstance();
            $count_all = ($media_regen_total > 0 ? $media_regen_total : (int)$itemResource->countResources());
            $count_done = $media_regen_done;
            $perc_done = ($count_all > 0 ? floor(($count_done * 100) / $count_all) : 0);
            $estimate_regen_seconds = (int)ceil($count_all / max(1, (int)$media_regen_batch)) * 10;
            $estimate_refresh_seconds = (int)ceil($count_all / max(1, (int)$media_refresh_batch)) * 10;
          ?>

          <div class="flashmessage flashmessage-info flashmessage-inline regen-info">
            <p>
              <?php if($media_regen_running == 1) { ?>
                <?php if($media_regen_skip_refresh == 1) { ?>
                  <?php _e('Image refresh started. Progress will continue on this page until finished. Do not close this window.'); ?><br/>
                <?php } else { ?>
                  <?php _e('Image regeneration started. Progress will continue on this page until finished. Do not close this window.'); ?><br/>
                <?php } ?>
              <?php } ?>
              <b><?php _e('Important:'); ?></b> <?php _e('This process runs in small batches and can continue when you return to this page.'); ?>
            </p>
          </div>

          <p class="stats-last-recalc"><?php echo sprintf(__('Last regeneration: %s'), osc_esc_html(media_format_last_recalc($media_regen_last_recalc))); ?></p>

          <?php if($media_regen_running == 1) { ?>
            <div class="regen-progress-wrap">
              <div class="regen-progress-bar"><div id="media_regen_progress_val" class="regen-progress-val" style="width:<?php echo $perc_done; ?>%;"></div></div>
              <p class="regen-progress-label"><strong id="media_regen_percent"><?php echo $perc_done; ?>%</strong> <span id="media_regen_progress_text"><?php echo sprintf(__('%s / %s processed'), $count_done, $count_all); ?></span></p>
            </div>
            <div id="media_regen_error_box" class="flashmessage flashmessage-error flashmessage-inline" style="display:none;">
              <p id="media_regen_error_text"></p>
            </div>
          <?php } ?>

          <p><b><?php _e('Regenerate images:'); ?></b> <?php _e('Recreate all image sizes (types) from original image if it exists. Execute regenerate when you have changed size for thumbnails, preview or normal image.'); ?></p>
          <p><b><?php _e('Refresh images:'); ?></b> <?php _e('Run image-related hooks that are often used by plugins.'); ?></p>
          <hr/>
          <p><?php echo sprintf(__('Your Osclass installation has <u>%s resources</u>.'), $count_all); ?></p>
          <p><?php echo sprintf(__('Estimated regenerate time with current batch settings (%s resources per batch) is: %s.'), $media_regen_batch, osc_esc_html(media_format_estimated_time($estimate_regen_seconds))); ?></p>
          <p><?php echo sprintf(__('Estimated refresh time with current batch settings (%s resources per batch) is: %s.'), $media_refresh_batch, osc_esc_html(media_format_estimated_time($estimate_refresh_seconds))); ?></p>

          <?php if($media_regen_running == 1) { ?>
            <div class="form-actions">
              <a class="btn" href="<?php echo osc_admin_base_url(true) . '?page=settings&action=images_post_reset&' . osc_csrf_token_url(); ?>"><?php  _e('Cancel current processing'); ?></a>
            </div>
          <?php } else { ?>
            <div class="form-actions">
              <a class="btn btn-submit" href="<?php echo osc_admin_base_url(true) . '?page=settings&action=images_post&regenerateAction=regenerate&'.osc_csrf_token_url(); ?>"><?php  _e('Regenerate images'); ?></a>
              <a class="btn" href="<?php echo osc_admin_base_url(true) . '?page=settings&action=images_post&regenerateAction=refresh&'.osc_csrf_token_url(); ?>"><?php  _e('Refresh images'); ?></a>
            </div>
          <?php } ?>
          </div>
        </div>

      </div>
    </fieldset>
  </form>
</div>
<div id="dialog-watermark-warning" title="<?php echo osc_esc_html(__('Recommendation')); ?>" class="has-form-actions hide">
  <div class="form-horizontal">
    <div class="form-row">
      <?php _e("We highly recommend you have the 'Keep original image' option active when you use watermarks."); ?>
    </div>
    <div class="form-actions">
      <div class="wrapper">
        <a class="btn float-right" href="javascript:void(0);" onclick="$('#dialog-watermark-warning').dialog('close');"><?php _e('Close'); ?></a>
        <div class="clear"></div>
      </div>
    </div>
  </div>
</div>
<?php osc_current_admin_theme_path('parts/footer.php');
