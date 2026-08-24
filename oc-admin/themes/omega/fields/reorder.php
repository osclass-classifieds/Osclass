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
osc_enqueue_script('jquery-ui-backoffice');

$fields = __get('fields');

function addHelp() {
  echo '<p>' . __('Drag and drop custom fields to reorder them. Use Quick edit for inline changes or Edit for the full form.') . '</p>';
}
osc_add_hook('help_box','addHelp');

function customPageHeader(){
  ?>
  <h1><?php _e('Custom fields'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
    <a href="<?php echo osc_esc_html(osc_admin_base_url(true) . '?page=custom_fields&amp;action=add'); ?>" class="btn btn-green ico ico-add-white float-right"><?php _e('Add custom field'); ?></a>
    <a href="<?php echo osc_esc_html(osc_admin_base_url(true) . '?page=custom_fields'); ?>" class="btn btn-white float-right"><?php _e('Manage custom fields'); ?></a>
  </h1>
  <?php
}
osc_add_hook('admin_page_header','customPageHeader');

function customHead() {
  $csrf_token = osc_csrf_token_url();
  ?>
<script type="text/javascript">
  function showFieldsAdminMessage(type, message) {
    var $box = $('.jsMessage');
    $box.removeClass('hide flashmessage-info flashmessage-ok flashmessage-error flashmessage-warning');
    if(type === 'ok') {
      $box.addClass('flashmessage-ok');
    } else if(type === 'error') {
      $box.addClass('flashmessage-error');
    } else {
      $box.addClass('flashmessage-info');
    }
    $box.find('p').attr('class', type).html(message);
    $box.show().fadeIn('fast');
  }

  $(function() {
    var list_original = '';

    $('.sortable').sortable({
      axis: "y",
      forcePlaceholderSize: true,
      handle: '.handle',
      helper: 'clone',
      items: 'li',
      opacity: .8,
      placeholder: 'placeholder',
      revert: 100,
      tabSize: 5,
      tolerance: 'intersect',
      start: function(event, ui) {
        list_original = $(this).sortable('serialize');
      },
      stop: function (event, ui) {
        showFieldsAdminMessage('info', '<img height="16" width="16" src="<?php echo osc_current_admin_theme_url('images/loading.gif');?>"> <?php echo osc_esc_js(__('This action could take a while.')); ?>');

        var c_list_original = $(this).sortable('serialize');

        if(list_original != c_list_original) {
          $.ajax({
            url: "<?php echo osc_admin_base_url(true) . '?page=ajax&action=cfields_order&' . osc_csrf_token_url(); ?>",
            type: "POST",
            data: c_list_original,
            success: function(res){
              var ret = eval( "(" + res + ")");
              var message = "";
              if(ret.error) {
                message += ret.error;
                showFieldsAdminMessage('error', message);
              } else if(ret.ok) {
                message += ret.ok;
                showFieldsAdminMessage('ok', message);
              }
            },
            error: function(){
              showFieldsAdminMessage('error', '<?php echo osc_esc_js(__('Ajax error, please try again.')); ?>');
            }
          });
        }
      }
    });

    list_original = $('.sortable').sortable('serialize');
  });

  function show_iframe(class_name, id, callback) {
    if($('.content_list_'+id+' .custom-field-frame').length == 0){
      $('.custom-field-frame').remove();
      var url  = '<?php echo osc_admin_base_url(true); ?>?page=ajax&action=field_categories_iframe&<?php echo $csrf_token; ?>&id=' + id;
      $.ajax({
        url: url,
        context: document.body,
        success: function(res){
          $('div.'+class_name).html(res);
          $('div.'+class_name).fadeIn("fast", function() {
            if(typeof callback === 'function') {
              callback();
            }
          });
        }
      });
    } else {
      $('.custom-field-frame').remove();
      if(typeof callback === 'function') {
        callback();
      }
    }
    return false;
  }

  function delete_field(id) {
    $("#dialog-delete-field").attr('data-field-id', id);
    $("#dialog-delete-field").dialog('open');
    return false;
  }

  function checkAll(id, check) {
    $('#' + id + ' input[type=checkbox]').each(function() {
      $(this).prop('checked', check);
    });
  }

  $(document).ready(function() {
    $('.cfield-div').on('mouseenter',function(){
      $(this).addClass('cfield-hover');
    }).on('mouseleave',function(){
      $(this).removeClass('cfield-hover');
    });

    $("#dialog-delete-field").dialog({
      autoOpen: false,
      modal: true
    });

    $("#field-delete-submit").click(function() {
      var id  = $("#dialog-delete-field").attr('data-field-id');
      var url = '<?php echo osc_admin_base_url(true); ?>?page=ajax&action=delete_field&<?php echo $csrf_token; ?>&id=' + id;
      $.ajax({
        url: url,
        context: document.body,
        success: function(res){
          var ret = eval( "(" + res + ")");
          if(ret.error) {
            showFieldsAdminMessage('error', ret.error);
          } else if(ret.ok) {
            showFieldsAdminMessage('ok', ret.ok);
            $('#list_'+id).fadeOut("slow");
            $('#list_'+id).remove();
          }
        },
        error: function(){
          showFieldsAdminMessage('error', '<?php echo osc_esc_js(__("Ajax error, try again.")); ?>');
        }
      });
      $('#dialog-delete-field').dialog('close');
      return false;
    });

    $("#add-button, .add-button").bind('click', function() {
      $.ajax({
        url: '<?php echo osc_admin_base_url(true); ?>?page=ajax&action=add_field&<?php echo $csrf_token; ?>',
        context: document.body,
        success: function(res){
          var ret = eval( "(" + res + ")");
          if(ret.error==0) {
            var rowClass = ($('#ul_fields > li').length % 2 === 0 ? 'even' : 'odd');
            var fieldName = $('<div/>').text(ret.field_name).html();
            var html = '';
            html += '<li id="list_'+ret.field_id+'" class="field_li '+rowClass+'">';
              html += '<div class="cfield-div" field_id="'+ret.field_id+'" >';
                html += '<div class="handle ico ico-32 ico-droppable"></div>';
                html += '<div class="name-edit-cfield" id="quick_edit_'+ret.field_id+'">';
                  html += fieldName;
                html += '</div>';
                html += '<div class="actions-edit-cfield">';
                  html += '<a href="javascript:void(0);" onclick="show_iframe(\'content_list_'+ret.field_id+'\',\''+ret.field_id+'\');"><?php echo osc_esc_js(__('Quick edit')); ?></a>';
                  html += ' &middot; ';
                  html += '<a href="<?php echo osc_admin_base_url(true); ?>?page=custom_fields&amp;action=edit&amp;id='+ret.field_id+'"><?php echo osc_esc_js(__('Edit')); ?></a>';
                  html += ' &middot; ';
                  html += '<a href="javascript:void(0);" onclick="delete_field(\''+ret.field_id+'\');"><?php echo osc_esc_js(__('Delete')); ?></a>';
                html += '</div>';
                html += '<div class="edit content_list_'+ret.field_id+'"></div>';
              html += '</div>';
            html += '</li>';
            $("#fields-empty").remove();
            $("#ul_fields").append(html);
            if(ret.ok) {
              showFieldsAdminMessage('ok', ret.ok);
            }
            show_iframe('content_list_'+ret.field_id, ret.field_id, function() {
              var $newItem = $('#list_'+ret.field_id);
              if($newItem.length && $newItem.offset()) {
                $('html, body').animate({scrollTop: $newItem.offset().top - 80}, 1000);
              }
            });
          } else {
            showFieldsAdminMessage('error', ret.message ? ret.message : '<?php echo osc_esc_js(__('Custom field could not be added')); ?>');
          }
        }
      });
      return false;
    });
  });
</script>
  <?php
}
osc_add_hook('admin_header','customHead', 10);

function customPageTitle($string) {
  return sprintf(__('Quick management - %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php');
?>

<div class="flashmessage flashmessage-info">
  <p class="info"><?php _e('Drag & drop the custom fields to reorder them. Use Quick edit for inline changes or Edit for the full form.'); ?></p>
</div>

<div class="header_title">
  <h2 class="render-title"><?php _e('Quick management'); ?> <a href="javascript:void(0);" class="btn btn-mini add-button" id="add-button"><?php _e('Add new'); ?></a></h2>
</div>

<div class="custom-fields">
  <div class="list-fields">
    <ul id="ul_fields" class="sortable">
    <?php $even = true;
    if(count($fields) == 0) { ?>
      <span id="fields-empty"><?php _e("You don't have any custom fields yet"); ?></span>
    <?php } else {
      foreach($fields as $field) { ?>
        <li id="list_<?php echo $field['pk_i_id']; ?>" class="field_li <?php echo ($even ? 'even' : 'odd'); ?>">
          <div class="cfield-div" field_id="<?php echo $field['pk_i_id']; ?>" >
            <div class="handle ico ico-32 ico-droppable"></div>
            <div class="name-edit-cfield" id="<?php echo 'quick_edit_' . $field['pk_i_id']; ?>">
              <?php echo osc_esc_html($field['s_name']); ?>
            </div>
            <div class="actions-edit-cfield">
              <a href="javascript:void(0);" onclick="show_iframe('content_list_<?php echo $field['pk_i_id']; ?>','<?php echo $field['pk_i_id']; ?>');"><?php _e('Quick edit'); ?></a>
              &middot;
              <a href="<?php echo osc_esc_html(osc_admin_base_url(true) . '?page=custom_fields&action=edit&id=' . (int)$field['pk_i_id']); ?>"><?php _e('Edit'); ?></a>
              &middot;
              <a href="javascript:void(0);" onclick="delete_field('<?php echo $field['pk_i_id']; ?>');"><?php _e('Delete'); ?></a>
            </div>
            <div class="edit content_list_<?php echo $field['pk_i_id']; ?>"></div>
          </div>
        </li>
        <?php $even = !$even; }
    } ?>
    </ul>
  </div>
</div>

<div class="clear"></div>

<div id="dialog-delete-field" title="<?php echo osc_esc_html(__('Delete custom field')); ?>" class="has-form-actions hide" data-field-id="">
  <div class="form-horizontal">
    <div class="form-row"><?php _e('Are you sure you want to delete this custom field?'); ?></div>
    <div class="form-actions">
      <div class="wrapper">
        <a id="field-delete-submit" href="javascript:void(0);" class="btn btn-submit"><?php echo osc_esc_html(__('Delete')); ?></a>
        <a class="btn" href="javascript:void(0);" onclick="$('#dialog-delete-field').dialog('close');"><?php _e('Cancel'); ?></a>
      </div>
    </div>
  </div>
</div>

<?php osc_current_admin_theme_path('parts/footer.php');
