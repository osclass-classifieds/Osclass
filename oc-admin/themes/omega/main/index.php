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

osc_add_hook('admin_header','add_market_jsvariables');

$color_schemes = omg_color_schemes();

osc_add_filter('render-wrapper','render_offset');
function render_offset() {
  return 'row-offset';
}


if(!function_exists('addBodyClass')){
  function addBodyClass($array){
    $array[] = 'dashboard';
    return $array;
  }
}

osc_add_filter('admin_body_class','addBodyClass');


function customPageHeader() {
  ?>
  <h1>
    <?php _e('Dashboard'); ?>
    <a href="<?php echo osc_admin_base_url(true) . '?page=dashboard&action=settings'; ?>" class="btn btn-green ico float-right"><?php _e('Settings'); ?></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Dashboard - %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');

function customHead() {
  $series = __get('dash_chart_series');
  $mix = __get('dash_chart_mix');
  if(!is_array($series)) {
    $series = array();
  }
  if(!is_array($mix)) {
    $mix = array();
  }
  if(!empty($series) || !empty($mix)) {
    echo osc_admin_stats_chart_js($series, array('mix' => $mix));
  }
  ?>
<script type="text/javascript">
  $(document).ready(function() {
    $('.notes-widget-textarea').on('input', function() {
      this.style.height = '90px';
      this.style.height = Math.max(90, this.scrollHeight) + 'px';
    });

    $('.notes-widget-textarea').each(function() {
      this.style.height = '90px';
      this.style.height = Math.max(90, this.scrollHeight) + 'px';
    });
  });
</script>
<?php
}
osc_add_hook('admin_header', 'customHead', 10);

osc_current_admin_theme_path('parts/header.php');


$cols_hidden = array_filter(explode(',', osc_get_preference('admindash_columns_hidden', 'osclass')));
$widgets_hidden = array_filter(explode(',', osc_get_preference('admindash_widgets_hidden', 'osclass')));
$general_notes = (string)osc_get_preference('notes', 'osclass');
$my_notes = (string)osc_get_preference('notes_' . (int)osc_logged_admin_id(), 'osclass');


function osc_admin_widget_column_width($id) {
  $cols_hidden = array_filter(explode(',', osc_get_preference('admindash_columns_hidden', 'osclass')));
  $cols_count = 3 - count($cols_hidden);

  if($cols_count <= 1) {
    return 100;
  }


  if($id == 1) {
    if($cols_count == 3) {
      return 30;
    } elseif($cols_count == 2) {
      if(in_array(2, $cols_hidden)) {           // col 2 hidden
        return 50;
      } else if(in_array(3, $cols_hidden)) {    // col 3 hidden
        return 40;
      }
    }
  } elseif($id == 2) {
    if($cols_count == 3) {
      return 50;
    } elseif($cols_count == 2) {
      return 60;
    }
  } elseif($id == 3) {
    if($cols_count == 3) {
      return 20;
    } elseif($cols_count == 2) {
      if(in_array(1, $cols_hidden)) {           // col 1 hidden
        return 40;
      } else if(in_array(2, $cols_hidden)) {    // col 2 hidden
        return 50;
      }
    }
  }
}


function osc_admin_widget_collapsed($id) {
  $collapsed_widgets = explode(',', osc_get_preference('admindash_widgets_collapsed', 'osclass'));

  if(in_array($id, $collapsed_widgets)) {
    return true;
  }

  return false;
}


function osc_admin_widget_title_controls($id) {
  $collapsed = osc_admin_widget_collapsed($id);
  echo '<i class="fa fa-caret-' . ($collapsed ? 'down' : 'up') . ' collapse" title="' . osc_esc_html(__('Collapse')) . '"></i>';
  echo '<i class="fa fa-arrows-alt maximize" title="' . osc_esc_html(__('Maximize')) . '"></i>';
}
?>

<div id="dashboard">
  <?php if(__get('engine_check') == false) { ?>
    <div class="flashmessage flashmessage-error">
      <p class="info"><?php _e('ERROR: Database tables engine check failed! All datatabase tables must use <b>InnoDB</b> engine, using different engine will cause compatibility problems with plugins, themes and foreign key constraints. Please change database tables engine to InnoDB. Note that item description can use MyISAM engine as well.'); ?></p>
    </div>
  <?php } ?>

  <?php if(testCurl() === false && testFsockopen() === false) { ?>
    <div class="flashmessage flashmessage-error">
      <p class="info"><?php _e('ERROR: Your server does not support <u>curl</u> or <u>fsockopen</u>. You must enable at least one for correct Osclass functionality! Contact your hosting provider for help.'); ?></p>
    </div>
  <?php } ?>

  <div class="widget-maximized-cover"></div>
  <div class="grid-system">
    <div class="grid-row grid-100">
      <div class="row-wrapper"><?php osc_run_hook('admin_dashboard_top'); ?></div>
    </div>

    <?php if(count($cols_hidden) == 3) { ?>
      <div class="grid-row grid-100">
        <div class="row-wrapper">
          <div class="dash-empty">
            <?php _e('Nothing to show there, you\'ve hidden all dashboard columns.'); ?></br>
            <?php _e('To configure columns & widgets, click on "Settings" button in top right corner.'); ?>
          </div>
        </div>
      </div>
    <?php } ?>

    <?php if(!in_array(1, $cols_hidden)) { ?>
      <div class="grid-row grid-<?php echo osc_admin_widget_column_width(1); ?>">
        <div class="row-wrapper">
          <?php osc_run_hook('admin_dashboard_col1_top'); ?>

          <?php if(!in_array('glance', $widgets_hidden)) { ?>
            <div class="widget-box" id="widget-glance" data-id="glance">
              <div class="widget-box-title">
                <h3>
                  <span><?php _e('At a glance'); ?></span>
                  <?php osc_admin_widget_title_controls('glance'); ?>
                </h3>
              </div>
              <div class="widget-box-content" <?php echo (osc_admin_widget_collapsed('glance') ? 'style="display:none;"' : ''); ?> >
                <?php
                  $front_theme = WebThemes::newInstance()->loadThemeInfo(WebThemes::newInstance()->getCurrentTheme());
                  $back_theme = AdminThemes::newInstance()->loadThemeInfo(AdminThemes::newInstance()->getCurrentTheme());

                  $front_name = trim($front_theme['name']);
                  if($front_theme['theme_uri'] <> '') {
                    $front_name = '<a href="' . $front_theme['theme_uri'] . '" target="_blank">' . trim($front_theme['name']) . '</a>';
                  }

                  $back_name = trim($back_theme['name']);
                  if($back_theme['theme_uri'] <> '') {
                    $back_name = '<a href="' . $back_theme['theme_uri'] . '" target="_blank">' . trim($back_theme['name']) . '</a>';
                  }

                  $plugins_all = count(Plugins::listAll());
                  $plugins_active = count(array_intersect(Plugins::listEnabled(), Plugins::listAll()));
                  $plugins_disabled = count(array_intersect(Plugins::listInstalled(), Plugins::listAll())) - $plugins_active;
                  $plugins_notinstalled = $plugins_all - $plugins_active - $plugins_disabled;
                ?>

                <div class="row"><?php echo sprintf(__('Running on osclass v%s, using %s for front office and %s for backoffice.'), OSCLASS_VERSION, $front_name, $back_name); ?></div>
                <div class="row"><?php echo sprintf(__('This website is using %s plugins, %s are active, %s are disabled and %s are not installed.'), $plugins_all, $plugins_active, $plugins_disabled, $plugins_notinstalled); ?></div>
              </div>
            </div>
          <?php } ?>


          <?php if(!in_array('notes-general', $widgets_hidden)) { ?>
            <div class="widget-box" id="widget-notes-general" data-id="notes-general">
              <div class="widget-box-title">
                <h3>
                  <span><?php _e('Notes'); ?></span>
                  <?php osc_admin_widget_title_controls('notes-general'); ?>
                </h3>
              </div>
              <div class="widget-box-content" <?php echo (osc_admin_widget_collapsed('notes-general') ? 'style="display:none;"' : ''); ?>>
                <form action="<?php echo osc_admin_base_url(true); ?>" method="post" class="nocsrf">
                  <input type="hidden" name="page" value="main" />
                  <input type="hidden" name="action" value="notes_post" />
                  <input type="hidden" name="notes_type" value="general" />
                  <?php echo osc_csrf_token_form(); ?>

                  <textarea name="notes" class="notes-widget-textarea notes-widget-field" placeholder="<?php echo osc_esc_html(__('Write your notes, reminders, and ideas here.')); ?>"><?php echo osc_esc_html($general_notes); ?></textarea>

                  <div class="notes-widget-actions">
                    <input type="submit" class="btn btn-submit" value="<?php echo osc_esc_html(__('Save')); ?>" />
                  </div>
                </form>
              </div>
            </div>
          <?php } ?>


          <?php if(!in_array('notes-my', $widgets_hidden)) { ?>
            <div class="widget-box" id="widget-notes-my" data-id="notes-my">
              <div class="widget-box-title">
                <h3>
                  <span><?php _e('My notes (only visible to you)'); ?></span>
                  <?php osc_admin_widget_title_controls('notes-my'); ?>
                </h3>
              </div>
              <div class="widget-box-content" <?php echo (osc_admin_widget_collapsed('notes-my') ? 'style="display:none;"' : ''); ?>>
                <form action="<?php echo osc_admin_base_url(true); ?>" method="post" class="nocsrf">
                  <input type="hidden" name="page" value="main" />
                  <input type="hidden" name="action" value="notes_post" />
                  <input type="hidden" name="notes_type" value="my" />
                  <?php echo osc_csrf_token_form(); ?>

                  <textarea name="notes" class="notes-widget-textarea notes-widget-field" placeholder="<?php echo osc_esc_html(__('Write your notes, reminders, and ideas here.')); ?>"><?php echo osc_esc_html($my_notes); ?></textarea>

                  <div class="notes-widget-actions">
                    <input type="submit" class="btn btn-submit" value="<?php echo osc_esc_html(__('Save')); ?>" />
                  </div>
                </form>
              </div>
            </div>
          <?php } ?>


          <?php if(!in_array('optimization', $widgets_hidden)) { ?>
            <div class="widget-box" id="widget-optimization" data-id="optimization">
              <div class="widget-box-title">
                <h3>
                  <span><?php _e('Optimization'); ?></span>
                  <?php osc_admin_widget_title_controls('optimization'); ?>
                </h3>
              </div>
              <div class="widget-box-content" <?php echo (osc_admin_widget_collapsed('optimization') ? 'style="display:none;"' : ''); ?> >
                <?php if(osc_css_minify()) { ?>
                  <div class="row"><i class="fa fa-check-circle-o"></i> <?php _e('CSS style sheets optimization is enabled.'); ?></div>
                <?php } else { ?>
                  <div class="row"><?php _e('CSS style sheets optimization is disabled.'); ?></div>
                <?php } ?>

                <?php if(osc_js_minify()) { ?>
                  <div class="row"><i class="fa fa-check-circle-o"></i> <?php _e('JS scripts optimization is enabled.'); ?></div>
                <?php } else { ?>
                  <div class="row"><?php _e('JS scripts optimization is disabled.'); ?></div>
                <?php } ?>

                <?php if(osc_css_minify() || osc_js_minify()) { ?>
                  <div class="row"><?php echo sprintf(__('Optimized files has size %s.'), '<strong>' . osc_dir_size(osc_base_path() . OC_CONTENT_FOLDER . '/uploads/minify/') . '</strong>'); ?></div>
                <?php } ?>

                <a href="<?php echo osc_admin_base_url(true); ?>?page=settings&action=optimization" class="btn btn-submit" style="margin-top:5px;"><?php echo __('Optimization settings'); ?></a>
              </div>
            </div>
          <?php } ?>


          <?php if(!in_array('api', $widgets_hidden)) { ?>
            <div class="widget-box" id="widget-api" data-id="api">
              <div class="widget-box-title">
                <h3>
                  <span><?php _e('Market accessibility'); ?></span>
                  <?php osc_admin_widget_title_controls('api'); ?>
                </h3>
              </div>
              <div class="widget-box-content" <?php echo (osc_admin_widget_collapsed('api') ? 'style="display:none;"' : ''); ?> >
                <div class="widget-loading">
                  <img src="<?php echo osc_current_admin_theme_url(); ?>images/spinner-2x.gif" alt="<?php echo osc_esc_html(__('Loading...')); ?>"/>
                  <span><?php _e('Loading...'); ?></span>
                </div>
              </div>
            </div>

            <script>
              $.ajax({
                url : 'index.php?page=main&action=widget&file=api.php',
                type: "POST",
                success: function(data) {
                  $('.widget-box#widget-api .widget-box-content').html(data);
                },
                error: function() {
                  $('.widget-box#widget-api .widget-box-content .widget-loading').html('<?php echo __('Unable to load data'); ?>');
                }
              });
            </script>
          <?php } ?>


          <?php if(!in_array('items', $widgets_hidden)) { ?>
            <div class="widget-box" id="widget-items" data-id="items">
              <div class="widget-box-title">
                <h3>
                  <span><?php _e('Listings activity'); ?></span>
                  <?php osc_admin_widget_title_controls('items'); ?>
                </h3>
              </div>
              <div class="widget-box-content" <?php echo (osc_admin_widget_collapsed('items') ? 'style="display:none;"' : ''); ?>>
                <?php
                  $items_last_day = osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_item WHERE dt_pub_date >= "%s"', DB_TABLE_PREFIX, date('Y-m-d H:i:s', strtotime('- 1 day'))));
                  $items_last_week = osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_item WHERE dt_pub_date >= "%s"', DB_TABLE_PREFIX, date('Y-m-d H:i:s', strtotime('- 7 day'))));
                  $items_all = osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_item', DB_TABLE_PREFIX));
                ?>

                <div class="row st">
                  <?php echo sprintf(__('Published in last 24 hours: %s'), '<strong>' . $items_last_day . '</strong>'); ?>
                </div>

                <div class="row st">
                  <?php echo sprintf(__('Published in last 7 days: %s'), '<strong>' . $items_last_week . '</strong>'); ?>
                </div>

                <div class="row st">
                  <?php echo sprintf(__('Overall published: %s'), '<strong>' . $items_all . '</strong>'); ?>
                </div>

                <div class="row"></div>

                <h4><?php _e('Recently published'); ?></h4>

                <?php
                  $mSearch = new Search();
                  $mSearch->addConditions('1=1 OR 1=1');
                  $mSearch->order('dt_pub_date', 'DESC');
                  $mSearch->limit(0, 5);
                  $items = $mSearch->doSearch();
                ?>

                <?php if(count($items) <= 0) { ?>
                  <div class="empty"><?php _e('No listings has been found'); ?></div>
                <?php } else { ?>
                  <?php foreach($items as $i) { ?>
                    <div class="row">
                      <?php
                        if($i['b_active'] == 1 && $i['b_enabled'] == 1 && $i['b_spam'] == 0) {
                          $title = __('Active');
                          $class = 'active';
                        } else if($i['b_active'] == 0) {
                          $title = __('Not validated');
                          $class = 'inactive';
                        } else if($i['b_spam'] == 1) {
                          $title = __('Spam');
                          $class = 'spam';
                        } else if($i['b_enabled'] == 0) {
                          $title = __('Blocked');
                          $class = 'blocked';
                        }
                      ?>

                      <span class="date"><?php echo osc_format_date($i['dt_pub_date'], 'd. M, H:i'); ?></span>
                      <a href="<?php echo osc_admin_base_url(true); ?>?page=items&action=item_edit&id=<?php echo $i['pk_i_id']; ?>">
                        <i class="fa fa-circle <?php echo $class; ?>" title="<?php echo osc_esc_html($title); ?>"></i>
                        <span><?php echo osc_highlight($i['s_title'], 30); ?></span>
                      </a>
                    </div>
                  <?php } ?>
                <?php } ?>
              </div>
            </div>
          <?php } ?>


          <?php if(!in_array('comments', $widgets_hidden)) { ?>
            <div class="widget-box" id="widget-comments" data-id="comments">
              <div class="widget-box-title">
                <h3>
                  <span><?php _e('Comments activity'); ?></span>
                  <?php osc_admin_widget_title_controls('comments'); ?>
                </h3>
              </div>
              <div class="widget-box-content" <?php echo (osc_admin_widget_collapsed('comments') ? 'style="display:none;"' : ''); ?>>
                <?php
                  $comments = osc_get_query_results(sprintf('SELECT * FROM %st_item_comment ORDER BY dt_pub_date DESC LIMIT 0, 5', DB_TABLE_PREFIX));
                  $comments_last_day = osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_item_comment WHERE dt_pub_date >= "%s"', DB_TABLE_PREFIX, date('Y-m-d H:i:s', strtotime('- 1 day'))));
                  $comments_last_week = osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_item_comment WHERE dt_pub_date >= "%s"', DB_TABLE_PREFIX, date('Y-m-d H:i:s', strtotime('- 7 day'))));
                  $comments_all = osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_item_comment', DB_TABLE_PREFIX));
                  $comments_pending = osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_item_comment WHERE b_active = 0 and b_spam = 0 and b_enabled = 1', DB_TABLE_PREFIX));
                ?>


                <div class="row st">
                  <?php echo sprintf(__('Published in last 24 hours: %s'), '<strong>' . $comments_last_day . '</strong>'); ?>
                </div>

                <div class="row st">
                  <?php echo sprintf(__('Published in last 7 days: %s'), '<strong>' . $comments_last_week . '</strong>'); ?>
                </div>

                <div class="row st">
                  <?php echo sprintf(__('Overall published: %s'), '<strong>' . $comments_all . '</strong>'); ?>
                </div>

                <div class="row st">
                  <a href="<?php echo osc_admin_base_url(true); ?>?page=comments"><?php echo sprintf(__('Pending validation: %s'), '<strong>' . $comments_pending . '</strong>'); ?></a>
                </div>

                <div class="row"></div>

                <h4><?php _e('Recently published'); ?></h4>

                <?php if(count($comments) <= 0) { ?>
                  <div class="empty"><?php _e('No comments has been found'); ?></div>
                <?php } else { ?>
                  <?php foreach($comments as $c) { ?>
                    <div class="row">
                      <?php
                        if($c['b_active'] == 1 && $c['b_enabled'] == 1 && $c['b_spam'] == 0) {
                          $title = __('Active');
                          $class = 'active';
                        } else if($c['b_active'] == 0) {
                          $title = __('Not validated');
                          $class = 'inactive';
                        } else if($c['b_spam'] == 1) {
                          $title = __('Spam');
                          $class = 'spam';
                        } else if($c['b_enabled'] == 0) {
                          $title = __('Blocked');
                          $class = 'blocked';
                        }
                      ?>

                      <span class="date"><?php echo osc_format_date($c['dt_pub_date'], 'd. M, H:i'); ?></span>
                      <a href="<?php echo osc_admin_base_url(true); ?>?page=comments&action=comment_edit&id=<?php echo $c['pk_i_id']; ?>">
                        <i class="fa fa-circle <?php echo $class; ?>" title="<?php echo osc_esc_html($title); ?>"></i>
                        <span><?php echo osc_highlight($c['s_title'], 30); ?></span>
                      </a>
                    </div>
                  <?php } ?>
                <?php } ?>
              </div>
            </div>
          <?php } ?>


          <?php if(!in_array('users', $widgets_hidden)) { ?>
            <div class="widget-box" id="widget-users" data-id="users">
              <div class="widget-box-title">
                <h3>
                  <span><?php _e('Users activity'); ?></span>
                  <?php osc_admin_widget_title_controls('users'); ?>
                </h3>
              </div>
              <div class="widget-box-content" <?php echo (osc_admin_widget_collapsed('users') ? 'style="display:none;"' : ''); ?>>
                <?php
                  $users = osc_get_query_results(sprintf('SELECT * FROM %st_user ORDER BY dt_reg_date DESC LIMIT 0, 5', DB_TABLE_PREFIX));
                  $users_last_day = osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_user WHERE dt_reg_date >= "%s"', DB_TABLE_PREFIX, date('Y-m-d H:i:s', strtotime('- 1 day'))));
                  $users_last_week = osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_user WHERE dt_reg_date >= "%s"', DB_TABLE_PREFIX, date('Y-m-d H:i:s', strtotime('- 7 day'))));
                  $users_all = osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_user', DB_TABLE_PREFIX));
                ?>


                <div class="row st">
                  <?php echo sprintf(__('Registered in last 24 hours: %s'), '<strong>' . $users_last_day . '</strong>'); ?>
                </div>

                <div class="row st">
                  <?php echo sprintf(__('Registered in last 7 days: %s'), '<strong>' . $users_last_week . '</strong>'); ?>
                </div>

                <div class="row st">
                  <?php echo sprintf(__('Overall registered: %s'), '<strong>' . $users_all . '</strong>'); ?>
                </div>

                <div class="row"></div>

                <h4><?php _e('Recently registered'); ?></h4>

                <?php if(count($users) <= 0) { ?>
                  <div class="empty"><?php _e('No users has been found'); ?></div>
                <?php } else { ?>
                  <?php foreach($users as $u) { ?>
                    <div class="row">
                      <?php
                        if($u['b_active'] == 1 && $u['b_enabled'] == 1) {
                          $title = __('Active');
                          $class = 'active';
                        } else if($u['b_active'] == 0) {
                          $title = __('Not validated');
                          $class = 'inactive';
                        } else if($u['b_enabled'] == 0) {
                          $title = __('Blocked');
                          $class = 'blocked';
                        }
                      ?>

                      <span class="date"><?php echo osc_format_date($u['dt_reg_date'], 'd. M, H:i'); ?></span>
                      <a href="<?php echo osc_admin_base_url(true); ?>?page=users&action=edit&id=<?php echo $u['pk_i_id']; ?>">
                        <i class="fa fa-circle <?php echo $class; ?>" title="<?php echo osc_esc_html($title); ?>"></i>
                        <span><?php echo osc_highlight($u['s_name'], 30); ?></span>
                      </a>
                    </div>
                  <?php } ?>
                <?php } ?>
              </div>
            </div>
          <?php } ?>


          <?php if(!in_array('banrules', $widgets_hidden)) { ?>
            <div class="widget-box" id="widget-banrules" data-id="banrules">
              <div class="widget-box-title">
                <h3>
                  <span><?php _e('Ban rules'); ?></span>
                  <?php osc_admin_widget_title_controls('banrules'); ?>
                </h3>
              </div>
              <div class="widget-box-content" <?php echo (osc_admin_widget_collapsed('banrules') ? 'style="display:none;"' : ''); ?>>
                <?php
                  $rules = osc_get_query_results(sprintf('SELECT * FROM %st_ban_rule ORDER BY pk_i_id DESC LIMIT 0, 5', DB_TABLE_PREFIX));
                  $rules_count = osc_get_count_query_data(sprintf('SELECT count(*) FROM %st_ban_rule', DB_TABLE_PREFIX));
                ?>

                <div class="row st" style="margin-bottom:15px;">
                  <a href="<?php echo osc_admin_base_url(true); ?>?page=users&action=ban"><?php echo sprintf(__('Total number of ban rules: %s'), '<strong>' . $rules_count . '</strong>'); ?></a>
                </div>

                <h4><?php _e('Latest rules'); ?></h4>

                <?php if(count($rules) <= 0) { ?>
                  <div class="empty"><?php _e('No ban rules has been found'); ?></div>
                <?php } else { ?>
                  <div class="widget-cont-wrap">
                    <?php foreach($rules as $r) { ?>
                      <div class="row">
                        <a class="rname" href="<?php echo osc_admin_base_url(true); ?>?page=users&action=edit_ban_rule&id=<?php echo $r['pk_i_id']; ?>" title="<?php echo osc_esc_html($r['s_name']); ?>"><?php echo $r['s_name']; ?></a>
                        <div class="l2"><?php echo $r['s_email']; ?> / <?php echo $r['s_ip']; ?></div>
                      </div>
                    <?php } ?>
                  </div>
                <?php } ?>
              </div>
            </div>
          <?php } ?>


          <?php if(!in_array('links', $widgets_hidden)) { ?>
            <div class="widget-box" id="widget-links" data-id="links">
              <div class="widget-box-title">
                <h3>
                  <span><?php _e('Useful links'); ?></span>
                  <?php osc_admin_widget_title_controls('links'); ?>
                </h3>
              </div>
              <div class="widget-box-content" <?php echo (osc_admin_widget_collapsed('links') ? 'style="display:none;"' : ''); ?>>
                <ul>
                  <li><a href="https://osclass-classifieds.com/download"><?php _e('Download osclass'); ?> <i class="fa fa-external-link"></i></a></li>
                  <li><a href="https://osclasspoint.com/"><?php _e('Market'); ?> <i class="fa fa-external-link"></i></a></li>
                  <li><a href="https://forums.osclasspoint.com/"><?php _e('Forums'); ?> <i class="fa fa-external-link"></i></a></li>
                  <li><a href="https://docs.osclass-classifieds.com/"><?php _e('Documentation'); ?> <i class="fa fa-external-link"></i></a></li>
                </ul>
              </div>
            </div>
          <?php } ?>

          <?php osc_run_hook('admin_dashboard_col1_bot'); ?>
          <?php osc_admin_dash_render_chart_widgets(1); ?>

        </div>
      </div>
    <?php } ?>

    <?php if(!in_array(2, $cols_hidden)) { ?>
      <div class="grid-row grid-<?php echo osc_admin_widget_column_width(2); ?>">
        <div class="row-wrapper">
          <?php osc_run_hook('admin_dashboard_col2_top'); ?>

          <?php osc_admin_dash_render_chart_widgets(2); ?>


          <?php if(!in_array('items-category', $widgets_hidden)) { ?>
            <div class="widget-box" id="widget-items-category" data-id="items-category">
              <div class="widget-box-title">
                <h3>
                  <span><?php _e('Listings by category'); ?></span>
                  <?php osc_admin_widget_title_controls('items-category'); ?>
                </h3>
              </div>

              <div class="widget-box-content" <?php echo (osc_admin_widget_collapsed('items-category') ? 'style="display:none;"' : ''); ?>>
                <?php
                  $countEvent = 1;
                  $numItemsPerCategory = osc_get_non_empty_categories();
                  $numItems = Item::newInstance()->count();
                ?>

                <?php if(!empty($numItemsPerCategory) ) { ?>
                <table class="table" cellpadding="0" cellspacing="0">
                  <tbody>
                  <?php
                  $even = false;
                  foreach($numItemsPerCategory as $c) {?>
                    <tr<?php if($even == true){ $even = false; echo ' class="even"'; } else { $even = true; } if($countEvent == 1){ echo ' class="table-first-row"';} ?>>
                      <td><a href="<?php echo osc_admin_base_url(true); ?>?page=items&amp;catId=<?php echo $c['pk_i_id']; ?>"><?php echo $c['s_name']; ?></a></td>
                      <td><?php echo $c['i_num_items'] . "&nbsp;" . ( ( $c['i_num_items'] == 1 ) ? __('Listing') : __('Listings') ); ?></td>
                    </tr>
                    <?php foreach($c['categories'] as $subc) {?>
                      <tr<?php if($even == true){ $even = false; echo ' class="even"'; } else { $even = true; } ?>>
                        <td class="children-cat"><a href="<?php echo osc_admin_base_url(true); ?>?page=items&amp;catId=<?php echo $subc['pk_i_id'];?>"><?php echo $subc['s_name']; ?></a></td>
                        <td><?php echo $subc['i_num_items'] . " " . ( ( $subc['i_num_items'] == 1 ) ? __('Listing') : __('Listings') ); ?></td>
                      </tr>
                    <?php
                    $countEvent++;
                    }
                    ?>
                  <?php
                  $countEvent++;
                  }
                  ?>
                  </tbody>
                </table>
                <?php } else { ?>
                  <?php _e("There aren't any uploaded listing yet"); ?>
                <?php } ?>
              </div>
            </div>
          <?php } ?>

          <?php osc_run_hook('admin_dashboard_col2_bot'); ?>

        </div>
      </div>
    <?php } ?>

    <?php if(!in_array(3, $cols_hidden)) { ?>
      <div class="grid-row grid-<?php echo osc_admin_widget_column_width(3); ?>">
        <div class="row-wrapper">
          <?php osc_run_hook('admin_dashboard_col3_top'); ?>
          <?php osc_admin_dash_render_chart_widgets(3); ?>

          <?php if(!in_array('blog', $widgets_hidden)) { ?>
            <div class="widget-box" id="widget-blog" data-id="blog">
              <div class="widget-box-title">
                <h3>
                  <span><?php _e('News on blog'); ?></span>
                  <?php osc_admin_widget_title_controls('blog'); ?>
                </h3>
              </div>
              <div class="widget-box-content" <?php echo (osc_admin_widget_collapsed('blog') ? 'style="display:none;"' : ''); ?>>
                <div class="widget-loading">
                  <img src="<?php echo osc_current_admin_theme_url(); ?>images/spinner-2x.gif" alt="<?php echo osc_esc_html(__('Loading...')); ?>"/>
                  <span><?php _e('Loading...'); ?></span>
                </div>
              </div>
            </div>

            <script>
              $.ajax({
                url : 'index.php?page=main&action=widget&file=blog.php',
                type: "POST",
                success: function(data) {
                  $('.widget-box#widget-blog .widget-box-content').html(data);
                },
                error: function() {
                  $('.widget-box#widget-blog .widget-box-content .widget-loading').html('<?php echo __('Unable to load data'); ?>');
                }
              });
            </script>
          <?php } ?>


          <?php if(!in_array('update', $widgets_hidden)) { ?>
            <div class="widget-box" id="widget-update" data-id="update">
              <div class="widget-box-title">
                <h3>
                  <span><?php _e('Osclass update'); ?></span>
                  <?php osc_admin_widget_title_controls('update'); ?>
                </h3>
              </div>
              <div class="widget-box-content" <?php echo (osc_admin_widget_collapsed('update') ? 'style="display:none;"' : ''); ?>>
                <div class="widget-loading">
                  <img src="<?php echo osc_current_admin_theme_url(); ?>images/spinner-2x.gif" alt="<?php echo osc_esc_html(__('Loading...')); ?>"/>
                  <span><?php _e('Loading...'); ?></span>
                </div>
              </div>
            </div>

            <script>
              $.ajax({
                url : 'index.php?page=main&action=widget&file=update.php',
                type: "POST",
                success: function(data) {
                  $('.widget-box#widget-update .widget-box-content').html(data);
                },
                error: function() {
                  $('.widget-box#widget-update .widget-box-content .widget-loading').html('<?php echo __('Unable to load data'); ?>');
                }
              });
            </script>
          <?php } ?>


          <?php if(!in_array('products', $widgets_hidden)) { ?>
            <div class="widget-box" id="widget-products" data-id="products">
              <div class="widget-box-title">
                <h3>
                  <span><?php _e('Latest products'); ?></span>
                  <?php osc_admin_widget_title_controls('products'); ?>
                </h3>
              </div>
              <div class="widget-box-content" <?php echo (osc_admin_widget_collapsed('products') ? 'style="display:none;"' : ''); ?>>
                <div class="widget-loading">
                  <img src="<?php echo osc_current_admin_theme_url(); ?>images/spinner-2x.gif" alt="<?php echo osc_esc_html(__('Loading...')); ?>"/>
                  <span><?php _e('Loading...'); ?></span>
                </div>
              </div>
            </div>

            <script>
              $.ajax({
                url : 'index.php?page=main&action=widget&file=products.php',
                type: "POST",
                success: function(data) {
                  $('.widget-box#widget-products .widget-box-content').html(data);
                },
                error: function() {
                  $('.widget-box#widget-products .widget-box-content .widget-loading').html('<?php echo __('Unable to load data'); ?>');
                }
              });
            </script>
          <?php } ?>


          <?php if(!in_array('product-updates', $widgets_hidden)) { ?>
            <div class="widget-box" id="widget-product-updates" data-id="product-updates">
              <div class="widget-box-title">
                <h3>
                  <span><?php _e('Product updates'); ?></span>
                  <?php osc_admin_widget_title_controls('product-updates'); ?>
                </h3>
              </div>
              <div class="widget-box-content" <?php echo (osc_admin_widget_collapsed('product-updates') ? 'style="display:none;"' : ''); ?>>
                <div class="widget-loading">
                  <img src="<?php echo osc_current_admin_theme_url(); ?>images/spinner-2x.gif" alt="<?php echo osc_esc_html(__('Loading...')); ?>"/>
                  <span><?php _e('Loading...'); ?></span>
                </div>
              </div>
            </div>

            <script>
              $.ajax({
                url : 'index.php?page=main&action=widget&file=product_updates.php',
                type: "POST",
                success: function(data) {
                  $('.widget-box#widget-product-updates .widget-box-content').html(data);
                },
                error: function() {
                  $('.widget-box#widget-product-updates .widget-box-content .widget-loading').html('<?php echo __('Unable to load data'); ?>');
                }
              });
            </script>
          <?php } ?>

          <?php osc_run_hook('admin_dashboard_col3_bot'); ?>

        </div>
      </div>
    <?php } ?>

    <div class="grid-row grid-100">
      <div class="row-wrapper"><?php osc_run_hook('admin_dashboard_bottom'); ?></div>
    </div>
  </div>
</div>

<div class="clear"></div>


<script>
$(document).ready(function() {
  var widgetMaximizeLabel = '<?php echo osc_esc_js(__('Maximize')); ?>';
  var widgetCloseLabel = '<?php echo osc_esc_js(__('Close')); ?>';

  $('#dashboard .widget-box-title .collapse').each(function() {
    if($(this).next('.maximize').length === 0) {
      $(this).after('<i class="fa fa-arrows-alt maximize" title="' + widgetMaximizeLabel + '"></i>');
    }
  });

  function oscDashboardWidgetChartsRedraw($widget) {
    if(!$widget || !$widget.length) {
      return;
    }
    if($widget.hasClass('widget-chart') && typeof oscDrawAdminStats === 'function') {
      oscDrawAdminStats();
    }
  }

  function oscDashboardWidgetRestore($widget) {
    if(!$widget || !$widget.length) {
      return;
    }

    $widget.removeClass('widget-maximized');
    $widget.find('.maximize').removeClass('fa-times').addClass('fa-arrows-alt').attr('title', widgetMaximizeLabel);

    var $ph = $widget.data('maximizePlaceholder');
    if($ph && $ph.length) {
      $ph.remove();
    }

    $widget.removeData('maximizePlaceholder');

    if($('#dashboard .widget-box.widget-maximized').length === 0) {
      $('body').removeClass('widget-maximized-active');
      $('#dashboard').removeClass('widget-maximized-active');
    }

    oscDashboardWidgetChartsRedraw($widget);
  }

  function oscDashboardWidgetMaximize($widget) {
    $('#dashboard .widget-box.widget-maximized').not($widget).each(function() {
      oscDashboardWidgetRestore($(this));
    });

    var $collapse = $widget.find('.collapse');
    if($collapse.hasClass('fa-caret-down')) {
      $collapse.removeClass('fa-caret-down').addClass('fa-caret-up');
      $widget.find('.widget-box-content').show();
    }

    var $ph = $('<div class="widget-maximize-placeholder"></div>').height($widget.outerHeight(true));
    $widget.data('maximizePlaceholder', $ph);
    $ph.insertBefore($widget);

    $('body').addClass('widget-maximized-active');
    $('#dashboard').addClass('widget-maximized-active');
    $widget.addClass('widget-maximized');
    $widget.find('.maximize').removeClass('fa-arrows-alt').addClass('fa-times').attr('title', widgetCloseLabel);

    setTimeout(function() {
      oscDashboardWidgetChartsRedraw($widget);
    }, 50);
  }

  $('body').on('click', '#dashboard .maximize', function(e) {
    e.preventDefault();
    var $widget = $(this).closest('.widget-box');

    if($widget.hasClass('widget-maximized')) {
      oscDashboardWidgetRestore($widget);
    } else {
      oscDashboardWidgetMaximize($widget);
    }

    return false;
  });

  $('body').on('click', '#dashboard .widget-maximized-cover', function() {
    $('#dashboard .widget-box.widget-maximized').each(function() {
      oscDashboardWidgetRestore($(this));
    });
  });

  $(document).on('keydown.widgetMaximize', function(e) {
    if(e.keyCode === 27 && $('#dashboard .widget-box.widget-maximized').length) {
      $('#dashboard .widget-box.widget-maximized').each(function() {
        oscDashboardWidgetRestore($(this));
      });
    }
  });

  $('body').on('click', '.collapse', function(e) {
    e.preventDefault();

    if($(this).closest('.widget-box').hasClass('widget-maximized')) {
      return false;
    }

    var collapse;
    var widgetId = $(this).closest('.widget-box').attr('data-id');

    if($(this).hasClass('fa-caret-up')) {
      $(this).removeClass('fa-caret-up').addClass('fa-caret-down');
      $(this).closest('.widget-box').find('.widget-box-content').slideUp(200);
      collapse = 1;

    } else {
      $(this).removeClass('fa-caret-down').addClass('fa-caret-up');
      $(this).closest('.widget-box').find('.widget-box-content').slideDown(200);
      collapse = 0;

      if($(this).closest('.widget-chart').length && typeof oscDrawAdminStats === 'function') {
        setTimeout(function() {
          oscDrawAdminStats();
        }, 210);
      }
    }

    $.ajax({
      url : 'index.php?page=ajax&action=admin_widget',
      type: 'POST',
      data: 'collapse='+collapse+'&widgetId='+widgetId,
      success: function(data) {
        //console.log(data);
      }
    });

    return false;
  });
});
</script>


<?php osc_current_admin_theme_path( 'parts/footer.php' );
