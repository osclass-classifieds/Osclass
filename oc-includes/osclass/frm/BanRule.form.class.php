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
* Class BanRuleForm
*/
class BanRuleForm extends Form {
  /**
  * @param $rule
  */
  public static function primary_input_hidden( $rule ) {
    parent::generic_input_hidden('id', (isset($rule[ 'pk_i_id' ]) ? $rule['pk_i_id'] : ''));
  }

  /**
  * @param null $rule
  */
  public static function name_text( $rule = null ) {
    parent::generic_input_text('s_name', isset($rule['s_name']) ? $rule['s_name'] : '');
  }

  /**
  * @param null $rule
  */
  public static function ip_text( $rule = null ) {
    parent::generic_input_text('s_ip', isset($rule['s_ip']) ? $rule['s_ip'] : '');
  }

  /**
  * @param null $rule
  */
  public static function email_text( $rule = null ) {
    parent::generic_input_text('s_email', isset($rule['s_email']) ? $rule['s_email'] : '');
  }
  
  /**
  * @param null $rule
  */
  public static function expire_date_text( $rule = null ) {
    parent::generic_input_text('dt_expire_date', isset($rule['dt_expire_date']) ? $rule['dt_expire_date'] : '', null, false, true, -1, 'date');
  }
}