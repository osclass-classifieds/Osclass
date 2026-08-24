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


function addHelp() {
  echo '<p>' . __("Manage the options related to users on your site. Here, you can decide if users must register or if email confirmation is necessary, among other options.") . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader(){ 
  ?>
  <h1><?php _e('Users'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('User Settings - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php'); 
?>

<!-- settings form -->
<h2 class="render-title"><?php _e('User Settings'); ?></h2>
<form action="<?php echo osc_admin_base_url(true); ?>" method="post">
  <input type="hidden" name="page" value="users" />
  <input type="hidden" name="action" value="settings_post" />
  <fieldset>
    <div class="form-horizontal">
      <div class="form-row" style="margin-bottom:0px;">
        <div class="form-label"><?php _e('Settings'); ?></div>
        
        <div class="form-controls">
          <label id="enabled_users" class="form-label-checkbox">
            <input type="checkbox" id="enabled_users" name="enabled_users" <?php echo (osc_users_enabled() ? 'checked="checked"' : ''); ?> value="1" />
            <?php _e('Users enabled'); ?>
          </label>
        </div>
      </div>

      <div class="form-row" style="margin-bottom:0px;">
        <div class="form-label">&nbsp;</div>
        
        <div class="form-controls separate-top-medium">
          <label id="enabled_user_registration">
            <input type="checkbox" id="enabled_user_registration" name="enabled_user_registration" <?php echo (osc_user_registration_enabled() ? 'checked="checked"' : ''); ?> value="1" />
            <?php _e('Anyone can register'); ?>
          </label>
        </div>
      </div>

      <div class="form-row">
        <div class="form-label">&nbsp;</div>
        
        <div class="form-controls separate-top-medium">
          <label id="enabled_user_validation">
            <input type="checkbox" id="enabled_user_validation" name="enabled_user_validation" <?php echo (osc_user_validation_enabled() ? 'checked="checked"' : ''); ?> value="1" />
            <?php _e('Users need to validate their account'); ?>
          </label>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-label"><?php _e('Admin notifications'); ?></div>
        <div class="form-controls">
          <label id="notify_new_user" class="form-label-checkbox">
            <input type="checkbox" id="notify_new_user" name="notify_new_user" <?php echo (osc_notify_new_user() ? 'checked="checked"' : ''); ?> value="1" />
            <?php _e('When a new user is registered'); ?>
          </label>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-label"><?php _e('Admin toolbar'); ?></div>
        <div class="form-controls">
          <label id="admin_toolbar_front" class="form-label-checkbox">
            <input type="checkbox" id="admin_toolbar_front" name="admin_toolbar_front" <?php echo (osc_admin_toolbar_front_enabled() ? 'checked="checked"' : ''); ?> value="1" />
            <?php _e('Enable admin toolbar in front page'); ?>
          </label> 
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-label"><?php _e('TinyMCE'); ?></div>
        <div class="form-controls">
          <label id="enabled_tinymce_users" class="form-label-checkbox">
            <input type="checkbox" id="enabled_tinymce_users" name="enabled_tinymce_users" <?php echo (osc_tinymce_users_enabled() ? 'checked="checked"' : ''); ?> value="1" />
            <?php _e('Enable TinyMCE on user profile - additional information textarea in front/back office'); ?>
          </label> 
        </div>
      </div>

      <div class="form-row">
        <div class="form-label"><?php _e('Username generator'); ?></div>
        <div class="form-controls">
          <select name="username_generator">
            <option value="ID" <?php if(osc_username_generator() == 'ID' || osc_username_generator() == '') { ?>selected="selected"<?php } ?>><?php _e('Use user ID'); ?></option>
            <option value="SLUG" <?php if(osc_username_generator() == 'SLUG') { ?>selected="selected"<?php } ?>><?php _e('Create slug from name'); ?></option>
          </select>
          
          <span class="help-box"><?php _e('Generator is not used, when user enters username in registration or profile form (when available).'); ?></span>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-label"><?php _e('Username blacklist'); ?></div>
        <div class="form-controls">
          <label id="username_blacklist" class="form-label-input">
            <input type="text" id="username_blacklist" name="username_blacklist" value="<?php echo osc_esc_html(osc_username_blacklist()); ?>" />
            <span class="help-box"><?php _e('List of terms not allowed in usernames, separated by commas'); ?></span>
          </label>
        </div>
      </div>

      <div class="form-row">
        <div class="form-label"><?php _e('Public profile page'); ?></div>
        <div class="form-controls">
          <select name="user_public_profile_enabled">
            <option value="ALL" <?php if(osc_user_public_profile_enabled() == 'ALL' || osc_username_generator() == '') { ?>selected="selected"<?php } ?>><?php _e('Enabled for all registered users'); ?></option>
            <option value="COMPANY" <?php if(osc_user_public_profile_enabled() == 'COMPANY') { ?>selected="selected"<?php } ?>><?php _e('Enabled for users registered as company only'); ?></option>
          </select>
          
          <span class="help-box"><?php _e('Specify when user public profile URL is enabled, otherwise public profile page shows 404.'); ?></span>
        </div>
      </div>

      <div class="form-row">
        <div class="form-label"><?php _e('Public profile min. items'); ?></div>
        <div class="form-controls">
          <label id="user_public_profile_min_items" class="form-label-input">
            <input type="number" class="input-small" min="0" step="1" id="user_public_profile_min_items" name="user_public_profile_min_items" value="<?php echo osc_esc_html(osc_user_public_profile_min_items()); ?>" />
            <div class="inpt-desc"><?php _e('items'); ?></div>
            
            <span class="help-box"><?php _e('Public profile page will be enabled just in case user has at least defined number of items. Default: 0'); ?></span>
          </label>
        </div>
      </div>
      

      <h2 class="render-title separate-top"><?php _e('Profile picture settings'); ?></h2>
      
      <div class="form-row">
        <div class="form-label"><?php _e('Profile pictures'); ?></div>
        <div class="form-controls">
          <label id="enable_profile_img" class="form-label-checkbox">
            <input type="checkbox" id="enable_profile_img" name="enable_profile_img" <?php echo (osc_profile_img_users_enabled() ? 'checked="checked"' : ''); ?> value="1" />
            <?php _e('Enable users to upload their profile picture'); ?>
          </label> 
          
          <span class="help-box"><?php _e('Required scripts and styles on user profile page will be automatically loaded.'); ?></span>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-label"><?php _e('Image uploader library'); ?></div>
        <div class="form-controls">
          <select name="profile_picture_library">
            <option value="" <?php if(osc_profile_picture_library() == '') { ?>selected="selected"<?php } ?>><?php _e('Cropper.js'); ?></option>
            <option value="UPPY" <?php if(osc_profile_picture_library() == 'UPPY') { ?>selected="selected"<?php } ?>><?php _e('Uppy.io'); ?></option>
          </select>
          
          <span class="help-box"><?php _e('Select library you want to use for profile pictures. Uppy.io is more advanced.'); ?></span>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-label"><?php _e('Profile picture size'); ?></div>
        <div class="form-controls">
          <input type="text" name="dimProfileImg" value="<?php echo osc_esc_html(osc_profile_img_dimensions()); ?>" required />
          <span class="help-box"><?php _e('The size listed below determine the optimal dimensions in pixels to use when uploading a profile picture. Format: <b>Width</b> x <b>Height</b>.'); ?></span>
        </div>
      </div>
      
      
      <div class="form-actions">
        <input type="submit" id="save_changes" value="<?php echo osc_esc_html(__('Save changes')); ?>" class="btn btn-submit" />
      </div>
    </div>
  </fieldset>
</form>
<!-- /settings form -->
<?php osc_current_admin_theme_path('parts/footer.php'); ?>