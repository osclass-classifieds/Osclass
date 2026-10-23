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
 * Send one claimed queue row (status SENDING) and store the result in the row and its history.
 * Used by the cron worker, instant mails ($params = original params) and admin "Send now" ($admin_id).
 * A failed attempt is tried again after the retry delays (default 3, 10 and 60 minutes), then the row is FAILED.
 * Fires actions mail_queue_sent($row) and mail_queue_failed($row).
 *
 * @param array      $row
 * @param array|null $params
 * @param int        $admin_id
 *
 * @return bool
 */
function osc_mail_queue_send($row, $params = null, $admin_id = 0) {
  $mq = MailQueue::newInstance();
  $now = date('Y-m-d H:i:s');
  $instant = is_array($params);
  $attempt = (int)$row['i_attempts'];
  $note = '';

  // Update only while the row is still ours (not recovered and claimed by another run)
  $where = array('pk_i_id' => $row['pk_i_id'], 'e_status' => 'SENDING');
  if((string)$row['s_lock'] != '') {
    $where['s_lock'] = $row['s_lock'];
  }

  // Expired mail is cancelled instead of being sent late
  if(!empty($row['dt_expire']) && $row['dt_expire'] < $now) {
    $set = array('e_status' => 'CANCELLED', 's_lock' => null, 'dt_lock' => null, 'dt_done' => $now, 's_error' => 'Expired before it could be sent');
    if($mq->update($set, $where)) {
      $mq->addLog($row['pk_i_id'], 'EXPIRED', $attempt, '', null, $admin_id);
    }

    return false;
  }

  if(!$instant) {
    $params = json_decode((string)$row['s_params'], true);
    $params = (is_array($params) ? $params : array());

    // Stored attachments are read only from the queue folder, missing files are dropped
    if(!empty($params['attachment'])) {
      $list = ((!is_array($params['attachment']) || isset($params['attachment']['path'])) ? array($params['attachment']) : $params['attachment']);
      $base = realpath(osc_uploads_path() . 'mail_queue');
      $base = ($base !== false ? str_replace('\\', '/', $base) . '/' : '');
      $params['attachment'] = array();

      foreach($list as $attachment) {
        $path = (string)(is_array($attachment) ? (isset($attachment['path']) ? $attachment['path'] : '') : $attachment);
        $real = ($path != '' ? realpath($path) : false);

        if($base != '' && $real !== false && is_file($real) && strpos(str_replace('\\', '/', $real), $base) === 0) {
          $params['attachment'][] = array('path' => $real, 'name' => (is_array($attachment) && isset($attachment['name']) ? (string)$attachment['name'] : basename($real)));
        } else {
          $note .= 'Attachment not found: ' . basename($path) . '. ';
        }
      }

      if(empty($params['attachment'])) {
        unset($params['attachment']);
      }
    }
  }

  $to = (isset($params['to']) ? $params['to'] : '');
  $has_recipient = (count(array_filter(array_map('trim', array_map('strval', (is_array($to) ? array_keys($to) : array($to)))), 'strlen')) > 0);

  // Keep the transport error text (trigger_error in osc_sendMailNow), normal error handling continues
  $error = '';
  $permanent = !$has_recipient;
  $start = microtime(true);
  set_error_handler(function($errno, $errstr) use (&$error) {
    if($errno & (E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE)) {
      $error = (string)$errstr;
    }

    return false;
  });

  try {
    $sent = ($has_recipient ? osc_sendMailNow($params, (string)$row['s_type']) : false);
  } catch(Throwable $e) {
    $sent = false;
    $error = $e->getMessage();
  }

  restore_error_handler();
  $duration = (int)round((microtime(true) - $start) * 1000);
  $error = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($error), ENT_QUOTES, 'UTF-8')));
  $now = date('Y-m-d H:i:s');

  if($sent) {
    $set = array('e_status' => 'SENT', 's_lock' => null, 'dt_lock' => null, 'dt_done' => $now, 's_error' => ($note != '' ? osc_substr(trim($note), 0, 500) : null));

    // One-time codes and passwords: keep only headers in the history (2 = text removed)
    if($row['b_private'] == 1) {
      $set['s_params'] = osc_mail_queue_strip_body($row['s_params']);
      $set['b_private'] = 2;
    }

    if($mq->update($set, $where)) {
      $mq->addLog($row['pk_i_id'], 'SENT', $attempt, trim($note), $duration, $admin_id);
      osc_run_hook('mail_queue_sent', array_merge($row, $set));
    }

    return true;
  }

  // Stopped on purpose (plugin filter, no SMTP error): cancel, retrying gives the same answer
  if($error == '' && $has_recipient) {
    $set = array('e_status' => 'CANCELLED', 's_lock' => null, 'dt_lock' => null, 'dt_done' => $now, 's_error' => 'Stopped by a plugin');
    if($mq->update($set, $where)) {
      $mq->addLog($row['pk_i_id'], 'CANCELLED', $attempt, $set['s_error'], $duration, $admin_id);
    }

    return false;
  }

  // Wrong or missing recipient never works on the next attempt (a wrong sender setting can be fixed, so it is retried)
  if(!$has_recipient) {
    $error = 'No recipient';
  } else if(preg_match('/invalid address:\s*\((to|cc|bcc)\)|at least one recipient/i', $error)) {
    $permanent = true;
  }

  $error = osc_substr(trim($note . $error), 0, 500);
  $delays = osc_mail_queue_retry_delays();

  if($permanent || $attempt > count($delays)) {
    $set = array('e_status' => 'FAILED', 's_lock' => null, 'dt_lock' => null, 'dt_done' => $now, 's_error' => $error);
    if($mq->update($set, $where)) {
      $mq->addLog($row['pk_i_id'], 'FAILED', $attempt, $error, $duration, $admin_id);
      osc_run_hook('mail_queue_failed', array_merge($row, $set));
    }

    return false;
  }

  // Next attempt after 3, 10, then 60 minutes (Settings > Mail queue)
  $set = array('e_status' => 'PENDING', 's_lock' => null, 'dt_lock' => null, 'dt_send_after' => date('Y-m-d H:i:s', time() + $delays[max(0, $attempt - 1)] * 60), 's_error' => $error);

  // Instant mail goes back to the queue: copy its attachments while they still exist
  if($instant && $row['b_attachment'] == 1) {
    $set['s_params'] = json_encode(osc_mail_queue_attachments($row['pk_i_id'], $params), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR);
  }

  if($mq->update($set, $where)) {
    $mq->addLog($row['pk_i_id'], 'ERROR', $attempt, $error, $duration, $admin_id);
  }

  return false;
}


