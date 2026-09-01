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

//customize Head
function customHead() { 
?>
<script type="text/javascript">
$(document).ready(function(){
  // Code for form validation
  $("form[name=permalinks_form]").validate({
    rules: {
      rewrite_item_url: {
        required: true,
        minlength: 2
      },
      rewrite_page_url: {
        required: true,
        minlength: 2
      },
      rewrite_cat_url: {
        required: true,
        minlength: 2
      },
      rewrite_search_url: {
        required: true,
        minlength: 2
      },
      rewrite_search_country: {
        required: true,
        minlength: 2
      },
      rewrite_search_region: {
        required: true,
        minlength: 2
      },
      rewrite_search_city: {
        required: true,
        minlength: 2
      },
      rewrite_search_city_area: {
        required: true,
        minlength: 2
      },
      rewrite_search_category: {
        required: true,
        minlength: 2
      },
      rewrite_search_user: {
        required: true,
        minlength: 2
      },
      rewrite_search_pattern: {
        required: true,
        minlength: 2
      },
      rewrite_search_order: {
        required: true,
        minlength: 2
      },
      rewrite_search_order_type: {
        required: true,
        minlength: 2
      },
      rewrite_search_order_by_price: {
        required: true,
        minlength: 2
      },
      rewrite_search_order_by_pub_date: {
        required: true,
        minlength: 2
      },
      rewrite_search_order_by_relevance: {
        required: true,
        minlength: 2
      },
      rewrite_search_order_by_expiration: {
        required: true,
        minlength: 2
      },
      rewrite_search_order_by_rating: {
        required: true,
        minlength: 2
      },
      rewrite_search_price_min: {
        required: true,
        minlength: 2
      },
      rewrite_search_price_max: {
        required: true,
        minlength: 2
      },
      rewrite_search_with_picture: {
        required: true,
        minlength: 2
      },
      rewrite_search_premium_only: {
        required: true,
        minlength: 2
      },
      rewrite_search_with_phone: {
        required: true,
        minlength: 2
      },
      rewrite_search_show_as: {
        required: true,
        minlength: 2
      },
      rewrite_search_page_number: {
        required: true,
        minlength: 2
      },
      rewrite_contact: {
        required: true,
        minlength: 2
      },
      rewrite_feed: {
        required: true,
        minlength: 2
      },
      rewrite_language: {
        required: true,
        minlength: 2
      },
      rewrite_item_mark: {
        required: true,
        minlength: 2
      },
      rewrite_item_send_friend: {
        required: true,
        minlength: 2
      },
      rewrite_item_contact: {
        required: true,
        minlength: 2
      },
      rewrite_item_activate: {
        required: true,
        minlength: 2
      },
      rewrite_item_deactivate: {
        required: true,
        minlength: 2
      },
      rewrite_item_renew: {
        required: true,
        minlength: 2
      },
      rewrite_item_edit: {
        required: true,
        minlength: 2
      },
      rewrite_item_delete: {
        required: true,
        minlength: 2
      },
      rewrite_item_resource_delete: {
        required: true,
        minlength: 2
      },
      rewrite_user_login: {
        required: true,
        minlength: 2
      },
      rewrite_user_dashboard: {
        required: true,
        minlength: 2
      },
      rewrite_user_logout: {
        required: true,
        minlength: 2
      },
      rewrite_user_register: {
        required: true,
        minlength: 2
      },
      rewrite_user_activate: {
        required: true,
        minlength: 2
      },
      rewrite_user_activate_alert: {
        required: true,
        minlength: 2
      },
      rewrite_user_profile: {
        required: true,
        minlength: 2
      },
      rewrite_user_items: {
        required: true,
        minlength: 2
      },
      rewrite_user_alerts: {
        required: true,
        minlength: 2
      },
      rewrite_user_recover: {
        required: true,
        minlength: 2
      },
      rewrite_user_forgot: {
        required: true,
        minlength: 2
      },
      rewrite_user_change_password: {
        required: true,
        minlength: 2
      },
      rewrite_user_change_email: {
        required: true,
        minlength: 2
      },
      rewrite_user_change_username: {
        required: true,
        minlength: 2
      },
      rewrite_user_change_email_confirm: {
        required: true,
        minlength: 2
      }
    },
    messages: {
      rewrite_item_url: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Listings url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Listings url"))); ?>.'
      },
      rewrite_page_url: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Static page url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Static page url"))); ?>.'
      },
      rewrite_cat_url: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Categories url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Categories url"))); ?>.'
      },
      rewrite_search_url: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search url"))); ?>.'
      },
      rewrite_search_country: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search country"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search country"))); ?>.'
      },
      rewrite_search_region: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search region"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search region"))); ?>.'
      },
      rewrite_search_city: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search city"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search city"))); ?>.'
      },
      rewrite_search_city_area: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search city area"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search city area"))); ?>.'
      },
      rewrite_search_category: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search category"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search category"))); ?>.'
      },
      rewrite_search_user: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search user"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search user"))); ?>.'
      },
      rewrite_search_pattern: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search pattern"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search pattern"))); ?>.'
      },
      rewrite_search_order: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search order"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search order"))); ?>.'
      },
      rewrite_search_order_type: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search order type"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search order type"))); ?>.'
      },
      rewrite_search_order_by_price: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search order by price"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search order by price"))); ?>.'
      },
      rewrite_search_order_by_pub_date: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search order by publish date"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search order by publish date"))); ?>.'
      },
      rewrite_search_order_by_relevance: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search order by relevance"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search order by relevance"))); ?>.'
      },
      rewrite_search_order_by_expiration: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search order by expiration date"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search order by expiration date"))); ?>.'
      },
      rewrite_search_order_by_rating: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search order by rating"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search order by rating"))); ?>.'
      },
      rewrite_search_price_min: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search price minimum"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search price minimum"))); ?>.'
      },
      rewrite_search_price_max: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search price maximum"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search price maximum"))); ?>.'
      },
      rewrite_search_with_picture: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search with picture"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search with picture"))); ?>.'
      },
      rewrite_search_premium_only: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search premium only"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search premium only"))); ?>.'
      },
      rewrite_search_with_phone: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search with phone"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search with phone"))); ?>.'
      },
      rewrite_search_show_as: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search show as"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search show as"))); ?>.'
      },
      rewrite_search_page_number: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search page number"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Search page number"))); ?>.'
      },
      rewrite_contact: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Contact url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Contact url"))); ?>.'
      },
      rewrite_feed: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Feed url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Feed url"))); ?>.'
      },
      rewrite_language: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Language url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Language url"))); ?>.'
      },
      rewrite_item_mark: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Listing mark url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Listing mark url"))); ?>.'
      },
      rewrite_item_send_friend: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Listing send friend url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Listing send friend url"))); ?>.'
      },
      rewrite_item_contact: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Listing contact url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Listing contact url"))); ?>.'
      },
      rewrite_item_new: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("New listing url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("New listing url"))); ?>.'
      },
      rewrite_item_activate: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Activate listing url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Activate listing url"))); ?>.'
      },
      rewrite_item_deactivate: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Deactivate listing url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Deactivate listing url"))); ?>.'
      },
      rewrite_item_renew: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Listing renewal url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Listing renewal url"))); ?>.'
      },
      rewrite_item_edit: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Edit listing url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Edit listing url"))); ?>.'
      },
      rewrite_item_delete: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Delete listing url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Delete listing url"))); ?>.'
      },
      rewrite_item_resource_delete: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Delete listing resource url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Delete listing resource url"))); ?>.'
      },
      rewrite_user_login: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Login url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Login url"))); ?>.'
      },
      rewrite_user_dashboard: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("User dashboard url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("User dashboard url"))); ?>.'
      },
      rewrite_user_logout: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Logout url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Logout url"))); ?>.'
      },
      rewrite_user_register: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("User register url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("User register url"))); ?>.'
      },
      rewrite_user_activate: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Activate user url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Activate user url"))); ?>.'
      },
      rewrite_user_activate_alert: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Activate alert url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Activate alert url"))); ?>.'
      },
      rewrite_user_profile: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("User profile url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("User profile url"))); ?>.'
      },
      rewrite_user_items: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("User listings url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("User listings url"))); ?>.'
      },
      rewrite_user_alerts: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("User alerts url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("User alerts url"))); ?>.'
      },
      rewrite_user_recover: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Recover user url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Recover user url"))); ?>.'
      },
      rewrite_user_forgot: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("User forgot url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("User forgot url"))); ?>.'
      },
      rewrite_user_change_password: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Change password url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Change password url"))); ?>.'
      },
      rewrite_user_change_email: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Change email url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Change email url"))); ?>.'
      },
      rewrite_user_change_username: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Change username url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Change username url"))); ?>.'
      },
      rewrite_user_change_email_confirm: {
        required: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Change email confirm url"))); ?>.',
        minlength: '<?php echo osc_esc_js(sprintf(__("%s: this field is required"), __("Change email confirm url"))); ?>.'
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


$(document).ready(function() {
  $('body').on('click', 'input#rewrite_enabled', function(e) {
    $('#inner_rules').addClass('has-changed').toggleClass('change');
  });
});
</script>
<?php
}

