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
  echo '<p>' . __('With the mail queue on, emails wait in a queue and the cron sends them in small batches. Codes, password resets and account emails are still sent right away. Here you see what waits, what was sent and what failed.') . '</p>';
  echo '<p>' . __('The queue needs a real cron on your server that calls the cron URL at least every 5 minutes. Without it, queued emails are not sent.') . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader() {
  ?>
  <h1><?php _e('Settings'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return __('Mail queue') . ' - ' . $string;
}

osc_add_filter('admin_title', 'customPageTitle');

$aData = __get('aData');
$stats = __get('mail_queue_stats');
$tab = __get('mail_queue_tab');
$base_url = osc_admin_base_url(true) . '?page=settings&action=mailqueue';
$action_url = osc_admin_base_url(true) . '?page=settings&action=mailqueue_action' . ($tab != '' ? '&status=' . $tab : '') . '&' . osc_csrf_token_url();
$date_format = osc_date_format() . ' ' . osc_time_format();
$tabs = array('' => __('All'), 'pending' => __('Pending'), 'sent' => __('Sent'), 'failed' => __('Failed'), 'cancelled' => __('Cancelled'));

osc_current_admin_theme_path('parts/header.php');
?>

<div id="mail-queue">
  <h2 class="render-title"><?php _e('Mail queue'); ?></h2>

  <?php if(!$stats['enabled']) { ?>
    <div class="flashmessage flashmessage-inline flashmessage-info mq-notice">
      <p><?php _e('The mail queue is off. Emails are sent right away, as before. Turn it on in the settings below only when a real cron runs on your server.'); ?></p>
    </div>
  <?php } ?>

  <?php if($stats['late']) { ?>
    <div class="flashmessage flashmessage-inline flashmessage-error mq-notice">
      <p><?php printf(__('Emails are waiting too long. The oldest one should have been sent at %s. Check that your cron runs (last run: %s).'), osc_esc_html(osc_format_date($stats['oldest_due'], $date_format)), ($stats['last_run'] != '' ? osc_esc_html(osc_format_date($stats['last_run'], $date_format)) : __('Never'))); ?></p>
    </div>
  <?php } ?>

  <?php if($stats['paused']) { ?>
    <div class="flashmessage flashmessage-inline flashmessage-warning mq-notice">
      <p><?php _e('Sending is paused. New emails still wait in the queue.'); ?></p>
    </div>
  <?php } ?>

  <div class="mq-health">
    <span>
      <span class="mq-label"><?php _e('Waiting'); ?></span>
      <span class="mq-value"><?php echo (int)$stats['pending']; ?></span>
      <span class="mq-sub"><?php echo ($stats['oldest_due'] != '' ? sprintf(__('Oldest due: %s'), osc_esc_html(osc_format_date($stats['oldest_due'], $date_format))) : __('Nothing is due')); ?></span>
    </span>
    <span>
      <span class="mq-label"><?php _e('Sent in last 24 hours'); ?></span>
      <span class="mq-value"><?php echo (int)$stats['sent_24h']; ?></span>
    </span>
    <span>
      <span class="mq-label"><?php _e('Failed in last 24 hours'); ?></span>
      <span class="mq-value"><?php echo (int)$stats['failed_24h']; ?></span>
    </span>
    <span>
      <span class="mq-label"><?php _e('Cron'); ?></span>
      <span class="mq-sub"><?php printf(__('Last run: %s'), ($stats['last_run'] != '' ? osc_esc_html(osc_format_date($stats['last_run'], $date_format)) : __('Never'))); ?></span>
      <span class="mq-sub"><?php printf(__('Next run: %s'), ($stats['next_run'] != '' ? osc_esc_html(osc_format_date($stats['next_run'], $date_format)) : __('Never'))); ?></span>
    </span>
    <span>
      <span class="mq-label"><?php _e('Sending'); ?></span>
      <?php if($stats['paused']) { ?>
        <a class="btn btn-mini btn-green" href="<?php echo osc_esc_html($action_url . '&do=resume'); ?>"><?php _e('Resume sending'); ?></a>
      <?php } else { ?>
        <a class="btn btn-mini" href="<?php echo osc_esc_html($action_url . '&do=pause'); ?>"><?php _e('Pause sending'); ?></a>
      <?php } ?>
    </span>
  </div>

  <div class="mq-tabs">
    <?php foreach($tabs as $key => $label) { ?>
      <a href="<?php echo osc_esc_html($base_url . ($key != '' ? '&status=' . $key : '')); ?>" class="btn<?php echo ($tab == $key ? ' btn-green' : ''); ?>"><?php echo $label; ?> (<?php echo (int)($key != '' ? $stats[$key] : $stats['total']); ?>)</a>
    <?php } ?>
  </div>

  <div class="mq-filters">
    <?php osc_custom_datatable_toolbar_per_page(array('sizes' => array(10, 25, 50, 100))); ?>
    <form method="get" action="<?php echo osc_admin_base_url(true); ?>" class="inline nocsrf">
      <input type="hidden" name="page" value="settings" />
      <input type="hidden" name="action" value="mailqueue" />
      <?php if($tab != '') { ?><input type="hidden" name="status" value="<?php echo osc_esc_html($tab); ?>" /><?php } ?>
      <?php if(Params::getParam('ref') != '') { ?><input type="hidden" name="ref" value="<?php echo osc_esc_html(Params::getParam('ref')); ?>" /><?php } ?>
      <select name="priority" class="select-box-extra select-box-medium">
        <option value=""><?php _e('All priorities'); ?></option>
        <option value="1" <?php if(Params::getParam('priority') == 1) echo 'selected'; ?>><?php _e('Instant'); ?></option>
        <option value="2" <?php if(Params::getParam('priority') == 2) echo 'selected'; ?>><?php _e('Normal'); ?></option>
        <option value="3" <?php if(Params::getParam('priority') == 3) echo 'selected'; ?>><?php _e('Bulk'); ?></option>
      </select>
      <input type="text" name="type" class="input-text" value="<?php echo osc_esc_html(Params::getParam('type')); ?>" placeholder="<?php echo osc_esc_html(__('Type')); ?>" aria-label="<?php echo osc_esc_html(__('Type')); ?>" />
      <input type="text" name="group" class="input-text" value="<?php echo osc_esc_html(Params::getParam('group')); ?>" placeholder="<?php echo osc_esc_html(__('Group')); ?>" aria-label="<?php echo osc_esc_html(__('Group')); ?>" />
      <input type="date" name="date_from" class="input-text" value="<?php echo osc_esc_html(Params::getParam('date_from')); ?>" title="<?php echo osc_esc_html(__('Created from')); ?>" aria-label="<?php echo osc_esc_html(__('Created from')); ?>" />
      <input type="date" name="date_to" class="input-text" value="<?php echo osc_esc_html(Params::getParam('date_to')); ?>" title="<?php echo osc_esc_html(__('Created to')); ?>" aria-label="<?php echo osc_esc_html(__('Created to')); ?>" />
      <input type="text" name="sSearch" class="input-text input-actions" value="<?php echo osc_esc_html(Params::getParam('sSearch')); ?>" placeholder="<?php echo osc_esc_html(__('Recipient or subject')); ?>" aria-label="<?php echo osc_esc_html(__('Recipient or subject')); ?>" />
      <input type="submit" class="btn submit-right" value="<?php echo osc_esc_html(__('Find')); ?>" />
      <?php if(__get('withFilters')) { ?>
        <a href="<?php echo osc_esc_html($base_url . ($tab != '' ? '&status=' . $tab : '')); ?>" class="btn"><?php _e('Reset filters'); ?></a>
      <?php } ?>
    </form>
  </div>

  <?php osc_custom_datatable_print_form_open(array('page' => 'settings', 'hidden' => array('action' => 'mailqueue_action', 'status' => $tab))); ?>
    <?php osc_custom_datatable_print_bulk_bar(array('action_name' => 'do')); ?>
    <?php osc_custom_datatable_print_table($aData, __get('aRawRows')); ?>
  </form>

  <?php osc_custom_datatable_print_pagination_block($aData, array('per_page' => array('sizes' => array(10, 25, 50, 100)))); ?>

  <h2 class="render-title separate-top"><?php _e('Mail queue settings'); ?></h2>
  <form name="mail_queue_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="page" value="settings" />
    <input type="hidden" name="action" value="mailqueue_post" />
    <?php if($tab != '') { ?><input type="hidden" name="status" value="<?php echo osc_esc_html($tab); ?>" /><?php } ?>
    <fieldset>
      <div class="form-horizontal">
        <div class="form-row">
          <div class="form-label"><?php _e('Mail queue'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" <?php echo (osc_mail_queue_enabled() ? 'checked="checked"' : ''); ?> name="mail_queue_enabled" value="1" />
                <?php _e('Put emails into the queue and send them by cron'); ?>
              </label>
            </div>
            <span class="help-box"><?php _e('Turn on only when a real cron calls the cron URL at least every 5 minutes. Emails left in the queue are still sent after you turn it off.'); ?></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Pause sending'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" <?php echo (osc_mail_queue_paused() ? 'checked="checked"' : ''); ?> name="mail_queue_paused" value="1" />
                <?php _e('Keep emails in the queue and send nothing'); ?>
              </label>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Emails per cron run'); ?></div>
          <div class="form-controls has-intext-input">
            <input type="number" class="input-small in-text" name="mail_queue_per_run" min=1 max=5000 value="<?php echo (int)osc_mail_queue_per_run(); ?>" />
            <span class="help-box"><?php _e('Instant and normal emails go first. Match the limits of your mail provider.'); ?></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Bulk emails per cron run'); ?></div>
          <div class="form-controls has-intext-input">
            <input type="number" class="input-small in-text" name="mail_queue_bulk_per_run" min=1 max=5000 value="<?php echo (int)osc_mail_queue_bulk_per_run(); ?>" />
            <span class="help-box"><?php _e('Alert digests and other bulk emails, part of the emails per run.'); ?></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Bulk emails per group'); ?></div>
          <div class="form-controls has-intext-input">
            <input type="number" class="input-small in-text" name="mail_queue_group_per_run" min=1 max=5000 value="<?php echo (int)osc_mail_queue_group_per_run(); ?>" />
            <span class="help-box"><?php _e('Most bulk emails of one group (for example one sender) in one run, so one big mailing does not hold back the others.'); ?></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Time limit per run'); ?></div>
          <div class="form-controls has-intext-input">
            <input type="number" class="input-small in-text" name="mail_queue_time_budget" min=5 max=3600 value="<?php echo (int)osc_mail_queue_time_budget(); ?>" />
            <?php _e('seconds'); ?>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Attempts'); ?></div>
          <div class="form-controls has-intext-input">
            <input type="number" class="input-small in-text" name="mail_queue_max_attempts" min=1 max=10 value="<?php echo (int)osc_mail_queue_max_attempts(); ?>" />
            <span class="help-box"><?php _e('A failed email is tried again after 5 minutes, 30 minutes and then every 2 hours. After the last attempt it is marked as failed.'); ?></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('History'); ?></div>
          <div class="form-controls has-intext-input">
            <?php _e('Remove sent, failed and cancelled emails after'); ?>
            <input type="number" class="input-small in-text" name="mail_queue_keep_days" min=1 max=365 value="<?php echo (int)osc_mail_queue_keep_days(); ?>" />
            <?php _e('days'); ?>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Instant email types'); ?></div>
          <div class="form-controls">
            <input type="text" class="xlarge" name="mail_queue_instant_types" value="<?php echo osc_esc_html(implode(',', osc_mail_queue_instant_types())); ?>" />
            <span class="help-box"><?php _e('Comma separated. These emails are sent right away and still shown in the list. Back office emails without a type are always sent right away.'); ?></span>
          </div>
        </div>

        <div class="form-actions">
          <input type="submit" value="<?php echo osc_esc_html(__('Save changes')); ?>" class="btn btn-submit" />
        </div>
      </div>
    </fieldset>
  </form>
</div>

<?php osc_current_admin_theme_path('parts/footer.php'); ?>