/**
 * Stored params without the email text (private mails after sending)
 *
 * @param string $json
 *
 * @return string
 */
function osc_mail_queue_strip_body($json) {
  $params = json_decode((string)$json, true);
  $params = (is_array($params) ? $params : array());
  $params['body'] = '';
  $params['alt_body'] = '';

  return json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR);
}


/**
 * Translated mail queue statuses
 *
 * @return array
 */
function osc_mail_queue_status_labels() {
  return array('PENDING' => __('Pending'), 'SENDING' => __('Sending'), 'SENT' => __('Sent'), 'FAILED' => __('Failed'), 'CANCELLED' => __('Cancelled'));
}


/**
 * True when the row can be sent (again): not being sent and its text was not removed after sending (b_private = 2)
 *
 * @param array $row
 *
 * @return bool
 */
function osc_mail_queue_can_send($row) {
  return ($row['e_status'] != 'SENDING' && (int)$row['b_private'] != 2);
}


/**
 * Copy mail attachments into the queue folder (uploads/mail_queue/{id}_{random}/), web access denied
 *
 * @param int   $id
 * @param array $params
 *
 * @return array  params with attachment paths of the copies
 */
function osc_mail_queue_attachments($id, $params) {
  if(empty($params['attachment'])) {
    return $params;
  }

  $list = ((!is_array($params['attachment']) || isset($params['attachment']['path'])) ? array($params['attachment']) : $params['attachment']);
  $base = osc_uploads_path() . 'mail_queue/';

  if(!is_dir($base)) {
    @mkdir($base, 0755, true);
    @file_put_contents($base . 'index.php', '<?php' . PHP_EOL);
    @file_put_contents($base . '.htaccess', '<IfModule mod_authz_core.c>' . PHP_EOL . '  Require all denied' . PHP_EOL . '</IfModule>' . PHP_EOL . '<IfModule !mod_authz_core.c>' . PHP_EOL . '  Deny from all' . PHP_EOL . '</IfModule>' . PHP_EOL);
  }

  $dir = $base . (int)$id . '_' . bin2hex(random_bytes(8)) . '/';
  @mkdir($dir, 0755);
  $params['attachment'] = array();

  foreach(array_values($list) as $i => $attachment) {
    $path = (string)(is_array($attachment) ? (isset($attachment['path']) ? $attachment['path'] : '') : $attachment);
    $name = (is_array($attachment) && isset($attachment['name']) ? (string)$attachment['name'] : basename($path));
    $copy = $dir . $i . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($name));

    if($path != '' && is_file($path) && is_dir($dir) && @copy($path, $copy)) {
      $path = $copy;
    }

    $params['attachment'][] = array('path' => $path, 'name' => $name);
  }

  return $params;
}


