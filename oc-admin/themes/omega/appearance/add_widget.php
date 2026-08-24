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
osc_enqueue_script('tiny_mce');

$widget = __get('widget');
$theme_sections = __get('theme_sections');
$custom_sections = __get('custom_sections');
$widget_sections = __get('widget_sections');
$locales = osc_get_locales();

if(Params::getParam('action') == 'edit_widget') {
  $title  = __('Edit widget');
  $edit   = true;
  $button = osc_esc_html(__('Save changes'));
} else {
  $title  = __('Add widget');
  $edit   = false;
  $button = osc_esc_html(__('Add widget'));
}

$selected_location = '';
if($edit && isset($widget['s_location'])) {
  $selected_location = $widget['s_location'];
} else if(Params::getParam('location') != '') {
  $selected_location = osc_widget_sanitize_slug(Params::getParam('location'));
}

$is_theme_section = (is_array($theme_sections) && in_array($selected_location, $theme_sections, true));

function addHelp() {
  echo '<p>' . __('Section is required. Pick a theme or custom section, or type a new slug. Name is the admin label only and is not printed on the site. Internal name is unique and becomes the CSS class wdg-{name}.') . '</p>';
  echo '<p>' . __('HTML content can differ per locale unless Single locale is checked. Code is optional HTML or JavaScript printed after the content (not localized). CSS is printed only when the widget has content or code.') . '</p>';
  echo '<p>' . __('Device visibility uses CSS: All devices, Mobile only (0-767px), or Desktop only (768px and up). A widget with empty content and empty code is not printed.') . '</p>';
}
osc_add_hook('help_box','addHelp');

function customPageHeader(){
  if(Params::getParam('action') == 'edit_widget') {
    $title  = __('Edit widget');
  } else {
    $title  = __('Add widget');
  }
  ?>
    <h1><?php echo $title; ?>
      <a href="#" class="btn ico ico-32 ico-help float-right"></a>
    </h1>
  <?php
}
osc_add_hook('admin_page_header','customPageHeader');

