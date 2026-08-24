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


/**
 * Helper Reports
 * @package Osclass
 * @subpackage Helpers
 * @author Osclass
 */


// Get list of report types (plugins can extend via filter)
function osc_report_types() {
  $types = array(
    'item' => __('Listing'),
    'user' => __('User'),
    'webcontact' => __('Contact form')
  );

  return osc_apply_filter('osc_report_types', $types);
}


// Get list of report reasons (plugins can extend via filter)
function osc_report_reasons() {
  $reasons = array(
    'spam' => __('Spam'),
    'duplicate' => __('Duplicate listing'),
    'fraud' => __('Fraud or scam'),
    'misclassified' => __('Wrong category'),
    'prohibited' => __('Prohibited or banned item'),
    'misleading' => __('Misleading or incorrect information'),
    'sold' => __('Already sold or unavailable'),
    'abusive' => __('Abusive or offensive'),
    'copyright' => __('Copyright or trademark issue'),
    'impersonation' => __('Impersonation or fake profile'),
    'legal' => __('Legal issue'),
    'contact' => __('Contact message'),
    'other' => __('Other')
  );

  return osc_apply_filter('osc_report_reasons', $reasons);
}


// Map each reason to report types it applies to (missing key = all types; plugins can extend via filter)
function osc_report_reason_types() {
  $types = array(
    'spam' => array('item', 'user'),
    'duplicate' => array('item'),
    'fraud' => array('item', 'user'),
    'misclassified' => array('item'),
    'prohibited' => array('item'),
    'misleading' => array('item', 'user'),
    'sold' => array('item'),
    'abusive' => array('item', 'user'),
    'copyright' => array('item', 'user'),
    'impersonation' => array('user'),
    'legal' => array('item', 'user'),
    'contact' => array('webcontact'),
    'other' => array('item', 'user')
  );

  return osc_apply_filter('osc_report_reason_types', $types);
}


// Check if reason applies to given report type (unmapped reasons apply to all types)
function osc_report_reason_applies($reason, $type) {
  $map = osc_report_reason_types();
  $applies = true;
  if(isset($map[$reason]) && is_array($map[$reason]) && !empty($map[$reason])) {
    $applies = in_array($type, $map[$reason], true);
  }

  return (bool)osc_apply_filter('osc_report_reason_applies', $applies, $reason, $type);
}


// Get type labels for a reason (settings / help text)
function osc_report_reason_types_label($reason) {
  $allTypes = osc_report_types();
  $map = osc_report_reason_types();
  $codes = (isset($map[$reason]) && is_array($map[$reason]) && !empty($map[$reason]) ? $map[$reason] : array_keys($allTypes));
  $labels = array();
  foreach($codes as $t) {
    if(isset($allTypes[$t])) {
      $labels[] = $allTypes[$t];
    }
  }

  return implode(', ', $labels);
}


// Map legacy "Mark as" type to a report reason (empty if no match or reason is disabled)
function osc_report_reason_from_mark($as, $type = 'item') {
  $as = strtolower(trim((string)$as));
  if($as === '') {
    return '';
  }

  $map = array(
    'spam' => 'spam',
    'bad' => 'misclassified',
    'badcat' => 'misclassified',
    'bad_classified' => 'misclassified',
    'misclassified' => 'misclassified',
    'duplicated' => 'duplicate',
    'repeated' => 'duplicate',
    'duplicate' => 'duplicate',
    'offensive' => 'abusive',
    'expired' => 'sold',
    'sold' => 'sold'
  );
  $map = osc_apply_filter('osc_report_reason_from_mark', $map);

  if(isset($map[$as])) {
    $reason = $map[$as];
  } else {
    $reason = $as;
  }

  if(!in_array($reason, array_keys(osc_report_reasons_for_type($type)), true)) {
    return '';
  }

  return $reason;
}


// Get list of report statuses (plugins can extend via filter)
function osc_report_statuses() {
  $statuses = array(
    'submitted' => __('Submitted'),
    'in_review' => __('In progress'),
    'on_hold' => __('On hold'),
    'awaiting_feedback' => __('Awaiting feedback'),
    'resolved' => __('Resolved'),
    'rejected' => __('Rejected'),
    'cancelled' => __('Cancelled')
  );

  return osc_apply_filter('osc_report_statuses', $statuses);
}


