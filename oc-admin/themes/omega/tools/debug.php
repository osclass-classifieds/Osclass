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
  echo '<p>' . __("Check your debug/error log settings and debug output.") . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader(){ 
  ?> 
  <h1><?php _e('Debug/Error log'); ?>
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

$log_files = __get('log_files');
$file = Params::getParam('log_file');

$log_file = '';
$log_file_url = '';
$limit_lines = 2000;
$log_exists = false;

if($file != '' && file_exists(CONTENT_PATH . $file)) {
  $log_exists = true;
  $log_file_url = osc_base_url() . OC_CONTENT_FOLDER . '/' . $file;
  
  $i = 0;
  $log_file = '';
  $handle = fopen(CONTENT_PATH . $file, "r");
  while(!feof($handle)){
    $log_file .= fgets($handle);
    $i++;
    
    if($i > $limit_lines) {
      $log_file .= '<br/>' . sprintf(__('First %s lines of %s shown, download log file to see full content'), $limit_lines, $file);
      break;
    }
  }

  fclose($handle);
}
?>

<div class="grid-row grid-30">
  <div class="row-wrapper">
    <div class="widget-box debg smr">
      <div class="widget-box-title"><h3><i class="fa fa-gear"></i> <?php _e('Debug/Error log settings'); ?></h3></div>
      <div class="widget-box-content">
        <p><strong><?php _e('PHP error log'); ?>:</strong> <?php if(defined('OSC_DEBUG') && OSC_DEBUG) { echo '<span class="enabled"><i class="fa fa-check"></i>' . __('Enabled') . '</span>'; } else { echo '<span class="disabled"><i class="fa fa-times"></i>' . __('Disabled') . '</span>'; }; ?></span></p>
        <p><strong><?php _e('PHP errors output to file'); ?>:</strong> <?php if(defined('OSC_DEBUG_LOG') && OSC_DEBUG_LOG) { echo '<span class="enabled"><i class="fa fa-check"></i>' . __('Enabled') . '</span>'; } else { echo '<span class="disabled"><i class="fa fa-times"></i>' . __('Disabled') . '</span>'; }; ?></span></p>
        <p><strong><?php _e('Database debug mode'); ?>:</strong> <?php if(defined('OSC_DEBUG_DB') && OSC_DEBUG_DB) { echo '<span class="enabled"><i class="fa fa-check"></i>' . __('Enabled') . '</span>'; } else { echo '<span class="disabled"><i class="fa fa-times"></i>' . __('Disabled') . '</span>'; }; ?></span></p>
      </div>
    </div>
    
    <div class="widget-box debg cnf">
      <div class="widget-box-title"><h3><i class="fa fa-gear"></i> <?php _e('Enable logs'); ?></h3></div>
      <div class="widget-box-content">
        <p><?php _e('You can enable error/debug logs in config.php (located in root directory). Please check Osclass Docs for more details, few samples are here:'); ?>:</p>
        <p><strong class="code">define('OSC_DEBUG', true);</strong> <span class="bubble">// <?php _e('Enable PHP debugging'); ?></span></p>
        <p><strong class="code">define('OSC_DEBUG_LOG', true);</strong> <span class="bubble">// <?php _e('Stores PHP debug into log file'); ?></span></p>
        <p><strong class="code">define('OSC_DEBUG_DB', true);</strong> <span class="bubble">// <?php _e('Enable DB debugging'); ?></span></p>
        <p><strong class="code">define('OSC_DEBUG_DB_LOG', true);</strong></p>
        <p><strong class="code">define('OSC_DEBUG_CACHE', true);</strong></p>
        <p><strong class="code">define('OSC_DEBUG_DB_AJAX_PRINT', true);</strong></p>
        <p><strong class="code">define('PHPMAILER_DEBUG_LEVEL', 4);</strong></p>
      </div>
    </div>

  </div>
</div>

<div class="grid-row grid-70">
  <div class="row-wrapper">
    <div class="widget-box debg">
      <div class="widget-box-title">
        <h3>
          <span style="float:left;">
            <i class="fa fa-database"></i> <?php _e('Log file'); ?>:
          </span>
          
          <form method="get" action="<?php echo osc_admin_base_url(true); ?>" class="inline nocsrf">
            <input type="hidden" name="page" value="tools"/>
            <input type="hidden" name="action" value="debug"/>

            <select name="log_file" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();">
              <option value=""><?php _e('Select log file'); ?></option>
              
              <?php if(is_array($log_files) && count($log_files) > 0) { ?>
                <?php foreach($log_files as $lfile) { ?>
                  <?php 
                    $fname = basename($lfile);
                    $fsize = round(filesize($lfile) / 1024, 2) . 'kb';
                  ?>
                  
                  <option value="<?php echo $fname; ?>" <?php if($fname == $file) { ?>selected="selected"<?php } ?>><?php echo $fname . ' (' . $fsize . ')'; ?></option>
                <?php } ?>
              <?php } ?>
            </select>
          </form>
        
          <?php if($log_file_url <> '') { ?>
            <a href="<?php echo $log_file_url; ?>" target="_blank" class="btn float-right" style="margin-top: -4px;"><?php _e('Download'); ?></a>
            <a href="<?php echo osc_admin_base_url(true); ?>?page=tools&action=debug_delete&log_file=<?php echo $file; ?>" class="btn float-right" style="margin-top: -4px;"><?php _e('Remove'); ?></a>
          <?php } ?>
        </h3>
      </div>
      <div class="widget-box-content dbgfl">
        <?php if($file == '') { ?>
          <div class="empty"><?php _e('No log file selected'); ?></div>
        
        <?php } else if(trim($log_file) == '') { ?>
          <div class="empty"><?php _e('Log file is empty'); ?></div>

        <?php } else if($log_exists) { ?>
          <div class="dbgfl">
            <div class="debug-file"><?php echo $log_file; ?></div>
          </div>
          
        <?php } else { ?>
          <div class="empty"><?php echo sprintf(__('Debug log file "%s" does not exists, it should be located in %s folder.'), $file, '<strong>' . OC_CONTENT_FOLDER . '</strong>'); ?></div>
        <?php } ?>
      </div>
    </div>
    
  </div>
</div>
<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>