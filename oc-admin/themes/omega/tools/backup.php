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
function customHead(){
  ?>
  <script type="text/javascript">
    function submitForm(frm, type) {
      frm.action.value = 'backup-' + type;
      frm.submit();
    }
  </script>
  <?php
}

osc_add_hook('admin_header','customHead', 10);


function render_offset(){
  return 'row-offset';
}


function addHelp() {
  echo '<p>' . __("Save a backup of all of your site's information: listings, users and configuration. You can save a backup on your server or on your computer.") . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader(){ 
  ?>
  <h1><?php _e('Tools'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Backup - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path( 'parts/header.php' ); 
?>

<div id="backup-setting">
  <!-- settings form -->
  <div id="backup-settings">
    <h2 class="render-title"><?php _e('Backup'); ?></h2>
    <form id="backup_form" name="backup_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
      <input type="hidden" name="page" value="tools" />
      <input type="hidden" name="action" value="" />
      <fieldset>
        <div class="form-horizontal">
          <div class="form-row">
            <div class="form-label"><?php _e('Backup folder'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-large" name="bck_dir" value="<?php echo osc_esc_html(osc_base_path()); ?>" />
              <div class="help-box">
                <?php _e("<strong>WARNING</strong>: If you don't specify a backup folder, the backup files will be created in the root of your Osclass installation."); ?>
                <br />
                <?php _e("This is the folder in which your backups will be created. We recommend that you choose a non-public path."); ?>
              </div>
            </div>
          </div>
          <div class="form-actions">
            <input type="button" id="backup_sql" onclick="javascript:submitForm(this.form, 'sql');" value="<?php echo osc_esc_html( __('Backup SQL (store on server)') ); ?>" class="btn btn-submit" />
            <input type="button" id="backup_sql_file" onclick="javascript:submitForm(this.form, 'sql_file');" value="<?php echo osc_esc_html( __('Backup SQL (download file)') ); ?>" class="btn btn-submit" />
            <input type="button" id="backup_zip" onclick="javascript:submitForm(this.form, 'zip');" value="<?php echo osc_esc_html( __('Backup files (store on server)') ); ?>" class="btn btn-submit" />
          </div>
        </div>
      </fieldset>
    </form>
  </div>
  <!-- /settings form -->
</div>
<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>