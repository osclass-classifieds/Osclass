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
  echo '<p>' . __('With the mail queue on, emails wait in the queue and the cron sends them in small batches. Codes, password resets and account emails are still sent right away and listed here too.') . '</p>';
  echo '<p>' . __('An email that cannot be sent is tried again after 3, 10 and 60 minutes, then it is marked as failed. Open an email to see its text and every attempt with the error.') . '</p>';
  echo '<p>' . __('The queue needs a real cron on your server that calls the cron URL at least every 5 minutes. Without it, queued emails are not sent.') . '</p>';
  echo '<p>' . __('No cron yet? Click Send pending emails to send the waiting emails yourself. Each click sends one batch, so the server is not overloaded.') . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader() {
  ?>
  <h1><?php _e('Mail queue'); ?>
    <a href="<?php echo osc_admin_base_url(true); ?>?page=settings&amp;action=mailserver#mail-queue" class="btn ico ico-32 ico-engine float-right" title="<?php echo osc_esc_html(__('Mail queue settings')); ?>"></a>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
    <?php if(osc_mail_queue_stats()['pending'] > 0 && !osc_mail_queue_paused()) { ?>
      <a href="<?php echo osc_esc_html(osc_admin_base_url(true) . '?page=settings&action=mailqueue_action&' . osc_csrf_token_url() . '&do=process'); ?>" class="btn btn-green ico float-right" title="<?php echo osc_esc_html(sprintf(__('Sends up to %d due emails now, the same batch the cron sends. Click again for the next batch.'), osc_mail_queue_per_run())); ?>"><?php _e('Send pending emails'); ?></a>
    <?php } ?>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return __('Mail queue') . ' - ' . $string;
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
          this.checked = (isChecked == 1);
        });
      });

      // dialog delete
      $("#dialog-mail-delete").dialog({
        autoOpen: false,
        modal: true,
        title: '<?php echo osc_esc_js(__('Delete email')); ?>'
      });

      // dialog cancel group
      $("#dialog-mail-cancel-group").dialog({
        autoOpen: false,
        modal: true,
        title: '<?php echo osc_esc_js(__('Cancel pending emails of this group')); ?>'
      });

      // dialog bulk actions
      $("#dialog-bulk-actions").dialog({
        autoOpen: false,
        modal: true
      });

      $("#bulk-actions-submit").click(function() {
        if($("#bulk-actions-submit").prop("clicked") == false) {
          $("#bulk-actions-submit").prop("clicked", true);
          $("#datatablesForm").submit();
        }
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
        $("#bulk-actions-submit").prop("clicked", false);
        $("#dialog-bulk-actions").dialog('open');
        return false;
      });

      var dialogWidth = 680;

      if($(window).width() < 720) {
        dialogWidth = $(window).width() - 40;
      }

      // dialog filters
      $('#display-filters').dialog({
        autoOpen: false,
        modal: true,
        width: dialogWidth,
        title: '<?php echo osc_esc_js(__('Filters')); ?>',
        dialogClass: 'item-filters'
      });

      $('#btn-display-filters').click(function(){
        $('#display-filters').dialog('open');
        return false;
      });
    });

    // dialog delete function
    function delete_dialog(id) {
      $("#dialog-mail-delete input[name='id[]']").attr('value', id);
      $("#dialog-mail-delete").dialog('open');
      return false;
    }

    // dialog cancel group function
    function cancel_group_dialog(link) {
      var group = $(link).attr('data-group');
      $("#dialog-mail-cancel-group input[name='group']").attr('value', group);
      $("#dialog-mail-cancel-group .mail-queue-group-name").text(group);
      $("#dialog-mail-cancel-group").dialog('open');
      return false;
    }
  </script>
  <?php
}

osc_add_hook('admin_header','customHead', 10);

$iDisplayLength = __get('iDisplayLength');
$aData = __get('aData');
$aRawRows = __get('aRawRows');
$stats = __get('mail_queue_stats');
$types = __get('mail_queue_types');
$withFilters = __get('withFilters');
$sort = Params::getParam('sort');
$direction = Params::getParam('direction');
$status = strtolower((string)Params::getParam('status'));

