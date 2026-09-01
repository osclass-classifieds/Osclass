<?php
if (!defined('OC_ADMIN')) exit('Direct access is not allowed.');
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

$current_host = parse_url(Params::getServerParam('HTTP_HOST'), PHP_URL_HOST);
if ($current_host === null) {
  $current_host = Params::getServerParam('HTTP_HOST');
}

//customize Head
function customHead() {}

osc_add_hook('admin_header', 'customHead', 10);

function render_offset() {
  return 'row-offset';
}

function addHelp() {
  echo '<p>' . __("Change advanced configuration of your Osclass. <strong>Be careful</strong> when modifying default values if you're not sure what you're doing!") . '</p>';
}

osc_add_hook('help_box', 'addHelp');

osc_add_hook('admin_page_header', 'customPageHeader');

function customPageHeader() {
  ?>
  <h1><?php _e('Settings'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

$ip_service = osc_ipdata_service_map('ALL', osc_get_ip());

function customPageTitle($string) {
  return sprintf(__('Advanced Settings - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php');
?>

<script type="text/javascript">
$(document).ready(function() {
  $('body').on('change', 'select#e_type', function() {
    if($(this).val() != 'country') {
      $('div.sd-country').slideUp(200); 
    } else {
      $('div.sd-country').slideDown(200); 
    }
    
    if($(this).val() != 'language') {
      $('div.sd-language').slideUp(200); 
    } else {
      $('div.sd-language').slideDown(200); 
    }
  });
});
</script>

<style>
<?php if(osc_subdomain_type() != 'country') { ?> 
div.sd-country, .form-row.sd-country {display:none;}
<?php } else if(osc_subdomain_type() != 'language') { ?> 
div.sd-language, .form-row.sd-language {display:none;}
<?php } ?>
</style>

<div id="general-setting">
  <!-- settings form -->
  <div id="general-settings">
    <?php
      $cache_type = Object_Cache_Factory::newInstance()->_get_cache();
      if( $cache_type != 'default' ) { ?>
      
      <!--  Cache flush  -->
      <h2 class="render-title"><?php _e('Flush cache'); ?></h2>
      <form id="cache_flush" name="cache_flush" action="<?php echo osc_admin_base_url(true); ?>" method="post">
        <input type="hidden" name="page" value="settings" />
        <input type="hidden" name="action" value="advanced_cache_flush" />
        <fieldset>
          <div class="form-horizontal">
            <div class="form-row">
              <div class="form-label"><?php __('Flush cache'); ?></div>
              <div class="form-controls"><input type="submit" value="<?php echo osc_esc_html(__('Flush cache')); ?>" class="btn btn-submit" />
                <div class="help-box"><?php _e('Remove all data from cache.'); ?> <b><?php echo $cache_type; ?></b></div>
              </div>
            </div>
          </div>
        </fieldset>
      </form>
    <?php } ?>
    
    
    <h2 class="render-title"><?php _e('Subdomains Settings'); ?></h2>
    <ul id="error_list"></ul>
    <form name="settings_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
      <input type="hidden" name="page" value="settings" />
      <input type="hidden" name="action" value="advanced_post" />
      
      <fieldset>
        <div class="form-horizontal">
          <div class="form-row">
            <div class="form-label"><?php _e('Subdomain type'); ?></div>
            <div class="form-controls">
              <select name="e_type" id="e_type">
                <option value="" <?php if (osc_subdomain_type() == '') { ?>selected="selected"<?php } ?>><?php _e('No subdomains'); ?></option>
                <option value="category" <?php if (osc_subdomain_type() == 'category') { ?>selected="selected"<?php } ?>><?php _e('Category based'); ?></option>
                <option value="country" <?php if (osc_subdomain_type() == 'country') { ?>selected="selected"<?php } ?>><?php _e('Country based'); ?></option>
                <option value="region" <?php if (osc_subdomain_type() == 'region') { ?>selected="selected"<?php } ?>><?php _e('Region based'); ?></option>
                <option value="city" <?php if (osc_subdomain_type() == 'city') { ?>selected="selected"<?php } ?>><?php _e('City based'); ?></option>
                <option value="user" <?php if (osc_subdomain_type() == 'user') { ?>selected="selected"<?php } ?>><?php _e('User based'); ?></option>
                <option value="language" <?php if (osc_subdomain_type() == 'language') { ?>selected="selected"<?php } ?>><?php _e('Language based'); ?></option>
              </select>

              <div class="help-box"><?php _e('Subdomains for those does not exists related entry in database (based on slug) will return 404 page.'); ?></div>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Base host'); ?></div>
            <div class="form-controls"><input type="text" class="xlarge" name="s_host" value="<?php echo osc_esc_html(osc_subdomain_host()); ?>" />
              <div class="help-box">
                <div><?php _e('Your host is required to know the base domain of your subdomains.'); ?> <?php printf(__('Your current host is "%s". Add it without "www".'), $current_host); ?></div>
                <div><?php _e('If you have subdomains as sub1.domain.com, sub2.domain.com etc, then your base host is domain.com.'); ?></div>
                <div><?php _e('Remember to enable sharing of cookies & sessions for subdomains (in config, define COOKIE_DOMAIN equal to your base host).'); ?></div>
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-label"><?php _e('Landing page'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo ( osc_subdomain_landing_enabled() ? 'checked="checked"' : '' ); ?> name="b_landing" value="1" />
                  <?php _e('Turn website on top-domain (base host) to landing page'); ?>
                </label>
              </div>
              <div class="help-box"><?php _e('When disabled, website on top-domain (base host) is fully functional Osclass site.'); ?></div>
            </div>
          </div>

          <div class="form-row sd-language">
            <div class="form-label"><?php _e('Language slug type'); ?></div>
            <div class="form-controls">
              <select name="s_language_slug_type" id="s_language_slug_type">
                <option value="" <?php if (osc_subdomain_language_slug_type() == '') { ?>selected="selected"<?php } ?>><?php _e('Short format (xx)'); ?></option>
                <option value="LONG" <?php if (osc_subdomain_language_slug_type() == 'LONG') { ?>selected="selected"<?php } ?>><?php _e('Long format (xx-yy)'); ?></option>
              </select>

              <div class="help-box"><?php _e('If short type selected, make sure you do not have 2 languages starting with same code (ie. en_US & en_GB).'); ?></div>
            </div>
          </div>
          
          <div class="form-row sd-country">
            <div class="form-label"><?php _e('Automatic redirect'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
                <label>
                  <input type="checkbox" <?php echo ( osc_subdomain_redirect_enabled() ? 'checked="checked"' : '' ); ?> name="b_redirect" value="1" />
                  <?php _e('Automatically redirect user to subdomain based on it\'s IP location.'); ?>
                </label>
              </div>
              <div class="help-box">
                <div><?php _e('Check user country based on IP, max once per 24 hours if not found.'); ?></div>
                <div><?php _e('This service use https://www.geoplugin.com/ service that allows 120 free API calls per minute. If you need more calls, consider using premium service at https://www.geoplugin.com/premium.'); ?></div>
                <div><?php _e('Only available for country based subdomains.'); ?></div>
              </div>
            </div>
          </div>
          
          <div class="form-row sd-country">
            <div class="form-label"><?php _e('Restricted countries'); ?></div>
            <div class="form-controls">
              <input type="text" class="xlarge" name="s_restricted_ids" value="<?php echo osc_esc_html(osc_subdomain_restricted_ids()); ?>" />

              <div class="help-box">
                <div><?php _e('Enter country codes delimited by comma those will be restricted and only users from this country will be able to access related subdomains.'); ?></div>
                <div><?php _e('Country of user is identified based on it\'s IP address. If user country is not same as subdomain country and it\'s in restricted list, user is redirected to top-domain with error message.'); ?></div>
                <div><?php _e('Enter "all" to restrict all countries.'); ?></div>
                <div><?php _e('Only available for country based subdomains.'); ?></div>
              </div>
            </div>
          </div>

          <?php if(Params::getParam('ipdata') != 1) { ?>
            <div class="form-row sd-country">
              <div class="form-label"><?php _e('Geo service (IP data)'); ?></div>
              <div class="form-controls">
                <a class="btn" href="<?php echo osc_admin_base_url(true); ?>?page=settings&action=advanced&ipdata=1"><?php _e('Click to retrieve IP data'); ?></a>
                <div class="help-box"><?php echo sprintf(__('You are currently using %s service.'), $ip_service['service']); ?> (<a href="<?php echo $ip_service['url']; ?>" target="_blank"><?php echo $ip_service['url']; ?></a>)</div>
              </div>
            </div>
          <?php } else { ?>
          
            <div class="form-row">
              <div class="form-label"><?php _e('Your Geo service response:'); ?></div>
              <div class="form-controls" style="width:100%;">
                <pre class="code" style="font-size:11px;margin:0;max-height:260px;overflow-y:auto;width:90%;"><?php echo osc_esc_html(json_encode(osc_user_country_from_ip(true), JSON_PRETTY_PRINT)); ?></pre>
                <div class="help-box"><?php echo sprintf(__('You are currently using %s service.'), $ip_service['service']); ?> (<a href="<?php echo $ip_service['url']; ?>" target="_blank"><?php echo $ip_service['url']; ?></a>)</div>
              </div>
            </div>
          <?php } ?>
          
          <div class="clear"></div>
          
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