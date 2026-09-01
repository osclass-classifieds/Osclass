<?php
if(!defined('ABS_PATH')) exit('ABS_PATH is not loaded. Direct access is not allowed.');

/*
  TESTING CRON:
  .../index.php?page=cron&force=1&type=daily&print=1
  .../index.php?page=cron&force=1&type=hourly&print=1

*/


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_WARNING | E_ERROR | E_PARSE | E_NOTICE);
// error_reporting(E_ALL);


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

$messages = array();
$shift_seconds = 60;
$shift_seconds_minutely = 20;
$d_now = date('Y-m-d H:i:s');
$i_now = strtotime($d_now);
$i_now_truncated = strtotime(date('Y-m-d H:i:00'));
$start_time = microtime(true);


// In order to manually execute cron no matter it's last run, use /index.php?page=cron&type=daily&force=1&print=1
$force = (Params::getParam('force') == 1 ? osc_is_admin_user_logged_in() : false);
$print = (Params::getParam('print') == 1 ? osc_is_admin_user_logged_in() : false);
$type = strtolower(osc_esc_html(Params::getParam('cron-type') <> '' ? Params::getParam('cron-type') : Params::getParam('type')));      // minutely, hourly, daily, weekly, monthly, yearly

$all_hooks = Plugins::getActive();


// Command line interface
if(!defined('CLI')) {
  define('CLI', PHP_SAPI === 'cli');
}


$messages[] = sprintf(__('Starting cron at %s'), $d_now);
$messages[] = str_repeat('-', 100);
$messages[] = ' > ' . sprintf(__('Cron params: %s'), json_encode(Params::getParamsAsArray()));


// Intitiate theme to get cron functions defined by theme - os812
WebThemes::newInstance();


// Minutely crons - execute also instant ones with minutely
$cron = Cron::newInstance()->getCronByType('MINUTELY');

if(is_array($cron)) {
  $i_next = strtotime($cron['d_next_exec']);

  if(
    (CLI && $type === 'minutely') 
    || ($force && $type === 'minutely') 
    || (!CLI && ($i_now - $i_next + $shift_seconds_minutely) >= 0)
  ) {
    // update the next execution time in t_cron
    $d_next = date('Y-m-d H:i:s', $i_now_truncated + (5 * 60));  // once per 5 minutes
    
    Cron::newInstance()->update(array('d_last_exec' => $d_now, 'd_next_exec' => $d_next), array('e_type' => 'MINUTELY'));
    
    osc_runAlert('INSTANT', $cron['d_last_exec']);
    osc_run_hook('cron_minutely');
    
    $messages[] = ' > ' . sprintf(__('Cron type "%s" finished at %s'), 'MINUTELY', date('Y-m-d H:i:s'));
  }
}


// Hourly crons
$cron = Cron::newInstance()->getCronByType('HOURLY');