$columns = $aData['aColumns'];
$columnSources = (isset($aData['aColumnSources']) ? $aData['aColumnSources'] : array());
$sortableColumns = (isset($aData['aSortableColumns']) ? $aData['aSortableColumns'] : array());
$rows = $aData['aRows'];

$date_format = osc_date_format() . ' ' . osc_time_format();
$list_url = osc_admin_base_url(true) . '?page=settings&action=mailqueue';
$action_url = osc_admin_base_url(true) . '?page=settings&action=mailqueue_action&' . osc_csrf_token_url() . '&do=';
$status_options = array('' => __('All emails'), 'pending' => __('Pending'), 'sending' => __('Sending'), 'sent' => __('Sent'), 'failed' => __('Failed'), 'cancelled' => __('Cancelled'));
// Last cron run as "5 min ago"; late = more than 3 intervals (at least 15 minutes)
$cron_age = ($stats['last_run'] != '' ? max(0, time() - strtotime($stats['last_run'])) : -1);
$cron_late = ($cron_age < 0 || $cron_age > max(900, osc_cron_minutely_interval() * 180));
if($cron_age < 0) {
  $cron_ago = __('Never');
} else if($cron_age < 60) {
  $cron_ago = __('Just now');
} else if($cron_age < 3600) {
  $cron_ago = sprintf(_n('%d minute ago', '%d minutes ago', floor($cron_age / 60)), floor($cron_age / 60));
} else if($cron_age < 86400) {
  $cron_ago = sprintf(_n('%d hour ago', '%d hours ago', floor($cron_age / 3600)), floor($cron_age / 3600));
} else {
  $cron_ago = sprintf(_n('%d day ago', '%d days ago', floor($cron_age / 86400)), floor($cron_age / 86400));
}
$filter_params = array('mailId', 'email', 'type', 'group', 'ref', 'priority', 'date_from', 'date_to');

osc_current_admin_theme_path('parts/header.php');
?>

<?php if(!$stats['enabled']) { ?>
  <div class="flashmessage flashmessage-inline flashmessage-info">
    <p><?php printf(__('The mail queue is off, so emails are sent right away as before. Turn it on in the <a href="%s">mail queue settings</a> only when a real cron runs on your server.'), osc_admin_base_url(true) . '?page=settings&amp;action=mailserver#mail-queue'); ?></p>
  </div>
<?php } ?>

<?php if($stats['late']) { ?>
  <div class="flashmessage flashmessage-inline flashmessage-error">
    <p><?php printf(__('Emails are waiting too long. The oldest one should have been sent at %s. Check that your cron runs (last run: %s).'), osc_esc_html(osc_format_date($stats['oldest_due'], $date_format)), ($stats['last_run'] != '' ? osc_esc_html(osc_format_date($stats['last_run'], $date_format)) : __('Never'))); ?></p>
  </div>
<?php } ?>

<?php if($stats['paused']) { ?>
  <div class="flashmessage flashmessage-inline flashmessage-warning">
    <p><?php _e('Sending is paused. New emails still wait in the queue.'); ?></p>
  </div>
<?php } ?>

