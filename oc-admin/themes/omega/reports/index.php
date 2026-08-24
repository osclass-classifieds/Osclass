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
  echo '<p>' . __('Manage reports submitted by users on listings, users or other content: review, reply, resolve, reject or delete them. Filter by status or user.') . '</p>';
  echo '<p>' . __('Website contact form messages can be stored as Contact form reports with no listing or user target.') . '</p>';
  echo '<p><strong>' . __('Report lifetime') . '</strong></p>';
  echo '<p>' . __('A report stays open while status is Submitted, In progress, On hold or Awaiting feedback. Resolved, Rejected and Cancelled close the report. You can reopen a closed report (status goes back to In progress).') . '</p>';
  echo '<p><strong>' . __('Statuses') . '</strong></p>';
  echo '<ul>';
  echo '<li><strong>' . osc_report_status_label('submitted') . '</strong>: ' . __('New report waiting for review.') . '</li>';
  echo '<li><strong>' . osc_report_status_label('in_review') . '</strong>: ' . __('You are reviewing or handling the report.') . '</li>';
  echo '<li><strong>' . osc_report_status_label('on_hold') . '</strong>: ' . __('Paused; no action expected right now.') . '</li>';
  echo '<li><strong>' . osc_report_status_label('awaiting_feedback') . '</strong>: ' . __('Reported user can reply on the front report page (if feedback is enabled).') . '</li>';
  echo '<li><strong>' . osc_report_status_label('resolved') . '</strong> / <strong>' . osc_report_status_label('rejected') . '</strong> / <strong>' . osc_report_status_label('cancelled') . '</strong>: ' . __('Close the report.') . '</li>';
  echo '</ul>';
  echo '<p>' . __('The user who created the report has no front access to it. Only the reported user (and admins) can open the front report page.') . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader(){ ?>
  <h1><?php _e('Reports'); ?>
    <a href="<?php echo osc_admin_base_url(true) . '?page=reports&action=settings'; ?>" class="btn btn-green ico float-right"><?php _e('Settings'); ?></a>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Manage reports - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');


//customize Head
function customHead() { ?>
  <script type="text/javascript">
    $(document).ready(function(){
      // check_all bulkactions
      $("#check_all").change(function(){
        var isChecked = $(this).prop("checked");
        $('.col-bulkactions input').each( function() {
          if(isChecked == 1 ) {
            this.checked = true;
          } else {
            this.checked = false;
          }
        });
      });

      // dialog delete
      $("#dialog-report-delete").dialog({
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
        if($("#bulk_actions option:selected").val() == "" ) {
          return false;
        }

        if($("#datatablesForm").attr('data-dialog-open') == "true" ) {
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
    function delete_dialog(item_id) {
      $("#dialog-report-delete input[name='id']").attr('value', item_id);
      $("#dialog-report-delete").dialog('open');
      return false;
    }
  </script>
  <?php
}

osc_add_hook('admin_header','customHead', 10);


$aData    = __get('aData');
$aRawRows   = __get('aRawRows');
$sort     = Params::getParam('sort');
$direction  = Params::getParam('direction');

$columns  = $aData['aColumns'];
$columnSources = (isset($aData['aColumnSources']) ? $aData['aColumnSources'] : array());
$sortableColumns = (isset($aData['aSortableColumns']) ? $aData['aSortableColumns'] : array());
$rows     = $aData['aRows'];
$hasActiveFilters = false;
$filterExclude = array('page', 'action', 'iDisplayLength', 'sort', 'direction', 'iPage');

foreach(Params::getParamsAsArray('get') as $key => $value) {
  if(in_array($key, $filterExclude, true)) {
    continue;
  }

  if(is_array($value)) {
    foreach($value as $v) {
      if(trim((string)$v) != '') {
        $hasActiveFilters = true;
        break 2;
      }
    }
  } else if(trim((string)$value) != '') {
    $hasActiveFilters = true;
    break;
  }
}

osc_current_admin_theme_path( 'parts/header.php' );
?>

<h2 class="render-title">
  <?php
    if(Params::getParam('reporterId') > 0) {
      echo sprintf(__('Reports by reporter #%d'), Params::getParam('reporterId'));
    } else if(Params::getParam('reportedUserId') > 0) {
      echo sprintf(__('Reports of user #%d'), Params::getParam('reportedUserId'));
    } else if(Params::getParam('itemId') > 0) {
      echo sprintf(__('Reports of listing #%d'), Params::getParam('itemId'));
    } else if(Params::getParam('userId') > 0) {
      echo sprintf(__('Reports related to user #%d'), Params::getParam('userId'));
    } else if(Params::getParam('reason') != '') {
      echo sprintf(__('Reports with reason: %s'), osc_report_reason_label(Params::getParam('reason')));
    } else {
      _e('Manage reports');
    }
  ?>
</h2>

<div class="relative">
  <div id="listing-toolbar">
    <div class="float-right">
      <form method="get" action="<?php echo osc_admin_base_url(true); ?>" class="inline nocsrf">
        <?php foreach(Params::getParamsAsArray('get') as $key => $value ) { ?>
          <?php if($key != 'iDisplayLength') { ?>
            <input type="hidden" name="<?php echo osc_esc_html(strip_tags($key)); ?>" value="<?php echo osc_esc_html(strip_tags($value)); ?>" />
          <?php } ?>
        <?php } ?>
        <select name="iDisplayLength" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();" >
          <option value="10" <?php if(Params::getParam('iDisplayLength') == 10 ) echo 'selected'; ?> ><?php printf(__('%d Reports'), 10); ?></option>
          <option value="25" <?php if(Params::getParam('iDisplayLength') == 25 ) echo 'selected'; ?> ><?php printf(__('%d Reports'), 25); ?></option>
          <option value="50" <?php if(Params::getParam('iDisplayLength') == 50 ) echo 'selected'; ?> ><?php printf(__('%d Reports'), 50); ?></option>
          <option value="100" <?php if(Params::getParam('iDisplayLength') == 100 ) echo 'selected'; ?> ><?php printf(__('%d Reports'), 100); ?></option>
        </select>
      </form>
      <form method="get" action="<?php echo osc_admin_base_url(true); ?>" id="shortcut-filters" class="inline nocsrf">
        <input type="hidden" name="page" value="reports" />
        <?php if(Params::getParam('userId') != '') { ?><input type="hidden" name="userId" value="<?php echo (int)Params::getParam('userId'); ?>" /><?php } ?>
        <?php if(Params::getParam('itemId') != '') { ?><input type="hidden" name="itemId" value="<?php echo (int)Params::getParam('itemId'); ?>" /><?php } ?>
        <?php if(Params::getParam('reporterId') != '') { ?><input type="hidden" name="reporterId" value="<?php echo (int)Params::getParam('reporterId'); ?>" /><?php } ?>
        <?php if(Params::getParam('reportedUserId') != '') { ?><input type="hidden" name="reportedUserId" value="<?php echo (int)Params::getParam('reportedUserId'); ?>" /><?php } ?>
        <?php if(Params::getParam('reportedId') != '') { ?><input type="hidden" name="reportedId" value="<?php echo (int)Params::getParam('reportedId'); ?>" /><?php } ?>
        <?php if(Params::getParam('reason') != '') { ?><input type="hidden" name="reason" value="<?php echo osc_esc_html(Params::getParam('reason')); ?>" /><?php } ?>
        <?php if(Params::getParam('type') != '') { ?><input type="hidden" name="type" value="<?php echo osc_esc_html(Params::getParam('type')); ?>" /><?php } ?>
        <?php if(Params::getParam('unseen') != '') { ?><input type="hidden" name="unseen" value="<?php echo osc_esc_html(Params::getParam('unseen')); ?>" /><?php } ?>
        <input type="hidden" name="iDisplayLength" value="<?php echo (int)Params::getParam('iDisplayLength'); ?>" />
        <?php if($hasActiveFilters) { ?>
          <a id="btn-hide-filters" href="<?php echo osc_admin_base_url(true); ?>?page=reports" class="btn"><?php _e('Reset filters'); ?></a>
        <?php } ?>
        <select name="status" id="report-filter-status" class="select-box-extra select-box-medium" onchange="this.form.submit();">
          <option value=""><?php _e('All statuses'); ?></option>
          <option value="open" <?php if(Params::getParam('status') == 'open') echo 'selected'; ?>><?php _e('Open'); ?></option>
          <option value="closed" <?php if(Params::getParam('status') == 'closed') echo 'selected'; ?>><?php _e('Closed'); ?></option>
          <?php foreach(osc_report_statuses_enabled() as $code => $label) { ?>
            <option value="<?php echo osc_esc_html($code); ?>" <?php if(Params::getParam('status') == $code) echo 'selected'; ?>><?php echo $label; ?></option>
          <?php } ?>
        </select>
        <input id="fPattern" type="text" name="sSearch" value="<?php echo osc_esc_html(Params::getParam('sSearch')); ?>" class="input-text input-actions" placeholder="<?php echo osc_esc_html(__('Search report')); ?>" />
        <input type="submit" class="btn submit-right" value="<?php echo osc_esc_html(__('Find')); ?>">
        <?php osc_run_hook('admin_reports_filters'); ?>
      </form>
      <?php if(Params::getParam('unseen') == '') { ?>
        <a href="<?php echo osc_admin_base_url(true) . '?page=reports&unseen=1'; ?>" class="btn hidden-commetns"><?php _e('New replies');?></a>
      <?php } else { ?>
        <a href="<?php echo osc_admin_base_url(true) . '?page=reports'; ?>" class="btn hidden-commetns"><?php _e('All reports');?></a>
      <?php } ?>
    </div>
  </div>
  <form class="" id="datatablesForm" action="<?php echo osc_admin_base_url(true); ?>" method="post" data-dialog-open="false">
    <input type="hidden" name="page" value="reports" />
    <input type="hidden" name="action" value="bulk_actions" />
    <div id="bulk-actions">
      <label>
        <?php osc_print_bulk_actions('bulk_actions', 'bulk_actions', __get('bulk_options'), 'select-box-extra'); ?>
        <input type="submit" id="bulk_apply" class="btn" value="<?php echo osc_esc_html( __('Apply') ); ?>" />
      </label>
    </div>
    <div class="table-contains-actions">
      <table class="table" cellpadding="0" cellspacing="0">
        <thead>
          <tr>
            <?php foreach($columns as $k => $v) {
              $sourceCol = (isset($columnSources[$k]) ? $columnSources[$k] : '');
              $isSortable = ((in_array($k, $sortableColumns, true) || strpos((string)$v, 'sort=') !== false) ? 'is-sortable' : '');
              echo '<th class="col-'.$k.' '.$isSortable.' '.($sort==$k?($direction=='desc'?'sort-desc':'sort-asc'):'').'" data-source-col="' . osc_esc_html($sourceCol) . '">' . $v . '</th>';
            } ?>
          </tr>
        </thead>
        <tbody>
        <?php if(count($rows) > 0 ) { ?>
          <?php foreach($rows as $key => $row) { ?>
            <tr class="<?php echo implode(' ', osc_apply_filter('datatable_report_class', array(), $aRawRows[$key], $row)); ?>">
              <?php foreach($row as $k => $v) { ?>
                <td class="col-<?php echo $k; ?>"><?php echo $v; ?></td>
              <?php } ?>
            </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td colspan="<?php echo max(1, count($columns)); ?>" class="text-center">
            <p class="table-empty-message"><?php _e('No data available in table'); ?></p>
            </td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
      <div id="table-row-actions"></div><!-- used for table actions -->
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
<div class="display-select-bottom">
  <form method="get" action="<?php echo osc_admin_base_url(true); ?>" class="inline nocsrf">
    <?php foreach(Params::getParamsAsArray('get') as $key => $value ) { ?>
      <?php if($key != 'iDisplayLength') { ?>
        <input type="hidden" name="<?php echo osc_esc_html(strip_tags($key)); ?>" value="<?php echo osc_esc_html(strip_tags($value)); ?>" />
      <?php } ?>
    <?php } ?>
    <select name="iDisplayLength" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();" >
      <option value="10" <?php if(Params::getParam('iDisplayLength') == 10 ) echo 'selected'; ?> ><?php printf(__('%d Reports'), 10); ?></option>
      <option value="25" <?php if(Params::getParam('iDisplayLength') == 25 ) echo 'selected'; ?> ><?php printf(__('%d Reports'), 25); ?></option>
      <option value="50" <?php if(Params::getParam('iDisplayLength') == 50 ) echo 'selected'; ?> ><?php printf(__('%d Reports'), 50); ?></option>
      <option value="100" <?php if(Params::getParam('iDisplayLength') == 100 ) echo 'selected'; ?> ><?php printf(__('%d Reports'), 100); ?></option>
    </select>
  </form>
</div>
<form id="dialog-report-delete" method="get" action="<?php echo osc_admin_base_url(true); ?>" class="has-form-actions hide" title="<?php echo osc_esc_html(__('Delete report')); ?>">
  <input type="hidden" name="page" value="reports" />
  <input type="hidden" name="action" value="delete" />
  <input type="hidden" name="id" value="" />
  <div class="form-horizontal">
    <div class="form-row">
      <?php _e('Are you sure you want to delete this report? Report replies and attachment will be removed too.'); ?>
    </div>
    <div class="form-actions">
      <div class="wrapper">
      <input id="report-delete-submit" type="submit" value="<?php echo osc_esc_html( __('Delete') ); ?>" class="btn btn-submit" />
      <a class="btn" href="javascript:void(0);" onclick="$('#dialog-report-delete').dialog('close');"><?php _e('Cancel'); ?></a>
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
<?php osc_current_admin_theme_path( 'parts/footer.php' );
