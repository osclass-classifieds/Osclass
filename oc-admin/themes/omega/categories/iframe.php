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


$category = __get("category");
$has_subcats = __get("has_subcategories");
$locales  = OSCLocale::newInstance()->listAllEnabled();
?>

<div class="iframe-category">
  <h3><?php echo sprintf(__('Edit category ID: %s'), $category['pk_i_id']); ?></h3>
  <form action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="page" value="ajax" />
    <input type="hidden" name="action" value="edit_category_post" />
    <?php CategoryForm::primary_input_hidden($category); ?>
    <fieldset>
      <div class="grid-system">
        <div class="grid-row grid-first-row grid-30 no-bottom-margin">
          <div class="row-wrapper">
            <label><?php _e('Expiration days'); ?></label>
            <div class="input micro">
              <?php CategoryForm::expiration_days_input_text($category); ?>
              <p class="help-inline"><?php _e("If the value is zero, it means this category doesn't have an expiration"); ?></p>
              <label><?php CategoryForm::price_enabled_for_category($category); ?><span><?php _e('Enable / Disable the price field'); ?></span></label>
              <?php if($has_subcats) { ?>
                <br />
                <br />
                <label><?php CategoryForm::apply_changes_to_subcategories($category); ?><span><?php _e('Apply the expiration date and price field changes to children categories'); ?></span></label>
              <?php }; ?>
            </div>
          </div>
        </div>
        <div class="grid-row grid-70 no-bottom-margin">
          <div class="row-wrapper">
            <?php CategoryForm::multilanguage_name_description($locales, $category); ?>
          </div>

          <div class="row-wrapper-catd">
            <div class="row-left"><label><?php _e('Icon'); ?></label><?php CategoryForm::icon_input_text($category); ?></div>
            <div class="row-right"><label><?php _e('Color'); ?></label><?php CategoryForm::color_input_text($category); ?></div>
          </div>
        </div>
        <div class="clear"></div>
      </div>
      <div class="form-vertical">
          <div class="form-actions">
            <input type="submit" class="btn btn-submit" value="<?php echo osc_esc_html( __('Save changes') ); ?>" />
            <input type="button" class="btn btn-red" onclick="$('.iframe-category').remove();" value="<?php echo osc_esc_html( __('Cancel') ); ?>" />
          </div>
      </div>
    </fieldset>
  </form>
</div>
<script type="text/javascript">
  $(document).ready(function() {
    $('.iframe-category form').submit(function() {
      $(".jsMessage").hide();
      $.ajax({
        type: 'POST',
        url: $(this).attr('action'),
        data: $(this).serialize(),
        success: function(data) {
          var ret = eval( "(" + data + ")");
          var message = "";
          if( ret.error == 0 || ret.error == 4 ) {
            $('.iframe-category').fadeOut('fast', function(){
              $('.iframe-category').remove();
            });
            $(".jsMessage p").attr('class', 'ok');
            message += ret.msg;
            $('.iframe-category').parent().parent().find('.name').html(ret.text);
          } else {
            $(".jsMessage p").attr('class', 'error');
            message += ret.msg;
          }

          $(".jsMessage").fadeIn("fast");
          $(".jsMessage p").html(message);
          $('div.content_list_<?php echo osc_category_id(); ?>').html('');
        },
        error: function(){
          $(".jsMessage").fadeIn("fast");
          $(".jsMessage p").attr('class', '');
          $(".jsMessage p").html('<?php echo osc_esc_js(__('Ajax error, please try again.')); ?>');
        }
      })
      return false;
    });
  oscTab();
  });
</script>