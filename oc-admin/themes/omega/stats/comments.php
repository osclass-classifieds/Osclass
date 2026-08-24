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


$comments    = __get("comments");
$comments_by_rating = __get("comments_by_rating");
$comments_by_reply = __get("comments_by_reply");
$comments_by_status = __get("comments_by_status");
$latest_comments = __get("latest_comments");
$period      = __get("stats_period");
$prev_comments = (int)__get('prev_comments');
$prev_rated = (int)__get('prev_rated');
$prev_replies = (int)__get('prev_replies');
$prev_pending = (int)__get('prev_pending');

function render_offset(){
  return 'row-offset';
}

osc_add_filter('render-wrapper','render_offset');


function addHelp() {
  echo '<p>' . __('New comments posted in the selected period, plus rating, replies and status for that same period.') . '</p>';
  echo '<ul><li>' . __('With rating counts comments that have a rating greater than 0.') . '</li>';
  echo '<li>' . __('Replies are comments linked to a parent comment.') . '</li>';
  echo '<li>' . __('Status: blocked includes blocked and spam comments. Pending validation is not yet activated. Active is enabled, activated and not spam.') . '</li></ul>';
}

osc_add_hook('help_box','addHelp');


osc_add_hook('admin_page_header','customPageHeader');
function customPageHeader(){
  ?>
  <h1><?php _e('Statistics'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

function customPageTitle($string) {
  return sprintf(__('Comment statistics - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');


function customHead() {
  $comments = __get("comments");
  $comments_by_rating = __get("comments_by_rating");
  $comments_by_reply = __get("comments_by_reply");
  $comments_by_status = __get("comments_by_status");
  if(!is_array($comments_by_rating)) {
    $comments_by_rating = array();
  }
  $comments_by_rating_rows = array(
    array('s_label' => __('Rated'), 'num' => (int)(isset($comments_by_rating['rated']) ? $comments_by_rating['rated'] : 0)),
    array('s_label' => __('Unrated'), 'num' => (int)(isset($comments_by_rating['unrated']) ? $comments_by_rating['unrated'] : 0))
  );
  if(!is_array($comments_by_reply)) {
    $comments_by_reply = array();
  }
  $comments_by_reply_rows = array(
    array('s_label' => __('Comments'), 'num' => (int)(isset($comments_by_reply['comment']) ? $comments_by_reply['comment'] : 0)),
    array('s_label' => __('Replies'), 'num' => (int)(isset($comments_by_reply['reply']) ? $comments_by_reply['reply'] : 0))
  );
  if(!is_array($comments_by_status)) {
    $comments_by_status = array();
  }
  $comments_by_status_rows = array(
    array('s_label' => __('Pending'), 'num' => (int)(isset($comments_by_status['pending']) ? $comments_by_status['pending'] : 0)),
    array('s_label' => __('Active'), 'num' => (int)(isset($comments_by_status['active']) ? $comments_by_status['active'] : 0)),
    array('s_label' => __('Blocked'), 'num' => (int)(isset($comments_by_status['blocked']) ? $comments_by_status['blocked'] : 0))
  );
  $chart_color = omg_current_color_scheme_chart();
  echo osc_admin_stats_chart_js(array(
    array('id' => 'placeholder', 'type' => 'stepped_area', 'labels' => array(__('Date'), __('New comments')), 'rows' => $comments, 'colors' => array($chart_color))
  ), array(
    'page' => 'comments',
    'mix' => array(
      array('id' => 'by_rating', 'type' => 'donut', 'labels' => array(__('Rating'), __('Comments')), 'rows' => $comments_by_rating_rows),
      array('id' => 'by_reply', 'type' => 'pie', 'labels' => array(__('Type'), __('Comments')), 'rows' => $comments_by_reply_rows),
      array('id' => 'by_status', 'type' => 'pie', 'labels' => array(__('Status'), __('Comments')), 'rows' => $comments_by_status_rows)
    )
  ));
  osc_run_hook('admin_stats_header', 'comments');
}

osc_add_hook('admin_header', 'customHead', 10);

osc_current_admin_theme_path( 'parts/header.php' );
if(!is_array($comments_by_rating)) {
  $comments_by_rating = array();
}
$comments_by_rating_sum = (int)(isset($comments_by_rating['rated']) ? $comments_by_rating['rated'] : 0) + (int)(isset($comments_by_rating['unrated']) ? $comments_by_rating['unrated'] : 0);
if(!is_array($comments_by_reply)) {
  $comments_by_reply = array();
}
$comments_by_reply_sum = (int)(isset($comments_by_reply['comment']) ? $comments_by_reply['comment'] : 0) + (int)(isset($comments_by_reply['reply']) ? $comments_by_reply['reply'] : 0);
if(!is_array($comments_by_status)) {
  $comments_by_status = array();
}
$comments_by_status_sum = (int)(isset($comments_by_status['pending']) ? $comments_by_status['pending'] : 0) + (int)(isset($comments_by_status['active']) ? $comments_by_status['active'] : 0) + (int)(isset($comments_by_status['blocked']) ? $comments_by_status['blocked'] : 0);
$sum_comments = array_sum((array)$comments);
$sum_rated = (int)(isset($comments_by_rating['rated']) ? $comments_by_rating['rated'] : 0);
$sum_replies = (int)(isset($comments_by_reply['reply']) ? $comments_by_reply['reply'] : 0);
$sum_pending = (int)(isset($comments_by_status['pending']) ? $comments_by_status['pending'] : 0);
$recent_comments = array();
foreach((array)$latest_comments as $c) {
  $name = (isset($c['s_author_name']) && $c['s_author_name'] != '' ? $c['s_author_name'] : '#' . (int)$c['pk_i_id']);
  $rating = (isset($c['i_rating']) ? (int)$c['i_rating'] : 0);
  $recent_comments[] = array(
    'label' => $name,
    'href' => osc_admin_base_url(true) . '?page=comments&action=comment_edit&id=' . (int)$c['pk_i_id'],
    'detail' => osc_admin_stats_recent_excerpt(isset($c['s_body']) ? $c['s_body'] : '', 110),
    'rating' => $rating,
    'date' => osc_admin_stats_recent_date(isset($c['dt_pub_date']) ? $c['dt_pub_date'] : '')
  );
}
?>

<div class="grid-system" id="stats-page">
  <div class="grid-row grid-50">
    <div class="row-wrapper">
      <h2 class="render-title"><?php _e('Comment statistics'); ?></h2>
    </div>
  </div>
  <div class="grid-row grid-50">
    <div class="row-wrapper">
      <?php echo osc_admin_stats_period_links('comments'); ?>
    </div>
  </div>
  <div class="grid-row grid-100">
    <div class="row-wrapper osc-stats-kpi-cards">
      <a href="<?php echo osc_admin_base_url(true); ?>?page=comments">
        <span class="k-label"><?php _e('Comments'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_comments, $prev_comments); ?>
      </a>
      <span>
        <span class="k-label"><?php _e('Rated'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_rated, $prev_rated); ?>
      </span>
      <span>
        <span class="k-label"><?php _e('Replies'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_replies, $prev_replies); ?>
      </span>
      <span>
        <span class="k-label"><?php _e('Pending'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_pending, $prev_pending); ?>
      </span>
      <?php osc_run_hook('admin_stats_kpi', 'comments'); ?>
    </div>
  </div>
  <div class="stats-band">
  <div class="grid-row grid-65 stats-main">
    <div class="row-wrapper">
      <div class="widget-box">
        <div class="widget-box-title">
          <h3><?php _e('Comments'); ?></h3>
        </div>
        <div class="widget-box-content">
          <?php echo osc_admin_stats_chart_total(__('Comments'), $sum_comments, $period); ?>
          <div id="placeholder" class="graph-placeholder">
            <?php if(count($comments) == 0 ) {
              _e("There're no statistics yet");
            } ?>
          </div>
        </div>
      </div>
      <div class="widget-box stats-span-2">
        <div class="widget-box-title"><h3><?php _e('Latest comments'); ?></h3></div>
        <div class="widget-box-content"><?php echo osc_admin_stats_recent_table($recent_comments); ?></div>
      </div>
      <?php osc_run_hook('admin_stats_main', 'comments'); ?>
    </div>
  </div>
  <div class="grid-row grid-35 stats-side">
    <div class="row-wrapper">
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('Comments by rating'); ?></h3></div>
        <div class="widget-box-content">
          <div id="by_rating" class="graph-placeholder">
            <?php if($comments_by_rating_sum == 0) {
              _e("There're no statistics yet");
            } ?>
          </div>
        </div>
      </div>
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('Comments vs replies'); ?></h3></div>
        <div class="widget-box-content">
          <div id="by_reply" class="graph-placeholder">
            <?php if($comments_by_reply_sum == 0) {
              _e("There're no statistics yet");
            } ?>
          </div>
        </div>
      </div>
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('Comments by status'); ?></h3></div>
        <div class="widget-box-content">
          <div id="by_status" class="graph-placeholder">
            <?php if($comments_by_status_sum == 0) {
              _e("There're no statistics yet");
            } ?>
          </div>
        </div>
      </div>
      <?php osc_run_hook('admin_stats_side', 'comments'); ?>
    </div>
  </div>
  </div>
  <div class="clear"></div>
  <?php osc_run_hook('admin_stats_after', 'comments'); ?>
</div>
<?php osc_current_admin_theme_path( 'parts/footer.php' );
