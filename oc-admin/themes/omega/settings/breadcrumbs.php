<?php
if(!defined('ABS_PATH')) exit('ABS_PATH is not loaded. Direct access is not allowed.');
/*
 * Copyright 2020 OsclassPoint.com
 *
 * Osclass maintained & developed by OsclassPoint.com
 * you may not use this file except in compliance with the License.
 * You may download copy of Osclass at
 *
 *     https://osclass-classifieds.com/download
 *
 * Software is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */


osc_enqueue_script('jquery-validate');

//customize Head
function customHead() { 
  ?>
  <script type="text/javascript">
  $(document).ready(function(){
    // Code for form validation

    $.validator.addMethod('customrule', function(value, element) {
      if($('input:radio[name=purge_searches]:checked').val()=='custom') {
        if($("#custom_queries").val()=='') {
          return false;
        }
      }
      return true;
    });

    $("form[name=searches_form]").validate({
      rules: {
        custom_queries: {
          digits: true,
          customrule: true
        }
      },
      messages: {
        custom_queries: {
          digits: '<?php echo osc_esc_js(__('Custom number: this field must only contain numeric characters')); ?>.',
          customrule: '<?php echo osc_esc_js(__('Custom number: this field cannot be left empty')); ?>.'
        }
      },
      wrapper: "li",
      errorLabelContainer: "#error_list",
      invalidHandler: function(form, validator) {
        $('html,body').animate({ scrollTop: $('h1').offset().top }, { duration: 250, easing: 'swing'});
      },
      submitHandler: function(form){
        $('button[type=submit], input[type=submit]').attr('disabled', 'disabled');
        form.submit();
      }
    });
  });
  </script>
  <?php
}

osc_add_hook('admin_header','customHead', 10);


function render_offset(){
  return 'row-offset';
}


