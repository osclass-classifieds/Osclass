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


function addHelp() {
  echo '<p>' . __("View logs created by Osclass, themes, or plugins detailing actions performed on your items, users, and other elements..") . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader(){ 
  ?> 
  <h1><?php _e('Action logs'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Tools - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

//customize Head
function customHead() { 
  ?>
  <script type="text/javascript">
    $(document).ready(function(){
      // check_all bulkactions
      $("#check_all").change(function(){
        var isChecked = $(this).prop("checked");
        $('.col-bulkactions input').each(function() {
          if(isChecked == 1) {
            this.checked = true;
          } else {
            this.checked = false;
          }
        });
      });

      // dialog delete
      $("#dialog-log-delete").dialog({
        autoOpen: false,
        modal: true
      });

      // dialog bulk actions
      $("#dialog-bulk-actions").dialog({
        autoOpen: false,
        modal: true
      });
      
      $("#bulk-actions-submit").click(function() {
        $("#datatablesForm").submit();
      });
      
      $("#bulk-actions-cancel").click(function() {
        $("#datatablesForm").attr('data-dialog-open', 'false');
        $('#dialog-bulk-actions').dialog('close');
      });
      
      // dialog bulk actions function
      $("#datatablesForm").submit(function() {
        if($("#bulk_actions option:selected").val() == "") {
          return false;
        }

        if($("#datatablesForm").attr('data-dialog-open') == "true") {
          return true;
        }

        $("#dialog-bulk-actions .form-row").html($("#bulk_actions option:selected").attr('data-dialog-content'));
        $("#bulk-actions-submit").html($("#bulk_actions option:selected").text());
        $("#datatablesForm").attr('data-dialog-open', 'true');
        $("#dialog-bulk-actions").dialog('open');
        
        return false;
      });
      // dialog bulk actions
    });

    // dialog delete function
    function delete_dialog(item_id) {
      $("#dialog-log-delete input[name='id[]']").attr('value', item_id);
      $("#dialog-log-delete").dialog('open');
      return false;
    }
    
    // show/hide details (json)
    function show_hide_log_details(elem, uniqueId) {
      var htmlShow = document.getElementById("details-" + uniqueId);

      if(htmlShow.style.display === "none") {
        htmlShow.style.display = "block";
      } else {
        htmlShow.style.display = "none";
      }
      
      window.scrollBy(0, 26);     // fix toolbar

      return false;
    }
  </script>
  <?php
}

osc_add_hook('admin_header','customHead', 10);

$iDisplayLength = __get('iDisplayLength');

$aData = __get('aData');
$aRawRows = __get('aRawRows');
$sort = Params::getParam('sort');
$direction = Params::getParam('direction');

$columns = $aData['aColumns'];
$rows = $aData['aRows'];

osc_current_admin_theme_path('parts/header.php'); 
?>

<h2 class="render-title"><?php _e('Manage logs'); ?></h2>
<div class="relative" id="logs-list">
  <div id="users-toolbar" class="table-toolbar">
    <div class="float-right"></div>

    <div class="display-select-top">
      <form method="get" action="<?php echo osc_admin_base_url(true); ?>" class="inline nocsrf" id="shortcut-filters">
        <?php foreach(Params::getParamsAsArray('get') as $key => $value) { ?>
          <?php if($key != 'iDisplayLength' && $key != 'sSearch') { ?>
            <input type="hidden" name="<?php echo osc_esc_html(strip_tags($key)); ?>" value="<?php echo osc_esc_html(strip_tags($value)); ?>" />
          <?php } ?>
        <?php } ?>
        
        <input type="text" name="sSearch" id="fPattern" class="input-text input-actions" value="<?php echo osc_esc_html(strip_tags(Params::getParam('sSearch'))); ?>" placeholder="<?php echo osc_esc_html(__('Search for a log')); ?>" />
        <input type="submit" class="btn submit-right" value="<?php echo osc_esc_html(__('Find')); ?>">
        
        <select name="iDisplayLength" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();" >
          <option value="10" <?php if(Params::getParam('iDisplayLength') == 10) echo 'selected'; ?> ><?php printf(__('%d logs'), 10); ?></option>
          <option value="25" <?php if(Params::getParam('iDisplayLength') == 25) echo 'selected'; ?> ><?php printf(__('%d logs'), 25); ?></option>
          <option value="50" <?php if(Params::getParam('iDisplayLength') == 50) echo 'selected'; ?> ><?php printf(__('%d logs'), 50); ?></option>
          <option value="100" <?php if(Params::getParam('iDisplayLength') == 100) echo 'selected'; ?> ><?php printf(__('%d logs'), 100); ?></option>
          <option value="500" <?php if(Params::getParam('iDisplayLength') == 500) echo 'selected'; ?> ><?php printf(__('%d logs'), 500); ?></option>
        </select>
      </form>
    </div>
  </div>
  
  <form class="" id="datatablesForm" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="page" value="tools" />

    <div id="bulk-actions">
      <label>
        <?php osc_print_bulk_actions('bulk_actions', 'action', __get('bulk_options'), 'select-box-extra'); ?>
        <input type="submit" id="bulk_apply" class="btn" value="<?php echo osc_esc_html(__('Apply')); ?>" />
      </label>
    </div>
    <div class="table-contains-actions">
      <table class="table" cellpadding="0" cellspacing="0">
        <thead>
          <tr>
            <?php foreach($columns as $k => $v) {
              echo '<th class="col-'.$k.' '.($sort==$k?($direction=='desc'?'sorting_desc':'sorting_asc'):'').'">'.$v.'</th>';
            }; ?>
          </tr>
        </thead>
        <tbody>
          <?php if(count($rows) > 0) { ?>
            <?php foreach($rows as $key => $row) { ?>
              <tr>
                <?php foreach($row as $k => $v) { ?>
                  <td class="col-<?php echo $k; ?>"><?php echo $v; ?></td>
                <?php }; ?>
              </tr>
            <?php }; ?>
          <?php } else { ?>
            <tr>
              <td colspan="5" class="text-center">
              <p><?php _e('No data available in table'); ?></p>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
      <div id="table-row-actions"></div> <!-- used for table actions -->
    </div>
  </form>
</div>

<div id="plg-show-bot">
  <?php
    function showingResults(){
      $aData = __get("aData");
      echo '<ul class="showing-results"><li><span>'.osc_pagination_showing((Params::getParam('iPage')-1)*$aData['iDisplayLength']+1, ((Params::getParam('iPage')-1)*$aData['iDisplayLength'])+count($aData['aRows']), $aData['iTotalDisplayRecords'], $aData['iTotalRecords']).'</span></li></ul>';
    }
    osc_add_hook('before_show_pagination_admin','showingResults');
    osc_show_pagination_admin($aData);
  ?>

  <div class="display-select-bottom">
    <form method="get" action="<?php echo osc_admin_base_url(true); ?>"  class="inline nocsrf">
      <?php foreach(Params::getParamsAsArray('get') as $key => $value) { ?>
        <?php if($key != 'iDisplayLength') { ?>
          <input type="hidden" name="<?php echo osc_esc_html(strip_tags($key)); ?>" value="<?php echo osc_esc_html(strip_tags($value)); ?>" />
        <?php } ?>
      <?php } ?>
      
      <select name="iDisplayLength" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();" >
        <option value="10" <?php if(Params::getParam('iDisplayLength') == 10) echo 'selected'; ?> ><?php printf(__('%d logs'), 10); ?></option>
        <option value="25" <?php if(Params::getParam('iDisplayLength') == 25) echo 'selected'; ?> ><?php printf(__('%d logs'), 25); ?></option>
        <option value="50" <?php if(Params::getParam('iDisplayLength') == 50) echo 'selected'; ?> ><?php printf(__('%d logs'), 50); ?></option>
        <option value="100" <?php if(Params::getParam('iDisplayLength') == 100) echo 'selected'; ?> ><?php printf(__('%d logs'), 100); ?></option>
        <option value="500" <?php if(Params::getParam('iDisplayLength') == 500) echo 'selected'; ?> ><?php printf(__('%d logs'), 500); ?></option>
      </select>
    </form>
  </div>
</div>

<form id="dialog-log-delete" method="get" action="<?php echo osc_admin_base_url(true); ?>" class="has-form-actions hide" title="<?php echo osc_esc_html(__('Delete rule')); ?>">
  <input type="hidden" name="page" value="tools" />
  <input type="hidden" name="action" value="logs_delete" />
  <input type="hidden" name="id[]" value="" />
  <div class="form-horizontal">
    <div class="form-row">
      <?php _e('Are you sure you want to delete this log?'); ?>
    </div>
    <div class="form-actions">
      <div class="wrapper">
      <input id="log-delete-submit" type="submit" value="<?php echo osc_esc_html(__('Delete')); ?>" class="btn btn-submit" />
      <a class="btn" href="javascript:void(0);" onclick="$('#dialog-log-delete').dialog('close');"><?php _e('Cancel'); ?></a>
      </div>
    </div>
  </div>
</form>

<div id="dialog-bulk-actions" title="<?php _e('Bulk actions'); ?>" class="has-form-actions hide">
  <div class="form-horizontal">
    <div class="form-row"></div>
    <div class="form-actions">
      <div class="wrapper">
        <a id="bulk-actions-submit" href="javascript:void(0);" class="btn btn-submit" ><?php echo osc_esc_html(__('Delete')); ?></a>
        <a id="bulk-actions-cancel" class="btn" href="javascript:void(0);"><?php _e('Cancel'); ?></a>
        <div class="clear"></div>
      </div>
    </div>
  </div>
</div>

<?php osc_current_admin_theme_path('parts/footer.php'); ?>