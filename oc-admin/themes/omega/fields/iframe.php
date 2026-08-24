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


$field = __get('field');
$categories = __get('categories');
$selected = __get('selected');
if(!is_array($field)) {
  $field = array();
}
$field = array_merge(array(
  'pk_i_id' => 0,
  's_name' => '',
  's_slug' => '',
  'e_type' => 'TEXT',
  'b_required' => 0,
  'b_searchable' => 0,
  's_options' => ''
), $field);
$fieldId = (int)$field['pk_i_id'];
?>

<div id="edit-custom-field-frame" class="custom-field-frame">
  <div class="form-horizontal">
    <form id="nedit_field_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="page" value="ajax" />
    <input type="hidden" name="action" value="field_categories_post" />
    <?php FieldForm::primary_input_hidden($field); ?>
    <fieldset>
      <div class="form-row">
        <div class="form-label"><?php _e('Custom field ID'); ?></div>
        <div class="form-controls">
          <input type="text" class="input-small" disabled="disabled" value="<?php echo $fieldId; ?>" />
        </div>
      </div>
      <div class="form-row">
        <div class="form-label"><?php _e('Name'); ?> *</div>
        <div class="form-controls cfname"><?php FieldForm::name_input_text($field); ?></div>
      </div>
      <div class="form-row">
        <div class="form-label"><?php _e('Identifier'); ?> *</div>
        <div class="form-controls">
          <input type="text" class="medium" name="field_slug" value="<?php echo osc_esc_html((string)$field['s_slug']); ?>" />
          <p class="help-inline"><?php _e('Only alphanumeric characters are allowed [a-z0-9_-]'); ?></p>
        </div>
      </div>
      <div class="form-row">
        <div class="form-label"><?php _e('Type'); ?></div>
        <div class="form-controls cftype"><?php FieldForm::type_select($field); ?></div>
      </div>
      <div class="form-row" id="div_field_options_iframe">
        <div class="form-label"><?php _e('Options'); ?></div>
        <div class="form-controls cfopts">
          <?php FieldForm::options_input_text($field); ?>
          <p class="help-inline"><?php _e('Separate options with commas'); ?></p>
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
      <div class="form-actions">
        <input type="submit" id="cfield_save" value="<?php echo osc_esc_html(__('Save changes')); ?>" class="btn btn-submit" />
        <input type="button" value="<?php echo osc_esc_html(__('Cancel')); ?>" class="btn btn-red" onclick="$('#edit-custom-field-frame').remove();" />
      </div>
    </fieldset>
  </form>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function(){
    $("#cat_tree").treeview({
      animated: "fast",
      collapsed: true
    });

    $('select[name="field_type"]').change(function() {
      if($(this).prop('value') == 'DROPDOWN' || $(this).prop('value') == 'RADIO' ) {
        $('#div_field_options_iframe').show();
      } else {
        $('#div_field_options_iframe').hide();
      }
    });

    $('select[name="field_type"]').change();

    $('#edit-custom-field-frame form').submit(function() {
      $.ajax({
        type: 'POST',
        url: '<?php echo osc_admin_base_url(true); ?>',
        data: $(this).serialize(),
        success: function(data) {
          var ret = eval( "(" + data + ")");

          if(ret.error) {
            if(typeof showFieldsAdminMessage === 'function') {
              showFieldsAdminMessage('error', ret.error);
            }
          } else if(ret.ok) {
            if(typeof showFieldsAdminMessage === 'function') {
              showFieldsAdminMessage('ok', ret.ok);
            }
            $('#quick_edit_'+ret.field_id).html(ret.text);
          }

          $('div.content_list_'+$('#nedit_field_form input[name="id"]').val()).html('');
        },
        error: function(){
          if(typeof showFieldsAdminMessage === 'function') {
            showFieldsAdminMessage('error', '<?php echo osc_esc_js(__('Ajax error, try again.')); ?>');
          }
        }
      });
      return false;
    });
  });
</script>
