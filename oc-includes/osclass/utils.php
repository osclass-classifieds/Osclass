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


use ReCaptcha\ReCaptcha;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\POP3;


// Get correct user id
function osc_resolve_user_id($user_id = NULL) {
  $user_id = (int)osc_apply_filter('osc_resolve_user_id', $user_id);
  
  if($user_id > 0) {
    return $user_id;
  }
  
  $loc = osc_get_osclass_location();
  $sec = osc_get_osclass_section();
  
  // User public profile
  if($loc == 'user' && $sec == 'pub_profile') {
    $user_id = (int)osc_esc_html(Params::getParam('id'));
    $user_id = ($user_id > 0 ? $user_id : osc_user_id());
    
    return ($user_id > 0 ? $user_id : false);

  // User items + other user pages
  } else if($loc == 'user') {
    $user_id = (int)(osc_user_id() > 0 ? osc_user_id() : osc_logged_user_id());
    return ($user_id > 0 ? $user_id : false);

  // Item page
  } else if($loc == 'item') {
    $user_id = (int)osc_item_user_id();
    return ($user_id > 0 ? $user_id : false);
  }
  
  $user_id = (int)osc_user_id();
  return ($user_id > 0 ? $user_id : false);
}


// Check if user is real visitor based on user agent
function osc_visitor_is_real_user() {
  $visitor_agent = Params::getServerParam('HTTP_USER_AGENT');
  
  if($visitor_agent == false || $visitor_agent == '') {
    return false;
  }
  
  // List of common bot/crawler keywords (case-insensitive)
  $bot_pattern = '/bot|crawl|spider|slurp|mediapartners|facebookexternalhit|pingdom|crawler|wget|curl|python|java|libwww-perl/i';

  // If match, it's bot or crawler
  if(preg_match($bot_pattern, $visitor_agent)) {
    return false;
  }
  
  foreach(osc_user_agents() as $agent) {
    if(preg_match('|' . $agent . '|', $visitor_agent)) {
      return true;
    }
  }
  
  return false;
}


// List of "OK" file type mimes those can be used in contact forms as attachment
function osc_allowed_mime_types() {
  $mime_types = array(
    'text/plain',
    'image/jpg',
    'image/jpeg',
    'image/pjpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'application/pdf',
    'text/csv',
    'application/msword'
  );
  
  return osc_apply_filter('allowed_mime_types', $mime_types);
}


// List of user agents - only real users/browsers
function osc_user_agents() {
  $user_agents = array(
    'MSIE ([0-9].*)$',
    '^Mozilla/[0-9].([^c][^o][^m].)$',
    '^Mozilla/5.*Gecko',
    '^Mozilla/5.(PLAYSTATION.)',
    'Safari',
    'Omni',
    'iCab',
    'Opera',
    'Konqueror',
    'AOL',
    'MSN',
    'WebTV',
    'BlackBerry.*',
    'Trident/.*rv:11\.0',
    'Chrome',
    'CriOS',
    'FxiOS',
    'Edge',
    'Edg/',
    'OPR',
    'Firefox',
    'SamsungBrowser',
    'UCBrowser'
  );
  
  return osc_apply_filter('user_agents', $user_agents);
}


/**
 * Create IP lookup service URL
 *
 * @param $ip
 *
 * @return string
 */
function osc_ip_lookup_url($ip) {
  $url = str_replace('{IP_ADDRESS}', $ip, IP_LOOKUP_SERVICE);
  return osc_apply_filter('osc_ip_lookup_url', $url, $ip);
}


/**
 * check if the item is expired
 *
 * @param $dt_expiration
 *
 * @return bool
 */
function osc_isExpired($dt_expiration) {
  $now = date('YmdHis');
  $dt_expiration = str_replace(array(' ', '-', ':'), '', $dt_expiration);
  return !($dt_expiration > $now);
}


/**
 * Remove resources from disk
 *
 * @param int|array $id
 * @param boolean   $admin
 *
 * @return bool|void
 */
function osc_deleteResource($id, $admin) {
  if(defined('DEMO')) {
    return false;
  }
  if(is_array($id)) {
    $id = $id[0];
  }
  $resource = ItemResource::newInstance()->findByPrimaryKey($id);
  if($resource !== null) {
    Log::newInstance()->insertLog(
      'item',
      'delete resource',
      $resource['pk_i_id'],
      $id,
      $admin ? 'admin' : 'user',
      $admin ? osc_logged_admin_id() : osc_logged_user_id()
    );

    $backtracel = '';
    foreach (debug_backtrace() as $k => $v) {
      if($v['function'] === 'include' || $v['function'] === 'include_once' || $v['function'] === 'require_once' || $v['function'] === 'require') {
        $backtracel .= '#' . $k . ' ' . $v['function'] . '(' . $v['args'][0] . ') called@ [' . $v['file'] . ':' . $v['line'] . '] / ';
      } else {
        $backtracel .= '#' . $k . ' ' . $v['function'] . ' called@ [' . $v['file'] . ':' . $v['line'] . '] / ';
      }
    }

    Log::newInstance()->insertLog(
      'item',
      'delete resource backtrace',
      $resource['pk_i_id'],
      $backtracel,
      $admin ? 'admin' : 'user',
      $admin ? osc_logged_admin_id() : osc_logged_user_id()
    );

    @unlink(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '.' . $resource['s_extension']);
    @unlink(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '_original.' . $resource['s_extension']);
    @unlink(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '_thumbnail.' . $resource['s_extension']);
    @unlink(osc_base_path() . $resource['s_path'] . $resource['pk_i_id'] . '_preview.' . $resource['s_extension']);
    osc_run_hook('delete_resource', $resource);
  }
}


/**
 * Returns current page URL that's visible in browser
 *
 * @return string.
 */
function osc_get_current_url() {
  if(isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])) {
    // return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    return (osc_is_ssl() ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  }

  return false;
}


/**
 * Tries to delete the directory recursively.
 *
 * @param $path
 *
 * @return true on success.
 */
function osc_deleteDir($path) {
  if(strpos($path, '../') !== false || strpos($path, "..\\") !== false) {
    return false;
  }

  if(!is_dir($path)) {
    return false;
  }

  $fd = @opendir($path);
  if(!$fd) {
    return false;
  }

  while ($file = @readdir($fd)) {
    if($file !== '.' && $file !== '..') {
      if(!is_dir($path . '/' . $file)) {
        @chmod($path . '/' . $file, 0755);
        if(!@unlink($path . '/' . $file)) {
          closedir($fd);

          return false;
        }

        osc_deleteDir($path . '/' . $file);
      } else {
        osc_deleteDir($path . '/' . $file);
      }
    }
  }
  closedir($fd);

  return @rmdir($path);
}


/**
 * Unpack a ZIP file into the specific path in the second parameter.
 *
 * @DEPRECATED : TO BE REMOVED IN 3.3
 *
 * @param $zipPath
 * @param $path
 *
 * @return true on success.
 */
function osc_packageExtract($zipPath, $path) {
  if(strpos($path, '../') !== false || strpos($path, "..\\") !== false) {
    return false;
  }

  if(!file_exists($path)) {
    if(@!mkdir($path, 0666) && !is_dir($path)) {
      return false;
    }
  }

  @chmod($path, 0755);

  $zip = new ZipArchive;
  if($zip->open($zipPath) === true) {
    $zip->extractTo($path);
    $zip->close();

    return true;
  }

  return false;
}


/**
 * Fix the problem of symbolics links in the path of the file
 *
 * @param string $file The filename of plugin.
 *
 * @return string The fixed path of a plugin.
 */
function osc_plugin_path($file) {
  // Sanitize windows paths and duplicated slashes
  $file = preg_replace('|/+|', '/', str_replace('\\', '/', $file));
  $plugin_path = preg_replace('|/+|', '/', str_replace('\\', '/', osc_plugins_path()));
  //$file = $plugin_path . preg_replace('#^.*oc-content\/plugins\/#', '', $file);
  $file = $plugin_path . preg_replace('#^.*' . osc_content_folder() . '\/plugins\/#', '', $file);

  return $file;
}


/**
 * Fix the problem of symbolics links in the path of the file
 *
 * @param string $file The filename of plugin.
 *
 * @return string The fixed path of a plugin.
 */
function osc_plugin_url($file) {
  // Sanitize windows paths and duplicated slashes
  $dir = preg_replace('|/+|', '/', str_replace('\\', '/', dirname($file)));
  $dir = osc_content_url() . 'plugins/' . preg_replace('#^.*' . osc_content_folder() . '\/plugins\/#', '', $dir) . '/';

  return $dir;
}


/**
 * Fix the problem of symbolics links in the path of the file
 *
 * @param string $file The filename of plugin.
 *
 * @return string The fixed path of a plugin.
 */
function osc_plugin_folder($file) {
  // Sanitize windows paths and duplicated slashes
  $dir = preg_replace('|/+|', '/', str_replace('\\', '/', dirname($file)));
  $dir = preg_replace('#^.*' . osc_content_folder() . '\/plugins\/#', '', $dir) . '/';

  return $dir;
}


/**
 * Serialize the data (usefull at plugins activation)
 *
 * @param $data
 *
 * @return string the data serialized
 */
function osc_serialize($data) {
  if(!is_serialized($data)) {
    if(is_array($data) || is_object($data)) {
      return serialize($data);
    }
  }

  return $data;
}


/**
 * Unserialize the data (usefull at plugins activation)
 *
 * @param $data
 *
 * @return mixed data unserialized
 */
function osc_unserialize($data) {
  if(is_serialized($data)) { // don't attempt to unserialize data that wasn't serialized going in
    return @unserialize($data);
  }

  return $data;
}


/**
 * Checks is $data is serialized or not
 *
 * @param $data
 *
 * @return bool False if not serialized and true if it was.
 */
function is_serialized($data) {
  // if it isn't a string, it isn't serialized
  if(!is_string($data)) {
    return false;
  }
  $data = trim($data);
  if('N;' === $data) {
    return true;
  }
  if(!preg_match('/^([adObis]):/', $data, $badions)) {
    return false;
  }
  switch ($badions[1]) {
    case 'a':
    case 'O':
    case 's':
      if(preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data)) {
        return true;
      }
      break;
    case 'b':
    case 'i':
    case 'd':
      if(preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data)) {
        return true;
      }
      break;
  }

  return false;
}


/**
 * VERY BASIC
 * Perform a POST request, so we could launch fake-cron calls and other core-system calls without annoying the user
 *
 * @param $url   string
 * @param $_data array
 *
 * @return bool false on error or number of bytes sent.
 */
function osc_doRequest($url, $_data) {
  if(ini_get('allow_url_fopen') === false) {
    error_log('enable allow_url_fopen in php.ini' . PHP_EOL);
    return false;
  }
  
  // parse the given URL
  $url = parse_url($url);
  if(!isset($url['host'], $url['path']) || $url === false) {
    return false;
  }
  
  // extract host, path, port:
  $host = $url['host'];
  $path = $url['path'];
  $port = 80;
  
  if(isset($url['port'])) {
    $port = $url['port'];
  }

  if(isset($url['scheme']) && $url['scheme'] === 'https' && !filter_var($host, FILTER_VALIDATE_IP)) {
    $host = 'ssl://' . $host;
    $port = 443;
  }
  
  $fp = @fsockopen($host, $port);

  if($fp === false) {
    return false;
  }
  
  $data = http_build_query($_data);
  $out = 'POST ' . $path . ' HTTP/1.1' . PHP_EOL;
  $out .= 'Host: ' . $url['host'] . PHP_EOL;
  $out .= 'Referer: Osclass ' . osc_version() . PHP_EOL;
  $out .= 'Content-type: application/x-www-form-urlencoded' . PHP_EOL;
  $out .= 'Content-Length: ' . strlen($data) . PHP_EOL;
  $out .= 'Connection: close' . PHP_EOL . PHP_EOL;
  $out .= $data;
  
  $number_bytes_sent = fwrite($fp, $out);
  fclose($fp);

  return $number_bytes_sent; // or false on fwrite() error
}