// Get list of report sources (plugins can extend via filter)
function osc_report_sources() {
  $sources = array(
    'osclass' => __('Osclass'),
    'blog' => __('Blog'),
    'auction' => __('Auction'),
    'business_profile' => __('Business profile')
  );

  return osc_apply_filter('osc_report_sources', $sources);
}


// Get list of statuses that close report (plugins can extend via filter)
function osc_report_closed_statuses() {
  return osc_apply_filter('osc_report_closed_statuses', array('resolved', 'rejected', 'cancelled'));
}


// Check if given status closes report
function osc_report_status_is_closed($status) {
  return in_array($status, osc_report_closed_statuses());
}


// Get next status in report lifecycle
function osc_report_status_next($status) {
  $next = array(
    'submitted' => 'in_review',
    'in_review' => 'resolved',
    'on_hold' => 'in_review',
    'awaiting_feedback' => 'in_review'
  );

  $next = osc_apply_filter('osc_report_status_next', $next);

  return (isset($next[$status]) ? $next[$status] : false);
}


// Get label of report status
function osc_report_status_label($status) {
  $statuses = osc_report_statuses();
  return (isset($statuses[$status]) ? $statuses[$status] : $status);
}


// Get label of report reason
function osc_report_reason_label($reason) {
  $reasons = osc_report_reasons();
  return (isset($reasons[$reason]) ? $reasons[$reason] : $reason);
}


// Get label of report type
function osc_report_type_label($type) {
  $types = osc_report_types();
  return (isset($types[$type]) ? $types[$type] : $type);
}


// Get label of report source
function osc_report_source_label($source) {
  $sources = osc_report_sources();
  return (isset($sources[$source]) ? $sources[$source] : $source);
}


// Reasons that cannot be disabled in settings
function osc_report_required_reasons() {
  return osc_apply_filter('osc_report_required_reasons', array('other'));
}


// Statuses that cannot be disabled in settings
function osc_report_required_statuses() {
  return osc_apply_filter('osc_report_required_statuses', array('submitted', 'resolved'));
}


// Get enabled report reason codes from preference (empty preference = all)
function osc_reports_enabled_reasons_codes() {
  $all = array_keys(osc_report_reasons());
  $required = array_values(array_intersect(osc_report_required_reasons(), $all));
  $raw = trim((string)osc_get_preference('reports_enabled_reasons'));
  if($raw === '') {
    return $all;
  }

  $codes = array();
  foreach(explode(',', $raw) as $code) {
    $code = trim((string)$code);
    if($code !== '' && in_array($code, $all, true)) {
      $codes[] = $code;
    }
  }

  if(empty($codes)) {
    return $all;
  }

  foreach($required as $code) {
    if(!in_array($code, $codes, true)) {
      $codes[] = $code;
    }
  }

  return $codes;
}


// Get enabled report reasons for FO form / settings (optional code always included)
function osc_report_reasons_enabled($include = null) {
  $all = osc_report_reasons();
  $enabled = osc_reports_enabled_reasons_codes();
  $out = array();
  foreach($enabled as $code) {
    if(isset($all[$code])) {
      $out[$code] = $all[$code];
    }
  }

  if($include !== null && $include !== '' && isset($all[$include]) && !isset($out[$include])) {
    $out = array($include => $all[$include]) + $out;
  }

  return (!empty($out) ? $out : $all);
}


// Get enabled report reasons for a given type (item|user). $include is always kept.
function osc_report_reasons_for_type($type, $include = null) {
  $out = osc_report_reasons_enabled($include);
  foreach($out as $code => $label) {
    if($include !== null && $include !== '' && $code === $include) {
      continue;
    }
    if(!osc_report_reason_applies($code, $type)) {
      unset($out[$code]);
    }
  }

  return osc_apply_filter('osc_report_reasons_for_type', $out, $type, $include);
}


