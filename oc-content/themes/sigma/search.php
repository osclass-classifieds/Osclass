<?php
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


// meta tag robots
// if( osc_count_items() == 0 || stripos($_SERVER['REQUEST_URI'], 'search') ) {
  // osc_add_hook('header','sigma_nofollow_construct');
// } else {
  // osc_add_hook('header','sigma_follow_construct');
// }

osc_add_hook('header','sigma_follow_construct');

sigma_add_body_class('search');
$listClass = '';
$buttonClass = 'list';
if(osc_search_show_as() == 'gallery'){
  $listClass = 'listing-grid';
  $buttonClass = 'grid';
}

osc_add_hook('before-main','sidebar');

function sidebar(){
  osc_current_web_theme_path('search-sidebar.php');
}
osc_add_hook('footer','autocompleteCity');

function autocompleteCity(){ ?>
<script type="text/javascript">
$(function() {
function log( message ) {
    $( "<div/>" ).text( message ).prependTo( "#log" );
    $( "#log" ).attr( "scrollTop", 0 );
}

$( "#sCity" ).autocomplete({
    source: "<?php echo osc_base_url(true); ?>?page=ajax&action=location",
    minLength: 2,
    select: function( event, ui ) {
    $("#sRegion").attr("value", ui.item.region);
    log( ui.item ?
        "<?php echo osc_esc_html( __('Selected', 'sigma') ); ?>: " + ui.item.value + " aka " + ui.item.id :
        "<?php echo osc_esc_html( __('Nothing selected, input was', 'sigma') ); ?> " + this.value );
    }
});
});
</script>
<?php
}

?>
  <?php osc_current_web_theme_path('header.php'); ?>
  
  <div class="list-header">
    <div class="resp-wrapper">
      <?php osc_run_hook('search_items_top'); ?>

      <?php if(trim(search_title()) <> '') { ?>
        <h1><?php echo search_title(); ?></h1>
      <?php } ?>
      
      <?php osc_run_hook('search_items_filter'); ?>

      <?php if(osc_count_items() == 0) { ?>
        <p class="empty" ><?php printf(__('There are no results matching "%s"', 'sigma'), osc_search_pattern()) ; ?></p>
      <?php } else { ?>
      <span class="counter-search"><?php
        $search_number = sigma_search_number();
        printf(__('%d - %d of %d listings', 'sigma'), $search_number['from'], $search_number['to'], $search_number['of']);
      ?></span>
      <div class="actions">
        <a href="#" class="resp-toogle show-filters-btn btn btn-secondary"><?php _e('Show filters','sigma'); ?></a>

        <!--   START sort by     -->
        <span class="see_by btn btn-secondary">
          <span><?php _e('Sort by', 'sigma'); ?>:</span>
          <?php
          $orders = osc_list_orders(); 
          $current = '';
          foreach($orders as $label => $params) {
            $orderType = ($params['iOrderType'] == 'asc') ? '0' : '1';

            if(osc_search_order() == $params['sOrder'] && osc_search_order_type() == $orderType) {
              $current = $label;
            }
          }
          ?>
          <label><?php echo $current; ?> <i class="fa fa-angle-down"></i></label>
          <?php $i = 0; ?>
          <ul>
            <?php
            foreach($orders as $label => $params) {
              $orderType = ($params['iOrderType'] == 'asc') ? '0' : '1'; ?>
              <?php if(osc_search_order() == $params['sOrder'] && osc_search_order_type() == $orderType) { ?>
                <li><a class="current" href="<?php echo osc_esc_html(osc_update_search_url($params)); ?>"><?php echo $label; ?></a></li>
              <?php } else { ?>
                <li><a href="<?php echo osc_esc_html(osc_update_search_url($params)); ?>"><?php echo $label; ?></a></li>
              <?php } ?>
              <?php $i++; ?>
            <?php } ?>
          </ul>
        </span>
        <!--   END sort by     -->

        <span class="doublebutton">
           <a href="<?php echo osc_esc_html(osc_update_search_url(array('sShowAs'=> 'list'))); ?>" class="list-button btn btn-secondary <?php echo ($buttonClass == 'list' ? 'active' : ''); ?>" data-class-toggle="listing-list" data-destination="#listing-card-list"><i class="fas fa-bars"></i></a>
           <a href="<?php echo osc_esc_html(osc_update_search_url(array('sShowAs'=> 'gallery'))); ?>" class="grid-button btn btn-secondary <?php echo ($buttonClass == 'grid' ? 'active' : ''); ?>" data-class-toggle="listing-grid" data-destination="#listing-card-list"><i class="fas fa-border-all"></i></a>
        </span>
      </div>

      <?php } ?>
      
      <?php osc_run_hook('search_items_bottom'); ?>
    </div>
  </div>
  
  <?php
    $i = 0;
    osc_get_premiums();
    if(osc_count_premiums() > 0) {
    echo '<h2>'.__('Premium listings','sigma').'</h2>';
    View::newInstance()->_exportVariableToView("listType", 'premiums');
    View::newInstance()->_exportVariableToView("listClass",$listClass.' premium-list');
    osc_current_web_theme_path('loop.php');
    echo '<div style="clear:both;"></div><br/>';
    }
  ?>
  
  <?php if(osc_count_items() > 0) {
    echo '<h2>'.__('Listings','sigma').'</h2>';
    View::newInstance()->_exportVariableToView("listType", 'items');
    View::newInstance()->_exportVariableToView("listClass",$listClass);
    osc_current_web_theme_path('loop.php');
  ?>

   <div class="clear"></div>
    <?php
    if(osc_rewrite_enabled()){
    $footerLinks = osc_search_footer_links();
    $c = 0;
    if(count($footerLinks)>0) {
    ?>
    <div id="related-searches">
      <h5><?php _e('Other searches that may interest you','sigma'); ?></h5>
      
      <?php foreach($footerLinks as $f) { ?>
        <?php View::newInstance()->_exportVariableToView('footer_link', $f); ?>
        <?php if($c >= 12) break; ?>
        <a href="<?php echo osc_footer_link_url(); ?>"><?php echo osc_footer_link_title(); ?></a>
        <?php $c++; ?>
      <?php } ?>
    </div>
    <?php }
    } ?>
   <div class="paginate" >
      <?php echo osc_search_pagination(); ?>
   </div>
   <?php } ?>
<?php osc_current_web_theme_path('footer.php') ; ?>