/**
 * @param $params
 *
 * @return bool
 */
function osc_sendMail($params, $type = '') {
  // DO NOT send mail if it's a demo
  if(defined('DEMO')) {
    return false;
  }
  
  // Check if sending mail was not explicitely stopped by some process
  $check = osc_apply_filter('pre_send_mail_filter', $params, $type);

  if(is_array($check) && isset($check['stop']) && $check['stop'] === true) {
    return false;
  }
  
  osc_run_hook('pre_send_email', $params, $type);

  $mail = new PHPMailer(true);
  $mail->clearAddresses();
  $mail->clearAllRecipients();
  $mail->clearAttachments();
  $mail->clearBCCs();
  $mail->clearCCs();
  $mail->clearCustomHeaders();
  $mail->clearReplyTos();
  
  if(OSC_DEBUG && PHPMAILER_DEBUG_LEVEL >= 1 && PHPMAILER_DEBUG_LEVEL <= 4) {
    $mail->SMTPDebug = PHPMAILER_DEBUG_LEVEL;   // 1 - client only, 2 - client and server (default), 3 - client, server and connection, 4 - low-level information

    $mail->Debugoutput = function($str, $level) {
      file_put_contents(osc_content_path() . 'debug.log', gmdate('Y-m-d H:i:s'). "\t$level\t$str\n", FILE_APPEND | LOCK_EX);
    };
  }
  
  /** @var \PHPMailer $mail */
  $mail = osc_apply_filter('init_send_mail', $mail, $params);

  if(osc_mailserver_pop()) {
    //$pop = new POP3();

    $pop3_host = osc_mailserver_host();
    if(array_key_exists('host', $params)) {
      $pop3_host = $params['host'];
    }

    $pop3_port = osc_mailserver_port();
    if(array_key_exists('port', $params)) {
      $pop3_port = $params['port'];
    }

    $pop3_username = osc_mailserver_username();
    if(array_key_exists('username', $params)) {
      $pop3_username = $params['username'];
    }

    $pop3_password = osc_mailserver_password();
    if(array_key_exists('password', $params)) {
      $pop3_password = $params['password'];
    }

    //$pop->authorise($pop3_host, $pop3_port, 30, $pop3_username, $pop3_password);
    $pop = POP3::popBeforeSmtp($pop3_host, $pop3_port, 30, $pop3_username, $pop3_password, 1);
  }

  if(osc_mailserver_auth()) {
    $mail->isSMTP();
    $mail->SMTPAuth = true;
  } elseif(osc_mailserver_pop()) {
    $mail->isSMTP();
  }

  $smtpSecure = osc_mailserver_ssl();
  if(array_key_exists('password', $params)) {
    $smtpSecure = $params['ssl'];
  }
  
  if($smtpSecure != '') {
    $mail->SMTPSecure = $smtpSecure;
  }

  $stmpUsername = osc_mailserver_username();
  if(array_key_exists('username', $params)) {
    $stmpUsername = $params['username'];
  }
  
  if($stmpUsername != '') {
    $mail->Username = $stmpUsername;
  }

  $smtpPassword = osc_mailserver_password();
  if(array_key_exists('password', $params)) {
    $smtpPassword = $params['password'];
  }
  
  if($smtpPassword != '') {
    $mail->Password = $smtpPassword;
  }

  $smtpHost = osc_mailserver_host();
  if(array_key_exists('host', $params)) {
    $smtpHost = $params['host'];
  }
  
  if($smtpHost != '') {
    $mail->Host = $smtpHost;
  }

  $smtpPort = osc_mailserver_port();
  if(array_key_exists('port', $params)) {
    $smtpPort = $params['port'];
  }
  
  if($smtpPort != '') {
    $mail->Port = $smtpPort;
  }

  $from = osc_mailserver_mail_from();
  if(empty($from)) {
    $from = 'osclass@' . osc_get_domain();
    if(array_key_exists('from', $params)) {
      $from = $params['from'];
    }
  }

  $from_name = osc_mailserver_name_from();
  if(empty($from_name)) {
    $from_name = osc_page_title();
    if(array_key_exists('from_name', $params)) {
      $from_name = $params['from_name'];
    }
  }

  $mail->From = osc_apply_filter('mail_from', $from, $params);
  $mail->FromName = osc_apply_filter('mail_from_name', $from_name, $params);

  $to = $params['to'];
  $to_name = '';
  if(array_key_exists('to_name', $params)) {
    $to_name = $params['to_name'];
  }

  if(!is_array($to)) {
    $to = array($to => $to_name);
  }

  $no_recipient = true;
  foreach($to as $to_email => $to_name) {
    if(trim((string)$to_email) <> '') {
      $no_recipient = false;
      $mail->addAddress($to_email, $to_name);
    }
  }

  // There is no one defined as target of this mail
  if($no_recipient) {
    //error_log();
    return false;
  }
  
  if(array_key_exists('add_bcc', $params)) {
    if(!is_array($params['add_bcc']) && $params['add_bcc'] != '') {
      $params['add_bcc'] = array($params['add_bcc']);
    }

    foreach ($params['add_bcc'] as $bcc) {
      $mail->addBCC($bcc);
    }
  }

  if(array_key_exists('reply_to', $params)) {
    if(trim((string)$params['reply_to']) != '') {
      $mail->addReplyTo($params['reply_to']);
    }
  }

  $mail->Subject = $params['subject'];
  $mail->Body = $params['body'];

  if(array_key_exists('attachment', $params)) {
    if(!is_array($params['attachment']) || isset($params['attachment']['path'])) {
      $params['attachment'] = array($params['attachment']);
    }

    foreach ($params['attachment'] as $attachment) {
      if(is_array($attachment)) {
        if(isset($attachment['path']) && isset($attachment['name'])) {
          try {
            $mail->addAttachment($attachment['path'], $attachment['name']);
          } catch (phpmailerException $e) {
            continue;
          }
        }
      } else {
        try {
          $mail->addAttachment($attachment);
        } catch (phpmailerException $e) {
          continue;
        }
      }
    }
  }

  $mail->CharSet = 'utf-8';
  $mail->isHTML();

  $mail = osc_apply_filter('pre_send_mail', $mail, $params, $type);
  
  // Check if $mail was not invalidated in filter
  if($mail === false) {
    return false;
  }

  osc_run_hook('before_send_email', $params, $mail, $type);

  // Send email!
  try {
    $mail->send();
    
  } catch (phpmailerException $e) {
    trigger_error($e->errorMessage(), (OSC_DEBUG ? E_USER_NOTICE : E_USER_WARNING));      // E_USER_NOTICE, E_USER_WARNING, E_USER_ERROR
    return false;
    
  } catch (Exception $e) {
    trigger_error($e->errorMessage(), (OSC_DEBUG ? E_USER_NOTICE : E_USER_WARNING));      // E_USER_NOTICE, E_USER_WARNING, E_USER_ERROR
    return false;
  }

  osc_run_hook('after_send_email', $params, $mail);

  return true;
}


/**
 * @param $text
 * @param $params
 *
 * @return mixed
 */
function osc_mailBeauty($text, $params) {
  $text = str_ireplace($params[0], $params[1], $text);
  
  $kwords = array(
    '{WEB_URL}',
    '{WEB_TITLE}',
    '{WEB_LINK}',
    '{CURRENT_DATE}',
    '{HOUR}',
    '{IP_ADDRESS}',
    '{LOCALE_CODE}',
    '{LOCALE_SHORT_CODE}'
  );
  
  $rwords = array(
    osc_base_url(),
    osc_page_title(),
    '<a href="' . osc_base_url() . '">' . osc_page_title() . '</a>',
    date(osc_date_format() ?: 'Y-m-d') . ' ' . date(osc_time_format() ?: 'H:i:s'),
    date(osc_time_format() ?: 'H:i'),
    osc_get_ip(),
    osc_current_user_locale_code(),
    substr(osc_current_user_locale_code(), 0, 2)
  );
  
  $text = str_ireplace($kwords, $rwords, $text);

  return $text;
}


/**
 * @param    $dir
 * @param int  $mode
 * @param bool $recursive
 *
 * @return bool
 */
function osc_mkdir($dir, $mode = 0755, $recursive = true) {
  if($dir === null || $dir === '') {
    return false;
  }
  
  if(is_dir($dir) || $dir === '/') {
    return true;
  }
  
  if(osc_mkdir(dirname($dir), $mode, $recursive)) {
    return @mkdir($dir, $mode);
  }

  return false;
}


/**
 * @param     $source
 * @param     $dest
 * @param array $options
 *
 * @return bool
 */
function osc_copy($source, $dest, $options = array('folderPermission' => 0755, 'filePermission' => 0755)) {
  $result = true;
  
  if(is_file($source)) {
    if($dest[strlen($dest) - 1] === '/') {
      if(!file_exists($dest)) {
        osc_mkdir($dest, $options['folderPermission']);
      }
      $__dest = $dest . '/' . basename($source);
    } else {
      $__dest = $dest;
    }
    if(function_exists('copy')) {
      $result = @copy($source, $__dest);
    } else {
      $result = osc_copyemz($source, $__dest);
    }
    @chmod($__dest, $options['filePermission']);
  } elseif(is_dir($source)) {
    if($dest[strlen($dest) - 1] === '/') {
      if($source[strlen($source) - 1] === '/') {
        //Copy only contents
      } else {
        //Change parent itself and its contents
        $dest = $dest . basename($source);
        if(@!mkdir($dest) && !is_dir($dest)) {
          throw new RuntimeException(sprintf('Directory "%s" was not created', $dest));
        }
        @chmod($dest, $options['filePermission']);
      }
    } elseif($source[strlen($source) - 1] === '/') {
      //Copy parent directory with new name and all its content
      if(@!mkdir($dest, $options['folderPermission']) && !is_dir($dest)) {
        throw new RuntimeException(sprintf('Directory "%s" was not created', $dest));
      }
      @chmod($dest, $options['filePermission']);
    } else {
      //Copy parent directory with new name and all its content
      if(@!mkdir($dest, $options['folderPermission']) && !is_dir($dest)) {
        throw new RuntimeException(sprintf('Directory "%s" was not created', $dest));
      }
      @chmod($dest, $options['filePermission']);
    }

    $dirHandle = opendir($source);
    $result = true;
    while ($file = readdir($dirHandle)) {
      if($file !== '.' && $file !== '..') {
        if(!is_dir($source . '/' . $file)) {
          $__dest = $dest . '/' . $file;
        } else {
          $__dest = $dest . '/' . $file;
        }
        //echo "$source/$file ||| $__dest<br />";
        $data = osc_copy($source . '/' . $file, $__dest, $options);
        if($data == false) {
          $result = false;
        }
      }
    }
    closedir($dirHandle);
  } else {
    $result = true;
  }

  return $result;
}


/**
 * @param $file1
 * @param $file2
 *
 * @return bool
 */
function osc_copyemz($file1, $file2) {
  $contentx = @file_get_contents($file1);
  $openedfile = fopen($file2, 'wb');
  fwrite($openedfile, $contentx);
  fclose($openedfile);
  if($contentx === false) {
    $status = false;
  } else {
    $status = true;
  }

  return $status;
}