function addHelp() {
  echo '<p>' . __("Modify breadcrumbs section shown bellow header, customize it or hide on particular pages") . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader() { 
  ?>
  <h1><?php _e('Settings'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Breadcrumbs Settings - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path( 'parts/header.php' ); 
?>

<div id="general-setting">
  <!-- settings form -->
  <div id="general-settings">
    <h2 class="render-title"><?php _e('Breadcrumbs Settings'); ?></h2>
      <ul id="error_list"></ul>
      <form name="breadcrumbs_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
        <input type="hidden" name="page" value="settings" />
        <input type="hidden" name="action" value="breadcrumbs_post" />
        <fieldset>
          <div class="form-horizontal">
            <div class="form-row">
              <div class="form-label"><?php _e('Breadcrumb elements on listings page'); ?></div>
              <div class="form-controls">
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_item_breadcrumbs_page_title() ? 'checked="checked"' : ''); ?> name="breadcrumbs_item_page_title" />
                  <span><?php _e('Website (Page) title'); ?></span>
                </div>
                
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_item_breadcrumbs_country() ? 'checked="checked"' : ''); ?> name="breadcrumbs_item_country" />
                  <span><?php _e('Country name'); ?></span>
                </div>
                
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_item_breadcrumbs_region() ? 'checked="checked"' : ''); ?> name="breadcrumbs_item_region" />
                  <span><?php _e('Region name'); ?></span>
                </div>
                
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_item_breadcrumbs_city() ? 'checked="checked"' : ''); ?> name="breadcrumbs_item_city" />
                  <span><?php _e('City name'); ?></span>
                </div>
                
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo (osc_item_breadcrumbs_category() ? 'checked="checked"' : ''); ?> name="breadcrumbs_item_category" />
                  <span><?php _e('Category name'); ?></span>
                </div>
                
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo (osc_item_breadcrumbs_parent_categories() ? 'checked="checked"' : ''); ?> name="breadcrumbs_item_parent_categories" />
                  <span><?php _e('Parent listing categories'); ?></span>
                </div>
              </div> 
            </div>
            
            
            <div class="form-row" style="margin-top:30px;">
              <div class="form-label"><?php _e('Hide breadcrumbs on specific page'); ?></div>
              <div class="form-controls">
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_breadcrumbs_hide('search') ? 'checked="checked"' : ''); ?> name="bchide-search" />
                  <span><?php _e('Search'); ?> <?php _e('page'); ?></span>
                </div>
                
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_breadcrumbs_hide('item') ? 'checked="checked"' : ''); ?> name="bchide-item" />
                  <span><?php _e('Listing'); ?> <?php _e('page'); ?></span>
                </div>
                
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_breadcrumbs_hide('item', 'send_friend') ? 'checked="checked"' : ''); ?> name="bchide-item-send_friend" />
                  <span><?php _e('Send friend'); ?> <?php _e('page'); ?></span>
                </div>
                
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_breadcrumbs_hide('item', 'contact') ? 'checked="checked"' : ''); ?> name="bchide-item-contact" />
                  <span><?php _e('Contact seller'); ?> <?php _e('page'); ?></span>
                </div>
                
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_breadcrumbs_hide('item', 'item_add') ? 'checked="checked"' : ''); ?> name="bchide-item-item_add" />
                  <span><?php _e('Item publish'); ?> <?php _e('page'); ?></span>
                </div>
                
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_breadcrumbs_hide('user', 'dashboard') ? 'checked="checked"' : ''); ?> name="bchide-user-dashboard" />
                  <span><?php _e('Item edit'); ?> <?php _e('page'); ?></span>
                </div>
                
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_breadcrumbs_hide('user', 'items') ? 'checked="checked"' : ''); ?> name="bchide-user-items" />
                  <span><?php _e('User items'); ?> <?php _e('page'); ?></span>
                </div>
                
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_breadcrumbs_hide('user', 'alerts') ? 'checked="checked"' : ''); ?> name="bchide-user-alerts" />
                  <span><?php _e('User alerts'); ?> <?php _e('page'); ?></span>
                </div>
                
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_breadcrumbs_hide('user', 'profile') ? 'checked="checked"' : ''); ?> name="bchide-user-profile" />
                  <span><?php _e('User profile'); ?> <?php _e('page'); ?></span>
                </div>
                
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_breadcrumbs_hide('user', 'change_email') ? 'checked="checked"' : ''); ?> name="bchide-user-change_email" />
                  <span><?php _e('User change email'); ?> <?php _e('page'); ?></span>
                </div>
                
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_breadcrumbs_hide('user', 'change_password') ? 'checked="checked"' : ''); ?> name="bchide-user-change_password" />
                  <span><?php _e('User change password'); ?> <?php _e('page'); ?></span>
                </div>
                
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_breadcrumbs_hide('user', 'change_username') ? 'checked="checked"' : ''); ?> name="bchide-user-change_username" />
                  <span><?php _e('User change username'); ?> <?php _e('page'); ?></span>
                </div>

                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_breadcrumbs_hide('login', '') ? 'checked="checked"' : ''); ?> name="bchide-login" />
                  <span><?php _e('Login'); ?> <?php _e('page'); ?></span>
                </div>
                
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_breadcrumbs_hide('login', 'recover') ? 'checked="checked"' : ''); ?> name="bchide-login-recover" />
                  <span><?php _e('Recover password'); ?> <?php _e('page'); ?></span>
                </div>
                
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_breadcrumbs_hide('login', 'forgot') ? 'checked="checked"' : ''); ?> name="bchide-login-forgot" />
                  <span><?php _e('Forgot password'); ?> <?php _e('page'); ?></span>
                </div>
                
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_breadcrumbs_hide('register', '') ? 'checked="checked"' : ''); ?> name="bchide-register" />
                  <span><?php _e('Register'); ?> <?php _e('page'); ?></span>
                </div>
                
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_breadcrumbs_hide('page', '') ? 'checked="checked"' : ''); ?> name="bchide-page" />
                  <span><?php _e('Static page'); ?></span>
                </div>
                
                <div class="form-label-checkbox">
                  <input type="checkbox" <?php echo ( osc_breadcrumbs_hide('contact', '') ? 'checked="checked"' : ''); ?> name="bchide-contact" />
                  <span><?php _e('Contact'); ?> <?php _e('page'); ?></span>
                </div>

              </div>
            </div>
                
            <div class="form-row">
              <div class="form-label"><?php _e('Custom hide rules'); ?></div>
              <div class="form-controls">
                <input type="text" name="breadcrumbs_hide_custom" class="xlarge" value="<?php echo osc_esc_html(osc_breadcrumbs_hide_custom_pref()); ?>"/>
                <div class="help-box"><?php _e('Enter custom pages where to hide breadcrumbs, i.e. for plugin pages, in format: location-section. Delimit with comma (,). Example: osp-cart, osp-promote, blg-home'); ?></div>
              </div>

            </div>

            <div class="form-actions">
              <input type="submit" id="save_changes" value="<?php echo osc_esc_html( __('Save changes') ); ?>" class="btn btn-submit" />
            </div>
          </div>
        </div>
      </fieldset>
    </form>
  </div>
  <!-- /settings form -->
</div>
<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>

<script>
  $(document).ready(function() {
    if($('input[name="breadcrumbs_item_category"]').is(':checked')) {
      $('input[name="breadcrumbs_item_parent_categories"]').prop('disabled', false);
    } else {
      $('input[name="breadcrumbs_item_parent_categories"]').prop('disabled', true);
    }
      
    $('body').on('change', 'input[name="breadcrumbs_item_category"]', function() {
      if($(this).is(':checked')) {
        $('input[name="breadcrumbs_item_parent_categories"]').prop('disabled', false);
      } else {
        $('input[name="breadcrumbs_item_parent_categories"]').prop('disabled', true);
      }
    });
  });
</script>