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

<?php
  $js_lang = array(
    'delete' => __('Delete', 'sigma'),
    'cancel' => __('Cancel', 'sigma')
  );

  osc_enqueue_script('jquery');
  osc_enqueue_script('jquery-ui');
  osc_register_script('global-theme-js', osc_current_web_theme_js_url('global.js?v=' . date('YmdHis')), 'jquery');
  osc_register_script('delete-user-js', osc_current_web_theme_js_url('delete_user.js'), 'jquery-ui');
  osc_enqueue_script('global-theme-js');
  
  if(osc_is_publish_page() || osc_is_edit_page()) {
    osc_enqueue_script('tabber');
  }
  
  if (!defined('JQUERY_VERSION') || JQUERY_VERSION == '1') {
    $jquery_version = '1';
  } else {
    $jquery_version = JQUERY_VERSION;
  }  
?>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />

<title><?php echo meta_title() ; ?></title>
<meta name="title" content="<?php echo osc_esc_html(meta_title()); ?>" />
<?php if( meta_description() != '' ) { ?>
<meta name="description" content="<?php echo osc_esc_html(meta_description()); ?>" />
<?php } ?>
<?php if( meta_keywords() != '' ) { ?>
<meta name="keywords" content="<?php echo osc_esc_html(meta_keywords()); ?>" />
<?php } ?>
<?php if( osc_get_canonical() != '' ) { ?>
<!-- canonical -->
<link rel="canonical" href="<?php echo osc_get_canonical(); ?>"/>
<!-- /canonical -->
<?php } ?>
<meta http-equiv="Cache-Control" content="no-cache" />
<meta http-equiv="Expires" content="Fri, Jan 01 1970 00:00:00 GMT" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<!-- favicon -->
<link rel="apple-touch-icon" sizes="180x180" href="<?php echo osc_current_web_theme_url('favicon/apple-touch-icon.png'); ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo osc_current_web_theme_url('favicon/favicon-32x32.png'); ?>">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo osc_current_web_theme_url('favicon/favicon-16x16.png'); ?>">
<!-- /favicon -->

<script type="text/javascript">
  var sigma = window.sigma || {};
  sigma.base_url = '<?php echo osc_base_url(true); ?>';
  sigma.langs = <?php echo json_encode($js_lang); ?>;
  sigma.fancybox_prev = '<?php echo osc_esc_js( __('Previous image','sigma')) ?>';
  sigma.fancybox_next = '<?php echo osc_esc_js( __('Next image','sigma')) ?>';
  sigma.fancybox_closeBtn = '<?php echo osc_esc_js( __('Close','sigma')) ?>';
  var jqueryVersion = '<?php echo $jquery_version; ?>';
</script>
<?php 
osc_enqueue_style('font-nunito', 'https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400&family=Nunito:wght@400;700&display=swap');
osc_enqueue_style('style', osc_current_web_theme_url('css/style.css?v=' . date('YmdHis')));
osc_enqueue_style('responsive', osc_current_web_theme_url('css/responsive.css?v=' . date('YmdHis')));

if(sigma_is_rtl()) {
  osc_enqueue_style('rtl', osc_current_web_theme_url('css/rtl.css?v=' . date('YmdHis')));
}
?>
<?php osc_run_hook('header'); ?>