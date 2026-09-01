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
  echo '<p>' . __("Save the searches users do on your site. In this way, you can get information on what they're most interested in. From here, you can manage the options on how much information you want to save.") . '</p>';
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
  return sprintf(__('Latest searches Settings - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path('parts/header.php'); 
?>

<div id="general-setting">
  <!-- settings form -->
  <div id="general-settings">
    <h2 class="render-title"><?php _e('Latest searches Settings'); ?></h2>
      <ul id="error_list"></ul>
      <form name="searches_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
        <input type="hidden" name="page" value="settings"/>
        <input type="hidden" name="action" value="latestsearches_post"/>
        
        <fieldset>
          <div class="form-horizontal">
          <div class="form-row">
            <div class="form-label"><?php _e('Latest searches'); ?></div>
            <div class="form-controls">
              <div class="form-label-checkbox">
              <input type="checkbox" <?php echo (osc_save_latest_searches()) ? 'checked="checked"' : ''; ?> name="save_latest_searches" />
              <?php _e('Save the latest user searches'); ?>
              <div class="help-box"><?php _e('It may be useful to know what queries users make.') ?></div>
            </div>
            </div>
          </div>
          
          <div class="form-row row-latest-radio">
            <div class="form-label"><?php _e('How long queries are stored'); ?></div>
            <div class="form-controls">
              <div>
                <input type="radio" name="purge_searches" value="hour" <?php echo ((osc_purge_latest_searches() == 'hour') ? 'checked="checked"' : ''); ?> onclick="javascript:document.getElementById('customPurge').value = 'hour';" />
                <?php _e('One hour'); ?>
              </div>
              
              <div>
                <input type="radio" name="purge_searches" value="day" <?php echo ((osc_purge_latest_searches() == 'day') ? 'checked="checked"' : ''); ?> onclick="javascript:document.getElementById('customPurge').value = 'day';" />
                <?php _e('One day'); ?>
              </div>
              
              <div>
                <input type="radio" name="purge_searches" value="week" <?php echo ((osc_purge_latest_searches() == 'week') ? 'checked="checked"' : ''); ?> onclick="javascript:document.getElementById('customPurge').value = 'week';" />
                <?php _e('One week'); ?>
              </div>

              <div>
                <input type="radio" name="purge_searches" value="month" <?php echo ((osc_purge_latest_searches() == 'month') ? 'checked="checked"' : ''); ?> onclick="javascript:document.getElementById('customPurge').value = 'month';" />
                <?php _e('One month'); ?>
              </div>
              
              <div>
                <input type="radio" name="purge_searches" value="year" <?php echo ((osc_purge_latest_searches() == 'year') ? 'checked="checked"' : ''); ?> onclick="javascript:document.getElementById('customPurge').value = 'year';" />
                <?php _e('One year'); ?>
              </div>
              
              <div>
                <input type="radio" name="purge_searches" value="forever" <?php echo ((osc_purge_latest_searches() == 'forever') ? 'checked="checked"' : ''); ?> onclick="javascript:document.getElementById('customPurge').value = 'forever';" />
                <?php _e('Forever'); ?>
              </div>
              
              <div>
                <input type="radio" name="purge_searches" value="1000" <?php echo ((osc_purge_latest_searches() == '1000') ? 'checked="checked"' : ''); ?> onclick="javascript:document.getElementById('customPurge').value = '1000';" />
                <?php _e('Store 1000 queries'); ?>
              </div>
              
              <div>
                <input type="radio" name="purge_searches" id="purge_searches" value="custom" <?php echo (!in_array(osc_purge_latest_searches(), array('hour', 'day', 'week', 'forever', '1000')) ? 'checked="checked"' : ''); ?> />
                <?php printf(__('Store %s queries'), '<input name="custom_queries" id="custom_queries" type="text" class="input-small" ' . (!in_array(osc_purge_latest_searches(), array('hour', 'day', 'week', 'forever', '1000')) ? 'value="' . osc_esc_html(osc_purge_latest_searches()) . '"' : '') . ' onkeyup="javascript:document.getElementById(\'customPurge\').value = this.value;" />'); ?>
                <div class="help-box">
                  <?php _e("This feature can generate a lot of data. It's recommended to purge this data periodically."); ?>
                </div>
              </div>
              <input type="hidden" id="customPurge" name="customPurge" value="<?php echo osc_esc_html(osc_purge_latest_searches()); ?>" />

            </div>
          </div>

          <h2 class="render-title separate-top"><?php _e('Words Restriction Settings'); ?></h2>
          <div class="form-row">
            <div class="form-label"><?php _e('Restrict mode'); ?></div>
            <div class="form-controls">
              <select name="latest_searches_restriction" id="latest_searches_restriction">
                <option value="0" <?php if(osc_latest_searches_restriction() == 0) { ?>selected="selected"<?php } ?>><?php _e('None'); ?></option>
                <option value="1" <?php if(osc_latest_searches_restriction() == 1) { ?>selected="selected"<?php } ?>><?php _e('Use as banned words'); ?></option>
                <option value="2" <?php if(osc_latest_searches_restriction() == 2) { ?>selected="selected"<?php } ?>><?php _e('Use as whitelisted words'); ?></option>
              </select>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-label"><?php _e('Restricted words'); ?></div>
            <div class="form-controls">
              <textarea name="latest_searches_words" id="latest_searches_words"><?php echo osc_latest_searches_words(); ?></textarea>
              
              <div class="help-box">
                <?php _e('Delimit words by comma. Words are not case sensitive. Extra white space is considered in pattern as well.'); ?>
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

<div id="clean-settings" style="margin-top:30px;">
  <div class="form-horizontal">
    <h2 class="render-title"><?php _e('Clean up latest searches'); ?></h2>

    <div class="form-row">
      <p><?php _e('Latest searches are cleaned from database using Cron.'); ?></p>
      <p><?php echo sprintf(__('You have currently %s search patterns stored, you should not have more than 50k, otherwise it may slow down performance of your website.'), '<strong>' . LatestSearches::newInstance()->countAllSearches() . '</strong>'); ?></p>
      <p><a class="btn" href="<?php echo osc_admin_base_url(true) . '?page=settings&action=latestsearches_clean&' . osc_csrf_token_url(); ?>"><?php  _e('Clean up latest searches'); ?></a></p>
      <p>&nbsp;</p>
    </div>
  </div>
</div>

<?php osc_current_admin_theme_path('parts/footer.php'); ?>