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
  echo '<p>' . __('Details of one email: recipients, status, every attempt with its error, and the text as it was sent.') . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader() {
  ?>
  <h1><?php _e('Mail queue'); ?>
    <a href="<?php echo osc_admin_base_url(true); ?>?page=settings&amp;action=mailserver#mail-queue" class="btn ico ico-32 ico-engine float-right" title="<?php echo osc_esc_html(__('Mail queue settings')); ?>"></a>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
    <a href="<?php echo osc_admin_base_url(true); ?>?page=settings&amp;action=mailqueue" class="btn float-right mail-queue-head-btn"><?php _e('Back to list'); ?></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Email #%d'), (int)Params::getParam('id')) . ' - ' . __('Mail queue') . ' - ' . $string;
}

osc_add_filter('admin_title', 'customPageTitle');


//customize Head
function customHead() {
  ?>
  <script type="text/javascript">
    $(document).ready(function(){
      $("#dialog-mail-delete").dialog({
        autoOpen: false,
        modal: true,
        title: '<?php echo osc_esc_js(__('Delete email')); ?>'
      });

      $(".mail-queue-views a").click(function() {
        $(".mail-queue-views a").removeClass("btn-submit");
        $(this).addClass("btn-submit");
        $(".mail-queue-body").css("width", $(this).data("width"));
        return false;
      });

      $("#mail-delete").click(function() {
        $("#dialog-mail-delete").dialog('open');
        return false;
      });
    });
  </script>
  <?php
}

osc_add_hook('admin_header','customHead', 10);

$mail = __get('mail');
$params = __get('mail_params');
$logs = __get('mail_logs');
$id = (int)$mail['pk_i_id'];
$status = (string)$mail['e_status'];
$date_format = osc_date_format() . ' ' . osc_time_format();
$priorities = array(1 => __('Instant'), 2 => __('Normal'), 3 => __('Bulk'));
$status_labels = osc_mail_queue_status_labels();
$status_classes = array('PENDING' => 'status-warning', 'SENDING' => 'status-info', 'SENT' => 'status-success', 'FAILED' => 'status-blocked', 'CANCELLED' => 'status-inactive');
$action_url = osc_admin_base_url(true) . '?page=settings&action=mailqueue_action&from=view&id[]=' . $id . '&' . osc_csrf_token_url() . '&do=';
$list_url = osc_admin_base_url(true) . '?page=settings&action=mailqueue';

// Recipients as "Name <email>"
$to = (isset($params['to']) ? $params['to'] : '');
$to = (is_array($to) ? $to : array((string)$to => (isset($params['to_name']) ? (string)$params['to_name'] : '')));
$recipients = array();
foreach($to as $email => $name) {
  $recipients[] = trim($name != '' ? $name . ' <' . $email . '>' : (string)$email);
}

$bcc = (isset($params['add_bcc']) ? (is_array($params['add_bcc']) ? $params['add_bcc'] : array($params['add_bcc'])) : array());
$attachments = array();
if(!empty($params['attachment'])) {
  foreach(((!is_array($params['attachment']) || isset($params['attachment']['path'])) ? array($params['attachment']) : $params['attachment']) as $a) {
    $attachments[] = (is_array($a) ? (isset($a['name']) ? $a['name'] : (isset($a['path']) ? basename($a['path']) : '')) : basename((string)$a));
  }
}

$send_after = '';
if($status == 'PENDING') {
  $send_after = osc_format_date($mail['dt_send_after'], $date_format);
}

$details = array(
  __('Recipient') => implode(', ', $recipients),
  __('Bcc') => implode(', ', array_map('strval', $bcc)),
  __('Reply to') => (isset($params['reply_to']) ? (string)$params['reply_to'] : ''),
  __('Subject') => (string)$mail['s_subject'],
  __('Attachments') => implode(', ', $attachments),
  __('Type') => (string)$mail['s_type'],
  __('Priority') => (isset($priorities[(int)$mail['i_priority']]) ? $priorities[(int)$mail['i_priority']] : (int)$mail['i_priority']),
  __('Group') => (string)$mail['s_group'],
  __('Reference') => (string)$mail['s_ref'],
  __('Attempts') => sprintf(__('%1$d of %2$d'), (int)$mail['i_attempts'], max(osc_mail_queue_max_attempts(), (int)$mail['i_attempts'])),
  __('Created') => osc_format_date($mail['dt_created'], $date_format),
  __('Scheduled') => $send_after,
  __('Expires') => ($mail['dt_expire'] != '' ? osc_format_date($mail['dt_expire'], $date_format) : ''),
  __('Finished') => ($mail['dt_done'] != '' ? osc_format_date($mail['dt_done'], $date_format) : ''),
  __('Last error') => (string)$mail['s_error']
);

