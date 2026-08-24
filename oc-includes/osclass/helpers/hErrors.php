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


/**
* Helper Error
* @package Osclass
* @subpackage Helpers
* @author Osclass
*/

/**
 * Kill Osclass with an error message
 *
 * @since 1.2
 *
 * @param string $message Error message
 * @param string $title Error title
 */
function osc_die($title, $message) {
  ?>
  <!DOCTYPE html>
  <html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US" xml:lang="en-US">
    <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0" />
      <title><?php echo $title; ?></title>
      <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400&family=Nunito:wght@300;600&display=swap" rel="stylesheet">
      <link rel="stylesheet" type="text/css" media="all" href="<?php echo osc_get_absolute_url() . OC_INCLUDES_FOLDER; ?>/osclass/installer/install.css?v=<?php echo date('YmdHis'); ?>" />
    </head>
    <body class="page-error">
      <h1><?php echo $title; ?></h1>
      <p><?php echo $message; ?></p>
    </body>
  </html>
  <?php die(); ?>
<?php }

/**
 * @param    $param
 * @param bool $htmlencode
 * @param bool $quotes_encode
 *
 * @return string
 */
function getErrorParam( $param , $htmlencode = false , $quotes_encode = true )
{
  if ( $param == '' ) {
    return '';
  }
  if ( ! isset( $_SERVER[ $param ] ) ) {
    return '';
  }
  $value = $_SERVER[$param];
  if ($htmlencode) {
    if($quotes_encode) {
      return htmlspecialchars(stripslashes($value), ENT_QUOTES);
    } else {
      return htmlspecialchars(stripslashes($value), ENT_NOQUOTES);
    }
  }

  // 422 update
  // if(get_magic_quotes_gpc()) {
    // $value = strip_slashes_extended_e($value);
  // }

  return $value;
}


/**
 * @param $array
 *
 * @return string
 */
function strip_slashes_extended_e( $array ) {
  if(is_array($array)) {
    foreach($array as $k => &$v) {
      $v = strip_slashes_extended_e($v);
    }
  } else {
    $array = stripslashes($array);
  }
  return $array;
}


/**
 * @return string
 */
function osc_get_absolute_url() {
  $protocol = ( getErrorParam('HTTPS') === 'on' || getErrorParam( 'HTTPS') == 1 || getErrorParam( 'HTTP_X_FORWARDED_PROTO') === 'https')? 'https' : 'http';
  $replace = '(oc-admin)|(oc-includes)|(oc-content)';
  
  if(defined('OC_ADMIN_FOLDER') && OC_ADMIN_FOLDER != 'oc-admin') {
    $replace .= '|(' . OC_ADMIN_FOLDER . ')';
  }
  
  if(defined('OC_INCLUDES_FOLDER') && OC_INCLUDES_FOLDER != 'oc-includes') {
    $replace .= '|(' . OC_INCLUDES_FOLDER . ')';
  }
  
  if(defined('OC_CONTENT_FOLDER') && OC_CONTENT_FOLDER != 'oc-content') {
    $replace .= '|(' . OC_CONTENT_FOLDER . ')';
  }
  
  return $protocol . '://' . getErrorParam('HTTP_HOST') . preg_replace('/(' . $replace . '|([a-z]+\.php)|(\?.*)).*/i', '', getErrorParam('REQUEST_URI', false, false));
}