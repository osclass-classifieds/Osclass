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

//customize Head
function customHead() { 
  ?>
  <link rel="stylesheet" media="screen" type="text/css" href="<?php echo osc_assets_url('js/colorpicker/css/colorpicker.css'); ?>" />
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
    });
  </script>
  <?php
}

osc_add_hook('admin_header','customHead', 10);


function render_offset(){
  return 'row-offset';
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
  return sprintf(__('Media Settings - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php'); 
?>

<!--los input tienen una class para el tamaño ...-->
<div id="general-settings">
  <h2 class="render-title"><?php _e('Media Settings'); ?></h2>
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
          <h2 class="render-title"><?php _e('Watermark Text Settings'); ?></h2>
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
          <h2 class="render-title separate-top"><?php _e('Watermark Image Settings'); ?></h2>
          <div class="form-row">
            <div class="form-label"><?php _e('Image'); ?></div>
            <div class="form-controls">
              <input type="file" name="watermark_image" id="watermark_image_file"/>
              <?php if(osc_is_watermark_image()!='') { ?>
                <div class="help-box"><img width="100px" src="<?php echo osc_base_url() . str_replace(osc_base_path(), '', osc_uploads_path()) . "watermark.png" ?>" /></div>
              <?php }; ?>
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
        
        <div class="clear"></div>
        <div class="form-actions">
          <input type="submit" id="save_changes" value="<?php echo osc_esc_html(__('Save changes')); ?>" class="btn btn-submit" />
        </div>
        
        <h2 class="render-title" id="regenerate" style="margin-top:40px"><?php _e('Regenerate images'); ?></h2>
        <div class="form-row reg-img">
          <div class="form-controls">
          <p><?php _e("You can regenerate different image dimensions. If you have changed the dimension of thumbnails, preview or normal images, you might want to regenerate your images."); ?></p>
          <p><?php _e("Refresh will not re-create images, but will only run relevant hooks before/after regeneration."); ?></p>

          <?php
            $itemResource = ItemResource::newInstance();
            $data = json_decode(osc_get_preference('regenerate_image_data'), true);

            $batch_id = 0;

            if(isset($data['batch_id']) && $data['batch_id'] > 0) {
            $batch_id = $data['batch_id'];
            $last_id = $data['last_id'];
            $count_done = $data['image_done'];
            $count_all = $data['count_all'];
            $skip_refresh = $data['skip_refresh'];
            $count_process = $count_all - $count_done;
            $perc_done = round(($count_done/$count_all)*100, 1);
            $date = $data['date'];
            } else {
            $count_all = $itemResource->countResources();
            }
          ?>

          <?php if($batch_id > 0) { ?>
            <p><?php echo sprintf(__('There is unfinished image regeneration from date %s with batch <b>#%s</b>, completed on <b>%s%%</b>.'), $date, $batch_id, $perc_done); ?></p>
            <p><?php echo sprintf(__('Images to be processed: %s, images already done: %s, all images count: %s. Click on "Continue regeneration" or "Continue refresh" to complete this process or "Reset" button to clean data from unfinishe process and start again.'), $count_process, $count_done, $count_all); ?></p>
            <a class="btn btn-submit" href="<?php echo osc_admin_base_url(true) . '?page=settings&action=images_post&' . osc_csrf_token_url(); ?>"><?php echo ($skip_refresh == 1 ? __('Continue refresh') : __('Continue regeneration')); ?></a>
            <a class="btn" href="<?php echo osc_admin_base_url(true) . '?page=settings&action=images_post_reset'; ?>"><?php  _e('Reset'); ?></a>
          <?php } else { ?>
            <p><?php echo sprintf(__('There is %s images to be processed. It is recommended to increase php maximum execution time and memory to highest values, in case you have thousands of images, as this process is extensive. If you get error after while, simple go back to this page and continue with process, regeneration will start on latest regenerated image.'), $count_all); ?></p>
            <a class="btn" href="<?php echo osc_admin_base_url(true) . '?page=settings&action=images_post&'.osc_csrf_token_url(); ?>"><?php  _e('Regenerate images'); ?></a>
            <a class="btn" href="<?php echo osc_admin_base_url(true) . '?page=settings&action=images_post&skip_refresh=1&'.osc_csrf_token_url(); ?>"><?php  _e('Refresh images'); ?></a>
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
<?php osc_current_admin_theme_path('parts/footer.php'); ?>