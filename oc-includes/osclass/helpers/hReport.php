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


// Get list of report statuses (plugins can extend via filter). Waiting statuses tell whose turn it is
function osc_report_statuses() {
  $statuses = array(
    'waiting_admin' => __('Waiting for admin'),
    'waiting_customer' => __('Waiting for customer'),
    'in_review' => __('In progress'),
    'on_hold' => __('On hold'),
    'resolved' => __('Resolved'),
    'rejected' => __('Rejected'),
    'cancelled' => __('Cancelled')
  );

  return osc_apply_filter('osc_report_statuses', $statuses);
}


// Status codes used before 8.4.1 mapped to current ones (old links, settings and plugin calls)
function osc_report_status_legacy_map() {
  return array('submitted' => 'waiting_admin', 'awaiting_feedback' => 'waiting_customer');
}


// Get current status code for given (possibly legacy) code
function osc_report_status_code($status) {
  $map = osc_report_status_legacy_map();
  return (isset($map[$status]) ? $map[$status] : (string)$status);
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
  $status = osc_report_status_code($status);
  $next = array(
    'waiting_admin' => 'in_review',
    'in_review' => 'resolved',
    'on_hold' => 'in_review',
    'waiting_customer' => 'in_review'
  );

  $next = osc_apply_filter('osc_report_status_next', $next);

  return (isset($next[$status]) ? $next[$status] : false);
}


