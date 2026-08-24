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
  <h1><?php _e('Reports'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');

function addHelp() {
  echo '<p>' . __('Review the report details, change status, reply in the conversation, or use the action buttons to resolve, reject, cancel, request feedback or reopen.') . '</p>';
  echo '<p><strong>' . __('Report lifetime') . '</strong></p>';
  echo '<p>' . __('Keep the report open while you investigate. Closing statuses (Resolved, Rejected, Cancelled) end the ticket. Reopen puts it back to In progress.') . '</p>';
  echo '<p><strong>' . __('Statuses') . '</strong></p>';
  echo '<ul>';
  echo '<li><strong>' . osc_report_status_label('submitted') . '</strong>: ' . __('New report.') . '</li>';
  echo '<li><strong>' . osc_report_status_label('in_review') . '</strong>: ' . __('Under review.') . '</li>';
  echo '<li><strong>' . osc_report_status_label('on_hold') . '</strong>: ' . __('Paused.') . '</li>';
  echo '<li><strong>' . osc_report_status_label('awaiting_feedback') . '</strong>: ' . __('Ask the reported user for a reply on front.') . '</li>';
  echo '<li><strong>' . osc_report_status_label('resolved') . '</strong> / <strong>' . osc_report_status_label('rejected') . '</strong> / <strong>' . osc_report_status_label('cancelled') . '</strong>: ' . __('Close the report.') . '</li>';
  echo '</ul>';
  echo '<p>' . __('Typical flow: Submitted to In progress to Resolved (or Rejected / Cancelled).') . '</p>';
}

osc_add_hook('help_box','addHelp');

function customHead() {
  ?>
  <script type="text/javascript">
  $(document).ready(function(){
    $("#dialog-report-confirm").dialog({
      autoOpen: false,
      modal: true
    });
  });

  function report_confirm_dialog(url, message) {
    $("#dialog-report-confirm .form-row").text(message);
    $("#dialog-report-confirm-ok").attr('href', url);
    $("#dialog-report-confirm").dialog('open');
    return false;
  }
  </script>
  <?php
}
osc_add_hook('admin_header', 'customHead', 10);

function customPageTitle($string) {
  $report = __get('report');
  return sprintf(__('Edit report #%s - %s'), $report['pk_i_id'], $string);
}

osc_add_filter('admin_title', 'customPageTitle');

$report = __get('report');
$item = __get('item');
$reporter = __get('reporter');
$reported_user = __get('reported_user');
$comments = __get('comments');

$csrf_token_url = osc_csrf_token_url();

$actions = array();

if($report['b_open']) {
  $actions[] = '<a class="btn float-left" href="'.osc_admin_base_url(true).'?page=reports&amp;action=status&amp;id='.$report['pk_i_id'].'&amp;'.$csrf_token_url.'&amp;value=resolved">'.__('Resolve').'</a>';
  $actions[] = '<a class="btn float-left" href="'.osc_admin_base_url(true).'?page=reports&amp;action=status&amp;id='.$report['pk_i_id'].'&amp;'.$csrf_token_url.'&amp;value=rejected">'.__('Reject').'</a>';
  $actions[] = '<a class="btn float-left" href="'.osc_admin_base_url(true).'?page=reports&amp;action=status&amp;id='.$report['pk_i_id'].'&amp;'.$csrf_token_url.'&amp;value=cancelled">'.__('Cancel').'</a>';

  if($report['fk_i_user_id'] > 0 && osc_reports_feedback_enabled() && $report['s_status'] != 'awaiting_feedback') {
    $actions[] = '<a class="btn float-left" href="'.osc_admin_base_url(true).'?page=reports&amp;action=request_feedback&amp;id='.$report['pk_i_id'].'&amp;'.$csrf_token_url.'">'.__('Request feedback').'</a>';
  }
} else {
  $actions[] = '<a class="btn float-left" href="'.osc_admin_base_url(true).'?page=reports&amp;action=status&amp;id='.$report['pk_i_id'].'&amp;'.$csrf_token_url.'&amp;value=in_review">'.__('Reopen').'</a>';
}

if($report['fk_i_user_id'] > 0) {
  $actions[] = '<a class="btn btn-red float-left" href="'.osc_admin_base_url(true).'?page=reports&amp;action=block_user&amp;id='.$report['pk_i_id'].'&amp;'.$csrf_token_url.'" onclick="return report_confirm_dialog(this.href, \''.osc_esc_js(__('Are you sure you want to block the reported user?')).'\');">'.__('Block reported user').'</a>';
}

if($report['fk_i_reporter_user_id'] > 0) {
  $actions[] = '<a class="btn btn-red float-left" href="'.osc_admin_base_url(true).'?page=reports&amp;action=block_reporter&amp;id='.$report['pk_i_id'].'&amp;'.$csrf_token_url.'" onclick="return report_confirm_dialog(this.href, \''.osc_esc_js(__('Are you sure you want to block the reporter?')).'\');">'.__('Block reporter').'</a>';
}

if($report['fk_i_item_id'] > 0) {
  $actions[] = '<a class="btn btn-red float-left" href="'.osc_admin_base_url(true).'?page=reports&amp;action=block_item&amp;id='.$report['pk_i_id'].'&amp;'.$csrf_token_url.'" onclick="return report_confirm_dialog(this.href, \''.osc_esc_js(__('Are you sure you want to block the reported listing?')).'\');">'.__('Block listing').'</a>';
}

if($report['fk_i_item_id'] > 0) {
  $actions[] = '<a class="btn float-left" href="'.osc_admin_base_url(true).'?page=items&amp;action=item_edit&amp;id='.(int)$report['fk_i_item_id'].'">'.__('Edit listing').'</a>';
}

if($report['fk_i_user_id'] > 0) {
  $actions[] = '<a class="btn float-left" href="'.osc_admin_base_url(true).'?page=users&amp;action=edit&amp;id='.(int)$report['fk_i_user_id'].'">'.__('Edit reported user').'</a>';
}

if($report['fk_i_reporter_user_id'] > 0) {
  $actions[] = '<a class="btn float-left" href="'.osc_admin_base_url(true).'?page=users&amp;action=edit&amp;id='.(int)$report['fk_i_reporter_user_id'].'">'.__('Edit reporter').'</a>';
}

$actions = osc_apply_filter('report_edit_actions', $actions, $report);

$reporter_name = ($reporter !== false ? (string)$reporter['s_name'] : __('Unknown'));
$can_notify_reported = ((int)$report['fk_i_user_id'] > 0 || ($item !== false && !empty($item['s_contact_email'])));
?>

<?php osc_current_admin_theme_path('parts/header.php'); ?>

<div class="grid-row no-bottom-margin">
  <div class="row-wrapper row-render-title-item">
    <h2 class="render-title">
      <?php _e('Edit report'); ?> #<?php echo $report['pk_i_id']; ?>
      <?php if($item !== false && isset($item['pk_i_id'])) { View::newInstance()->_exportVariableToView('item', $item); ?>
        <span class="front-link"><a href="<?php echo osc_item_url(); ?>" target="_blank"><?php _e('View listing on front'); ?> <i class="fa fa-external-link"></i></a></span>
      <?php } ?>
    </h2>
  </div>
</div>

<div class="grid-row no-bottom-margin float-right">
  <div class="row-wrapper">
    <?php if(count($actions) > 0) { ?>
    <ul id="item-action-list">
      <?php foreach($actions as $action) { ?>
      <li><?php echo $action; ?></li>
      <?php } ?>
    </ul>
    <div class="clear"></div>
    <?php } ?>
  </div>
</div>
<div class="clear"></div>

<div id="report-edit">
<div id="language-form">
  <ul id="error_list"></ul>
  <form name="report_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="action" value="edit_post" />
    <input type="hidden" name="page" value="reports" />
    <input type="hidden" name="id" value="<?php echo $report['pk_i_id']; ?>" />

    <div class="form-horizontal">
      <div class="form-row">
        <div class="form-label"><?php _e('Type'); ?></div>
        <div class="form-controls">
          <?php echo osc_report_type_label($report['s_type']); ?>
          <?php if($report['s_source'] != 'osclass') { ?>
            <span class="help-inline"><?php echo sprintf(__('Source: %s'), osc_esc_html(osc_report_source_label($report['s_source']))); ?></span>
          <?php } ?>
        </div>
      </div>

      <div class="form-row">
        <div class="form-label"><?php _e('Report created by'); ?></div>
        <div class="form-controls">
          <?php if($reporter !== false) { ?>
            <a href="<?php echo osc_admin_base_url(true); ?>?page=users&action=edit&id=<?php echo (int)$report['fk_i_reporter_user_id']; ?>"><?php echo osc_esc_html((string)$reporter['s_name']); ?></a>
          <?php } else if($report['s_type'] == 'webcontact') { ?>
            <?php _e('Guest'); ?>
          <?php } else { ?>
            -
          <?php } ?>
        </div>
      </div>

      <div class="form-row">
        <div class="form-label"><?php _e('Reported user'); ?></div>
        <div class="form-controls">
          <?php
            $reported_user_id = (int)$report['fk_i_user_id'];
          ?>
          <?php if($reported_user !== false && $reported_user_id > 0) { ?>
            <a href="<?php echo osc_admin_base_url(true); ?>?page=users&action=edit&id=<?php echo $reported_user_id; ?>"><?php echo osc_esc_html((string)$reported_user['s_name']); ?></a>
          <?php } else if($item !== false && !empty($item['s_contact_email'])) { ?>
            <?php echo osc_esc_html((string)$item['s_contact_name']); ?> &lt;<?php echo osc_esc_html((string)$item['s_contact_email']); ?>&gt;
          <?php } else { ?>
            -
          <?php } ?>
        </div>
      </div>

      <div class="form-row">
        <div class="form-label"><?php _e('Reported content'); ?></div>
        <div class="form-controls">
          <?php if($report['s_type'] == 'item' && $report['fk_i_item_id'] > 0 && $item !== false) { ?>
            <a href="<?php echo osc_admin_base_url(true); ?>?page=items&action=item_edit&id=<?php echo (int)$report['fk_i_item_id']; ?>"><?php echo osc_esc_html((string)$item['s_title']); ?></a>
          <?php } else if($report['s_type'] == 'user' && $reported_user !== false) { ?>
            <a href="<?php echo osc_admin_base_url(true); ?>?page=users&action=edit&id=<?php echo (int)$report['fk_i_user_id']; ?>"><?php echo osc_esc_html((string)$reported_user['s_name']); ?></a>
          <?php } else if($report['s_type'] == 'webcontact') { ?>
            <?php echo osc_esc_html(osc_report_type_label('webcontact')); ?>
          <?php } else if($report['i_reported_id'] > 0) { ?>
            <?php echo osc_esc_html(osc_report_type_label($report['s_type'])); ?> #<?php echo (int)$report['i_reported_id']; ?>
          <?php } else { ?>
            -
          <?php } ?>
        </div>
      </div>

      <div class="form-row">
        <div class="form-label"><?php _e('Status'); ?></div>
        <div class="form-controls">
          <select name="status">
            <?php foreach(osc_report_statuses_enabled($report['s_status']) as $code => $label) { ?>
              <option value="<?php echo osc_esc_html($code); ?>" <?php if($report['s_status'] == $code) echo 'selected'; ?>><?php echo $label; ?></option>
            <?php } ?>
          </select>
          <span class="help-inline"><?php echo sprintf(__('Last status change: %s'), osc_format_date($report['dt_status_date'])); ?></span>
          <span class="help-box"><?php _e('Open statuses keep the ticket active. Closing statuses (Resolved, Rejected, Cancelled) end it. Awaiting feedback lets the reported user reply on front.'); ?></span>
        </div>
      </div>

      <div class="form-row">
        <div class="form-label"><?php _e('Reason'); ?></div>
        <div class="form-controls">
          <select name="reason">
            <?php foreach(osc_report_reasons_for_type($report['s_type'], $report['s_reason']) as $code => $label) { ?>
              <option value="<?php echo osc_esc_html($code); ?>" <?php if($report['s_reason'] == $code) echo 'selected'; ?>><?php echo $label; ?></option>
            <?php } ?>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-label"><?php _e('Original report text'); ?></div>
        <div class="form-controls">
          <?php echo nl2br(osc_esc_html((string)$report['s_comment'])); ?>
        </div>
      </div>

      <?php if(!empty($report['s_file'])) { ?>
      <div class="form-row">
        <div class="form-label"><?php _e('Attachment'); ?></div>
        <div class="form-controls">
          <a href="<?php echo osc_report_attachment_url($report['s_file']); ?>" target="_blank"><?php echo osc_esc_html($report['s_file']); ?></a>
        </div>
      </div>
      <?php } ?>

      <div class="form-row">
        <div class="form-label"><?php _e('Admin note'); ?></div>
        <div class="form-controls">
          <textarea id="admin_comment" name="admin_comment" rows="4"><?php echo osc_esc_html((string)$report['s_admin_comment']); ?></textarea>
          <span class="help-inline"><?php _e('Internal note, not visible to users'); ?></span>
        </div>
      </div>

      <div class="form-row">
        <div class="form-label"><?php _e('Created'); ?></div>
        <div class="form-controls">
          <?php echo osc_format_date($report['dt_create_date']); ?>
        </div>
      </div>

      <?php osc_run_hook('report_edit_form', $report); ?>

      <div class="clear"></div>
      <div class="form-actions">
        <input type="submit" id="save_changes" value="<?php echo osc_esc_html(__('Save changes')); ?>" class="btn btn-submit" />
      </div>
    </div>
  </form>
</div>

<a name="comments"></a>
<h2 class="render-title separate-top"><?php _e('Report conversation'); ?></h2>
<p class="help-box"><?php _e('Only the reported user can open this conversation on front. The user who created the report cannot.'); ?></p>

<div class="report-conversation">
  <div class="report-conversation-thread">
    <div class="report-message report-message-reporter">
      <div class="report-message-avatar">
        <?php if(osc_profile_img_users_enabled()) { ?>
          <img src="<?php echo osc_report_comment_profile_img_url(null, $report['fk_i_reporter_user_id']); ?>" alt="<?php echo osc_esc_html($reporter_name); ?>"/>
        <?php } else { ?>
          <?php echo osc_esc_html(osc_report_message_initials($reporter_name)); ?>
        <?php } ?>
      </div>
      <div class="report-message-content">
        <div class="report-message-meta">
          <span class="report-message-from">
            <span class="report-message-author"><?php echo osc_esc_html($reporter_name); ?></span>
            <span class="report-message-role"><?php _e('Report created by'); ?></span>
          </span>
          <span class="report-message-date"><?php echo osc_format_date($report['dt_create_date']); ?></span>
        </div>
        <div class="report-message-body">
          <?php echo nl2br(osc_esc_html((string)$report['s_comment'])); ?>
          <?php if(!empty($report['s_file'])) { ?>
            <div class="report-message-attachment"><a href="<?php echo osc_report_attachment_url($report['s_file']); ?>" target="_blank"><?php _e('Attachment'); ?></a></div>
          <?php } ?>
        </div>
      </div>
    </div>

    <?php if(!empty($comments)) { ?>
      <?php foreach($comments as $comment) {
        $is_admin = ($comment['fk_i_admin_id'] > 0);
        $is_creator = (!$is_admin && $comment['fk_i_user_id'] > 0 && (int)$comment['fk_i_user_id'] === (int)$report['fk_i_reporter_user_id']);
        $is_reported = (!$is_admin && $comment['fk_i_user_id'] > 0 && (int)$comment['fk_i_user_id'] === (int)$report['fk_i_user_id']);
        $msg_class = ($is_admin ? 'report-message-admin' : ($is_reported ? 'report-message-user' : 'report-message-reporter'));
        $author = '-';
        $role = __('User');

        if($is_admin) {
          $author = ($comment['s_admin_name'] != '' ? $comment['s_admin_name'] : __('Admin'));
          $role = __('Admin');
        } else if($is_reported) {
          $author = (string)$comment['s_user_name'];
          $role = __('Reported user');
        } else if($is_creator) {
          $author = (string)$comment['s_user_name'];
          $role = __('Report created by');
        } else if($comment['fk_i_user_id'] > 0) {
          $author = (string)$comment['s_user_name'];
          $role = __('User');
        }
      ?>
      <div class="report-message <?php echo $msg_class; ?>">
        <div class="report-message-avatar">
          <?php if(osc_profile_img_users_enabled()) { ?>
            <img src="<?php echo osc_report_comment_profile_img_url($comment); ?>" alt="<?php echo osc_esc_html($author); ?>"/>
          <?php } else { ?>
            <?php echo osc_esc_html(osc_report_message_initials($author)); ?>
          <?php } ?>
        </div>
        <div class="report-message-content">
          <div class="report-message-meta">
            <span class="report-message-from">
              <span class="report-message-author"><?php if(!$is_admin && $comment['fk_i_user_id'] > 0) { ?><a href="<?php echo osc_admin_base_url(true); ?>?page=users&action=edit&id=<?php echo (int)$comment['fk_i_user_id']; ?>"><?php echo osc_esc_html($author); ?></a><?php } else { echo osc_esc_html($author); } ?></span>
              <span class="report-message-role"><?php echo $role; ?></span>
            </span>
            <span class="report-message-date"><?php echo osc_format_date($comment['dt_date']); ?></span>
          </div>
          <div class="report-message-body"><?php echo nl2br(osc_esc_html((string)$comment['s_comment'])); ?></div>
        </div>
      </div>
      <?php } ?>
    <?php } ?>
  </div>
</div>

<div id="language-form">
  <form name="report_reply_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="action" value="comment_post" />
    <input type="hidden" name="page" value="reports" />
    <input type="hidden" name="id" value="<?php echo $report['pk_i_id']; ?>" />

    <div class="form-horizontal">
      <div class="form-row">
        <div class="form-label"><?php _e('Add reply'); ?></div>
        <div class="form-controls">
          <textarea id="comment" name="comment" rows="4"></textarea>
          <span class="help-inline"><?php _e('Visible to the reported user on front. The user who created the report has no front access.'); ?></span>
        </div>
      </div>

      <div class="form-row">
        <div class="form-label"><?php _e('Status'); ?></div>
        <div class="form-controls">
          <select name="status">
            <?php foreach(osc_report_statuses_enabled($report['s_status']) as $code => $label) { ?>
              <option value="<?php echo osc_esc_html($code); ?>" <?php if($report['s_status'] == $code) echo 'selected'; ?>><?php echo $label; ?></option>
            <?php } ?>
          </select>
          <span class="help-inline"><?php printf(__('The reported user can reply on front only when the status is %s'), osc_report_status_label('awaiting_feedback')); ?></span>
        </div>
      </div>

      <div class="form-row">
        <div class="form-label"><?php _e('Notify'); ?></div>
        <div class="form-controls">
          <div class="form-label-checkbox">
            <label>
              <input type="checkbox" name="notify_reporter" value="1" <?php if(!($report['fk_i_reporter_user_id'] > 0)) echo 'disabled="disabled"'; ?> />
              <?php _e('Notify report creator by email'); ?>
            </label>
          </div>
          <div class="form-label-checkbox">
            <label>
              <input type="checkbox" name="notify_reported" value="1" <?php if(!$can_notify_reported) echo 'disabled="disabled"'; ?> />
              <?php _e('Notify reported user by email'); ?>
            </label>
          </div>
        </div>
      </div>

      <?php osc_run_hook('report_admin_comment_form', $report); ?>

      <div class="form-actions">
        <input type="submit" value="<?php echo osc_esc_html(__('Send reply')); ?>" class="btn btn-submit" />
      </div>
    </div>
  </form>
</div>
</div>

<div id="dialog-report-confirm" title="<?php echo osc_esc_html(__('Confirm action')); ?>" class="has-form-actions hide">
  <div class="form-horizontal">
    <div class="form-row"></div>
    <div class="form-actions">
      <div class="wrapper">
        <a id="dialog-report-confirm-ok" href="javascript:void(0);" class="btn btn-red"><?php _e('Continue'); ?></a>
        <a class="btn" href="javascript:void(0);" onclick="$('#dialog-report-confirm').dialog('close');"><?php _e('Cancel'); ?></a>
        <div class="clear"></div>
      </div>
    </div>
  </div>
</div>

<?php osc_current_admin_theme_path('parts/footer.php'); ?>
