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
 
define('ABS_PATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/');
require_once ABS_PATH . 'oc-load.php';

if(!osc_is_admin_user_logged_in()) {
  header("HTTP/1.1 401 Unauthorized");
  exit;
}


$accepted_origins = array(
  osc_base_url(),
  osc_base_url(false, true),
  osc_subdomain_top_url(),
  osc_subdomain_base_url(),
  'http://localhost/',
  'https://192.168.1.1/',
  'https://localhost/',
  'https://192.168.1.1/'
);

$accepted_origins = osc_apply_filter('tinymce_accepted_origins', $accepted_origins);
$allowed_extensions = osc_apply_filter('tinymce_allowed_extensions', array("gif", "jpg", "jpeg", "png", "webp", "avif"));

$type = Params::getParam('dataType');

if($type == '' || $type == 'page') {
  $image_folder_path = osc_uploads_path() . 'page-images/';
} else if ($type == 'item') {
  $image_folder_path = osc_uploads_path() . 'item-images/';
} else if ($type == 'widget') {
  $image_folder_path = osc_uploads_path() . 'widget-images/';
} else if ($type == 'custom') {
  $image_folder_path = osc_uploads_path() . 'custom-images/';
}


// Check if target folder exists. If not, try to create it
if(!file_exists($image_folder_path) || !is_dir($image_folder_path)) {
  @mkdir($image_folder_path);
}


$image_folder_path = osc_apply_filter('tinymce_image_folder_path', $image_folder_path, $type);
$image_folder_url = str_replace(osc_base_path(), osc_base_url(), $image_folder_path);
$image_folder_url = osc_apply_filter('tinymce_image_folder_url', $image_folder_url, $type);

reset($_FILES);
$temp = current($_FILES);


if(is_uploaded_file($temp['tmp_name'])){

  // same-origin requests won't set an origin. If the origin is set, it must be valid.
  if(isset($_SERVER['HTTP_ORIGIN'])) {
    if(in_array($_SERVER['HTTP_ORIGIN'] . '/', $accepted_origins)) {
      header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
      
    } else {
      header("HTTP/1.1 403 Origin Denied");
      exit;
    }
  }


  // Sanitize input
  if(preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $temp['name'])) {
    header("HTTP/1.1 400 Invalid file name.");
    exit;
  }


  // Verify extension
  if(!in_array(strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION)), $allowed_extensions)) {
    header("HTTP/1.1 400 Invalid extension.");
    exit;
  }


  // Accept upload if there was no origin, or if it is an accepted origin
  $file_name = date('YmdHis') . '_' . $temp['name'];
  $file_name = osc_apply_filter('tinymce_file_name', $file_name);

  $filetowrite = $image_folder_path . $file_name;
  
  move_uploaded_file($temp['tmp_name'], $filetowrite);

  // Respond to the successful upload with JSON.
  // Use a location key to specify the path to the saved image resource.
  echo json_encode(array('location' => $image_folder_url . $file_name));
  exit;
  
} else {
  // Notify editor that the upload failed
  header("HTTP/1.1 500 Server Error");
  exit;
}
?>