<form method="get" action="<?php echo osc_admin_base_url(true); ?>" id="display-filters" class="has-form-actions hide nocsrf">
  <input type="hidden" name="page" value="settings" />
  <input type="hidden" name="action" value="mailqueue" />
  <input type="hidden" name="status" value="<?php echo osc_esc_html($status); ?>" />
  <input type="hidden" name="iDisplayLength" value="<?php echo (int)$iDisplayLength; ?>" />
  <input type="hidden" name="sort" value="<?php echo osc_esc_html($sort); ?>" />
  <input type="hidden" name="direction" value="<?php echo osc_esc_html($direction); ?>" />
  <div class="form-horizontal">
    <div class="grid-system">
      <div class="grid-row grid-50">
        <div class="row-wrapper">
          <div class="form-row">
            <div class="form-label"><?php _e('Recipient email'); ?></div>
            <div class="form-controls">
              <input type="text" name="email" value="<?php echo osc_esc_html(Params::getParam('email')); ?>" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-label"><?php _e('Type'); ?></div>
            <div class="form-controls">
              <select name="type">
                <option value=""><?php _e('Choose an option'); ?></option>
                <?php foreach($types as $type) { ?>
                  <option value="<?php echo osc_esc_html($type); ?>" <?php echo (Params::getParam('type') == $type ? 'selected="selected"' : ''); ?>><?php echo osc_esc_html($type); ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-label"><?php _e('Group'); ?></div>
            <div class="form-controls">
              <input type="text" name="group" value="<?php echo osc_esc_html(Params::getParam('group')); ?>" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-label"><?php _e('Reference'); ?></div>
            <div class="form-controls">
              <input type="text" name="ref" value="<?php echo osc_esc_html(Params::getParam('ref')); ?>" />
            </div>
          </div>
        </div>
      </div>
      <div class="grid-row grid-50">
        <div class="row-wrapper">
          <div class="form-row">
            <div class="form-label"><?php _e('Priority'); ?></div>
            <div class="form-controls">
              <select name="priority">
                <option value=""><?php _e('Choose an option'); ?></option>
                <option value="1" <?php echo (Params::getParam('priority') == 1 ? 'selected="selected"' : ''); ?>><?php _e('Instant'); ?></option>
                <option value="2" <?php echo (Params::getParam('priority') == 2 ? 'selected="selected"' : ''); ?>><?php _e('Normal'); ?></option>
                <option value="3" <?php echo (Params::getParam('priority') == 3 ? 'selected="selected"' : ''); ?>><?php _e('Bulk'); ?></option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-label"><?php _e('Created from'); ?></div>
            <div class="form-controls">
              <input type="date" name="date_from" value="<?php echo osc_esc_html(Params::getParam('date_from')); ?>" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-label"><?php _e('Created to'); ?></div>
            <div class="form-controls">
              <input type="date" name="date_to" value="<?php echo osc_esc_html(Params::getParam('date_to')); ?>" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-label"><?php _e('Email ID'); ?></div>
            <div class="form-controls">
              <input type="text" name="mailId" value="<?php echo osc_esc_html(Params::getParam('mailId')); ?>" />
            </div>
          </div>
        </div>
      </div>
      <div class="clear"></div>
    </div>
  </div>
  <div class="form-actions">
    <div class="wrapper">
      <input id="show-filters" type="submit" value="<?php echo osc_esc_html(__('Apply filters')); ?>" class="btn btn-submit" />
      <a class="btn" href="<?php echo osc_esc_html($list_url); ?>"><?php _e('Reset filters'); ?></a>
    </div>
  </div>
</form>

<h2 class="render-title"><?php _e('Manage emails'); ?>
  <?php if($stats['paused']) { ?>
    <a href="<?php echo osc_esc_html($action_url . 'resume'); ?>" class="btn btn-mini mail-queue-resume"><?php _e('Resume sending'); ?></a>
  <?php } else { ?>
    <a href="<?php echo osc_esc_html($action_url . 'pause'); ?>" class="btn btn-mini mail-queue-pause"><?php _e('Pause sending'); ?></a>
  <?php } ?>
</h2>

<div class="mail-queue-health">
  <div>
    <span class="mail-queue-label"><?php _e('Waiting'); ?></span>
    <strong><?php echo (int)$stats['pending']; ?></strong>
    <span class="mail-queue-sub"><?php echo ($stats['oldest_due'] != '' ? sprintf(__('Oldest due: %s'), osc_esc_html(osc_format_date($stats['oldest_due'], $date_format))) : __('Nothing is due')); ?></span>
  </div>
  <div>
    <span class="mail-queue-label"><?php _e('Sent in last 24 hours'); ?></span>
    <strong><?php echo (int)$stats['sent_24h']; ?></strong>
  </div>
  <div class="<?php echo ($stats['failed_24h'] > 0 ? 'mail-queue-bad' : ''); ?>">
    <span class="mail-queue-label"><?php _e('Failed in last 24 hours'); ?></span>
    <strong><?php echo (int)$stats['failed_24h']; ?></strong>
  </div>
  <div class="<?php echo ($cron_late ? 'mail-queue-bad' : ''); ?>">
    <span class="mail-queue-label"><?php _e('Last cron run'); ?></span>
    <strong title="<?php echo osc_esc_html($stats['last_run'] != '' ? osc_format_date($stats['last_run'], $date_format) : ''); ?>"><?php echo $cron_ago; ?></strong>
    <span class="mail-queue-sub"><?php echo ($cron_late ? __('The cron does not run, queued emails are not sent') : sprintf(__('Runs every %d min'), osc_cron_minutely_interval())); ?></span>
  </div>
</div>

<div class="relative">
  <div id="mail-queue-toolbar" class="table-toolbar">
    <div class="float-right">
      <form method="get" action="<?php echo osc_admin_base_url(true); ?>" class="inline nocsrf">
        <?php foreach(Params::getParamsAsArray('get') as $key => $value) { ?>
          <?php if($key != 'iDisplayLength' && $key != 'iPage') { ?>
            <input type="hidden" name="<?php echo osc_esc_html(strip_tags($key)); ?>" value="<?php echo osc_esc_html(strip_tags($value)); ?>" />
          <?php } ?>
        <?php } ?>
        <select name="iDisplayLength" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();">
          <?php foreach(array(10, 25, 50, 100, 500) as $size) { ?>
            <option value="<?php echo $size; ?>" <?php if(Params::getParam('iDisplayLength') == $size) echo 'selected'; ?>><?php printf(__('%d emails'), $size); ?></option>
          <?php } ?>
        </select>
      </form>
      <form method="get" action="<?php echo osc_admin_base_url(true); ?>" id="shortcut-filters" class="inline nocsrf">
        <input type="hidden" name="page" value="settings" />
        <input type="hidden" name="action" value="mailqueue" />
        <input type="hidden" name="iDisplayLength" value="<?php echo (int)$iDisplayLength; ?>" />
        <?php foreach($filter_params as $key) { ?>
          <?php if(Params::getParam($key) != '') { ?>
            <input type="hidden" name="<?php echo $key; ?>" value="<?php echo osc_esc_html(strip_tags(Params::getParam($key))); ?>" />
          <?php } ?>
        <?php } ?>
        <select name="status" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();" aria-label="<?php echo osc_esc_html(__('Status')); ?>">
          <?php foreach($status_options as $key => $label) { ?>
            <option value="<?php echo $key; ?>" <?php if($status == $key) echo 'selected'; ?>><?php echo $label; ?> (<?php echo (int)($key != '' ? $stats[$key] : $stats['total']); ?>)</option>
          <?php } ?>
        </select>
        <?php if($withFilters) { ?>
          <a id="btn-hide-filters" href="<?php echo osc_esc_html($list_url . ($status != '' ? '&status=' . $status : '')); ?>" class="btn"><?php _e('Reset filters'); ?></a>
        <?php } ?>
        <a id="btn-display-filters" href="#" class="btn<?php if($withFilters) { echo ' btn-red'; } ?>"><?php _e('Show filters'); ?></a>
        <input type="text" name="sSearch" id="fPattern" class="input-text input-actions" value="<?php echo osc_esc_html(strip_tags(Params::getParam('sSearch'))); ?>" placeholder="<?php echo osc_esc_html(__('Search recipient or subject')); ?>" />
        <input type="submit" class="btn submit-right" value="<?php echo osc_esc_html(__('Find')); ?>">
      </form>
    </div>
  </div>

  <form class="" id="datatablesForm" action="<?php echo osc_admin_base_url(true); ?>" method="post" data-dialog-open="false">
    <?php echo osc_csrf_token_form(); ?>
    <input type="hidden" name="page" value="settings" />
    <input type="hidden" name="action" value="mailqueue_action" />

    <div id="bulk-actions">
      <label>
        <?php osc_print_bulk_actions('bulk_actions', 'do', __get('bulk_options'), 'select-box-extra'); ?>
        <input type="submit" id="bulk_apply" class="btn" value="<?php echo osc_esc_html(__('Apply')); ?>" />
      </label>
    </div>
    <div class="table-contains-actions-wrap">
      <table class="table table-contains-actions" cellpadding="0" cellspacing="0">
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
          <?php if(count($rows) > 0) { ?>
            <?php foreach($rows as $key => $row) { ?>
              <tr class="<?php echo implode(' ', osc_apply_filter('datatable_mail_queue_class', array(), $aRawRows[$key], $row)); ?>">
                <?php foreach($row as $k => $v) { ?>
                  <td class="col-<?php echo $k; ?>"><?php echo $v; ?></td>
                <?php } ?>
              </tr>
            <?php } ?>
          <?php } else { ?>
            <tr>
              <td colspan="<?php echo max(1, count($columns)); ?>" class="text-center">
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
  function showingResults() {
    $aData = __get('aData');
    echo '<ul class="showing-results"><li><span>'.osc_pagination_showing((Params::getParam('iPage')-1)*$aData['iDisplayLength']+1, ((Params::getParam('iPage')-1)*$aData['iDisplayLength'])+count($aData['aRows']), $aData['iTotalDisplayRecords'], $aData['iTotalRecords']).'</span></li></ul>';
  }

  osc_add_hook('before_show_pagination_admin','showingResults');
  osc_show_pagination_admin($aData);
?>

<div class="display-select-bottom">
  <form method="get" action="<?php echo osc_admin_base_url(true); ?>" class="inline nocsrf">
    <?php foreach(Params::getParamsAsArray('get') as $key => $value) { ?>
      <?php if($key != 'iDisplayLength' && $key != 'iPage') { ?>
        <input type="hidden" name="<?php echo osc_esc_html(strip_tags($key)); ?>" value="<?php echo osc_esc_html(strip_tags($value)); ?>" />
      <?php } ?>
    <?php } ?>
    <select name="iDisplayLength" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();">
      <?php foreach(array(10, 25, 50, 100, 500) as $size) { ?>
        <option value="<?php echo $size; ?>" <?php if(Params::getParam('iDisplayLength') == $size) echo 'selected'; ?>><?php printf(__('%d emails'), $size); ?></option>
      <?php } ?>
    </select>
  </form>
</div>

<form id="dialog-mail-delete" method="post" action="<?php echo osc_admin_base_url(true); ?>" class="has-form-actions hide">
  <?php echo osc_csrf_token_form(); ?>
  <input type="hidden" name="page" value="settings" />
  <input type="hidden" name="action" value="mailqueue_action" />
  <input type="hidden" name="do" value="delete" />
  <input type="hidden" name="id[]" value="" />
  <div class="form-horizontal">
    <div class="form-row">
      <?php _e('Are you sure you want to delete this email? Its history is deleted too.'); ?>
    </div>
    <div class="form-actions">
      <div class="wrapper">
        <input type="submit" value="<?php echo osc_esc_html(__('Delete')); ?>" class="btn btn-submit" />
        <a class="btn" href="javascript:void(0);" onclick="$('#dialog-mail-delete').dialog('close');"><?php _e('Cancel'); ?></a>
      </div>
    </div>
  </div>
</form>

<form id="dialog-mail-cancel-group" method="post" action="<?php echo osc_admin_base_url(true); ?>" class="has-form-actions hide">
  <?php echo osc_csrf_token_form(); ?>
  <input type="hidden" name="page" value="settings" />
  <input type="hidden" name="action" value="mailqueue_action" />
  <input type="hidden" name="do" value="cancel_group" />
  <input type="hidden" name="group" value="" />
  <div class="form-horizontal">
    <div class="form-row">
      <?php printf(__('Are you sure you want to cancel all pending emails of the group %s?'), '<strong class="mail-queue-group-name"></strong>'); ?>
    </div>
    <div class="form-actions">
      <div class="wrapper">
        <input type="submit" value="<?php echo osc_esc_html(__('Cancel emails')); ?>" class="btn btn-submit" />
        <a class="btn" href="javascript:void(0);" onclick="$('#dialog-mail-cancel-group').dialog('close');"><?php _e('Close'); ?></a>
      </div>
    </div>
  </div>
</form>

<div id="dialog-bulk-actions" title="<?php _e('Bulk actions'); ?>" class="has-form-actions hide">
  <div class="form-horizontal">
    <div class="form-row"></div>
    <div class="form-actions">
      <div class="wrapper">
        <a id="bulk-actions-submit" href="javascript:void(0);" class="btn btn-submit"><?php echo osc_esc_html(__('Delete')); ?></a>
        <a id="bulk-actions-cancel" class="btn" href="javascript:void(0);"><?php _e('Cancel'); ?></a>
        <div class="clear"></div>
      </div>
    </div>
  </div>
</div>

<?php osc_current_admin_theme_path('parts/footer.php'); ?>
