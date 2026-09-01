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
 * Class PageForm
 */
class PageForm extends Form {
  /**
   * @param null $page
   */
  public static function primary_input_hidden($page = null) {
    if(isset($page['pk_i_id'])) {
      parent::generic_input_hidden('id' , $page['pk_i_id']);
    }
  }

  /**
   * @param null $page
   */
  public static function internal_name_input_text($page = null) {
    $internal_name = '';
    if(is_array($page) && isset($page['s_internal_name'])) {
      $internal_name = $page['s_internal_name'];
    }
    if(Session::newInstance()->_getForm('s_internal_name') != '') {
      $internal_name = Session::newInstance()->_getForm('s_internal_name');
    }
    
    parent::generic_input_text('s_internal_name' , $internal_name , null , (isset($page['b_indelible']) && $page['b_indelible'] == 1));
  }

  /**
   * @param null $page
   */
  public static function index_checkbox($page = null) {
    $checked = true;
    if(is_array($page) && isset($page['b_index']) && $page['b_index']==0) {
      $checked = false;
    }

    parent::generic_input_checkbox('b_index', '1' , $checked);
  }

  /**
   * @param null $page
   */
  public static function link_checkbox($page = null) {
    $checked = true;
    if(is_array($page) && isset($page['b_link']) && $page['b_link']==0) {
      $checked = false;
    }

    parent::generic_input_checkbox('b_link', '1' , $checked);
  }

  /**
   * @param null $page
  */
  public static function visibility_select($page = null) {
    $options = array();
    $data = osc_static_page_visibility_options();
    
    foreach($data as $id => $name) {
      $options[] = array('i_value' => $id, 's_text' => $name);
    }
    
    parent::generic_select('i_visibility', $options, 'i_value', 's_text', null, isset($page['i_visibility']) ? $page['i_visibility'] : 0);
  }
  
  /**
   * @param    $locales
   * @param null $page
   */
  public static function multilanguage_name_description($locales , $page = null) {
    $num_locales = count($locales);
    if ($num_locales > 1) {
      echo '<div class="tabber">';
    }
    
    $aFieldsDescription = Session::newInstance()->_getForm('aFieldsDescription');
    foreach($locales as $locale) {
      if($num_locales > 1) {
        echo '<div class="tabbertab">';
        echo '<h2>' . $locale['s_name'] . '</h2>';
      }
      
      echo '<div class="FormElement">';
      echo '<div class="FormElementName">' . __('Title') . '</div>';
      echo '<div class="FormElementInput">';
      
      $title = '';
      if(isset($page['locale'][$locale['pk_c_code']])) {
        $title = $page['locale'][$locale['pk_c_code']]['s_title'];
      }
      
      if(isset($aFieldsDescription[$locale['pk_c_code']]) && isset($aFieldsDescription[$locale['pk_c_code']]['s_title']) &&$aFieldsDescription[$locale['pk_c_code']]['s_title'] != '') {
        $title = $aFieldsDescription[$locale['pk_c_code']]['s_title'];
      }
      
      parent::generic_input_text($locale['pk_c_code'] . '#s_title', $title);
      
      echo '</div>';
      echo '</div>';
      echo '<div class="FormElement">';
      echo '<div class="FormElementName">' . __('Body') . '</div>';
      echo '<div class="FormElementInput">';
      
      $description = '';
      if(isset($page['locale'][$locale['pk_c_code']])) {
        $description = $page['locale'][$locale['pk_c_code']]['s_text'];
      }
      
      if(isset($aFieldsDescription[$locale['pk_c_code']]) && isset($aFieldsDescription[$locale['pk_c_code']]['s_text']) &&$aFieldsDescription[$locale['pk_c_code']]['s_text'] != '') {
        $description = $aFieldsDescription[$locale['pk_c_code']]['s_text'];
      }
      
      parent::generic_textarea($locale['pk_c_code'] . '#s_text', $description);
      
      echo '</div>';
      echo '</div>';
      if($num_locales > 1) {
        echo '</div>';
      }
    }
     
    if($num_locales > 1) {
      echo '</div>';
    }
  }
}

/* file end: ./oc-includes/osclass/frm/Page.form.class.php */