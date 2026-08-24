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
osc_add_hook('header','sigma_nofollow_construct');

sigma_add_body_class('user user-items');

function sidebar(){
  osc_current_web_theme_path('user-sidebar.php');
}
osc_add_hook('before-main','sidebar');

osc_current_web_theme_path('header.php') ;

$listClass = 'listing-list';
$buttonClass = '';
if(Params::getParam('ShowAs') == 'gallery'){
  $listClass = 'listing-grid';
  $buttonClass = 'active';
}
?>
<div class="list-header">
  <?php osc_run_hook('user_items_top'); ?>
  
  <h1><?php _e('My listings', 'sigma'); ?></h1>
  
  <form name="user-items-search" action="<?php echo osc_base_url(true); ?>" method="get" class="user-items-search-form nocsrf">
    <input type="hidden" name="page" value="user"/>
    <input type="hidden" name="action" value="items"/>

    <?php osc_run_hook('user_items_search_form_top'); ?>
    
    <div class="control-group">
      <label class="control-label" for="sItemType"><?php _e('Item type', 'sigma'); ?></label>
      
      <div class="controls">
        <?php UserForm::search_item_type_select(); ?>
      </div>
    </div>
    
    <div class="control-group">
      <label class="control-label" for="sPattern"><?php _e('Keyword', 'sigma'); ?></label>
      
      <div class="controls">
        <?php UserForm::search_pattern_text(); ?>
      </div>
    </div>
    
    <div class="control-group">
      <label class="control-label" for="sCategory"><?php _e('Category', 'sigma'); ?></label>
      
      <div class="controls">
        <?php UserForm::search_category_select(); ?>
      </div>
    </div>

    
    <?php osc_run_hook('user_items_search_form_bottom'); ?>
    
    <div class="actions">
      <button type="submit" class="btn btn-primary"><?php _e('Apply', 'sigma'); ?></button>
    </div>
  </form>
  
  <?php if(osc_count_items() == 0) { ?>
    <p class="empty" ><?php _e('No listings found', 'sigma'); ?></p>
  <?php } else { ?>

    <?php
      // print_r(Params::getParamsAsArray()); 
    
      View::newInstance()->_exportVariableToView("listClass",$listClass);
      View::newInstance()->_exportVariableToView("listAdmin", true);
      osc_current_web_theme_path('loop.php');
    ?>
    
    <div class="clear"></div>
    
    <?php osc_run_hook('user_items_bottom'); ?>
    
    <div class="paginate">
      <?php echo osc_pagination_items(); ?>
    </div>
  <?php } ?>
</div>

<?php osc_current_web_theme_path('footer.php') ; ?>