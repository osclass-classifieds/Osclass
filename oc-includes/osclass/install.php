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

error_reporting(E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_PARSE);

define('ABS_PATH', dirname(dirname(__DIR__)) . '/');
define('LIB_PATH', ABS_PATH . 'oc-includes/');
define('CONTENT_PATH', ABS_PATH . 'oc-content/');
define('TRANSLATIONS_PATH', CONTENT_PATH . 'languages/');
define('OSC_INSTALLING', 1);

define('PHP_MIN', '7.2');
define('PHP_MAX', '');


// Moved here to avoid issues with session_start() in Osclass 8.3
require_once LIB_PATH . 'osclass/core/Session.php';
Session::newInstance()->session_start();


require_once LIB_PATH . 'vendor/autoload.php';

if(extension_loaded('mysqli')) {
  require_once LIB_PATH . 'osclass/Logger/Logger.php';
  require_once LIB_PATH . 'osclass/Logger/LogDatabase.php';
  require_once LIB_PATH . 'osclass/Logger/LogOsclassInstaller.php';
  require_once LIB_PATH . 'osclass/classes/database/DBConnectionClass.php';
  require_once LIB_PATH . 'osclass/classes/database/DBCommandClass.php';
  require_once LIB_PATH . 'osclass/classes/database/DBRecordsetClass.php';
  require_once LIB_PATH . 'osclass/classes/database/DAO.php';
  require_once LIB_PATH . 'osclass/model/Preference.php';
  require_once LIB_PATH . 'osclass/helpers/hPreference.php';
}

require_once LIB_PATH . 'osclass/core/iObject_Cache.php';
require_once LIB_PATH . 'osclass/core/Object_Cache_Factory.php';
require_once LIB_PATH . 'osclass/helpers/hCache.php';

require_once LIB_PATH . 'osclass/core/Params.php';
require_once LIB_PATH . 'osclass/core/View.php';
require_once LIB_PATH . 'osclass/helpers/hDatabaseInfo.php';
require_once LIB_PATH . 'osclass/helpers/hDefines.php';
require_once LIB_PATH . 'osclass/helpers/hErrors.php';
require_once LIB_PATH . 'osclass/helpers/hLocale.php';
require_once LIB_PATH . 'osclass/helpers/hSearch.php';
require_once LIB_PATH . 'osclass/helpers/hPlugins.php';
require_once LIB_PATH . 'osclass/helpers/hTranslations.php';
require_once LIB_PATH . 'osclass/helpers/hSanitize.php';
require_once LIB_PATH . 'osclass/helpers/hLocation.php';
require_once LIB_PATH . 'osclass/helpers/hUsers.php';
require_once LIB_PATH . 'osclass/helpers/hItems.php';
require_once LIB_PATH . 'osclass/helpers/hCategories.php';
require_once LIB_PATH . 'osclass/helpers/hCurrency.php';
require_once LIB_PATH . 'osclass/default-constants.php';
require_once LIB_PATH . 'osclass/install-functions.php';
require_once LIB_PATH . 'osclass/utils.php';
require_once LIB_PATH . 'osclass/core/Translation.php';
require_once LIB_PATH . 'osclass/classes/Plugins.php';
require_once LIB_PATH . 'osclass/locales.php';


Params::init();

$locales = osc_listLocales();

if(Params::getParam('install_locale') != '') {
  Session::newInstance()->_set('userLocale', Params::getParam('install_locale'));
  Session::newInstance()->_set('adminLocale', Params::getParam('install_locale'));
}

if(Session::newInstance()->_get('adminLocale') != '' && array_key_exists(Session::newInstance()->_get('adminLocale'), $locales)) {
  $current_locale = Session::newInstance()->_get('adminLocale');
} else if(isset($locales['en_US'])) {
  $current_locale = 'en_US';
} else {
  $current_locale = key($locales);
}

Session::newInstance()->_set('userLocale', $current_locale);
Session::newInstance()->_set('adminLocale', $current_locale);