/**
 * Mail queue worker (minutely cron): one run at a time, recover crashed rows, claim due rows
 * (instant and normal first, then bulk round-robin by group) and send them within the time budget.
 *
 * @return array  sent, not_sent, released, busy
 */
function osc_mail_queue_run() {
  $result = array('sent' => 0, 'not_sent' => 0, 'released' => 0, 'busy' => false);
  $mq = MailQueue::newInstance();

  // Queue switched off later still sends what is left in it
  if(osc_mail_queue_paused() || (!osc_mail_queue_enabled() && !$mq->hasOpen())) {
    return $result;
  }

  // Another run is still sending
  if(!$mq->lock()) {
    $result['busy'] = true;
    return $result;
  }

  $budget = osc_mail_queue_time_budget();
  $end = microtime(true) + $budget;
  $max_time = (int)ini_get('max_execution_time');
  if($max_time > 0) {
    $end = min($end, (isset($_SERVER['REQUEST_TIME_FLOAT']) ? (float)$_SERVER['REQUEST_TIME_FLOAT'] : microtime(true)) + $max_time - 5);
  }

  // Rows left in SENDING (crash, timeout): back to the queue, failed after the last attempt
  foreach($mq->findStuck(date('Y-m-d H:i:s', time() - max(600, $budget + 120)), 100) as $row) {
    $where = array('pk_i_id' => $row['pk_i_id'], 'e_status' => 'SENDING');

    if((int)$row['i_attempts'] >= osc_mail_queue_max_attempts()) {
      $set = array('e_status' => 'FAILED', 's_lock' => null, 'dt_lock' => null, 'dt_done' => date('Y-m-d H:i:s'), 's_error' => 'Sending was interrupted');
      if($mq->update($set, $where)) {
        $mq->addLog($row['pk_i_id'], 'FAILED', $row['i_attempts'], $set['s_error']);
        osc_run_hook('mail_queue_failed', array_merge($row, $set));
      }
    } else if($mq->update(array('e_status' => 'PENDING', 's_lock' => null, 'dt_lock' => null, 's_error' => 'Sending was interrupted'), $where)) {
      $mq->addLog($row['pk_i_id'], 'INTERRUPTED', $row['i_attempts']);
    }
  }

  $per_run = osc_mail_queue_per_run();
  $ids = array();

  foreach(array(1, 2) as $priority) {
    if(count($ids) < $per_run) {
      $ids = array_merge($ids, array_column($mq->findDue($priority, $per_run - count($ids)), 'pk_i_id'));
    }
  }

  // Bulk: oldest first, taken in turns per group, at most group_per_run per named group
  $bulk_limit = min(osc_mail_queue_bulk_per_run(), $per_run - count($ids));
  if($bulk_limit > 0) {
    $window = $bulk_limit * 10;
    $candidates = $mq->findDue(3, $window);

    // One big sender fills the window: look for rows of other groups too
    if(count($candidates) >= $window) {
      $seen = array_values(array_unique(array_filter(array_map('strval', array_column($candidates, 's_group')), 'strlen')));
      $candidates = array_merge($candidates, $mq->findDue(3, $window, $seen));
    }

    $by_group = array();
    foreach($candidates as $c) {
      $by_group[(string)$c['s_group']][] = $c['pk_i_id'];
    }

    $taken = array();
    $group_limit = osc_mail_queue_group_per_run();

    while($bulk_limit > 0) {
      $added = false;

      foreach(array_keys($by_group) as $group) {
        if($bulk_limit <= 0) {
          break;
        }

        if(empty($by_group[$group]) || ((string)$group !== '' && isset($taken[$group]) && $taken[$group] >= $group_limit)) {
          continue;
        }

        $ids[] = array_shift($by_group[$group]);
        $taken[$group] = (isset($taken[$group]) ? $taken[$group] + 1 : 1);
        $bulk_limit--;
        $added = true;
      }

      if(!$added) {
        break;
      }
    }
  }

  if(empty($ids)) {
    $mq->unlock();
    return $result;
  }

  $token = bin2hex(random_bytes(16));
  $rows = $mq->claim($ids, $token);
  $left = array_column($rows, 'pk_i_id');

  // A fatal error in a send: the rows not started yet go back to the queue at once
  register_shutdown_function(function() use (&$left, $token) {
    if(!empty($left)) {
      MailQueue::newInstance()->release($left, $token);
    }
  });

  $pause = osc_mail_queue_pause();

  foreach($rows as $row) {
    // Wait between emails, not before the first one
    if($pause > 0 && $result['sent'] + $result['not_sent'] > 0) {
      usleep($pause * 1000);
    }

    if(microtime(true) >= $end) {
      $result['released'] = $mq->release($left, $token);
      $left = array();
      break;
    }

    array_shift($left);

    if(osc_mail_queue_send($row)) {
      $result['sent']++;
    } else {
      $result['not_sent']++;
    }
  }

  $mq->unlock();
  return $result;
}