osc_add_hook('admin_header','customHead', 10);


function render_offset(){
  return 'row-offset';
}

osc_add_hook('admin_page_header','customPageHeader');


function addHelp() {
  echo '<p>' . __("Activate this option if you want your site's URLs to be more attractive to search engines and intelligible for users. <strong>Be careful</strong>: depending on your hosting service, this might not work correctly.") . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader(){ 
  ?>
  <h1><?php _e('Settings'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

function customPageTitle($string) {
  return sprintf(__('Permalinks - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php'); 
?>

<div id="static-page-setting">
  <div class="flashmessage flashmessage-info">
    <p class="info">
      <?php _e('By default Osclass uses web URLs which have question marks and lots of numbers in them, those are considered as "not user friendly". Friendly URLs improve the aesthetics, usability, and forward-compatibility of your links, but most important impact is SEO - only website with friendly URLs can get good SEO!'); ?><br/>
      <?php _e('Wrong configuration may brake your site! Make sure that page configuration is unique and does not overlap.'); ?><br/><br/>
      <a href="https://docs.osclasspoint.com/perlmalinks" target="_blank"><?php _e('Permalinks documentation'); ?></a>
    </p>
  </div>

  <ul id="error_list"></ul>

  <div>&nbsp;</div>

  <div>
    <h2 class="render-title"><?php _e('Permalinks Settings'); ?></h2>

    <form name="settings_form" class="separate-top" action="<?php echo osc_admin_base_url(true); ?>" method="post">
      <input type="hidden" name="page" value="settings" />
      <input type="hidden" name="action" value="permalinks_post" />
      
      <fieldset>
      <div class="form-horizontal">
      <div class="form-row">
        <div class="form-label"><?php _e('Friendly urls'); ?></div>
        <div class="form-controls">
          <div class="form-label-checkbox">
            <label id="rewrite-enabled-label">
              <input type="checkbox" <?php echo (osc_rewrite_enabled() ? 'checked="checked"' : ''); ?> name="rewrite_enabled" id="rewrite_enabled" value="1" />
              <?php _e('Enable friendly urls'); ?>
            </label>
          </div>
        </div>
      </div>
      
      <div id="custom_rules">
        <div id="inner_rules" class="<?php if(osc_rewrite_enabled()) { echo 'rewrite-enabled'; } else { echo 'rewrite-disabled'; } ?>">
        
          <h2 class="render-title separate-top"><?php _e('Core pages structure'); ?></h2>
        
          <div class="form-row">
            <div class="form-label"><?php _e('Listing URL:'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-large" size="60" name="rewrite_item_url" value="<?php echo osc_esc_html(osc_get_preference('rewrite_item_url')); ?>" />
              <div class="help-box">
                <?php echo sprintf(__('Required keywords: %s'), '{ITEM_ID}'); ?>
                <br/>
                <?php echo sprintf(__('Accepted keywords: %s'), '<b>{ITEM_ID}</b>,{CATEGORIES},{CATEGORY},{ITEM_TITLE},{ITEM_COUNTRY},{ITEM_COUNTRY_CODE},{ITEM_REGION},{ITEM_CITY},{ITEM_CITY_AREA},{ITEM_ZIP},{ITEM_CONTACT_NAME},{ITEM_CONTACT_EMAIL},{ITEM_CURRENCY_CODE},{ITEM_PUB_DATE}'); ?>
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Static page URL:'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-large" size="40" name="rewrite_page_url" value="<?php echo osc_esc_html(osc_get_preference('rewrite_page_url')); ?>" />
              <div class="help-box">
                <?php echo sprintf(__('Accepted keywords: %s.'), '{PAGE_ID}, {PAGE_SLUG}'); ?> 
                <?php echo sprintf(__('When not using %s, add prefix before page slug to keep static page URLs unique, example: %s.'), '{PAGE_ID}', 'help/{PAGE_SLUG}'); ?>
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Category page URL:'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-large" size="40" name="rewrite_cat_url" value="<?php echo osc_esc_html(osc_get_preference('rewrite_cat_url')); ?>" />
              <div class="help-box">
                <?php echo sprintf(__('Accepted keywords: %s'), '{CATEGORY_ID},{CATEGORY_NAME},{CATEGORIES}'); ?>
              </div>
            </div>
          </div>


          <h2 class="render-title separate-top"><?php _e('Search page structure'); ?></h2>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Search prefix URL:'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="seo_url_search_prefix" value="<?php echo osc_esc_html(osc_get_preference('seo_url_search_prefix')); ?>" />
              <div class="help-box">
                <?php _e('It always appear before the category, region or city url.'); ?>
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Search URL:'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_url" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_url')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Search keyword country'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_country" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_country')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Search keyword region'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_region" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_region')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Search keyword city'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_city" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_city')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Search keyword city area'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_city_area" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_city_area')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Search keyword category'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_category" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_category')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Search keyword user'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_user" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_user')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Search keyword pattern'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_pattern" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_pattern')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Search order'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_order" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_order')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Search order type'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_order_type" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_order_type')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Search order by price'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_order_by_price" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_order_by_price')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Search order by publish date'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_order_by_pub_date" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_order_by_pub_date')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Search order by relevance'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_order_by_relevance" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_order_by_relevance')); ?>" />
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Search order by expiration date'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_order_by_expiration" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_order_by_expiration')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Search order by rating'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_order_by_rating" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_order_by_rating')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Search minimum price'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_price_min" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_price_min')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Search maximum price'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_price_max" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_price_max')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Search with picture'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_with_picture" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_with_picture')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Search premium only'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_premium_only" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_premium_only')); ?>" />
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Search with phone'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_with_phone" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_with_phone')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Search show as'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_show_as" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_show_as')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Search page number'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_search_page_number" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_page_number')); ?>" />
            </div>
          </div>

          <h2 class="render-title separate-top"><?php _e('Custom search page structure (optional)'); ?></h2>

          <div class="flashmessage flashmessage-info">
            <p class="info">
              <?php _e('You can customize search page URLs based on active parameters in search engine.'); ?><br/>
              <?php echo sprintf(__('Following parameters are ignored in custom rules (no matter if strict is enabled) and will be added at the end of rule: %s. Do not use these!'), 'lang, iPage, sShowAs, sOrder, iOrderType'); ?><br/>

              <?php if(osc_subdomain_type_to_raw_param() != '') { ?>
                <?php echo sprintf(__('Subdomain params are forbidden in custom rules and will be removed: %s.'), osc_subdomain_type_to_raw_param()); ?><br/>
              <?php } ?>

              <?php _e('Custom rule is activated and applied only if all required parameters exists (not empty). Priority is reversed - A is lowest, Z is highest. Place very general rules at start and very specific (those overlap with general ones) at the end.'); ?><br/>
              <?php _e('Custom rules has higher priority then canonical urls (category, category + city, ...)'); ?><br/>
              <?php echo sprintf(__('All other search parameters are accepted. Use technical name. Examples: %s'), 'sCategory, sCountry, sRegion, sCity, sPattern, sPriceMin, sPriceMax, bPic, ...'); ?><br/>
            </p>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Custom rules'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label id="">
                  <input type="checkbox" <?php echo (osc_rewrite_search_custom_rules_enabled() ? 'checked="checked"' : ''); ?> name="rewrite_search_custom_rules_enabled" id="rewrite_search_custom_rules_enabled" value="1" />
                  <?php _e('Enable custom search rules'); ?>
                </label>
              </div>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Strict form'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label id="">
                  <input type="checkbox" <?php echo (osc_rewrite_search_custom_rules_strict() ? 'checked="checked"' : ''); ?> name="rewrite_search_custom_rules_strict" id="rewrite_search_custom_rules_strict" value="1" />
                  <?php _e('Custom rules must strictly match to pattern'); ?>
                </label>
              </div>
              
              <div class="help-box">
                <?php echo __('If strict is enabled, your search page params must strictly match params from custom rule. If there are any extra params, rule will not match. '); ?>
              </div>
            </div>
          </div>
          
          <?php foreach(range('a','z') as $search_rule_id) { ?>
            <div class="form-row">
              <div class="form-label"><?php echo sprintf(__('Search url rule "%s"'), strtoupper($search_rule_id)); ?></div>
              <div class="form-controls">
                <input type="text" class="input-medium" size="60" name="rewrite_search_rule_<?php echo $search_rule_id; ?>" value="<?php echo osc_esc_html(osc_get_preference('rewrite_search_rule_' . $search_rule_id)); ?>" />

                <?php if($search_rule_id == 'a') { ?>
                  <div class="help-box"><?php echo __('Rule "A" - lowest priority'); ?></div>
                  
                <?php } else if ($search_rule_id == 'z') { ?>
                  <div class="help-box"><?php echo __('Rule "Z" - highest priority'); ?></div>
                <?php } ?>
              </div>
            </div>
          <?php } ?>

          
          <h2 class="render-title separate-top"><?php _e('Listing pages structure'); ?></h2>

          <div class="form-row">
            <div class="form-label"><?php _e('Listing mark'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_item_mark" value="<?php echo osc_esc_html(osc_get_preference('rewrite_item_mark')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Listing send friend'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_item_send_friend" value="<?php echo osc_esc_html(osc_get_preference('rewrite_item_send_friend')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Listing contact'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_item_contact" value="<?php echo osc_esc_html(osc_get_preference('rewrite_item_contact')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Listing new'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_item_new" value="<?php echo osc_esc_html(osc_get_preference('rewrite_item_new')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Listing activate'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_item_activate" value="<?php echo osc_esc_html(osc_get_preference('rewrite_item_activate')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Listing deactivate'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_item_deactivate" value="<?php echo osc_esc_html(osc_get_preference('rewrite_item_deactivate')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Listing renew'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_item_renew" value="<?php echo osc_esc_html(osc_get_preference('rewrite_item_renew')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Listing edit'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_item_edit" value="<?php echo osc_esc_html(osc_get_preference('rewrite_item_edit')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Listing delete'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_item_delete" value="<?php echo osc_esc_html(osc_get_preference('rewrite_item_delete')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Listing resource delete'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_item_resource_delete" value="<?php echo osc_esc_html(osc_get_preference('rewrite_item_resource_delete')); ?>" />
            </div>
          </div>


          <h2 class="render-title separate-top"><?php _e('User pages structure'); ?></h2>
          
          <div class="form-row">
            <div class="form-label"><?php _e('User login'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_user_login" value="<?php echo osc_esc_html(osc_get_preference('rewrite_user_login')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('User dashboard'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_user_dashboard" value="<?php echo osc_esc_html(osc_get_preference('rewrite_user_dashboard')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('User logout'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_user_logout" value="<?php echo osc_esc_html(osc_get_preference('rewrite_user_logout')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('User register'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_user_register" value="<?php echo osc_esc_html(osc_get_preference('rewrite_user_register')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('User activate'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_user_activate" value="<?php echo osc_esc_html(osc_get_preference('rewrite_user_activate')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('User activate alert'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_user_activate_alert" value="<?php echo osc_esc_html(osc_get_preference('rewrite_user_activate_alert')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('User profile'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_user_profile" value="<?php echo osc_esc_html(osc_get_preference('rewrite_user_profile')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('User listings'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_user_items" value="<?php echo osc_esc_html(osc_get_preference('rewrite_user_items')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('User alerts'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_user_alerts" value="<?php echo osc_esc_html(osc_get_preference('rewrite_user_alerts')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('User recover'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_user_recover" value="<?php echo osc_esc_html(osc_get_preference('rewrite_user_recover')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('User forgot'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_user_forgot" value="<?php echo osc_esc_html(osc_get_preference('rewrite_user_forgot')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('User change password'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_user_change_password" value="<?php echo osc_esc_html(osc_get_preference('rewrite_user_change_password')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('User change email'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_user_change_email" value="<?php echo osc_esc_html(osc_get_preference('rewrite_user_change_email')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('User change email confirm'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_user_change_email_confirm" value="<?php echo osc_esc_html(osc_get_preference('rewrite_user_change_email_confirm')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('User change username'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_user_change_username" value="<?php echo osc_esc_html(osc_get_preference('rewrite_user_change_username')); ?>" />
            </div>
          </div>
          

          <h2 class="render-title separate-top"><?php _e('Other pages structure'); ?></h2>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Contact'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_contact" value="<?php echo osc_esc_html(osc_get_preference('rewrite_contact')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Feed'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_feed" value="<?php echo osc_esc_html(osc_get_preference('rewrite_feed')); ?>" />
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Language'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-medium" size="20" name="rewrite_language" value="<?php echo osc_esc_html(osc_get_preference('rewrite_language')); ?>" />
            </div>
          </div>
          
        </div>
      </div>
      
      <div class="form-actions">
        <input type="submit" id="save_changes" value="<?php echo osc_esc_html(__('Save changes')); ?>" class="btn btn-submit" />
      </div>



      <?php if(osc_rewrite_enabled()) { ?>
        <h2 class="render-title" style="margin-top:40px;"><?php _e('Configuration of .htaccess file'); ?></h2>

        <?php if(!file_exists(osc_base_path() . '.htaccess')) { ?>
          <div class="flashmessage flashmessage-error flashmessage-small">
            <p class="info">
              <?php _e('Permalinks will be functional just thanks to .htaccess file. Your .htaccess file could not be found! Ask your hosting provider for help.'); ?>
            </p>
          </div>
        <?php } ?>
      
        <?php if(file_exists(osc_base_path() . '.htaccess')) { ?>
          <div class="form-row">
            <h3 class="separate-top"><strong class="htaccess-label"><?php _e('Your .htaccess file:') ?></strong></h3>
            <pre><?php
              $htaccess_content =  file_get_contents(osc_base_path() . '.htaccess');
              echo htmlentities($htaccess_content);
            ?></pre>
          </div>
        <?php } ?>

        <div class="form-row">
          <h3 class="separate-top"><strong class="htaccess-label"><?php _e('What your .htaccess file should contain:'); ?></strong></h3>
          <pre><?php
            $rewrite_base = REL_WEB_URL;
            
            $htaccess = <<<HTACCESS
              <IfModule mod_rewrite.c>
              RewriteEngine On
              RewriteBase {$rewrite_base}
              RewriteRule ^index\.php$ - [L]
              RewriteCond %{REQUEST_FILENAME} !-f
              RewriteCond %{REQUEST_FILENAME} !-d
              RewriteRule . {$rewrite_base}index.php [L]
              </IfModule>
              HTACCESS;
              
              echo htmlentities($htaccess);
          ?></pre>
        </div>
      <?php } ?>
      </fieldset>
    </form>
  </div>
</div>

<?php osc_current_admin_theme_path('parts/footer.php'); ?>