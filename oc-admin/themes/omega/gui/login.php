<?php
$demo['name'] = '';
$demo['password'] = '';    

if((defined('DEMO_PLUGINS') && DEMO_PLUGINS === true) || (defined('DEMO_THEMES') && DEMO_THEMES === true) || (defined('DEMO') && DEMO === true)) {
  $demo_admin = Admin::newInstance()->findByUserName('demo');
  
  if($demo_admin !== false) {
    $demo['name'] = 'demo';
    $demo['password'] = 'demo123';    
  }
}
?>

<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
  <meta name="robots" content="noindex, nofollow, noarchive"/>
  <meta name="googlebot" content="noindex, nofollow, noarchive"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
  <title><?php _e('Log in'); ?> - <?php echo osc_page_title(); ?></title>
  <script type="text/javascript" src="<?php echo osc_assets_url('js/jquery.min.js'); ?>"></script>
  <link type="text/css" href="style/backoffice_login.css" media="screen" rel="stylesheet"/>
  <?php osc_run_hook('admin_login_header'); ?>
</head>

<body class="login <?php echo implode(' ',osc_apply_filter('admin_body_class', array())); ?>">
  <div id="login-header">
    <a href="https://osclass-classifieds.com"><img src="<?php echo osc_current_admin_theme_url('images/osclass-icon.svg'); ?>" class="logoimg" alt="<?php osc_esc_html(__('Osclass')); ?>" /></a>
  </div>

  <div id="login-sub-header">
    <h1><a href="<?php echo osc_base_url(); ?>"><?php echo osc_apply_filter('admin_title', osc_page_title()); ?></a></h1>
  </div>

  <div id="login">
    <?php osc_show_flash_message('admin'); ?>

    <form name="loginform" id="loginform" action="<?php echo osc_admin_base_url(true); ?>" method="post">
      <input type="hidden" name="page" value="login"/>
      <input type="hidden" name="action" value="login_post"/>


      <p>
        <label for="user_login"><?php _e('Username'); ?></label>
        <input type="text" name="user" id="user_login" class="input" value="<?php echo $demo['name']; ?>" size="20"/>
      </p>

      <p>
        <label for="user_pass"><?php _e('Password'); ?></label>

        <span class="pass-box">
          <input type="password" name="password" id="user_pass" class="input" value="<?php echo $demo['password']; ?>" size="20" autocomplete="off"/>
          <a href="#" class="toggle-pass" title="<?php echo osc_esc_html(__('Show/hide password')); ?>"><i class="fa fa-eye"></i></a>
        </span>
      </p>

      <?php $locales = osc_all_enabled_locales_for_admin(); ?>
      <?php if(count($locales) > 1) { ?>
        <p>
          <label for="user_language"><?php _e('Language'); ?></label>
          <select name="locale" id="user_language">
            <?php foreach($locales as $locale) { ?>
              <option value="<?php echo $locale ['pk_c_code']; ?>" <?php if(osc_admin_language() == $locale['pk_c_code']) echo 'selected="selected"'; ?>><?php echo $locale['s_short_name']; ?></option>
            <?php } ?>
          </select>
        </p>
      <?php } else {?>
        <input type="hidden" name="locale" value="<?php echo $locales[0]["pk_c_code"]; ?>"/>
      <?php } ?>

      <div class="log-hooks"><?php osc_run_hook('login_admin_form'); ?></div>
      
      <p class="">
        <button type="submit" name="submit" id="submit" class="btn btn-submit"><?php _e('Log in'); ?></button>
      </p>

      <p class="lastline">
        <label for="remember">
          <input name="remember" type="checkbox" id="remember" value="1" checked/><span><?php _e('Remember me'); ?></span>
        </label>

        <a href="<?php echo osc_admin_base_url(true); ?>?page=login&amp;action=recover" title="<?php echo osc_esc_html( __('Forgot your password?')); ?>" class="forgot"><?php _e('Forgot your password?'); ?></a>
      </p>
    </form>
  </div>

  <div id="login-footer">
    <span><?php _e('Osclass'); ?> v<?php echo OSCLASS_VERSION; ?></span>
  </div>

  <script type="text/javascript">
    $(document).ready(function() {
      $(".ico-close").click(function() {
        $(this).parent().hide();
      });

      $("#user_login").focus();

      $(".toggle-pass").click(function(e) {
        e.preventDefault();

        $(this).find('i').toggleClass("fa-eye fa-eye-slash");
        var input = $('input[name="password"]');
        if (input.attr("type") == "password") {
          input.prop("type", "text");
        } else {
          input.prop("type", "password");
        }
      });
    });
  </script>
  
  <?php osc_run_hook('admin_login_footer'); ?>
</body>
</html>