/**
 * Change the status of queue rows from oc-admin. Rows being sent are never changed.
 * PENDING = send again at the next cron run (attempts start again), SENT / FAILED / CANCELLED = finished.
 * Fires action mail_queue_status_changed($row, $old_status, $admin_id).
 *
 * @param array  $ids
 * @param string $status
 * @param int    $admin_id
 *
 * @return int  changed rows
 */
function osc_mail_queue_set_status($ids, $status, $admin_id = 0) {
  if(!in_array($status, array('PENDING', 'SENT', 'FAILED', 'CANCELLED'))) {
    return 0;
  }

  $mq = MailQueue::newInstance();
  $now = date('Y-m-d H:i:s');
  $count = 0;

  foreach($mq->findByIds($ids) as $row) {
    if($row['e_status'] == 'SENDING' || $row['e_status'] == $status || ($status == 'PENDING' && !osc_mail_queue_can_send($row))) {
      continue;
    }

    if($status == 'PENDING') {
      $set = array('e_status' => 'PENDING', 'i_attempts' => 0, 'dt_send_after' => $now, 'dt_done' => null, 's_error' => null);

      // Sent again on purpose: an old expiry date would cancel it
      if($row['dt_expire'] != '' && $row['dt_expire'] < $now) {
        $set['dt_expire'] = null;
      }
    } else {
      $set = array('e_status' => $status, 'dt_done' => $now);

      if($status == 'SENT' && $row['b_private'] == 1) {
        $set['s_params'] = osc_mail_queue_strip_body($row['s_params']);
        $set['b_private'] = 2;
      }
    }

    if($mq->update($set, array('pk_i_id' => $row['pk_i_id'], 'e_status' => $row['e_status']))) {
      $mq->addLog($row['pk_i_id'], $status, $row['i_attempts'], '', null, $admin_id);
      osc_run_hook('mail_queue_status_changed', array_merge($row, $set), $row['e_status'], $admin_id);
      $count++;
    }
  }

  return $count;
}


/**
 * Send queue rows now from oc-admin (also sent, failed and cancelled ones). What does not fit
 * into the time limit stays pending and goes with the next cron run.
 *
 * @param array $ids
 * @param int   $admin_id
 *
 * @return array  sent, not_sent, queued, skipped
 */
