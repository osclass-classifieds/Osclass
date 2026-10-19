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
 * Send one claimed queue row (status SENDING) and store the result.
 * Used by the cron worker, instant mails ($params = original params) and admin "Send now".
 * Fires actions mail_queue_sent($row) and mail_queue_failed($row).
 *
 * @param array      $row
 * @param array|null $params
 *
 * @return bool
 */
function osc_mail_queue_send($row, $params = null) {
  $mq = MailQueue::newInstance();
  $now = date('Y-m-d H:i:s');
  $instant = is_array($params);
  $note = '';

  // Expired mail is cancelled instead of being sent late
  if(!empty($row['dt_expire']) && $row['dt_expire'] < $now) {
    $set = array('e_status' => 'CANCELLED', 's_lock' => null, 'dt_lock' => null, 'dt_done' => $now, 's_error' => 'Expired before it could be sent');
    $mq->update($set, array('pk_i_id' => $row['pk_i_id']));
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

  // Keep the transport error text (trigger_error in osc_sendMailNow), normal error handling continues
  $error = '';
  set_error_handler(function($errno, $errstr) use (&$error) {
    if($errno & (E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE)) {
      $error = (string)$errstr;
    }

    return false;
  });

  try {
    $sent = osc_sendMailNow($params, (string)$row['s_type']);
  } catch(Throwable $e) {
    $sent = false;
    $error = $e->getMessage();
  }

  restore_error_handler();
  $now = date('Y-m-d H:i:s');

  if($sent) {
    $set = array('e_status' => 'SENT', 's_lock' => null, 'dt_lock' => null, 'dt_done' => $now, 's_error' => ($note != '' ? osc_substr(trim($note), 0, 500) : null));

    // One-time codes and passwords: keep only headers in the history
    if($row['b_private'] == 1) {
      $stored = json_decode((string)$row['s_params'], true);
      $stored = (is_array($stored) ? $stored : array());
      $stored['body'] = '';
      $stored['alt_body'] = '';
      $set['s_params'] = json_encode($stored, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    $mq->update($set, array('pk_i_id' => $row['pk_i_id']));
    osc_run_hook('mail_queue_sent', array_merge($row, $set));
    return true;
  }

  $error = osc_substr(trim($note . ($error != '' ? $error : 'Not sent: stopped by a plugin or no recipient')), 0, 500);

  if((int)$row['i_attempts'] >= osc_mail_queue_max_attempts()) {
    $set = array('e_status' => 'FAILED', 's_lock' => null, 'dt_lock' => null, 'dt_done' => $now, 's_error' => $error);
    $mq->update($set, array('pk_i_id' => $row['pk_i_id']));
    osc_run_hook('mail_queue_failed', array_merge($row, $set));
    return false;
  }

  // Next attempt in 5 minutes, 30 minutes, then every 2 hours
  $delays = array(1 => 5, 2 => 30);
  $delay = (isset($delays[(int)$row['i_attempts']]) ? $delays[(int)$row['i_attempts']] : 120);
  $set = array('e_status' => 'PENDING', 's_lock' => null, 'dt_lock' => null, 'dt_send_after' => date('Y-m-d H:i:s', time() + $delay * 60), 's_error' => $error);

  // Instant mail goes back to the queue: copy its attachments while they still exist
  if($instant && $row['b_attachment'] == 1) {
    $set['s_params'] = json_encode(osc_mail_queue_attachments($row['pk_i_id'], $params), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR);
  }

  $mq->update($set, array('pk_i_id' => $row['pk_i_id']));
  return false;
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
 * Mail queue worker (minutely cron): recover crashed rows, claim due rows (instant and normal first,
 * then bulk round-robin by group) and send them within the time budget.
 *
 * @return array  sent, not_sent, released
 */
function osc_mail_queue_run() {
  $result = array('sent' => 0, 'not_sent' => 0, 'released' => 0);
  $mq = MailQueue::newInstance();

  // Queue switched off later still sends what is left in it
  if(osc_mail_queue_paused() || (!osc_mail_queue_enabled() && !$mq->hasOpen())) {
    return $result;
  }

  $end = microtime(true) + osc_mail_queue_time_budget();
  $max_time = (int)ini_get('max_execution_time');
  if($max_time > 0) {
    $end = min($end, (isset($_SERVER['REQUEST_TIME_FLOAT']) ? (float)$_SERVER['REQUEST_TIME_FLOAT'] : microtime(true)) + $max_time - 5);
  }

  // Rows left in SENDING for 10 minutes: back to the queue, failed after the last attempt
  foreach($mq->findStuck(date('Y-m-d H:i:s', time() - 600), 100) as $row) {
    if((int)$row['i_attempts'] >= osc_mail_queue_max_attempts()) {
      $set = array('e_status' => 'FAILED', 's_lock' => null, 'dt_lock' => null, 'dt_done' => date('Y-m-d H:i:s'), 's_error' => 'Sending was interrupted');
      $mq->update($set, array('pk_i_id' => $row['pk_i_id']));
      osc_run_hook('mail_queue_failed', array_merge($row, $set));
    } else {
      $mq->update(array('e_status' => 'PENDING', 's_lock' => null, 'dt_lock' => null), array('pk_i_id' => $row['pk_i_id']));
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
    return $result;
  }

  $token = bin2hex(random_bytes(16));
  $rows = $mq->claim($ids, $token);

  foreach($rows as $i => $row) {
    if(microtime(true) >= $end) {
      $result['released'] = $mq->release(array_column(array_slice($rows, $i), 'pk_i_id'), $token);
      break;
    }

    if(osc_mail_queue_send($row)) {
      $result['sent']++;
    } else {
      $result['not_sent']++;
    }
  }

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
 * Delete queue rows (never rows being sent) and their stored attachments
 *
 * @param array $ids
 *
 * @return int  deleted rows
 */
function osc_mail_queue_delete($ids) {
  $mq = MailQueue::newInstance();

  foreach($mq->findAttachmentIds($ids) as $id) {
    $dirs = glob(osc_uploads_path() . 'mail_queue/' . (int)$id . '_*', GLOB_ONLYDIR);

    foreach(($dirs ?: array()) as $dir) {
      osc_deleteDir($dir);
    }
  }

  return $mq->deleteIds($ids);
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
      'last_run' => (isset($cron['d_last_exec']) ? $cron['d_last_exec'] : ''),
      'next_run' => (isset($cron['d_next_exec']) ? $cron['d_next_exec'] : ''),
      'enabled' => osc_mail_queue_enabled(),
      'paused' => osc_mail_queue_paused()
    );
  }

  return $stats;
}

/* file end: ./oc-includes/osclass/mail_queue.php */
