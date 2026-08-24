<?php
if(!defined('ABS_PATH')) exit('ABS_PATH is not loaded. Direct access is not allowed.');

set_time_limit(0);

error_log(' ------- START upgrade-funcs ------- ');

if(!defined('ABS_PATH')) {
  define('ABS_PATH', dirname(dirname(__DIR__)) . '/');
}

require_once ABS_PATH . 'oc-load.php';
require_once LIB_PATH . 'osclass/helpers/hErrors.php';

if(!defined('OC_ADMIN_FOLDER')) { define('OC_ADMIN_FOLDER', 'oc-admin'); }
if(!defined('OC_INCLUDES_FOLDER')) { define('OC_INCLUDES_FOLDER', 'oc-includes'); }
if(!defined('OC_CONTENT_FOLDER')) { define('OC_CONTENT_FOLDER', 'oc-content'); }

$updated_version = '100';

if(!defined('AUTO_UPGRADE') && UPGRADE_SKIP_DB === false) {
  $error_queries = array();

  if(file_exists(osc_lib_path() . 'osclass/installer/struct.sql')) {
    $sql = file_get_contents(osc_lib_path() . 'osclass/installer/struct.sql');

    $conn = DBConnectionClass::newInstance();
    $c_db = $conn->getOsclassDb();
    $comm = new DBCommandClass($c_db);

    $error_queries = $comm->updateDB(str_replace('/*TABLE_PREFIX*/', DB_TABLE_PREFIX, $sql));
  } else {
    $error_queries[0] = true;
  }

  if(Params::getParam('skipdb') == '' && (int)Params::getParam('rerun') <= 0 && !$error_queries[0]) {
    $skip_db_link = osc_admin_base_url(true) . '?page=upgrade&action=upgrade-funcs&skipdb=true';
    $title  = __('Osclass has some errors');
    $message  = __("We've encountered some problems while updating the database structure. The following queries failed:");
    $message .= '<br/><br/>' . '<span class="upgr-errors">' . implode('<br>', $error_queries[2]) . '</span>';
    $message .= '<br/><br/>' . '<strong class="upgr-notice">' . __("These errors could be false-positive errors. If you're sure that is the case, you can continue with the upgrade or <a href=\"https://forums.osclasspoint.com\">ask in our forums</a>.") . '</strong>';
    $message .= '<br/><br/>' . sprintf('<a href="%s" class="btn btn-submit">Continue with the upgrade</a>', $skip_db_link) . '</strong>';
    osc_die($title, $message);
  }
}

$aMessages = array();
//osc_set_preference('last_version_check', time());

$conn = DBConnectionClass::newInstance();
$c_db = $conn->getOsclassDb();
$comm = new DBCommandClass($c_db);


$current_version = (int)str_replace('.', '', getPreference('version'));   // osc_version()

// Developer: re-run one version block, e.g. ?page=upgrade&action=upgrade-funcs&rerun=840
$rerun = (int)Params::getParam('rerun');
if($rerun > 800 && $rerun <= (int)str_replace('.', '', OSCLASS_VERSION) && osc_is_admin_user_logged_in() && defined('OSC_DEBUG') && OSC_DEBUG) {
  $current_version = $rerun - 1;
}

// UPLOAD ONLY AVAILABLE FROM v3.0.0
if($current_version < 300) {
  throw new \RuntimeException(sprintf('Unsupported Osclass version for upgrade: "%s"', getPreference('version')));
}

