<?php
if(!defined('ABS_PATH')) exit('ABS_PATH is not loaded. Direct access is not allowed.');

osc_enqueue_script('tabber');
osc_enqueue_script('jquery-validate');

function customFrmText($return = 'title') {
  $category = __get('category');
  $text = array();

  if(isset($category['pk_i_id']) && (int)$category['pk_i_id'] > 0) {
    $text['edit'] = true;
    $text['title'] = __('Edit category');
    $text['action_frm'] = 'edit_post';
    $text['btn_text'] = __('Save changes');
  } else {
    $text['edit'] = false;
    $text['title'] = __('Add category');
    $text['action_frm'] = 'add_post';
    $text['btn_text'] = __('Add category');
  }

  return $text[$return];
}

function customPageHeader() {
  ?>
  <h1><?php _e('Categories'); ?></h1>
  <?php
}
osc_add_hook('admin_page_header','customPageHeader');

function customPageTitle($string) {
  return sprintf('%s - %s', customFrmText('title'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');

function customHead() {
  $maxLevels = (int)osc_num_category_levels();
  if($maxLevels <= 0) {
    $maxLevels = 4;
  }
  ?>
  <script type="text/javascript">
  $(document).ready(function(){
    oscTab();

    $.validator.addMethod('atLeastOneCategoryName', function(value, element) {
      var ok = false;
      $('input[name$="#s_name"]').each(function() {
        if($.trim($(this).val()) !== '') {
          ok = true;
        }
      });
      return ok;
    }, '<?php echo osc_esc_js(__('Sorry, including at least a title is mandatory')); ?>');

    $("form[name=category_form]").validate({
      rules: {
        '<?php echo osc_esc_js(osc_language()); ?>#s_name': {
          atLeastOneCategoryName: true
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

$category = __get('category');
$categories_tree = __get('categories_tree');
$disabled_parent_ids = __get('disabled_parent_ids');
$has_subcategories = __get('has_subcategories');
$locales = __get('locales');
$isEdit = customFrmText('edit');
$parent = (int)Params::getParam('parent');
$maxLevels = (int)osc_num_category_levels();
if($maxLevels <= 0) {
  $maxLevels = 4;
}
$listUrl = osc_categories_admin_list_url(($parent > 0 ? array('parent' => $parent) : array()));

osc_current_admin_theme_path('parts/header.php');
?>

<div id="edit-category-settings">
  <ul id="error_list"></ul>
  <form name="category_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <?php echo osc_csrf_token_form(); ?>
    <input type="hidden" name="page" value="categories" />
    <input type="hidden" name="action" value="<?php echo customFrmText('action_frm'); ?>" />
    <?php if($isEdit) { ?><input type="hidden" name="id" value="<?php echo (int)$category['pk_i_id']; ?>" /><?php } ?>
    <?php if($parent > 0) { ?><input type="hidden" name="parent" value="<?php echo $parent; ?>" /><?php } ?>
    <?php if(Params::getParam('iDisplayLength') != '') { ?><input type="hidden" name="iDisplayLength" value="<?php echo (int)Params::getParam('iDisplayLength'); ?>" /><?php } ?>
    <?php if(Params::getParam('sort') != '') { ?><input type="hidden" name="sort" value="<?php echo osc_esc_html(Params::getParam('sort')); ?>" /><?php } ?>
    <?php if(Params::getParam('direction') != '') { ?><input type="hidden" name="direction" value="<?php echo osc_esc_html(Params::getParam('direction')); ?>" /><?php } ?>
    <?php if(Params::getParam('iPage') != '') { ?><input type="hidden" name="iPage" value="<?php echo (int)Params::getParam('iPage'); ?>" /><?php } ?>
    <?php if(Params::getParam('sSearch') != '') { ?><input type="hidden" name="sSearch" value="<?php echo osc_esc_html(Params::getParam('sSearch')); ?>" /><?php } ?>

    <?php osc_run_hook('admin_categories_form_top', $category); ?>

    <fieldset>
      <div class="form-horizontal">
        <?php if($isEdit) { ?>
        <div class="form-row">
          <div class="form-label"><?php _e('Category ID'); ?></div>
          <div class="form-controls">
            <input type="text" class="input-small" disabled="disabled" value="<?php echo (int)$category['pk_i_id']; ?>" />
          </div>
        </div>
        <?php } ?>
        <div class="form-row">
          <div class="form-label"><?php _e('Parent category'); ?></div>
          <div class="form-controls">
            <?php CategoryForm::parent_select_admin($categories_tree, $category, $disabled_parent_ids); ?>
            <span class="help-inline"><?php printf(__('Maximum category depth is %d levels. Options beyond this limit are disabled.'), $maxLevels); ?></span>
          </div>
        </div>

        <?php osc_run_hook('admin_categories_form', $category); ?>

        <hr />
        <div class="form-row" style="margin:0;">
          <div class="form-label"></div>
          <div class="form-controls">
            <?php printLocaleTabs($locales); ?>
          </div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('Name'); ?> *</div>
          <div class="form-controls">
            <?php CategoryForm::printLocaleName($locales, $category); ?>
          </div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('Description'); ?></div>
          <div class="form-controls">
            <?php CategoryForm::printLocaleDescription($locales, $category); ?>
          </div>
        </div>
        <hr />
        <div class="form-row">
          <div class="form-label"><?php _e('Icon'); ?></div>
          <div class="form-controls">
            <input class="input-large" type="text" name="s_icon" id="s_icon" value="<?php echo osc_esc_html(isset($category['s_icon']) ? $category['s_icon'] : ''); ?>" />
            <span class="help-inline"><?php _e('URL to an image, Font Awesome classes (e.g. fa fa-star), or inline SVG code.'); ?></span>
          </div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('Color'); ?></div>
          <div class="form-controls">
            <?php CategoryForm::color_input_text($category); ?>
            <span class="help-inline"><?php _e('Hex color code (e.g. #ff0000) used for category styling.'); ?></span>
          </div>
        </div>
        <hr />
        <div class="form-row">
          <div class="form-label"><?php _e('Expiration'); ?></div>
          <div class="form-controls">
            <input class="input-small" type="text" name="i_expiration_days" id="i_expiration_days" value="<?php echo osc_esc_html(isset($category['i_expiration_days']) ? $category['i_expiration_days'] : ''); ?>" />
            <div class="inpt-desc"><?php _e('days'); ?></div>
            <span class="help-inline"><?php _e('Enter days until listings expire, or 0 to disable expiration (non-expiring listings).'); ?></span>
          </div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('Price field'); ?></div>
          <div class="form-controls">
            <?php CategoryForm::price_enabled_select($category); ?>
            <span class="help-inline"><?php _e('When enabled, users can enter a price when posting listings in this category.'); ?></span>
            <?php if($has_subcategories) { ?>
            <label class="apply-subcats-label">
              <?php CategoryForm::apply_changes_to_subcategories($category); ?>
              <?php _e('Apply expiration and price field changes to subcategories'); ?>
            </label>
            <?php } ?>
          </div>
        </div>

        <?php osc_run_hook('admin_categories_form_bottom', $category); ?>

        <div class="form-actions">
          <input type="submit" class="btn btn-submit" value="<?php echo osc_esc_html(customFrmText('btn_text')); ?>" />
          <a class="btn" href="<?php echo osc_esc_html($listUrl); ?>"><?php _e('Cancel'); ?></a>
        </div>
      </div>
    </fieldset>
  </form>
</div>

<?php osc_current_admin_theme_path('parts/footer.php');