// History events: system text, admin text
$events = array(
  'CREATED' => array(__('Added to the queue'), __('Added to the queue')),
  'SENT' => array(__('Sent'), __('Marked as sent')),
  'ERROR' => array(__('Not sent, will try again'), __('Not sent, will try again')),
  'FAILED' => array(__('Failed, no more attempts'), __('Marked as failed')),
  'CANCELLED' => array(__('Cancelled'), __('Cancelled')),
  'EXPIRED' => array(__('Expired before it could be sent'), __('Expired before it could be sent')),
  'INTERRUPTED' => array(__('Sending was interrupted, back in the queue'), __('Sending was interrupted, back in the queue')),
  'PENDING' => array(__('Back in the queue'), __('Marked as pending'))
);

// Preview as the customer gets it: html and plain text. Plugins that style the email at send time return the finished versions here
$preview = osc_apply_filter('mail_queue_preview', array('html' => (isset($params['body']) ? (string)$params['body'] : ''), 'text' => (isset($params['alt_body']) ? (string)$params['alt_body'] : '')), $mail, $params);
$body = (string)$preview['html'];

osc_current_admin_theme_path('parts/header.php');
?>

<h2 class="render-title"><?php printf(__('Email #%d'), $id); ?></h2>

<div id="mail-queue-view" class="form-horizontal">
  <div class="form-row">
    <div class="form-label"><?php _e('Status'); ?></div>
    <div class="form-controls">
      <span class="mail-queue-status <?php echo (isset($status_classes[$status]) ? $status_classes[$status] : 'status-inactive'); ?>"><?php echo (isset($status_labels[$status]) ? $status_labels[$status] : osc_esc_html($status)); ?></span>
    </div>
  </div>

  <?php foreach($details as $label => $value) { ?>
    <?php if((string)$value !== '') { ?>
      <div class="form-row">
        <div class="form-label"><?php echo $label; ?></div>
        <div class="form-controls"><?php echo osc_esc_html((string)$value); ?></div>
      </div>
    <?php } ?>
  <?php } ?>

  <?php if($status != 'SENDING') { ?>
    <div class="form-actions">
      <?php if(osc_mail_queue_can_send($mail)) { ?>
        <a class="btn btn-submit" href="<?php echo osc_esc_html($action_url . 'send'); ?>"><?php echo ($status == 'SENT' ? __('Send again') : __('Send now')); ?></a>
      <?php } ?>
      <?php if(in_array($status, array('PENDING', 'FAILED'))) { ?>
        <a class="btn" href="<?php echo osc_esc_html($action_url . 'cancel'); ?>"><?php _e('Cancel'); ?></a>
      <?php } ?>
      <?php if($status != 'PENDING' && osc_mail_queue_can_send($mail)) { ?>
        <a class="btn" href="<?php echo osc_esc_html($action_url . 'pending'); ?>"><?php _e('Mark as pending'); ?></a>
      <?php } ?>
      <?php if($status != 'SENT') { ?>
        <a class="btn" href="<?php echo osc_esc_html($action_url . 'sent'); ?>"><?php _e('Mark as sent'); ?></a>
      <?php } ?>
      <?php if($status != 'FAILED') { ?>
        <a class="btn" href="<?php echo osc_esc_html($action_url . 'failed'); ?>"><?php _e('Mark as failed'); ?></a>
      <?php } ?>
      <a id="mail-delete" class="btn btn-red" href="#"><?php _e('Delete'); ?></a>
    </div>
  <?php } else { ?>
    <div class="flashmessage flashmessage-inline flashmessage-info">
      <p><?php _e('This email is being sent right now. Reload the page in a moment to see the result.'); ?></p>
    </div>
  <?php } ?>