if($current_version < 310) {
  $comm->query(sprintf('ALTER TABLE %st_pages ADD  `s_meta` TEXT NULL', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_pages ADD  `b_link` TINYINT(1) NOT NULL DEFAULT 1', DB_TABLE_PREFIX));
  $comm->query(sprintf("UPDATE %st_alerts SET dt_date = '%s' ", DB_TABLE_PREFIX, date('Y-m-d H:i:s')));

  // remove files moved to controller folder
  @unlink(osc_base_path() . 'ajax.php');
  @unlink(osc_base_path() . 'contact.php');
  @unlink(osc_base_path() . 'custom.php');
  @unlink(osc_base_path() . 'item.php');
  @unlink(osc_base_path() . 'language.php');
  @unlink(osc_base_path() . 'login.php');
  @unlink(osc_base_path() . 'main.php');
  @unlink(osc_base_path() . 'page.php');
  @unlink(osc_base_path() . 'register.php');
  @unlink(osc_base_path() . 'search.php');
  @unlink(osc_base_path() . 'user-non-secure.php');
  @unlink(osc_base_path() . 'user.php');
  @unlink(osc_base_path() . 'readme.php');
  @unlink(osc_lib_path() . 'osclass/plugins.php');
  @unlink(osc_lib_path() . 'osclass/feeds.php');

  $comm->query(sprintf('UPDATE %st_user t, (SELECT pk_i_id FROM %st_user) t1 SET t.s_username = t1.pk_i_id WHERE t.pk_i_id = t1.pk_i_id', DB_TABLE_PREFIX, DB_TABLE_PREFIX));
  osc_set_preference('username_blacklist', 'admin,user');
  osc_set_preference('rewrite_user_change_username', 'username/change');
  osc_set_preference('csrf_name', 'CSRF'.mt_rand(0, mt_getrandmax()));

  if(!@mkdir(osc_uploads_path() . 'page-images') && ! is_dir(osc_uploads_path() . 'page-images')) {
    throw new \RuntimeException(sprintf('Directory "%s" was not created', osc_uploads_path() . 'page-images'));
  }

  $updated_version = 310;
}

if($current_version < 320) {
  osc_set_preference('mailserver_mail_from');
  osc_set_preference('mailserver_name_from');
  osc_set_preference('seo_url_search_prefix');

  $comm->query(sprintf('ALTER TABLE %st_category ADD `b_price_enabled` TINYINT(1) NOT NULL DEFAULT 1', DB_TABLE_PREFIX));

  osc_set_preference('subdomain_type');
  osc_set_preference('subdomain_host');
  // email_new_admin
  $comm->query(sprintf("INSERT INTO %st_pages (s_internal_name, b_indelible, dt_pub_date) VALUES ('email_new_admin', 1, '%s' )", DB_TABLE_PREFIX, date('Y-m-d H:i:s')));
  $comm->query(sprintf("INSERT INTO %st_pages_description (fk_i_pages_id, fk_c_locale_code, s_title, s_text) VALUES (%d, 'en_US', '{WEB_TITLE} - Success creating admin account!', '<p>Hi {ADMIN_NAME},</p><p>The admin of {WEB_LINK} has created an account for you,</p><ul><li>Credentials: {USERNAME} / {PASSWORD}</li></ul><p>You can access the admin panel here {WEB_ADMIN_LINK}.</p><p>Thank you!</p><p>Regards,</p>')", DB_TABLE_PREFIX, $comm->insertedId()));

  osc_set_preference('warn_expiration', '0', 'osclass', 'INTEGER');

  $comm->query(sprintf("INSERT INTO %st_pages (s_internal_name, b_indelible, dt_pub_date) VALUES ('email_warn_expiration', 1, '%s' )", DB_TABLE_PREFIX, date('Y-m-d H:i:s')));
  $comm->query(sprintf("INSERT INTO %st_pages_description (fk_i_pages_id, fk_c_locale_code, s_title, s_text) VALUES (%d, 'en_US', '{WEB_TITLE} - Your ad is about to expire', '<p>Hi {USER_NAME},</p><p>Your listing <a href=\"{ITEM_URL}\">{ITEM_TITLE}</a> is about to expire at {WEB_LINK}.')", DB_TABLE_PREFIX, $comm->insertedId()));

  osc_set_preference('force_aspect_image', '0', 'osclass', 'BOOLEAN');
  $updated_version = 320;
}

if($current_version < 321) {
  if(function_exists('osc_calculate_location_slug')) {
    osc_calculate_location_slug(osc_subdomain_type());
  }
  $updated_version = 321;
}

if($current_version < 330) {
  if(!@mkdir(osc_content_path() . 'uploads/temp/') && ! is_dir(osc_content_path() . 'uploads/temp/')) {
    throw new \RuntimeException(sprintf('Directory "%s" was not created', osc_content_path() . 'uploads/temp/'));
  }

  $concurrentDirectory = osc_content_path() . 'downloads/oc-temp/';
  if(!@mkdir($concurrentDirectory) && !is_dir($concurrentDirectory)) {
    throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
  }

  unset($concurrentDirectory);
  @unlink(osc_lib_path() . 'osclass/classes/Watermark.php');
  osc_set_preference('title_character_length', '100', 'osclass', 'INTEGER');
  osc_set_preference('description_character_length', '5000', 'osclass', 'INTEGER');
  $updated_version = 330;
}

if($current_version < 340) {
  $comm->query(sprintf('ALTER TABLE `%st_widget` ADD INDEX `idx_s_description` (`s_description`);', DB_TABLE_PREFIX));
  osc_set_preference('force_jpeg', '0', 'osclass', 'BOOLEAN');
  $updated_version = 340;
}

if($current_version < 343) {
  $mAlerts = Alerts::newInstance();
  $aAlerts = $mAlerts->findByType('HOURLY');

  foreach($aAlerts as $alert) {
    $s_search = base64_decode($alert['s_search']);
    if(stripos(strtolower($s_search), 'union select')!==false || stripos(strtolower($s_search), 't_admin')!==false) {
      $mAlerts->delete(array('pk_i_id' => $alert['pk_i_id']));
    } else {
      $mAlerts->update(array('s_search' => $s_search), array('pk_i_id' => $alert['pk_i_id']));
    }
  }
  unset($aAlerts);

  $aAlerts = $mAlerts->findByType('DAILY');
  foreach($aAlerts as $alert) {
    $s_search = base64_decode($alert['s_search']);
    if(stripos(strtolower($s_search), 'union select')!==false || stripos(strtolower($s_search), 't_admin')!==false) {
      $mAlerts->delete(array('pk_i_id' => $alert['pk_i_id']));
    } else {
      $mAlerts->update(array('s_search' => $s_search), array('pk_i_id' => $alert['pk_i_id']));
    }
  }
  unset($aAlerts);

  $aAlerts = $mAlerts->findByType('WEEKLY');
  foreach($aAlerts as $alert) {
    $s_search = base64_decode($alert['s_search']);
    if(stripos(strtolower($s_search), 'union select')!==false || stripos(strtolower($s_search), 't_admin')!==false) {
      $mAlerts->delete(array('pk_i_id' => $alert['pk_i_id']));
    } else {
      $mAlerts->update(array('s_search' => $s_search), array('pk_i_id' => $alert['pk_i_id']));
    }
  }
  unset($aAlerts);
  $updated_version = 343;
}

if($current_version < 370) {
  osc_set_preference('recaptcha_version', '1');
  $comm->query(sprintf('ALTER TABLE %st_category_description MODIFY s_slug VARCHAR(255) NOT NULL', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_preference MODIFY s_section VARCHAR(128) NOT NULL', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_preference MODIFY s_name VARCHAR(128) NOT NULL', DB_TABLE_PREFIX));
  $updated_version = 370;
}

if($current_version < 372) {
  osc_delete_preference('recaptcha_version', 'STRING');
  $updated_version = 372;
}

if($current_version < 374) {
  $admin = Admin::newInstance()->findByEmail('demo@demo.com');

  if(isset($admin['pk_i_id'])) {
    Admin::newInstance()->deleteByPrimaryKey($admin['pk_i_id']);
  }

  $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(ABS_PATH), RecursiveIteratorIterator::SELF_FIRST, RecursiveIteratorIterator::CATCH_GET_CHILD);
  $objects = iterator_to_array($iterator);

  foreach($objects as $file => $object) {
    try {
      $handle = @fopen($file, 'rb');
      if($handle!==false) {
        $exist = false;
        $text = array("htmlspecialchars(file_get_contents(\$_POST['path']))", '?option&path=$path', 'msdsaa' ,"shell_exec('cat /proc/cpuinfo');", 'PHPTerm', 'lzw_decompress');

        while(($buffer = fgets($handle)) !== false) {
          foreach($text as $_t) {
            if(strpos($buffer, $_t) !== false) {
              $exist = true;
              break;
            }
          }
        }

        fclose($handle);

        if($exist && strpos($file, __FILE__) === false) {
          error_log('remove ' . $file);
          @unlink($file);
        }
      }
    } catch(Exception $e) {
      error_log($e);
    }
  }
  $updated_version = 374;
}

if($current_version < 390) {
  osc_delete_preference('marketAllowExternalSources');
  osc_delete_preference('marketURL');
  osc_delete_preference('marketAPIConnect');
  osc_delete_preference('marketCategories');
  osc_delete_preference('marketDataUpdate');
  $updated_version = 390;
}

if($current_version < 400) {
  $comm->query(sprintf('ALTER TABLE %st_item ADD COLUMN s_contact_other VARCHAR(100) NULL AFTER s_contact_email;', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_item ADD COLUMN s_contact_phone VARCHAR(100) NULL AFTER s_contact_email;', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_item ADD COLUMN b_show_phone TINYINT(1) NULL DEFAULT 1 AFTER b_show_email;', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_category ADD COLUMN s_color VARCHAR(20) NULL AFTER s_icon;', DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'best_fit_image', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $updated_version = 400;
}

if($current_version < 401) {
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'search_pattern_method', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'enabled_tinymce_items', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $updated_version = 401;
}

if($current_version < 410) {
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'osclasspoint_api_key', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'reg_user_can_see_phone', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_country ADD COLUMN s_name_native VARCHAR(80) NULL AFTER s_name;', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_country ADD COLUMN s_phone_code VARCHAR(10) NULL AFTER s_name_native;', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_country ADD COLUMN s_currency VARCHAR(10) NULL AFTER s_phone_code;', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_region ADD COLUMN s_name_native VARCHAR(60) NULL AFTER s_name;', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_city ADD COLUMN s_name_native VARCHAR(60) NULL AFTER s_name;', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_city ADD COLUMN d_coord_lat DECIMAL(20, 10) NULL AFTER b_active;', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_city ADD COLUMN d_coord_long DECIMAL(20, 10) NULL AFTER d_coord_lat;', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_item_location ADD COLUMN s_country_native VARCHAR(80) NULL AFTER s_country;', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_item_location ADD COLUMN s_region_native VARCHAR(60) NULL AFTER s_region;', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_item_location ADD COLUMN s_city_native VARCHAR(60) NULL AFTER s_city;', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_item_location CHANGE d_coord_lat d_coord_lat DECIMAL(20, 10) NULL DEFAULT NULL;', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_item_location CHANGE d_coord_long d_coord_long DECIMAL(20, 10) NULL DEFAULT NULL;', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_locale ADD COLUMN b_locations_native TINYINT(1) NULL DEFAULT 0 AFTER b_enabled_bo;', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_user CHANGE dt_access_date dt_access_date DATETIME NULL;', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_user ADD COLUMN s_country_native VARCHAR(80) NULL AFTER s_country;', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_user ADD COLUMN s_region_native VARCHAR(60) NULL AFTER s_region;', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_user ADD COLUMN s_city_native VARCHAR(60) NULL AFTER s_city;', DB_TABLE_PREFIX));
  $updated_version = 410;
}

if($current_version < 411) {
  $comm->query(sprintf('ALTER TABLE %st_item CHANGE b_show_phone b_show_phone TINYINT(1) NULL DEFAULT 1;', DB_TABLE_PREFIX));
  $updated_version = 411;
}

if($current_version < 420) {
  if(!@mkdir(osc_uploads_path() . 'user-images/') && !is_dir(osc_uploads_path() . 'user-images/')) {   // user profile pictures dir
    throw new \RuntimeException(sprintf('Directory "%s" was not created', osc_uploads_path() . 'user-images/'));
  }

  if(!@mkdir(osc_uploads_path() . 'minify/') && !is_dir(osc_uploads_path() . 'minify/')) {   // user profile pictures dir
    throw new \RuntimeException(sprintf('Directory "%s" was not created', osc_uploads_path() . 'minify/'));
  }

  if(file_exists(osc_lib_path() . 'phpmailer') && is_dir(osc_lib_path() . 'phpmailer')) {
    $phpmailer_files = glob(osc_lib_path() . 'phpmailer/*');

    if(count($phpmailer_files) > 0) {
      foreach($phpmailer_files as $fl) {
        if(is_file($fl)) {
          @unlink($fl);
        }
      }
    }

    @rmdir(osc_lib_path() . 'phpmailer');
  }

  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'enable_comment_rating', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_post_redirect', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'enabled_tinymce_users', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'enable_profile_img', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'dimProfileImg', '180x180', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'admindash_widgets_collapsed', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'admindash_widgets_hidden', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'admindash_columns_hidden', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'enabled_renewal_items', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'renewal_update_pub_date', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'renewal_limit', 0, 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'rewrite_item_renew', 'item/renew', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'structured_data', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'css_merge', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'css_minify', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'js_merge', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'js_minify', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'css_banned_words', 'font,awesome', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'css_banned_pages', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'js_banned_words', 'tiny', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'js_banned_pages', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'admin_toolbar_front', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'can_deactivate_items', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'rewrite_item_deactivate', 'item/deactivate', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_pages ADD b_index TINYINT(1) NOT NULL DEFAULT 1 AFTER b_link', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_meta_fields ADD i_order INT(3) NOT NULL DEFAULT 0 AFTER b_searchable', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_item_comment ADD i_rating INT(3) NULL AFTER s_body', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_user ADD i_login_fails INT(3) NULL DEFAULT 0 AFTER s_access_ip', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_user ADD dt_login_fail_date DATETIME NULL AFTER i_login_fails', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_user ADD s_profile_img VARCHAR(100) AFTER dt_login_fail_date', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_admin ADD s_moderator_access VARCHAR(1000) NULL AFTER b_moderator', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_admin ADD i_login_fails INT(3) NULL DEFAULT 0 AFTER s_moderator_access', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_admin ADD dt_login_fail_date DATETIME NULL AFTER i_login_fails', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_item ADD i_renewed INT(3) NULL DEFAULT 0 AFTER b_show_phone', DB_TABLE_PREFIX));
  $updated_version = 420;
}

if($current_version < 421) {
  // change backoffice theme if upgrading from different branch
  if(file_exists(osc_base_path() . 'oc-admin/themes/evolution/') && is_dir(osc_base_path() . 'oc-admin/themes/evolution/')) {
    osc_deleteDir(osc_base_path() . 'oc-admin/themes/evolution/');
  }
  $updated_version = 421;
}

if($current_version < 430) {
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'custom_css', '', 'STRING')", DB_TABLE_PREFIX));
  $updated_version = 430;
}

if($current_version < 440) {
  $comm->query(sprintf("UPDATE %st_region SET s_name_native = null WHERE s_name_native = '' ", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'custom_html', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'breadcrumbs_item_page_title', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'breadcrumbs_item_country', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'breadcrumbs_item_region', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'breadcrumbs_item_city', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'breadcrumbs_item_category', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'breadcrumbs_item_parent_categories', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'breadcrumbs_hide', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'breadcrumbs_hide_custom', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'widget_data_api', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'widget_data_blog', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'widget_data_product', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'widget_data_update', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'market_products_version', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'jquery_version', '1', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'admin_color_scheme', '', 'STRING')", DB_TABLE_PREFIX));
  $updated_version = 440;
}

if($current_version < 800) {
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'widget_data_product_updates', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_item_description ENGINE = InnoDB", DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_meta_fields CHANGE e_type e_type ENUM('TEXT','NUMBER','TEL','EMAIL','COLOR','TEXTAREA','DROPDOWN','RADIO','CHECKBOX','URL','DATE','DATEINTERVAL') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TEXT'", DB_TABLE_PREFIX));
  $updated_version = 800;
}

if($current_version < 801) {
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'update_include_occontent', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_contact_form_disabled', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'web_contact_form_disabled', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_locale ADD COLUMN b_rtl TINYINT(1) NULL DEFAULT 0 AFTER b_locations_native;", DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_user CHANGE s_pass_ip s_pass_ip VARCHAR(64) NULL;", DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_user CHANGE s_access_ip s_access_ip VARCHAR(64) NOT NULL DEFAULT '';", DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_ban_rule CHANGE s_ip s_ip VARCHAR(64) NOT NULL DEFAULT '';", DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_log CHANGE s_ip s_ip VARCHAR(64) NULL;", DB_TABLE_PREFIX));
  $updated_version = 801;
}

if($current_version < 802) {
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'canvas_background', 'white', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'hide_generator', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'username_generator', 'ID', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_ban_rule ADD COLUMN dt_date DATETIME NULL AFTER s_email;", DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_cron CHANGE e_type e_type ENUM('INSTANT','MINUTELY','HOURLY','DAILY','WEEKLY','MONTHLY','YEARLY','CUSTOM') NOT NULL;", DB_TABLE_PREFIX));
  $comm->query("SET SQL_MODE='ALLOW_INVALID_DATES';");
  $comm->query(sprintf("INSERT INTO %st_cron VALUES ('MINUTELY', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),('MONTHLY', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),('YEARLY', '0000-00-00 00:00:00', '0000-00-00 00:00:00');", DB_TABLE_PREFIX));
  $updated_version = 802;
}

