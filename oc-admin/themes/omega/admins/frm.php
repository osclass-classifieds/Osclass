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

osc_enqueue_script('jquery-validate');

$admin = __get("admin");
$admin['b_moderator'] = (isset($admin['b_moderator']) ? $admin['b_moderator'] : 0);

function customFrmText() {
  $admin = __get("admin");
  $return = array();
  if( isset($admin['pk_i_id']) ) {
    $return['admin_edit'] = true;
    $return['title']    = __('Edit admin');
    $return['action_frm'] = 'edit_post';
    $return['btn_text']   = __('Save');
  } else {
    $return['admin_edit']  = false;
    $return['title']    = __('Add admin');
    $return['action_frm'] = 'add_post';
    $return['btn_text']   = __('Add');
  }
  return $return;
}


function customPageHeader(){ 
  ?>
  <h1><?php _e('Admins'); ?></h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


$aux = customFrmText();

function customPageTitle($string) {
  $aux = customFrmText();
  return sprintf('%s - %s', $aux['title'], $string);
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php'); 
?>

<h2 class="render-title"><?php echo $aux['title']; ?></h2>

<!-- add/edit admin form -->
<div class="settings-user">
  <ul id="error_list"></ul>
  
  <form name="admin_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="action" value="<?php echo $aux['action_frm']; ?>" />
    <input type="hidden" name="page" value="admins" />
    
    <?php AdminForm::primary_input_hidden($admin); ?>
    <?php AdminForm::js_validation(); ?>
    
    <fieldset>
      <div class="form-horizontal">
        <div class="form-row">
          <div class="form-label"><?php _e('Name <em>(required)</em>'); ?></div>
          <div class="form-controls">
            <?php AdminForm::name_text($admin); ?>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-label"><?php _e('Username <em>(required)</em>'); ?></div>
          <div class="form-controls"><?php AdminForm::username_text($admin); ?></div>
        </div>
        
        <div class="form-row">
          <div class="form-label"><?php _e('E-mail <em>(required)</em>'); ?></div>
          <div class="form-controls"><?php AdminForm::email_text($admin); ?></div>
        </div>
        
        <?php if(!$aux['admin_edit'] || ($aux['admin_edit'] && Params::getParam('id')!= osc_logged_admin_id() && Params::getParam('id')!='')) { ?>
          <div class="form-row">
            <div class="form-label"><?php _e('Admin type <em>(required)</em>'); ?></div>
            <div class="form-controls">
              <?php AdminForm::type_select($admin); ?>
              <p class="help-inline"><em><?php _e('Administrators have total control over all aspects of your installation, while moderators are only allowed to moderate listings, comments and media files'); ?></em></p>
            </div>
          </div>
        <?php } ?>
        
        <?php if(!$aux['admin_edit'] || ($aux['admin_edit'] && Params::getParam('id')!= osc_logged_admin_id() && Params::getParam('id')!='')) { ?>
          <div class="form-row mod-access" <?php echo ($admin['b_moderator'] <> 1 ? 'style="display:none;"' : ''); ?>>
            <div class="form-label"><?php _e('Moderator extra access'); ?></div>
            <div class="form-controls">
              <?php AdminForm::moderator_access_textarea($admin); ?>
              <p class="help-inline">
                <em>
                  <?php _e('Allow moderator to access plugin pages. Enter plugin file path (from URL) or route (from URL). Separate by comma.'); ?><br/>
                  <?php _e('File examples'); ?>: instant_messenger/admin/configure.php, business_profile/admin/profiles.php<br/>
                  <?php _e('Route examples'); ?>: google_maps_settings, anr-admin-settings
                </em>
              </p>
            </div>
          </div>
        <?php } ?>
        
        <div class="form-row">
          <div class="form-label"><?php _e('New password'); ?></div>
          <div class="form-controls">
            <div>
            <?php AdminForm::password_text($admin); ?>
            </div>

          <?php if($aux['admin_edit']) { ?>
            <div class="newp">
              <?php AdminForm::check_password_text($admin); ?>
              <p class="help-inline"><em><?php _e('Type your new password again'); ?></em></p>
            </div>
          </div>

          <?php } ?>
        </div>

        <hr/>
        
        <div class="form-row">
          <div class="form-label"><?php _e('Your current password'); ?></div>
          <div class="form-controls">
            <?php AdminForm::old_password_text(); ?>
            <p class="help-inline"><em><?php _e('For security, type <b>your current password</b>'); ?></em></p>
          </div>
        </div>


        <?php osc_run_hook('admin_profile_form', $admin); ?>
        
        <div class="clear"></div>
        
        <div class="form-actions">
          <?php if( $aux['admin_edit'] ) { ?>
          <a href="javascript:history.go(-1)" class="btn"><?php _e('Cancel'); ?></a>
          <?php } ?>
          <input type="submit" value="<?php echo osc_esc_html($aux['btn_text']); ?>" class="btn btn-submit" />
        </div>
      </div>
    </fieldset>
  </form>
</div>

<script>
$(document).ready(function() {
  $('select[name="b_moderator"]').on('change', function() {
    if($(this).val() == '0') {
      $('.form-row.mod-access').slideUp(200);
      $('textarea[name="s_moderator_access"]').val('');
    } else {
      $('.form-row.mod-access').slideDown(200);
    }    
  });  
});
</script>
<!-- /add user form -->
<?php osc_current_admin_theme_path('parts/footer.php'); ?>