// RENAME DIRECTORY
// Usable on osclass upgrade, when oc-content or oc-admin folder names are changed
function osc_rename_dir($path, $old_dir, $new_dir, $clean_before_rename = false) {
  // Basic validation
  if(empty($path) || empty($old_dir) || empty($new_dir) || trim($old_dir, '/\\') == trim($new_dir, '/\\')) {
    return 1;
  }

  // Reject dangerous directory names
  $dangerous = array('.', '..', './', '../');
  if(in_array($old_dir, $dangerous, true) || in_array($new_dir, $dangerous, true)) {
    return 2;
  }

  // Normalize base path
  $path = rtrim($path, '/\\');
  $src = $path . '/' . trim($old_dir, '/\\');
  $dst = $path . '/' . trim($new_dir, '/\\');

  // Old directory must exist
  if(!is_dir($src)) {
    return 3;
  }

  // Destination folder exists
  if(is_dir($dst)) {
    if($clean_before_rename) {
      // Delete recursively
      $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dst, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
      );
      
      foreach($it as $file) {
        if($file->isDir()) {
          @rmdir($file->getRealPath());
        } else {
          @unlink($file->getRealPath());
        }
      }
      
      @rmdir($dst);
      
      // Rename normally
      if(!@rename($src, $dst)) {
        return 4;
      }
      
    } else {
      // Merge src into dst
      $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
      );
      
      foreach($it as $item) {
        $destPath = $dst . substr($item->getRealPath(), strlen($src));
        if($item->isDir()) {
          if(!is_dir($destPath)) {
            @mkdir($destPath, 0755, true);
          }
        } else {
          @copy($item->getRealPath(), $destPath);
        }
      }
      
      // Delete original src after merge
      $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
      );
      
      foreach($it as $file) {
        if($file->isDir()) {
          @rmdir($file->getRealPath());
        } else {
          @unlink($file->getRealPath());
        }
      }
      
      @rmdir($src);
    }
    
  } else {
    // Destination does not exist - do normal rename
    if(!@rename($src, $dst)) {
      // Try making source writable
      $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
      );
      
      foreach($it as $file) {
        @chmod($file->getRealPath(), 0755);
      }
      
      @chmod($src, 0755);

      if(!@rename($src, $dst)) {
        return 4; // rename failed
      }
    }
  }

  // Clear cached filesystem info
  clearstatcache(true, $src);
  clearstatcache(true, $dst);

  return 0;
}


/**
 * Dump osclass database into path file
 *
 * @param string $path
 * @param string $file
 *
 * @return int
 */
function osc_dbdump($path, $file) {
  require_once LIB_PATH . 'osclass/model/Dump.php';
  if(!is_writable($path)) {
    return -4;
  }
  
  if($path == '') {
    return -1;
  }

  //checking connection
  $dump = Dump::newInstance();
  if(!$dump) {
    return -2;
  }

  $path .= $file;
  $result = $dump->showTables();

  if(!$result) {
    $_str = '';
    $_str .= '/* no tables in ' . DB_NAME . ' */';
    $_str .= "\n";

    $f = fopen($path, 'ab');
    fwrite($f, $_str);
    fclose($f);

    return -3;
  }

  $_str = '/* OSCLASS MYSQL Autobackup (' . date(osc_date_format() ?: 'Y-m-d') . ' ' . date(osc_time_format() ?: 'H:i:s') . ') */' . "\n";

  $f = fopen($path, 'ab');
  fwrite($f, $_str);
  fclose($f);

  $tables = array();
  foreach ($result as $_table) {
    $tableName = current($_table);
    $tables[$tableName] = $tableName;
  }

  $tables_order = array(
    't_locale',
    't_country',
    't_currency',
    't_region',
    't_city',
    't_city_area',
    't_widget',
    't_admin',
    't_user',
    't_user_description',
    't_category',
    't_category_description',
    't_category_stats',
    't_item',
    't_item_description',
    't_item_location',
    't_item_stats',
    't_item_resource',
    't_item_comment',
    't_preference',
    't_user_preferences',
    't_pages',
    't_pages_description',
    't_plugin_category',
    't_cron',
    't_alerts',
    't_keywords',
    't_meta_fields',
    't_meta_categories',
    't_item_meta'
  );
  
  // Backup default Osclass tables in order, so no problem when importing them back
  foreach ($tables_order as $table) {
    if(array_key_exists(DB_TABLE_PREFIX . $table, $tables)) {
      $dump->table_structure($path, DB_TABLE_PREFIX . $table);
      $dump->table_data($path, DB_TABLE_PREFIX . $table);
      unset($tables[DB_TABLE_PREFIX . $table]);
    }
  }

  // Backup the rest of tables
  foreach ($tables as $table) {
    $dump->table_structure($path, $table);
    $dump->table_data($path, $table);
  }

  return 1;
}


/**
 * Returns true if there is curl on system environment
 *
 * @return bool
 */
function testCurl() {
  if(function_exists('curl_init') && function_exists('curl_exec')) {
    return true;
  }
  
  return false;
}


/**
 * Returns true if there is fsockopen on system environment
 *
 * @return bool|\type
 */
function testFsockopen() {
  if(function_exists('fsockopen')) {
    return true;
  }

  return false;
}


/**
 * IF http-chunked-decode not exist implement here
 *
 * @since 3.0
 */
if(!function_exists('http_chunked_decode')) {
  /**
   * dechunk an http 'transfer-encoding: chunked' message
   *
   * @param string $chunk the encoded message
   *
   * @return string the decoded message.  If $chunk wasn't encoded properly it will be returned unmodified.
   */
  function http_chunked_decode($chunk) {
    $pos = 0;
    $len = strlen($chunk);
    $dechunk = null;
    while (($pos < $len)
      && ($chunkLenHex = substr($chunk, $pos, ($newlineAt = strpos($chunk, "\n", $pos + 1)) - $pos))) {
      if(!is_hex($chunkLenHex)) {
        trigger_error('Value is not properly chunk encoded', E_USER_WARNING);

        return $chunk;
      }

      $pos = $newlineAt + 1;
      $chunkLen = hexdec(rtrim($chunkLenHex, "\r\n"));
      $dechunk  .= substr($chunk, $pos, $chunkLen);
      $pos = strpos($chunk, "\n", $pos + $chunkLen) + 1;
    }

    return $dechunk;
  }
}

/**
 * determine if a string can represent a number in hexadecimal
 *
 * @param string $hex
 *
 * @return boolean true if the string is a hex, otherwise false
 * @since 3.0
 *
 */
function is_hex($hex) {
  // regex is for weenies
  $hex = strtolower(trim(ltrim($hex, '0')));
  if(empty($hex)) {
    $hex = 0;
  }
  $dec = hexdec($hex);

  return ($hex == dechex($dec));
}


/**
 * Process response and return headers and body
 *
 * @param type $content
 *
 * @return array|\type
 * @since 3.0
 */
function processResponse($content) {
  $res = explode("\r\n\r\n", $content);
  $headers = $res[0];
  $body = isset($res[1]) ? $res[1] : '';

  if(!is_string($headers)) {
    return array();
  }

  return array('headers' => $headers, 'body' => $body);
}


/**
 * Parse headers and return into array format
 *
 * @param string $headers
 *
 * @return array|\type
 */
function processHeaders($headers) {
  $headers = str_replace("\r\n", "\n", $headers);
  $headers = preg_replace('/\n[ \t]/', ' ', $headers);
  $headers = explode("\n", $headers);
  $tmpHeaders = $headers;
  $headers = array();

  foreach ($tmpHeaders as $aux) {
    if(preg_match('/^(.*):\s(.*)$/', $aux, $matches)) {
      $headers[strtolower($matches[1])] = $matches[2];
    }
  }

  return $headers;
}


/**
 * Download file using fsockopen
 *
 * @param string $sourceFile
 * @param mixed  $fileout
 *
 * @param null   $post_data
 *
 * @return bool|string
 * @since 3.0
 */
function download_fsockopen($sourceFile, $fileout = null, $post_data = null) {
  // parse URL
  $aUrl = parse_url($sourceFile);
  $host = (isset($aUrl['host']) ? $aUrl['host'] : 'localhost');
  $headers = osc_req_headers();
  
  if('localhost' === strtolower($host)) {
    $host = '127.0.0.1';
  }

  $link = $aUrl['path'] . (isset($aUrl['query']) ? '?' . $aUrl['query'] : '');

  if(empty($link)) {
    $link .= '/';
  }

  $fp = @fsockopen($host, 80, $errno, $errstr, 30);
  
  if(!$fp) {
    return false;
  }

  $ua = Params::getServerParam('HTTP_USER_AGENT') . ' Osclass (v.' . osc_version() . ')';
  $out = ($post_data != null && is_array($post_data) ? 'POST' : 'GET') . " $link HTTP/1.1\r\n";
  $out .= "Host: $host\r\n";
  $out .= "User-Agent: $ua\r\n";
  $out .= "Connection: Close\r\n";
  
  if(!empty($headers)) {
    $out .= implode("\r\n", $headers) . "\r\n";
  }

  $out .= "\r\n";

  if($post_data != null && is_array($post_data)) {
    $out .= http_build_query($post_data);
  }
  
  fwrite($fp, $out);

  $contents = '';
  while (!feof($fp)) {
    $contents .= fgets($fp, 1024);
  }

  fclose($fp);

  // check redirections ?
  // if(redirections) then do request again
  $aResult = processResponse($contents);
  $headers = processHeaders($aResult['headers']);

  $location = @$headers['location'];
  if(isset($location) && $location != '') {
    $aUrl = parse_url($headers['location']);

    $host = $aUrl['host'];
    if('localhost' === strtolower($host)) {
      $host = '127.0.0.1';
    }

    $requestPath = $aUrl['path'] . (isset($aUrl['query']) ? '?' . $aUrl['query'] : '');

    if(empty($requestPath)) {
      $requestPath .= '/';
    }

    download_fsockopen($host, $requestPath, $fileout);
  } else {
    $body = $aResult['body'];
    $transferEncoding = @$headers['transfer-encoding'];
    if($transferEncoding === 'chunked') {
      $body = http_chunked_decode($aResult['body']);
    }
    if($fileout != null) {
      $ff = @fopen($fileout, 'wb+');
      if($ff !== false) {
        fwrite($ff, $body);
        fclose($ff);

        return true;
      }

      return false;
    }

    return $body;
  }
}


/**
 * @param    $sourceFile
 * @param    $downloadedFile
 * @param null $post_data
 *
 * @return bool
 */
function osc_downloadFile($sourceFile, $downloadedFile, $post_data = null) {
  if(strpos($downloadedFile, '../') !== false || strpos($downloadedFile, "..\\") !== false) {
    return false;
  }

  if(testCurl()) {
    @set_time_limit(0);
    $fp = @fopen(osc_content_path() . 'downloads/' . $downloadedFile, 'wb+');
    
    if($fp) {
      $ch = curl_init($sourceFile);
      
      @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
      curl_setopt($ch, CURLOPT_USERAGENT, Params::getServerParam('HTTP_USER_AGENT') . ' Osclass (v.' . osc_version() . ')');
      curl_setopt($ch, CURLOPT_FILE, $fp);
      @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_REFERER, osc_base_url());

      if(stripos($sourceFile, 'https') !== false) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
      }
      if($post_data != null) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
      }

      curl_exec($ch);
      fclose($fp);

      return true;
    }

    return false;
  }

  if(testFsockopen()) { // test curl/fsockopen
    $downloadedFile = osc_content_path() . 'downloads/' . $downloadedFile;
    download_fsockopen($sourceFile, $downloadedFile);

    return true;
  }

  return false;
}

/**
 * @param    $url
 * @param null $post_data
 *
 * @return bool|string|null
 */
