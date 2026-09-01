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

?>
<div id="sidebar">
  <?php osc_run_hook('item_sidebar_top'); ?>
  
  <?php if(osc_price_enabled_at_items() && osc_item_category_price_enabled()) { ?><div class="price isDesktop isTablet"><?php echo osc_item_formated_price(); ?></div><?php } ?>

  <?php if(!osc_item_mark_disable() && (!osc_is_web_user_logged_in() || osc_logged_user_id()!=osc_item_user_id())) { ?>
    <form action="<?php echo osc_base_url(true); ?>" method="post" name="mask_as_form" id="mask_as_form">
      <input type="hidden" name="id" value="<?php echo osc_item_id(); ?>" />
      <input type="hidden" name="as" value="spam" />
      <input type="hidden" name="action" value="mark" />
      <input type="hidden" name="page" value="item" />
      <select name="as" id="as" class="mark_as">
          <option><?php _e("Mark as...", 'sigma'); ?></option>
          <option value="spam"><?php _e("Mark as spam", 'sigma'); ?></option>
          <option value="badcat"><?php _e("Mark as misclassified", 'sigma'); ?></option>
          <option value="repeated"><?php _e("Mark as duplicated", 'sigma'); ?></option>
          <option value="expired"><?php _e("Mark as expired", 'sigma'); ?></option>
          <option value="offensive"><?php _e("Mark as offensive", 'sigma'); ?></option>
      </select>
    </form>
  <?php } ?>

  <?php if( osc_get_preference('sidebar-300x250', 'sigma') != '') {?>
  <!-- sidebar ad 350x250 -->
  <div class="ads_300">
    <?php echo osc_get_preference('sidebar-300x250', 'sigma'); ?>
  </div>
  <!-- /sidebar ad 350x250 -->
  <?php } ?>

  <div id="contact" class="widget-box form-container form-vertical">
    <h2><?php _e("Contact publisher", 'sigma'); ?></h2>

    <?php if(osc_profile_img_users_enabled()) { ?>
      <p class="user-img">
        <img src="<?php echo osc_user_profile_img_url(osc_item_user_id()); ?>" alt="<?php echo osc_esc_html(osc_item_contact_name()); ?>"/>
      </p>
    <?php } ?>
    
    <?php if( osc_item_user_id() != null ) { ?>
      <p class="name bld"><span><?php _e('Name', 'sigma') ?>:</span> <a href="<?php echo osc_user_public_profile_url( osc_item_user_id() ); ?>" ><?php echo osc_item_contact_name(); ?></a> <?php echo (osc_user_is_online(osc_item_user_id()) ? '<span class="is-online">(' . __('online', 'sigma') . ')</span>' : ''); ?></p>
    <?php } else { ?>
      <p class="name bld"><span><?php _e('Name', 'sigma'); ?>:</span> <?php echo osc_item_contact_name(); ?></p>
    <?php } ?>

    <?php if( osc_item_show_email() ) { ?>
      <p class="email bld"><span><?php _e('E-mail', 'sigma'); ?>:</span> <a href="mailto:<?php echo osc_item_contact_email(); ?>"><?php echo osc_item_contact_email(); ?></a></p>
    <?php } ?>

    <?php if ( osc_item_contact_phone() != '' && osc_item_show_phone()) { ?>
      <p class="phone bld"><span><?php _e('Phone', 'sigma'); ?>:</span> <?php echo '<a href="tel:' . osc_item_contact_phone(true) . '">' . osc_item_contact_phone(false) . '</a>'; ?></p>
    <?php } ?>

    <?php if ( osc_item_contact_other() != '' ) { ?>
      <p class="other bld"><span><?php _e('Other', 'sigma'); ?>:</span> <?php echo osc_item_contact_other(); ?></p>
    <?php } ?>
    
    <a href="#contact-in" class="resp-toogle btn btn-secondary show-contact-btn"><?php _e('Contact seller', 'sigma'); ?></a>


    <div id="contact-in" class="fixed-layout">
      <div class="fixed-close"><i class="fas fa-times"></i></div>

      <?php if(osc_item_contact_form_disabled()) { ?>
        <!-- Contact form disabled -->
      <?php } else if( osc_item_is_expired () ) { ?>
        <p class="problem expired">
          <?php _e("The listing is expired. You can't contact the publisher.", 'sigma'); ?>
        </p>
      <?php } else if( ( osc_logged_user_id() == osc_item_user_id() ) && osc_logged_user_id() != 0 ) { ?>
        <p class="problem own">
          <?php _e("It's your own listing, you can't contact the publisher.", 'sigma'); ?>
        </p>
      <?php } else if( osc_reg_user_can_contact() && !osc_is_web_user_logged_in() ) { ?>
        <p class="problem unlogged">
          <?php _e("You must log in or register a new account in order to contact the advertiser", 'sigma'); ?>
        </p>

        <a href="<?php echo osc_user_login_url(); ?>" class="btn btn-secondary lgn"><?php _e('Login', 'sigma'); ?></a>

      <?php } else { ?>
        <ul id="error_list"></ul>
        <form action="<?php echo osc_base_url(true); ?>" method="post" name="contact_form" id="contact_form"<?php if(osc_item_attachment()) { ?> enctype="multipart/form-data"<?php } ?>>
          <?php osc_prepare_user_info(); ?>
           <input type="hidden" name="action" value="contact_post" />
            <input type="hidden" name="page" value="item" />
            <input type="hidden" name="id" value="<?php echo osc_item_id(); ?>" />
          <div class="control-group">
            <label class="control-label" for="yourName"><?php _e('Your name', 'sigma'); ?>:</label>
            <div class="controls"><?php ContactForm::your_name(); ?></div>
          </div>
          <div class="control-group">
            <label class="control-label" for="yourEmail"><?php _e('Your email address', 'sigma'); ?>:</label>
            <div class="controls"><?php ContactForm::your_email(); ?></div>
          </div>
          <div class="control-group">
            <label class="control-label" for="phoneNumber"><?php _e('Phone number', 'sigma'); ?> (<?php _e('optional', 'sigma'); ?>):</label>
            <div class="controls"><?php ContactForm::your_phone_number(); ?></div>
          </div>

          <div class="control-group">
            <label class="control-label" for="message"><?php _e('Message', 'sigma'); ?>:</label>
            <div class="controls textarea"><?php ContactForm::your_message(); ?></div>
          </div>

          <?php if(osc_item_attachment()) { ?>
            <div class="control-group">
              <label class="control-label" for="attachment"><?php _e('Attachment', 'sigma'); ?>:</label>
              <div class="controls"><?php ContactForm::your_attachment(); ?></div>
            </div>
          <?php }; ?>

          <div class="control-group">
            <div class="controls">
              <?php osc_run_hook('item_contact_form', osc_item_id()); ?>
              <?php osc_show_recaptcha(); ?>
              <button type="submit" class="btn btn-primary"><?php _e("Send", 'sigma');?></button>
            </div>
          </div>
        </form>
        <?php ContactForm::js_validation(); ?>
      <?php } ?>
    </div>
  </div>

  <?php osc_run_hook('item_contact'); ?>

  <div id="useful_info">
    <h2><?php _e('Useful information', 'sigma'); ?></h2>
    <ul>
      <li><?php _e('Avoid scams by acting locally or paying with PayPal', 'sigma'); ?></li>
      <li><?php _e('Never pay with Western Union, Moneygram or other anonymous payment services', 'sigma'); ?></li>
      <li><?php _e('Don\'t buy or sell outside of your country. Don\'t accept cashier cheques from outside your country', 'sigma'); ?></li>
      <li><?php _e('This site is never involved in any transaction, and does not handle payments, shipping, guarantee transactions, provide escrow services, or offer "buyer protection" or "seller certification"', 'sigma'); ?></li>
    </ul>
  </div>
  
  <?php osc_run_hook('item_sidebar_bottom'); ?>
</div><!-- /sidebar -->
