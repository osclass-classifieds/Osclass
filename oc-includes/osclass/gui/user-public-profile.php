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

  $user = osc_user();

  // meta tag robots
  osc_add_hook('header','sigma_follow_construct');

  $address = '';
  if(osc_user_address()!='') {
    if(osc_user_city_area()!='') {
      $address = osc_user_address().", ".osc_user_city_area();
    } else {
      $address = osc_user_address();
    }
  } else {
    $address = osc_user_city_area();
  }
  $location_array = array();
  if(trim(osc_user_city()." ".osc_user_zip())!='') {
    $location_array[] = trim(osc_user_city()." ".osc_user_zip());
  }
  if(osc_user_region()!='') {
    $location_array[] = osc_user_region();
  }
  if(osc_user_country()!='') {
    $location_array[] = osc_user_country();
  }
  $location = implode(", ", $location_array);
  unset($location_array);

  osc_enqueue_script('jquery-validate');

  sigma_add_body_class('user-public-profile');
  osc_add_hook('after-main','sidebar');
  function sidebar(){
    osc_current_web_theme_path('user-public-sidebar.php');
  }


  View::newInstance()->_exportVariableToView('user', $user);
  osc_current_web_theme_path('header.php');
  View::newInstance()->_exportVariableToView('user', $user); 
?>
  
<div id="item-content">
  <div class="user-card">
    <?php if(osc_profile_img_users_enabled()) { ?>
      <p class="user-img">
        <img src="<?php echo osc_user_profile_img_url(osc_user_id()); ?>" alt="<?php echo osc_esc_html(osc_user_name()); ?>"/>
      </p>
    <?php } ?>

    <ul id="user_data">
      <li class="name"><?php echo osc_user_name(); ?></li>
      <?php if( osc_user_website() !== '' ) { ?>
      <li class="website"><a href="<?php echo osc_user_website(); ?>"><?php echo osc_user_website(); ?></a></li>
      <?php } ?>
      <?php if( $address !== '' ) { ?>
      <li class="adress"><?php printf(__('<strong>Address:</strong> %1$s'), $address); ?></li>
      <?php } ?>
      <?php if( $location !== '' ) { ?>
      <li class="location"><?php printf(__('<strong>Location:</strong> %1$s'), $location); ?></li>
      <?php } ?>
    </ul>
  </div>
  <?php if( osc_user_info() !== '' ) { ?>
  <h2><?php _e('User description', 'sigma'); ?></h2>
  <?php } ?>
  <?php echo nl2br(osc_user_info()); ?>
  <?php if( osc_count_items() > 0 ) { ?>
  <div class="similar_ads">
    <h2><?php _e('Latest listings', 'sigma'); ?></h2>
    <?php osc_current_web_theme_path('loop.php'); ?>
    <div class="paginate"><?php echo osc_pagination_items(); ?></div>
    <div class="clear"></div>
  </div>
  <?php } ?>
</div>
<?php osc_current_web_theme_path('footer.php') ; ?>