function osc_file_get_contents($url, $post_data = '', $time_limit_ms = 0, $format = '') {
  $response = null;
  $http_code = null;
  $body = null;
  
  $post_data = ($post_data == '' ? null : $post_data);
  $time_limit_ms = ($time_limit_ms > 0 ? $time_limit_ms : 10000);
  
  if(testCurl()) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $time_limit_ms);
    curl_setopt($ch, CURLOPT_USERAGENT, Params::getServerParam('HTTP_USER_AGENT') . ' Osclass (v.' . osc_version() . ')');
    
    if(!defined('CURLOPT_RETURNTRANSFER')) {
      define('CURLOPT_RETURNTRANSFER', 1);
    }
    
    @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_REFERER, osc_base_url());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, osc_req_headers());

    if(stripos($url, 'https') !== false) {
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    }

    if($post_data != null) {
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $header_size);
    $body = substr($response, $header_size);

    // print_r($response); exit;
    //echo '<p><strong>' . sprintf(__('Load took %s seconds'), round(@curl_getinfo($ch)['total_time'], 2)) . '</strong></p>';
    
  } elseif(testFsockopen()) {
    $response = download_fsockopen($url, null, $post_data);
  }

  // Check if expected format is JSON, if yes return as array
  if($format == 'json') {
    if($response != '') {
      $response_arr = json_decode($response, true);
    
      if(json_last_error() === JSON_ERROR_NONE && is_array($response_arr)) {
        return $response_arr;
      }
    }
    
    // Json is invalid, add some additional info
    $msg = osc_esc_html(trim(preg_replace('/\s+/', ' ', (strip_tags((string)($body != '' ? $body : $response))))));
    
    $output = array(
      'status' => 'ERR',
      'message' => '[' . $http_code . '] ' . ($msg != '' ? $msg : _m('Empty server response')),
      'httpcode' => $http_code,
      'body' => $body,
      'response' => $response,
      'json_error_code' => json_last_error(),
      'json_error_message' => json_last_error_msg()
    );
    
    return $output;
  }

  return $response;
}


// Call URL and expect json response
function osc_file_get_contents_json($url, $post_data = '', $time_limit_ms = 0) {
  return osc_file_get_contents($url, $post_data, $time_limit_ms, 'json');
}


// Create headers for osclass requests
function osc_req_headers() {
  $headers = array(
    'X-Request-Id: ' . osc_csrfguard_generate_token(),      // bin2hex(random_bytes(8))
    'X-Api-Version: 3.0',
    'X-Api-Key: ' . rawurlencode(osc_get_preference('osclasspoint_api_key', 'osclass')),
    'X-Email: ' . rawurlencode(osc_contact_email()),
    'X-Ip-Address: ' . rawurlencode(osc_get_ip()),
    'X-Osclass-Version: ' . rawurlencode(osc_version(true)),
    'X-Url: ' . rawurlencode(osc_base_url()),
    'X-Domain: ' . rawurlencode(osc_get_parent_domain()),
    'X-Subdomain-Enabled: ' . rawurlencode((string)(osc_subdomain_enabled() ? 1 : 0)),
    'X-Subdomain-Type: ' . rawurlencode(osc_subdomain_type())
  );

  return $headers;
}

/**
 * Check if we loaded some specific module of apache
 *
 * @param string $mod
 *
 * @return bool
 */
function apache_mod_loaded($mod) {
  if(function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    
    if(in_array($mod, $modules)) {
      return true;
    }
    
  } elseif(function_exists('phpinfo')) {
    ob_start();
    phpinfo(INFO_MODULES);
    
    $content = ob_get_clean();
    
    if(stripos($content, $mod) !== false) {
      return true;
    }
  }

  return false;
}


/**
 * Change version to param number
 *
 * @param mixed version
 */
function osc_changeVersionTo($version = null) {
  if($version != null) {
    osc_set_preference('version', $version);
    osc_reset_preferences();
  }
}


/**
 * @param $array
 *
 * @return string
 */
function strip_slashes_extended($array) {
  if(is_array($array)) {
    foreach ($array as $k => &$v) {
      $v = strip_slashes_extended($v);
    }
  } else {
    $array = stripslashes($array);
  }

  return $array;
}


/**
 * Unzip's a specified ZIP file to a location
 *
 * @param string $file Full path of the zip file
 * @param string $to   Full path where it is going to be unzipped
 *
 * @return int
 *  0 - destination folder not writable (or not exist and cannot be created)
 *  1 - everything was OK
 *  2 - zip is empty
 *  -1 : file could not be created (or error reading the file from the zip)
 */
function osc_unzip_file($file, $to) {
  if(strpos($to, '../') !== false || strpos($to, "..\\") !== false) {
    return 0;
  }

  if(!file_exists($to) && @!mkdir($to, 0766) && !is_dir($to)) {
    return 0;
  }

  @chmod($to, 0755);

  if(!is_writable($to)) {
    return 0;
  }

  if(class_exists('ZipArchive')) {
    return _unzip_file_ziparchive($file, $to);
  }

  // if ZipArchive class doesn't exist, we use PclZip
  return _unzip_file_pclzip($file, $to);
}


/**
 * We assume that the $to path is correct and can be written. It unzips an archive using the PclZip library.
 *
 * @param string $file Full path of the zip file
 * @param string $to   Full path where it is going to be unzipped
 *
 * @return int
 */
function _unzip_file_ziparchive($file, $to) {
  if(strpos($to, '../') !== false || strpos($to, "..\\") !== false) {
    return 0;
  }

  $zip = new ZipArchive();
  $zipopen = $zip->open($file, 4);

  if($zipopen !== true) {
    return 2;
  }
  
  // The zip is empty
  if($zip->numFiles == 0) {
    return 2;
  }

  for ($i = 0; $i < $zip->numFiles; $i++) {
    $file = $zip->statIndex($i);

    // Fix language problem - when parent directory is not identified
    if($i == 0 && isset($file['name']) && isset($file['crc']) && $file['crc'] > 0) {   // is not dir
      $name_with_folder = explode('/', $file['name']);
      
      //if(count($name_with_folder) > 1 && strlen((string)$name_with_folder[0]) == 5) {  // it's language folder
      if(count($name_with_folder) > 1) {   // First file is path already, missing creation of parent dir
        $folder = $name_with_folder[0];
        @mkdir($to . $folder);    // create lang folder in ../downloads/oc-temp/de_DE/
      }
    }

    if(!$file) {
      return -1;
    }

    if(strpos($file['name'], '__MACOSX/') === 0) {
      continue;
    }
    if(strpos($file['name'], '../') !== false) {
      continue;
    }

    if(substr($file['name'], -1) === '/') {
      if(@!mkdir($concurrentDirectory = $to . $file['name'], 0755) && !is_dir($concurrentDirectory)) {
        throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
      }
      continue;
    }

    $content = $zip->getFromIndex($i);
    if($content === false) {
      return -1;
    }

    $fp = @fopen($to . $file['name'], 'wb');
    if(!$fp) {
      return -1;
    }

    @fwrite($fp, $content);
    @fclose($fp);
  }

  $zip->close();

  return 1;
}


/**
 * We assume that the $to path is correct and can be written. It unzips an archive using the PclZip library.
 *
 * @param string $zip_file Full path of the zip file
 * @param string $to     Full path where it is going to be unzipped
 *
 * @return int
 */
function _unzip_file_pclzip($zip_file, $to) {
  if(strpos($to, '../') !== false || strpos($to, "..\\") !== false) {
    return false;
  }

  $archive = new PclZip($zip_file);
  $files = $archive->extract(PCLZIP_OPT_EXTRACT_AS_STRING);
  if(($files) == false) {
    return 2;
  }

  // check if the zip is not empty
  if(count($files) === 0) {
    return 2;
  }

  // Extract the files from the zip
  foreach ($files as $file) {
    if(strpos($file['filename'], '__MACOSX/') === 0) {
      continue;
    }
    if(strpos($file['filename'], '../') !== false) {
      continue;
    }

    if($file['folder']) {
      if(@!mkdir($concurrentDirectory = $to . $file['filename'], 0755) && !is_dir($concurrentDirectory)) {
        throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
      }
      continue;
    }


    $fp = @fopen($to . $file['filename'], 'wb');
    if(!$fp) {
      return -1;
    }

    @fwrite($fp, $file['content']);
    @fclose($fp);
  }

  return 1;
}


/**
 * Common interface to zip a specified folder to a file using ziparchive or pclzip
 *
 * @param string $archive_folder full path of the folder
 * @param string $archive_name   full path of the destination zip file
 *
 * @return int
 */
function osc_zip_folder($archive_folder, $archive_name) {
  if(strpos($archive_folder, '../') !== false || strpos($archive_name, '../') !== false || strpos($archive_folder, "..\\") !== false
    || strpos($archive_name, "..\\") !== false
 ) {
    return false;
  }

  if(class_exists('ZipArchive')) {
    return _zip_folder_ziparchive($archive_folder, $archive_name);
  }

  // if ZipArchive class doesn't exist, we use PclZip
  return _zip_folder_pclzip($archive_folder, $archive_name);
}


/**
 * Zips a specified folder to a file
 *
 * @param string $archive_folder full path of the folder
 * @param string $archive_name   full path of the destination zip file
 *
 * @return int
 */
function _zip_folder_ziparchive($archive_folder, $archive_name) {
  if(strpos($archive_folder, '../') !== false || strpos($archive_name, '../') !== false || strpos($archive_folder, "..\\") !== false
    || strpos($archive_name, "..\\") !== false
 ) {
    return false;
  }

  $zip = new ZipArchive;
  if($zip->open($archive_name, ZipArchive::CREATE) === true) {
    $dir = preg_replace('/[\/]{2,}/', '/', $archive_folder . '/');

    $dirs = array($dir);
    while (count($dirs)) {
      $dir = current($dirs);
      $zip->addEmptyDir(str_replace(ABS_PATH, '', $dir));

      $dh = opendir($dir);
      while (false !== ($_file = readdir($dh))) {
        if($_file !== '.' && $_file !== '..' && stripos($_file, 'Osclass_backup.') === false) {
          if(is_file($dir . $_file)) {
            $zip->addFile($dir . $_file, str_replace(ABS_PATH, '', $dir . $_file));
          } elseif(is_dir($dir . $_file)) {
            $dirs[] = $dir . $_file . '/';
          }
        }
      }
      closedir($dh);
      array_shift($dirs);
    }
    $zip->close();

    return true;
  }

  return false;
}


/**
 * Zips a specified folder to a file
 *
 * @param string $archive_folder full path of the folder
 * @param string $archive_name   full path of the destination zip file
 *
 * @return int
 */
function _zip_folder_pclzip($archive_folder, $archive_name) {
  if(strpos($archive_folder, '../') !== false || strpos($archive_name, '../') !== false || strpos($archive_folder, "..\\") !== false
    || strpos($archive_name, "..\\") !== false
 ) {
    return false;
  }

  $zip = new PclZip($archive_name);
  if($zip) {
    $dir = preg_replace('/[\/]{2,}/', '/', $archive_folder . '/');

    $v_dir = osc_base_path();
    $v_remove = $v_dir;

    // To support windows and the C: root you need to add the
    // following 3 lines, should be ignored on linux
    if($v_dir[1] === ':') {
      $v_remove = substr($v_dir, 2);
    }
    $v_list = $zip->create($dir, PCLZIP_OPT_REMOVE_PATH, $v_remove);

    return !($v_list == 0);
  }

  return false;
}


/**
 * @return bool
 */
function osc_check_recaptcha() {
  if(!osc_recaptcha_enabled() || trim(osc_recaptcha_private_key()) == '') {
    return true;
  }
  
  $gReCaptchaResponse = Params::getParam('g-recaptcha-response');
  
  if($gReCaptchaResponse !== '' || $gReCaptchaResponse !== false || $gReCaptchaResponse !== 0) {
    $recaptcha = new ReCaptcha(osc_recaptcha_private_key());
    $resp = $recaptcha->verify($gReCaptchaResponse, osc_get_ip());
    
    if($resp->isSuccess()) {
      return true;
    }
  }

  return false;
}


