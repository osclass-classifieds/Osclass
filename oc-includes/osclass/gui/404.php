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
sigma_add_body_class('error not-found');
osc_current_web_theme_path('header.php') ;
?>

<div class="flashmessage-404">
  <div class="error404">
    <h1><?php _e('404', 'sigma'); ?></h1>
    <h2><?php _e('OOPS! Page Not Found!', 'sigma'); ?></h2>
    <h3><?php _e('Either something get wrong or the page doesn\'t exist anymore.', 'sigma'); ?></h3>

    <a href="<?php echo osc_base_url(); ?>" class="btn btn-secondary"><?php _e('Take me home', 'sigma'); ?></a>
  </div>
</div>
<?php osc_current_web_theme_path('footer.php') ; ?>