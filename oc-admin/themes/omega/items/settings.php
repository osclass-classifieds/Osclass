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
  echo '<p>' . __('Modify the general settings for your listings. Decide if users have to register in order to publish something, the number of pictures allowed for each listing, etc.') . '</p>';
}

osc_add_hook('help_box','addHelp');


//customize Head
function customHead(){ 
  ?>
  <script type="text/javascript">
  $(document).ready(function() {
    $('input[name="moderate_items"]').bind('change', function() {
      if( $(this).is(':checked')) {
        $(".num-moderated-items").show();
        $('input[name="num_moderate_items"]').val(0);
      } else {
        $('input[name="logged_user_item_validation"]').prop('checked', false);
        $('.num-moderated-items').hide();
      }
    });
    if(!$('input[name="moderate_items"]').is(':checked')) {
      $('.num-moderated-items').hide();
    }
  });
  </script>
  <?php
}
osc_add_hook('admin_header','customHead', 10);


function render_offset(){
  return 'row-offset';
}

function customPageHeader(){ 
  ?>
  <h1><?php _e('Listings'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Listing Settings - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path( 'parts/header.php'); 
?>

<div id="general-setting">
  <!-- settings form -->
  <div id="item-settings">
    <h2 class="render-title"><?php _e('Listing Settings'); ?></h2>
      <form action="<?php echo osc_admin_base_url(true); ?>" method="post">
        <input type="hidden" name="page" value="items" />
        <input type="hidden" name="action" value="settings_post" />
        <fieldset>
          <div class="form-horizontal">
            <div class="form-row">
              <div class="form-label"> <?php _e('Settings'); ?></div>
              <div class="form-controls">
                <div class="form-label-checkbox">
                  <label>
                    <input type="checkbox" <?php echo ( osc_reg_user_post() ? 'checked="checked"' : ''); ?> name="reg_user_post" value="1" />
                    <?php _e('Only logged in users can post listings'); ?>
                  </label>
                </div>
                
                <div>
                  <?php printf( __('An user has to wait %s seconds between each listing added'), '<input type="text" class="input-small" name="items_wait_time" value="' . osc_items_wait_time() . '" />'); ?>
                  <div class="help-box">
                    <?php _e('If the value is set to zero, there is no wait period'); ?>
                  </div>
                </div>
                
                <div class="separate-top-medium">
                  <label>
                    <input type="checkbox" <?php echo ( ( osc_moderate_items() == -1) ? '' : 'checked="checked"'); ?> name="moderate_items" value="1" />
                    <?php _e('Users have to validate their listings'); ?>
                  </label>
                </div>
                
                <div class="separate-top-medium">
                  <label>
                    <input type="checkbox" <?php echo ( osc_can_deactivate_items() ? 'checked="checked"' : ''); ?> name="can_deactivate_items" value="1" />
                    <?php _e('Users can disable / de-activate listing'); ?>
                  </label>
                </div>
                
                <div class="separate-top-medium">
                  <label>
                    <input type="checkbox" <?php echo ( osc_item_mark_disable() ? 'checked="checked"' : ''); ?> name="item_mark_disable" value="1" />
                    <?php _e('Disable mark/report listing feature'); ?>
                  </label>
                </div>
                
                <div class="num-moderated-items" >
                  <div>
                    <?php printf( __("After %s validated listings the user doesn't need to validate the listings any more"), '<input type="text" class="input-small" name="num_moderate_items" value="' . ( ( osc_moderate_items() == -1) ? '' : osc_moderate_items()) . '" />'); ?>
                    <div class="help-box">
                      <?php _e('If the value is zero, it means that each listing must be validated'); ?>
                    </div>
                  </div>
                  <div class="separate-top-medium">
                    <label>
                      <input type="checkbox" <?php echo ( osc_logged_user_item_validation() ? 'checked="checked"' : ''); ?> name="logged_user_item_validation" value="1" />
                      <?php _e("Logged in users don't need to validate their listings"); ?>
                    </label>
                  </div>
                </div>
                
                <div class="separate-top-medium">
                  <label>
                    <input type="checkbox" <?php echo ( ( osc_recaptcha_items_enabled() == '0') ? '' : 'checked="checked"'); ?> name="enabled_recaptcha_items" value="1" />
                    <?php _e('Show reCAPTCHA in add/edit listing form'); ?>
                  </label>
                  <div class="help-box"><?php _e('<strong>Remember</strong> that you must configure reCAPTCHA first'); ?></div>
                </div>
                
                <div>
                  <label>
                    <input type="checkbox" <?php echo ( ( osc_tinymce_items_enabled() == '0') ? '' : 'checked="checked"'); ?> name="enabled_tinymce_items" value="1" />
                    <?php _e('Enable TinyMCE on add/edit listing form in front/back office'); ?>
                  </label>
                  <div class="help-box"><?php _e('TinyMCE enable to format description of listings'); ?></div>
                </div>
              </div>
            </div>
            

            <div class="form-row">
              <div class="form-label"> <?php _e('Renewal'); ?></div>
              <div class="form-controls">
                <div class="form-label-checkbox">
                  <label>
                    <input type="checkbox" <?php echo ( osc_renewal_items_enabled() ? 'checked="checked"' : ''); ?> name="enabled_renewal_items" value="1" />
                    <?php _e('User can renew its listings once expired. This feature is available only to registered users.'); ?>
                  </label>
                </div>
                
                <div class="form-label-checkbox separate-top-medium">
                  <label>
                    <input type="checkbox" <?php echo ( osc_renewal_update_publish_date() ? 'checked="checked"' : ''); ?> name="renewal_update_pub_date" value="1" />
                    <?php _e('Publish date of renewed listing is updated to current date'); ?>
                  </label>
                </div>
                
                <div class="separate-top-medium">
                  <?php printf( __('Listing can be republished %s times at maximum'), '<input type="text" class="input-small" name="renewal_limit" value="' . osc_renewal_limit() . '" />'); ?>
                  <div class="help-box">
                    <?php _e('If the value is set to zero, there is no renewal limit'); ?>
                  </div>
                </div>
                
              </div>
            </div>

            <div class="form-row">
              <div class="form-label"> <?php _e('Contact publisher'); ?></div>
              <div class="form-controls">
                <div class="form-label-checkbox">
                  <label>
                    <input type="checkbox" <?php echo ( osc_item_contact_form_disabled() ? 'checked="checked"' : ''); ?> name="item_contact_form_disabled" value="1" />
                    <?php _e('Disable contact publisher form'); ?>
                  </label>
                </div>
                
                <div class="form-label-checkbox separate-top-medium">
                  <label>
                    <input type="checkbox" <?php echo ( osc_reg_user_can_contact() ? 'checked="checked"' : ''); ?> name="reg_user_can_contact" value="1" />
                    <?php _e('Only allow registered users to contact publisher'); ?>
                  </label>
                </div>
                
                <div class="form-label-checkbox separate-top-medium">
                  <label>
                    <input type="checkbox" <?php echo ( osc_reg_user_can_see_phone() ? 'checked="checked"' : ''); ?> name="osc_reg_user_can_see_phone" value="1" />
                    <?php _e('Only allow registered users to see contact phone number'); ?>
                  </label>
                </div>
                
                <div class="separate-top-medium">
                  <label>
                    <input type="checkbox" <?php echo ( osc_item_attachment() ? 'checked="checked"' : ''); ?> name="item_attachment" value="1" />
                    <?php _e('Allow attached files in contact publisher form'); ?>
                  </label>
                </div>
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-label"> <?php _e('Send to friend'); ?></div>
              <div class="form-controls">
                <div class="form-label-checkbox">
                  <label>
                    <input type="checkbox" <?php echo ( osc_item_send_friend_form_disabled() ? 'checked="checked"' : ''); ?> name="item_send_friend_form_disabled" value="1" />
                    <?php _e('Disable send to friend form'); ?>
                  </label>
                </div>
              </div>
            </div>

            <div class="form-row">
              <div class="form-label"> <?php _e('Notifications'); ?></div>
              <div class="form-controls">
                <div class="form-label-checkbox">
                  <label>
                    <input type="checkbox" <?php echo (osc_notify_new_item() ? 'checked="checked"' : ''); ?> name="notify_new_item" value="1" />
                    <?php _e('Notify admin when a new listing is added'); ?>
                  </label>
                </div>
                <div class="separate-top-medium">
                  <label>
                    <input type="checkbox" <?php echo (osc_notify_contact_item() ? 'checked="checked"' : ''); ?> name="notify_contact_item" value="1" />
                    <?php _e('Send admin a copy of the "contact publisher" email'); ?>
                  </label>
                </div>
                <div class="separate-top-medium">
                  <label>
                    <input type="checkbox" <?php echo (osc_notify_contact_friends() ? 'checked="checked"' : ''); ?> name="notify_contact_friends" value="1" />
                    <?php _e('Send admin a copy to "share listing" email'); ?>
                  </label>
                </div>
              </div>
            </div>

            <div class="form-row">
              <div class="form-label"><?php _e('Warn about expiration'); ?></div>
              <div class="form-controls">
                <input type="text" class="input-small" name="warn_expiration" value="<?php echo osc_esc_html(osc_warn_expiration()); ?>" />
                <div class="inpt-desc"><?php _e('days'); ?></div>
                <span class="help-box">
                  <?php _e('This option will send an email X days before an ad expires to the author. 0 for email at day of expiration. -1 for no email.'); ?><br/>
                  <?php _e('Require hourly cron to be configured.'); ?>
                </span>
              </div>
            </div>

            <div class="form-row">
							<div class="form-label"> <?php _e('Redirect priority after publish'); ?></div>
              <div class="form-controls">
								<div class="">
                  <select name="item_post_redirect">
                    <option value="DASH-ITEM-CAT" <?php if(osc_get_redirect_after_publish() == 'DASH-ITEM-CAT') { ?>selected="selected"<?php } ?>><?php _e('Dashboard > Listing > Category'); ?></option>
                    <option value="ITEM-CAT" <?php if(osc_get_redirect_after_publish() == 'ITEM-CAT') { ?>selected="selected"<?php } ?>><?php _e('Listing > Category'); ?></option>
                    <option value="" <?php if(osc_get_redirect_after_publish() == '') { ?>selected="selected"<?php } ?>><?php _e('Category'); ?></option>
                  </select>
                  <div class="help-box"><?php _e('Redirect priority once item is published. I.e. Dashboard > Listing > Search means, that if user is logged in, will be redirected to user listings page in account. If is not logged and item is active, will be redirected to listing page. Otherwise will go to category page.'); ?></div>
								</div> 
							</div> 
						</div>

            <div class="form-row">
							<div class="form-label"> <?php _e('Title length'); ?></div>
              <div class="form-controls">
								<div class="">
									<?php printf(__('%s'), '<input type="text" class="input-medium" name="max_chars_per_title" value="' . osc_max_characters_per_title() . '" />'); ?>
                  <div class="inpt-desc"><?php _e('characters'); ?></div>
								</div>
							</div>
						</div>

						<div class="form-row">
							<div class="form-label"> <?php _e('Description length'); ?></div>
								<div class="form-controls">
                  <div class="">
                    <?php printf(__('%s'), '<input type="text" class="input-medium" name="max_chars_per_description" value="' . osc_max_characters_per_description() . '" />'); ?>
                    <div class="inpt-desc"><?php _e('characters'); ?></div>
                  </div>
                <div class="help-box"><?php _e('Note that if TinyMCE is enabled, real limit will be lower, as HTML symbols are counted towards limit as well. In this case you should set limit higher by 30%.'); ?></div>
							</div>
						</div>
            
            <div class="form-row">
              <div class="form-label"> <?php _e('Optional fields'); ?></div>
              <div class="form-controls">
                <div class="form-label-checkbox">
                  <label>
                    <input type="checkbox" <?php echo (osc_price_enabled_at_items() ? 'checked="checked"' : ''); ?> name="enableField#f_price@items" value="1"  />
                    <?php _e('Price'); ?>
                  </label>
                  
                  <div class="separate-top-medium">
                    <label>
                      <input type="checkbox" <?php echo (osc_images_enabled_at_items() ? 'checked="checked"' : ''); ?> name="enableField#images@items" value="1" />
                      <?php _e('Upload images'); ?>
                    </label>
                  </div>
                  
                  <div class="separate-top-medium">
                    <?php printf(__('Upload max. %s images per listing'), '<input type="text" class="input-small" name="numImages@items" value="' . osc_max_images_per_item() . '" />'); ?>
                    <div class="help-box"><?php _e('If the value is zero, it means an unlimited number of images is allowed'); ?></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-row">
              <div class="form-label"> <?php _e('Statistics'); ?></div>
              <div class="form-controls">
								<div class="">
                  <select name="item_stats_method">
                    <option value="SESSION" <?php if(osc_item_stats_method() == 'SESSION') { ?>selected="selected"<?php } ?>><?php _e('One view per session'); ?></option>
                    <option value="PAGELOAD" <?php if(osc_item_stats_method() == 'PAGELOAD') { ?>selected="selected"<?php } ?>><?php _e('One view per page load'); ?></option>
                  </select>
                  
                  <div class="help-box"><?php _e('By default, page view is counter for each item once for each session. If one view per page load selected, it might be misused by sellers to increase page views.'); ?></div>
								</div> 
							</div>               
              
            </div>
            
            
            <div class="form-actions">
              <input type="submit" id="save_changes" value="<?php echo osc_esc_html(__('Save changes')); ?>" class="btn btn-submit" />
            </div>
          </div>
        </fieldset>
        </form>
        </div>
        <!-- /settings form -->
</div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>