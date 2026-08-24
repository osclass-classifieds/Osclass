<?php
/*
 * Copyright 2020 OsclassPoint.com
 *
 * Osclass maintained & developed by OsclassPoint.com
 * you may not use this file except in compliance with the License.
 * You may download copy of Osclass at
 *
 *   https://osclass-classifieds.com/download
 *
 * Software is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
*/


/**
* Helper Flash Messages
* @package Osclass
* @subpackage Helpers
* @author Osclass
*/

/**
 * Adds an ephemeral message to the session. (error style)
 *
 * @param $msg
 * @param $section
 * @return string
 */
function osc_add_flash_message($msg, $section = 'pubMessages') {
  $msg = osc_apply_filter('osc_add_flash_message_value', $msg, $section, 'error');
  
  if($msg !== false && $msg !== '') {
    Session::newInstance()->_setMessage($section, $msg, 'error');
  }
}

/**
 * Adds an ephemeral message to the session. (ok style)
 *
 * @param $msg
 * @param $section
 * @return string
 */
function osc_add_flash_ok_message($msg, $section = 'pubMessages') {
  $msg = osc_apply_filter('osc_add_flash_message_value', $msg, $section, 'ok');
  
  if($msg !== false && $msg !== '') {
    Session::newInstance()->_setMessage($section, $msg, 'ok');
  }
}

/**
 * Adds an ephemeral message to the session. (error style)
 *
 * @param $msg
 * @param $section
 * @return string
 */
function osc_add_flash_error_message($msg, $section = 'pubMessages') {
  $msg = osc_apply_filter('osc_add_flash_message_value', $msg, $section, 'error');
  
  if($msg !== false && $msg !== '') {
    Session::newInstance()->_setMessage($section, $msg, 'error');
  }
}

/**
 * Adds an ephemeral message to the session. (info style)
 *
 * @param $msg
 * @param $section
 * @return string
 */
function osc_add_flash_info_message($msg, $section = 'pubMessages') {
  $msg = osc_apply_filter('osc_add_flash_message_value', $msg, $section, 'info');
  
  if($msg !== false && $msg !== '') {
    Session::newInstance()->_setMessage($section, $msg, 'info');
  }
}

/**
 * Adds an ephemeral message to the session. (warning style)
 *
 * @param $msg
 * @param $section
 * @return string
 */
function osc_add_flash_warning_message($msg, $section = 'pubMessages') {
  $msg = osc_apply_filter('osc_add_flash_message_value', $msg, $section, 'warning');
  
  if($msg !== false && $msg !== '') {
    Session::newInstance()->_setMessage($section, $msg, 'warning');
  }
}

/**
 * Shows all the pending flash messages in session and cleans up the array.
 *
 * @param $section
 * @param $class
 * @param $id
 * @return void
 */
function osc_show_flash_message($section = 'pubMessages', $class = 'flashmessage', $id = 'flashmessage', $close_btn = NULL) {
  $messages = Session::newInstance()->_getMessage($section);
  $messages = osc_apply_filter('osc_show_flash_message', $messages, $class, $id);
  
  if($close_btn === NULL) {
    $close_btn = '<a class="btn ico btn-mini ico-close">x</a>';
  }

  if(is_array($messages) && count($messages) > 0) {
    foreach($messages as $message) {
      echo '<div id="flash_js"></div>';
  
      if(isset($message['msg']) && $message['msg'] != '') {
        $text = osc_apply_filter('flash_message_text', $message['msg'], $section);
        
        if($text !== false && trim((string)$text) != '') {
          echo '<div id="' . $id . '" class="' . strtolower($class) . ' ' . strtolower($class) . '-' .$message['type'] . '">' . $close_btn . $text . '</div>';
        }
        
      } else if(!is_array($message) && $message != '') {
        $text = osc_apply_filter('flash_message_text', $message, $section);
        
        if($text !== false && trim((string)$text) != '') {
          echo '<div id="' . $id . '" class="' . $class . '">' . $text . '</div>';
        }
        
      } else {
        // Empty message
        $text = osc_apply_filter('flash_message_text', '', $section);
        
        //if($text !== false && trim((string)$text) != '') {
          echo '<div id="' . $id . '" class="' . $class . '" style="display:none;">' . $text . '</div>';
        //}
      }
    }
  }
  
  Session::newInstance()->_dropMessage($section);
}


/**
 *
 *
 * @param string $section
 * @param bool   $dropMessages
 *
 * @return string Message
 */
function osc_get_flash_message($section = 'pubMessages', $dropMessages = true) {
  $message = Session::newInstance()->_getMessage($section);
  
  if($dropMessages) {
    Session::newInstance()->_dropMessage($section);
  }

  return $message;
}