<?php
if(!defined('ABS_PATH')) exit('ABS_PATH is not loaded. Direct access is not allowed.');

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
 * Class Logger
 */
abstract class Logger
{

  /**
   * Log a message with the INFO level.
   *
   * @param string $message
   *
   * @param null   $caller
   *
   */
  abstract public function info($message = '', $caller = null );

  /**
   * Log a message with the WARN level.
   *
   * @param string $message
   *
   * @param null   $caller
   *
   */
  abstract public function warn($message = '', $caller = null );

  /**
   * Log a message with the ERROR level.
   *
   * @param string $message
   *
   * @param null   $caller
   */
  abstract public function error($message = '', $caller = null );

  /**
   * Log a message with the DEBUG level.
   * @param string $message
   * @param null   $caller
   */
  abstract public function debug($message = '', $caller = null );

  /**
   * Log a message object with the FATAL level including the caller.
   * @param string $message
   * @param null   $caller
   */
  abstract public function fatal($message = '', $caller = null );
}

/* file end: ./oc-includes/osclass/Logger/Logger.php */