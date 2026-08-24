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


$all = (int)osc_get_preference('location_todo');
$worktodo = LocationsTmp::newInstance()->count();
$category_running = (int)osc_get_preference('category_stats_recalc_running');
$category_done = (int)osc_get_preference('category_stats_recalc_done');
$category_total = (int)osc_get_preference('category_stats_recalc_total');
$user_running = (int)osc_get_preference('user_stats_recalc_running');
$user_done = (int)osc_get_preference('user_stats_recalc_done');
$user_total = (int)osc_get_preference('user_stats_recalc_total');

function render_offset(){
  return 'row-offset';
}

function statistics_format_last_recalc($time) {
  $time = (int)$time;
  if($time <= 0) {
    return __('Never');
  }

  return date(osc_date_format() . ' ' . osc_time_format(), $time);
}

function customHead() {
  $all = (int)osc_get_preference('location_todo');
  if($all < 0) {
    $all = 0;
  }

  $worktodo = LocationsTmp::newInstance()->count();
  $category_running = (int)osc_get_preference('category_stats_recalc_running');
  $user_running = (int)osc_get_preference('user_stats_recalc_running');
  ?>
  <style>
    #statistics-setting #cleanup-settings {margin:0 0 35px 0;}
    #statistics-setting .stats-last-recalc {margin:0 0 10px 0;color:#646970;}
    #statistics-setting .stats-progress-wrap {margin:8px 0 10px 0;}
    #statistics-setting .stats-progress-bar {height:8px;border-radius:3px;background:#dcdcde;overflow:hidden;}
    #statistics-setting .stats-progress-val {height:100%;width:0;background:#007cba;transition:width 0.2s ease;}
    #statistics-setting .stats-progress-val.stats-progress-done {background:#46b450;}
    #statistics-setting .stats-progress-label {margin:8px 0 0 0;}
    #location_percent, #category_percent, #user_percent {margin-right:10px;display:inline-block;}
    #statistics-setting .form-horizontal .form-actions.no-padding {padding-bottom:0;}
  </style>
  <script type="text/javascript">
    function updateProgress(prefix, data) {
      var percent = parseInt(data.percent, 10);
      var pending = parseInt(data.pending, 10);
      var processed = parseInt(data.processed, 10);
      var total = parseInt(data.total, 10);

      if(isNaN(percent)) {
        percent = 0;
      }

      if(isNaN(pending)) {
        pending = 0;
      }

      if(isNaN(processed)) {
        processed = 0;
      }

      if(isNaN(total)) {
        total = 0;
      }

      if(percent < 0) {
        percent = 0;
      }

      if(percent > 100) {
        percent = 100;
      }

      $('#' + prefix + '_percent').text(percent + '%');
      $('#' + prefix + '_progress_val').css('width', percent + '%');
      if(percent < 100) {
        $('#' + prefix + '_progress_val').removeClass('stats-progress-done');
      }
      $('#' + prefix + '_progress_text').text(processed + ' / ' + total + ' processed, ' + pending + ' remaining');
    }

    function setProgressDone(prefix, text) {
      $('#' + prefix + '_percent').text('100%');
      $('#' + prefix + '_progress_val').css('width', '100%').addClass('stats-progress-done');
      $('#' + prefix + '_progress_text').text(text);
    }

    function pollLocationStats() {
      $.ajax({
        type: "POST",
        url: '<?php echo osc_admin_base_url(true)?>?page=ajax&action=location_stats&<?php echo osc_csrf_token_url(); ?>',
        dataType: 'json',
        success: function(data) {
          updateProgress('location', data);

          if(data.status == 'done') {
            setProgressDone('location', '<?php echo osc_esc_js(__('Location statistics recalculation completed.')); ?>');
          } else {
            setTimeout(pollLocationStats, 700);
          }
        }
      });
    }

    function pollUserStats() {
      $.ajax({
        type: "POST",
        url: '<?php echo osc_admin_base_url(true)?>?page=ajax&action=user_stats_recalc&<?php echo osc_csrf_token_url(); ?>',
        dataType: 'json',
        success: function(data) {
          updateProgress('user', data);

          if(data.status == 'done') {
            setProgressDone('user', '<?php echo osc_esc_js(__('User statistics recalculation completed.')); ?>');
          } else {
            setTimeout(pollUserStats, 700);
          }
        }
      });
    }

    function pollCategoryStats() {
      $.ajax({
        type: "POST",
        url: '<?php echo osc_admin_base_url(true)?>?page=ajax&action=category_stats_recalc&<?php echo osc_csrf_token_url(); ?>',
        dataType: 'json',
        success: function(data) {
          updateProgress('category', data);

          if(data.status == 'done') {
            setProgressDone('category', '<?php echo osc_esc_js(__('Category statistics recalculation completed.')); ?>');
          } else {
            setTimeout(pollCategoryStats, 700);
          }
        }
      });
    }

    $(document).ready(function(){
      if(<?php echo $worktodo; ?> > 0) {
        $('#location_progress_text').text('<?php echo osc_esc_js(__('Location statistics recalculation is running. Please wait until it reaches 100%.')); ?>');
        pollLocationStats();
      }

      if(<?php echo $category_running; ?> == 1) {
        $('#category_progress_text').text('<?php echo osc_esc_js(__('Category statistics recalculation is running. Please wait until it reaches 100%.')); ?>');
        pollCategoryStats();
      }

      if(<?php echo $user_running; ?> == 1) {
        $('#user_progress_text').text('<?php echo osc_esc_js(__('User statistics recalculation is running. Please wait until it reaches 100%.')); ?>');
        pollUserStats();
      }
    });
  </script>
  <?php
}

