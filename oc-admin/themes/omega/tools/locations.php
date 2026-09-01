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


$all    = osc_get_preference('location_todo');
$worktodo   = LocationsTmp::newInstance()->count();

function render_offset(){
  return 'row-offset';
}


function customHead() {
  $all = osc_get_preference('location_todo');
  if( $all == '' ) $all = 0;
  $worktodo   = LocationsTmp::newInstance()->count();
  ?>
  <script type="text/javascript">
    function reload() {
      window.location = '<?php echo osc_admin_base_url(true).'?page=tools&action=locations'; ?>';
    }

    function ajax_() {
      $.ajax({
        type: "POST",
        url: '<?php echo osc_admin_base_url(true)?>?page=ajax&action=location_stats&<?php echo osc_csrf_token_url(); ?>',
        dataType: 'json',
        success: function(data) {
          if(data.status=='done') {
            $('span#percent').html(100);
          }else{
            var pending = data.pending;
            var all = <?php echo osc_esc_js($all);?>;
            var percent = parseInt( ((all-pending)*100) / all );
            $('span#percent').html(percent);
            ajax_();
          }
        }
      });
    }

    $(document).ready(function(){
      if(<?php echo $worktodo;?>> 0) {
        ajax_();
      }
    });
  </script>
  <?php
}

osc_add_hook('admin_header','customHead', 10);


function customPageHeader(){ 
  ?>
  <h1><?php _e('Tools'); ?></h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Location stats - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path( 'parts/header.php' ); 
?>

<div id="locations-stats-setting">
  <!-- settings form -->
  <div id="backup-settings">
    <h2 class="render-title"><?php _e('Locations stats'); ?></h2>
    <?php if($worktodo > 0) { ?>
    <p>
      <span id="percent">0</span> % <?php _e("Complete"); ?>
    </p>
    <?php } ?>
    <p>
      <?php _e('You can recalculate your location stats. This is useful if you upgrade from versions older than Osclass 2.4'); ?>.
    </p>
    <form action="<?php echo osc_admin_base_url(true); ?>" method="post">
      <input type="hidden" name="action" value="locations_post" />
      <input type="hidden" name="page" value="tools" />
      <fieldset>
        <div class="form-horizontal">
          <div class="form-actions no-padding">
            <input id="button_save" type="submit" value="<?php echo osc_esc_html( __('Calculate location stats')); ?>" class="btn btn-submit" />
          </div>
        </div>
      </fieldset>
    </form>
  </div>
  <!-- /settings form -->
</div>
<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>