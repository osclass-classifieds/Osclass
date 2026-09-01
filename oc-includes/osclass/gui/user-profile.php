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
 

if(osc_profile_img_users_enabled() == '1') {
  osc_enqueue_script('cropper');
  osc_enqueue_style('cropper', osc_assets_url('js/cropper/cropper.min.css'));
}



// meta tag robots
osc_add_hook('header','sigma_nofollow_construct');

sigma_add_body_class('user user-profile');
osc_add_hook('before-main','sidebar');
function sidebar(){
  osc_current_web_theme_path('user-sidebar.php');
}
osc_add_filter('meta_title_filter','custom_meta_title');
function custom_meta_title($data){
  return __('Update account', 'sigma');
}
osc_current_web_theme_path('header.php') ;
$osc_user = osc_user();
?>

<h1><?php _e('Update account', 'sigma'); ?></h1>
<?php UserForm::location_javascript(); ?>
<div class="form-container form-horizontal">
  <div class="resp-wrapper">
    <ul id="error_list"></ul>
    <form action="<?php echo osc_base_url(true); ?>" method="post">
      <input type="hidden" name="page" value="user" />
      <input type="hidden" name="action" value="profile_post" />

      <?php if(osc_profile_img_users_enabled()) { ?>
        <div class="control-group">
          <label class="control-label" for="name"><?php _e('Picture', 'sigma'); ?></label>
          <div class="controls">
            <div class="user-img">
              <div class="img-preview">
                <img src="<?php echo osc_user_profile_img_url(osc_logged_user_id()); ?>" alt="<?php echo osc_esc_html(osc_logged_user_name()); ?>"/>
              </div> 
            </div> 

            <div class="user-img-button">
              <?php UserForm::upload_profile_img(); ?>
            </div>
          </div>
        </div>
      <?php } ?>


      <div class="control-group">
        <label class="control-label" for="name"><?php _e('Name', 'sigma'); ?></label>
        <div class="controls">
          <?php UserForm::name_text(osc_user()); ?>
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="user_type"><?php _e('User type', 'sigma'); ?></label>
        <div class="controls">
          <?php UserForm::is_company_select(osc_user()); ?>
        </div>
      </div>

      <div class="control-group">
        <label class="control-label" for="phoneMobile"><?php _e('Mobile phone', 'sigma'); ?></label>
        <div class="controls">
          <?php UserForm::mobile_text(osc_user()); ?>
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="phoneLand"><?php _e('Land phone', 'sigma'); ?></label>
        <div class="controls">
          <?php UserForm::phone_land_text(osc_user()); ?>
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="country"><?php _e('Country', 'sigma'); ?></label>
        <div class="controls">
          <?php UserForm::country_select(osc_get_countries(), osc_user()); ?>
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="region"><?php _e('Region', 'sigma'); ?></label>
        <div class="controls">
          <?php UserForm::region_select(osc_get_regions(), osc_user()); ?>
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="city"><?php _e('City', 'sigma'); ?></label>
        <div class="controls">
          <?php UserForm::city_select(osc_get_cities(), osc_user()); ?>
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="city_area"><?php _e('City area', 'sigma'); ?></label>
        <div class="controls">
          <?php UserForm::city_area_text(osc_user()); ?>
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="city_area"><?php _e('ZIP', 'sigma'); ?></label>
        <div class="controls">
          <?php UserForm::zip_text(osc_user()); ?>
        </div>
      </div>
      <div class="control-group">
        <label class="control-label"l for="address"><?php _e('Address', 'sigma'); ?></label>
        <div class="controls">
          <?php UserForm::address_text(osc_user()); ?>
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="webSite"><?php _e('Website', 'sigma'); ?></label>
        <div class="controls">
          <?php UserForm::website_text(osc_user()); ?>
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="s_info"><?php _e('Description', 'sigma'); ?></label>
        <div class="controls">
          <?php UserForm::info_textarea('s_info', osc_locale_code(), @$osc_user['locale'][osc_locale_code()]['s_info']); ?>
        </div>
      </div>
      
      <?php osc_run_hook('user_profile_form', osc_user()); ?>

      <div class="control-group bts">
        <div class="controls">
          <button type="submit" class="btn btn-primary"><?php _e("Update", 'sigma');?></button>
        </div>
      </div>
      <div class="control-group">
        <div class="controls">
          <?php osc_run_hook('user_form', osc_user()); ?>
        </div>
      </div>
    </form>
  </div>
</div>

<?php osc_current_web_theme_path('footer.php') ; ?>