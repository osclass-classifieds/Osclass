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
osc_add_hook('header','sigma_follow_construct');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo sigma_default_direction()=='0' ? 'ltr': 'rtl'; ?>" lang="<?php echo str_replace('_', '-', osc_current_user_locale()); ?>">
  <head>
    <?php osc_current_web_theme_path('head.php') ; ?>
  </head>
<body class="subdomain-navigation sd-<?php echo osc_subdomain_type(); ?> <?php if(in_array(osc_subdomain_type(), array('country', 'language'))) { ?>sd-with-icon<?php } ?>">
<header>
  <div class="wrapper">
    <div class="box">
      <div id="logo">
        <?php echo logo_header(); ?>
      </div>
  </div>
</header>

<section>
  <div class="wrapper wrapper-flash flash2"><?php osc_show_flash_message(); ?></div>

  <div class="wrapper">
    <div class="m25"><?php _e('Join our community to buy and sell from each other everyday around the world.'); ?></div>
    <div><strong><?php _e('Please select preferred site:'); ?></strong></div>
    <?php echo osc_subdomain_links($with_images = true, $with_counts = true, $with_toplink = false, $limit = 1000, $min_item_count = 0); ?>
  </div>
</section>

<footer><?php _e('Copyright', 'sigma'); ?> &copy; <?php echo date('Y'); ?> <?php echo osc_page_title(); ?></footer>