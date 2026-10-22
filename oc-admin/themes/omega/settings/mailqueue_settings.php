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
  echo '<p>' . __('With the mail queue on, emails wait in the queue and the cron sends them in small batches, so pages load faster and a slow mail server does not hold up visitors.') . '</p>';
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
  return __('Mail queue settings') . ' - ' . $string;
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php');
?>

<div id="mail-queue-settings">
  <h2 class="render-title"><?php _e('Mail queue settings'); ?>
    <a href="<?php echo osc_admin_base_url(true); ?>?page=settings&amp;action=mailqueue" class="btn btn-mini mail-queue-back"><?php _e('Back to mail queue'); ?></a>
  </h2>
  <form name="mail_queue_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="page" value="settings" />
    <input type="hidden" name="action" value="mailqueue_settings_post" />
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
            <div class="help-box"><?php _e('Turn on only when a real cron calls the cron URL at least every 5 minutes. Emails left in the queue are still sent after you turn it off.'); ?></div>
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
            <input type="number" class="input-small in-text" name="mail_queue_per_run" min="1" max="5000" value="<?php echo (int)osc_mail_queue_per_run(); ?>" />
            <?php _e('emails'); ?>
            <div class="help-box"><?php _e('Instant and normal emails go first. Match the limits of your mail provider.'); ?></div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Bulk emails per cron run'); ?></div>
          <div class="form-controls has-intext-input">
            <input type="number" class="input-small in-text" name="mail_queue_bulk_per_run" min="1" max="5000" value="<?php echo (int)osc_mail_queue_bulk_per_run(); ?>" />
            <?php _e('emails'); ?>
            <div class="help-box"><?php _e('Alert digests and other bulk emails, part of the emails per run.'); ?></div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Bulk emails per group'); ?></div>
          <div class="form-controls has-intext-input">
            <input type="number" class="input-small in-text" name="mail_queue_group_per_run" min="1" max="5000" value="<?php echo (int)osc_mail_queue_group_per_run(); ?>" />
            <?php _e('emails'); ?>
            <div class="help-box"><?php _e('Most bulk emails of one group (for example one sender) in one run, so one big mailing does not hold back the others.'); ?></div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Time limit per run'); ?></div>
          <div class="form-controls has-intext-input">
            <input type="number" class="input-small in-text" name="mail_queue_time_budget" min="5" max="3600" value="<?php echo (int)osc_mail_queue_time_budget(); ?>" />
            <?php _e('seconds'); ?>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Try again after'); ?></div>
          <div class="form-controls has-intext-input">
            <input type="text" class="input-medium in-text mail-queue-delays" name="mail_queue_retry_delays" value="<?php echo osc_esc_html(implode(', ', osc_mail_queue_retry_delays())); ?>" />
            <?php _e('minutes'); ?>
            <div class="help-box"><?php _e('Minutes between the attempts, comma separated, at most 10. With 3, 10, 60 a failed email is tried again after 3, 10 and 60 minutes, then it is marked as failed. Enter 0 to never try again.'); ?></div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('History'); ?></div>
          <div class="form-controls has-intext-input">
            <?php _e('Remove sent, failed and cancelled emails after'); ?>
            <input type="number" class="input-small in-text" name="mail_queue_keep_days" min="1" max="365" value="<?php echo (int)osc_mail_queue_keep_days(); ?>" />
            <?php _e('days'); ?>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Instant email types'); ?></div>
          <div class="form-controls">
            <input type="text" class="xlarge mail-queue-types" name="mail_queue_instant_types" value="<?php echo osc_esc_html(implode(', ', osc_mail_queue_instant_types())); ?>" />
            <div class="help-box"><?php _e('Comma separated. These emails are sent right away and still shown in the queue. Back office emails without a type are always sent right away.'); ?></div>
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
