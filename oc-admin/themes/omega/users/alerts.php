<?php
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
  echo '<p>' . __('Add, edit or delete information associated to alerts.') . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader(){ 
  ?>
  <h1><?php _e('Alerts'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Manage alerts - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');


//customize Head
function customHead() { 
  ?>
  <script type="text/javascript">
  $(document).ready(function(){
    //tooltip
    $('.more-tooltip').each(function(){
      $(this).osc_tooltip($(this).attr("details"), {layout:'gray-tooltip',position:{x:'right', y:'middle'}});
    });


    // Modal
    $('body').on('click', '.alert-popup', function(e){
      e.preventDefault();
      var btn = $(this);

      $("#alert_id").text(btn.attr('data-id'));
      $("#alert_secret").text(btn.attr('data-secret'));
      $("#alert_conditions").text(btn.attr('data-conditions'));
      $("#alert_params").text(btn.attr('data-params'));
      $("#alert_sql").text(btn.attr('data-sql'));
      $("#alert_secret").text(btn.attr('data-secret'));

      var dialogWidth = 700;
      var dialogHeight = 540;
      
      if($(window).width() < 740) {
        dialogWidth = $(window).width() - 40;
      }
      
      $('#alert_details').dialog({
        modal: true,
        title: '<?php echo osc_esc_js(__('Alert details')); ?>',
        width: dialogWidth,
        height: dialogHeight
      });

      return false;
    });
    
    // check_all bulkactions
    $("#check_all").change(function(){
      var isChecked = $(this).prop("checked");
      $('.col-bulkactions input').each( function() {
        if( isChecked == 1 ) {
          this.checked = true;
        } else {
          this.checked = false;
        }
      });
    });

    // dialog delete
    $("#dialog-alert-delete").dialog({
      autoOpen: false,
      modal: true
    });

    // dialog bulk actions
    $("#dialog-bulk-actions").dialog({
      autoOpen: false,
      modal: true
    });
    
    $("#bulk-actions-submit").click(function() {
      if($("#bulk_actions").attr("value")=="delete") {
        $("#action").attr("value", "delete_alerts");
      } else if($("#bulk_actions").attr("value")=="activate") {
        $("#action").attr("value", "status_alerts");
        $("#status").attr("value", "1");
      } else {
        $("#action").attr("value", "status_alerts");
        $("#status").attr("value", "0");
      }
      
      $("#datatablesForm").submit();
    });
    
    $("#bulk-actions-cancel").click(function() {
      $("#datatablesForm").attr('data-dialog-open', 'false');
      $('#dialog-bulk-actions').dialog('close');
    });
    
    // dialog bulk actions function
    $("#datatablesForm").submit(function() {
      if( $("#bulk_actions option:selected").val() == "" ) {
        return false;
      }

      if( $("#datatablesForm").attr('data-dialog-open') == "true" ) {
        return true;
      }

      $("#dialog-bulk-actions .form-row").html($("#bulk_actions option:selected").attr('data-dialog-content'));
      $("#bulk-actions-submit").html($("#bulk_actions option:selected").text());
      $("#datatablesForm").attr('data-dialog-open', 'true');
      $("#dialog-bulk-actions").dialog('open');
      return false;
    });
    // /dialog bulk actions
  });

  // dialog delete function
  function delete_alert(id) {
    $("#alert_id").attr('value', id);
    $("#dialog-alert-delete").dialog('open');
  };

  </script>
  <?php
}

osc_add_hook('admin_header','customHead', 10);


$aData = __get('aData');
$aRawRows = __get('aRawRows');
$sort = Params::getParam('sort');
$direction = Params::getParam('direction');

$columns = $aData['aColumns'];
$rows = $aData['aRows'];

osc_current_admin_theme_path( 'parts/header.php' ); 
?>

<h2 class="render-title"><?php _e('Manage alerts'); ?></h2>
<div class="relative">
  <div id="users-toolbar" class="table-toolbar">
    <div class="float-right">
      <form method="get" action="<?php echo osc_admin_base_url(true); ?>" id="shortcut-filters" class="inline">
        <input type="hidden" name="page" value="users" />
        <input type="hidden" name="action" value="alerts" />
        <input 
          id="fPattern" type="text" name="sSearch"
          value="<?php echo osc_esc_html(Params::getParam('sSearch')); ?>" 
          class="input-text input-actions"/>
        <input type="submit" class="btn submit-right" value="<?php echo osc_esc_html( __('Find') ); ?>">
      </form>
    </div>
  </div>
  
  <form class="" id="datatablesForm" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="page" value="users" />
    <input type="hidden" name="action" id="action" value="status_alerts" />
    <input type="hidden" name="status" id="status" value="0" />
    
    <div id="bulk-actions">
      <label>
        <select name="alert_action" id="bulk_actions" class="select-box-extra">
          <option value=""><?php _e('Bulk Actions'); ?></option>
          <option value="activate" data-dialog-content="<?php printf(__('Are you sure you want to %s the selected alerts?'), strtolower(__('Activate'))); ?>"><?php _e('Activate'); ?></option>
          <option value="deactivate" data-dialog-content="<?php printf(__('Are you sure you want to %s the selected alerts?'), strtolower(__('Deactivate'))); ?>"><?php _e('Deactivate'); ?></option>
          <option value="delete" data-dialog-content="<?php printf(__('Are you sure you want to %s the selected alerts?'), strtolower(__('Delete'))); ?>"><?php _e('Delete'); ?></option>
        </select> <input type="submit" id="bulk_apply" class="btn" value="<?php echo osc_esc_html( __('Apply') ); ?>" />
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
        <?php if( count($rows) > 0 ) { ?>
          <?php foreach($rows as $key => $row) { ?>
            <tr class="<?php echo implode(' ', osc_apply_filter('datatable_alert_class', array(), $aRawRows[$key], $row)); ?>">
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

<?php 
  function showingResults(){
    $aData = __get('aData');
    echo '<ul class="showing-results"><li><span>'.osc_pagination_showing((Params::getParam('iPage')-1)*$aData['iDisplayLength']+1, ((Params::getParam('iPage')-1)*$aData['iDisplayLength'])+count($aData['aRows']), $aData['iTotalDisplayRecords'], $aData['iTotalRecords']).'</span></li></ul>';
  }
  osc_add_hook('before_show_pagination_admin','showingResults');
  osc_show_pagination_admin($aData);
?>
<form id="dialog-alert-delete" method="get" action="<?php echo osc_admin_base_url(true); ?>" class="has-form-actions hide" title="<?php echo osc_esc_html(__('Delete alert')); ?>">
  <input type="hidden" name="page" value="users" />
  <input type="hidden" name="action" value="delete_alerts" />
  <input type="hidden" name="alert_id[]" id="alert_id" value="" />
  <input type="hidden" name="alert_user_id" value="" />
  <div class="form-horizontal">
    <div class="form-row">
      <?php _e('Are you sure you want to delete this alert?'); ?>
    </div>
    <div class="form-actions">
      <div class="wrapper">
        <input id="alert-delete-submit" type="submit" value="<?php echo osc_esc_html( __('Delete') ); ?>" class="btn btn-submit" />
        <a class="btn" href="javascript:void(0);" onclick="$('#dialog-alert-delete').dialog('close');"><?php _e('Cancel'); ?></a>
      </div>
    </div>
  </div>
</form>
<div id="dialog-bulk-actions" title="<?php _e('Bulk actions'); ?>" class="has-form-actions hide">
  <div class="form-horizontal">
    <div class="form-row"></div>
    <div class="form-actions">
      <div class="wrapper">
        <a id="bulk-actions-submit" href="javascript:void(0);" class="btn btn-submit" ><?php echo osc_esc_html( __('Delete') ); ?></a>
        <a id="bulk-actions-cancel" class="btn" href="javascript:void(0);"><?php _e('Cancel'); ?></a>
        <div class="clear"></div>
      </div>
    </div>
  </div>
</div>

<div id="more-tooltip"></div>


<div id="alert_details" class="hide">
  <div class="osc-modal-content-alert">
    <table class="table" cellpadding="0" cellspacing="0">
      <tbody>

        <tr class="table-first-row">
          <td><strong><?php _e('ID'); ?></strong></td>
          <td><span id="alert_id" class="alert-modal-id"></span></td>
        </tr>
        
        <tr class="even">
          <td><strong><?php _e('Secret'); ?></strong></td>
          <td><span id="alert_secret" class="alert-modal-secret"></span></td>
        </tr>

        <tr>
          <td><strong><?php _e('Conditions'); ?></strong></td>
          <td><span id="alert_conditions" class="alert-modal-code"></span></td>
        </tr>
        
        <tr class="even">
          <td><strong><?php _e('Parameters'); ?></strong></td>
          <td><span id="alert_params" class="alert-modal-code"></span></td>
        </tr>
        
        <tr>
          <td><strong><?php _e('SQL'); ?></strong></td>
          <td><span id="alert_sql" class="alert-modal-code"></span></td>
        </tr>
        

        
        
      </tbody>
    </table>
    <div class="clear"></div>
  </div>
</div>

<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>