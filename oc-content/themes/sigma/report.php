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


// meta tag robots
osc_add_hook('header','sigma_nofollow_construct');

if(!function_exists('osc_report_enabled') || !osc_report_enabled()) {
  osc_add_flash_warning_message(__('Reports are not available.', 'sigma'));
  osc_redirect_to(osc_base_url());
}

osc_enqueue_script('jquery-validate');
sigma_add_body_class('report');
osc_current_web_theme_path('header.php');

$report = (osc_report_mode() == 'view' ? osc_report() : array());
?>

<?php if(osc_report_mode() == 'view') { ?>
<div id="report-view">
  <div class="header">
    <h1><?php echo sprintf(__('Report #%d', 'sigma'), $report['pk_i_id']); ?></h1>
  </div>
  <div class="resp-wrapper">
    <?php osc_run_hook('report_view_top'); ?>

    <div class="control-group">
      <label class="control-label"><?php _e('Status', 'sigma'); ?></label>
      <div class="controls"><span class="report-status report-status-<?php echo osc_esc_html($report['s_status']); ?>"><?php echo osc_report_status_label($report['s_status']); ?></span></div>
    </div>

    <div class="control-group">
      <label class="control-label"><?php _e('Reason', 'sigma'); ?></label>
      <div class="controls"><?php echo osc_report_reason_label($report['s_reason']); ?></div>
    </div>

    <div class="control-group">
      <label class="control-label"><?php _e('Report details', 'sigma'); ?></label>
      <div class="controls"><?php echo nl2br(osc_esc_html((string)$report['s_comment'])); ?></div>
    </div>

    <?php if(!empty($report['s_file'])) { ?>
      <div class="control-group">
        <label class="control-label"><?php _e('Attachment', 'sigma'); ?></label>
        <div class="controls"><a href="<?php echo osc_report_attachment_url($report['s_file']); ?>" target="_blank"><?php _e('Download attachment', 'sigma'); ?></a></div>
      </div>
    <?php } ?>

    <?php osc_run_hook('report_view_form', $report); ?>

    <?php $comments = osc_report_comments(); ?>
    <?php if(!empty($comments)) { ?>
      <div class="comments_list">
        <h3><?php _e('Conversation history', 'sigma'); ?></h3>
        <?php foreach($comments as $comment) { ?>
          <?php
          $is_admin = ($comment['fk_i_admin_id'] > 0);
          $author = osc_report_front_author_label($comment, $report);
          $img_user_id = ($is_admin ? 0 : (int)$comment['fk_i_user_id']);
          ?>

          <div class="comment <?php echo ($is_admin ? 'reply' : ''); ?><?php if(osc_profile_img_users_enabled()) { ?> has-user-img<?php } ?>">
            <?php if(osc_profile_img_users_enabled()) { ?>
              <p class="user-img">
                <img src="<?php echo osc_user_profile_img_url($img_user_id); ?>" alt="<?php echo osc_esc_html($author); ?>"/>
              </p>
            <?php } ?>
            <p class="bld"><?php echo osc_esc_html($author); ?> <span><?php echo osc_format_date($comment['dt_date'], osc_date_format() . ' ' . osc_time_format()); ?></span></p>
            <p><?php echo nl2br(osc_esc_html((string)$comment['s_comment'])); ?></p>
          </div>
        <?php } ?>
      </div>
    <?php } ?>

    <?php if(osc_report_user_can_comment($report)) { ?>
      <ul id="error_list"></ul>
      <form name="report_reply" action="<?php echo osc_base_url(true); ?>" method="post">
        <input type="hidden" name="page" value="report" />
        <input type="hidden" name="action" value="comment_post" />
        <input type="hidden" name="id" value="<?php echo (int)$report['pk_i_id']; ?>" />
        <div class="control-group">
          <label class="control-label" for="comment"><?php _e('Your reply', 'sigma'); ?></label>
          <div class="controls textarea">
            <textarea name="comment" id="comment" rows="4"></textarea>
          </div>
        </div>
        <?php osc_run_hook('report_comment_form', $report); ?>
        <div class="control-group">
          <div class="controls">
            <button type="submit" class="btn btn-primary"><?php _e('Send reply', 'sigma'); ?></button>
          </div>
        </div>
      </form>
      <?php ReportForm::js_validation_reply(); ?>
    <?php } else if(osc_is_admin_user_logged_in()) { ?>
      <p class="report-note"><a href="<?php echo osc_admin_base_url(true) . '?page=reports&action=edit&id=' . (int)$report['pk_i_id']; ?>"><?php _e('Manage this report in backoffice', 'sigma'); ?></a></p>
    <?php } else if($report['b_open'] == 0) { ?>
      <p class="report-note"><?php _e('This report has been closed.', 'sigma'); ?></p>
    <?php } else if(osc_reports_feedback_enabled()) { ?>
      <p class="report-note"><?php _e('You can reply only when feedback was requested on this report.', 'sigma'); ?></p>
    <?php } ?>

    <?php osc_run_hook('report_view_bottom'); ?>
  </div>
</div>

<?php } else { ?>

<div id="report-form">
  <div class="header">
    <h1><?php echo (osc_report_form_type() == 'user' ? __('Report user', 'sigma') : __('Report listing', 'sigma')); ?></h1>
  </div>
  <div class="resp-wrapper">
    <?php if(osc_report_form_type() == 'user') { ?>
      <p class="report-target"><?php echo sprintf(__('You are reporting user %s.', 'sigma'), '<a href="' . osc_user_public_profile_url(osc_report_form_target_id()) . '">' . osc_esc_html((string)osc_user_name()) . '</a>'); ?></p>
    <?php } else { ?>
      <p class="report-target"><?php echo sprintf(__('You are reporting listing %s.', 'sigma'), '<a href="' . osc_item_url() . '">' . osc_esc_html((string)osc_item_title()) . '</a>'); ?></p>
    <?php } ?>

    <?php osc_report_form(osc_report_form_type(), osc_report_form_target_id()); ?>
  </div>
</div>
<?php } ?>

<?php osc_current_web_theme_path('footer.php');
