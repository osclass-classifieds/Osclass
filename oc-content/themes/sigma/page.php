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
osc_add_hook('header', osc_static_page_indexable() == 0 ? 'sigma_nofollow_construct' : 'sigma_follow_construct');

sigma_add_body_class('page');
osc_current_web_theme_path('header.php') ;
?>

<h1><?php echo osc_static_page_title(); ?></h1>
<?php echo osc_static_page_text(); ?>
<?php osc_current_web_theme_path('footer.php') ; ?>