function customPageTitle($string) {
  return sprintf(__('Appearance - %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');

function customHead2() {
  ?>
  <script type="text/javascript">
    tinyMCE.init({
      selector: 'textarea.widget-html',
      width: "100%",
      height: "560px",
      language: 'en',
      theme_advanced_toolbar_align : "left",
      theme_advanced_toolbar_location : "top",

      content_style: "body {font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue',sans-serif;}",
      contextmenu: 'link linkchecker image editimage table spellchecker configurepermanentpen',
      plugins: 'paste print preview importcss searchreplace autolink autosave save directionality visualblocks visualchars fullscreen image link media code codesample table charmap hr pagebreak nonbreaking toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap quickbars',
      menubar: 'file edit view insert format tools table tc help',
      toolbar1: 'undo redo | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | fontselect fontsizeselect formatselect',
      toolbar2: 'outdent indent | numlist bullist checklist | forecolor backcolor removeformat | link image media blockquote | ltr rtl | pagebreak codesample charmap | print code fullscreen',
      image_caption: true,
      quickbars_selection_toolbar: 'bold italic underline strikethrough | quicklink h2 h3 h4 | blockquote quickimage quicktable',
      toolbar_mode: 'wrap',

      entity_encoding : "raw",
      theme_advanced_buttons1_add : "forecolorpicker,fontsizeselect",
      theme_advanced_buttons2_add: "media",
      theme_advanced_buttons3: "fullscreen",
      theme_advanced_disable : "styleselect",
      extended_valid_elements : "script[type|src|charset|defer]",
      relative_urls : false,
      remove_script_host : false,
      convert_urls : false,
      paste_data_images: true,
      images_upload_url: '<?php echo osc_admin_base_url(); ?>themes/omega/tinyMceUploader.php',
      images_upload_base_path: '<?php echo osc_base_path() . OC_CONTENT_FOLDER; ?>/uploads/widget-images/',
      images_upload_credentials: true,
      images_upload_handler: function (blobInfo, success, failure) {
        var xhr, formData;
        xhr = new XMLHttpRequest();
        xhr.withCredentials = false;
        xhr.open('POST', '<?php echo osc_admin_base_url(); ?>themes/omega/tinyMceUploader.php?dataType=widget&ajaxRequest=1&nolog=1');
        xhr.onload = function() {
          var json;

          if(xhr.status != 200) {
            failure('HTTP Error: ' + xhr.status);
            return;
          }

          json = JSON.parse(xhr.responseText);

          if(!json || typeof json.location != 'string') {
            failure('Invalid JSON: ' + xhr.responseText);
            return;
          }

          success(json.location);
        };

        formData = new FormData();

        if(typeof(blobInfo.blob().name) !== undefined) {
          fileName = blobInfo.blob().name;
        } else {
          fileName = blobInfo.filename();
        }

        formData.append('file', blobInfo.blob(), fileName);

        xhr.send(formData);
      },
      mobile: {
        menubar: true,
        toolbar_mode: true
      }
    });
  </script>

  <script type="text/javascript">
    function toggleWidgetSingleLocale() {
      var single = $('#b_single_locale').is(':checked');
      if(single) {
        $('#language-tab').hide();
        $('.widget-locale-content').hide();
        $('.widget-locale-content.widget-locale-current').show();
      } else {
        if($('#language-tab li').length > 1) {
          $('#language-tab').show();
        }
        $('.widget-locale-content').show();
      }
    }

    $(document).ready(function(){
      toggleWidgetSingleLocale();
      $('#b_single_locale').on('change', toggleWidgetSingleLocale);

      $("form[name=widget_form]").validate({
        rules: {
          description: {
            required: true
          },
          location: {
            required: function() {
              return $.trim($('input[name=new_section]').val()) == '';
            }
          }
        },
        messages: {
          description: {
            required: '<?php echo osc_esc_js(__("Description: this field is required")); ?>.'
          },
          location: {
            required: '<?php echo osc_esc_js(__("Section: this field is required")); ?>.'
          }
        },
        errorLabelContainer: "#error_list",
        wrapper: "li",
        invalidHandler: function(form, validator) {
          $('html,body').animate({ scrollTop: $('h1').offset().top }, { duration: 250, easing: 'swing'});
        },
        submitHandler: function(form){
          $('button[type=submit], input[type=submit]').attr('disabled', 'disabled');
          form.submit();
        }
      });
    });
  </script>
  <?php
}

osc_add_hook('admin_header','customHead2',9);

osc_current_admin_theme_path('parts/header.php');
?>

<div id="widgets-page">
  <div class="widgets">
    <div id="item-form">
      <ul id="error_list"></ul>
      <form name="widget_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
        <input type="hidden" name="action" value="<?php echo ($edit ? 'edit_widget_post' : 'add_widget_post'); ?>" />
        <input type="hidden" name="page" value="appearance" />
        <?php if($edit) { ?>
        <input type="hidden" name="id" value="<?php echo osc_esc_html(Params::getParam('id', true)); ?>" />
        <?php } ?>
        <fieldset>
          <div class="input-line">
            <label><?php _e('Section'); ?> *</label>
            <div class="input">
              <select name="location" id="location">
                <option value=""><?php _e('Select a section'); ?></option>
                <?php if(is_array($theme_sections) && count($theme_sections) > 0) { ?>
                  <optgroup label="<?php echo osc_esc_html(__('Theme')); ?>">
                    <?php foreach($theme_sections as $section) { ?>
                      <option value="<?php echo osc_esc_html($section); ?>" <?php if($selected_location === $section) { echo 'selected="selected"'; } ?>><?php echo osc_esc_html($section); ?></option>
                    <?php } ?>
                  </optgroup>
                <?php } ?>
                <?php if(is_array($custom_sections) && count($custom_sections) > 0) { ?>
                  <optgroup label="<?php echo osc_esc_html(__('Custom sections')); ?>">
                    <?php foreach($custom_sections as $section) { ?>
                      <?php if(is_array($theme_sections) && in_array($section, $theme_sections, true)) { continue; } ?>
                      <option value="<?php echo osc_esc_html($section); ?>" <?php if($selected_location === $section) { echo 'selected="selected"'; } ?>><?php echo osc_esc_html($section); ?></option>
                    <?php } ?>
                  </optgroup>
                <?php } ?>
              </select>
              <span class="help-box"><?php _e('Or add a new custom section:'); ?></span>
              <input type="text" class="medium" name="new_section" value="" placeholder="<?php echo osc_esc_html(__('xyz')); ?>" />
            </div>
          </div>

          <?php if($selected_location != '' && !$is_theme_section) { ?>
          <div class="flashmessage flashmessage-info" style="display:block;">
            <p><?php printf(__('This section is not listed in the theme. Add this to your theme file: %s'), '<code>&lt;?php osc_widget(\'' . osc_esc_html($selected_location) . '\'); ?&gt;</code>'); ?></p>
            <p><?php _e('If the section is empty, that call prints nothing.'); ?></p>
          </div>
          <?php } ?>

          <div class="input-line">
            <label><?php _e('Name'); ?> *</label>
            <div class="input">
              <input type="text" class="large" name="description" maxlength="40" value="<?php if($edit && isset($widget['s_description'])) { echo osc_esc_html($widget['s_description']); } ?>" />
              <span class="help-box"><?php _e('Admin label only. It is not printed on the site.'); ?></span>
            </div>
          </div>

          <div class="input-line">
            <label><?php _e('Internal name'); ?></label>
            <div class="input">
              <input type="text" class="large" name="s_internal_name" value="<?php if($edit && isset($widget['s_internal_name'])) { echo osc_esc_html($widget['s_internal_name']); } ?>" />
              <span class="help-box"><?php _e('Unique slug used in CSS as wdg-{name}. Letters, numbers, hyphen and underscore.'); ?></span>
            </div>
          </div>

          <div class="input-line">
            <label><?php _e('Device visibility'); ?></label>
            <div class="input">
              <?php $device = ($edit && isset($widget['s_device_visibility']) ? $widget['s_device_visibility'] : 'all'); ?>
              <select name="s_device_visibility">
                <option value="all" <?php if($device === 'all') { echo 'selected="selected"'; } ?>><?php _e('All devices'); ?></option>
                <option value="mobile" <?php if($device === 'mobile') { echo 'selected="selected"'; } ?>><?php _e('Mobile only'); ?></option>
                <option value="desktop" <?php if($device === 'desktop') { echo 'selected="selected"'; } ?>><?php _e('Desktop only'); ?></option>
              </select>
            </div>
          </div>

          <div class="input-line">
            <label><?php _e('Single locale'); ?></label>
            <div class="input">
              <label>
                <input type="checkbox" id="b_single_locale" name="b_single_locale" value="1" <?php if($edit && isset($widget['b_single_locale']) && (int)$widget['b_single_locale'] === 1) { echo 'checked="checked"'; } ?> />
                <?php _e('Use one content field for all locales.'); ?>
              </label>
            </div>
          </div>

          <?php printLocaleTabs($locales); ?>
          <?php printLocaleContentWidget($locales, ($edit ? $widget : array())); ?>

          <div class="input-description-wide">
            <label><?php _e('Code'); ?></label>
            <textarea name="s_code" id="s_code" rows="8"><?php if($edit && isset($widget['s_code'])) { echo osc_esc_html($widget['s_code']); } ?></textarea>
            <span class="help-box"><?php _e('Optional HTML or JavaScript printed after the content. Not localized. Can be used without content (for example AdSense).'); ?></span>
          </div>

          <div class="input-description-wide">
            <label><?php _e('CSS'); ?></label>
            <textarea name="s_css" id="s_css" rows="6"><?php if($edit && isset($widget['s_css'])) { echo osc_esc_html($widget['s_css']); } ?></textarea>
            <span class="help-box"><?php _e('Optional CSS printed only when the widget has content or code.'); ?></span>
          </div>

          <div class="form-actions">
            <a class="btn" href="<?php echo osc_admin_base_url(true); ?>?page=appearance&amp;action=widgets"><?php _e('Cancel'); ?></a>
            <input type="submit" value="<?php echo $button; ?>" class="btn btn-submit" />
          </div>
        </fieldset>
      </form>
    </div>
  </div>
</div>
<?php osc_current_admin_theme_path( 'parts/footer.php' );