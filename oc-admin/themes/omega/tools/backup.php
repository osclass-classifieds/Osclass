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
$backup_files = __get('backup_files');
$backup_folder = __get('backup_folder');
if($backup_folder == '') {
  $backup_folder = osc_base_path();
}
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
              <input type="text" class="input-large" name="bck_dir" value="<?php echo osc_esc_html($backup_folder); ?>" />
              <div class="help-box">
                <?php _e("<strong>WARNING</strong>: If you don't specify a backup folder, the backup files will be created in the root of your Osclass installation."); ?>
                <br />
                <?php _e("This is the folder in which your backups will be created. We recommend that you choose a non-public path."); ?>
                <br />
                <?php _e("Backup names use pattern: osclass_db_backup_{timestamp}.sql and osclass_file_backup_{timestamp}.zip."); ?>
              </div>
            </div>
          </div>
          <div class="form-actions">
            <input type="button" id="backup_sql" onclick="javascript:submitForm(this.form, 'sql');" value="<?php echo osc_esc_html( __('Create database backup') ); ?>" class="btn btn-submit" />
            <input type="button" id="backup_sql_file" onclick="javascript:submitForm(this.form, 'sql_file');" value="<?php echo osc_esc_html( __('Download database backup') ); ?>" class="btn btn-submit" />
            <input type="button" id="backup_zip" onclick="javascript:submitForm(this.form, 'zip');" value="<?php echo osc_esc_html( __('Create file system backup') ); ?>" class="btn btn-submit" />
          </div>
        </div>
      </fieldset>
    </form>

    <fieldset>
      <div class="form-horizontal">
        <h2 class="render-title separate-top"><?php _e('Existing backups'); ?></h2>

        <?php if(is_array($backup_files) && count($backup_files) > 0) { ?>
          <table class="table" cellpadding="0" cellspacing="0">
            <thead>
              <tr>
                <th><?php _e('File name'); ?></th>
                <th><?php _e('Type'); ?></th>
                <th><?php _e('Size'); ?></th>
                <th><?php _e('Created'); ?></th>
                <th><?php _e('Action'); ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($backup_files as $bck) { ?>
                <tr>
                  <td><?php echo osc_esc_html($bck['name']); ?></td>
                  <td><?php echo osc_esc_html(($bck['type'] == 'db' ? __('Database') : __('Files'))); ?></td>
                  <td><?php echo osc_esc_html($bck['size_label']); ?></td>
                  <td><?php echo osc_esc_html(date('Y-m-d H:i:s', (int)$bck['modified'])); ?></td>
                  <td>
                    <a href="<?php echo osc_admin_base_url(true); ?>?page=tools&action=backup-download&file=<?php echo urlencode($bck['name']); ?>" class="btn" style="margin-right:5px;padding:4px 6px;height:auto;"><?php _e('Download'); ?></a>
                    <a href="<?php echo osc_admin_base_url(true); ?>?page=tools&action=backup-delete&<?php echo osc_csrf_token_url(); ?>&file=<?php echo urlencode($bck['name']); ?>" class="btn btn-red" style="padding:4px 6px;height:auto;" onclick="return confirm('<?php echo osc_esc_js(__('Are you sure you want to delete this backup file?')); ?>');"><?php _e('Delete'); ?></a>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        <?php } else { ?>
          <div class="help-box">
            <?php _e('No backups were found in selected backup folder.'); ?>
          </div>
        <?php } ?>
      </div>
    </fieldset>
  </div>
  <!-- /settings form -->
</div>
<?php osc_current_admin_theme_path( 'parts/footer.php' );
