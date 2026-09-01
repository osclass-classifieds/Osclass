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

    sigma_add_body_class('recover');
    osc_current_web_theme_path('header.php');
?>
<div class="form-container form-horizontal form-container-box">
    <div class="header">
        <h1><?php _e('Recover your password', 'sigma'); ?></h1>
    </div>
    <div class="resp-wrapper">
        <form action="<?php echo osc_base_url(true); ?>" method="post" >
        <input type="hidden" name="page" value="login" />
        <input type="hidden" name="action" value="recover_post" />
        <div class="control-group">
            <label class="control-label" for="email"><?php _e('E-mail', 'sigma'); ?></label>
            <div class="controls">
                <?php UserForm::email_text(); ?>
                <?php osc_run_hook('user_recover_form'); ?>
                <?php osc_show_recaptcha('recover_password'); ?>
            </div>
        </div>
            <div class="control-group">
                <div class="controls">
                    <button type="submit" class="btn btn-primary"><?php _e("Send me a new password", 'sigma');?></button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php osc_current_web_theme_path('footer.php') ; ?>