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
 *
 */
class LogDatabase
{
  /**
   *
   * @var
   */
  private static $instance;
  /**
   *
   * @var
   */
  public $messages;
  /**
   *
   * @var
   */
  public $explain_messages;

  /**
   *
   * @return \LogDatabase
   */
  public static function newInstance()
  {
    if (!self::$instance instanceof self) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  /**
   *
   */
  public function __construct()
  {
    $this->messages     = array();
    $this->explain_messages = array();
  }

  /**
   *
   * @param $sql
   * @param $time
   * @param $errorLevel
   * @param $errorDescription
   */
  public function addMessage($sql, $time, $errorLevel, $errorDescription)
  {
    $this->messages[] = array(
      'query'    => $sql,
      'query_time' => $time,
      'errno'    => $errorLevel,
      'error'    => $errorDescription
    );
  }

  /**
   *
   * @param $sql
   * @param    $results
   */
  public function addExplainMessage($sql, $results)
  {
    $this->explain_messages[] = array(
      'query'   => $sql,
      'explain' => $results
    );
  }

  /**
   *
   */
  public function printMessages()
  {
    echo '<fieldset id="osc-database-logs" style="border:1px solid #000;line-height:1.4;padding:8px 10px 10px 10px;margin: 12px;width:calc(100% - 24px);background-color:#fff;">' . PHP_EOL;
    echo '<legend style="font-size:14px;font-weight:600;padding:4px 8px;border:1px solid #000;background:#fff;">Database queries (Total queries: ' . $this->getTotalNumberQueries() .' - Total queries time: ' . $this->getTotalQueriesTime() . ' sec)</legend>' . PHP_EOL;
    echo '<table style="border-collapse: collapse;width:100%;font-size:13px;padding:0;border-spacing:0;font-family:monospace;line-height:1.4;">' . PHP_EOL;
    if (count($this->messages) == 0) {
      echo '<tr><td>No queries</td></tr>' . PHP_EOL;
    } else {
      foreach ($this->messages as $msg) {
        $row_style = '';
        if ($msg['errno'] != 0) {
          $row_style = 'style="background-color: #FFC2C2;"';
        }
        echo '<tr ' . $row_style . '>' . PHP_EOL;
        echo '<td style="padding:6px 8px;text-align:left;vertical-align:top;border: 1px solid #ccc;min-width:75px;">' . $msg['query_time'] . '</td>' . PHP_EOL;
        echo '<td style="padding:6px 8px;text-align:left;vertical-align:top;border: 1px solid #ccc;">';
        if ($msg['errno'] != 0) {
          echo '<strong>Error number:</strong> ' . $msg['errno'] . '<br/>';
          echo '<strong>Error description:</strong> ' . $msg['error'] . '<br/><br/>';
        }
        echo nl2br($msg['query']);
        echo '</td>' . PHP_EOL;
        echo '</tr>' . PHP_EOL;
      }
    }
    echo '</table>' . PHP_EOL;
    echo '</fieldset>' . PHP_EOL;
  }

  /**
   * @return bool
   */
  public function writeMessages()
  {
    $filename = CONTENT_PATH . 'queries.log';

    if ((!file_exists($filename) && !is_writable(CONTENT_PATH)) || (file_exists($filename) && !is_writable($filename))) {
      error_log('Can not write explain_queries.log file in "'.CONTENT_PATH.'", please check directory/file permissions.');
      return false;
    }

    $fp = fopen($filename, 'ab');

    if ($fp == false) {
      return false;
    }

    fwrite($fp, '==================================================' . PHP_EOL);
    fwrite($fp, '=' . str_pad('Date: ' . date(osc_date_format()!=''?osc_date_format():'Y-m-d').' '.date(osc_time_format()!=''?osc_date_format():'H:i:s'), 48, ' ', STR_PAD_BOTH) . '=' . PHP_EOL);
    fwrite($fp, '=' . str_pad('Total queries: ' . $this->getTotalNumberQueries(), 48, ' ', STR_PAD_BOTH) . '=' . PHP_EOL);
    fwrite($fp, '=' . str_pad('Total queries time: ' . $this->getTotalQueriesTime(), 48, ' ', STR_PAD_BOTH) . '=' . PHP_EOL);
    fwrite($fp, '==================================================' . PHP_EOL . PHP_EOL);

    foreach ($this->messages as $msg) {
      fwrite($fp, 'QUERY TIME' . ' ' . $msg['query_time'] . PHP_EOL);
      if ($msg['errno'] != 0) {
        fwrite($fp, 'Error number: ' . $msg['errno'] . PHP_EOL);
        fwrite($fp, 'Error description: ' . $msg['error'] . PHP_EOL);
      }
      fwrite($fp, '**************************************************' . PHP_EOL);
      fwrite($fp, $msg['query'] . PHP_EOL);
      fwrite($fp, '--------------------------------------------------' . PHP_EOL);
    }

    fwrite($fp, PHP_EOL . PHP_EOL. PHP_EOL);
    fclose($fp);
    return true;
  }

  /**
   * @return bool
   */
  public function writeExplainMessages()
  {
    $filename = CONTENT_PATH . 'explain_queries.log';

    if ((!file_exists($filename) && !is_writable(CONTENT_PATH)) || (file_exists($filename) && !is_writable($filename))) {
      error_log('Can not write explain_queries.log file in "'.CONTENT_PATH.'", please check directory/file permissions.');
      return false;
    }

    $fp = fopen($filename, 'ab');

    if ($fp == false) {
      return false;
    }

    fwrite($fp, '==================================================' . PHP_EOL);
    fwrite($fp, '=' . str_pad('Date: ' . date(osc_date_format() ?: 'Y-m-d') . ' ' . date(osc_time_format() ?: 'H:i:s'), 48, ' ', STR_PAD_BOTH) . '=' . PHP_EOL);
    fwrite($fp, '==================================================' . PHP_EOL . PHP_EOL);

    $title  = '|' . str_pad('id', 3, ' ', STR_PAD_BOTH) . '|';
    $title .= str_pad('select_type', 20, ' ', STR_PAD_BOTH) . '|';
    $title .= str_pad('table', 20, ' ', STR_PAD_BOTH) . '|';
    $title .= str_pad('type', 8, ' ', STR_PAD_BOTH) . '|';
    $title .= str_pad('possible_keys', 28, ' ', STR_PAD_BOTH) . '|';
    $title .= str_pad('key', 18, ' ', STR_PAD_BOTH) . '|';
    $title .= str_pad('key_len', 9, ' ', STR_PAD_BOTH) . '|';
    $title .= str_pad('ref', 48, ' ', STR_PAD_BOTH) . '|';
    $title .= str_pad('rows', 8, ' ', STR_PAD_BOTH) . '|';
    $title .= str_pad('Extra', 38, ' ', STR_PAD_BOTH) . '|';

    for ($i = 0 , $iMax = count($this->explain_messages); $i < $iMax; $i ++) {
      fwrite($fp, $this->explain_messages[$i]['query'] . PHP_EOL);
      fwrite($fp, str_pad('', 211, '-', STR_PAD_BOTH) . PHP_EOL);
      fwrite($fp, $title . PHP_EOL);
      fwrite($fp, str_pad('', 211, '-', STR_PAD_BOTH) . PHP_EOL);
      foreach ($this->explain_messages[$i]['explain'] as $explain) {
        $row  = '|' . str_pad((string)$explain['id'], 3, ' ', STR_PAD_BOTH) . '|';
        $row .= str_pad((string)$explain['select_type'], 20, ' ', STR_PAD_BOTH) . '|';
        $row .= str_pad((string)$explain['table'], 20, ' ', STR_PAD_BOTH) . '|';
        $row .= str_pad((string)$explain['type'], 8, ' ', STR_PAD_BOTH) . '|';
        $row .= str_pad((string)$explain['possible_keys'], 28, ' ', STR_PAD_BOTH) . '|';
        $row .= str_pad((string)$explain['key'], 18, ' ', STR_PAD_BOTH) . '|';
        $row .= str_pad((string)$explain['key_len'], 9, ' ', STR_PAD_BOTH) . '|';
        $row .= str_pad((string)$explain['ref'], 48, ' ', STR_PAD_BOTH) . '|';
        $row .= str_pad((string)$explain['rows'], 8, ' ', STR_PAD_BOTH) . '|';
        $row .= str_pad((string)$explain['Extra'], 38, ' ', STR_PAD_BOTH) . '|';
        fwrite($fp, $row . PHP_EOL);
        fwrite($fp, str_pad('', 211, '-', STR_PAD_BOTH) . PHP_EOL);
      }
      if ($i != (count($this->explain_messages) - 1)) {
        fwrite($fp, PHP_EOL . PHP_EOL);
      }
    }

    fwrite($fp, PHP_EOL . PHP_EOL);
    fclose($fp);
    return true;
  }

  /**
   * @return int
   */
  public function getTotalQueriesTime()
  {
    $time = 0;
    foreach ($this->messages as $m) {
      $time += $m[ 'query_time' ];
    }

    return $time;
  }

  /**
   * @return int
   */
  public function getTotalNumberQueries()
  {
    return count($this->messages);
  }
}

/* file end: ./oc-includes/osclass/Logger/LogDatabase.php */