/**
 * replace double slash with single slash
 *
 * @param $path
 *
 * @return string
 */
function osc_replace_double_slash($path) {
  return str_replace('//', '/', $path);
}


/**
 * @param mixed|string $dir
 *
 * @return bool
 */
function osc_check_dir_writable($dir = ABS_PATH) {
  if(strpos($dir, '../') !== false || strpos($dir, "..\\") !== false) {
    return false;
  }

  clearstatcache();
  if($dh = opendir($dir)) {
    while (($file = readdir($dh)) !== false) {
      if($file !== '.' && $file !== '..') {
        if(is_dir(osc_replace_double_slash($dir . '/' . $file))) {
          if(osc_replace_double_slash($dir) === (osc_content_path() . 'themes')) {
            if($file === 'bender' || $file === 'index.php') {
              $res = osc_check_dir_writable(osc_replace_double_slash($dir . '/' . $file));
              if(!$res) {
                return false;
              }
            }
          } elseif(osc_replace_double_slash($dir) === (osc_content_path() . 'plugins')) {
            if($file === 'google_maps' || $file === 'google_analytics' || $file === 'index.php') {
              $res = osc_check_dir_writable(osc_replace_double_slash($dir . '/' . $file));
              if(!$res) {
                return false;
              }
            }
          } elseif(osc_replace_double_slash($dir) === (osc_content_path() . 'languages')) {
            if($file === 'en_US' || $file === 'index.php') {
              $res = osc_check_dir_writable(osc_replace_double_slash($dir . '/' . $file));
              if(!$res) {
                return false;
              }
            }
          } elseif(osc_replace_double_slash($dir) === (osc_content_path() . 'downloads')) {
            continue;
          } elseif(osc_replace_double_slash($dir) === osc_uploads_path()) {
            continue;
          } else {
            $res = osc_check_dir_writable(osc_replace_double_slash($dir . '/' . $file));
            if(!$res) {
              return false;
            }
          }
        } else {
          return is_writable(osc_replace_double_slash($dir . '/' . $file));
        }
      }
    }
    closedir($dh);
  }

  return true;
}


/**
 * @param mixed|string $dir
 *
 * @return bool
 */
function osc_change_permissions($dir = ABS_PATH) {
  if(strpos($dir, '../') !== false || strpos($dir, "..\\") !== false) {
    return false;
  }

  clearstatcache();
  if($dh = opendir($dir)) {
    while (($file = readdir($dh)) !== false) {
      if($file !== '.' && $file !== '..' && $file[0] !== '.') {
        // Update os812 make sure file does not get 755
        if(is_dir(osc_replace_double_slash($dir . '/' . $file)) && !is_writable(osc_replace_double_slash($dir . '/' . $file))) {
          $result = chmod(str_replace('//', '/', $dir . '/' . $file), 0755);
        }

        if(is_dir(osc_replace_double_slash($dir . '/' . $file))) {
          if(osc_replace_double_slash($dir) === (osc_content_path() . 'themes')) {
            if($file === 'modern' || $file === 'sigma' || $file === 'bender' || $file === 'index.php') {
              $res = osc_change_permissions(str_replace('//', '/', $dir . '/' . $file));
              if(!$res) {
                return false;
              }
            }
          } elseif(osc_replace_double_slash($dir) === (osc_content_path() . 'plugins')) {
            if($file === 'google_maps' || $file === 'google_analytics' || $file === 'index.php') {
              $res = osc_change_permissions(osc_replace_double_slash($dir . '/' . $file));
              if(!$res) {
                return false;
              }
            }
          } elseif(osc_replace_double_slash($dir) === (osc_content_path() . 'languages')) {
            if($file === 'en_US' || $file === 'index.php') {
              $res = osc_change_permissions(osc_replace_double_slash($dir . '/' . $file));
              if(!$res) {
                return false;
              }
            }
          } elseif(osc_replace_double_slash($dir) === (osc_content_path() . 'downloads')) {
            continue;
          } elseif(osc_replace_double_slash($dir) === osc_uploads_path()) {
            continue;
          } else {
            $res = osc_change_permissions(osc_replace_double_slash($dir . '/' . $file));
            if(!$res) {
              return false;
            }
          }
          return true;
        } else {
          // is not dir (file)
          $result = chmod(str_replace('//', '/', $dir . '/' . $file), 0644);
        }

        if(isset($result)) {
          return $result;
        }

        return false;
      }
    }
    closedir($dh);
  }
  return true;
}


/**
 * @param mixed|string $dir
 *
 * @return array|bool
 */
function osc_save_permissions($dir = ABS_PATH) {
  if(strpos($dir, '../') !== false || strpos($dir, "..\\") !== false) {
    return false;
  }

  $perms = array();
  $perms[$dir] = fileperms($dir);
  clearstatcache();
  
  if($dh = opendir($dir)) {
    while (($file = readdir($dh)) !== false) {
      if($file !== '.' && $file !== '..') {
        if(is_dir(str_replace('//', '/', $dir . '/' . $file))) {
          $res = osc_save_permissions(str_replace('//', '/', $dir . '/' . $file));
          foreach ($res as $k => $v) {
            $perms[$k] = $v;
          }
        } else {
          $perms[str_replace('//', '/', $dir . '/' . $file)] = fileperms(str_replace('//', '/', $dir . '/' . $file));
        }
      }
    }
    closedir($dh);
  }

  return $perms;
}


/**
 * @param $price
 *
 * @return string
 */
function osc_prepare_price($price) {
  if(is_numeric($price)) {
    return number_format((float)$price / 1000000, osc_locale_num_dec(), osc_locale_dec_point(), osc_locale_thousands_sep());
  }
  
  return $price;
}


/**
 * Recursive glob function
 *
 * @param string $pattern
 * @param int  $flags
 * @param string $path
 *
 * @return array of files
 */
function rglob($pattern, $flags = 0, $path = '') {
  if(!$path && ($dir = dirname($pattern)) !== '.') {
    if($dir === '\\' || $dir === '/') {
      $dir = '';
    }

    return rglob(basename($pattern), $flags, $dir . '/');
  }
  
  /**
   *
   * $paths = glob($path . '*', GLOB_ONLYDIR | GLOB_NOSORT);
   * $files = glob($path . $pattern, $flags);
   * foreach ($paths as $p) {
   * $files = array_merge($files, rglob($pattern, $flags, $p . '/'));
   * }
   */
  //TODO
  //Better Maybe ?

  $paths = glob($path . '*', GLOB_ONLYDIR | GLOB_NOSORT);
  $files = glob($path . $pattern, $flags);
  
  foreach ($paths as $p) {
    $files[] = rglob($pattern, $flags, $p . '/');
  }
  
  $files = array_merge([], $files);

  return $files;
}


/**
 * Market util functions
 *
 * @param    $update_uri
 * @param null $version
 *
 * @return bool
 */
function osc_check_plugin_update($product_key, $version = null) {
  $uri = _get_market_url('check_version', $product_key);
  
  if($uri != false) {
    return _need_update($product_key, $version);
  }

  return false;
}


/**
 * @param string $product_key
 * @param null   $version
 *
 * @return bool
 */
function osc_check_theme_update($product_key, $version = null) {
  $uri = _get_market_url('check_version', $product_key);
  
  if($uri != false) {
    return _need_update($product_key, $version);
  }

  return false;
}


/**
 * @param string $code
 * @param null   $version
 *
 * @return bool
 */
function osc_check_language_update($code, $version = null) {
  $uri = osc_language_url($code);
  if($uri != false) {
    if(false === ($json = @osc_file_get_contents($uri))) {
      return false;
    }

    $data = json_decode($json, true);
    if(isset($data['s_version'])) {
      $result = version_compare2($version, $data['s_version']);
      if($result == -1) {    // A < B
        // market have a newer version of this language
        $result = version_compare2($data['s_version'], OSCLASS_VERSION);
        if($result == 0 || $result == -1) {    // A <= B
          // market version is compatible with current osclass version
          return true;
        }
      }
    }
  }

  return false;
}


/**
 * @param $type
 * @param $product_key
 *
 * @return bool|string
 */
function _get_market_url($type, $product_key = '') {
  if($product_key == null || $product_key == '') {
    return false;
  }

  if(in_array($type, array('plugins', 'themes', 'check_version', 'download', 'products_version', 'product_updates'))) {
    $uri = osc_market_url($type , $product_key);
    return $uri;
  }

  return false;
}


/**
 * @param $product_key
 * @param $version
 * @param $use_cache
 *
 * @return bool
 */
function _need_update($product_key, $version, $use_cache = false) {
  $data = osc_get_preference('market_products_version', 'osclass');
  $prepare = json_decode($data, true);

  if($use_cache === true || (isset($prepare['date']) && strtotime('-1 day') < strtotime($prepare['date']) && @$prepare['data'] <> '')) {
    $products = isset($prepare['data']) ? $prepare['data'] : array();
  } else {
    $products = osc_file_get_contents_json(_get_market_url('products_version', $product_key));
    
    osc_set_preference('market_products_version', json_encode(array('date' => date('Y-m-d H:i:s'), 'data' => $products)));
    osc_reset_preferences();
  }

  $product = (isset($products[$product_key]) ? $products[$product_key] : false);
  
  if($product !== false) {
    $version_new = (@$product['s_version'] <> '' ? @$product['s_version'] : @$product['version']);

    if($version_new <> '') {
      $result = version_compare2($version_new, $version);
      if($result === 1) {   // A > B
        return true;
      }
    }
  }

  return false;
}


/**
 * Returns
 *    0  if both are equal,
 *    1  if A > B, and
 *   -1  if B > A.
 *
 * @param string $a -> from market
 * @param string $b -> installed version
 *
 * @return int
 */
function version_compare2($a, $b) {
  // in case $a is integer (420), change it to 4.2.0
  if(count(explode('.', $a)) <= 1) {
    $a = implode('.', str_split($a));
  }
  
  // in case $b is integer (420), change it to 4.2.0
  if(count(explode('.', $b)) <= 1) {
    $b = implode('.', str_split($b));
  }
  
  $aA = explode('.', rtrim($a, '.0')); //Split version into pieces and remove trailing .0
  $aB = explode('.', rtrim($b, '.0')); //Split version into pieces and remove trailing .0
  
  foreach ($aA as $depth => $aVal) { //Iterate over each piece of A
    if(isset($aB[$depth])) { //If B matches A to this depth, compare the values
      if($aVal > $aB[$depth]) {
        return 1;     // returns A > B
      }

      if($aVal < $aB[$depth]) {
        return -1;   // returns B > A
      } 
      //An equal result is inconclusive at this point
    } else { //If B does not match A to this depth, then A comes after B in sort order
      if($aVal > 0) {
        return 1; //so return A > B
      } else {
        return 0;
      }
    }
  }
  //At this point, we know that to the depth that A and B extend to, they are equivalent.
  //Either the loop ended because A is shorter than B, or both are equal.
  return (count($aA) < count($aB)) ? -1 : 0;
}


/**
 * @param $aux
 * @param $categoryTotal
 *
 * @return int
 */
function _recursive_category_stats(&$aux, &$categoryTotal) {
  $count_items = Item::newInstance()->numItems($aux);
  if(is_array($aux['categories'])) {
    foreach ($aux['categories'] as &$cat) {
      $count_items += _recursive_category_stats($cat, $categoryTotal);
    }
    unset($cat);
  }
  $categoryTotal[$aux['pk_i_id']] = $count_items;

  return $count_items;
}


/**
 * Update category stats
 *
 * @return void
 */
