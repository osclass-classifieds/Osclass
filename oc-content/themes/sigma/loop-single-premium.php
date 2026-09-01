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

?>

<?php $size = explode('x', osc_thumbnail_dimensions()); ?>
<li class="<?php osc_run_hook("highlight_class"); ?>listing-card <?php echo $class . ' premium'; ?>">
  <?php osc_run_hook('item_loop_top', true); ?>

  <?php if( osc_images_enabled_at_items() ) { ?>
    <?php if(osc_count_premium_resources()) { ?>
      <a class="listing-thumb" href="<?php echo osc_premium_url() ; ?>" title="<?php echo osc_esc_html(osc_premium_title()) ; ?>"><img src="<?php echo osc_resource_thumbnail_url(); ?>" title="" alt="<?php echo osc_esc_html(osc_premium_title()) ; ?>" width="<?php echo $size[0]; ?>" height="<?php echo $size[1]; ?>"></a>
    <?php } else { ?>
      <a class="listing-thumb" href="<?php echo osc_premium_url() ; ?>" title="<?php echo osc_esc_html(osc_premium_title()) ; ?>"><img src="<?php echo osc_current_web_theme_url('images/no_photo.gif'); ?>" title="" alt="<?php echo osc_esc_html(osc_premium_title()) ; ?>" width="<?php echo $size[0]; ?>" height="<?php echo $size[1]; ?>"></a>
    <?php } ?>
  <?php } ?>

  <?php 
    if(function_exists('fi_make_favorite')) { 
      echo fi_make_favorite();
    }
  ?>
  
  <div class="listing-detail">
    <div class="listing-cell">
      <div class="listing-data">
        <div class="listing-basicinfo">
          <a href="<?php echo osc_premium_url() ; ?>" class="title" title="<?php echo osc_esc_html(osc_premium_title()) ; ?>"><?php echo osc_premium_title() ; ?></a>
          <?php osc_run_hook('item_loop_title', true); ?>
          
          <div class="listing-attributes">
            <?php if(osc_price_enabled_at_items()  && osc_premium_category_price_enabled()) { ?><span class="currency-value"><?php echo osc_format_price(osc_premium_price(),osc_premium_currency_symbol()); ?></span><?php } ?>

            <div class="listing-details">
              <span class="category"><?php echo osc_premium_category() ; ?></span>
              <span class="location"><?php echo osc_premium_city(); ?><?php if( osc_premium_region() != '' ) { ?> (<?php echo osc_premium_region(); ?>)<?php } ?></span> 
              <span class="date"><?php echo osc_format_date(osc_premium_pub_date()); ?></span>
            </div>
          </div>

          <div class="desc"><?php echo osc_highlight( osc_premium_description() ,250) ; ?></div>
          <?php osc_run_hook('item_loop_description', true); ?>
        </div>
      </div>
    </div>
  </div>
  
  <?php osc_run_hook('item_loop_bottom', true); ?>
</li>