if(is_array($cron)) {
  $i_next = strtotime($cron['d_next_exec']);

  if(
    (CLI && $type === 'hourly') 
    || ($force && ($type === 'hourly' || $type === 'all')) 
    || (!CLI && ($i_now - $i_next + $shift_seconds_minutely) >= 0)
  ) {
    // update the next execution time in t_cron
    $d_next = date('Y-m-d H:i:s', $i_now_truncated + 3600);
    Cron::newInstance()->update(array('d_last_exec' => $d_now, 'd_next_exec' => $d_next), array('e_type' => 'HOURLY'));
    
    osc_runAlert('HOURLY', $cron['d_last_exec']);
    
    // Run cron AFTER updating the next execution time to avoid double run of cron
    $purge = osc_purge_latest_searches();
    
    if($purge === 'hour') {
      LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', time() - 3600));
    }
    
    osc_update_location_stats(true, 'auto');

    // WARN EXPIRATION EACH HOUR (COMMENT TO DISABLE)
    // NOTE: IF THIS IS ENABLE, SAME CODE SHOULD BE DISABLE ON CRON DAILY
    if(is_numeric(osc_warn_expiration()) && osc_warn_expiration() >= 0) {
      $items = Item::newInstance()->findItemsWarnExpiration('HOURLY', osc_warn_expiration(), 1);
      
      if(is_array($items) && count($items) > 0) {
        foreach($items as $item) {
          osc_run_hook('hook_email_warn_expiration', $item);
        }
      }
    }

    osc_clean_temp_images();

    osc_run_hook('cron_hourly');

    if($print === true) {
      if(isset($all_hooks['cron_hourly']) && is_array($all_hooks['cron_hourly']) && count($all_hooks['cron_hourly']) > 0) {
        $messages[] = ' > ' . sprintf(__('Starting to run functions of "%s"'), 'cron_hourly');

        foreach($all_hooks['cron_hourly'] as $priority => $cron_functions) {
          if(is_array($cron_functions) && count($cron_functions) > 0) {
            foreach($cron_functions as $cfunc) {
              if(is_string($cfunc)) {
                $messages[] = ' >>> ' . sprintf(__('Executed cron function "%s" with priority %s'), $cfunc, $priority);
              }
            }
          }
        }
      }
    }
    
    $messages[] = ' > ' . sprintf(__('Cron type "%s" finished at %s'), 'HOURLY', date('Y-m-d H:i:s'));
  }
}


// Daily crons
$cron = Cron::newInstance()->getCronByType('DAILY');

if(is_array($cron)) {
  $i_next = strtotime($cron['d_next_exec']);

  if(
    (CLI && $type === 'daily') 
    || ($force && ($type === 'daily' || $type === 'all')) 
    || (!CLI && ($i_now - $i_next + $shift_seconds_minutely) >= 0)
  ) {
    // update the next execution time in t_cron
    $d_next = date('Y-m-d H:i:s', $i_now_truncated + (24 * 3600));
    Cron::newInstance()->update(array('d_last_exec' => $d_now, 'd_next_exec' => $d_next), array('e_type' => 'DAILY'));

    // upgrade osclass if there are new updates
    osc_do_auto_upgrade();

    osc_runAlert('DAILY', $cron['d_last_exec']);

    // Run cron AFTER updating the next execution time to avoid double run of cron
    $purge = osc_purge_latest_searches();

    if($purge === 'hour') {
      LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', strtotime('- 1 hour')));
      
    } else if($purge === 'day') {
      LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', strtotime('- 1 day')));

    } else if ($purge == 'week') {
      LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', strtotime('- 1 week')));
      
    } else if ($purge == 'month') {
      LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', strtotime('- 1 month')));

    } else if ($purge == 'year') {
      LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', strtotime('- 1 year')));

    // } else if(!in_array($purge, array('forever', 'day', 'week', 'month', 'year'))) {
    } else {
      LatestSearches::newInstance()->purgeNumber((int)$purge);
    }
    
    osc_update_cat_stats();

    // WARN EXPIRATION EACH DAY (UNCOMMENT TO ENABLE)
    // NOTE: IF THIS IS ENABLE, SAME CODE SHOULD BE DISABLE ON CRON HOURLY
    /*if(is_numeric(osc_warn_expiration()) && osc_warn_expiration()>=0) {
      $items = Item::newInstance()->findItemsWarnExpiration('DAILY', osc_warn_expiration(), 24);
      
      if(is_array($items) && count($items) > 0) {
        foreach($items as $item) {
          osc_run_hook('hook_email_warn_expiration', $item);
        }
      }
    }*/

    osc_clean_temp_images();

    osc_run_hook('cron_daily');

    if($print === true) {
      if(isset($all_hooks['cron_daily']) && is_array($all_hooks['cron_daily']) && count($all_hooks['cron_daily']) > 0) {
        $messages[] = ' > ' . sprintf(__('Starting to run functions of "%s"'), 'cron_daily');

        foreach($all_hooks['cron_daily'] as $priority => $cron_functions) {
          if(is_array($cron_functions) && count($cron_functions) > 0) {
            foreach($cron_functions as $cfunc) {
              if(is_string($cfunc)) {
                $messages[] = ' >>> ' . sprintf(__('Executed cron function "%s" with priority %s'), $cfunc, $priority);
              }
            }
          }
        }
      }
    }
    
    $messages[] = ' > ' . sprintf(__('Cron type "%s" finished at %s'), 'DAILY', date('Y-m-d H:i:s'));
  }
}


