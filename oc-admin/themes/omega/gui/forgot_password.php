<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <meta name="robots" content="noindex, nofollow, noarchive"/>
  <meta name="googlebot" content="noindex, nofollow, noarchive"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
  <title><?php _e('Change your password'); ?> - <?php echo osc_page_title(); ?></title>
  <script type="text/javascript" src="<?php echo osc_assets_url('js/jquery.min.js'); ?>"></script>
  <link type="text/css" href="style/backoffice_login.css" media="screen" rel="stylesheet"/>
  <?php osc_run_hook('admin_login_header'); ?>
</head>

<body class="forgot login <?php echo implode(' ',osc_apply_filter('admin_body_class', array())); ?>">
  <div id="login-header">
    <a href="https://osclass-classifieds.com"><img src="<?php echo osc_current_admin_theme_url('images/osclass-icon.svg'); ?>" class="logoimg" alt="<?php osc_esc_html(__('Osclass')); ?>" /></a>
  </div>

  <div id="login-sub-header">
    <h1><a href="<?php echo osc_base_url(); ?>"><?php echo osc_apply_filter('admin_title', osc_page_title()); ?></a></h1>
  </div>

  <div id="login">
    <h2><?php _e('Change your password'); ?></h2>

    <?php osc_show_flash_message('admin'); ?>
    <div class="flashmessage"><?php _e('Type your new password'); ?></div>

    <form action="<?php echo osc_admin_base_url(true); ?>" method="post" >
      <input type="hidden" name="page" value="login"/>
      <input type="hidden" name="action" value="forgot_post"/>
      <input type="hidden" name="adminId" value="<?php echo Params::getParam('adminId', true); ?>"/>
      <input type="hidden" name="code" value="<?php echo Params::getParam('code', true); ?>"/>

      <p>
        <label for="new_password"><?php _e('New password'); ?></label>
        <input id="new_password" type="password" name="new_password" value="" autocomplete="new-password"/>
      </p>

      <p>
        <label for="new_password2"><?php _e('Repeat new password'); ?></label>
        <input id="new_password2" type="password" name="new_password2" value="" autocomplete="new-password"/>
      </p>

      <p class="">
        <button type="submit" name="submit" id="submit" class="btn btn-submit"><?php echo osc_esc_html( __('Change password')); ?></button>
      </p>
    </form>

    <p class="lastline">
      <a title="<?php _e('Log in'); ?>" href="<?php echo osc_admin_base_url(); ?>"><?php _e('Log in'); ?></a>
    </p>
  </div>

  <script type="text/javascript">
    $(document).ready(function(){
      $(".ico-close").click(function(){
        $(this).parent().hide();
      });

      $("#new_password").focus();
    });
  </script>
  
  <?php osc_run_hook('admin_login_footer'); ?>
</body>
</html>