$translation = Translation::newInstance(true);

$step = Params::getParam('step');
if(!is_numeric($step)) {
  $step = '1';
}

if(is_osclass_installed()) {
  $message = __("Looks like you've already installed Osclass. To reinstall please clear your old database tables first.");
  osc_die('Osclass Error', $message);
}

switch ($step) {
  case 1:
    $requirements = get_requirements();
    $error = check_requirements($requirements);
    break;
    
  case 2:
    if(isset($_COOKIE)) {
      if(Params::getParam('save_stats') == '1' || isset($_COOKIE['osclass_save_stats'])) {
        setcookie('osclass_save_stats', 1, time() + (24 * 60 * 60));
      } else {
        setcookie('osclass_save_stats', 0, time() + (24 * 60 * 60));
      }

      if(isset($_COOKIE['osclass_ping_engines'])) {
        setcookie('osclass_ping_engines', 1, time() + (24 * 60 * 60));
      }
    }
    break;
    
  case 3:
    if(Params::getParam('dbname') != '') {
      $error = oc_install();
    }
    break;
    
  case 4:
    if(Params::getParam('result') != '') {
      $error = Params::getParam('result');
    }
    
    $password = Params::getParam('password', false, false);
    break;
    
  case 5:
    $password = Params::getParam('password', false, false);
    break;
    
  default:
    break;
}
?>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0" />
  <title><?php _e('Osclass Installation'); ?></title>
  <script src="<?php echo get_absolute_url(); ?>oc-includes/osclass/assets/js/jquery.min.js" type="text/javascript"></script>
  <script src="<?php echo get_absolute_url(); ?>oc-includes/osclass/assets/js/jquery-ui.min.js" type="text/javascript"></script>
  <script src="<?php echo get_absolute_url(); ?>oc-includes/osclass/installer/vtip/vtip.js" type="text/javascript"></script>
  <script src="<?php echo get_absolute_url(); ?>oc-includes/osclass/assets/js/jquery.json.js" type="text/javascript"></script>
  <script src="<?php echo get_absolute_url(); ?>oc-includes/osclass/installer/install.js?v=<?php echo date('YmdHis'); ?>" type="text/javascript"></script>
  <link rel="stylesheet" type="text/css" media="all" href="<?php echo get_absolute_url(); ?>oc-includes/osclass/installer/install.css?v=<?php echo date('YmdHis'); ?>"/>
  <link rel="stylesheet" type="text/css" media="all" href="<?php echo get_absolute_url(); ?>oc-includes/osclass/installer/vtip/css/vtip.css"/>
  <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400&family=Nunito:wght@300;600&display=swap" rel="stylesheet">
</head>

<body>
<div class="pre-wrapper">
  <img src="<?php echo get_absolute_url(); ?>oc-includes/images/osclass-logo.png" alt="Osclass" title="Osclass"/>
</div>

