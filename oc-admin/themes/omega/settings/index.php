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

$dateFormats = array('F j, Y', 'Y/m/d', 'm/d/Y', 'd/m/Y', 'j. M Y', 'j. m. Y', 'j-M-Y');
$timeFormats = array('g:i a', 'g:i A', 'H:i');

$aLanguages = __get('aLanguages');
$aCurrencies = __get('aCurrencies');

$admin_color_scheme = (osc_get_preference('admin_color_scheme') <> '' ? osc_get_preference('admin_color_scheme') : 'default');
$color_schemes = omg_color_schemes();

//customize Head
function customHead() { 
  ?>
  <script type="text/javascript">
  $(document).ready(function(){
    // When API key is entered, does not allow validate
    $('body').on('change keypress keyup', 'input[name="osclasspoint_api_key"]', function(e) {
      $('a.btn.validate').addClass('disabled').addClass('is-changed').prop('disabled', true).attr('href', '#');
    });
    
    $('.btn.validate').on('click', function(e) {
      if($(this).hasClass('disabled') && $(this).hasClass('is-changed')) {
        e.preventDefault();
        alert('<?php echo osc_esc_js(__('Save changes before validating API key')); ?>');
        return false;
      }
    });
    
    // Color scheme selector
    $('body').on('click', '.form-ts .color-scheme', function(e) {
      e.preventDefault();
      var scheme = $(this).find('input[type="radio"]').val();
      
      $('.form-ts .color-scheme').removeClass('selected');
      $(this).addClass('selected');
      $(this).find('input[type="radio"]').prop('checked', true);
      
      $('body').attr('class', function(i, c){
        return c.replace(/(^|\s)scheme-\S+/g, '');
      });
      
      $('body').addClass('scheme-' + scheme);
    });
    
    
    // Code for form validation
    $("form[name=settings_form]").validate({
      rules: {
        pageTitle: {
          required: true,
          minlength: 1
        },
        contactEmail: {
          required: true,
          email: true
        },
        num_rss_items: {
          required: true,
          digits: true
        },
        max_latest_items_at_home: {
          required: true,
          digits: true
        },
        default_results_per_page: {
          required: true,
          digits: true
        }
      },
      messages: {
        pageTitle: {
          required: '<?php echo osc_esc_js(__("Page title: this field is required")); ?>.',
          minlength: '<?php echo osc_esc_js(__("Page title: this field is required")); ?>.'
        },
        contactEmail: {
          required: '<?php echo osc_esc_js(__("Email: this field is required")); ?>.',
          email: '<?php echo osc_esc_js(__("Invalid email address")); ?>.'
        },
        num_rss_items: {
          required: '<?php echo osc_esc_js(__("Listings shown in RSS feed: this field is required")); ?>.',
          digits: '<?php echo osc_esc_js(__("Listings shown in RSS feed: this field must only contain numeric characters")); ?>.'
        },
        max_latest_items_at_home: {
          required: '<?php echo osc_esc_js(__("Latest listings shown: this field is required")); ?>.',
          digits: '<?php echo osc_esc_js(__("Latest listings shown: this field must only contain numeric characters")); ?>.'
        },
        default_results_per_page: {
          required: '<?php echo osc_esc_js(__("The search page shows: this field is required")); ?>.',
          digits: '<?php echo osc_esc_js(__("The search page shows: this field must only contain numeric characters")); ?>.'
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

  function custom_date(date_format) {
    $.getJSON(
      "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=date_format",
      {"format" : date_format},
      function(data){
        if(data.str_formatted!='') {
          $("#custom_date").text(' <?php _e('Preview'); ?>: ' + data.str_formatted)
        } else {
          $("#custom_date").text('');
        }
      }
    );
  }

  function custom_time(time_format) {
    $.getJSON(
      "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=date_format",
      {"format" : time_format},
      function(data){
        if(data.str_formatted!='') {
          $("#custom_time").text(' <?php _e('Preview'); ?>: ' + data.str_formatted)
        } else {
          $("#custom_time").text('');
        }
      }
    );
  }
  </script>
  <?php
}

osc_add_hook('admin_header','customHead', 10);


function render_offset(){
  return 'row-offset';
}

function addHelp() {
  echo '<p>' . __("Change the basic configuration of your Osclass. From here, you can modify variables such as the site’s name, the default currency or how lists of listings are displayed. <strong>Be careful</strong> when modifying default values if you're not sure what you're doing!") . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader(){ 
  ?>
  <h1><?php _e('Settings'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('General Settings - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php'); 
?>

<div id="general-setting">
  <!-- settings form -->
  <div id="general-settings">
    <h2 class="render-title"><?php _e('General Settings'); ?></h2>
      <ul id="error_list"></ul>
      <form name="settings_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
        <input type="hidden" name="page" value="settings"/>
        <input type="hidden" name="action" value="update"/>
        
        <fieldset>
          <div class="form-horizontal">
          <div class="form-row">
            <div class="form-label"><?php _e('Page title'); ?></div>
            <div class="form-controls"><input type="text" class="xlarge" name="pageTitle" value="<?php echo osc_esc_html(osc_page_title()); ?>"/></div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Page description'); ?></div>
            <div class="form-controls"><input type="text" class="xlarge" name="pageDesc" value="<?php echo osc_esc_html(osc_page_description()); ?>"/></div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Contact e-mail'); ?></div>
            <div class="form-controls"><input type="text" class="large" name="contactEmail" value="<?php echo osc_esc_html(osc_contact_email()); ?>"/></div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Backoffice Theme'); ?></div>
            <div class="form-controls">
              <select name="admin_theme" id="admin_theme">
              <?php $directories = glob(osc_admin_base_path() . 'themes/*', GLOB_ONLYDIR); ?>
              <?php foreach($directories as $d) { ?>
                <?php $name = basename($d); ?>
                <option value="<?php echo osc_esc_html($name); ?>" <?php echo ((osc_get_preference('admin_theme') == $name) ? 'selected="selected"' : ''); ?>><?php echo ucfirst($name); ?></option>
              <?php } ?>
              </select>
            </div>
          </div>
          
          <div class="form-row form-ts">
            <div class="form-label"><?php _e('Backoffice Color Scheme'); ?></div>
            <div class="form-controls">
              <?php foreach($color_schemes as $scheme) { ?>
                <div class="color-scheme <?php if($admin_color_scheme == $scheme['id']) { ?>selected<?php } ?>" data-id="<?php echo $scheme['id']; ?>">
                  <input type="radio" name="admin_color_scheme" id="admin_color_scheme_<?php echo $scheme['id']; ?>" value="<?php echo $scheme['id']; ?>" <?php if($admin_color_scheme == $scheme['id']) { ?>checked<?php } ?>/>
                  <label for="admin_color_scheme_<?php echo $scheme['id']; ?>"><?php echo $scheme['name']; ?></label>
                  
                  <div class="bars">
                    <?php foreach($scheme['colors'] as $c) { ?>
                      <div class="bar" style="background:<?php echo $c; ?>;width:<?php echo floor(100/count($scheme['colors'])); ?>%"></div>
                    <?php } ?>
                  </div>
                </div>
              <?php } ?>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('jQuery Version'); ?></div>
            <div class="form-controls">
              <select name="jquery_version" id="jquery_version">
                <option value="1" <?php echo ((osc_jquery_version() == '1') ? 'selected="selected"' : ''); ?>>1.x.x</option>
                <option value="3" <?php echo ((osc_jquery_version() == '3') ? 'selected="selected"' : ''); ?>>3.x.x</option>
              </select>
              
              <span class="help-box"><?php _e('jQuery 3.x.x may not be compatible with your theme or plugins, make sure to do proper testing before using it in production website!'); ?></span>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Structured data'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo (osc_structured_data_enabled() ? 'checked="checked"' : ''); ?> name="structured_data" value="1"/>
                  <?php _e('Allow osclass to generate structured data (microdata) for google, facebook, twitter, ...'); ?>
                </label>
              </div>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Generator tag'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo (osc_hide_generator_enabled() ? 'checked="checked"' : ''); ?> name="hide_generator" value="1"/>
                  <?php _e('Hide generator meta tag added to website head'); ?>
                </label>
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Canonical tag'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo (osc_always_generate_canonical_enabled() ? 'checked="checked"' : ''); ?> name="always_gen_canonical" value="1"/>
                  <?php _e('Always Generate Canonical URL tag. It is generated on every page (not just search)'); ?>
                </label>
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Enhance canonical tag'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo (osc_enhance_canonical_url_enabled() ? 'checked="checked"' : ''); ?> name="enhance_canonical_url_enabled" value="1"/>
                  <?php _e('Improve canonical tags by removing redundant parameters (sort, pagination, ...) and using default language code in canonical URLs'); ?>
                </label>
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Block indexing'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo (osc_block_indexing_enabled() ? 'checked="checked"' : ''); ?> name="block_indexing_enabled" value="1"/>
                  <?php _e('Prevent search engines from indexing or following any page by injecting noindex, nofollow meta tags site-wide, overriding theme headers'); ?>
                </label>
              </div>
            </div>
          </div>
          
          <div class="form-row separate-top">
            <div class="form-label"><?php _e('Week starts on'); ?></div>
            <div class="form-controls">
              <select name="weekStart" id="weekStart">
              <option value="0" <?php if(osc_week_starts_at() == '0') { ?>selected="selected"<?php } ?>><?php _e('Sunday'); ?></option>
              <option value="1" <?php if(osc_week_starts_at() == '1') { ?>selected="selected"<?php } ?>><?php _e('Monday'); ?></option>
              <option value="2" <?php if(osc_week_starts_at() == '2') { ?>selected="selected"<?php } ?>><?php _e('Tuesday'); ?></option>
              <option value="3" <?php if(osc_week_starts_at() == '3') { ?>selected="selected"<?php } ?>><?php _e('Wednesday'); ?></option>
              <option value="4" <?php if(osc_week_starts_at() == '4') { ?>selected="selected"<?php } ?>><?php _e('Thursday'); ?></option>
              <option value="5" <?php if(osc_week_starts_at() == '5') { ?>selected="selected"<?php } ?>><?php _e('Friday'); ?></option>
              <option value="6" <?php if(osc_week_starts_at() == '6') { ?>selected="selected"<?php } ?>><?php _e('Saturday'); ?></option>
              </select>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Timezone'); ?></div>
            <div class="form-controls">
              <?php require osc_lib_path() . 'osclass/timezones.php'; ?>
              <select name="timezone" id="timezone">
              <?php $selected_tz = osc_timezone(); ?>
              <option value="" selected="selected"><?php _e('Select a timezone...'); ?></option>
              <?php foreach ($timezone as $tz) { ?>
              <option value="<?php echo $tz; ?>" <?php if($selected_tz == $tz) { ?> selected="selected" <?php } ?>><?php echo $tz; ?></option>
              <?php } ?>
              </select>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Date & time format'); ?></div>
            <div class="form-controls">
              <div class="custom-date-time">
                <div id="date">
                  <?php
                  $custom_checked = true;
                  foreach($dateFormats as $df) {
                    $checked = false;
                    if($df == osc_date_format()) {
                      $custom_checked = false;
                      $checked = true;
                    } ?>
                    <div>
                      <input type="radio" name="df" id="<?php echo $df; ?>" value="<?php echo $df; ?>" <?php echo ($checked ? 'checked="checked"' : ''); ?> onclick="javascript:document.getElementById('dateFormat').value = '<?php echo $df; ?>';"/>
                      <?php echo date($df); ?>
                    </div>
                    <?php } ?>
                  
                    <input type="radio" name="df" id="df_custom" value="df_custom" <?php echo ($custom_checked ? 'checked="checked"' : ''); ?>/>
                    <input type="text" name="df_custom_text" id="df_custom_text" class="input-medium" <?php echo ($custom_checked ? 'value="' . osc_esc_html(osc_date_format()) . '"' : ''); ?> onchange="javascript:document.getElementById('dateFormat').value = this.value;" onkeyup="javascript:custom_date(this.value);"/>

                    <br/>

                    <span id="custom_date"></span>
                    <input type="hidden" name="dateFormat" id="dateFormat" value="<?php echo osc_date_format(); ?>"/>
                  </div>
                  
                  <div id="time">
                    <?php
                      $custom_checked = true;
                      foreach($timeFormats as $tf) {
                        $checked = false;
                        if($tf == osc_time_format()) {
                          $custom_checked = false;
                          $checked = true;
                        }
                      ?>
                      <div>
                        <input type="radio" name="tf" id="<?php echo $tf; ?>" value="<?php echo $tf; ?>" <?php echo ($checked ? 'checked="checked"' : ''); ?> onclick="javascript:document.getElementById('timeFormat').value = '<?php echo $tf; ?>';"/>
                        <?php echo date($tf); ?>
                      </div>
                    <?php } ?>
                  
                  <input type="radio" name="tf" id="tf_custom" value="tf_custom" <?php echo ($custom_checked ? 'checked="checked"' : ''); ?>/>
                  <input type="text" class="input-medium" <?php echo ($custom_checked ? 'value="' . osc_esc_html(osc_time_format()) . '"' : ''); ?> onchange="javascript:document.getElementById('timeFormat').value = this.value;" onkeyup="javascript:custom_time(this.value);"/>
                  <br/>
                  
                  <span id="custom_time"></span>
                  <input type="hidden" name="timeFormat" id="timeFormat" value="<?php echo osc_esc_html(osc_time_format()); ?>"/>
                </div>
              </div>
              <div class="help-box" style="clear:both; float:none;"><a href="http://php.net/date" target="_blank"><?php _e('Documentation on date and time formatting'); ?></a></div>
            </div>
          </div>


          <h2 class="render-title separate-top"><?php _e('Limits & RSS settings'); ?></h2>

          <div class="form-row">
            <div class="form-label"><?php _e('Latest listings shown'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-small" name="max_latest_items_at_home" value="<?php echo osc_esc_html(osc_max_latest_items_at_home()); ?>"/>
              <?php _e('at most'); ?>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Search page shows'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-small" name="default_results_per_page" value="<?php echo osc_esc_html(osc_default_results_per_page_at_search()); ?>"/>
              <?php _e('listings at most'); ?>
            </div>
          </div>


          <div class="form-row">
            <div class="form-label"><?php _e('RSS feed'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo (osc_rss_enabled() ? 'checked="checked"' : ''); ?> name="rss_enabled" value="1"/>
                  <?php _e('Enable RSS feed on your site'); ?> (<a href="<?php echo osc_base_url(true); ?>?page=search&sFeed=rss" target="_blank"><?php _e('link'); ?></a>)
                </label>
              </div>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('RSS shows'); ?></div>
            <div class="form-controls">
              <input type="text" class="input-small" name="num_rss_items" value="<?php echo osc_esc_html(osc_num_rss_items()); ?>"/>
              <?php _e('listings at most'); ?>
            </div>
          </div>


          <h2 class="render-title separate-top"><?php _e('Localization settings'); ?></h2>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Default language'); ?></div>
            <div class="form-controls">
              <select name="language">
              <?php foreach($aLanguages as $lang) { ?>
                <option value="<?php echo $lang['pk_c_code']; ?>" <?php echo ((osc_language() == $lang['pk_c_code']) ? 'selected="selected"' : ''); ?>><?php echo $lang['s_name']; ?></option>
              <?php } ?>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Language code in URL'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo (osc_locale_to_base_url_enabled() ? 'checked="checked"' : ''); ?> name="locale_to_base_url_enabled" value="1"/>
                  <?php _e('Add language code into base URL (domain.com/xx/)'); ?>
                </label>
              </div>
              
              <span class="help-box"><?php _e('Only works with Friendly URLs enabled!'); ?></span>
            </div>
          </div>
          
          
          <div class="form-row">
            <div class="form-label"><?php _e('Language code type'); ?></div>
            <div class="form-controls">
              <select name="locale_to_base_url_type" id="locale_to_base_url_type">
                <option value="" <?php if(osc_locale_to_base_url_type() == '') { ?>selected="selected"<?php } ?>><?php _e('Compact format (xx)'); ?></option>
                <option value="LONG" <?php if(osc_locale_to_base_url_type() == 'LONG') { ?>selected="selected"<?php } ?>><?php _e('Long format (xx-yy)'); ?></option>
              </select>
              
              <span class="help-box"><?php echo sprintf(__('Add language code into base URL and change it to (based on your locale): %s'), osc_base_url() . osc_base_url_locale_slug(osc_current_admin_locale()) . '/'); ?></span>
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Default currency'); ?></div>
            <div class="form-controls">
              <select name="currency" id="currency_admin">
                <?php foreach($aCurrencies as $currency) { ?>
                  <option value="<?php echo osc_esc_html($currency['pk_c_code']); ?>" <?php echo ((osc_currency() == $currency['pk_c_code']) ? 'selected="selected"' : ''); ?>><?php echo $currency['pk_c_code'] ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Hreflang Tags'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo (osc_generate_hreflang_tags_enabled() ? 'checked="checked"' : ''); ?> name="gen_hreflang_tags" value="1"/>
                  <?php _e('Generate hreflang tags and add them to header to define alternative site locales'); ?>
                </label>
              </div>
              
              <span class="help-box"><?php _e('Only works with "Language based subdomains" or "Language code in URL" features'); ?></span>
            </div>
          </div>
          
          
          
          <h2 class="render-title separate-top"><?php _e('Search settings'); ?></h2>
          <div class="form-row">
            <div class="form-label"><?php _e('Pattern filter method'); ?></div>
            <div class="form-controls">
              <select name="search_pattern_method" id="search_pattern_method">
                <option value="" <?php if(osc_search_pattern_method() == '') { ?>selected="selected"<?php } ?>><?php _e('Full-text search (default)'); ?></option>
                <option value="nlp" <?php if(osc_search_pattern_method() == 'nlp') { ?>selected="selected"<?php } ?>><?php _e('Enhanced full-text search (with NLP)'); ?></option>
                <option value="like" <?php if(osc_search_pattern_method() == 'like') { ?>selected="selected"<?php } ?>><?php _e('Title/Description contains pattern'); ?></option>
              </select>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Restrict by locale'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo (osc_search_pattern_current_locale_only() ? 'checked="checked"' : ''); ?> name="search_pattern_locale" value="1"/>
                  <?php _e('Only include item title & description those exists in current user locale into pattern filter'); ?>
                </label>
              </div>
            </div>
          </div>
          
          
          <h2 class="render-title separate-top"><?php _e('Category settings'); ?></h2>
          <div class="form-row">
            <div class="form-label"><?php _e('Parent categories'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo (osc_selectable_parent_categories() ? 'checked="checked"' : ''); ?> name="selectable_parent_categories" value="1"/>
                  <?php _e('Allow users to select a parent category as a category when inserting or editing a listing'); ?>
                </label>
              </div>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Category nesting'); ?></div>
            <div class="form-controls">
              <input type="number" class="input-small" name="num_category_levels" min=1 max=12 value="<?php echo osc_esc_html(osc_num_category_levels()); ?>"/>
              <?php _e('levels at most'); ?>
            </div>
          </div>
          
          
          <h2 class="render-title separate-top"><?php _e('Contact Settings'); ?></h2>
          <div class="form-row">
            <div class="form-label"><?php _e('Disable contact form'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo (osc_web_contact_form_disabled() ? 'checked="checked"' : ''); ?> name="web_contact_form_disabled" value="1"/>
                  <?php _e('Customers will not be able to user web contact form'); ?>
                </label>
              </div>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Attachments'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo (osc_contact_attachment() ? 'checked="checked"' : ''); ?> name="enabled_attachment" value="1"/>
                  <?php _e('Allow people to attach a file to the contact form'); ?>
                </label>
              </div>
            </div>
          </div>
          
          <h2 class="render-title separate-top"><?php _e('Cron Settings'); ?></h2>
          <div class="form-row">
            <div class="form-label"><?php _e('Automatic cron process'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo (osc_auto_cron() ? 'checked="checked"' : ''); ?> name="auto_cron"/>
                  <?php printf(__('Allow Osclass to run a built-in <a href="%s" target="_blank">cron</a> automatically without setting crontab'), 'https://docs.osclass-classifieds.com/cron-setup-i82'); ?>
                </label>
              </div>
              <span class="help-box"><?php _e('<b>For testing purpose only!</b> On live/production websites never use this feature and setup cron via cron panel on your hosting.'); ?></span>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Cron execution history (last/next)'); ?></div>
            <div class="form-controls cron-exec">
              <?php 
                $cron_minutely = Cron::newInstance()->getCronByType('MINUTELY'); 
                $cron_hourly = Cron::newInstance()->getCronByType('HOURLY'); 
                $cron_daily = Cron::newInstance()->getCronByType('DAILY'); 
                $cron_weekly = Cron::newInstance()->getCronByType('WEEKLY'); 
                $cron_monthly = Cron::newInstance()->getCronByType('MONTHLY'); 
                $cron_yearly = Cron::newInstance()->getCronByType('YEARLY'); 
              ?>

              <p>
                <span class="type-id"><?php _e('Minutely'); ?>:</span>
                <span class="last"><?php echo (@$cron_minutely['d_last_exec'] != null ? osc_format_date($cron_minutely['d_last_exec'], osc_date_format() . ' ' . osc_time_format()) : __('Never')); ?> / </span>
                <span class="next"><?php echo (@$cron_minutely['d_next_exec'] != null ? osc_format_date($cron_minutely['d_next_exec'], osc_date_format() . ' ' . osc_time_format()) : __('Never')); ?></span>
              </p>
              
              <p>
                <span class="type-id"><?php _e('Hourly'); ?>:</span>
                <span class="last"><?php echo (@$cron_hourly['d_last_exec'] != null ? osc_format_date($cron_hourly['d_last_exec'], osc_date_format() . ' ' . osc_time_format()) : __('Never')); ?> / </span>
                <span class="next"><?php echo (@$cron_hourly['d_next_exec'] != null ? osc_format_date($cron_hourly['d_next_exec'], osc_date_format() . ' ' . osc_time_format()) : __('Never')); ?></span>
              </p>
            
              <p>
                <span class="type-id"><?php _e('Daily'); ?>:</span>
                <span class="last"><?php echo (@$cron_daily['d_last_exec'] != null ? osc_format_date($cron_daily['d_last_exec'], osc_date_format() . ' ' . osc_time_format()) : __('Never')); ?> / </span>
                <span class="next"><?php echo (@$cron_daily['d_next_exec'] != null ? osc_format_date($cron_daily['d_next_exec'], osc_date_format() . ' ' . osc_time_format()) : __('Never')); ?></span>
              </p>

              <p>
                <span class="type-id"><?php _e('Weekly'); ?>:</span>
                <span class="last"><?php echo (@$cron_weekly['d_last_exec'] != null ? osc_format_date($cron_weekly['d_last_exec'], osc_date_format() . ' ' . osc_time_format()) : __('Never')); ?> / </span>
                <span class="next"><?php echo (@$cron_weekly['d_next_exec'] != null ? osc_format_date($cron_weekly['d_next_exec'], osc_date_format() . ' ' . osc_time_format()) : __('Never')); ?></span>
              </p>
              
              <p>
                <span class="type-id"><?php _e('Monthly'); ?>:</span>
                <span class="last"><?php echo (@$cron_monthly['d_last_exec'] != null ? osc_format_date($cron_monthly['d_last_exec'], osc_date_format() . ' ' . osc_time_format()) : __('Never')); ?> / </span>
                <span class="next"><?php echo (@$cron_monthly['d_next_exec'] != null ? osc_format_date($cron_monthly['d_next_exec'], osc_date_format() . ' ' . osc_time_format()) : __('Never')); ?></span>
              </p>
              
              <p>
                <span class="type-id"><?php _e('Yearly'); ?>:</span>
                <span class="last"><?php echo (@$cron_yearly['d_last_exec'] != null ? osc_format_date($cron_yearly['d_last_exec'], osc_date_format() . ' ' . osc_time_format()) : __('Never')); ?> / </span>
                <span class="next"><?php echo (@$cron_yearly['d_next_exec'] != null ? osc_format_date($cron_yearly['d_next_exec'], osc_date_format() . ' ' . osc_time_format()) : __('Never')); ?></span>
              </p>
            </div>
          </div>

          
          <h2 class="render-title separate-top"><?php _e('Software updates'); ?></h2>

          <div class="form-row">
            <div class="form-label"><?php _e('OsclassPoint API Key'); ?></div>
            <div class="form-controls">
              <input type="password" class="xlarge" name="osclasspoint_api_key" value="<?php echo osc_esc_html(osc_update_api_key()); ?>"/>

              <?php if(osc_update_api_key() <> '') { ?>
                <a class="btn btn-mini validate" href="<?php echo osc_admin_base_url(true); ?>?page=settings&action=validate_api_key"><i class="fa fa-check"></i> <span><?php _e('Validate'); ?></span></a>
              <?php } else { ?>
                <a class="btn btn-mini validate disabled" disabled href="#" onclick="return false;" title="<?php echo osc_esc_html(__('Enter and save your api key before you validate it')); ?>"><i class="fa fa-check"></i> <span><?php _e('Validate'); ?></span></a>
              <?php } ?>

              <span class="help-box" style="padding-bottom:0;"><?php echo sprintf(__('You can find your API key at %s section'), '<a href="https://osclasspoint.com/user/profile" target="_blank">' . __('OsclassPoint > Account > My profile') . '</a>'); ?></span>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Core updates'); ?></div>
            <div class="form-controls">
              <select name="auto_update[]" id="auto_update_core">
                <option value="disabled" ><?php _e('Disabled'); ?></option>
                <option value="branch" <?php if(strpos(osc_auto_update(),'branch')!==false) { ?>selected="selected"<?php } ?>><?php _e('New branch (new osclass version - 4.x.x) + Major + Minor'); ?></option>
                <option value="major" <?php if(strpos(osc_auto_update(),'major')!==false) { ?>selected="selected"<?php } ?>><?php _e('Major updates (new features - x.4.x) + Minor'); ?></option>
                <option value="minor" <?php if(strpos(osc_auto_update(),'minor')!==false) { ?>selected="selected"<?php } ?>><?php _e('Minor updates (bug fixes - x.x.4)'); ?></option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Plugin updates'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo ((strpos(osc_auto_update(),'plugins')!==false) ? 'checked="checked"' : ''); ?> name="auto_update[]" value="plugins"/>
                  <?php _e('Allow auto-updates of plugins'); ?>
                </label>
              </div>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Theme updates'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo ((strpos(osc_auto_update(),'themes')!==false) ? 'checked="checked"' : ''); ?> name="auto_update[]" value="themes"/>
                  <?php _e('Allow auto-updates of themes'); ?>
                </label>
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Language updates'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo ((strpos(osc_auto_update(),'languages')!==false) ? 'checked="checked"' : ''); ?> name="auto_update[]" value="languages"/>
                  <?php _e('Allow auto-updates of languages'); ?>
                </label>
              </div>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Content folder updates'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo (osc_update_occontent() ? 'checked="checked"' : ''); ?> name="update_include_occontent"/>
                  <?php _e('Allow updates of oc-content folder'); ?>
                </label>
              </div>
              
              <span class="help-box"><?php _e('Include update of default theme (sigma), languages folder (replace en_US) etc. Applicable for manual & auto update.'); ?></span>
            </div>
          </div>

          <?php if(1==2) { ?>
          <div class="form-row">
            <div class="form-label"><?php _e('Market external sources'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo (osc_market_external_sources() ? 'checked="checked"' : ''); ?> name="market_external_sources"/>
                  <?php _e('Allow updates and installations of non-official plugins and themes'); ?>
                </label>
              </div>
            </div>
          </div>
          <?php } ?>

          <div class="form-row">
            <div class="form-label"></div>
            <div class="form-controls">
              <?php 
                if(osc_get_preference('last_version_check') > 0) { 
                echo sprintf(__('Last checked on %s'), date('Y-m-d H:i:s', osc_get_preference('last_version_check'))); 
                } else {
                echo __('Never checked');
                }
              ?> 
              <a class="btn btn-mini check" href="<?php echo osc_admin_base_url(true); ?>?page=settings&action=check_updates"><?php _e('Check updates');?></a>
            </div>
          </div>
          

          <h2 class="render-title separate-top"><?php _e('Logging Settings'); ?></h2>
          <div class="form-row">
            <div class="form-label"><?php _e('Logs'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo (osc_logging_enabled() ? 'checked="checked"' : ''); ?> name="logging_enabled" value="1"/>
                  <?php _e('Enable logs'); ?>
                </label>
              </div>
              
              <span class="help-box"><?php _e('Osclass automatically generates logs for various actions, including publishing items, editing items, updating user profiles, and more.'); ?></span>
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Cleanup'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo (osc_logging_auto_cleanup() ? 'checked="checked"' : ''); ?> name="logging_auto_cleanup" value="1"/>
                  <?php _e('Automatically purge outdated logs'); ?>
                </label>
              </div>
              
              <span class="help-box"><?php _e('Based on your retention policy, old logs are automatically purged daily by a cron job.'); ?></span>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Retention policy'); ?></div>
            <div class="form-controls has-intext-input">
              <?php _e('Remove logs after'); ?>
              <input type="number" class="input-small in-text" name="logging_months" min=1 max=120 value="<?php echo osc_esc_html(osc_logging_months()); ?>"/>
              <?php _e('months'); ?>
            </div>
          </div>
          
          
          
          <div class="clear"></div>
          
          <div class="form-actions">
            <input type="submit" id="save_changes" value="<?php echo osc_esc_html(__('Save changes')); ?>" class="btn btn-submit"/>
          </div>
          
        </div>
      </fieldset>
    </form>
  </div>
  <!-- /settings form -->
</div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>