// Get label of report status
function osc_report_status_label($status) {
  $statuses = osc_report_statuses();
  $status = (isset($statuses[$status]) ? $status : osc_report_status_code($status));
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
  return osc_apply_filter('osc_report_required_statuses', array('waiting_admin', 'resolved'));
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


// Get reasons a website contact form can send in field "reason" (plugins map own reasons to type webcontact)
function osc_report_contact_reasons() {
  return osc_report_reasons_for_type('webcontact');
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
    $code = osc_report_status_code(trim((string)$code));
    if($code !== '' && in_array($code, $all, true) && !in_array($code, $codes, true)) {
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


// Get secret used to sign report access keys (created on first use)
function osc_report_secret() {
  $secret = (string)osc_get_preference('report_secret');
  if(strlen($secret) < 32) {
    $secret = osc_genRandomPassword(48);
    osc_set_preference('report_secret', $secret);
    osc_reset_preferences();
  }

  return $secret;
}


// Get contact of report party (reporter = who created the report, reported = reported user or listing owner)
// Returns array('user_id', 'email', 'name'); empty email when the party cannot be reached
function osc_report_party_contact($report, $party) {
  $out = array('user_id' => 0, 'email' => '', 'name' => '');
  if(!is_array($report)) {
    return $out;
  }

  if($party == 'reporter') {
    $out['user_id'] = (int)$report['fk_i_reporter_user_id'];
    $out['email'] = trim((string)@$report['s_reporter_email']);
    $out['name'] = trim((string)@$report['s_reporter_name']);
  } else if($party == 'reported') {
    $out['user_id'] = (int)$report['fk_i_user_id'];
    if($out['user_id'] <= 0 && (int)$report['fk_i_item_id'] > 0) {
      $item = Item::newInstance()->findByPrimaryKey((int)$report['fk_i_item_id']);
      if($item) {
        $out['email'] = trim((string)@$item['s_contact_email']);
        $out['name'] = trim((string)@$item['s_contact_name']);
      }
    }
  }

  if($out['user_id'] > 0 && ($out['email'] == '' || $out['name'] == '')) {
    $user = User::newInstance()->findByPrimaryKey($out['user_id']);
    if($user) {
      $out['email'] = ($out['email'] != '' ? $out['email'] : (string)$user['s_email']);
      $out['name'] = ($out['name'] != '' ? $out['name'] : (string)$user['s_name']);
    }
  }

  return osc_apply_filter('report_party_contact', $out, $report, $party);
}


// Get access key of report front view for role (reporter|reported|admin): signed JSON of report id, role and email of the person
// Admin key needs admin email; reporter and reported keys use the party email. Empty when there is no email
function osc_report_access_key($report, $role, $email = '') {
  if(!is_array($report)) {
    $report = Report::newInstance()->findByPrimaryKey((int)$report);
  }
  if(!is_array($report) || !isset($report['pk_i_id']) || !in_array($role, array('reporter', 'reported', 'admin'), true)) {
    return '';
  }

  if($email == '' && $role != 'admin') {
    $contact = osc_report_party_contact($report, $role);
    $email = $contact['email'];
  }

  $email = strtolower(trim((string)$email));
  if($email == '') {
    return '';
  }

  return substr(hash_hmac('sha256', json_encode(array('id' => (int)$report['pk_i_id'], 'role' => $role, 'email' => $email)), osc_report_secret()), 0, 32);
}


// Get front url of report for party or admin, with access key (opens without login)
function osc_report_access_url($report, $role, $email = '') {
  return osc_report_view_url((int)$report['pk_i_id'], osc_report_access_key($report, $role, $email));
}


// Who is looking at the report on front: array('role' => admin|reporter|reported|'', 'admin_id', 'user_id', 'key')
// Order: access key from url, logged admin, logged user (reporter or reported user), access key remembered in session for this report
function osc_report_viewer($report, $key = null) {
  static $cache = array();
  $out = array('role' => '', 'admin_id' => 0, 'user_id' => 0, 'key' => '');
  if(!is_array($report) || !isset($report['pk_i_id'])) {
    return $out;
  }

  $id = (int)$report['pk_i_id'];
  $url_key = strtolower(trim((string)($key === null ? Params::getParam('key') : $key)));
  $keys = Session::newInstance()->_get('report_keys');
  $keys = (is_array($keys) ? $keys : array());
  $cache_id = $id . '|' . $url_key . '|' . (int)osc_logged_user_id() . '|' . (int)osc_logged_admin_id();
  if(isset($cache[$cache_id])) {
    return $cache[$cache_id];
  }

  $sources = array('url' => $url_key, 'admin' => '', 'user' => '', 'session' => (isset($keys[$id]) ? strtolower((string)$keys[$id]) : ''));
  foreach($sources as $source => $k) {
    if($source == 'admin') {
      if(osc_is_admin_user_logged_in()) {
        $out = array('role' => 'admin', 'admin_id' => (int)osc_logged_admin_id(), 'user_id' => 0, 'key' => '');
        break;
      }
      continue;
    }

    if($source == 'user') {
      $email = strtolower(trim((string)osc_logged_user_email()));
      if(osc_is_web_user_logged_in() && (osc_report_is_reporter($report) || ($email != '' && $email === strtolower(trim((string)@$report['s_reporter_email']))))) {
        $out = array('role' => 'reporter', 'admin_id' => 0, 'user_id' => (int)osc_logged_user_id(), 'key' => '');
        break;
      } else if(osc_is_web_user_logged_in() && osc_report_is_reported_user($report)) {
        $out = array('role' => 'reported', 'admin_id' => 0, 'user_id' => (int)osc_logged_user_id(), 'key' => '');
        break;
      }
      continue;
    }

    if(!preg_match('/^[a-f0-9]{32}$/', $k)) {
      continue;
    }

    foreach(array('reporter', 'reported') as $role) {
      $contact = osc_report_party_contact($report, $role);
      if($contact['email'] != '' && hash_equals(osc_report_access_key($report, $role, $contact['email']), $k)) {
        $out = array('role' => $role, 'admin_id' => 0, 'user_id' => (int)$contact['user_id'], 'key' => $k);
        break 2;
      }
    }

    foreach(Admin::newInstance()->listAll() as $admin) {
      if(!empty($admin['s_email']) && hash_equals(osc_report_access_key($report, 'admin', $admin['s_email']), $k)) {
        $out = array('role' => 'admin', 'admin_id' => (int)$admin['pk_i_id'], 'user_id' => 0, 'key' => $k);
        break 2;
      }
    }
  }

  // Remember valid key from url, so replies and refreshes work with plain report url. Admin acting through a key (no login) is logged once per session
  if($out['key'] != '' && $out['key'] === $url_key) {
    if($out['role'] == 'admin' && (!isset($keys[$id]) || $keys[$id] !== $url_key)) {
      Log::newInstance()->insertLog('report', 'access_key', $id, 'admin key', 'admin', $out['admin_id']);
    }
    $keys[$id] = $url_key;
    Session::newInstance()->_set('report_keys', array_slice($keys, -20, 20, true));
  }

  $out = osc_apply_filter('report_viewer', $out, $report);
  $cache[$cache_id] = $out;

  return $out;
}


// Remember that party (reporter|reported|admin) saw the conversation now; written at most once in 30 seconds
function osc_report_mark_seen($report, $party) {
  if(!is_array($report) || !in_array($party, array('reporter', 'reported', 'admin'), true)) {
    return false;
  }

  if(!empty($report['dt_seen_' . $party]) && strtotime((string)$report['dt_seen_' . $party]) > time() - 30) {
    return true;
  }

  return (bool)Report::newInstance()->update(array('dt_seen_' . $party => date('Y-m-d H:i:s')), array('pk_i_id' => (int)$report['pk_i_id']));
}


// Smart reply email to party (reporter|reported|admin): none while it is active in the chat (seen in last 90 seconds),
// and only one until it opens the conversation again (no second email while the first one is unread). Marks party as notified when allowed
function osc_report_notify_allowed($report, $party) {
  $report = (is_array($report) ? Report::newInstance()->findByPrimaryKey((int)$report['pk_i_id']) : false);
  if(!is_array($report) || !in_array($party, array('reporter', 'reported', 'admin'), true)) {
    return false;
  }

  $seen = (!empty($report['dt_seen_' . $party]) ? strtotime((string)$report['dt_seen_' . $party]) : 0);
  $notified = (!empty($report['dt_notified_' . $party]) ? strtotime((string)$report['dt_notified_' . $party]) : 0);
  $allowed = ($seen < time() - 90 && ($notified == 0 || $notified < $seen));
  $allowed = (bool)osc_apply_filter('report_notify_allowed', $allowed, $report, $party);

  if($allowed) {
    Report::newInstance()->update(array('dt_notified_' . $party => date('Y-m-d H:i:s')), array('pk_i_id' => (int)$report['pk_i_id']));
  }

  return $allowed;
}


// Get role of current front viewer of report (admin|reporter|reported|'')
function osc_report_viewer_role($report = null) {
  $v = osc_report_viewer(($report === null ? osc_report() : $report));
  return $v['role'];
}


// Get conversation party of comment (reporter|reported); rows from before 8.4.1 have no party and follow the old rules
function osc_report_comment_party($comment, $report) {
  $party = (string)@$comment['s_party'];
  if($party == 'reporter' || $party == 'reported') {
    return $party;
  }

  if((int)@$comment['fk_i_user_id'] > 0) {
    return ((int)$comment['fk_i_user_id'] === (int)$report['fk_i_reporter_user_id'] ? 'reporter' : 'reported');
  }

  return ((int)$report['fk_i_user_id'] > 0 && $report['s_type'] != 'webcontact' ? 'reported' : 'reporter');
}


// Get party the admin replies to by default: who wrote last, else reporter when reachable, else reported user
function osc_report_default_party($report, $comments = null) {
  $comments = (is_array($comments) ? $comments : Report::newInstance()->getComments($report['pk_i_id']));
  foreach(array_reverse($comments) as $comment) {
    if((int)$comment['fk_i_admin_id'] <= 0) {
      return osc_report_comment_party($comment, $report);
    }
  }

  $reporter = osc_report_party_contact($report, 'reporter');
  return ($reporter['email'] != '' || $reporter['user_id'] > 0 ? 'reporter' : 'reported');
}


// Status as reporter or reported user reads it: both conversations share one status, so whose turn it is comes from the last message of their own conversation
function osc_report_viewer_status($report) {
  $status = (string)$report['s_status'];
  $role = osc_report_viewer_role($report);
  if(($role == 'reporter' || $role == 'reported') && ($status == 'waiting_admin' || $status == 'waiting_customer')) {
    $last = null;
    foreach(Report::newInstance()->getComments($report['pk_i_id']) as $comment) {
      $last = (osc_report_comment_party($comment, $report) == $role ? $comment : $last);
    }
    $status = ($last !== null && (int)$last['fk_i_admin_id'] > 0 ? 'waiting_customer' : 'waiting_admin');
  }

  return osc_apply_filter('report_viewer_status', $status, $report, $role);
}


// Front access: admin, reporter or reported user (logged in or with access key)
function osc_report_user_can_view($report, $userId = null) {
  if(!is_array($report)) {
    return false;
  }

  if($userId === null) {
    $viewer = osc_report_viewer($report);
    $can = ($viewer['role'] != '');
  } else {
    $can = (osc_is_admin_user_logged_in() || osc_report_is_reporter($report, $userId) || osc_report_is_reported_user($report, $userId));
  }

  return (bool)osc_apply_filter('report_can_view', $can, $report, ($userId === null ? (int)osc_logged_user_id() : (int)$userId));
}


// Front reply: open report; admin always, reporter and reported user when replies are enabled
function osc_report_user_can_comment($report, $userId = null) {
  $role = '';
  if(is_array($report)) {
    if($userId === null) {
      $viewer = osc_report_viewer($report);
      $role = $viewer['role'];
    } else if(osc_report_is_reporter($report, $userId)) {
      $role = 'reporter';
    } else if(osc_report_is_reported_user($report, $userId)) {
      $role = 'reported';
    }
  }

  $can = (
    is_array($report)
    && (int)$report['b_open'] === 1
    && ($role == 'admin' || (osc_reports_feedback_enabled() && ($role == 'reporter' || $role == 'reported')))
  );

  return (bool)osc_apply_filter('report_can_comment', $can, $report, ($userId === null ? (int)osc_logged_user_id() : (int)$userId));
}


// Filter conversation comments for front: admin sees both conversations, reporter and reported user only their own with admin
function osc_report_front_comments($comments, $report) {
  if(!is_array($comments) || !is_array($report)) {
    return array();
  }

  $role = osc_report_viewer_role($report);
  if($role == 'admin') {
    return osc_apply_filter('report_front_comments', $comments, $comments, $report);
  }

  $out = array();
  foreach($comments as $comment) {
    if($role != '' && osc_report_comment_party($comment, $report) == $role) {
      $out[] = $comment;
    }
  }

  return osc_apply_filter('report_front_comments', $out, $comments, $report);
}


// Public author label for front conversation (reporter identity is shown to admin only)
function osc_report_front_author_label($comment, $report) {
  $role = osc_report_viewer_role($report);
  $party = osc_report_comment_party($comment, $report);

  if((int)@$comment['fk_i_admin_id'] > 0) {
    $label = ($role == 'admin' && trim((string)@$comment['s_admin_name']) != '' ? (string)$comment['s_admin_name'] : __('Admin'));
  } else if($role == $party) {
    $label = __('You');
  } else if($role == 'admin') {
    $label = ($party == 'reported' ? __('Reported user') : __('Report created by'));
  } else {
    $label = __('Participant');
  }

  return osc_apply_filter('report_front_author_label', $label, $comment, $report);
}


// Get comment as plain data for front templates and ajax (text is not escaped)
function osc_report_comment_data($comment, $report) {
  $role = osc_report_viewer_role($report);
  $party = osc_report_comment_party($comment, $report);
  $is_admin = ((int)@$comment['fk_i_admin_id'] > 0);
  $time = strtotime((string)$comment['dt_date']);

  $data = array(
    'id' => (int)$comment['pk_i_id'],
    'party' => $party,
    'admin' => $is_admin,
    'mine' => ($is_admin ? $role == 'admin' : $role == $party),
    'author' => osc_report_front_author_label($comment, $report),
    'text' => (string)$comment['s_comment'],
    'date' => (string)$comment['dt_date'],
    'date_label' => osc_format_date($comment['dt_date']) . ' ' . date(osc_time_format(), $time)
  );

  return osc_apply_filter('report_comment_data', $data, $comment, $report);
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
    return osc_ensure_reports_columns();
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
  s_status VARCHAR(30) NOT NULL DEFAULT 'waiting_admin',
  s_source VARCHAR(30) NOT NULL DEFAULT 'osclass',
  s_reporter_name VARCHAR(100) NULL,
  s_reporter_email VARCHAR(100) NULL,
  s_comment VARCHAR(2000) NULL,
  s_admin_comment VARCHAR(2000) NULL,
  s_file VARCHAR(80) NULL,
  b_open TINYINT(1) NOT NULL DEFAULT 1,
  dt_status_date DATETIME NULL,
  dt_update_date DATETIME NULL,
  dt_seen_reporter DATETIME NULL,
  dt_seen_reported DATETIME NULL,
  dt_seen_admin DATETIME NULL,
  dt_notified_reporter DATETIME NULL,
  dt_notified_reported DATETIME NULL,
  dt_notified_admin DATETIME NULL,
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
  s_party VARCHAR(10) NULL,
  s_comment VARCHAR(2000) NOT NULL,
  b_admin_seen TINYINT(1) NOT NULL DEFAULT 0,
  dt_date DATETIME NOT NULL,
  PRIMARY KEY (pk_i_id),
  INDEX fk_i_report_id (fk_i_report_id),
  INDEX fk_i_user_id (fk_i_user_id),
  INDEX idx_b_admin_seen (b_admin_seen)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'", DB_TABLE_PREFIX));

  if(osc_reports_tables_ready(true)) {
    osc_set_preference('reports_schema', 8412, 'osclass', 'INTEGER');
    osc_reset_preferences();
    return true;
  }

  return false;
}


// Report data of 8.4.1, once (upgrade or first use): reporter name and email, conversation party of comments, statuses that say whose turn it is,
// last visit and last reply email per party (pref reports_schema 8412)
function osc_ensure_reports_columns() {
  if((int)osc_get_preference('reports_schema') >= 8412) {
    return true;
  }

  $conn = DBConnectionClass::newInstance()->getOsclassDb();
  $comm = new DBCommandClass($conn);
  $add = array(
    't_report' => array('s_reporter_name' => 'VARCHAR(100) NULL AFTER s_source', 's_reporter_email' => 'VARCHAR(100) NULL AFTER s_reporter_name', 'dt_seen_reporter' => 'DATETIME NULL AFTER dt_update_date', 'dt_seen_reported' => 'DATETIME NULL AFTER dt_seen_reporter', 'dt_seen_admin' => 'DATETIME NULL AFTER dt_seen_reported', 'dt_notified_reporter' => 'DATETIME NULL AFTER dt_seen_admin', 'dt_notified_reported' => 'DATETIME NULL AFTER dt_notified_reporter', 'dt_notified_admin' => 'DATETIME NULL AFTER dt_notified_reported'),
    't_report_comment' => array('s_party' => 'VARCHAR(10) NULL AFTER fk_i_admin_id')
  );

  foreach($add as $table => $columns) {
    foreach($columns as $column => $definition) {
      $result = $comm->query(sprintf("SHOW COLUMNS FROM %s%s LIKE '%s'", DB_TABLE_PREFIX, $table, $column));
      if($result !== false && $result->numRows() == 0) {
        $comm->query(sprintf('ALTER TABLE %s%s ADD %s %s', DB_TABLE_PREFIX, $table, $column, $definition));
      }
    }
  }

  $result = $comm->query(sprintf("SHOW COLUMNS FROM %st_report LIKE 'dt_notified_admin'", DB_TABLE_PREFIX));
  if($result === false || $result->numRows() == 0) {
    return false;
  }

  $comm->query(sprintf("ALTER TABLE %st_report ALTER s_status SET DEFAULT 'waiting_admin'", DB_TABLE_PREFIX));
  $comm->query(sprintf("UPDATE %st_report SET s_status = 'waiting_admin' WHERE s_status = 'submitted'", DB_TABLE_PREFIX));
  $comm->query(sprintf("UPDATE %st_report SET s_status = 'waiting_customer' WHERE s_status = 'awaiting_feedback'", DB_TABLE_PREFIX));

  $enabled_statuses = trim((string)osc_get_preference('reports_enabled_statuses'));
  if($enabled_statuses != '') {
    osc_set_preference('reports_enabled_statuses', str_replace(array('awaiting_feedback', 'submitted'), array('waiting_customer', 'waiting_admin'), $enabled_statuses));
  }

  // Party of older comments: user comment by reporter or reported user, admin comment to reported user except contact messages
  $comm->query(sprintf("UPDATE %st_report_comment c INNER JOIN %st_report r ON r.pk_i_id = c.fk_i_report_id SET c.s_party = IF(c.fk_i_user_id > 0, IF(c.fk_i_user_id = r.fk_i_reporter_user_id, 'reporter', 'reported'), IF(r.fk_i_user_id > 0 AND r.s_type <> 'webcontact', 'reported', 'reporter')) WHERE c.s_party IS NULL", DB_TABLE_PREFIX, DB_TABLE_PREFIX));

  // Reporter name and email: account of registered reporter, From line of contact messages
  $comm->query(sprintf("UPDATE %st_report r INNER JOIN %st_user u ON u.pk_i_id = r.fk_i_reporter_user_id SET r.s_reporter_name = LEFT(u.s_name, 100), r.s_reporter_email = LEFT(u.s_email, 100) WHERE r.s_reporter_email IS NULL", DB_TABLE_PREFIX, DB_TABLE_PREFIX));
  $contact_reports = $comm->query(sprintf("SELECT pk_i_id, s_comment FROM %st_report WHERE s_type = 'webcontact' AND s_reporter_email IS NULL", DB_TABLE_PREFIX));
  if($contact_reports !== false) {
    foreach($contact_reports->result() as $row) {
      $head = implode("\n", array_slice(preg_split('/\R/', (string)$row['s_comment']), 0, 2));
      if(preg_match('/^[^:\n]+:\s*(.*?)\s*<([^<>\s]+@[^<>\s]+)>/m', $head, $m) && osc_validate_email($m[2])) {
        $comm->query(sprintf("UPDATE %st_report SET s_reporter_name = '%s', s_reporter_email = '%s' WHERE pk_i_id = %d", DB_TABLE_PREFIX, $comm->escapeStr(osc_substr($m[1], 0, 100)), $comm->escapeStr(osc_substr($m[2], 0, 100)), (int)$row['pk_i_id']));
      }
    }
  }

  osc_ensure_report_rewrite_rules();
  osc_set_preference('reports_schema', 8412, 'osclass', 'INTEGER');
  osc_reset_preferences();
  return true;
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


// Get url to view report conversation (optional access key opens it without login, see osc_report_access_url)
function osc_report_view_url($id, $key = '') {
  $key = (preg_match('/^[a-f0-9]{32}$/', (string)$key) ? (string)$key : '');
  if(osc_rewrite_enabled()) {
    $url = osc_base_url(false, true) . osc_get_preference('rewrite_report_view') . '/' . (int)$id . ($key != '' ? '/' . $key : '');
  } else {
    $url = osc_base_url(true) . '?page=report&action=view&id=' . (int)$id . ($key != '' ? '&key=' . $key : '');
  }

  return osc_apply_filter('osc_report_view_url', $url, $id, $key);
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


// Create a report, run the report hooks and send the notification emails
// $after_create(int $reportId) runs right after the insert, before the hooks (attachments)
function osc_report_create($data, $notify = true, $after_create = null) {
  if((int)@$data['fk_i_reporter_user_id'] > 0 && (empty($data['s_reporter_email']) || empty($data['s_reporter_name']))) {
    $reporter = User::newInstance()->findByPrimaryKey((int)$data['fk_i_reporter_user_id']);
    if($reporter) {
      $data['s_reporter_email'] = (!empty($data['s_reporter_email']) ? $data['s_reporter_email'] : $reporter['s_email']);
      $data['s_reporter_name'] = (!empty($data['s_reporter_name']) ? $data['s_reporter_name'] : $reporter['s_name']);
    }
  }
  foreach(array('s_reporter_email', 's_reporter_name') as $field) {
    if(isset($data[$field])) {
      $data[$field] = osc_substr(trim(strip_tags((string)$data[$field])), 0, 100);
    }
  }
  if(isset($data['s_reporter_email']) && !osc_validate_email($data['s_reporter_email'])) {
    $data['s_reporter_email'] = null;
  }

  $reportId = Report::newInstance()->createReport($data);
  if(!$reportId) {
    return false;
  }

  if(is_callable($after_create)) {
    call_user_func($after_create, $reportId);
  }

  $report = Report::newInstance()->findByPrimaryKey($reportId);
  osc_run_hook('after_report_add', $report);
  osc_run_hook('posted_report', $report);

  if($notify) {
    if(osc_reports_notify_admin()) {
      osc_run_hook('hook_email_report_admin', $report);
    }
    if(@$data['s_type'] != 'webcontact') {
      if(osc_reports_notify_reporter_created()) {
        osc_run_hook('hook_email_report_reporter_created', $report);
      }
      if(osc_reports_notify_owner_created()) {
        osc_run_hook('hook_email_report_owner_created', $report);
      }
    }
  }

  osc_run_hook('report_created', $report);

  return $reportId;
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

  $reason = trim((string)(isset($params['reason']) ? $params['reason'] : Params::getParam('reason')));
  if($reason == '' || !isset(osc_report_contact_reasons()[$reason])) {
    $reason = 'contact';
  }

  $data = array(
    'fk_i_reporter_user_id' => (osc_is_web_user_logged_in() ? (int)osc_logged_user_id() : 0),
    'fk_c_locale_code' => osc_current_user_locale(),
    's_type' => 'webcontact',
    's_reason' => $reason,
    's_source' => 'osclass',
    's_reporter_name' => $name,
    's_reporter_email' => $email,
    's_comment' => $comment
  );

  $flash_error = osc_apply_filter('pre_webcontact_report_error', '', $data);
  if($flash_error != '') {
    return false;
  }

  osc_run_hook('pre_report_add', $data);

  $reportId = osc_report_create($data, true, function($reportId) use ($params) {
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
  });
  if(!$reportId) {
    return false;
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
