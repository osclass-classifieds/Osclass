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


$users      = __get("users");
$listings   = __get("listings");
$item       = __get("item");
$users_by_country = __get("users_by_country");
$users_by_region  = __get("users_by_region");
$users_by_company = __get("users_by_company");
$users_by_status = __get("users_by_status");
$items_by_user_type = __get("items_by_user_type");
$latest_users   = __get("latest_users");
$comments_by_kind = __get("comments_by_kind");
$period     = __get("stats_period");
$prev_users = (int)__get('prev_users');
$prev_listings = (int)__get('prev_listings');

osc_add_filter('render-wrapper','render_offset');
function render_offset(){
  return 'row-offset';
}

function addHelp() {
  echo '<p>' . __('New user registrations and new listings for the selected period, plus where registered users are located.') . '</p>';
  echo '<p>' . __('Users by country and region are all-time totals. Those charts show the top values; the rest are grouped as Other.') . '</p>';
  echo '<p>' . __('Users by type counts registered personal and company accounts.') . '</p>';
  echo '<p>' . __('Users by status counts pending validation, active and blocked accounts. Blocked takes priority over pending validation.') . '</p>';
  echo '<p>' . __('Listings by user type counts all listings from non-registered posters, personal accounts and business accounts.') . '</p>';
  echo '<p>' . __('Comments chart uses the same period. It splits registered users from guests and shows each as a share of that day.') . '</p>';
  echo '<p>' . __('Saved-search alerts have their own statistics page.') . '</p>';
}
osc_add_hook('help_box','addHelp');