// Get enabled report status codes from preference (empty preference = all)
function osc_reports_enabled_statuses_codes() {
  $all = array_keys(osc_report_statuses());
  $required = array_values(array_intersect(osc_report_required_statuses(), $all));
  $raw = trim((string)osc_get_preference('reports_enabled_statuses'));
  if($raw === '') {
    return $all;
  }

  $codes = array();
  foreach(explode(',', $raw) as $code) {
    $code = trim((string)$code);
    if($code !== '' && in_array($code, $all, true)) {
      $codes[] = $code;
    }
  }

  if(empty($codes)) {
    return $all;
  }

  foreach($required as $code) {
    if(!in_array($code, $codes, true)) {
      $codes[] = $code;
    }
  }

  return $codes;
}


// Get enabled report statuses for admin UI (optional code always included)
function osc_report_statuses_enabled($include = null) {
  $all = osc_report_statuses();
  $enabled = osc_reports_enabled_statuses_codes();
  $out = array();
  foreach($enabled as $code) {
    if(isset($all[$code])) {
      $out[$code] = $all[$code];
    }
  }

  if($include !== null && $include !== '' && isset($all[$include]) && !isset($out[$include])) {
    $out = array($include => $all[$include]) + $out;
  }

  return (!empty($out) ? $out : $all);
}


// Flash info when the same target was already reported by this user
function osc_report_add_duplicate_flash($type, $report) {
  $id = (is_array($report) ? (int)$report['pk_i_id'] : 0);
  if($type == 'item') {
    osc_add_flash_info_message(sprintf(_m('A report for the selected listing already exists (report #%d).'), $id));
  } else if($type == 'user') {
    osc_add_flash_info_message(sprintf(_m('A report for the selected user already exists (report #%d).'), $id));
  } else {
    osc_add_flash_info_message(sprintf(_m('A report for the selected %s already exists (report #%d).'), strtolower(osc_report_type_label($type)), $id));
  }
}


// Resolve redirect after duplicate report (admins go to existing report view)
function osc_report_duplicate_redirect_url($type, $report, $fallback_url) {
  $id = (is_array($report) ? (int)$report['pk_i_id'] : 0);

  if(osc_is_admin_user_logged_in() && $id > 0) {
    osc_add_flash_info_message(sprintf(_m('A report already exists (report #%d).'), $id));
    return osc_report_view_url($id);
  }

  osc_report_add_duplicate_flash($type, $report);
  return $fallback_url;
}


// Build avatar initials for report conversation messages
function osc_report_message_initials($name) {
  $name = trim(preg_replace('/\s+/', ' ', (string)$name));
  if($name == '') {
    return '?';
  }

  $parts = explode(' ', $name);
  $initials = osc_substr($parts[0], 0, 1);
  if(count($parts) > 1) {
    $initials .= osc_substr($parts[count($parts) - 1], 0, 1);
  }

  return strtoupper($initials);
}


// Get profile image URL for report conversation author (admin uses default image)
function osc_report_comment_profile_img_url($comment = null, $userId = null) {
  if(is_array($comment) && isset($comment['fk_i_admin_id']) && (int)$comment['fk_i_admin_id'] > 0) {
    return osc_user_profile_img_url(0);
  }

  if(is_array($comment) && isset($comment['fk_i_user_id'])) {
    $userId = (int)$comment['fk_i_user_id'];
  }

  $userId = (int)$userId;
  return osc_user_profile_img_url($userId > 0 ? $userId : 0);
}


// Check if user is the reporter of given report
function osc_report_is_reporter($report, $userId = null) {
  if(!is_array($report)) {
    return false;
  }
  if($userId === null) {
    $userId = osc_logged_user_id();
  }
  return ((int)$userId > 0 && (int)$report['fk_i_reporter_user_id'] === (int)$userId);
}


// Check if user is the reported user of given report
function osc_report_is_reported_user($report, $userId = null) {
  if(!is_array($report)) {
    return false;
  }
  if($userId === null) {
    $userId = osc_logged_user_id();
  }
  return ((int)$userId > 0 && (int)$report['fk_i_user_id'] > 0 && (int)$report['fk_i_user_id'] === (int)$userId);
}


// Front access: reported user, or admin logged in (any report)
function osc_report_user_can_view($report, $userId = null) {
  if(!is_array($report)) {
    return false;
  }
  $can = (osc_is_admin_user_logged_in() || osc_report_is_reported_user($report, $userId));
  return (bool)osc_apply_filter('report_can_view', $can, $report, ($userId === null ? (int)osc_logged_user_id() : (int)$userId));
}


