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
  echo '<p>' . __('Enter your custom CSS code to modify design of your theme. Code will not be affected by theme or osclass updates. Code will be inserted into footer between &lt;style&gt;&lt;/style&gt; tags.') . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader(){ 
  ?>
  <h1><?php _e('Appearance'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Customization - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');


osc_current_admin_theme_path('parts/header.php'); 
?>

<div id="customization-setting">
  <!-- settings form -->
  <div id="customization-settings" class="form-horizontal">
    <h2 class="render-title"><?php _e('Theme Customization'); ?></h2>
    <ul id="error_list"></ul>
    <form name="settings_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
      <input type="hidden" name="page" value="appearance" />
      <input type="hidden" name="action" value="customization_update" />
      <fieldset>
        <div class="form-horizontal">
          <div class="form-row">
            <div class="form-label"><?php _e('Your Custom CSS'); ?></div>
            <div class="form-controls css">
              <textarea type="text" class="" name="customCss"><?php echo osc_get_preference('custom_css'); ?></textarea>
              <span class="help-box"><?php _e('Do not enter &lt;style&gt;&lt;/style&gt; tags. You may check following CSS guide:'); ?> <a target="_blank" ref="noopener norefer nofollow" href="https://www.w3schools.com/css/">https://www.w3schools.com/css/</a></span>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Custom CSS Hook'); ?></div>
          <div class="form-controls">
            <select name="customCssHook">
              <option value="" <?php if(osc_get_preference('custom_css_hook') == '') { ?>selected="selected"<?php } ?>><?php _e('Footer'); ?></option>
              <option value="HEADER" <?php if(osc_get_preference('custom_css_hook') == 'HEADER') { ?>selected="selected"<?php } ?>><?php _e('Header'); ?></option>
              <option value="BEFORE_HTML" <?php if(osc_get_preference('custom_css_hook') == 'BEFORE_HTML') { ?>selected="selected"<?php } ?>><?php _e('Before HTML'); ?></option>
              <option value="AFTER_HTML" <?php if(osc_get_preference('custom_css_hook') == 'AFTER_HTML') { ?>selected="selected"<?php } ?>><?php _e('After HTML'); ?></option>
              <option value="NO_HOOK" <?php if(osc_get_preference('custom_css_hook') == 'NO_HOOK') { ?>selected="selected"<?php } ?>><?php _e('Do not hook'); ?></option>
            </select>
            
            <span class="help-box"><?php _e('Select in which part of page body is CSS included to your site.'); ?></span>
          </div>
        </div>
        
        <hr/>
          
        <div class="form-horizontal">
          <div class="form-row">
            <div class="form-label"><?php _e('Your Custom HTML code'); ?></div>
            <div class="form-controls html">
              <textarea type="text" class="" name="customHtml"><?php echo osc_get_preference('custom_html'); ?></textarea>
              <span class="help-box"><?php _e('You may enter any HTML or JavaScript code. Code will be added into footer. Do not add PHP code.'); ?></span>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Custom HTML Hook'); ?></div>
          <div class="form-controls">
            <select name="customHtmlHook">
              <option value="" <?php if(osc_get_preference('custom_html_hook') == '') { ?>selected="selected"<?php } ?>><?php _e('Footer'); ?></option>
              <option value="HEADER" <?php if(osc_get_preference('custom_html_hook') == 'HEADER') { ?>selected="selected"<?php } ?>><?php _e('Header'); ?></option>
              <option value="BEFORE_HTML" <?php if(osc_get_preference('custom_html_hook') == 'BEFORE_HTML') { ?>selected="selected"<?php } ?>><?php _e('Before HTML'); ?></option>
              <option value="AFTER_HTML" <?php if(osc_get_preference('custom_html_hook') == 'AFTER_HTML') { ?>selected="selected"<?php } ?>><?php _e('After HTML'); ?></option>
              <option value="NO_HOOK" <?php if(osc_get_preference('custom_html_hook') == 'NO_HOOK') { ?>selected="selected"<?php } ?>><?php _e('Do not hook'); ?></option>
            </select>
            
            <span class="help-box"><?php _e('Select in which part of page body is HTML included to your site.'); ?></span>
          </div>
        </div>
        
        <hr/>

        <div class="form-horizontal">
          <div class="form-row">
            <div class="form-label"><?php _e('Your Custom JavaScript'); ?></div>
            <div class="form-controls css">
              <textarea type="text" class="" name="customJs"><?php echo osc_get_preference('custom_js'); ?></textarea>
              <span class="help-box"><?php _e('Do not enter &lt;script&gt;&lt;/script&gt; tags. You may check following JS guide:'); ?> <a target="_blank" ref="noopener norefer nofollow" href="https://www.w3schools.com/js/">https://www.w3schools.com/js/</a></span>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-label"><?php _e('Custom JavaScript Hook'); ?></div>
          <div class="form-controls">
            <select name="customJsHook">
              <option value="" <?php if(osc_get_preference('custom_js_hook') == '') { ?>selected="selected"<?php } ?>><?php _e('Footer'); ?></option>
              <option value="HEADER" <?php if(osc_get_preference('custom_js_hook') == 'HEADER') { ?>selected="selected"<?php } ?>><?php _e('Header'); ?></option>
              <option value="BEFORE_HTML" <?php if(osc_get_preference('custom_js_hook') == 'BEFORE_HTML') { ?>selected="selected"<?php } ?>><?php _e('Before HTML'); ?></option>
              <option value="AFTER_HTML" <?php if(osc_get_preference('custom_js_hook') == 'AFTER_HTML') { ?>selected="selected"<?php } ?>><?php _e('After HTML'); ?></option>
              <option value="NO_HOOK" <?php if(osc_get_preference('custom_js_hook') == 'NO_HOOK') { ?>selected="selected"<?php } ?>><?php _e('Do not hook'); ?></option>
            </select>
            
            <span class="help-box"><?php _e('Select in which part of page body is JS included to your site.'); ?></span>
          </div>
        </div>
      </fieldset>

      <div class="clear"></div>
      
      <div class="form-actions">
        <input type="submit" id="save_changes" value="<?php echo osc_esc_html( __('Save changes') ); ?>" class="btn btn-submit" />
      </div>
    </form>
  </div>
</div>

<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>