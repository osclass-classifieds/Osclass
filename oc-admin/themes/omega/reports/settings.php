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


osc_enqueue_script('jquery-validate');

//customize Head
function customHead() {
  ?>
  <script type="text/javascript">
  $(document).ready(function(){
    // Code for form validation
    $("form[name=reports_form]").validate({
      rules: {
        reports_per_day: {
          required: true,
          digits: true
        },
        reports_auto_close_days: {
          required: true,
          digits: true
        },
        reports_retention_months: {
          required: true,
          digits: true
        }
      },
      messages: {
        reports_per_day: {
          required: '<?php echo osc_esc_js(__("Reports per day: this field is required")); ?>.',
          digits: '<?php echo osc_esc_js(__("Reports per day: this field must only contain numeric characters")); ?>.'
        },
        reports_auto_close_days: {
          required: '<?php echo osc_esc_js(__("Auto-close days: this field is required")); ?>.',
          digits: '<?php echo osc_esc_js(__("Auto-close days: this field must only contain numeric characters")); ?>.'
        },
        reports_retention_months: {
          required: '<?php echo osc_esc_js(__("Retention months: this field is required")); ?>.',
          digits: '<?php echo osc_esc_js(__("Retention months: this field must only contain numeric characters")); ?>.'
        }
      },
      wrapper: "li",
      errorLabelContainer: "#error_list",
      invalidHandler: function(form, validator) {
        $('html,body').animate({ scrollTop: $('h1').offset().top }, { duration: 250, easing: 'swing'});
      },
      submitHandler: function(form){
        $('button[type=submit], input[type=submit]').attr('disabled', 'disabled');
        form.submit();
      }
    });
  });
  </script>
  <?php
}
osc_add_hook('admin_header','customHead', 10);