function osc_update_cat_stats() {
  $categoryTotal = array();
  $aCategories = Category::newInstance()->toTreeAll();

  foreach ($aCategories as &$category) {
    if($category['fk_i_parent_id'] === null) {
      _recursive_category_stats($category, $categoryTotal);
    }
  }
  unset($category);

  $sql = 'REPLACE INTO ' . DB_TABLE_PREFIX . 't_category_stats (fk_i_category_id, i_num_items) VALUES ';
  $aValues = array();
  foreach ($categoryTotal as $k => $v) {
    $aValues[] = "($k, $v)";
  }
  $sql .= implode(',', $aValues);

  CategoryStats::newInstance()->dao->query($sql);
}


/**
 * Recount items for a given a category id
 *
 * @param int $id
 */
function osc_update_cat_stats_id($id) {
  // get sub categorias
  $aCategories = Category::newInstance()->findSubcategories($id);
  $categoryTotal = 0;
  $category = osc_get_category_row($id);

  if(count($aCategories) > 0) {
    // sumar items de la categoría
    foreach ($aCategories as $subcategory) {
      $total = Item::newInstance()->numItems($subcategory);
      $categoryTotal += $total;
    }
    $categoryTotal += Item::newInstance()->numItems($category);
  } else {
    $total = Item::newInstance()->numItems($category);
    $categoryTotal += $total;
  }

  $sql = 'REPLACE INTO ' . DB_TABLE_PREFIX . 't_category_stats (fk_i_category_id, i_num_items) VALUES ';
  $sql .= ' (' . $id . ', ' . $categoryTotal . ')';

  CategoryStats::newInstance()->dao->query($sql);

  if($category['fk_i_parent_id'] != 0) {
    osc_update_cat_stats_id($category['fk_i_parent_id']);
  }
}


/**
 * Update locations stats. I moved this function from cron.daily.php:update_location_stats
 *
 * @param bool $force
 * @param int  $limit
 *
 * @return int
 * @since 3.1
 */
function osc_update_location_stats($force = false, $limit = 1000) {
  $loctmp = LocationsTmp::newInstance();
  $workToDo = $loctmp->count();

  if($workToDo > 0) {
    // there is work to do
    if($limit === 'auto') {
      $total_cities = City::newInstance()->count();
      $limit = max(1000, ceil($total_cities / 22));
    }
    $aLocations = $loctmp->getLocations($limit);
    foreach ($aLocations as $location) {
      $id = $location['id_location'];
      $type = $location['e_type'];
      $data = 0;
      // update locations stats
      switch ($type) {
        case 'COUNTRY':
          $numItems = CountryStats::newInstance()->calculateNumItems($id);
          $data = CountryStats::newInstance()->setNumItems($id, $numItems);
          unset($numItems);
          break;
        case 'REGION':
          $numItems = RegionStats::newInstance()->calculateNumItems($id);
          $data = RegionStats::newInstance()->setNumItems($id, $numItems);
          unset($numItems);
          break;
        case 'CITY':
          $numItems = CityStats::newInstance()->calculateNumItems($id);
          $data = CityStats::newInstance()->setNumItems($id, $numItems);
          unset($numItems);
          break;
        default:
          break;
      }
      if($data >= 0) {
        $loctmp->delete(array(
          'e_type' => $location['e_type'],
          'id_location' => $location['id_location']
       ));
      }
    }
  } elseif($force) {
    // we need to populate location tmp table
    $aCountry = Country::newInstance()->listAll();

    foreach ($aCountry as $country) {
      $aRegionsCountry = Region::newInstance()->findByCountry($country['pk_c_code']);
      $loctmp->insert(array('id_location' => $country['pk_c_code'], 'e_type' => 'COUNTRY'));
      foreach ($aRegionsCountry as $region) {
        $aCitiesRegion = City::newInstance()->findByRegion($region['pk_i_id']);
        $loctmp->insert(array('id_location' => $region['pk_i_id'], 'e_type' => 'REGION'));
        $batchCities = array();
        foreach ($aCitiesRegion as $city) {
          $batchCities[] = $city['pk_i_id'];
        }
        unset($aCitiesRegion);
        $loctmp->batchInsert($batchCities, 'CITY');
        unset($batchCities);
      }
      unset($aRegionsCountry);
    }
    unset($aCountry);
    osc_set_preference('location_todo', LocationsTmp::newInstance()->count());
  }

  return LocationsTmp::newInstance()->count();
}


/** Translate current categories to new locale
 *
 * @param $locale
 *
 * @since 3.2.1
 */
function osc_translate_categories($locale) {
  $old_locale = Session::newInstance()->_get('adminLocale');
  Session::newInstance()->_set('adminLocale', $locale);
  Translation::newInstance()->_load(osc_translations_path() . $locale . '/core.mo', 'cat_' . $locale);
  $catManager = Category::newInstance();
  $old_categories = $catManager->_findNameIDByLocale($old_locale);
  $tmp_categories = $catManager->_findNameIDByLocale($locale);
  foreach ($tmp_categories as $category) {
    $new_categories[$category['pk_i_id']] = $category['s_name'];
  }
  unset($tmp_categories);
  foreach ($old_categories as $category) {
    if(!isset($new_categories[$category['pk_i_id']])) {
      $fieldsDescription['s_name'] = __($category['s_name'], 'cat_' . $locale);
      $fieldsDescription['s_description'] = '';
      $fieldsDescription['fk_i_category_id'] = $category['pk_i_id'];
      $fieldsDescription['fk_c_locale_code'] = $locale;
      $slug_tmp = $slug = osc_sanitizeString(osc_apply_filter('slug', $fieldsDescription['s_name']));
      $slug_unique = 1;
      
      while (true) {
        if(!$catManager->findBySlug($slug)) {
          break;
        }

        $slug = $slug_tmp . '_' . $slug_unique;
        $slug_unique++;
      }
      $fieldsDescription['s_slug'] = $slug;
      $catManager->insertDescription($fieldsDescription);
    }
  }
  Session::newInstance()->_set('adminLocale', $old_locale);
}


/**
 * @return string
 */
function get_ip() {
  return osc_get_ip();
  
  /*
  //DISABLED IN V440
  if(Params::getServerParam('HTTP_CLIENT_IP') != '') {
    return Params::getServerParam('HTTP_CLIENT_IP');
  }

  if(Params::getServerParam('HTTP_X_FORWARDED_FOR') != '') {
    $ip_array = explode(',', Params::getServerParam('HTTP_X_FORWARDED_FOR'));
    foreach ($ip_array as $ip) {
      return trim($ip);
    }
  }

  return Params::getServerParam('REMOTE_ADDR');
  */
}


/**
 * Get visitor IP address
 * @return string
 */
function osc_get_ip() {
  if(isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
  }
  
  $client = @$_SERVER['HTTP_CLIENT_IP'];
  $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
  $remote = @$_SERVER['REMOTE_ADDR'];

  if(filter_var($client, FILTER_VALIDATE_IP)) {
    $ip = $client;
  } elseif(filter_var($forward, FILTER_VALIDATE_IP)) {
    $ip = $forward;
  } else {
    $ip = $remote;
  }

  return $ip;
}


/**
 * CSRFGUARD functions *
 *
 */
function osc_csrfguard_generate_token() {
  // update 420
  $token = Session::newInstance()->_get('octoken');
  
  if($token != '') {
    return $token;
  }

  $token = strtolower(osc_genRandomPassword(12));
  Session::newInstance()->_set('octoken', $token);
  return $token;
  
  
  // $token_name = Session::newInstance()->_get('token_name');
  // if($token_name != '' && Session::newInstance()->_get($token_name) != '') {
    // return array($token_name, Session::newInstance()->_get($token_name));
  // }
  // $unique_token_name = osc_csrf_name() . '_' . mt_rand(0, mt_getrandmax());
  // if(function_exists('hash_algos') and in_array('sha512', hash_algos())) {
    // $token = hash('sha512', mt_rand(0, mt_getrandmax()));
  // } else {
    // $token = '';
    // for ($i = 0; $i < 128; ++$i) {
      // $r = mt_rand(0, 35);
      // if($r < 26) {
        // $c = chr(ord('a') + $r);
      // } else {
        // $c = chr(ord('0') + $r - 26);
      // }
      // $token .= $c;
    // }
  // }
  // Session::newInstance()->_set('token_name', $unique_token_name);
  // Session::newInstance()->_set($unique_token_name, $token);

  // return array($unique_token_name, $token);
}


/**
 * @param $unique_form_name
 * @param $token_value
 *
 * @return bool
 */
/**
 * @param $unique_form_name
 * @param $token_value
 *
 * @return bool
 */
 
  //function osc_csrfguard_validate_token($unique_form_name, $token_value)
  // {
    // $name = Session::newInstance()->_get('token_name');
    // $token = Session::newInstance()->_get($unique_form_name);

    // return $name === $unique_form_name && $token === $token_value;
  // }

function osc_csrfguard_validate_token($token) {
  $token_session = Session::newInstance()->_get('octoken');
  return $token === $token_session;
}


/**
 * @param $form_data_html
 *
 * @return mixed
 */
/**
 * @param $form_data_html
 *
 * @return mixed
 */
function osc_csrfguard_replace_forms($form_data_html) {
  $count = preg_match_all('/<form(.*?)>/is', $form_data_html, $matches, PREG_SET_ORDER);
  if(is_array($matches)) {
    foreach ($matches as $m) {
      if(strpos($m[1], 'nocsrf') !== false || strpos($m[1], 'notoken') !== false || strpos($m[1], 'nooctoken') !== false) {
        continue;
      }
      $form_data_html = str_replace($m[0], "<form{$m[1]}>" . osc_csrf_token_form(), $form_data_html);
    }
  }

  return $form_data_html;
}


function osc_csrfguard_inject() {
  $data = ob_get_clean();
  $data = osc_csrfguard_replace_forms($data);
  echo $data;
}


function osc_csrfguard_start() {
  ob_start();
  $functions = osc_apply_filter('shutdown_functions', array('osc_csrfguard_inject'));
  foreach ($functions as $f) {
    register_shutdown_function($f);
  }
}

/**
 * @param    $url
 * @param null $code
 */
/**
 * @param    $url
 * @param null $code
 */
function osc_redirect_to($url, $code = null) {
  if(ob_get_length() > 0) {
    ob_end_flush();
  }
  
  // Solution suggested by ChatGPT
  // if(ob_get_level() > 0 && ob_get_length() > 0) {
    // ob_clean();
  // }
  
  Cookie::newInstance()->_setRefererHistory();
  Cookie::newInstance()->_setRefererHistory($url);
  
  if($code !== null) {
    header('Location: ' . $url, true, $code);
  } else {
    header('Location: ' . $url);
  }
  
  exit;
}


/**
 * @param $type
 *
 * @return bool|int|mixed
 */
function osc_calculate_location_slug($type) {
  $field = 'pk_i_id';
  switch ($type) {
    case 'country':
      $manager = Country::newInstance();
      $field = 'pk_c_code';
      break;
    case 'region':
      $manager = Region::newInstance();
      break;
    case 'city':
      $manager = City::newInstance();
      break;
    default:
      return false;
      break;
  }
  $locations = $manager->listByEmptySlug();
  $locations_changed = 0;
  foreach ($locations as $location) {
    $slug_tmp = $slug = osc_sanitizeString($location['s_name']);
    $slug_unique = 1;
    while (true) {
      $location_slug = $manager->findBySlug($slug);
      if(!isset($location_slug[$field])) {
        break;
      }

      $slug = $slug_tmp . '-' . $slug_unique;
      $slug_unique++;
    }
    $locations_changed += $manager->update(array('s_slug' => $slug), array($field => $location[$field]));
  }

  return $locations_changed;
}


/**
 * @param $input
 */
function osc_prune_array(&$input) {
  foreach ($input as $key => &$value) {
    if(is_array($value)) {
      osc_prune_array($value);
      if(empty($input[$key])) {
        unset($input[$key]);
      }
    } elseif($value === '' || $value === false || $value === null) {
      unset($input[$key]);
    }
  }
}


