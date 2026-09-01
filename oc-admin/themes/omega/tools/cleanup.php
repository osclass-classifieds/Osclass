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


//customize Head
function customHead() { }
osc_add_hook('admin_header','customHead', 10);

function addHelp() {
  echo '<p>' . __("Clean up not validated, disabled or spam data in osclass") . '</p>';
}
osc_add_hook('help_box','addHelp');

function customPageHeader(){ 
  ?> 
  <h1><?php _e('Cleanup data'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Cleanup - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');


osc_current_admin_theme_path('parts/header.php');

$limit_days = 7;
$limit_date = date('Y-m-d', strtotime('-' . $limit_days . ' days'));

$items_inactive_count = osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_item WHERE b_active != 1 AND dt_pub_date <= "%s"', DB_TABLE_PREFIX, $limit_date));
$items_blocked_spam_count = osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_item WHERE b_enabled = 0 OR b_spam = 1', DB_TABLE_PREFIX));
$items_expired_count = osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_item WHERE dt_expiration <= "%s"', DB_TABLE_PREFIX, $limit_date));

$users_inactive_count = osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_user WHERE b_active != 1 AND dt_reg_date <= "%s"', DB_TABLE_PREFIX, $limit_date));
$users_blocked_count = osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_user WHERE b_enabled = 0', DB_TABLE_PREFIX));

$comments_inactive_count = osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_item_comment WHERE b_active != 1 AND dt_pub_date <= "%s"', DB_TABLE_PREFIX, $limit_date));
$comments_blocked_count = osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_item_comment WHERE b_enabled = 0', DB_TABLE_PREFIX));

$alerts_unsubscribed_count = osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_alerts WHERE b_active = 0 AND coalesce(dt_unsub_date, dt_date) <= "%s"', DB_TABLE_PREFIX, $limit_date));
$expired_ban_rules_count = osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_ban_rule WHERE dt_expire_date <= "%s"', DB_TABLE_PREFIX, $limit_date));

$limit_months = (osc_logging_months() > 0 ? osc_logging_months() : 24);
$limit_date_log = date('Y-m-d', strtotime('-' . $limit_months . ' months'));

$old_logs_count = osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_log WHERE date(dt_date) <= "%s"', DB_TABLE_PREFIX, $limit_date_log));

?>

<!-- cleanup forms -->
<div id="cleanup-settings">
  <h2 class="render-title"><?php _e('Inactive listings'); ?></h2>
  <form id="cleanup_form" name="cleanup_form" action="<?php echo osc_admin_base_url(true); ?>" enctype="multipart/form-data" method="post">
    <input type="hidden" name="page" value="tools" />
    <input type="hidden" name="action" value="cleanup_post" />
    <input type="hidden" name="type" value="items_inactive" />
    <fieldset>
      <div class="form-horizontal">
        <div class="form-row">
          <p><?php echo sprintf(__('Remove listings those has not been validated in last %d days. These data are usually spam/unwanted and are redundant for your installation.'), $limit_days); ?></p>
          <p><?php echo sprintf(__('Total number of listings matching criteria: %s'), '<strong>' . $items_inactive_count . '</strong>'); ?></p>
        </div>

        <div class="form-actions">
          <input type="submit" value="<?php echo osc_esc_html( __('Clean up now') ); ?>" class="btn btn-submit" />
        </div>
      </div>
    </fieldset>
  </form>
</div>

<div id="cleanup-settings">
  <h2 class="render-title"><?php _e('Blocked & Spam listings'); ?></h2>
  <form id="cleanup_form" name="cleanup_form" action="<?php echo osc_admin_base_url(true); ?>" enctype="multipart/form-data" method="post">
    <input type="hidden" name="page" value="tools" />
    <input type="hidden" name="action" value="cleanup_post" />
    <input type="hidden" name="type" value="items_blocked_spam" />
    <fieldset>
      <div class="form-horizontal">
        <div class="form-row">
          <p><?php echo __('Remove listings those has been marked as spam, or has been disabled by you or your moderators.'); ?></p>
          <p><?php echo sprintf(__('Total number of listings matching criteria: %s'), '<strong>' . $items_blocked_spam_count . '</strong>'); ?></p>
        </div>

        <div class="form-actions">
          <input type="submit" value="<?php echo osc_esc_html( __('Clean up now') ); ?>" class="btn btn-submit" />
        </div>
      </div>
    </fieldset>
  </form>
</div>

<div id="cleanup-settings">
  <h2 class="render-title"><?php _e('Expired listings'); ?></h2>
  <form id="cleanup_form" name="cleanup_form" action="<?php echo osc_admin_base_url(true); ?>" enctype="multipart/form-data" method="post">
    <input type="hidden" name="page" value="tools" />
    <input type="hidden" name="action" value="cleanup_post" />
    <input type="hidden" name="type" value="items_expired" />
    <fieldset>
      <div class="form-horizontal">
        <div class="form-row">
          <p><?php echo sprintf(__('Remove listings those has expired more than %d days ago and were not removed or reactivated. These listings are not visible on your site.'), $limit_days); ?></p>
          <p><?php echo sprintf(__('Total number of listings matching criteria: %s'), '<strong>' . $items_expired_count . '</strong>'); ?></p>
        </div>

        <div class="form-actions">
          <input type="submit" value="<?php echo osc_esc_html( __('Clean up now') ); ?>" class="btn btn-submit" />
        </div>
      </div>
    </fieldset>
  </form>
</div>

<div id="cleanup-settings">
  <h2 class="render-title"><?php _e('Inactive users'); ?></h2>
  <form id="cleanup_form" name="cleanup_form" action="<?php echo osc_admin_base_url(true); ?>" enctype="multipart/form-data" method="post">
    <input type="hidden" name="page" value="tools" />
    <input type="hidden" name="action" value="cleanup_post" />
    <input type="hidden" name="type" value="users_inactive" />
    <fieldset>
      <div class="form-horizontal">
        <div class="form-row">
          <p><?php echo sprintf(__('Remove users those has registered more than %d days ago did not activated their account.'), $limit_days); ?></p>
          <p><?php echo sprintf(__('Total number of users matching criteria: %s'), '<strong>' . $users_inactive_count . '</strong>'); ?></p>
        </div>

        <div class="form-actions">
          <input type="submit" value="<?php echo osc_esc_html( __('Clean up now') ); ?>" class="btn btn-submit" />
        </div>
      </div>
    </fieldset>
  </form>
</div>

<div id="cleanup-settings">
  <h2 class="render-title"><?php _e('Blocked users'); ?></h2>
  <form id="cleanup_form" name="cleanup_form" action="<?php echo osc_admin_base_url(true); ?>" enctype="multipart/form-data" method="post">
    <input type="hidden" name="page" value="tools" />
    <input type="hidden" name="action" value="cleanup_post" />
    <input type="hidden" name="type" value="users_blocked" />
    <fieldset>
      <div class="form-horizontal">
        <div class="form-row">
          <p><?php echo sprintf(__('Remove users those has been blocked more than %d days (based on last access date). This will remove also all listings published by this user.'), $limit_days); ?></p>
          <p><?php echo sprintf(__('Total number of users matching criteria: %s'), '<strong>' . $users_blocked_count . '</strong>'); ?></p>
        </div>

        <div class="form-actions">
          <input type="submit" value="<?php echo osc_esc_html( __('Clean up now') ); ?>" class="btn btn-submit" />
        </div>
      </div>
    </fieldset>
  </form>
</div>

<div id="cleanup-settings">
  <h2 class="render-title"><?php _e('Inactive comments'); ?></h2>
  <form id="cleanup_form" name="cleanup_form" action="<?php echo osc_admin_base_url(true); ?>" enctype="multipart/form-data" method="post">
    <input type="hidden" name="page" value="tools" />
    <input type="hidden" name="action" value="cleanup_post" />
    <input type="hidden" name="type" value="comments_inactive" />
    <fieldset>
      <div class="form-horizontal">
        <div class="form-row">
          <p><?php echo sprintf(__('Remove comments those has not been activated for more than %d days.'), $limit_days); ?></p>
          <p><?php echo sprintf(__('Total number of comments matching criteria: %s'), '<strong>' . $comments_inactive_count . '</strong>'); ?></p>
        </div>

        <div class="form-actions">
          <input type="submit" value="<?php echo osc_esc_html( __('Clean up now') ); ?>" class="btn btn-submit" />
        </div>
      </div>
    </fieldset>
  </form>
</div>

<div id="cleanup-settings">
  <h2 class="render-title"><?php _e('Blocked comments'); ?></h2>
  <form id="cleanup_form" name="cleanup_form" action="<?php echo osc_admin_base_url(true); ?>" enctype="multipart/form-data" method="post">
    <input type="hidden" name="page" value="tools" />
    <input type="hidden" name="action" value="cleanup_post" />
    <input type="hidden" name="type" value="comments_blocked" />
    <fieldset>
      <div class="form-horizontal">
        <div class="form-row">
          <p><?php echo __('Remove comments those has been blocked by your or your moderators.'); ?></p>
          <p><?php echo sprintf(__('Total number of comments matching criteria: %s'), '<strong>' . $comments_blocked_count . '</strong>'); ?></p>
        </div>

        <div class="form-actions">
          <input type="submit" value="<?php echo osc_esc_html( __('Clean up now') ); ?>" class="btn btn-submit" />
        </div>
      </div>
    </fieldset>
  </form>
</div>

<div id="cleanup-settings">
  <h2 class="render-title"><?php _e('Unsubscribed alerts'); ?></h2>
  <form id="cleanup_form" name="cleanup_form" action="<?php echo osc_admin_base_url(true); ?>" enctype="multipart/form-data" method="post">
    <input type="hidden" name="page" value="tools" />
    <input type="hidden" name="action" value="cleanup_post" />
    <input type="hidden" name="type" value="unsubscribed_alerts" />
    <fieldset>
      <div class="form-horizontal">
        <div class="form-row">
          <p><?php echo sprintf(__('Remove inactive alerts those has been unsubscribed for more than %d days.'), $limit_days); ?></p>
          <p><?php echo sprintf(__('Total number of alerts matching criteria: %s'), '<strong>' . $alerts_unsubscribed_count . '</strong>'); ?></p>
        </div>

        <div class="form-actions">
          <input type="submit" value="<?php echo osc_esc_html( __('Clean up now') ); ?>" class="btn btn-submit" />
        </div>
      </div>
    </fieldset>
  </form>
</div>


<div id="cleanup-settings">
  <h2 class="render-title"><?php _e('Expired ban rules'); ?></h2>
  <form id="cleanup_form" name="cleanup_form" action="<?php echo osc_admin_base_url(true); ?>" enctype="multipart/form-data" method="post">
    <input type="hidden" name="page" value="tools" />
    <input type="hidden" name="action" value="cleanup_post" />
    <input type="hidden" name="type" value="expired_ban_rules" />
    <fieldset>
      <div class="form-horizontal">
        <div class="form-row">
          <p><?php echo sprintf(__('Remove expired ban rules those expired more than %d days ago.'), $limit_days); ?></p>
          <p><?php echo sprintf(__('Total number of expired ban rules matching criteria: %s'), '<strong>' . $expired_ban_rules_count . '</strong>'); ?></p>
        </div>

        <div class="form-actions">
          <input type="submit" value="<?php echo osc_esc_html( __('Clean up now') ); ?>" class="btn btn-submit" />
        </div>
      </div>
    </fieldset>
  </form>
</div>



<div id="cleanup-settings">
  <h2 class="render-title"><?php _e('Old Osclass logs'); ?></h2>
  <form id="cleanup_form" name="cleanup_form" action="<?php echo osc_admin_base_url(true); ?>" enctype="multipart/form-data" method="post">
    <input type="hidden" name="page" value="tools" />
    <input type="hidden" name="action" value="cleanup_post" />
    <input type="hidden" name="type" value="old_logs" />
    <fieldset>
      <div class="form-horizontal">
        <div class="form-row">
          <p><?php echo sprintf(__('Remove old Osclass logs older than %d months.'), $limit_months); ?></p>
          <p><?php echo sprintf(__('Total number of logs matching criteria: %s'), '<strong>' . $old_logs_count . '</strong>'); ?></p>
        </div>

        <div class="form-actions">
          <input type="submit" value="<?php echo osc_esc_html( __('Clean up now') ); ?>" class="btn btn-submit" />
        </div>
      </div>
    </fieldset>
  </form>
</div>

<!-- /cleanup forms -->
<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>