// Front reply: reported user only, when feedback was requested
function osc_report_user_can_comment($report, $userId = null) {
  $can = (
    osc_reports_feedback_enabled()
    && is_array($report)
    && (int)$report['b_open'] === 1
    && $report['s_status'] == 'awaiting_feedback'
    && osc_report_is_reported_user($report, $userId)
  );
  return (bool)osc_apply_filter('report_can_comment', $can, $report, ($userId === null ? (int)osc_logged_user_id() : (int)$userId));
}


// Filter conversation comments for front (hide reporter identity/messages; admins see all)
function osc_report_front_comments($comments, $report) {
  if(!is_array($comments) || !is_array($report)) {
    return array();
  }

  if(osc_is_admin_user_logged_in()) {
    return osc_apply_filter('report_front_comments', $comments, $comments, $report);
  }

  $out = array();
  foreach($comments as $comment) {
    if((int)@$comment['fk_i_admin_id'] > 0) {
      $out[] = $comment;
      continue;
    }
    if((int)@$comment['fk_i_user_id'] > 0 && (int)$comment['fk_i_user_id'] === (int)$report['fk_i_user_id']) {
      $out[] = $comment;
    }
  }

  return osc_apply_filter('report_front_comments', $out, $comments, $report);
}


// Public author label for front conversation (reporter hidden except for admin)
function osc_report_front_author_label($comment, $report) {
  if((int)@$comment['fk_i_admin_id'] > 0) {
    $label = __('Admin');
  } else if(osc_is_admin_user_logged_in()) {
    if(osc_report_is_reported_user($report, @$comment['fk_i_user_id'])) {
      $label = __('Reported user');
    } else if(osc_report_is_reporter($report, @$comment['fk_i_user_id'])) {
      $label = __('Report created by');
    } else {
      $label = __('Participant');
    }
  } else if(osc_report_is_reported_user($report, @$comment['fk_i_user_id'])) {
    $label = __('You');
  } else {
    $label = __('Participant');
  }

  return osc_apply_filter('report_front_author_label', $label, $comment, $report);
}


// Check if reports feature is enabled
function osc_reports_enabled() {
  return getBoolPreference('reports_enabled');
}


// Alias used by themes to detect reports (function_exists) and whether they are enabled
function osc_report_enabled() {
  return osc_reports_enabled();
}


// Check if current visitor should see the report listing link (guests yes; own listing no)
function osc_can_report_item($item_id = null) {
  if(!osc_reports_enabled()) {
    return false;
  }

  $use_view = ($item_id === null || $item_id === '' || (int)$item_id === (int)osc_item_id());
  if($use_view) {
    $item_id = (int)osc_item_id();
    $owner_id = (int)osc_item_user_id();
    $owner_email = (string)osc_item_contact_email();
  } else {
    $item_id = (int)$item_id;
    $item = Item::newInstance()->findByPrimaryKey($item_id);
    if(!$item) {
      return (bool)osc_apply_filter('osc_can_report_item', false, $item_id);
    }
    $owner_id = (int)$item['fk_i_user_id'];
    $owner_email = (isset($item['s_contact_email']) ? (string)$item['s_contact_email'] : '');
  }

  if($item_id <= 0) {
    return false;
  }

  $can = true;
  if(osc_is_web_user_logged_in()) {
    $is_owner = ($owner_id > 0 && (int)osc_logged_user_id() === $owner_id);
    $logged_email = (string)osc_logged_user_email();
    $is_email = ($logged_email != '' && $owner_email != '' && strcasecmp($logged_email, $owner_email) === 0);
    $can = (!$is_owner && !$is_email);
  }

  return (bool)osc_apply_filter('osc_can_report_item', $can, $item_id);
}


// Check if current visitor should see the report user link (guests yes; own profile no)
function osc_can_report_user($user_id = null) {
  if(!osc_reports_enabled()) {
    return false;
  }

  if($user_id === null || $user_id === '') {
    $user_id = osc_user_id();
  }
  $user_id = (int)$user_id;
  if($user_id <= 0) {
    return false;
  }

  $can = true;
  if(osc_is_web_user_logged_in()) {
    $can = ((int)osc_logged_user_id() !== $user_id);
  }

  return (bool)osc_apply_filter('osc_can_report_user', $can, $user_id);
}