// UPGRADE FUNCTION
// Download ZIP from osclass and perform upgrade of system
function osc_do_upgrade() {
  $message = '';
  $error = 0;
  $sql_error_msg = '';
  $rm_errors = 0;
  $perms = osc_save_permissions();
  osc_change_permissions();

  // Enable maintenance mode
  $maintenance_file = ABS_PATH . '.maintenance';
  $fileHandler = @fopen($maintenance_file, 'wb');
  fclose($fileHandler);

  // Download file from osclass-classifieds.com
  $data = osc_file_get_contents_json(osc_osclass_url('download'));
  $source_file = $data['url'];

  if($source_file != '') {
    $tmp = explode('/', $source_file);
    $filename = end($tmp);
    $result = osc_downloadFile($source_file, $filename);

    // Osclass ZIP was saved to temp folder, continue
    if($result) {

      // Unzip file now
      $tmp_path = osc_content_path() . 'downloads/oc-temp/core-' . $data['version'] . '/';
      if(@!mkdir($concurrentDirectory = osc_content_path() . 'downloads/oc-temp/', 0755) && !is_dir($concurrentDirectory)) {
        throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
      }
      
      if(@!mkdir($tmp_path, 0755) && !is_dir($tmp_path)) {
        throw new RuntimeException(sprintf('Directory "%s" was not created', $tmp_path));
      }
      
      $res = osc_unzip_file(osc_content_path() . 'downloads/' . $filename, $tmp_path);
      
      // Unzip successful
      if($res == 1) {
        // Oc-admin folder is renamed
        if(defined('OC_ADMIN_FOLDER') && OC_ADMIN_FOLDER != 'oc-admin') {
          osc_rename_dir($tmp_path, 'oc-admin', OC_ADMIN_FOLDER);
        }
        
        // Oc-content folder is renamed
        if(defined('OC_CONTENT_FOLDER') && OC_CONTENT_FOLDER != 'oc-content') {
          osc_rename_dir($tmp_path, 'oc-content', OC_CONTENT_FOLDER);
        }

        // Oc-includes folder is renamed
        if(defined('OC_INCLUDES_FOLDER') && OC_INCLUDES_FOLDER != 'oc-includes') {
          osc_rename_dir($tmp_path, 'oc-includes', OC_INCLUDES_FOLDER);
        }
        
        // Copy files from extracted zip in temp folder
        $fail = -1;
        
        if($handle = opendir($tmp_path)) {
          $fail = 0;
          
          while(false !== ($_file = readdir($handle))) {
            if($_file !== '.' && $_file !== '..' && (($_file !== OC_CONTENT_FOLDER && $_file !== 'oc-content') || osc_update_occontent())) {  // this will block sigma theme update
              $data = osc_copy($tmp_path . $_file, ABS_PATH . $_file);
              
              if($data == false) {
                $fail = 1;
              }
            }
          }
          
          closedir($handle);
          
          // Remove zip package
          @unlink(osc_content_path() . 'downloads/' . $filename);

          // Replace files from temp folder to osclass live files was successful
          if($fail == 0) {
            
            // Upgrade database
            $error_queries = array();
            
            // Run upgrade DB from struct.sql (usually disabled)
            if(UPGRADE_SKIP_DB === false && file_exists(osc_lib_path() . 'osclass/installer/struct.sql')) {
              $sql = file_get_contents(osc_lib_path() . 'osclass/installer/struct.sql');

              $conn = DBConnectionClass::newInstance();
              $c_db = $conn->getOsclassDb();
              $comm = new DBCommandClass($c_db);
              $error_queries = $comm->updateDB(str_replace('/*TABLE_PREFIX*/', DB_TABLE_PREFIX, $sql));
              
            } else {
              $error_queries[0] = true;
            }
            
            
            if(UPGRADE_SKIP_DB === true || $error_queries[0]) {
              // Run upgrade func
              osc_set_preference('update_core_json', '');
              
              if(file_exists(osc_lib_path() . 'osclass/upgrade-funcs.php')) {
                require_once osc_lib_path() . 'osclass/upgrade-funcs.php';
              }
              
              // Additional actions is not important for the rest of the proccess
              // We will inform the user of the problems but the upgrade could continue

              // Remove temp files
              $rm_errors = 0;
              $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tmp_path), RecursiveIteratorIterator::CHILD_FIRST);

              for($dir->rewind(); $dir->valid(); $dir->next()) {
                if($dir->isDir()) {
                  if($dir->getFilename() !== '.' && $dir->getFilename() !== '..' && !rmdir($dir->getPathname())) {
                    $rm_errors++;
                  }
                } elseif(!unlink($dir->getPathname())) {
                  $rm_errors++;
                }
              }
              
              if(!rmdir($tmp_path)) {
                $rm_errors++;
              }
              
              $deleted = @unlink(ABS_PATH . '.maintenance');
              
              if($rm_errors == 0) {
                $message = __('Everything looks good! Your Osclass installation is up-to-date');
                osc_add_flash_ok_message($message, 'admin');
                
              } else {
                $message = sprintf(__('Nearly everything looks good! Your Osclass installation is up-to-date, but there were some errors removing temporary files. Please manually remove the "%s/downloads/oc-temp" folder'), OC_CONTENT_FOLDER);
                osc_add_flash_warning_message($message, 'admin');
                $error = 6; // Some errors removing files
              }
              
            } else {
              $sql_error_msg = $error_queries[2];
              $message = __('Problems when upgrading the database');
              $error = 5; // Problems upgrading the database
            }
            
          } else {
            $message = __('Problems when copying files. Please check your permissions. ');
            $error = 4; // Problems copying files. Maybe permissions are not correct
          }
          
        } else {
          $message = __('Nothing to copy');
          $error = 99; // Nothing to copy. THIS SHOULD NEVER HAPPEN, means we don't update any file!
          $deleted = @unlink(ABS_PATH . '.maintenance');
        }
        
      } else {
        $message = __('Unzip failed');
        $error = 3; // Unzip failed
        $deleted = @unlink(ABS_PATH . '.maintenance');
      }
      
    } else {
      $message = __('Download failed');
      $error = 2; // Download failed
      unlink(ABS_PATH . '.maintenance');
    }
    
  } else {
    $message = __('Missing download URL');
    $error = 1; // Missing download URL
    $deleted = @unlink(ABS_PATH . '.maintenance');
  }

  if($error == 5) {
    $message .= '<br /><br />' . __('We had some errors upgrading your database. The following queries failed:') . implode('<br />', $sql_error_msg);
  }

  foreach ($perms as $k => $v) {
    @chmod($k, $v);
  }
  
  osc_run_hook('after_upgrade', $error);

  return array(
    'error' => $error, 
    'message' => $message, 
    'version' => @$data['s_name']
  );
}


// AUTO-UPGRADE PROCESS
// Usually executed via cron, covers core, themes, plugins, languages
function osc_do_auto_upgrade() {
  $data = osc_file_get_contents(osc_osclass_url());
  //$data = preg_replace('|^\?\((.*?)\);$|', '$01', $data);
  $json = json_decode($data, true);
  $result = array();
  $result['error'] = 0;
  $old_version = osc_version();
  $themes_updated = 0;
  $plugins_updated = 0;
  $languages_updated = 0;
  $core_updated = 0;

  if(isset($json['version'])) {
    if($json['version'] > osc_version()) {
      osc_set_preference('update_core_json', $data);

      if(osc_check_dir_writable()) {
        if(strpos($json['version'], substr(osc_version(), 0, 1)) !== 0) {
          // NEW BRANCH
          if(strpos(osc_auto_update(), 'branch') !== false) {
            osc_run_hook('before_auto_upgrade');
            $result = osc_do_upgrade();
            $core_updated = 1;
            osc_run_hook('after_auto_upgrade', $result);
            osc_set_preference('update_core_json', '');
          }
          
        } elseif(substr($json['version'], 1, 1) !== substr(osc_version(), 1, 1)) {
          // MAJOR RELEASE
          if(strpos(osc_auto_update(), 'branch') !== false || strpos(osc_auto_update(), 'major') !== false) {
            osc_run_hook('before_auto_upgrade');
            $result = osc_do_upgrade();
            $core_updated = 1;
            osc_run_hook('after_auto_upgrade', $result);
            osc_set_preference('update_core_json', '');
          }
          
        } elseif(substr($json['version'], 2, 1) !== substr(osc_version(), 2, 1)) {
          // MINOR RELEASE
          if(strpos(osc_auto_update(), 'branch') !== false || strpos(osc_auto_update(), 'major') !== false || strpos(osc_auto_update(), 'minor') !== false) {
            osc_run_hook('before_auto_upgrade');
            $result = osc_do_upgrade();
            $core_updated = 1;
            osc_run_hook('after_auto_upgrade', $result);
            osc_set_preference('update_core_json', '');
          }
        }
      }
      
    } else {
      osc_set_preference('update_core_json', '');
    }
    
    osc_set_preference('last_version_check', time());
    
  } else {
    osc_set_preference('update_core_json', '');
    osc_set_preference('last_version_check', time() - 23 * 3600);
  }

  // Core has been updated correctly or there was no core update at all
  if($result['error'] == 0 || $result['error'] == 6) {
    if(strpos(osc_auto_update(), 'plugins') !== false) { 
      $total = osc_check_plugins_update(true);
      
      if($total > 0) {
        $elements = osc_get_preference('plugins_to_update');
        
        foreach ($elements as $element) {   // element is product key
          if(osc_is_update_compatible('check_version', $element, $json['version_string'])) {
            osc_market('plugins', $element);
            $plugins_updated = $total;
          }
        }
      }
    }

    if(strpos(osc_auto_update(), 'themes') !== false) { 
      $total = osc_check_themes_update(true);
      
      if($total > 0) {
        $elements = osc_get_preference('themes_to_update');
        
        foreach ($elements as $element) {   // element is product key
          if(osc_is_update_compatible('check_version', $element, $json['version_string'])) {
            osc_market('themes', $element);
            $themes_updated = $total;
          }
        }
      }
    }

    if(strpos(osc_auto_update(), 'languages') !== false) { 
      $total = osc_check_languages_update(true);
      
      if($total > 0) {
        $elements = osc_get_preference('languages_to_update');
        
        foreach ($elements as $element) {    // element is language code
          $lang_row = OSCLocale::newInstance()->findByCode($element);
          if(!isset($lang_row['s_version']) || osc_is_update_compatible('languages', $element, @$lang_row['s_version'])) {
            osc_market('languages', $element);
            $languages_updated = $total;
          }
        }
      }
    }


    // Notify site admin
    if($core_updated > 0 || $plugins_updated > 0 || $themes_updated > 0 || $languages_updated > 0) {
      $title = __('[{WEB_TITLE}] has been auto-upgraded');

      $body = '<p>' . __('Hi Admin!') . '</p>';
      $body .= '<p>' . __('Let us inform you, that your osclass website {WEB_TITLE} on {WEB_URL} has been auto-upgraded. List of upgraded items is bellow.') . '</p>';

      if($core_updated > 0) {
        $body .= '<p>' . __('Osclass core has been updated') . '</p>';
        $body .= '<p>' . __('Old version') . ': <strong>{OLD_VERSION}</strong></p>';
        $body .= '<p>' . __('Current version') . ': <strong>{NEW_VERSION}</strong></p>';
      }

      if($plugins_updated > 0) {
        $body .= '<p>' . sprintf(__('%s plugin(s) has been updated'), '<strong>' . $plugins_updated . '</strong>') . '</p>';
      }

      if($themes_updated > 0) {
        $body .= '<p>' . sprintf(__('%s theme(s) has been updated'), '<strong>' . $themes_updated . '</strong>') . '</p>';
      }

      if($languages_updated > 0) {
        $body .= '<p>' . sprintf(__('%s plugin(s) has been updated'), '<strong>' . $languages_updated . '</strong>') . '</p>';
      }

      $body .= '<p><br/></p>';
      $body .= '<p>' . __('This is an automatic email, if you already did that, please ignore this email.') . '</p>';
      $body .= '<p>' . __('Thank you') . ', <br />{WEB_TITLE}</p>';

      $words = array();
      $words[] = array('{WEB_TITLE}', '{WEB_URL}', '{OLD_VERSION}', '{NEW_VERSION}');
      $words[] = array(osc_page_title(), osc_base_url(), $old_version, $json['version']);

      $title = osc_mailBeauty($title, $words);
      $body = osc_mailBeauty($body, $words);
    
      $emailParams = array(
        'subject' => $title,
        'to' => osc_contact_email(),
        'to_name' => __('Admin'),
        'body' => $body,
        'alt_body' => $body
      );

      osc_sendMail($emailParams);
    }
  }
}