osc_add_hook('admin_header', 'customHead', 10);


function customPageHeader(){
  ?>
  <h1><?php _e('Statistics'); ?></h1>
  <?php
}

osc_add_hook('admin_page_header', 'customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Statistics - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php');
?>

<div id="statistics-setting">
  <div id="cleanup-settings">
      <h2 class="render-title"><?php _e('Location statistics'); ?></h2>
      <p class="stats-last-recalc"><?php echo sprintf(__('Last recalculation: %s'), osc_esc_html(statistics_format_last_recalc(osc_stats_last_recalc('location')))); ?></p>
      <?php if($worktodo > 0) { ?>
        <div class="stats-progress-wrap">
          <div class="stats-progress-bar"><div id="location_progress_val" class="stats-progress-val" style="width:0;"></div></div>
          <p class="stats-progress-label"><strong id="location_percent">0%</strong> <span id="location_progress_text"><?php _e('Preparing recalculation...'); ?></span></p>
        </div>
      <?php } ?>
      <p><?php _e('Rebuilds totals for countries, regions and cities based on current listings.'); ?></p>
      <form action="<?php echo osc_admin_base_url(true); ?>" method="post">
        <?php echo osc_csrf_token_form(); ?>
        <input type="hidden" name="action" value="locations_post" />
        <input type="hidden" name="page" value="tools" />
        <fieldset>
          <div class="form-horizontal">
            <div class="form-actions no-padding">
              <input type="submit" value="<?php echo osc_esc_html(__('Re-calculate location statistics')); ?>" class="btn btn-submit" />
            </div>
          </div>
        </fieldset>
      </form>
  </div>

  <div id="cleanup-settings">
      <h2 class="render-title"><?php _e('Category statistics'); ?></h2>
      <p class="stats-last-recalc"><?php echo sprintf(__('Last recalculation: %s'), osc_esc_html(statistics_format_last_recalc(osc_stats_last_recalc('category')))); ?></p>
      <?php if($category_running == 1) { ?>
        <div class="stats-progress-wrap">
          <div class="stats-progress-bar"><div id="category_progress_val" class="stats-progress-val" style="width:<?php echo ($category_total > 0 ? floor(($category_done * 100) / $category_total) : 0); ?>%;"></div></div>
          <p class="stats-progress-label"><strong id="category_percent"><?php echo ($category_total > 0 ? floor(($category_done * 100) / $category_total) : 0); ?>%</strong> <span id="category_progress_text"><?php echo sprintf(__('%s / %s processed'), $category_done, $category_total); ?></span></p>
        </div>
      <?php } ?>
      <p><?php _e('Rebuilds listing counters for all categories.'); ?></p>
      <form action="<?php echo osc_admin_base_url(true); ?>" method="post">
        <?php echo osc_csrf_token_form(); ?>
        <input type="hidden" name="action" value="category_post" />
        <input type="hidden" name="page" value="tools" />
        <fieldset>
          <div class="form-horizontal">
            <div class="form-actions no-padding">
              <input type="submit" value="<?php echo osc_esc_html(__('Re-calculate category statistics')); ?>" class="btn btn-submit" />
            </div>
          </div>
        </fieldset>
      </form>
  </div>

  <div id="cleanup-settings">
      <h2 class="render-title"><?php _e('User statistics'); ?></h2>
      <p class="stats-last-recalc"><?php echo sprintf(__('Last recalculation: %s'), osc_esc_html(statistics_format_last_recalc(osc_stats_last_recalc('user')))); ?></p>
      <?php if($user_running == 1) { ?>
        <div class="stats-progress-wrap">
          <div class="stats-progress-bar"><div id="user_progress_val" class="stats-progress-val" style="width:<?php echo ($user_total > 0 ? floor(($user_done * 100) / $user_total) : 0); ?>%;"></div></div>
          <p class="stats-progress-label"><strong id="user_percent"><?php echo ($user_total > 0 ? floor(($user_done * 100) / $user_total) : 0); ?>%</strong> <span id="user_progress_text"><?php echo sprintf(__('%s / %s processed'), $user_done, $user_total); ?></span></p>
        </div>
      <?php } ?>
      <p><?php _e('Rebuilds user totals such as number of active listings and active comments.'); ?></p>
      <form action="<?php echo osc_admin_base_url(true); ?>" method="post">
        <?php echo osc_csrf_token_form(); ?>
        <input type="hidden" name="action" value="user_stats_post" />
        <input type="hidden" name="page" value="tools" />
        <fieldset>
          <div class="form-horizontal">
            <div class="form-actions no-padding">
              <input type="submit" value="<?php echo osc_esc_html(__('Re-calculate user statistics')); ?>" class="btn btn-submit" />
            </div>
          </div>
        </fieldset>
      </form>
  </div>
</div>

<?php osc_current_admin_theme_path('parts/footer.php');
