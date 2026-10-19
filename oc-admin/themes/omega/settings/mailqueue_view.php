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


function customPageHeader() {
  ?>
  <h1><?php _e('Settings'); ?></h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return __('Mail queue') . ' - ' . $string;
}

osc_add_filter('admin_title', 'customPageTitle');

$mail = __get('mail');
$params = __get('mail_params');
$date_format = osc_date_format() . ' ' . osc_time_format();
$priorities = array(1 => __('Instant'), 2 => __('Normal'), 3 => __('Bulk'));
$status_labels = array('PENDING' => __('Pending'), 'SENDING' => __('Sending'), 'SENT' => __('Sent'), 'FAILED' => __('Failed'), 'CANCELLED' => __('Cancelled'));
$action_url = osc_admin_base_url(true) . '?page=settings&action=mailqueue_action&id[]=' . (int)$mail['pk_i_id'] . '&' . osc_csrf_token_url() . '&do=';

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

$details = array(
  __('Status') => (isset($status_labels[$mail['e_status']]) ? $status_labels[$mail['e_status']] : $mail['e_status']),
  __('Recipient') => implode(', ', $recipients),
  __('Reply to') => (isset($params['reply_to']) ? (string)$params['reply_to'] : ''),
  __('Bcc') => implode(', ', array_map('strval', $bcc)),
  __('From') => trim((isset($params['from_name']) ? $params['from_name'] . ' ' : '') . (isset($params['from']) ? '<' . $params['from'] . '>' : '')),
  __('Subject') => (string)$mail['s_subject'],
  __('Attachments') => implode(', ', $attachments),
  __('Type') => (string)$mail['s_type'],
  __('Priority') => (isset($priorities[(int)$mail['i_priority']]) ? $priorities[(int)$mail['i_priority']] : (int)$mail['i_priority']),
  __('Group') => (string)$mail['s_group'],
  __('Reference') => (string)$mail['s_ref'],
  __('Created') => osc_format_date($mail['dt_created'], $date_format),
  __('Send after') => osc_format_date($mail['dt_send_after'], $date_format),
  __('Expires') => ($mail['dt_expire'] != '' ? osc_format_date($mail['dt_expire'], $date_format) : ''),
  __('Finished') => ($mail['dt_done'] != '' ? osc_format_date($mail['dt_done'], $date_format) : ''),
  __('Attempts') => (int)$mail['i_attempts'],
  __('Error') => (string)$mail['s_error']
);

$body = (isset($params['body']) ? (string)$params['body'] : '');

osc_current_admin_theme_path('parts/header.php');
?>

<div id="mail-queue">
  <h2 class="render-title">
    <?php printf(__('Email #%d'), (int)$mail['pk_i_id']); ?>
    <a href="<?php echo osc_admin_base_url(true); ?>?page=settings&amp;action=mailqueue" class="btn btn-mini float-right"><?php _e('Back to mail queue'); ?></a>
  </h2>

  <div class="form-horizontal mq-detail">
    <?php foreach($details as $label => $value) { ?>
      <?php if((string)$value !== '') { ?>
        <div class="form-row">
          <div class="form-label"><?php echo $label; ?></div>
          <div class="form-controls"><?php echo osc_esc_html((string)$value); ?></div>
        </div>
      <?php } ?>
    <?php } ?>

    <div class="form-row">
      <div class="form-label"><?php _e('Actions'); ?></div>
      <div class="form-controls">
        <?php if(in_array($mail['e_status'], array('PENDING', 'FAILED', 'CANCELLED'))) { ?>
          <a class="btn btn-mini" href="<?php echo osc_esc_html($action_url . 'send'); ?>"><?php _e('Send now'); ?></a>
        <?php } ?>
        <?php if($mail['e_status'] == 'PENDING') { ?>
          <a class="btn btn-mini" href="<?php echo osc_esc_html($action_url . 'cancel'); ?>"><?php _e('Cancel'); ?></a>
        <?php } ?>
        <?php if(in_array($mail['e_status'], array('FAILED', 'CANCELLED'))) { ?>
          <a class="btn btn-mini" href="<?php echo osc_esc_html($action_url . 'retry'); ?>"><?php _e('Retry'); ?></a>
        <?php } ?>
        <?php if($mail['e_status'] != 'SENDING') { ?>
          <a class="btn btn-mini btn-red" href="<?php echo osc_esc_html($action_url . 'delete'); ?>" onclick="return confirm('<?php echo osc_esc_js(__('Delete this email from the queue?')); ?>');"><?php _e('Delete'); ?></a>
        <?php } ?>
      </div>
    </div>
  </div>

  <h2 class="render-title separate-top"><?php _e('Email text'); ?></h2>
  <?php if($body == '' && $mail['b_private'] == 1) { ?>
    <div class="flashmessage flashmessage-inline flashmessage-info mq-notice">
      <p><?php _e('The text was removed after sending because it contained a code, link or password for one person.'); ?></p>
    </div>
  <?php } else { ?>
    <iframe class="mq-body" sandbox="" referrerpolicy="no-referrer" title="<?php echo osc_esc_html(__('Email text')); ?>" srcdoc="<?php echo htmlspecialchars($body, ENT_QUOTES, 'UTF-8'); ?>"></iframe>
  <?php } ?>
</div>

<?php osc_current_admin_theme_path('parts/footer.php'); ?>
