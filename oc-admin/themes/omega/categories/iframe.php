<?php
if(!defined('ABS_PATH')) exit('ABS_PATH is not loaded. Direct access is not allowed.');

$category = __get("category");
$has_subcats = __get("has_subcategories");
$locales  = OSCLocale::newInstance()->listAllEnabled();
?>

<div class="iframe-category">
  <form action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="page" value="ajax" />
    <input type="hidden" name="action" value="edit_category_post" />
    <?php CategoryForm::primary_input_hidden($category); ?>
    <fieldset>
      <?php osc_run_hook('admin_categories_form_top', $category); ?>
      <div class="grid-system">
        <div class="grid-row grid-100">
          <div class="row-wrapper">
            <label><?php _e('Category ID'); ?></label>
            <div class="input micro">
              <input type="text" class="input-small" disabled="disabled" value="<?php echo (int)$category['pk_i_id']; ?>" />
            </div>
          </div>
        </div>

        <?php osc_run_hook('admin_categories_form', $category); ?>

        <div class="grid-row grid-100">
          <div class="row-wrapper">
            <?php CategoryForm::multilanguage_name_description($locales, $category); ?>
          </div>
        </div>
        <div class="grid-row grid-100 category-form-divider">
          <hr />
        </div>
        <div class="grid-row grid-100">
          <div class="row-wrapper">
            <label><?php _e('Icon'); ?></label>
            <div class="input micro">
              <input class="input-large" type="text" name="s_icon" id="s_icon" value="<?php echo osc_esc_html(isset($category['s_icon']) ? $category['s_icon'] : ''); ?>" />
              <span class="help-inline"><?php _e('URL to an image, Font Awesome classes (e.g. fa fa-star), or inline SVG code.'); ?></span>
            </div>
          </div>
        </div>
        <div class="grid-row grid-100">
          <div class="row-wrapper">
            <label><?php _e('Color'); ?></label>
            <div class="input micro">
              <?php CategoryForm::color_input_text($category); ?>
              <span class="help-inline"><?php _e('Hex color code (e.g. #ff0000) used for category styling.'); ?></span>
            </div>
          </div>
        </div>
        <div class="grid-row grid-100 category-form-divider">
          <hr />
        </div>
        <div class="grid-row grid-100">
          <div class="row-wrapper">
            <label><?php _e('Expiration'); ?></label>
            <div class="input micro">
              <input class="input-small" type="text" name="i_expiration_days" id="i_expiration_days" maxlength="3" value="<?php echo osc_esc_html(isset($category['i_expiration_days']) ? $category['i_expiration_days'] : ''); ?>" />
              <div class="inpt-desc"><?php _e('days'); ?></div>
              <span class="help-inline"><?php _e('Enter days until listings expire, or 0 to disable expiration (non-expiring listings).'); ?></span>
            </div>
          </div>
        </div>
        <div class="grid-row grid-100">
          <div class="row-wrapper">
            <label><?php _e('Price field'); ?></label>
            <div class="input micro">
              <?php CategoryForm::price_enabled_select($category); ?>
              <span class="help-inline"><?php _e('When enabled, users can enter a price when posting listings in this category.'); ?></span>
              <?php if($has_subcats) { ?>
              <label class="apply-subcats-label">
                <?php CategoryForm::apply_changes_to_subcategories($category); ?>
                <?php _e('Apply expiration and price field changes to subcategories'); ?>
              </label>
              <?php } ?>
            </div>
          </div>
        </div>
        <div class="clear"></div>
      </div>

      <?php osc_run_hook('admin_categories_form_bottom', $category); ?>

      <div class="form-vertical">
        <div class="form-actions">
          <input type="submit" class="btn btn-submit" value="<?php echo osc_esc_html(__('Save changes')); ?>" />
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
          var ret = eval("(" + data + ")");
          var message = "";
          if(ret.error == 0 || ret.error == 4 ) {
            $('.iframe-category').fadeOut('fast', function(){
              $('.iframe-category').remove();
              $('.quick-edit-btn').text('<?php echo osc_esc_js(__('Quick edit')); ?>');
            });
            $(".jsMessage p").attr('class', 'ok');
            message += ret.msg;
            $('.content_list_<?php echo (int)$category['pk_i_id']; ?>').closest('.category_div').find('.name').html(ret.text);
          } else {
            $(".jsMessage p").attr('class', 'error');
            message += ret.msg;
          }
          $(".jsMessage").fadeIn("fast");
          $(".jsMessage p").html(message);
        },
        error: function(){
          $(".jsMessage").fadeIn("fast");
          $(".jsMessage p").attr('class', '');
          $(".jsMessage p").html('<?php echo osc_esc_js(__('Ajax error, please try again.')); ?>');
        }
      });
      return false;
    });
    oscTab();
  });
</script>