if($current_version < 810) {
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'enable_comment_reply', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'enable_comment_reply_rating', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'comment_reply_user_type', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'notify_new_comment_reply', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'notify_new_comment_reply_user', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'comment_rating_limit', '1', 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'latest_searches_restriction', '0', 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'latest_searches_words', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'subdomain_landing', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'subdomain_redirect', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'subdomain_restricted_ids', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'subdomain_language_slug_type', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'locale_to_base_url_enabled', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'locale_to_base_url_type', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_mark_disable', '0', 'BOOLEAN')", DB_TABLE_PREFIX));

  $comm->query(sprintf("ALTER TABLE %st_item_comment ADD COLUMN fk_i_reply_id INT(10) UNSIGNED NULL AFTER fk_i_user_id;", DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_pages ADD COLUMN i_visibility TINYINT(1) NULL DEFAULT 0 AFTER b_index;", DB_TABLE_PREFIX));

  @unlink(osc_base_path() . OC_ADMIN_FOLDER . '/controller/settings/currencies.php');
  @unlink(osc_base_path() . OC_ADMIN_FOLDER . '/themes/omega/settings/currencies.php');
  @unlink(osc_base_path() . OC_ADMIN_FOLDER . '/themes/omega/settings/currency_form.php');

  @unlink(osc_base_path() . OC_ADMIN_FOLDER . '/controller/settings/locations.php');
  @unlink(osc_base_path() . OC_ADMIN_FOLDER . '/themes/omega/settings/locations.php');

  // Remove language less folder from omega as it is not used
  if(file_exists(osc_base_path() . OC_ADMIN_FOLDER  . '/themes/omega/less/') && is_dir(osc_base_path() . OC_ADMIN_FOLDER  . '/themes/omega/less/')) {
    osc_deleteDir(osc_base_path() . OC_ADMIN_FOLDER  . '/themes/omega/less/');
  }

  // Remove language folder of omega, as Core already contains it
  if(file_exists(osc_base_path() . OC_ADMIN_FOLDER  . '/themes/omega/languages/en_US/') && is_dir(osc_base_path() . OC_ADMIN_FOLDER  . '/themes/omega/languages/en_US/')) {
    osc_deleteDir(osc_base_path() . OC_ADMIN_FOLDER  . '/themes/omega/languages/en_US/');
  }

  // Remove class related to metadata DB (multisite)
  @unlink(osc_base_path() . OC_INCLUDES_FOLDER . '/osclass/model/SiteInfo.php');
  $updated_version = 810;
}

if($current_version < 811) {
  // No changes
  $updated_version = 811;
}

if($current_version < 812) {
  osc_set_preference('css_banned_pages', 'item-item_add,item-item_edit');
  osc_set_preference('js_banned_pages', 'item-item_add,item-item_edit');
  osc_set_preference('css_banned_words', 'font,awesome,tiny,fineuploader');
  osc_set_preference('js_banned_words', 'tiny,fineuploader');

  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'gen_hreflang_tags', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'always_gen_canonical', '1', 'BOOLEAN')", DB_TABLE_PREFIX));

  $updated_version = 812;
}

if($current_version < 820) {
  $comm->query(sprintf('ALTER TABLE %st_user ADD fk_c_locale_code CHAR(5) NULL', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_locale ADD fk_c_currency_code CHAR(3) NULL', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_item_resource ADD i_order INT(3) NOT NULL DEFAULT 0', DB_TABLE_PREFIX));

  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'enable_rss', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'recaptchaEnabled', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'image_upload_library', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'image_upload_reorder', 0, 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'image_upload_lib_force_replace', 0, 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_send_friend_form_disabled', 0, 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'profile_picture_library', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'num_category_levels', 4, 'INTEGER')", DB_TABLE_PREFIX));

  $updated_version = 820;
}

if($current_version < 821) {
  // NO DB UPDATES
  $updated_version = 821;
}

if($current_version < 830) {
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'search_pattern_locale', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_method', 'SESSION', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'user_public_profile_enabled', 'ALL', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'user_public_profile_min_items', 0, 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'enhance_canonical_url_enabled', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'custom_css_hook', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'custom_html_hook', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'custom_js', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'custom_js_hook', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'optimize_uploaded_images', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'logging_enabled', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'logging_auto_cleanup', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'logging_months', 24, 'INTEGER')", DB_TABLE_PREFIX));

  $comm->query(sprintf('ALTER TABLE %st_ban_rule ADD i_hit INT DEFAULT 1 AFTER s_email', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_ban_rule ADD dt_expire_date DATE DEFAULT NULL', DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_ban_rule CHANGE s_name s_name VARCHAR(1000) NOT NULL DEFAULT '';", DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_ban_rule CHANGE dt_date dt_date DATETIME NULL DEFAULT CURRENT_TIMESTAMP;", DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_alerts ADD s_name VARCHAR(255) AFTER pk_i_id', DB_TABLE_PREFIX));

  $comm->query(sprintf('ALTER TABLE %st_city_area ADD fk_i_region_id INT(10) UNSIGNED NULL AFTER fk_i_city_id', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_city_area ADD fk_c_country_code CHAR(2) NULL AFTER fk_i_region_id', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_city_area ADD s_name_native VARCHAR(60) NULL AFTER s_name', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_city_area ADD s_slug VARCHAR(60) NOT NULL DEFAULT "" AFTER s_name_native', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_city_area ADD b_active TINYINT(1) NOT NULL DEFAULT 1 AFTER s_slug', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_city_area ADD d_coord_lat DECIMAL(20, 10) AFTER b_active', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_city_area ADD d_coord_long DECIMAL(20, 10) AFTER d_coord_lat', DB_TABLE_PREFIX));

  $comm->query(sprintf('ALTER TABLE %st_city_area ADD INDEX fk_i_region_id (fk_i_region_id);', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_city_area ADD INDEX fk_c_country_code (fk_c_country_code);', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_city_area ADD INDEX idx_s_name_native (s_name_native);', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_city_area ADD INDEX idx_s_slug (s_slug);', DB_TABLE_PREFIX));

  $comm->query(sprintf('ALTER TABLE %st_city ADD INDEX idx_s_name_native (s_name_native);', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_region ADD INDEX idx_s_name_native (s_name_native);', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_country ADD INDEX idx_s_name_native (s_name_native);', DB_TABLE_PREFIX));

  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'rewrite_search_order', 'order', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'rewrite_search_order_type', 'sort', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'rewrite_search_order_by_price', 'price', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'rewrite_search_order_by_pub_date', 'pub-date', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'rewrite_search_order_by_relevance', 'relevance', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'rewrite_search_order_by_expiration', 'expiration', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'rewrite_search_order_by_rating', 'rating', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'rewrite_search_price_min', 'price-min', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'rewrite_search_price_max', 'price-max', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'rewrite_search_with_picture', 'with-picture', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'rewrite_search_premium_only', 'premium-only', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'rewrite_search_with_phone', 'with-phone', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'rewrite_search_show_as', 'view', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'rewrite_search_page_number', 'pn', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'rewrite_search_custom_rules_enabled', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'rewrite_search_custom_rules_strict', '1', 'BOOLEAN')", DB_TABLE_PREFIX));

  $comm->query(sprintf('ALTER TABLE %st_log ADD s_detail TEXT NULL AFTER s_data', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_log ADD s_comment VARCHAR(512) NULL AFTER s_detail', DB_TABLE_PREFIX));

  $comm->query(sprintf('ALTER TABLE %st_log ADD INDEX idx_s_section (s_section);', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_log ADD INDEX idx_s_action (s_action);', DB_TABLE_PREFIX));

  $comm->query(sprintf('ALTER TABLE %st_alerts ADD s_param VARCHAR(1000) AFTER s_search', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_alerts ADD s_sql VARCHAR(1000) AFTER s_param', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_alerts ADD i_num_trigger INT(10) DEFAULT 0 AFTER e_type', DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_alerts MODIFY s_search TEXT', DB_TABLE_PREFIX));


  // Emoji support - tables those has foreign key to t_locale cannot be automatically converted and must be done manually by user
  if(defined('UPGRADE_UTF8MB4_SOFT') && UPGRADE_UTF8MB4_SOFT === true) {
    $comm->query(sprintf('ALTER DATABASE %s DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_unicode_ci', DB_NAME));
    $comm->query(sprintf('ALTER TABLE %st_item_description CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', DB_TABLE_PREFIX));
    $comm->query(sprintf('ALTER TABLE %st_item_comment CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', DB_TABLE_PREFIX));
    $comm->query(sprintf('ALTER TABLE %st_item_meta CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', DB_TABLE_PREFIX));
    //$comm->query(sprintf('ALTER TABLE %st_pages_description CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', DB_TABLE_PREFIX));
    //$comm->query(sprintf('ALTER TABLE %st_category_description CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', DB_TABLE_PREFIX));
    //$comm->query(sprintf('ALTER TABLE %st_user_description CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', DB_TABLE_PREFIX));
    $comm->query(sprintf('ALTER TABLE %st_widget CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', DB_TABLE_PREFIX));
    $comm->query(sprintf('ALTER TABLE %st_preference CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', DB_TABLE_PREFIX));
    //$comm->query(sprintf('ALTER TABLE %st_keywords CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', DB_TABLE_PREFIX));
    $comm->query(sprintf('ALTER TABLE %st_log CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', DB_TABLE_PREFIX));
  }

  if(!@mkdir(osc_uploads_path() . 'item-images') && !is_dir(osc_uploads_path() . 'item-images')) {
    throw new \RuntimeException(sprintf('Directory "%s" was not created', osc_uploads_path() . 'item-images'));
  }

  if(!@mkdir(osc_uploads_path() . 'widget-images') && !is_dir(osc_uploads_path() . 'widget-images')) {
    throw new \RuntimeException(sprintf('Directory "%s" was not created', osc_uploads_path() . 'widget-images'));
  }

  if(!@mkdir(osc_uploads_path() . 'custom-images') && !is_dir(osc_uploads_path() . 'custom-images')) {
    throw new \RuntimeException(sprintf('Directory "%s" was not created', osc_uploads_path() . 'custom-images'));
  }

  $updated_version = 830;
}

if($current_version < 831) {
  // Update permalink rules to accept new user items urls (should be done in 830)
  $rules = osc_unserialize(osc_get_preference('rewrite_rules'));

  if(osc_rewrite_enabled() && is_array($rules) && !empty($rules)) {
    unset($rules['^user/items/?$']);
    $rules['^user/items?$'] = 'index.php?page=user&action=items';
    $rules['^user/items/(.*)$'] = 'index.php?page=user&action=items&sParams=$1';
    $rules = osc_serialize($rules);
    osc_set_preference('rewrite_rules', $rules);
  }

  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'block_indexing_enabled', '0', 'BOOLEAN')", DB_TABLE_PREFIX));


  // Emoji support - tables those has foreign key to t_locale cannot be automatically converted and must be done manually by user
  if(!defined('UPGRADE_UTF8MB4_SOFT') || UPGRADE_UTF8MB4_SOFT === false) {
    // This update database, tables (oc_), columns, keys, indexes
    // $comm->migrateToUtf8mb4();  // may not work for automatic updates

    // **********************************************************
    // MIGRATE TO UTF8MB4 - START
    $charset = 'utf8mb4';
    $collate = 'utf8mb4_unicode_ci';
    $prefix = DB_TABLE_PREFIX;
    $dbName = DB_NAME;

    error_log('*** MIGRATE TO UTF8MB4 - START ***');
    error_log('SET FOREIGN_KEY_CHECKS = 0');
    $comm->query('SET FOREIGN_KEY_CHECKS = 0');

    error_log("ALTER DATABASE `$dbName` CHARACTER SET $charset COLLATE $collate");
    $comm->query("ALTER DATABASE `$dbName` CHARACTER SET $charset COLLATE $collate");

    // Get tables that start with prefix AND are not yet utf8mb4
    error_log("SELECT TABLE_NAME, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE '{$prefix}%' AND TABLE_COLLATION <> '$collate'");
    $tables = $comm->query("SELECT TABLE_NAME, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME LIKE '{$prefix}%' AND TABLE_COLLATION <> '$collate'")->result();

    if(is_array($tables) && count($tables) > 0) {
      foreach($tables as $t) {
        $table = $t['TABLE_NAME'];

        error_log("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' AND REFERENCED_TABLE_NAME IS NOT NULL AND REFERENCED_COLUMN_NAME <> 'pk_i_id'");
        $fks = $comm->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' AND REFERENCED_TABLE_NAME IS NOT NULL AND REFERENCED_COLUMN_NAME <> 'pk_i_id'")->result();

        error_log("SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' AND REFERENCED_TABLE_NAME IS NOT NULL AND REFERENCED_COLUMN_NAME <> 'pk_i_id'");
        $fkDefs = $comm->query("SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' AND REFERENCED_TABLE_NAME IS NOT NULL AND REFERENCED_COLUMN_NAME <> 'pk_i_id'")->result();

        if(is_array($fks) && count($fks) > 0) {
          foreach($fks as $fk) {
            $name = $fk['CONSTRAINT_NAME'];
            error_log("ALTER TABLE `$table` DROP FOREIGN KEY `$name`");
            $comm->query("ALTER TABLE `$table` DROP FOREIGN KEY `$name`");
          }
        }

        error_log("ALTER TABLE `$table` CONVERT TO CHARACTER SET $charset COLLATE $collate");
        $comm->query("ALTER TABLE `$table` CONVERT TO CHARACTER SET $charset COLLATE $collate");

        error_log("SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' AND (CHARACTER_SET_NAME <> '$charset' OR COLLATION_NAME <> '$collate') AND CHARACTER_SET_NAME IS NOT NULL");
        $cols = $comm->query("SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' AND (CHARACTER_SET_NAME <> '$charset' OR COLLATION_NAME <> '$collate') AND CHARACTER_SET_NAME IS NOT NULL")->result();

        if(is_array($cols) && count($cols) > 0) {
          foreach($cols as $c) {
            $name = $c['COLUMN_NAME'];
            $type = $c['COLUMN_TYPE'];
            $null = ($c['IS_NULLABLE'] === 'NO' ? 'NOT NULL' : 'NULL');

            $default = '';
            if($c['COLUMN_DEFAULT'] !== null) {
              $raw = $c['COLUMN_DEFAULT'];
              if(preg_match('/^current_timestamp(\(\))?$/i', $raw)) {
                $default = "DEFAULT $raw";
              } elseif(is_numeric($raw)) {
                $default = "DEFAULT $raw";
              } else {
                $def = $comm->escape($raw);
                $default = "DEFAULT '$def'";
              }
            }

            $sql = "ALTER TABLE `$table` MODIFY `$name` $type CHARACTER SET $charset COLLATE $collate $null $default";
            error_log($sql);
            $comm->query($sql);
          }
        }

        if(is_array($fkDefs) && count($fkDefs) > 0) {
          foreach($fkDefs as $fk) {
            $sql = "ALTER TABLE `$table` ADD CONSTRAINT `{$fk['CONSTRAINT_NAME']}` FOREIGN KEY (`{$fk['COLUMN_NAME']}`) REFERENCES `{$fk['REFERENCED_TABLE_NAME']}` (`{$fk['REFERENCED_COLUMN_NAME']}`) ON DELETE CASCADE ON UPDATE CASCADE";
            error_log($sql);
            $comm->query($sql);
          }
        }
      }
    }

    error_log('SET FOREIGN_KEY_CHECKS = 1');
    $comm->query('SET FOREIGN_KEY_CHECKS = 1');
    error_log('*** MIGRATE TO UTF8MB4 - END ***');
    // MIGRATE TO UTF8MB4 - END
    // **********************************************************
  }

  // Specify final version
  $updated_version = 831;       // Version 8.3.1
}

if($current_version < 840) {
  // Former 8.3.2 changes (no 8.3.2 release - shipped in 8.4.0)
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'cleanup_threshold_days', 14, 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'cleanup_auto_types', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_auto_cleanup_enabled', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_cleanup_months', 24, 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'latest_searches_min_length', '3', 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'latest_searches_max_length', '15', 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf('ALTER TABLE %st_alerts ADD dt_expire_date DATETIME NULL DEFAULT NULL AFTER dt_date', DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'alerts_enabled', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'alerts_expiration_months_user', 3, 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'alerts_expiration_months_guest', 3, 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'alerts_allow_non_expiring', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'alerts_allow_user_expiration_change', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'alerts_default_non_expiring', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'alerts_unsub_inactive_months', 3, 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'optimization_cleanup_frequency', 'weekly', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'database_optimization_frequency', 'none', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'database_optimization_operations', 'check,analyze,optimize', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'database_optimization_running', 0, 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'location_stats_last_recalc', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'category_stats_last_recalc', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'user_stats_last_recalc', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'category_stats_recalc_running', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'category_stats_recalc_done', 0, 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'category_stats_recalc_offset', 0, 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'category_stats_recalc_total', 0, 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'user_stats_recalc_running', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'user_stats_recalc_last_id', 0, 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'user_stats_recalc_done', 0, 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'user_stats_recalc_total', 0, 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'media_regen_batch', 10, 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'media_refresh_batch', 30, 'INTEGER')", DB_TABLE_PREFIX));

  $widgets_hidden = trim((string)osc_get_preference('admindash_widgets_hidden'));
  $widgets_hidden_arr = array_filter(array_unique(array_map('trim', explode(',', $widgets_hidden))));

  if(!in_array('notes-my', $widgets_hidden_arr)) {
    $widgets_hidden_arr[] = 'notes-my';
  }
  if(function_exists('osc_admin_dash_stats_opt_in_ids')) {
    foreach(osc_admin_dash_stats_opt_in_ids() as $oid) {
      if(!in_array($oid, $widgets_hidden_arr)) {
        $widgets_hidden_arr[] = $oid;
      }
    }
  }
  $dash_show = array('chart-listing-views', 'chart-items', 'chart-comments', 'chart-reports', 'chart-users');
  $widgets_hidden_keep = array();
  foreach($widgets_hidden_arr as $hid) {
    if(!in_array($hid, $dash_show, true)) {
      $widgets_hidden_keep[] = $hid;
    }
  }
  $widgets_hidden_arr = $widgets_hidden_keep;

  osc_set_preference('admindash_widgets_hidden', implode(',', $widgets_hidden_arr));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'admindash_stats_opt_in_seeded', '5', 'BOOLEAN')", DB_TABLE_PREFIX));

  $comm->query(sprintf('ALTER TABLE %st_currency ADD COLUMN d_exchange_rate DECIMAL(20,10) NULL DEFAULT 1.0000000000 AFTER s_description', DB_TABLE_PREFIX));
  $comm->query(sprintf('UPDATE %st_currency SET d_exchange_rate = 1.0000000000 WHERE d_exchange_rate IS NULL', DB_TABLE_PREFIX));

  // Reports feature - replacement of legacy "Mark as" feature
  // No FOREIGN KEY: reports must survive removal of related user/item
  $comm->query(sprintf("CREATE TABLE IF NOT EXISTS %st_report (
  pk_i_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  fk_i_reporter_user_id INT(10) UNSIGNED NOT NULL,
  fk_i_user_id INT(10) UNSIGNED NULL,
  fk_i_item_id INT(10) UNSIGNED NULL,
  i_reported_id INT(10) UNSIGNED NULL,
  fk_c_locale_code CHAR(5) NULL,
  s_type VARCHAR(20) NOT NULL DEFAULT 'item',
  s_reason VARCHAR(30) NULL,
  s_status VARCHAR(30) NOT NULL DEFAULT 'submitted',
  s_source VARCHAR(30) NOT NULL DEFAULT 'osclass',
  s_comment VARCHAR(2000) NULL,
  s_admin_comment VARCHAR(2000) NULL,
  s_file VARCHAR(80) NULL,
  b_open TINYINT(1) NOT NULL DEFAULT 1,
  dt_status_date DATETIME NULL,
  dt_update_date DATETIME NULL,
  dt_create_date DATETIME NOT NULL,
  PRIMARY KEY (pk_i_id),
  INDEX idx_s_status (s_status),
  INDEX idx_s_type (s_type),
  INDEX idx_b_open (b_open),
  INDEX idx_reporter_date (fk_i_reporter_user_id, dt_create_date),
  INDEX fk_i_user_id (fk_i_user_id),
  INDEX fk_i_item_id (fk_i_item_id)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'", DB_TABLE_PREFIX));

  $comm->query(sprintf("CREATE TABLE IF NOT EXISTS %st_report_comment (
  pk_i_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  fk_i_report_id INT(10) UNSIGNED NOT NULL,
  fk_i_user_id INT(10) UNSIGNED NULL,
  fk_i_admin_id INT(10) UNSIGNED NULL,
  s_comment VARCHAR(2000) NOT NULL,
  b_admin_seen TINYINT(1) NOT NULL DEFAULT 0,
  dt_date DATETIME NOT NULL,
  PRIMARY KEY (pk_i_id),
  INDEX fk_i_report_id (fk_i_report_id),
  INDEX fk_i_user_id (fk_i_user_id),
  INDEX idx_b_admin_seen (b_admin_seen)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'", DB_TABLE_PREFIX));

  // Drop old report columns from item stats (one by one so a missing column does not block the rest)
  foreach(array('i_num_spam', 'i_num_repeated', 'i_num_bad_classified', 'i_num_offensive', 'i_num_expired') as $col) {
    $comm->query(sprintf('ALTER TABLE %st_item_stats DROP COLUMN %s', DB_TABLE_PREFIX, $col));
  }

  $item_stats_new_cols = array(
    'i_num_views_engaged' => 'i_num_premium_views',
    'i_num_views_search' => 'i_num_views_engaged',
    'i_num_views_logged' => 'i_num_views_search',
    'i_num_views_home' => 'i_num_views_logged',
    'i_num_view_minutes' => 'i_num_views_home',
    'i_num_phone_clicks' => 'i_num_view_minutes',
    'i_num_contactother_clicks' => 'i_num_phone_clicks',
    'i_num_favorites' => 'i_num_contactother_clicks',
    'i_num_contactforms' => 'i_num_favorites',
    'i_num_contacts' => 'i_num_contactforms',
    'i_num_orders' => 'i_num_contacts',
    'i_num_offers' => 'i_num_orders',
    'i_num_promotions' => 'i_num_offers',
    'i_num_reports' => 'i_num_promotions',
    'i_num_tops' => 'i_num_reports',
    'i_num_renews' => 'i_num_tops',
    'i_num_repubs' => 'i_num_renews',
    'i_num_alerts_sent' => 'i_num_repubs',
    'i_num_shares' => 'i_num_alerts_sent',
    'i_num_comments' => 'i_num_shares',
    'i_num_rated_comments' => 'i_num_comments',
    'i_num_custom1' => 'i_num_rated_comments',
    'i_num_custom2' => 'i_num_custom1',
    'i_num_custom3' => 'i_num_custom2'
  );
  foreach($item_stats_new_cols as $col => $after) {
    $comm->query(sprintf('ALTER TABLE %st_item_stats ADD COLUMN %s INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER %s', DB_TABLE_PREFIX, $col, $after));
  }

  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_logged_only', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_enabled', 'views,premium_views,views_engaged,views_logged,view_minutes,phone_clicks,contactforms,reports,alerts_sent', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_chart_periods', '30d,90d,12m,all', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_admin_default_period', '30d', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_admin_default_measures', 'views,premium_views,views_engaged,views_logged,view_minutes,phone_clicks,contactforms,reports,alerts_sent', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_user_chart_enabled', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_user_chart_measures', 'views,premium_views,views_engaged,phone_clicks,contactforms,reports', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_user_chart_period', '30d', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_user_chart_periods', '30d,90d,12m,all', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_user_chart_type', 'line', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_user_chart_kpis', 'views,premium_views,contactforms,favorites', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_user_chart_hooks', 'user_items_top', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_chart_audience', 'all', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_item_chart_enabled', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_item_chart_admin', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_item_chart_measures', 'views,premium_views,views_engaged,phone_clicks,contactforms,reports', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_item_chart_period', '30d', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_item_chart_periods', '30d,90d,12m,all', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_item_chart_type', 'line', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_item_chart_hooks', 'item_top', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_custom1_label', 'Custom 1', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_custom2_label', 'Custom 2', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_custom3_label', 'Custom 3', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_engaged_seconds', '15', 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("UPDATE %st_preference SET s_value = '15' WHERE s_name = 'item_stats_engaged_seconds' AND s_value = '60'", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_phone_selectors', '.phone, div.phone, .show-phone, .show-mobile, a[href^=\"tel:\"]', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_contactother_selectors', '.other, .contact-other, .show-other, [data-osc-stat=\"contactother_clicks\"]', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'item_stats_preset', 'essential', 'STRING')", DB_TABLE_PREFIX));

  // Reports preferences
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'reports_enabled', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'web_contact_create_report', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'reports_per_day', 5, 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'reports_allow_multiple', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'reports_notify_admin', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'reports_notify_reporter_created', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'reports_notify_reporter_resolved', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'reports_notify_owner_created', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'reports_notify_owner_resolved', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'reports_notify_comments', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'reports_enable_feedback', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'reports_auto_close_enabled', '1', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'reports_auto_close_days', 3, 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'reports_retention_months', 24, 'INTEGER')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'reports_attachment_enabled', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'reports_attachment_extensions', 'jpg,jpeg,png', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'reports_enabled_reasons', '', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'reports_enabled_statuses', '', 'STRING')", DB_TABLE_PREFIX));

  // Reports permalinks
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'rewrite_report_item', 'report/item', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'rewrite_report_user', 'report/user', 'STRING')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'rewrite_report_view', 'report/view', 'STRING')", DB_TABLE_PREFIX));
  osc_reset_preferences();

  // Update permalink rules to accept new report urls; old mark urls keep routing so they can redirect
  $rules = osc_unserialize(osc_get_preference('rewrite_rules'));
  if(osc_rewrite_enabled() && is_array($rules) && !empty($rules)) {
    $mark = osc_get_preference('rewrite_item_mark');
    if($mark != '') {
      $rules['^' . $mark . '/(.*?)/([0-9]+)/?$'] = 'index.php?page=item&action=mark&as=$1&id=$2';
    }
    $rules['^item/mark/(.*?)/([0-9]+)/?$'] = 'index.php?page=item&action=mark&as=$1&id=$2';

    $report_item = osc_get_preference('rewrite_report_item');
    $report_user = osc_get_preference('rewrite_report_user');
    $report_view = osc_get_preference('rewrite_report_view');
    if($report_item != '') {
      $rules['^' . $report_item . '/([0-9]+)/?$'] = 'index.php?page=report&action=item&id=$1';
    }
    if($report_user != '') {
      $rules['^' . $report_user . '/([0-9]+)/?$'] = 'index.php?page=report&action=user&id=$1';
    }
    if($report_view != '') {
      $rules['^' . $report_view . '/([0-9]+)/?$'] = 'index.php?page=report&action=view&id=$1';
    }

    osc_set_preference('rewrite_rules', osc_serialize($rules));
  }

  // Remove legacy "Mark as" preferences and obsolete reported listings views
  $comm->query(sprintf("DELETE FROM %st_preference WHERE s_section = 'osclass' AND s_name IN ('item_mark_disable', 'rewrite_item_mark')", DB_TABLE_PREFIX));

  @unlink(osc_base_path() . OC_ADMIN_FOLDER . '/themes/omega/items/reported.php');
  @unlink(osc_base_path() . OC_ADMIN_FOLDER . '/themes/modern/items/reported.php');

  // Reports email templates (2 variable templates)
  $email_report_admin_text = '<p>Hi admin,</p><p>{REPORT_MESSAGE}</p><p><b>Report #{REPORT_ID}</b><br /><b>Type:</b> {REPORT_TYPE}<br /><b>Reason:</b> {REPORT_REASON}<br /><b>Status:</b> {REPORT_STATUS}<br /><b>Reported:</b> {REPORTED_TITLE}<br /><b>Reporter:</b> {REPORTER_NAME}</p><p>{REPORT_COMMENT}</p>{REPORT_REPLY}<p>{REPORT_ADMIN_LINK}</p><p>Regards,</p><p>{WEB_LINK}</p>';
  $email_report_user_text = '<p>Hi {USER_NAME},</p><p>{REPORT_MESSAGE}</p><p><b>Report #{REPORT_ID}</b><br /><b>Type:</b> {REPORT_TYPE}<br /><b>Reason:</b> {REPORT_REASON}<br /><b>Status:</b> {REPORT_STATUS}<br /><b>Reported:</b> {REPORTED_TITLE}</p><p>{REPORT_COMMENT}</p>{REPORT_REPLY}<p>{REPORT_LINK}</p><p>Regards,</p><p>{WEB_LINK}</p>';
  osc_email_template_delete('email_report_admin');
  osc_email_template_delete('email_report_user');
  osc_email_template_create('email_report_admin', '{WEB_TITLE} - {REPORT_EVENT}', $email_report_admin_text);
  osc_email_template_create('email_report_user', '{WEB_TITLE} - {REPORT_EVENT}', $email_report_user_text);

  $comm->query(sprintf("CREATE TABLE IF NOT EXISTS %st_widget_description (
  fk_i_widget_id INT(10) UNSIGNED NOT NULL,
  fk_c_locale_code CHAR(5) NOT NULL,
  s_content MEDIUMTEXT NULL,
  PRIMARY KEY (fk_i_widget_id, fk_c_locale_code),
  FOREIGN KEY (fk_i_widget_id) REFERENCES %st_widget (pk_i_id),
  FOREIGN KEY (fk_c_locale_code) REFERENCES %st_locale (pk_c_code)
) ENGINE=InnoDB DEFAULT CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'", DB_TABLE_PREFIX, DB_TABLE_PREFIX, DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_widget ADD COLUMN s_code MEDIUMTEXT NULL AFTER e_kind", DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_widget ADD COLUMN s_internal_name VARCHAR(50) NULL AFTER s_description", DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_widget ADD COLUMN s_device_visibility VARCHAR(10) NOT NULL DEFAULT 'all' AFTER s_code", DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_widget ADD COLUMN s_css TEXT NULL AFTER s_device_visibility", DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_widget ADD COLUMN i_order INT(10) NOT NULL DEFAULT 0 AFTER s_css", DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_widget ADD COLUMN b_single_locale TINYINT(1) NOT NULL DEFAULT 0 AFTER i_order", DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_widget MODIFY s_location VARCHAR(64) NOT NULL", DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_widget ADD INDEX idx_s_location (s_location, i_order)", DB_TABLE_PREFIX));
  $widget_locale = osc_language();
  if($widget_locale == '') {
    $widget_locale = 'en_US';
  }
  $comm->query(sprintf("INSERT INTO %st_widget_description (fk_i_widget_id, fk_c_locale_code, s_content) SELECT pk_i_id, '%s', s_content FROM %st_widget", DB_TABLE_PREFIX, addslashes($widget_locale), DB_TABLE_PREFIX));
  $comm->query(sprintf("UPDATE %st_widget SET i_order = pk_i_id WHERE i_order = 0", DB_TABLE_PREFIX));
  $comm->query(sprintf("UPDATE %st_widget SET s_internal_name = CONCAT('widget-', pk_i_id) WHERE s_internal_name IS NULL OR s_internal_name = ''", DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_widget MODIFY s_internal_name VARCHAR(50) NOT NULL", DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_widget ADD UNIQUE INDEX uk_s_internal_name (s_internal_name)", DB_TABLE_PREFIX));
  $comm->query(sprintf("ALTER TABLE %st_widget DROP COLUMN s_content", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'widget_locale_strict', '0', 'BOOLEAN')", DB_TABLE_PREFIX));
  $comm->query(sprintf("INSERT INTO %st_preference VALUES ('osclass', 'widget_custom_sections', '', 'STRING')", DB_TABLE_PREFIX));

  $updated_version = 840;       // Version 8.4.0
}

// Resolve current version
// Critical for auto-upgrade, where constant OSCLASS_VERSION keeps current installation version, as new file is being downloaded
$known_version = (int)str_replace('.', '', OSCLASS_VERSION);

if(isset($updated_version) && $updated_version >= $known_version) {
  $current_version = $updated_version;
} else {
  $current_version = $known_version;
}

osc_changeVersionTo($current_version);

// Make sure Omega is selected as admin theme
osc_set_preference('admin_theme', 'omega');

// Disable maintenance mode
@unlink(ABS_PATH . '.maintenance');


if(!defined('IS_AJAX') || !IS_AJAX) {
  if(empty($aMessages)) {
    osc_add_flash_ok_message(_m('Osclass has been updated successfully. <a href="https://forums.osclasspoint.com">Need more help?</a>'), 'admin');
    echo '<script type="text/javascript"> window.location = "'.osc_admin_base_url(true).'?page=tools&action=version"; </script>';
  } else {
    echo '<div class="well ui-rounded-corners separate-top-medium">';
    echo '<p>'.__('Osclass updated correctly').'</p>';
    echo '<p>'.__('Osclass has been updated successfully. <a href="https://forums.osclasspoint.com">Need more help?</a>').'</p>';
    foreach($aMessages as $msg) {
      echo '<p>' . $msg . '</p>';
    }
    echo '</div>';
  }
}