function osc_mail_queue_send_now($ids, $admin_id = 0) {
  $result = array('sent' => 0, 'not_sent' => 0, 'queued' => 0, 'skipped' => 0);
  $mq = MailQueue::newInstance();
  $pending = array();

  foreach($mq->findByIds($ids) as $row) {
    if(!osc_mail_queue_can_send($row)) {
      $result['skipped']++;
    } else if($row['e_status'] == 'PENDING') {
      $set = array('i_attempts' => 0);
      if($row['dt_expire'] != '' && $row['dt_expire'] < date('Y-m-d H:i:s')) {
        $set['dt_expire'] = null;
      }

      $mq->update($set, array('pk_i_id' => $row['pk_i_id'], 'e_status' => 'PENDING'));
      $pending[] = $row['pk_i_id'];
    } else if(osc_mail_queue_set_status(array($row['pk_i_id']), 'PENDING', $admin_id) > 0) {
      $pending[] = $row['pk_i_id'];
    } else {
      $result['skipped']++;
    }
  }

  $end = microtime(true) + min(30, osc_mail_queue_time_budget());
  $max_time = (int)ini_get('max_execution_time');
  if($max_time > 0) {
    $end = min($end, (isset($_SERVER['REQUEST_TIME_FLOAT']) ? (float)$_SERVER['REQUEST_TIME_FLOAT'] : microtime(true)) + $max_time - 5);
  }

  $token = bin2hex(random_bytes(16));
  $rows = $mq->claim($pending, $token);
  $left = array_column($rows, 'pk_i_id');

  $pause = osc_mail_queue_pause();

  foreach($rows as $row) {
    if($pause > 0 && $result['sent'] + $result['not_sent'] > 0) {
      usleep($pause * 1000);
    }

    if(microtime(true) >= $end) {
      $result['queued'] = $mq->release($left, $token);
      break;
    }

    array_shift($left);

    if(osc_mail_queue_send($row, null, $admin_id)) {
      $result['sent']++;
    } else {
      $result['not_sent']++;
    }
  }

  // Claimed by the cron meanwhile: it sends them
  $result['queued'] += count($pending) - count($rows);
  return $result;
}


/**
 * Delete sent, failed and cancelled mails older than the history setting (daily cron), in chunks
 *
 * @return int  deleted rows
 */
function osc_mail_queue_purge() {
  $mq = MailQueue::newInstance();
  $date = date('Y-m-d H:i:s', time() - osc_mail_queue_keep_days() * 86400);
  $end = microtime(true) + 30;
  $deleted = 0;

  foreach(array('SENT', 'FAILED', 'CANCELLED') as $status) {
    do {
      $ids = array_column($mq->findDone($status, $date, 1000), 'pk_i_id');
      $deleted += osc_mail_queue_delete($ids);
    } while(count($ids) == 1000 && microtime(true) < $end);
  }

  return $deleted;
}


/**
 * Delete queue rows (never rows being sent), their history and stored attachments
 *
 * @param array $ids
 *
 * @return int  deleted rows
 */
function osc_mail_queue_delete($ids) {
  $mq = MailQueue::newInstance();
  $attachment_ids = $mq->findAttachmentIds($ids);
  $deleted = $mq->deleteIds($ids);

  // Folders only of rows that are really gone (a row claimed by the cron meanwhile keeps its files)
  if(!empty($attachment_ids)) {
    $kept = array_column($mq->findByIds($attachment_ids), 'pk_i_id');

    foreach(array_diff($attachment_ids, $kept) as $id) {
      foreach((glob(osc_uploads_path() . 'mail_queue/' . (int)$id . '_*', GLOB_ONLYDIR) ?: array()) as $dir) {
        osc_deleteDir($dir);
      }
    }
  }

  return $deleted;
}


/**
 * Mail queue counters for admin screens and dashboards (cached per request)
 *
 * @return array  pending, sending, sent, failed, cancelled, total, oldest_due, late, sent_24h, failed_24h, last_run, next_run, enabled, paused
 */
function osc_mail_queue_stats() {
  static $stats = null;

  if($stats === null) {
    $data = MailQueue::newInstance()->stats(date('Y-m-d H:i:s', time() - 86400));
    $cron = Cron::newInstance()->getCronByType('MINUTELY');
    $last_run = (isset($cron['d_last_exec']) && strtotime((string)$cron['d_last_exec']) > 0 ? $cron['d_last_exec'] : '');
    $next_run = (isset($cron['d_next_exec']) && strtotime((string)$cron['d_next_exec']) > 0 ? $cron['d_next_exec'] : '');

    $stats = array(
      'pending' => $data['PENDING'],
      'sending' => $data['SENDING'],
      'sent' => $data['SENT'],
      'failed' => $data['FAILED'],
      'cancelled' => $data['CANCELLED'],
      'total' => $data['PENDING'] + $data['SENDING'] + $data['SENT'] + $data['FAILED'] + $data['CANCELLED'],
      'oldest_due' => $data['oldest_due'],
      'late' => ($data['oldest_due'] != '' && strtotime($data['oldest_due']) < time() - 1800),
      'sent_24h' => $data['sent_since'],
      'failed_24h' => $data['failed_since'],
      'last_run' => $last_run,
      'next_run' => $next_run,
      'enabled' => osc_mail_queue_enabled(),
      'paused' => osc_mail_queue_paused()
    );
  }

  return $stats;
}

/* file end: ./oc-includes/osclass/mail_queue.php */
