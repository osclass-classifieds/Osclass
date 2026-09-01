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


//customize Head
function customHead() { }
osc_add_hook('admin_header','customHead', 10);

function addHelp() {
  echo '<p>' . __("Browse configuration information of your server, database, osclass and PHP") . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader(){ 
  ?> 
  <h1><?php _e('Configuration information'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Tools - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path( 'parts/header.php' );


$php = phpinfo2array(); 

$conn = @new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if (!mysqli_connect_errno()) {
  $mysql_version = $conn->server_info;
}
$conn->close();

$ftheme = WebThemes::newInstance()->loadThemeInfo(WebThemes::newInstance()->getCurrentTheme());
$btheme = AdminThemes::newInstance()->loadThemeInfo(AdminThemes::newInstance()->getCurrentTheme());

$details = false;

if(Params::getParam('details') == 1) {
  $details = true;
}
?>

<div class="grid-row grid-50">
  <div class="row-wrapper">
    <div class="widget-box cinfo">
      <div class="widget-box-title"><h3><i class="fa fa-info-circle"></i> <?php _e('Configuration information'); ?></h3></div>
      <div class="widget-box-content">
        <p><?php _e('This information can be provided when you report an issue via support ticket or on forums.'); ?></p>
        
        <?php if($details == false) { ?>
          <p><?php _e('Detail information are not included as it takes more resources to compute. Click on button bellow to include also details about your website.'); ?></p>
          <p><a style="float:none;display:inline-block;" href="<?php echo osc_admin_base_url(true); ?>?page=tools&action=info&details=1" class="btn btn-submit"><?php _e('Run with details'); ?></a></p>
        <?php } ?>
      </div>
    </div>

    <div class="widget-box cinfo">
      <div class="widget-box-title"><h3><i class="fa fa-info-circle"></i> <?php _e('Server information'); ?></h3></div>
      <div class="widget-box-content">
        <p><strong><?php _e('Server information'); ?>:</strong> <span><?php echo @$php['phpinfo']['System']; ?></span></p>
        <p><strong><?php _e('Server software version'); ?>:</strong> <span><?php echo @$php['apache2handler']['Apache Version']; ?> (<?php echo @$php['apache2handler']['Apache API Version']; ?>)</span></p>
        <p><strong><?php _e('PHP version'); ?>:</strong> <span><?php echo phpversion(); ?></span></p>
        <p><strong><?php _e('Max execution time'); ?>:</strong> <span><?php echo ini_get('max_execution_time'); ?>s</span></p>
        <p><strong><?php _e('Upload max file size'); ?>:</strong> <span><?php echo ini_get('upload_max_filesize'); ?></span></p>
        <p><strong><?php _e('Post max file size'); ?>:</strong> <span><?php echo ini_get('post_max_size'); ?></span></p>
        <p><strong><?php _e('Memory limit'); ?>:</strong> <span><?php echo ini_get('memory_limit'); ?></span></p>
        <p><strong><?php _e('Max input vars'); ?>:</strong> <span><?php echo ini_get("max_input_vars"); ?></span></p>
        <p><strong><?php _e('Allow URL Fopen'); ?>:</strong> <span><?php echo (ini_get('allow_url_fopen') ? __('Enabled') : __('Disabled')); ?></span></p>

      </div>
    </div>

    <div class="widget-box cinfo">
      <div class="widget-box-title"><h3><i class="fa fa-info-circle"></i> <?php _e('Database information'); ?></h3></div>
      <div class="widget-box-content">
        <p><strong><?php _e('MySQL version'); ?>:</strong> <span><?php echo $mysql_version; ?></span></p>
        <p><strong><?php _e('MySQL server'); ?>:</strong> <span><?php echo DB_HOST; ?></span></p>
        <p><strong><?php _e('MySQL name'); ?>:</strong> <span><?php echo DB_NAME; ?></span></p>
        <p><strong><?php _e('MySQL user'); ?>:</strong> <span><?php echo DB_USER; ?></span></p>
        <p><strong><?php _e('Tables prefix'); ?>:</strong> <span><?php echo DB_TABLE_PREFIX; ?></span></p>
      </div>
    </div>
    
    <div class="widget-box cinfo">
      <div class="widget-box-title"><h3><i class="fa fa-info-circle"></i> <?php _e('Size information'); ?></h3></div>
      <div class="widget-box-content">
        <?php if($details) { ?>
          <p><strong><?php _e('All osclass files'); ?>:</strong> <span><?php echo osc_dir_size(osc_base_path()); ?></span></p>
          <p><strong title="/oc-includes/"><?php _e('Includes'); ?>:</strong> <span><?php echo osc_dir_size(osc_base_path() . OC_INCLUDES_FOLDER . '/'); ?></span></p>
          <p><strong title="/oc-admin/"><?php _e('Admin'); ?>:</strong> <span><?php echo osc_dir_size(osc_base_path() . OC_ADMIN_FOLDER . '/'); ?></span></p>
          <p><strong title="/oc-content/"><?php _e('Content'); ?>:</strong> <span><?php echo osc_dir_size(osc_base_path() . OC_CONTENT_FOLDER . '/'); ?></span></p>
          <p><strong title="/oc-content/downloads/"><?php _e('Downloads'); ?>:</strong> <span><?php echo osc_dir_size(osc_base_path() . OC_CONTENT_FOLDER . '/downloads/'); ?></span></p>
          <p><strong title="/oc-content/languages/"><?php _e('Languages'); ?>:</strong> <span><?php echo osc_dir_size(osc_base_path() . OC_CONTENT_FOLDER . '/languages/'); ?></span></p>
          <p><strong title="/oc-content/plugins/"><?php _e('Plugins'); ?>:</strong> <span><?php echo osc_dir_size(osc_base_path() . OC_CONTENT_FOLDER . '/plugins/'); ?></span></p>
          <p><strong title="/oc-content/themes/"><?php _e('Themes'); ?>:</strong> <span><?php echo osc_dir_size(osc_base_path() . OC_CONTENT_FOLDER . '/themes/'); ?></span></p>
          <p><strong title="/oc-content/uploads/"><?php _e('Uploads'); ?>:</strong> <span><?php echo osc_dir_size(osc_base_path() . OC_CONTENT_FOLDER . '/uploads/'); ?></span></p>
          <p><strong title="/oc-content/uploads/minify/"><?php _e('Optimization'); ?>:</strong> <span><?php echo osc_dir_size(osc_base_path() . OC_CONTENT_FOLDER . '/uploads/minify/'); ?></span></p>
        <?php } else { ?>
          <p><?php _e('Not available in summary, run configuration information with details'); ?></p>
        <?php } ?>
      </div>
    </div>
    
    <div class="widget-box cinfo">
      <div class="widget-box-title"><h3><i class="fa fa-info-circle"></i> <?php _e('Subdomains setup'); ?></h3></div>
      <div class="widget-box-content">
        <p><strong><?php _e('Status'); ?>:</strong> <span><?php echo osc_subdomain_enabled() ? __('Enabled') : __('Disabled'); ?></span></p>
        <p><strong><?php _e('Type'); ?>:</strong> <span><?php echo osc_subdomain_type_name(); ?></span></p>
        <p><strong><?php _e('Count of available subdomains'); ?>:</strong> <span><?php echo count(osc_get_subdomains(99999, 0)); ?></span></p>
        <p><strong><?php _e('Is subdomain?'); ?>:</strong> <span><?php echo osc_is_subdomain() ? __('Yes') : __('No'); ?></span></p>
        <p><strong><?php _e('Base host'); ?>:</strong> <span><?php echo (osc_subdomain_host() <> '' ? osc_subdomain_host() : '-'); ?></span></p>
        <p><strong><?php _e('Landing page'); ?>:</strong> <span><?php echo osc_subdomain_landing_enabled() ? __('Enabled') : __('Disabled'); ?></span></p>
        <p><strong><?php _e('Automatic redirect'); ?>:</strong> <span><?php echo osc_subdomain_redirect_enabled() ? __('Enabled') : __('Disabled'); ?></span></p>
        <p><strong><?php _e('Restricted countries'); ?>:</strong> <span><?php echo (osc_subdomain_restricted_ids() <> '' ? osc_subdomain_restricted_ids() : '-'); ?></span></p>
        <p>
          <strong><?php _e('List of first 20 subdomains'); ?>:</strong>
          <span>
            <?php 
              $sub_list = osc_get_subdomains(20, 0); 
              
              $i = 1;
              if(count($sub_list) > 0) {
                foreach($sub_list as $sd) {
                  if($i != 1) { 
                    echo ', ';
                  }
                  
                  echo '<a href="' . $sd['url'] . '">' . $sd['name'] . '</a>';
                  $i++;
                }
              }
              
              if(count($sub_list) == 20) {
                echo '...';
              } else if(count($sub_list) == 0) {
                echo '-';
              }
            ?>
          </span>
        </p>

      </div>
    </div>
  </div>
</div>


<div class="grid-row grid-50">
  <div class="row-wrapper">
    <div class="widget-box cinfo">
      <div class="widget-box-title"><h3><i class="fa fa-info-circle"></i> <?php _e('Osclass information'); ?></h3></div>
      <div class="widget-box-content">
        <?php
          $theme_front = $ftheme['name'];
          if(@$ftheme['theme_uri'] <> '') {
            $theme_front = '<a href="' . $ftheme['theme_uri'] . '" target="_blank">' . $theme_front . '</a>';
          }
          $theme_front = $theme_front . ' (' . $ftheme['int_name'] . ')';

          $theme_back = $btheme['name'];
          if(@$btheme['theme_uri'] <> '') {
            $theme_back = '<a href="' . $btheme['theme_uri'] . '" target="_blank">' . $theme_back . '</a>';
          }

          $theme_back = $theme_back . ' (' . $btheme['int_name'] . ')';
        ?>

        <p><strong><?php _e('Osclass version'); ?>:</strong> <span><?php echo OSCLASS_VERSION; ?></span></p>
        <p><strong><?php _e('Web URL'); ?>:</strong> <span><?php echo osc_base_url(); ?></span></p>
        <p><strong><?php _e('Web path'); ?>:</strong> <span><?php echo osc_base_path(); ?></span></p>
        <p><strong><?php _e('Oc-admin URL'); ?>:</strong> <span><?php echo osc_admin_base_url(); ?></span></p>
        <p><strong><?php _e('Last update check'); ?>:</strong> <span><?php echo date('Y-m-d H:i:s', osc_get_preference('last_version_check', 'osclass')); ?></span></p>
        <p><strong><?php _e('Using dev cron'); ?>:</strong> <span><?php echo osc_get_preference('auto_cron', 'osclass') == 1 ? __('Yes') : __('No'); ?></span></p>
        <p><strong><?php _e('Current front theme'); ?>:</strong> <span><?php echo $theme_front; ?></span></p>
        <p><strong><?php _e('Current oc-admin theme'); ?>:</strong> <span><?php echo $theme_back; ?></span></p>
        <p><strong><?php _e('PHP error log'); ?>:</strong> <span><?php if(defined('OSC_DEBUG') && OSC_DEBUG) { _e('Enabled'); } else { _e('Disabled'); }; ?></span></p>
        <p><strong><?php _e('PHP errors output to file'); ?>:</strong> <span><?php if(defined('OSC_DEBUG_LOG') && OSC_DEBUG_LOG) { _e('Enabled'); } else { _e('Disabled'); }; ?></span></p>
        <p><strong><?php _e('Database debug mode'); ?>:</strong> <span><?php if(defined('OSC_DEBUG_DB') && OSC_DEBUG_DB) { _e('Enabled'); } else { _e('Disabled'); }; ?></span></p>
        <p><strong><?php _e('Cache'); ?>:</strong> <span><?php echo defined('OSC_CACHE') ? __('Enabled') . ' (' . OSC_CACHE . ')' : __('Disabled'); ?></span></p>
      </div>
    </div>
    
    
    <?php
      $plugins_all = count(Plugins::listAll());
      $plugins_active = count(Plugins::listEnabled());
      $plugins_disabled = count(Plugins::listInstalled()) - $plugins_active; 
      $plugins_notinstalled = $plugins_all - $plugins_active - $plugins_disabled;
      
      $themes_all = count(WebThemes::newInstance()->getListThemes());
    ?>
    
    <div class="widget-box cinfo">
      <div class="widget-box-title"><h3><i class="fa fa-info-circle"></i> <?php _e('Themes & Plugins information'); ?></h3></div>
      <div class="widget-box-content">
        <p><strong><?php _e('All plugins'); ?>:</strong> <span><?php echo $plugins_all; ?> <?php _e('plugins'); ?></span></p>
        <p><strong><?php _e('Enabled plugins'); ?>:</strong> <span><?php echo $plugins_active; ?> <?php _e('plugins'); ?></span></p>
        <p><strong><?php _e('Disabled plugins'); ?>:</strong> <span><?php echo $plugins_disabled; ?> <?php _e('plugins'); ?></span></p>
        <p><strong><?php _e('Not installed plugins'); ?>:</strong> <span><?php echo $plugins_notinstalled; ?> <?php _e('plugins'); ?></span></p>
        <p><strong><?php _e('All themes'); ?>:</strong> <span><?php echo $themes_all; ?> <?php _e('themes'); ?></span></p>
        <p><strong><?php _e('Inactive themes'); ?>:</strong> <span><?php echo $themes_all - 1; ?> <?php _e('themes'); ?></span></p>
      </div>
    </div>

    <div class="widget-box cinfo">
      <div class="widget-box-title"><h3><i class="fa fa-info-circle"></i> <?php _e('Your information'); ?></h3></div>
      <div class="widget-box-content">
        <p><strong><?php _e('Your web browser'); ?>:</strong> <span><?php echo $_SERVER['HTTP_USER_AGENT']; ?></span></p>
      </div>
    </div>
    
    <div class="widget-box cinfo">
      <div class="widget-box-title"><h3><i class="fa fa-info-circle"></i> <?php _e('Permissions information'); ?></h3></div>
      <div class="widget-box-content">
        <?php if($details) { ?>
          <p><?php _e('In case folder is not readable or writtable, it may be issue when updating or upgrading osclass, themes or plugins.'); ?></p>
              
          <?php $chmod = osc_dir_chmod(osc_base_path()); ?>
          <p>
            <strong><?php _e('Folders those are not readable'); ?>:</strong><br/>
            <span>
              <?php if(empty($chmod['not_readable'])) { ?>
                <i class="fa fa-check-circle-o"></i> <?php _e('All folders are readable'); ?>
              <?php } else { ?>
                <?php echo implode('<br/>', $chmod['not_readable']); ?>
              <?php } ?>
            </span>
          </p>
                  
          <p>
            <strong><?php _e('Folders those are not writtable'); ?>:</strong><br/>
            <span>
              <?php if(empty($chmod['not_writtable'])) { ?>
                <i class="fa fa-check-circle-o"></i> <?php _e('All folders are writtable'); ?>
              <?php } else { ?>
                <?php echo implode('<br/>', $chmod['not_writtable']); ?>
              <?php } ?>
            </span>
          </p>
        <?php } else { ?>
          <p><?php _e('Not available in summary, run configuration information with details'); ?></p>
        <?php } ?>
      </div>
    </div>
    
  </div>
</div>

<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>