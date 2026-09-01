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

//customize Head
function customHead() { 
  ?>
  <script type="text/javascript">
  $(document).ready(function(){
    // Code for form validation
    $("form[name=currency_form]").validate({
      rules: {
        pk_c_code: {
          required: true,
          minlength: 3,
          maxlength: 3
        },
        s_description: {
          required: true,
          minlength: 1
        },
        s_name: {
          required: true,
          minlength: 1
        }
      },
      messages: {
        pk_c_code: {
          required: '<?php echo osc_esc_js(__('Currency code: this field is required')); ?>.',
          minlength: '<?php echo osc_esc_js(__('Currency code: length of code must be exactly 3 characters')); ?>.',
          maxlength: '<?php echo osc_esc_js(__('Currency code: length of code must be exactly 3 characters')); ?>.'
        },
        s_description: {
          required: '<?php echo osc_esc_js(__('Currency symbol: this field is required')); ?>.',
          minlength: '<?php echo osc_esc_js(__('Currency symbol: length of symbol must be at least 1 character')); ?>.'
        },
        s_name: {
          required: '<?php echo osc_esc_js(__('Name: this field is required')); ?>.',
          minlength: '<?php echo osc_esc_js(__('Name: length of currency name must be at least 1 character')); ?>.'
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
  });
  </script>
  <?php
}

osc_add_hook('admin_header','customHead', 10);


function customPageHeader(){ 
  ?>
  <h1><?php _e('Currencies'); ?></h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


$typeForm = __get('typeForm');
function customText($return = 'title') {
  $typeForm = __get('typeForm');
  $text = array();
  switch($typeForm) {
    case('add_post'):
      $text['title'] = __('Add currency');
      $text['button'] = __('Add currency');
    break;
    case('edit_post'):
      $text['title'] = __('Edit currency');
      $text['button'] = __('Update currency');
    break;
  }

  return $text[$return];
}


function customPageTitle($string) {
  return sprintf('%s - %s', customText('title'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');


$aCurrency = View::newInstance()->_get('aCurrency');

osc_current_admin_theme_path('parts/header.php'); 
?>

<div id="add-currency-settings">
  <h2 class="render-title"><?php echo customText('title'); ?></h2>
  <ul id="error_list"></ul>
  <form name="currency_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="page" value="currencies" />
    <input type="hidden" name="action" value="<?php echo $typeForm; ?>" />
    <?php if($typeForm == 'edit_post') { ?>
    <input type="hidden" name="pk_c_code" value="<?php echo osc_esc_html($aCurrency['pk_c_code']); ?>" />
    <?php } ?>
    <fieldset>
      <div class="form-horizontal">
        <div class="form-row">
          <div class="form-label"><?php _e('Currency Code'); ?></div>
          <div class="form-controls">
            <input type="text" class="input-small" name="pk_c_code" value="<?php echo osc_esc_html($aCurrency['pk_c_code']); ?>" <?php if($typeForm == 'edit_post') echo 'disabled="disabled"'; ?> />
            <span class="help-box"><?php printf(__('Must be a three-character code according to the <a href="%s" target="_blank">ISO 4217</a>'), 'http://en.wikipedia.org/wiki/ISO_4217'); ?></span>
          </div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('Currency symbol'); ?></div>
          <div class="form-controls">
            <input type="text" class="input-small" name="s_description" value="<?php echo osc_esc_html($aCurrency['s_description']); ?>" />
            <span class="help-box"><?php _e('Should not be longer than 2 characters. Examples: $, €, £, ₽, ₦, ₮, ₩, ¥, ₹, ...'); ?></span>
          </div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('Name'); ?></div>
          <div class="form-controls">
            <input type="text" name="s_name" value="<?php echo osc_esc_html($aCurrency['s_name']); ?>" />
          </div>
        </div>
        <div class="form-actions">
          <input type="submit" value="<?php echo osc_esc_html(customText('button')); ?>" class="btn btn-submit" />

          <?php if($typeForm == 'edit_post') { ?>
          <input class="btn btn-red" type="button" value="<?php echo osc_esc_html(__('Cancel')); ?>" onclick="location.href='<?php echo osc_admin_base_url(true); ?>?page=settings&amp;action=currencies'">
          <?php } ?>
        </div>
      </div>
    </fieldset>
  </form>
</div>
<!-- /settings form -->
<?php osc_current_admin_theme_path('parts/footer.php'); ?>