// Check if reports database tables are available
function osc_reports_tables_ready($force = false) {
  static $ready = null;

  if(!$force && $ready !== null) {
    return $ready;
  }

  $conn = DBConnectionClass::newInstance()->getOsclassDb();
  $comm = new DBCommandClass($conn);
  $result = $comm->query(sprintf("SHOW TABLES LIKE '%st_report'", DB_TABLE_PREFIX));
  $ready = ($result !== false && $result->numRows() > 0);

  return $ready;
}


// Create reports tables when missing (upgrade recovery, no foreign keys for compatibility)
function osc_ensure_reports_tables() {
  if(osc_reports_tables_ready()) {
    return true;
  }

  $conn = DBConnectionClass::newInstance()->getOsclassDb();
  $comm = new DBCommandClass($conn);

  $comm->query(sprintf("CREATE TABLE IF NOT EXISTS %st_report (
  pk_i_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  fk_i_reporter_user_id INT(10) UNSIGNED NOT NULL,
  fk_i_user_id INT(10) UNSIGNED NULL,
  fk_i_item_id INT(10) UNSIGNED NULL,
  i_reported_id INT(10) UNSIGNED NULL,
  fk_c_locale_code CHAR(5) NULL,
  s_type VARCHAR(20) NOT NULL DEFAULT 'item',
  s_reason VARCHAR(30) NULL,
  s_status VARCHAR(30) NOT NULL DEFAULT 'submitted',
  s_source VARCHAR(30) NOT NULL DEFAULT 'osclass',
  s_comment VARCHAR(2000) NULL,
  s_admin_comment VARCHAR(2000) NULL,
  s_file VARCHAR(80) NULL,
  b_open TINYINT(1) NOT NULL DEFAULT 1,
  dt_status_date DATETIME NULL,
  dt_update_date DATETIME NULL,
  dt_create_date DATETIME NOT NULL,
  PRIMARY KEY (pk_i_id),
  INDEX idx_s_status (s_status),
  INDEX idx_s_type (s_type),
  INDEX idx_b_open (b_open),
  INDEX idx_reporter_date (fk_i_reporter_user_id, dt_create_date),
  INDEX fk_i_user_id (fk_i_user_id),
  INDEX fk_i_item_id (fk_i_item_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'", DB_TABLE_PREFIX));

  $comm->query(sprintf("CREATE TABLE IF NOT EXISTS %st_report_comment (
  pk_i_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  fk_i_report_id INT(10) UNSIGNED NOT NULL,
  fk_i_user_id INT(10) UNSIGNED NULL,
  fk_i_admin_id INT(10) UNSIGNED NULL,
  s_comment VARCHAR(2000) NOT NULL,
  b_admin_seen TINYINT(1) NOT NULL DEFAULT 0,
  dt_date DATETIME NOT NULL,
  PRIMARY KEY (pk_i_id),
  INDEX fk_i_report_id (fk_i_report_id),
  INDEX fk_i_user_id (fk_i_user_id),
  INDEX idx_b_admin_seen (b_admin_seen)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'", DB_TABLE_PREFIX));

  return osc_reports_tables_ready(true);
}


// Get maximum number of reports per user per day
function osc_reports_per_day() {
  return (int)getPreference('reports_per_day');
}


// Check if one user may submit multiple reports for the same listing or user
function osc_reports_allow_multiple() {
  return getBoolPreference('reports_allow_multiple');
}


// Check if admin is notified about new report
function osc_reports_notify_admin() {
  return getBoolPreference('reports_notify_admin');
}


// Check if reporter is notified when report is created
function osc_reports_notify_reporter_created() {
  return getBoolPreference('reports_notify_reporter_created');
}


// Check if reporter is notified when report is resolved/closed
function osc_reports_notify_reporter_resolved() {
  return getBoolPreference('reports_notify_reporter_resolved');
}


// Check if reported user is notified when report is created
function osc_reports_notify_owner_created() {
  return getBoolPreference('reports_notify_owner_created');
}


// Check if reported user is notified when report is resolved/closed
function osc_reports_notify_owner_resolved() {
  return getBoolPreference('reports_notify_owner_resolved');
}


// Check if email notifications on report comments are enabled
function osc_reports_notify_comments() {
  return getBoolPreference('reports_notify_comments');
}


// Check if users can reply to reports (conversation)
function osc_reports_feedback_enabled() {
  return getBoolPreference('reports_enable_feedback');
}


// Check if reports awaiting feedback are closed automatically
function osc_reports_auto_close_enabled() {
  return getBoolPreference('reports_auto_close_enabled');
}


// Get number of days after which reports awaiting feedback are closed
function osc_reports_auto_close_days() {
  return (int)getPreference('reports_auto_close_days');
}


// Get number of months old reports are kept in database
function osc_reports_retention_months() {
  return (int)getPreference('reports_retention_months');
}


// Check if report attachment upload is enabled
function osc_reports_attachment_enabled() {
  return getBoolPreference('reports_attachment_enabled');
}


// Get allowed extensions for report attachment
function osc_reports_attachment_extensions() {
  return array_filter(array_map('trim', explode(',', strtolower((string)getPreference('reports_attachment_extensions')))));
}


// Get maximum report attachment size in megabytes (fixed limit)
function osc_reports_attachment_max_size_mb() {
  return 16;
}


// Get url to report a listing ($item_id defaults to current listing; optional reason is preselected)
function osc_report_item_url($item_id = null, $reason = '') {
  if($item_id === null || $item_id === '') {
    $item_id = osc_item_id();
  }
  $item_id = (int)$item_id;

  if(osc_rewrite_enabled()) {
    $url = osc_base_url(false, true) . osc_get_preference('rewrite_report_item') . '/' . $item_id;
  } else {
    $url = osc_base_url(true) . '?page=report&action=item&id=' . $item_id;
  }

  $reason = osc_report_reason_from_mark($reason, 'item');
  if($reason != '') {
    $url .= (strpos($url, '?') === false ? '?' : '&') . 'reason=' . urlencode($reason);
  }

  return osc_apply_filter('osc_report_item_url', $url, $item_id, $reason);
}


// Get url to report a user ($user_id defaults to current public profile user; optional reason is preselected)
function osc_report_user_url($user_id = null, $reason = '') {
  if($user_id === null || $user_id === '') {
    $user_id = osc_user_id();
  }
  $user_id = (int)$user_id;

  if(osc_rewrite_enabled()) {
    $url = osc_base_url(false, true) . osc_get_preference('rewrite_report_user') . '/' . $user_id;
  } else {
    $url = osc_base_url(true) . '?page=report&action=user&id=' . $user_id;
  }

  $reason = osc_report_reason_from_mark($reason, 'user');
  if($reason != '') {
    $url .= (strpos($url, '?') === false ? '?' : '&') . 'reason=' . urlencode($reason);
  }

  return osc_apply_filter('osc_report_user_url', $url, $user_id, $reason);
}


// Get url to view report conversation
function osc_report_view_url($id) {
  if(osc_rewrite_enabled()) {
    $url = osc_base_url(false, true) . osc_get_preference('rewrite_report_view') . '/' . $id;
  } else {
    $url = osc_base_url(true) . '?page=report&action=view&id=' . $id;
  }

  return osc_apply_filter('osc_report_view_url', $url, $id);
}


// Get generic url to report given object (plugins can pass own type/source and optional reason)
function osc_report_url($type, $id, $reportedId = null, $source = 'osclass', $reason = '') {
  if($type == 'item') {
    $url = osc_report_item_url($id, $reason);
  } else if($type == 'user') {
    $url = osc_report_user_url($id, $reason);
  } else {
    $url = osc_base_url(true) . '?page=report&action=' . osc_esc_html($type) . '&id=' . (int)$id;
    $mapped = osc_report_reason_from_mark($reason, $type);
    if($mapped != '') {
      $url .= (strpos($url, '?') === false ? '?' : '&') . 'reason=' . urlencode($mapped);
    }
  }

  if($reportedId !== null) {
    $url .= (strpos($url, '?') === false ? '?' : '&') . 'reportedId=' . (int)$reportedId;
  }

  if($source != 'osclass') {
    $url .= (strpos($url, '?') === false ? '?' : '&') . 'source=' . osc_esc_html($source);
  }

  return osc_apply_filter('osc_report_url', $url, $type, $id, $reportedId, $source, $reason);
}


// Get server path to report attachment file
function osc_report_attachment_path($file) {
  return osc_uploads_path() . 'report/' . $file;
}


// Get url to report attachment file
function osc_report_attachment_url($file) {
  return UPLOADS_WEB_PATH . 'report/' . $file;
}


// Get current report page mode exported to view (form|view)
function osc_report_mode() {
  return View::newInstance()->_get('report_mode');
}


// Get current report row exported to view
function osc_report() {
  return View::newInstance()->_get('report');
}


// Get comments of current report exported to view
function osc_report_comments() {
  return View::newInstance()->_get('report_comments');
}


// Get report type exported to view (item|user|...)
function osc_report_form_type() {
  return View::newInstance()->_get('report_type');
}


// Get report target id exported to view
function osc_report_form_target_id() {
  return View::newInstance()->_get('report_target_id');
}


// Render complete report submission form (one-line integration for themes)
function osc_report_form($type, $id, $reportedId = null, $source = 'osclass') {
  osc_run_hook('report_top', $type, $id, $reportedId, $source);
  ?>
  <ul id="error_list"></ul>
  <form name="report" id="report_form" action="<?php echo osc_base_url(true); ?>" method="post" <?php if(osc_reports_attachment_enabled()) { ?>enctype="multipart/form-data"<?php } ?>>
    <input type="hidden" name="page" value="report" />
    <input type="hidden" name="action" value="report_post" />
    <input type="hidden" name="type" value="<?php echo osc_esc_html($type); ?>" />
    <input type="hidden" name="id" value="<?php echo (int)$id; ?>" />
    <?php if($reportedId !== null) { ?>
      <input type="hidden" name="reportedId" value="<?php echo (int)$reportedId; ?>" />
    <?php } ?>
    <input type="hidden" name="source" value="<?php echo osc_esc_html($source); ?>" />

    <div class="control-group">
      <label class="control-label" for="reason"><?php _e('Reason'); ?></label>
      <div class="controls input-box">
        <select name="reason" id="reason">
          <?php $selected = osc_report_reason_from_mark(Params::getParam('reason'), $type); ?>
          <option value=""<?php if($selected === '') { ?> selected="selected"<?php } ?>><?php _e('Select a reason...'); ?></option>
          <?php foreach(osc_report_reasons_for_type($type) as $code => $label) { ?>
            <option value="<?php echo osc_esc_html($code); ?>"<?php if($selected === $code) { ?> selected="selected"<?php } ?>><?php echo $label; ?></option>
          <?php } ?>
        </select>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="comment"><?php _e('Comment'); ?></label>
      <div class="controls textarea input-box">
        <textarea name="comment" id="comment" rows="4"></textarea>
      </div>
    </div>

    <?php if(osc_reports_attachment_enabled()) { ?>
      <div class="control-group">
        <label class="control-label" for="attachment"><?php _e('Attachment'); ?></label>
        <div class="controls input-box">
          <input type="file" name="attachment" id="attachment" />
          <span class="help-block"><?php echo sprintf(__('Allowed file types: %s. Maximum size: %d MB'), implode(', ', osc_reports_attachment_extensions()), osc_reports_attachment_max_size_mb()); ?></span>
        </div>
      </div>
    <?php } ?>

    <?php osc_run_hook('report_form_fields', $type, $id, $reportedId, $source); ?>

    <div class="control-group">
      <div class="controls">
        <?php osc_run_hook('report_form', $type, $id, $reportedId, $source); ?>
        <?php osc_show_recaptcha('report'); ?>
        <button type="submit" class="btn btn-primary"><?php _e('Send report'); ?></button>
      </div>
    </div>
  </form>
  <?php
  ReportForm::js_validation();
  osc_run_hook('report_bottom', $type, $id, $reportedId, $source);
}


// Remember if the current contact form submit was stored as a report
function osc_report_web_contact_created($set = null) {
  static $created = false;
  if($set !== null) {
    $created = (bool)$set;
  }
  return $created;
}


// Create a webcontact report from website contact form data (no target id)
function osc_report_create_from_web_contact($params) {
  $name = trim(strip_tags((string)(isset($params['yourName']) ? $params['yourName'] : Params::getParam('yourName'))));
  $email = trim(strip_tags((string)(isset($params['yourEmail']) ? $params['yourEmail'] : Params::getParam('yourEmail'))));
  $subject = trim(strip_tags((string)(isset($params['contact_subject']) ? $params['contact_subject'] : Params::getParam('subject'))));
  $message = trim(strip_tags((string)Params::getParam('message')));

  $lines = array();
  if($name != '' || $email != '') {
    $from = $name;
    if($email != '') {
      $from .= ($from != '' ? ' <' . $email . '>' : $email);
    }
    $lines[] = sprintf(__('From: %s'), $from);
  }
  if($subject != '') {
    $lines[] = sprintf(__('Subject: %s'), $subject);
  }
  if($message != '') {
    if(!empty($lines)) {
      $lines[] = '';
    }
    $lines[] = $message;
  }

  $comment = trim(implode("\n", $lines));
  if($comment == '') {
    $comment = __('Website contact form');
  }
  if(osc_strlen($comment) > 2000) {
    $comment = osc_substr($comment, 0, 2000);
  }

  $data = array(
    'fk_i_reporter_user_id' => (osc_is_web_user_logged_in() ? (int)osc_logged_user_id() : 0),
    'fk_c_locale_code' => osc_current_user_locale(),
    's_type' => 'webcontact',
    's_reason' => 'contact',
    's_source' => 'osclass',
    's_comment' => $comment
  );

  $flash_error = osc_apply_filter('pre_webcontact_report_error', '', $data);
  if($flash_error != '') {
    return false;
  }

  osc_run_hook('pre_report_add', $data);

  $reportId = Report::newInstance()->createReport($data);
  if(!$reportId) {
    return false;
  }

  $attachment = (isset($params['attachment']) ? $params['attachment'] : null);
  if(is_array($attachment) && isset($attachment['path']) && $attachment['path'] != '' && file_exists($attachment['path'])) {
    $ext = strtolower(pathinfo((string)$attachment['name'], PATHINFO_EXTENSION));
    $ext = preg_replace('/[^a-z0-9]/', '', $ext);
    $blocked = array('php', 'phtml', 'phar', 'exe', 'js', 'cgi', 'pl');
    $size_ok = ((int)@filesize($attachment['path']) <= osc_reports_attachment_max_size_mb() * 1024 * 1024);

    if($ext != '' && $size_ok && !in_array($ext, $blocked, true)) {
      $upload_dir = osc_uploads_path() . 'report/';
      if(!file_exists($upload_dir)) {
        @mkdir($upload_dir, 0755, true);
        @file_put_contents($upload_dir . 'index.php', '<?php // Silence is golden');
      }

      $file_name = $reportId . '_' . strtolower(osc_genRandomPassword(8)) . '.' . $ext;
      if(@copy($attachment['path'], $upload_dir . $file_name)) {
        Report::newInstance()->update(array('s_file' => $file_name), array('pk_i_id' => $reportId));
      }
    }
  }

  $report = Report::newInstance()->findByPrimaryKey($reportId);
  osc_run_hook('after_report_add', $report);
  osc_run_hook('posted_report', $report);

  if(osc_reports_notify_admin()) {
    osc_run_hook('hook_email_report_admin', $report);
  }

  osc_report_web_contact_created(true);

  return $reportId;
}


// Store website contact form as a report when the option is enabled
function osc_report_hook_web_contact($params) {
  if(!osc_web_contact_create_report()) {
    return;
  }
  if(!osc_ensure_reports_tables()) {
    osc_add_flash_error_message(_m('Your message could not be stored. Please try again later.'));
    return;
  }
  if(!osc_report_create_from_web_contact($params)) {
    osc_add_flash_error_message(_m('Your message could not be stored. Please try again later.'));
  }
}


// Skip contact form email when contact messages are stored as reports
function osc_report_filter_contact_send_mail($send, $params) {
  if(!$send) {
    return false;
  }
  if(osc_web_contact_create_report()) {
    return false;
  }
  return $send;
}

/* file end: ./oc-includes/osclass/helpers/hReport.php */