// Weekly crons
$cron = Cron::newInstance()->getCronByType('WEEKLY');
if(is_array($cron)) {
  $i_next = strtotime($cron['d_next_exec']);

  if(
    (CLI && $type === 'weekly') 
    || ($force && ($type === 'weekly' || $type === 'all')) 
    || (!CLI && ($i_now - $i_next + $shift_seconds_minutely) >= 0)
  ) {
    // update the next execution time in t_cron
    $d_next = date('Y-m-d H:i:s', $i_now_truncated + (7 * 24 * 3600));
    Cron::newInstance()->update(array('d_last_exec' => $d_now, 'd_next_exec' => $d_next), array('e_type' => 'WEEKLY'));
    
    osc_runAlert('WEEKLY', $cron['d_last_exec']);
    
    // Run cron AFTER updating the next execution time to avoid double run of cron
    $purge = osc_purge_latest_searches();

    if($purge === 'hour') {
      LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', strtotime('- 1 hour')));
      
    } else if($purge === 'day') {
      LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', strtotime('- 1 day')));

    } else if ($purge == 'week') {
      LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', strtotime('- 1 week')));
      
    } else if ($purge == 'month') {
      LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', strtotime('- 1 month')));

    } else if ($purge == 'year') {
      LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', strtotime('- 1 year')));

    // } else if(!in_array($purge, array('forever', 'day', 'week', 'month', 'year'))) {
    } else {
      LatestSearches::newInstance()->purgeNumber((int)$purge);
    }

    osc_run_hook('cron_weekly');

    if($print === true) {
      if(isset($all_hooks['cron_weekly']) && is_array($all_hooks['cron_weekly']) && count($all_hooks['cron_weekly']) > 0) {
        $messages[] = ' > ' . sprintf(__('Starting to run functions of "%s"'), 'cron_weekly');

        foreach($all_hooks['cron_weekly'] as $priority => $cron_functions) {
          if(is_array($cron_functions) && count($cron_functions) > 0) {
            foreach($cron_functions as $cfunc) {
              if(is_string($cfunc)) {
                $messages[] = ' >>> ' . sprintf(__('Executed cron function "%s" with priority %s'), $cfunc, $priority);
              }
            }
          }
        }
      }
    }
    
    $messages[] = ' > ' . sprintf(__('Cron type "%s" finished at %s'), 'WEEKLY', date('Y-m-d H:i:s'));

  }
}


// Monthly crons
$cron = Cron::newInstance()->getCronByType('MONTHLY');

if(is_array($cron)) {
  $i_next = strtotime($cron['d_next_exec']);

  if(
    (CLI && $type === 'monthly') 
    || ($force && ($type === 'monthly' || $type === 'all')) 
    || (!CLI && ($i_now - $i_next + $shift_seconds_minutely) >= 0)
  ) {
    // update the next execution time in t_cron
    //$d_next = date('Y-m-d H:i:s', $i_now_truncated + (30 * 24 * 3600));
    $d_next = date('Y-m-d H:i:s', strtotime('next month', $i_now_truncated));

    Cron::newInstance()->update(array('d_last_exec' => $d_now, 'd_next_exec' => $d_next), array('e_type' => 'MONTHLY'));
    
    
    // Run cron AFTER updating the next execution time to avoid double run of cron
    $purge = osc_purge_latest_searches();

    if($purge === 'hour') {
      LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', strtotime('- 1 hour')));
      
    } else if($purge === 'day') {
      LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', strtotime('- 1 day')));

    } else if ($purge == 'week') {
      LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', strtotime('- 1 week')));
      
    } else if ($purge == 'month') {
      LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', strtotime('- 1 month')));

    } else if ($purge == 'year') {
      LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', strtotime('- 1 year')));

    // } else if(!in_array($purge, array('forever', 'day', 'week', 'month', 'year'))) {
    } else {
      LatestSearches::newInstance()->purgeNumber((int)$purge);
    }

    osc_run_hook('cron_monthly');

    if($print === true) {
      if(isset($all_hooks['cron_monthly']) && is_array($all_hooks['cron_monthly']) && count($all_hooks['cron_monthly']) > 0) {
        $messages[] = ' > ' . sprintf(__('Starting to run functions of "%s"'), 'cron_monthly');

        foreach($all_hooks['cron_monthly'] as $priority => $cron_functions) {
          if(is_array($cron_functions) && count($cron_functions) > 0) {
            foreach($cron_functions as $cfunc) {
              if(is_string($cfunc)) {
                $messages[] = ' >>> ' . sprintf(__('Executed cron function "%s" with priority %s'), $cfunc, $priority);
              }
            }
          }
        }
      }
    }
    
    $messages[] = ' > ' . sprintf(__('Cron type "%s" finished at %s'), 'MONTHLY', date('Y-m-d H:i:s'));
  }
}