<div id="wrapper">
  <div id="container">
    <div id="header" class="installation">
      <h1><?php _e('Installation assistant'); ?></h1>
      <h2>
        <?php 
          if($step == 1) {
            _e('1. Hosting requirements check');
          } else if($step == 2) {
            _e('2. Database information setup');
          } else if($step == 3) {
            _e('3. Website information setup');
          }
        ?>
      </h2>
    </div>

    <div id="content">
      <?php if($step == 1) { ?>
        <?php if($error) { ?>
          <div class="flash error">
            <strong><?php _e('Oops! You need a compatible Hosting'); ?></strong>
            <span><?php _e('Your hosting seems to be not compatible, check your settings.'); ?></span>
          </div>
          <br>
        <?php } ?>

        <form action="install.php" method="post">
          <input type="hidden" name="step" value="2"/>

          <div class="row abt">
            <?php echo sprintf(__('Welcome to the <u>Osclass v%s</u> installation, this action takes 3 minutes and does not require technical knowledge. Just fill the information bellow and you will be on your way to using most extendable and powerful classified script in the world!'), OSCLASS_VERSION); ?>
          </div>

          <?php if(count($locales) > 1) { ?>
            <div class="row locs">
              <div>
                <label for="install_locale" class="line-label"><?php _e('Continue installation in:'); ?></label>
                <select name="install_locale" id="install_locale" onchange="window.location.href='?install_locale='+document.getElementById(this.id).value">
                  <?php foreach ($locales as $k => $locale) { ?>
                    <option value="<?php echo osc_esc_html($k); ?>" <?php if($k == $current_locale) { echo 'selected="selected"';} ?>><?php echo $locale['name']; ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
          <?php } ?>

          <label class="line-label"><?php _e('Requirements check:'); ?></label>
          <div class="form-table reqs">
            <?php if($error) { ?>
              <p><?php _e('Check the next requirements:'); ?></p>
              
              <div class="flash info">
                <p><b><?php _e('Requirements help:'); ?></b></p>
                
                <ul>
                  <?php foreach ($requirements as $k => $v) { ?>
                    <?php if(!$v['fn'] && $v['solution'] != '') { ?>
                      <li><?php echo $v['solution']; ?></li>
                    <?php } ?>
                  <?php } ?>
                </ul>
              </div>
            <?php } else { ?>
              <p><?php _e('All right, all the requirements have met!'); ?></p>
            <?php } ?>
            
            <ul>
              <?php foreach ($requirements as $k => $v) { ?>
                <li><?php echo $v['requirement']; ?> <img src="<?php echo get_absolute_url(); ?>oc-includes/images/<?php echo $v['fn'] ? 'tick.svg' : 'cross.svg'; ?>" alt="" title=""/></li>
              <?php } ?>
            </ul>
          </div>
          <?php if($error) { ?>
            <p class="margin25t"><input type="button" class="btn btn-primary" onclick="document.location = 'install.php?step=1'" value="<?php echo osc_esc_html(__('Try again')); ?>"/></p>
          <?php } else { ?>
            <p class="margin25t"><input type="submit" class="btn btn-primary" value="<?php echo osc_esc_html(__('Run the install')); ?>"/></p>
          <?php } ?>
        </form>
      <?php } elseif($step == 2) { ?>

        <div class="row">
          <?php _e('Bellow you should enter your database connection details. If you are not sure about these, contact your hosting provider.'); ?>
        </div>

      <?php
        display_database_config();
      } elseif($step == 3) {
        if(!isset($error['error'])) {
          display_target();
        } else {
          display_database_error($error, $step - 1);
        }
      } elseif($step == 4) {
        // ping engines

        if(isset($_COOKIE)) {
          ping_search_engines($_COOKIE['osclass_ping_engines']);

          setcookie('osclass_save_stats', '', time() - 3600);
          setcookie('osclass_ping_engines', '', time() - 3600);
        }
        
        // copy robots.txt
        $source = LIB_PATH . 'osclass/installer/robots.txt';
        $destination = ABS_PATH . 'robots.txt';
        
        if(function_exists('copy')) {
          @copy($source, $destination);
        } else {
          $contentx = @file_get_contents($source);
          $openedfile = fopen($destination, 'wb');
          fwrite($openedfile, $contentx);
          fclose($openedfile);
          $status = true;
          
          if($contentx === false) {
            $status = false;
          }
        }
        
        display_finish($password);
      }
      ?>
    </div>
  </div>
</div>

<div id="footer">
  <div>
    <a href="https://osclass-classifieds.com" target="_blank" hreflang="en"><?php _e('Osclass'); ?></a>
    <a href="https://docs.osclass-classifieds.com" target="_blank" hreflang="en"><?php _e('Documentation'); ?></a>
    <a href="https://forums.osclasspoint.com" target="_blank" hreflang="en"><?php _e('Forums'); ?></a>
    <a href="https://osclasspoint.com" target="_blank" hreflang="en"><?php _e('Market'); ?></a>
  </div>
</div>
</body>
</html>