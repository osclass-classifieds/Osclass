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

$category = __get("category");

if(!isset($category['pk_i_id']) ) {
  $category = array();
  $category['pk_i_id'] = null;
}

$allowedTypesForSorting = Search::getAllowedTypesForSorting(); 
$sort_order = (osc_search_order() == 'dt_pub_date' ? '' : osc_search_order());
$sort_order_type = (osc_search_order() == 'dt_pub_date' ? '' : $allowedTypesForSorting[osc_search_order_type()])

?>
<?php osc_run_hook('search_sidebar_pre'); ?>

<div id="sidebar" class="fixed-layout">
  <div class="fixed-close"><i class="fas fa-times"></i></div>

  <div class="filters">
    <form action="<?php echo osc_base_url(true); ?>" method="get" class="nocsrf">
      <input type="hidden" name="page" value="search"/>
      <input type="hidden" name="sOrder" value="<?php echo $sort_order; ?>" />
      <input type="hidden" name="iOrderType" value="<?php echo $sort_order_type; ?>" />
      <?php foreach(osc_search_user() as $userId) { ?>
      <input type="hidden" name="sUser[]" value="<?php echo $userId; ?>"/>
      <?php } ?>
      
      <?php osc_run_hook('search_sidebar_top'); ?>
      
      <fieldset class="first">
        <h3><?php _e('Your search', 'sigma'); ?></h3>
        <div class="row">
          <input class="input-text" type="text" name="sPattern"  id="query" value="<?php echo osc_esc_html(osc_search_pattern()); ?>" />
        </div>
      </fieldset>
      <fieldset>
        <h3><?php _e('City', 'sigma'); ?></h3>
        <div class="row">
          <input class="input-text" type="hidden" id="sRegion" name="sRegion" value="<?php echo osc_esc_html(Params::getParam('sRegion')); ?>" />
          <input class="input-text" type="text" id="sCity" name="sCity" value="<?php echo osc_esc_html(osc_search_city()); ?>" />
        </div>
      </fieldset>
      <?php if( osc_images_enabled_at_items() ) { ?>
      <fieldset>
        <h3><?php _e('Show only', 'sigma') ; ?></h3>
        <div class="row picture">
          <input type="checkbox" name="bPic" id="withPicture" value="1" <?php echo (osc_search_has_pic() ? 'checked' : ''); ?> />
          <label for="withPicture"><?php _e('listings with pictures', 'sigma') ; ?></label>
        </div>
      </fieldset>
      <?php } ?>
      <?php if( osc_price_enabled_at_items() ) { ?>
      <fieldset>
        <div class="row price-slice">
          <h3><?php _e('Price', 'sigma') ; ?></h3>

          <div class="left">
            <span><?php _e('Min', 'sigma') ; ?>.</span>
            <input class="input-text" type="text" id="priceMin" name="sPriceMin" value="<?php echo osc_esc_html(osc_search_price_min()); ?>" size="6" maxlength="6" />
          </div>

          <div class="right">
            <span><?php _e('Max', 'sigma') ; ?>.</span>
            <input class="input-text" type="text" id="priceMax" name="sPriceMax" value="<?php echo osc_esc_html(osc_search_price_max()); ?>" size="6" maxlength="6" />
          </div>
        </div>
      </fieldset>
      <?php } ?>

      <div class="plugin-hooks"><?php
        if(osc_search_category_id()) {
          osc_run_hook('search_form', osc_search_category_id()) ;
        } else {
          osc_run_hook('search_form') ;
        }
        ?></div>
        
      <?php
      $aCategories = osc_search_category();
      foreach($aCategories as $cat_id) { ?>
        <input type="hidden" name="sCategory[]" value="<?php echo osc_esc_html($cat_id); ?>"/>
      <?php } ?>
      
      <?php osc_run_hook('search_sidebar_bottom'); ?>
      
      <div class="actions">
        <button type="submit" class="btn btn-primary"><?php _e('Apply', 'sigma'); ?></button>
      </div>
    </form>
  </div>

  <?php osc_run_hook('search_sidebar_after'); ?>
  <?php osc_alert_form(); ?>

  <div class="refine">
    <h3><?php _e('Refine category', 'sigma') ; ?></h3>
    <?php sigma_sidebar_category_search($category['pk_i_id']); ?>
  </div>

</div>