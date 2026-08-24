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

$info   = __get("info");
$widget = __get("widget");

if( Params::getParam('action') == 'edit_widget' ) {
  $title  = __('Edit widget');
  $edit   = true;
  $button = osc_esc_html( __('Save changes') );
} else {
  $title  = __('Add widget');
  $edit   = false;
  $button = osc_esc_html( __('Add widget') );
}

function customPageHeader(){
  if( Params::getParam('action') == 'edit_widget' ) {
    $title  = __('Edit widget');
  } else {
    $title  = __('Add widget');
  }
  ?>
    <h1><?php echo $title; ?></h1>
  <?php
}
osc_add_hook('admin_page_header','customPageHeader');

function customPageTitle($string) {
  return sprintf(__('Appearance - %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');

function customHead() {
  $info   = __get("info");
  $widget = __get("widget");
  
  if( Params::getParam('action') == 'edit_widget' ) {
    $title  = __('Edit widget');
    $edit   = true;
    $button = osc_esc_html( __('Save changes') );
  } else {
    $title  = __('Add widget');
    $edit   = false;
    $button = osc_esc_html( __('Add widget') );
  }
}


function customHead2() {
  // if(osc_tinymce_widget_enabled() == '1') { 
  if(1==1) { 
  ?>
  <script type="text/javascript">
    tinyMCE.init({
      mode : "textareas",
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
      
      // plugins : [
        // "advlist autolink lists link image charmap preview anchor",
        // "searchreplace visualblocks code fullscreen",
        // "insertdatetime media table paste autoresize"
      // ],
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
        //formData.append('file', blobInfo.blob(), fileName(blobInfo));

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
    $(document).ready(function(){
      // Code for form validation
      $("form[name=widget_form]").validate({
        rules: {
          description: {
            required: true
          }
        },
        messages: {
          description: {
            required: '<?php echo osc_esc_js(__("Description: this field is required")); ?>.'
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
}


osc_add_hook('admin_header', 'customHead', 10);
osc_add_hook('admin_header','customHead2',9);

osc_current_admin_theme_path('parts/header.php'); 
?>

<div id="widgets-page">
  <div class="widgets">
    <div id="item-form">
      <ul id="error_list"></ul>
      <form name="widget_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
        <input type="hidden" name="action" value="<?php echo ( $edit ? 'edit_widget_post' : 'add_widget_post' ); ?>" />
        <input type="hidden" name="page" value="appearance" />
        <?php if( $edit) { ?>
        <input type="hidden" name="id" value="<?php echo Params::getParam('id', true); ?>" />
        <?php } ?>
        <input type="hidden" name="location" value="<?php echo Params::getParam('location', true); ?>" />
        <fieldset>
          <div class="input-line">
            <label><?php _e('Widget Name'); ?></label>
            
            <div class="input">
              <input type="text" class="large" name="description" value="<?php if( $edit ) { echo osc_esc_html($widget['s_description']); } ?>" />
              <span class="help-box"><?php _e('For internal purposes only'); ?></span>
            </div>
          </div>
          
          <div class="input-description-wide">
            <label><?php _e('Content'); ?></label>
            <textarea name="content" id="body"><?php if($edit) { echo osc_esc_html($widget['s_content']); } ?></textarea>
          </div>
          
          <div class="form-actions">
            <input type="submit" value="<?php echo $button; ?>" class="btn btn-submit" />
          </div>
        </fieldset>
      </form>
    </div>
  </div>
</div>
<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>