/**
 * @param    $section
 * @param    $element
 * @param string $osclass_version
 *
 * @return bool
 */
function osc_is_update_compatible($section, $product_key, $osclass_version = OSCLASS_VERSION){
  if($product_key != '') {
    $data = array();

    if($section == 'languages') {
      $url = osc_language_url($product_key);   // lang code
      $data = osc_file_get_contents_json($url);

      if(isset($data['s_version'])) {
        $result = version_compare2($osclass_version, $data['s_version']);

        if($result == -1) {
          // market have a newer version of this language
          $result = version_compare2($data['s_version'], $osclass_version);
          
          if($result == 0 || $result == -1) {    // A <= B
            // market version is compatible with current osclass version
            return true;
          }
        }
      }
    } else {
      $url = osc_market_url('check_version' , $product_key);
      $data = osc_file_get_contents_json($url);

      if(isset($data['s_osc_version_from']) && $data['s_osc_version_from'] <> '') {
        $result = version_compare2($osclass_version, $data['s_osc_version_from']);

        if($result == 0 || $result == 1) {   // A >= B
          if(isset($data['s_osc_version_to']) && $data['s_osc_version_to'] <> '') {
            $result = version_compare2($data['s_osc_version_to'], $osclass_version);

            if($result == 0 || $result == 1) {   // A >= B
              return true;
            } else {
              return false;
            }
          }
          
          return true;
        } else {
          return false;
        }
      }

      if(isset($data['s_osc_version_to']) && $data['s_osc_version_to'] <> '') {
        $result = version_compare2($data['s_osc_version_to'], $osclass_version);

        if($result == 0 || $result == 1) {   // A >= B
          return true;
        } else {
          return false;
        }
      }

      return true;
    }
  }

  return false;
}


/**
 * @param $section
 * @param $code
 *
 * @return array
 */

function osc_market($type, $product_key, $install = 0) {
  $plugin = false;
  $re_enable = false;
  $message = "";
  $data = array();
  $error = 0;

  /************************
   *** CHECK VALID CODE ***
   ************************/
  if($product_key != '' && $type != '') {
    if($type == 'languages') {
      $url = osc_language_url($product_key);
    } else {
      $url = osc_market_url('product', $product_key);
    }

    $data = osc_file_get_contents_json($url);

    if($type == 'languages') {
      if(isset($data[$product_key])) {
        $data = $data[$product_key];
      }      
    }

    /***********************
     **** DOWNLOAD FILE ****
     ***********************/
    if(@$data['type'] <> '' && (isset($data['download']) || isset($data['url']))) {
      $data['type'] = strtolower($data['type']);

      if($data['type'] == 'theme') {
        if($data['theme_type'] == 'oc-admin') {
          $folder = osc_admin_base_url() . 'themes/';
        } else {
          $folder = osc_content_path() . 'themes/';
        }
      } else if($data['type'] == 'plugin') {
        $folder = osc_content_path() . 'plugins/';
        $plugin = osc_find_by_product_key($data['product_key'], 'plugin');
        
        if($plugin!=false) {
          if(Plugins::isEnabled($plugin)) {
            Plugins::runHook($plugin.'_disable');
            Plugins::deactivate($plugin);
            $re_enable = true;
          }
        }
      } else if($data['type'] == 'language') {
        $folder = osc_content_path() . 'languages/';
      }
      
      if($type == 'languages') {
        $filename = $data['full_name'];
        $url_source_file = $data['url'];
      } else {
        $filename = date('YmdHis')."_".osc_sanitize_string($data['name'])."_".preg_replace('/[.,]/', '', (isset($data['s_version']) ? $data['s_version'] : @$data['i_version'])).".zip";
        $url_source_file = osc_market_url('download', $product_key);
      }

      $check = osc_file_get_contents_json($url_source_file);

      // check if file can be downloaded or we got error in json format
      if(!isset($check['error'])) {
        $result = osc_downloadFile($url_source_file, $filename);

        if($result) { // Everything is OK, continue
          /**********************
           ***** UNZIP FILE *****
           **********************/
          @mkdir(osc_content_path() . 'downloads/oc-temp/');
          $res = osc_unzip_file(osc_content_path() . 'downloads/' . $filename, osc_content_path() . 'downloads/oc-temp/');

          if($res == 1) { // Everything is OK, continue
            /**********************
             ***** COPY FILES *****
             **********************/
            $fail = -1;
            if($handle = opendir(osc_content_path() . 'downloads/oc-temp')) {
              $folder_dest = $folder;

              if(function_exists('posix_getpwuid')) {
                $current_user = posix_getpwuid(posix_geteuid());
                $ownerFolder = posix_getpwuid(fileowner($folder_dest));
              }

              $fail = 0;
              while (false !== ($_file = readdir($handle))) {
                if($_file != '.' && $_file != '..') {
                  $copyprocess = osc_copy(osc_content_path() . "downloads/oc-temp/" . $_file, $folder_dest . $_file);
                  if($copyprocess == false) {
                    $fail = 1;
                  };
                }
              }
              closedir($handle);

              // Additional actions is not important for the rest of the proccess
              // We will inform the user of the problems but the upgrade could continue
              // Also remove the zip package
              /****************************
               ** REMOVE TEMPORARY FILES **
               ****************************/
              @unlink(osc_content_path() . 'downloads/' . $filename);
              $path = osc_content_path() . 'downloads/oc-temp';
              $rm_errors = 0;
              $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::CHILD_FIRST);
              for ($dir->rewind(); $dir->valid(); $dir->next()) {
                if($dir->isDir()) {
                  if($dir->getFilename() != '.' && $dir->getFilename() != '..') {
                    if(!rmdir($dir->getPathname())) {
                      $rm_errors++;
                    }
                  }
                } else {
                  if(!unlink($dir->getPathname())) {
                    $rm_errors++;
                  }
                }
              }

              if(!rmdir($path)) {
                $rm_errors++;
              }

              if($fail == 0) { // Everything is OK, continue
                if($data['type'] == 'plugin') {
                  if($plugin!=false && $re_enable) {
                    $enabled = Plugins::activate($plugin);
                    if($enabled) {
                      Plugins::runHook($plugin.'_enable');
                    }
                  }

                  if($plugin!=false && $install) {
                    $installed = Plugins::install($plugin);
                  }
                } else if($data['type'] == 'language') {
                  osc_checkLocales();
                }

                // recount plugins&themes for update
                if($type == 'plugins') {
                  osc_check_plugins_update(true);
                } else if($type == 'themes') {
                  osc_check_themes_update(true);
                } else if($type == 'languages') {
                  osc_check_languages_update(true);
                }
                if($rm_errors == 0) {
                  $message = __('Everything looks good!');
                  $error = 0;
                } else {
                  $message = sprintf(__('Nearly everything looks good! but there were some errors removing temporary files. Please manually remove the \"oc-content/downloads/oc-temp\" folder'), OC_CONTENT_FOLDER);
                  $error = 6; // Some errors removing files
                }
              } else {
                $message = __('Problems when copying files. Please check your permissions. ');

                if($current_user['uid'] != $ownerFolder['uid']) {
                  if(function_exists('posix_getgrgid')) {
                    $current_group = posix_getgrgid($current_user['gid']);
                    $message .= '<p><strong>' . sprintf(__('NOTE: Web user and destination folder user is not the same, you might have an issue there. <br/>Do this in your console:<br/>chown -R %s:%s %s'), $current_user['name'], $current_group['name'], $folder_dest).'</strong></p>';
                  }
                }
                $error = 4;  // Problems copying files. Maybe permissions are not correct
              }
            } else {
              $message = __('Nothing to copy'); // Should never happen
              $error = 99; 
            }
          } else {
            $message = __('Unzip failed');
            $error = 3; 
          }
        } else {
          $message = __('Download failed');
          $error = 2; 
        }
      } else {
        $message = $check['error'];
        $error = 29; 
      }
    } else {
      if(isset($data['s_buy_url']) && isset($data['b_paid']) && $data['s_buy_url']!='' && $data['b_paid']==0) {
        $message = __('This is a paid item, you need to buy it before you are able to download it');
        $error = 8; 
      } else {
        $message = __('Login has failed');
        $error = 7; 
      }
    }
  } else {
    $message = __('Missing product key');
    $error = 1;
  }

  return array('error' => $error, 'message' => $message, 'data' => $data);
}


function osc_find_by_product_key($product_key, $type = 'plugin') {
  if($type == 'plugin') {
    $plugins = Plugins::listAll();

    foreach($plugins as $p) {
      $info = osc_plugin_get_info($p);

      if($info && $info['product_key'] == $product_key && $product_key <> '') {
        return $p;
      }
    }
  }

  if($type == 'theme') {
    $themes = WebThemes::newInstance()->getListThemes();
    foreach($themes as $theme) {
      $info = WebThemes::newInstance()->loadThemeInfo($theme);

      if($info && $info['product_key'] == $product_key && $product_key <> '') {
        return $theme;
      }
    }
  }

  return false;
}


// GET LIST OF SQL COMMANDS FOR UTF8MB4 CONVERSION
// Set $dryRun to false to execute SQLs
// Only list tables those charset/collate was not converted yet
function osc_utf8mb4_sqls($dryRun = true) {
  $conn = DBConnectionClass::newInstance();
  $c_db = $conn->getOsclassDb();
  $comm = new DBCommandClass($c_db);
  $comm->migrateToUtf8mb4($dryRun);
}



/**
 * @return bool
 */
function osc_is_ssl() {
  return (
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
    || (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == 1))
  );
}


if(!function_exists('hex2b64')) {
  /*
 * Used to encode a field for Amazon Auth
 * (taken from the Amazon S3 PHP example library)
 */
  /**
   * @param $str
   *
   * @return string
   */
  function hex2b64($str){
    $raw = '';
    for ($i = 0, $iMax = strlen($str); $i < $iMax; $i += 2) {
      $raw .= chr(hexdec(substr($str, $i, 2)));
    }

    return base64_encode($raw);
  }
}

if(!function_exists('hmacsha1')) {
  /*
 * Calculate HMAC-SHA1 according to RFC2104
 * See http://www.faqs.org/rfcs/rfc2104.html
 */
  /**
   * @param $key
   * @param $data
   *
   * @return string
   */
  function hmacsha1($key, $data) {
    $blocksize = 64;
    $hashfunc = 'sha1';
    
    if(strlen($key) > $blocksize) {
      $key = pack('H*', $hashfunc($key));
    }
    
    $key = str_pad($key, $blocksize, chr(0x00));
    $ipad = str_repeat(chr(0x36), $blocksize);
    $opad = str_repeat(chr(0x5c), $blocksize);
    $hmac = pack(
      'H*',
      $hashfunc(
        ($key ^ $opad) . pack(
          'H*',
          $hashfunc(
            ($key ^ $ipad) . $data
         )
       )
     )
    );

    return bin2hex($hmac);
  }
}