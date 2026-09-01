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

$rule = __get('rule');

function customFrmText(){
  $rule = __get('rule');
  $return = array();

  if( isset($rule['pk_i_id']) ) {
    $return['edit']       = true;
    $return['title']      = __('Edit rule');
    $return['action_frm'] = 'edit_ban_rule_post';
    $return['btn_text']   = __('Update rule');
  } else {
    $return['edit']       = false;
    $return['title']      = __('Add new ban rule');
    $return['action_frm'] = 'create_ban_rule_post';
    $return['btn_text']   = __('Add new ban rule');
  }
  return $return;
}


function customPageHeader(){ 
?>
  <h1><?php _e('Ban rules'); ?></h1>
<?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  $aux = customFrmText();
  return sprintf('%s - %s', $aux['title'], $string);
}

osc_add_filter('admin_title', 'customPageTitle');


//customize Head
function customHead() {}
osc_add_hook('admin_header','customHead', 10);

$aux  = customFrmText();

osc_current_admin_theme_path('parts/header.php'); 
?>

<div id="user-settings">
<h2 class="render-title"><?php echo $aux['title']; ?></h2>
  <div class="settings-user">
    <ul id="error_list"></ul>
    
    <form name="register" action="<?php echo osc_admin_base_url(true); ?>" method="post">
      <input type="hidden" name="page" value="users" />
      <input type="hidden" name="action" value="<?php echo $aux['action_frm']; ?>" />
      <?php BanRuleForm::primary_input_hidden($rule); ?>
      
      <fieldset>
        <div class="form-horizontal">
          <div class="form-row">
            <div class="form-label"><?php _e('Ban name / Reason'); ?></div>
            <div class="form-controls">
              <?php BanRuleForm::name_text($rule); ?>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('IP rule'); ?></div>
            <div class="form-controls">
              <?php BanRuleForm::ip_text($rule); ?>
              <span class="help-box"><?php _e('Example: 192.168.10-20.*, 192.*.*.*, 192.*.20.*, 192.*.10-20.1, 192.168.10.1'); ?></span>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('E-mail rule'); ?></div>
            <div class="form-controls">
              <?php BanRuleForm::email_text($rule); ?>
              <span class="help-box"><?php _e('Example: *@badsite.com, *@subdomain.badsite.com, *@*badsite.com, *badsite.*, *badsite*, *.com'); ?></span>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Expire date'); ?></div>
            <div class="form-controls">
              <?php BanRuleForm::expire_date_text($rule); ?>
              <span class="help-box"><?php _e('After this date, ban rule will not be used. Keep blank for never-expiring ban rules.'); ?></span>
            </div>
          </div>
          
          <div class="clear"></div>
          
          <div class="form-actions">
            <input type="submit" value="<?php echo osc_esc_html($aux['btn_text']); ?>" class="btn btn-submit" />
          </div>
        </div>
      </fieldset>
    </form>
  </div>
</div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>