function addHelp() {
  echo '<p>' . __('Modify the options for reports users can submit on listings, users or other content.') . '</p>';
  echo '<p><strong>' . __('Report lifetime') . '</strong></p>';
  echo '<p>' . __('A report is open from Submitted through In progress, On hold and Awaiting feedback. Setting Resolved, Rejected or Cancelled closes it. Closed reports can be reopened.') . '</p>';
  echo '<p>' . __('Auto-close can close reports that stay in Awaiting feedback with no activity. Retention deletes old reports after the months you set (0 = keep forever). The daily report limit still applies even when multiple reports for the same listing or user are allowed.') . '</p>';
  echo '<p><strong>' . __('Statuses') . '</strong></p>';
  echo '<p>' . sprintf(
    __('%s = new; %s = you are working on it; %s = paused; %s = reported user can reply on front; %s / %s / %s = closed.'),
    osc_report_status_label('submitted'),
    osc_report_status_label('in_review'),
    osc_report_status_label('on_hold'),
    osc_report_status_label('awaiting_feedback'),
    osc_report_status_label('resolved'),
    osc_report_status_label('rejected'),
    osc_report_status_label('cancelled')
  ) . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader(){
  ?>
  <h1><?php _e('Reports'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Reports settings - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php');
?>

<div id="general-settings">
  <ul id="error_list"></ul>
  <form name="reports_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="page" value="reports" />
    <input type="hidden" name="action" value="settings_post" />
    <fieldset>
      <div class="form-horizontal">
        <h2 class="render-title"><?php _e('Reports settings'); ?></h2>

        <div class="form-row">
          <div class="form-label"><?php _e('Reports'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" <?php echo (osc_reports_enabled() ? 'checked="checked"' : ''); ?> name="reports_enabled" value="1" /> <?php _e('Allow logged in users to report listings, users and other content'); ?>
              </label>
            </div>
            <span class="help-box"><?php _e('When disabled, report links and report pages are not available on the front.'); ?></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Reports per day'); ?></div>
          <div class="form-controls">
            <input type="number" class="input-small" name="reports_per_day" value="<?php echo osc_esc_html(osc_reports_per_day()); ?>" min="1" step="1" />
            <div class="inpt-desc"><?php _e('reports'); ?></div>
            <span class="help-inline"><?php _e('Maximum number of reports single user can submit per day'); ?></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Multiple reports'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" <?php echo (osc_reports_allow_multiple() ? 'checked="checked"' : ''); ?> name="reports_allow_multiple" value="1" /> <?php _e('Allow the same user to submit more than one report for the same listing or user'); ?>
              </label>
            </div>
            <span class="help-inline"><?php _e('When disabled, a user who already reported a listing or user cannot open the report form again for that target.'); ?></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Email notifications'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" <?php echo (osc_reports_notify_admin() ? 'checked="checked"' : ''); ?> name="reports_notify_admin" value="1" /> <?php _e('Notify admin when new report is submitted'); ?>
              </label>
            </div>

            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" <?php echo (osc_reports_notify_reporter_created() ? 'checked="checked"' : ''); ?> name="reports_notify_reporter_created" value="1" /> <?php _e('Notify reporter when report is received (email only, no front access)'); ?>
              </label>
            </div>

            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" <?php echo (osc_reports_notify_reporter_resolved() ? 'checked="checked"' : ''); ?> name="reports_notify_reporter_resolved" value="1" /> <?php _e('Notify reporter when report is closed (resolved, rejected or cancelled)'); ?>
              </label>
            </div>

            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" <?php echo (osc_reports_notify_owner_created() ? 'checked="checked"' : ''); ?> name="reports_notify_owner_created" value="1" /> <?php _e('Notify reported user when a report about their content is submitted'); ?>
              </label>
            </div>

            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" <?php echo (osc_reports_notify_owner_resolved() ? 'checked="checked"' : ''); ?> name="reports_notify_owner_resolved" value="1" /> <?php _e('Notify reported user when report about their content is closed'); ?>
              </label>
            </div>

            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" <?php echo (osc_reports_notify_comments() ? 'checked="checked"' : ''); ?> name="reports_notify_comments" value="1" /> <?php _e('Notify admin when a new reply is added to a report'); ?>
              </label>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Report conversation'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" <?php echo (osc_reports_feedback_enabled() ? 'checked="checked"' : ''); ?> name="reports_enable_feedback" value="1" /> <?php _e('Allow reported user to reply on front when status is Awaiting feedback (reporter identity is never shown)'); ?>
              </label>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Auto-close'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" <?php echo (osc_reports_auto_close_enabled() ? 'checked="checked"' : ''); ?> name="reports_auto_close_enabled" value="1" />
                <?php printf(__('Automatically close reports awaiting feedback with no activity after %s days'), '<input type="number" class="input-small" name="reports_auto_close_days" value="' . osc_esc_html(osc_reports_auto_close_days()) . '" min="1" step="1" />'); ?>
              </label>
            </div>
            <span class="help-box"><?php _e('Part of report lifetime: closes idle tickets that wait for user feedback.'); ?></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Retention'); ?></div>
          <div class="form-controls">
            <input type="number" class="input-small" name="reports_retention_months" value="<?php echo osc_esc_html(osc_reports_retention_months()); ?>" min="0" step="1" />
            <div class="inpt-desc"><?php _e('months'); ?></div>
            <span class="help-inline"><?php _e('Reports older than given number of months are removed by daily cron, set 0 to keep reports forever'); ?></span>
            <span class="help-box"><?php _e('End of report lifetime: old reports are deleted automatically when retention is greater than 0.'); ?></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Attachment'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <label>
                <input type="checkbox" <?php echo (osc_reports_attachment_enabled() ? 'checked="checked"' : ''); ?> name="reports_attachment_enabled" value="1" /> <?php _e('Allow users to upload 1 attachment to report'); ?>
              </label>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Attachment allowed extensions'); ?></div>
          <div class="form-controls">
            <input type="text" class="input-large" name="reports_attachment_extensions" value="<?php echo osc_esc_html(implode(',', osc_reports_attachment_extensions())); ?>" />
            <span class="help-inline"><?php echo sprintf(__('Comma separated list of allowed file extensions, e.g. jpg,jpeg,png. Maximum file size is %d MB.'), osc_reports_attachment_max_size_mb()); ?></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Enabled reasons'); ?></div>
          <div class="form-controls">
            <?php
              $enabledReasons = osc_reports_enabled_reasons_codes();
              $requiredReasons = osc_report_required_reasons();
            ?>
            <select name="reports_enabled_reasons[]" multiple="multiple" style="min-width:360px;height:170px;">
              <?php foreach(osc_report_reasons() as $code => $label) {
                $isRequired = in_array($code, $requiredReasons, true);
                $typeHint = osc_report_reason_types_label($code);
              ?>
                <option value="<?php echo osc_esc_html($code); ?>" <?php if(in_array($code, $enabledReasons, true)) echo 'selected'; ?> <?php if($isRequired) echo 'disabled="disabled"'; ?>><?php echo $label; ?><?php if($typeHint != '') echo ' (' . $typeHint . ')'; ?><?php if($isRequired) echo ' *'; ?></option>
              <?php } ?>
            </select>
            <span class="help-box"><?php _e('Reasons available on the front report form. Types in parentheses show where the reason is offered. Hold Ctrl/Cmd to select multiple. Items marked with * cannot be disabled.'); ?></span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Enabled statuses'); ?></div>
          <div class="form-controls">
            <?php
              $enabledStatuses = osc_reports_enabled_statuses_codes();
              $requiredStatuses = osc_report_required_statuses();
            ?>
            <select name="reports_enabled_statuses[]" multiple="multiple" style="min-width:360px;height:170px;">
              <?php foreach(osc_report_statuses() as $code => $label) {
                $isRequired = in_array($code, $requiredStatuses, true);
              ?>
                <option value="<?php echo osc_esc_html($code); ?>" <?php if(in_array($code, $enabledStatuses, true)) echo 'selected'; ?> <?php if($isRequired) echo 'disabled="disabled"'; ?>><?php echo $label; ?><?php if($isRequired) echo ' *'; ?></option>
              <?php } ?>
            </select>
            <span class="help-box"><?php _e('Statuses available in report filters and status dropdowns. Hold Ctrl/Cmd to select multiple. Items marked with * cannot be disabled.'); ?></span>
          </div>
        </div>

        <?php osc_run_hook('reports_settings_form'); ?>

        <div class="clear"></div>
        <div class="form-actions">
          <input type="submit" id="save_changes" value="<?php echo osc_esc_html(__('Save changes')); ?>" class="btn btn-submit" />
        </div>
      </div>
    </fieldset>
  </form>
</div>

<?php osc_current_admin_theme_path('parts/footer.php'); ?>
