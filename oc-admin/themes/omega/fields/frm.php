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


osc_enqueue_script('jquery-treeview');
osc_enqueue_script('jquery-validate');

$field = __get('field');
$is_add = (bool)__get('is_add');
if(!is_array($field)) {
  $field = array();
}

function customPageHeader() {
  ?>
  <h1><?php _e('Custom fields'); ?></h1>
  <?php
}
osc_add_hook('admin_page_header','customPageHeader');

function customPageTitle($string) {
  global $is_add;
  if($is_add) {
    return sprintf(__('Add custom field - %s'), $string);
  }
  return sprintf(__('Edit custom field - %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');

function customHead() {
  global $is_add;
  ?>
  <script type="text/javascript">
  $(document).ready(function(){
    $("#cat_tree").treeview({
      animated: "fast",
      collapsed: true
    });

    $('select[name="field_type"]').change(function() {
      if($(this).prop('value') == 'DROPDOWN' || $(this).prop('value') == 'RADIO') {
        $('#div_field_options').show();
      } else {
        $('#div_field_options').hide();
      }
    });

    $('select[name="field_type"]').change();

    var rules = {
      's_name': {
        required: true
      }
    };
    <?php if($is_add) { ?>
    rules['field_slug'] = {
      required: true
    };
    <?php } ?>

    $("form[name=field_form]").validate({
      rules: rules,
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

  function checkAll(id, check) {
    $('#' + id + ' input[type=checkbox]').each(function() {
      $(this).prop('checked', check);
    });
  }
  </script>
  <?php
}
osc_add_hook('admin_header','customHead', 10);

$categories = __get('categories');
$selected = __get('selected');
$listUrl = __get('list_url');
if($listUrl == '') {
  $listUrl = osc_admin_base_url(true) . '?page=custom_fields';
}
$formAction = ($is_add ? 'add_post' : 'edit_post');

osc_current_admin_theme_path('parts/header.php');
?>

<div id="edit-field-settings">
  <ul id="error_list"></ul>
  <form name="field_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <?php echo osc_csrf_token_form(); ?>
    <input type="hidden" name="page" value="custom_fields" />
    <input type="hidden" name="action" value="<?php echo osc_esc_html($formAction); ?>" />
    <?php if(!$is_add) { ?><input type="hidden" name="id" value="<?php echo (int)$field['pk_i_id']; ?>" /><?php } ?>
    <?php if(Params::getParam('iDisplayLength') != '') { ?><input type="hidden" name="iDisplayLength" value="<?php echo (int)Params::getParam('iDisplayLength'); ?>" /><?php } ?>
    <?php if(Params::getParam('sort') != '') { ?><input type="hidden" name="sort" value="<?php echo osc_esc_html(Params::getParam('sort')); ?>" /><?php } ?>
    <?php if(Params::getParam('direction') != '') { ?><input type="hidden" name="direction" value="<?php echo osc_esc_html(Params::getParam('direction')); ?>" /><?php } ?>
    <?php if(Params::getParam('iPage') != '') { ?><input type="hidden" name="iPage" value="<?php echo (int)Params::getParam('iPage'); ?>" /><?php } ?>
    <?php if(Params::getParam('sSearch') != '') { ?><input type="hidden" name="sSearch" value="<?php echo osc_esc_html(Params::getParam('sSearch')); ?>" /><?php } ?>

    <?php osc_run_hook('admin_fields_form_top', $field); ?>

    <fieldset>
      <div class="form-horizontal">
        <?php if(!$is_add) { ?>
        <div class="form-row">
          <div class="form-label"><?php _e('Custom field ID'); ?></div>
          <div class="form-controls">
            <input type="text" class="input-small" disabled="disabled" value="<?php echo (int)$field['pk_i_id']; ?>" />
          </div>
        </div>
        <?php } ?>

        <?php osc_run_hook('admin_fields_form', $field); ?>

        <div class="form-row">
          <div class="form-label"><?php _e('Name'); ?> *</div>
          <div class="form-controls cfname"><?php FieldForm::name_input_text($field); ?></div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('Identifier'); ?> *</div>
          <div class="form-controls">
            <input type="text" class="medium" name="field_slug" value="<?php echo osc_esc_html(isset($field['s_slug']) ? $field['s_slug'] : ''); ?>" />
            <span class="help-inline"><?php _e('Only alphanumeric characters are allowed [a-z0-9_-]'); ?></span>
          </div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('Type'); ?></div>
          <div class="form-controls cftype"><?php FieldForm::type_select($field); ?></div>
        </div>
        <div class="form-row" id="div_field_options">
          <div class="form-label"><?php _e('Options'); ?></div>
          <div class="form-controls cfopts">
            <?php FieldForm::options_input_text($field); ?>
            <span class="help-inline"><?php _e('Separate options with commas'); ?></span>
          </div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('Required'); ?></div>
          <div class="form-controls cf-checkbox"><label><?php FieldForm::required_checkbox($field); ?> <span><?php _e('Required field on publish item page'); ?></span></label></div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('Searchable'); ?></div>
          <div class="form-controls cf-checkbox"><label><?php FieldForm::searchable_checkbox($field); ?> <span><?php _e('Added into search form on search page'); ?></span></label></div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('Categories'); ?></div>
          <div class="form-controls">
            <p><?php _e('Select the categories where you want to apply this attribute:'); ?></p>
            <ul id="cat_tree">
              <?php CategoryForm::categories_tree($categories, $selected); ?>
            </ul>
            <p class="check-uncheck">
              <a href="javascript:void(0);" onclick="checkAll('cat_tree', true); return false;"><?php _e('Check all'); ?></a> &middot;
              <a href="javascript:void(0);" onclick="checkAll('cat_tree', false); return false;"><?php _e('Uncheck all'); ?></a>
            </p>
          </div>
        </div>

        <?php osc_run_hook('admin_fields_form_bottom', $field); ?>

        <div class="form-actions">
          <input type="submit" class="btn btn-submit" value="<?php echo osc_esc_html($is_add ? __('Add custom field') : __('Save changes')); ?>" />
          <a class="btn" href="<?php echo osc_esc_html($listUrl); ?>"><?php _e('Cancel'); ?></a>
        </div>
      </div>
    </fieldset>
  </form>
</div>

<?php osc_current_admin_theme_path('parts/footer.php');