// Yearly crons
$cron = Cron::newInstance()->getCronByType('YEARLY');
if(is_array($cron)) {
  $i_next = strtotime($cron['d_next_exec']);

  if(
    (CLI && $type === 'yearly') 
    || ($force && ($type === 'yearly' || $type === 'all')) 
    || (!CLI && ($i_now - $i_next + $shift_seconds_minutely) >= 0)
  ) {
    // update the next execution time in t_cron
    $d_next = date('Y-m-d H:i:s', strtotime('+1 year', $i_now_truncated));

    Cron::newInstance()->update(array('d_last_exec' => $d_now, 'd_next_exec' => $d_next), array('e_type' => 'YEARLY'));


    // Run cron AFTER updating the next execution time to avoid double run of cron
    $purge = osc_purge_latest_searches();

    if($purge === 'hour') {
      LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', strtotime('- 1 hour')));
      
    } else if($purge === 'day') {
      LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', strtotime('- 1 day')));

    } else if ($purge == 'week') {
      LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', strtotime('- 1 week')));
      
    } else if ($purge == 'month') {
      LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', strtotime('- 1 month')));

    } else if ($purge == 'year') {
      LatestSearches::newInstance()->purgeDate(date('Y-m-d H:i:s', strtotime('- 1 year')));

    // } else if(!in_array($purge, array('forever', 'day', 'week', 'month', 'year'))) {
    } else {
      LatestSearches::newInstance()->purgeNumber((int)$purge);
    }


    osc_run_hook('cron_yearly');

    if($print === true) {
      if(isset($all_hooks['cron_yearly']) && is_array($all_hooks['cron_yearly']) && count($all_hooks['cron_yearly']) > 0) {
        $messages[] = ' > ' . sprintf(__('Starting to run functions of "%s"'), 'cron_yearly');

        foreach($all_hooks['cron_yearly'] as $priority => $cron_functions) {
          if(is_array($cron_functions) && count($cron_functions) > 0) {
            foreach($cron_functions as $cfunc) {
              if(is_string($cfunc)) {
                $messages[] = ' >>> ' . sprintf(__('Executed cron function "%s" with priority %s'), $cfunc, $priority);
              }
            }
          }
        }
      }
    }
    
    $messages[] = ' > ' . sprintf(__('Cron type "%s" finished at %s'), 'YEARLY', date('Y-m-d H:i:s'));
  }
}


osc_run_hook('cron');

$exec_time =  microtime(true) - $start_time;

$messages[] = str_repeat('-', 100);
$messages[] = sprintf(__('Cron finished at %s'), date('Y-m-d H:i:s'));
$messages[] = sprintf(__('Execution time: %s sec'), number_format($exec_time, 4));


// Print report
if($print === true) {
  echo '<pre>';

  foreach($messages as $m) {
    echo $m . PHP_EOL;
  }
  
  echo '</pre>';
}