</div>

<h2 class="render-title separate-top"><?php _e('History'); ?></h2>
<div class="table-contains-actions-wrap">
  <table class="table mail-queue-history" cellpadding="0" cellspacing="0">
    <thead>
      <tr>
        <th class="col-date"><?php _e('Date'); ?></th>
        <th class="col-event"><?php _e('Event'); ?></th>
        <th class="col-attempt"><?php _e('Attempt'); ?></th>
        <th class="col-duration"><?php _e('Duration'); ?></th>
        <th class="col-message"><?php _e('Details'); ?></th>
        <th class="col-who"><?php _e('Who'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php if(count($logs) > 0) { ?>
        <?php foreach($logs as $log) { ?>
          <?php $by_admin = ((int)$log['fk_i_admin_id'] > 0); ?>
          <?php $manual = ($by_admin && ($log['i_duration'] === null || $log['i_duration'] === '')); ?>
          <tr class="mail-queue-event-<?php echo strtolower(osc_esc_html($log['s_event'])); ?>">
            <td class="col-date"><?php echo osc_format_date($log['dt_date'], $date_format); ?></td>
            <td class="col-event"><?php echo (isset($events[$log['s_event']]) ? $events[$log['s_event']][$manual ? 1 : 0] : osc_esc_html($log['s_event'])); ?></td>
            <td class="col-attempt"><?php echo ((int)$log['i_attempt'] > 0 ? (int)$log['i_attempt'] : '-'); ?></td>
            <td class="col-duration"><?php echo ($log['i_duration'] !== null && $log['i_duration'] !== '' ? sprintf(__('%s s'), number_format((int)$log['i_duration'] / 1000, 2)) : '-'); ?></td>
            <td class="col-message"><?php echo ((string)$log['s_message'] != '' ? osc_esc_html($log['s_message']) : '-'); ?></td>
            <td class="col-who">
              <?php if($by_admin) { ?>
                <?php echo osc_esc_html($log['s_admin_username'] != '' ? $log['s_admin_username'] : sprintf(__('Admin #%d'), (int)$log['fk_i_admin_id'])); ?>
              <?php } else { ?>
                <?php _e('System'); ?>
              <?php } ?>
            </td>
          </tr>
        <?php } ?>
      <?php } else { ?>
        <tr>
          <td colspan="6" class="text-center"><p><?php _e('No history for this email'); ?></p></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<h2 class="render-title separate-top"><?php _e('Email text'); ?></h2>
<?php if((int)$mail['b_private'] == 2) { ?>
  <div class="flashmessage flashmessage-inline flashmessage-info">
    <p><?php _e('The text was removed after sending because it contained a code, link or password for one person.'); ?></p>
  </div>
<?php } else { ?>
  <div class="mail-queue-views">
    <a href="#" class="btn btn-submit" data-width="100%"><?php _e('Desktop'); ?></a>
    <a href="#" class="btn" data-width="375px"><?php _e('Mobile'); ?></a>
  </div>
  <iframe class="mail-queue-body" sandbox="" referrerpolicy="no-referrer" title="<?php echo osc_esc_html(__('Email text')); ?>" srcdoc="<?php echo htmlspecialchars($body, ENT_QUOTES, 'UTF-8'); ?>"></iframe>
  <?php if(trim((string)$preview['text']) !== '') { ?>
    <h2 class="render-title separate-top"><?php _e('Plain text version'); ?></h2>
    <pre class="mail-queue-text"><?php echo osc_esc_html((string)$preview['text']); ?></pre>
  <?php } ?>
<?php } ?>

<form id="dialog-mail-delete" method="post" action="<?php echo osc_admin_base_url(true); ?>" class="has-form-actions hide">
  <?php echo osc_csrf_token_form(); ?>
  <input type="hidden" name="page" value="settings" />
  <input type="hidden" name="action" value="mailqueue_action" />
  <input type="hidden" name="do" value="delete" />
  <input type="hidden" name="from" value="view" />
  <input type="hidden" name="id[]" value="<?php echo $id; ?>" />
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

<?php osc_current_admin_theme_path('parts/footer.php'); ?>
