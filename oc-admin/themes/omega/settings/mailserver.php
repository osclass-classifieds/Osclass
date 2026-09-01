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


//customize Head
function customHead() { ?>
  <script type="text/javascript">
    jQuery(document).ready(function(){
      $('select[name="mailserver_type"]').bind('change', function(){
        if( $(this).val() == 'gmail' ) {
          $('input[name="mailserver_host"]').val('smtp.gmail.com');
          $('input[name="mailserver_host"]').attr('readonly', true);
          $('input[name="mailserver_port"]').val('465');
          $('input[name="mailserver_port"]').attr('readonly', true);
          $('input[name="mailserver_username"]').val('');
          $('input[name="mailserver_password"]').val('');
          $('input[name="mailserver_ssl"]').val('ssl');
          $('input[name="mailserver_auth"]').prop('checked', true);
          $('input[name="mailserver_pop"]').prop('checked', false);
        } else {
          $('input[name="mailserver_host"]').attr('readonly', false);
          $('input[name="mailserver_port"]').attr('readonly', false);
        }
      });

      $('#btn-test-it2').bind('click', function(e) {
        e.preventDefault();
        var btn = $(this);

        // $.ajax({
          // "url": "<?php echo osc_admin_base_url(true)?>?page=ajax&action=test_mail",
          // "dataType": 'json',
          // success: function(data) {
            // alert(data.html);
          // }
        // });
        
        btn.addClass('loading');

        $.ajax({
          url: "<?php echo osc_admin_base_url(true)?>?page=ajax&action=test_mail",
          dataType: 'json',
          timeout: 30000,     // 30 seconds
          success: function(data) {
            if(typeof data === 'object' && data !== null && data.hasOwnProperty('html')) {
              console.log("SUCCESS: ", data);   // Log the data for debugging
              alert(data.html);
              
            } else {
              // Data is not in the expected format
              console.error("FAILURE: ", data);
              alert("<?php echo osc_esc_html(__('Error: Received unexpected data from the server. Please check the server response.')); ?>");
            }
            
            btn.removeClass('loading');
            
          },
          error: function(jqXHR, textStatus, errorThrown) {
            // Error: Handle AJAX errors
            console.error("FAILURE: ", textStatus, errorThrown, jqXHR);
            let errorMessage = "[" + jqXHR.status + "][" + textStatus + "] ";
            errorMessage += "<?php echo osc_esc_html(__('An error occurred while processing your request. Please try again later.')); ?>";
            errorMessage += "\n------------------------------------------------------\n" + errorThrown;
            errorMessage += "\n------------------------------------------------------\n" + jqXHR.responseText;

            alert(errorMessage);
            btn.removeClass('loading');
          }
        });


      });
    });
  </script>
  <?php
}

osc_add_hook('admin_header','customHead', 10);


function render_offset() {
  return 'row-offset';
}


function addHelp() {
  echo '<p>' . __("Modify the settings of the mail server from which your site's emails are sent. <strong>Be careful</strong>: these settings can vary depending on your hosting or server. If you run into any issues, check your hosting's help section.") . '</p>';
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
  return sprintf(__('Mail Settings - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

osc_current_admin_theme_path( 'parts/header.php' ); 
?>

<div id="mail-setting">
  <!-- settings form -->
    <div id="mail-settings">
      <h2 class="render-title"><?php _e('Mail Settings'); ?></h2>
      <ul id="error_list"></ul>
      <form name="settings_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
        <input type="hidden" name="page" value="settings" />
        <input type="hidden" name="action" value="mailserver_post" />
        <fieldset>
        <div class="form-horizontal">
        <div class="form-row">
          <div class="form-label"><?php _e('Server type'); ?></div>
          <div class="form-controls">
            <select name="mailserver_type">
              <option value="custom" <?php echo (osc_mailserver_type() == 'custom') ? 'selected="true"' : ''; ?>><?php _e('Custom Server'); ?></option>
              <option value="gmail" <?php echo (osc_mailserver_type() == 'gmail') ? 'selected="true"' : ''; ?>><?php _e('GMail Server'); ?></option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('Hostname'); ?></div>
          <div class="form-controls">
            <input type="text" class="input-large" name="mailserver_host" value="<?php echo osc_esc_html(osc_mailserver_host()); ?>" />
          </div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('Mail from'); ?></div>
          <div class="form-controls">
            <input type="text" class="input-large" name="mailserver_mail_from" value="<?php echo osc_esc_html(osc_mailserver_mail_from()); ?>" />
          </div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('Name from'); ?></div>
          <div class="form-controls">
            <input type="text" class="input-large" name="mailserver_name_from" value="<?php echo osc_esc_html(osc_mailserver_name_from()); ?>" />
          </div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('Server port'); ?></div>
          <div class="form-controls">
            <input type="text" class="input-large" name="mailserver_port" value="<?php echo osc_esc_html(osc_mailserver_port()); ?>" />
          </div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('Username'); ?></div>
          <div class="form-controls">
            <input type="text" class="input-large" name="mailserver_username" value="<?php echo osc_esc_html(osc_mailserver_username()); ?>" />
          </div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('Password'); ?></div>
          <div class="form-controls">
            <input type="password" class="input-large" name="mailserver_password" value="<?php echo osc_esc_html(osc_mailserver_password()); ?>" />
          </div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('Encryption'); ?></div>
          <div class="form-controls">
            <input type="text" class="input-medium" name="mailserver_ssl" value="<?php echo osc_esc_html(osc_mailserver_ssl()); ?>" />
            <?php _e('Options: blank, ssl or tls'); ?>

            <?php if( php_sapi_name() == 'cgi-fcgi' || php_sapi_name() == 'cgi' ) { ?>
              <div class="flashmessage flashmessage-inline flashmessage-warning">
                <p><?php _e("Cannot be sure that Apache Module <b>mod_ssl</b> is loaded."); ?></p>
              </div>
            <?php } else if( !@apache_mod_loaded('mod_ssl') ) { ?>
              <div class="flashmessage flashmessage-inline flashmessage-warning">
                <p><?php _e("Apache Module <b>mod_ssl</b> is not loaded, SMTP authentication may not be functional"); ?></p>
              </div>
            <?php } ?>
          </div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('SMTP'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox"><input type="checkbox" <?php echo ( osc_mailserver_auth() ? 'checked="checked"' : '' ); ?> name="mailserver_auth" value="1" />
            <?php _e('SMTP authentication enabled'); ?></div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-label"><?php _e('POP'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox"><input type="checkbox" <?php echo ( osc_mailserver_pop() ? 'checked="checked"' : '' ); ?> name="mailserver_pop" value="1" />
            <?php _e('Use POP before SMTP'); ?></div>
          </div>
        </div>

        <div class="form-row separate-top">
          <div class="form-label"><?php _e('Click to test'); ?></div>
          <div class="form-controls">
            <button id="btn-test-it2" class="btn" title="<?php echo osc_esc_html(osc_contact_email()); ?>">
              <i class="fa fa-spinner fa-spin"></i>
              <span><?php _e('Send test email'); ?></span>
            </button>
          </div>
        </div>


        <div class="form-actions">
          <input type="submit" id="save_changes" value="<?php echo osc_esc_html( __('Save changes') ); ?>" class="btn btn-submit" />
        </div>
      </div>
      </fieldset>
    </form>
  </div>
  <!-- /settings form -->
</div>
<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>