osc_add_hook('admin_page_header','customPageHeader');
function customPageHeader(){ ?>
  <h1><?php _e('Statistics'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
<?php
}

function customPageTitle($string) {
  return sprintf(__('User statistics - %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');

function customHead(){
  $users = __get("users");
  $listings = __get("listings");
  $users_by_country = osc_admin_stats_limit_slices((array)__get("users_by_country"), 's_country');
  $users_by_region  = osc_admin_stats_limit_slices((array)__get("users_by_region"), 's_region');
  $users_by_company = __get("users_by_company");
  if(!is_array($users_by_company)) {
    $users_by_company = array();
  }
  $users_by_company_rows = array(
    array('s_label' => __('Personal'), 'num' => (int)(isset($users_by_company['personal']) ? $users_by_company['personal'] : 0)),
    array('s_label' => __('Company'), 'num' => (int)(isset($users_by_company['company']) ? $users_by_company['company'] : 0))
  );
  if(isset($users_by_company['other']) && (int)$users_by_company['other'] > 0) {
    $users_by_company_rows[] = array('s_label' => __('Other'), 'num' => (int)$users_by_company['other']);
  }
  $users_by_status = __get("users_by_status");
  if(!is_array($users_by_status)) {
    $users_by_status = array();
  }
  $users_by_status_rows = array(
    array('s_label' => __('Pending'), 'num' => (int)(isset($users_by_status['pending']) ? $users_by_status['pending'] : 0)),
    array('s_label' => __('Active'), 'num' => (int)(isset($users_by_status['active']) ? $users_by_status['active'] : 0)),
    array('s_label' => __('Blocked'), 'num' => (int)(isset($users_by_status['blocked']) ? $users_by_status['blocked'] : 0))
  );
  $items_by_user_type = __get("items_by_user_type");
  if(!is_array($items_by_user_type)) {
    $items_by_user_type = array();
  }
  $items_by_user_type_rows = array(
    array('s_label' => __('Guest'), 'num' => (int)(isset($items_by_user_type['guest']) ? $items_by_user_type['guest'] : 0)),
    array('s_label' => __('Personal'), 'num' => (int)(isset($items_by_user_type['personal']) ? $items_by_user_type['personal'] : 0)),
    array('s_label' => __('Business'), 'num' => (int)(isset($items_by_user_type['business']) ? $items_by_user_type['business'] : 0))
  );
  $comments_by_kind = __get("comments_by_kind");
  if(!is_array($comments_by_kind)) {
    $comments_by_kind = array();
  }
  $comments_kind_user = 0;
  $comments_kind_guest = 0;
  foreach($comments_by_kind as $vals) {
    if(!is_array($vals)) {
      continue;
    }
    $comments_kind_user += (int)(isset($vals[0]) ? $vals[0] : 0);
    $comments_kind_guest += (int)(isset($vals[1]) ? $vals[1] : 0);
  }
  $comments_kind_rows = array(
    array('s_label' => __('Registered'), 'num' => $comments_kind_user),
    array('s_label' => __('Guest'), 'num' => $comments_kind_guest)
  );
  $chart_color = omg_current_color_scheme_chart();
  echo osc_admin_stats_chart_js(array(
    array('id' => 'placeholder', 'type' => 'line', 'labels' => array(__('Date'), __('New users')), 'rows' => $users, 'colors' => array($chart_color)),
    array('id' => 'placeholder_listings', 'type' => 'area', 'labels' => array(__('Date'), __('New listings')), 'rows' => $listings, 'colors' => array(osc_item_stats_palette_color(6))),
    array('id' => 'placeholder_comments', 'type' => 'stacked_percent', 'labels' => array(__('Date'), __('Registered'), __('Guest')), 'rows' => $comments_by_kind, 'colors' => array($chart_color, osc_item_stats_palette_color(6)))
  ), array(
    'page' => 'users',
    'mix' => array(
      array('id' => 'by_user_type', 'type' => 'pie', 'labels' => array(__('User type'), __('Listings')), 'rows' => $items_by_user_type_rows),
      array('id' => 'by_company', 'type' => 'donut', 'labels' => array(__('User type'), __('Users')), 'rows' => $users_by_company_rows),
      array('id' => 'by_status', 'type' => 'pie', 'labels' => array(__('Status'), __('Users')), 'rows' => $users_by_status_rows),
      array('id' => 'by_comments_kind', 'type' => 'donut', 'labels' => array(__('Account'), __('Comments')), 'rows' => $comments_kind_rows),
      array('id' => 'by_country', 'type' => 'bar', 'labels' => array(__('Country'), __('Users per country')), 'rows' => $users_by_country, 'colors' => array($chart_color)),
      array('id' => 'by_region', 'type' => 'bar', 'labels' => array(__('Region'), __('Users per region')), 'rows' => $users_by_region, 'colors' => array($chart_color))
    )
  ));
  osc_run_hook('admin_stats_header', 'users');
}

osc_add_hook('admin_header', 'customHead', 10);

osc_current_admin_theme_path( 'parts/header.php' );
?>

<?php
if(!is_array($items_by_user_type)) {
  $items_by_user_type = array();
}
$items_by_user_type_sum = (int)(isset($items_by_user_type['guest']) ? $items_by_user_type['guest'] : 0) + (int)(isset($items_by_user_type['personal']) ? $items_by_user_type['personal'] : 0) + (int)(isset($items_by_user_type['business']) ? $items_by_user_type['business'] : 0);
if(!is_array($users_by_company)) {
  $users_by_company = array();
}
$users_by_company_sum = (int)(isset($users_by_company['personal']) ? $users_by_company['personal'] : 0) + (int)(isset($users_by_company['company']) ? $users_by_company['company'] : 0) + (int)(isset($users_by_company['other']) ? $users_by_company['other'] : 0);
if(!is_array($users_by_status)) {
  $users_by_status = array();
}
$users_by_status_sum = (int)(isset($users_by_status['pending']) ? $users_by_status['pending'] : 0) + (int)(isset($users_by_status['active']) ? $users_by_status['active'] : 0) + (int)(isset($users_by_status['blocked']) ? $users_by_status['blocked'] : 0);
if(!is_array($comments_by_kind)) {
  $comments_by_kind = array();
}
$comments_kind_user = 0;
$comments_kind_guest = 0;
foreach($comments_by_kind as $vals) {
  if(!is_array($vals)) {
    continue;
  }
  $comments_kind_user += (int)(isset($vals[0]) ? $vals[0] : 0);
  $comments_kind_guest += (int)(isset($vals[1]) ? $vals[1] : 0);
}
$comments_kind_sum = $comments_kind_user + $comments_kind_guest;
$sum_users = array_sum((array)$users);
$sum_listings = array_sum((array)$listings);
$sum_personal = (int)(isset($users_by_company['personal']) ? $users_by_company['personal'] : 0);
$sum_company = (int)(isset($users_by_company['company']) ? $users_by_company['company'] : 0);
$recent_users = array();
foreach((array)$latest_users as $u) {
  $label = (isset($u['s_name']) && $u['s_name'] != '' ? $u['s_name'] : (isset($u['s_email']) ? $u['s_email'] : '#' . (int)$u['pk_i_id']));
  $meta_parts = array();
  if(isset($u['s_email']) && $u['s_email'] != '' && $u['s_email'] != $label) {
    $meta_parts[] = $u['s_email'];
  }
  if(isset($u['s_country']) && $u['s_country'] != '') {
    $meta_parts[] = $u['s_country'];
  }
  if(isset($u['b_company']) && (int)$u['b_company'] == 1) {
    $meta_parts[] = __('Company');
  } else {
    $meta_parts[] = __('Personal');
  }
  $recent_users[] = array(
    'label' => $label,
    'href' => osc_admin_base_url(true) . '?page=users&action=edit&id=' . (int)$u['pk_i_id'],
    'meta' => implode(', ', $meta_parts),
    'date' => osc_admin_stats_recent_date(isset($u['dt_reg_date']) ? $u['dt_reg_date'] : '')
  );
}
?>
<div class="grid-system" id="stats-page">
  <div class="grid-row grid-50">
    <div class="row-wrapper">
      <h2 class="render-title"><?php _e('User statistics'); ?></h2>
    </div>
  </div>
  <div class="grid-row grid-50">
    <div class="row-wrapper">
      <?php echo osc_admin_stats_period_links('users'); ?>
    </div>
  </div>
  <div class="grid-row grid-100">
    <div class="row-wrapper osc-stats-kpi-cards">
      <a href="<?php echo osc_admin_base_url(true); ?>?page=users">
        <span class="k-label"><?php _e('New users'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_users, $prev_users); ?>
      </a>
      <a href="<?php echo osc_admin_base_url(true); ?>?page=stats&amp;action=items&amp;stats_period=<?php echo osc_esc_html($period); ?>">
        <span class="k-label"><?php _e('New listings'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_listings, $prev_listings); ?>
      </a>
      <span>
        <span class="k-label"><?php _e('Personal'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_personal); ?>
      </span>
      <span>
        <span class="k-label"><?php _e('Company'); ?></span>
        <?php echo osc_stats_kpi_value_html($sum_company); ?>
      </span>
      <?php osc_run_hook('admin_stats_kpi', 'users'); ?>
    </div>
  </div>
  <div class="stats-band">
  <div class="grid-row grid-65 stats-main">
    <div class="row-wrapper">
      <div class="widget-box">
        <div class="widget-box-title">
          <h3><?php _e('New users'); ?></h3>
        </div>
        <div class="widget-box-content">
          <?php echo osc_admin_stats_chart_total(__('New users'), $sum_users, $period); ?>
          <div id="placeholder" class="graph-placeholder">
            <?php if(count($users) == 0 ) {
              _e("There're no statistics yet");
            } ?>
          </div>
        </div>
      </div>
      <div class="widget-box">
        <div class="widget-box-title">
          <h3><?php _e('New listings'); ?></h3>
        </div>
        <div class="widget-box-content">
          <?php echo osc_admin_stats_chart_total(__('New listings'), $sum_listings, $period); ?>
          <div id="placeholder_listings" class="graph-placeholder">
            <?php if(count($listings) == 0 ) {
              _e("There're no statistics yet");
            } ?>
          </div>
        </div>
      </div>
      <div class="widget-box">
        <div class="widget-box-title">
          <h3><?php _e('New comments'); ?></h3>
        </div>
        <div class="widget-box-content">
          <?php echo osc_admin_stats_chart_total(__('New comments'), $comments_kind_sum, $period, array(__('Registered') => $comments_kind_user, __('Guest') => $comments_kind_guest)); ?>
          <div id="placeholder_comments" class="graph-placeholder">
            <?php if($comments_kind_sum == 0) {
              _e("There're no statistics yet");
            } ?>
          </div>
        </div>
      </div>
      <?php osc_run_hook('admin_stats_main', 'users'); ?>
    </div>
  </div>
  <div class="grid-row grid-35 stats-side">
    <div class="row-wrapper">
      <div class="widget-box">
        <div class="widget-box-title">
          <h3><?php _e('Listings by user type'); ?></h3>
        </div>
        <div class="widget-box-content">
          <div id="by_user_type" class="graph-placeholder">
            <?php if($items_by_user_type_sum == 0) {
              _e("There're no statistics yet");
            } ?>
          </div>
        </div>
      </div>
      <div class="widget-box">
        <div class="widget-box-title">
          <h3><?php _e('Users by type'); ?></h3>
        </div>
        <div class="widget-box-content">
          <div id="by_company" class="graph-placeholder">
            <?php if($users_by_company_sum == 0) {
              _e("There're no statistics yet");
            } ?>
          </div>
        </div>
      </div>
      <div class="widget-box">
        <div class="widget-box-title">
          <h3><?php _e('Users by status'); ?></h3>
        </div>
        <div class="widget-box-content">
          <div id="by_status" class="graph-placeholder">
            <?php if($users_by_status_sum == 0) {
              _e("There're no statistics yet");
            } ?>
          </div>
        </div>
      </div>
      <?php osc_run_hook('admin_stats_side', 'users'); ?>
    </div>
  </div>
  </div>
  <div class="stats-band">
  <div class="grid-row grid-65 stats-main">
    <div class="row-wrapper">
      <div class="widget-box">
        <div class="widget-box-title">
          <h3><?php _e('Users per country'); ?></h3>
        </div>
        <div class="widget-box-content">
          <div id="by_country" class="graph-placeholder">
            <?php if(count($users_by_country) == 0 ) {
              _e("There're no statistics yet");
            } ?>
          </div>
        </div>
      </div>
      <div class="widget-box">
        <div class="widget-box-title">
          <h3><?php _e('Users per region'); ?></h3>
        </div>
        <div class="widget-box-content">
          <div id="by_region" class="graph-placeholder">
            <?php if(count($users_by_region) == 0 ) {
              _e("There're no statistics yet");
            } ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="grid-row grid-35 stats-side">
    <div class="row-wrapper">
      <div class="widget-box stats-span-2">
        <div class="widget-box-title"><h3><?php _e('Latest users'); ?></h3></div>
        <div class="widget-box-content"><?php echo osc_admin_stats_recent_table($recent_users); ?></div>
      </div>
      <div class="widget-box">
        <div class="widget-box-title"><h3><?php _e('Avg. items per user'); ?></h3></div>
        <div class="widget-box-content">
          <div class="stats-detail">
            <?php printf( __('%s listings per user'), number_format($item, 2) ); ?>
          </div>
        </div>
      </div>
      <div class="widget-box">
        <div class="widget-box-title">
          <h3><?php _e('Comments by account'); ?></h3>
        </div>
        <div class="widget-box-content">
          <div id="by_comments_kind" class="graph-placeholder">
            <?php if($comments_kind_sum == 0) {
              _e("There're no statistics yet");
            } ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
  <div class="clear"></div>
  <?php osc_run_hook('admin_stats_after', 'users'); ?>
</div>

<?php osc_current_admin_theme_path( 'parts/footer.php' );
