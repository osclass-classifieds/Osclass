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


// meta tag robots
osc_add_hook('header','sigma_nofollow_construct');

sigma_add_body_class('contact');
osc_enqueue_script('jquery-validate');
osc_current_web_theme_path('header.php');
?>
<div class="form-container form-horizontal form-container-box">
  <div class="header">
    <h1><?php _e('Contact us', 'sigma'); ?></h1>
  </div>
  
  <div class="resp-wrapper">
    <ul id="error_list"></ul>
    
    <form name="contact_form" action="<?php echo osc_base_url(true); ?>" method="post" <?php if(osc_contact_attachment()) { ?>enctype="multipart/form-data"<?php } ?>>
      <input type="hidden" name="page" value="contact" />
      <input type="hidden" name="action" value="contact_post" />
      
      <div class="control-group">
        <label class="control-label" for="yourName"><?php _e('Your name', 'sigma'); ?> (<?php _e('optional', 'sigma'); ?>)</label>
        <div class="controls"><?php ContactForm::your_name(); ?></div>
      </div>
      
      <div class="control-group">
        <label class="control-label" for="yourEmail"><?php _e('Your email address', 'sigma'); ?></label>
        <div class="controls"><?php ContactForm::your_email(); ?></div>
      </div>
      
      <div class="control-group">
        <label class="control-label" for="subject"><?php _e('Subject', 'sigma'); ?> (<?php _e('optional', 'sigma'); ?>)</label>
        <div class="controls"><?php ContactForm::the_subject(); ?></div>
      </div>
      
      <div class="control-group">
        <label class="control-label" for="message"><?php _e('Message', 'sigma'); ?></label>
        <div class="controls textarea"><?php ContactForm::your_message(); ?></div>
      </div>
      
      <?php if(osc_contact_attachment()) { ?>
        <div class="control-group">
          <label class="control-label" for="attachment"><?php _e('Attachment', 'sigma'); ?></label>
          <div class="controls file"><?php ContactForm::your_attachment(); ?></div>
        </div>
      <?php } ?>
      
      <div class="control-group">
          <div class="controls">
            <?php osc_run_hook('contact_form'); ?>
            <?php osc_show_recaptcha(); ?>
            
            <button type="submit" class="btn btn-primary"><?php _e("Send", 'sigma');?></button>
            
            <?php osc_run_hook('admin_contact_form'); ?>
          </div>
      </div>
    </form>
    
    <?php ContactForm::js_validation(); ?>
  </div>
</div>

<?php osc_current_web_theme_path('footer.php') ; ?>