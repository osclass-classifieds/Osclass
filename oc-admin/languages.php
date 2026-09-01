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


class CAdminLanguages extends AdminSecBaseModel {
  //specific for this class
  private $localeManager;

  public function __construct() {
    parent::__construct();

    //specific things for this class
    $this->localeManager = OSCLocale::newInstance();
  }

  //Business Layer...
  public function doModel() {
    switch ($this->action) {
      case('sync'):   // Synchronize - recheck added languages (lang folder)
        osc_checkLocales(true);
        osc_add_flash_ok_message(_m('Languages synchronized'), 'admin');
        $this->redirectTo(osc_admin_base_url(true) . '?page=languages');
        break;
        
      case('add'):        // caliing add view
        $this->doView('languages/add.php');
        break;
        
      case('add_post'):       // adding a new language
        if(defined('DEMO')) {
          osc_add_flash_warning_message(_m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=languages');
        }
        
        osc_csrf_check();
        $filePackage = Params::getFiles('package');

        if(isset($filePackage['size']) && $filePackage['size'] !== 0) {
          $path = osc_translations_path();
          $status = osc_unzip_file($filePackage['tmp_name'], $path);
          @unlink($filePackage['tmp_name']);
        } else {
          $status = 3;
        }

        switch ($status) {
          case(0):
            $msg = _m('The translation folder is not writable');
            osc_add_flash_error_message($msg, 'admin');
            break;
            
          case(1):
            if(osc_checkLocales()) {
              $msg = _m('The language has been installed correctly');
              osc_add_flash_ok_message($msg, 'admin');
            } else {
              $msg = _m('File uploaded but unable to activate the language');
              osc_add_flash_error_message($msg, 'admin');
            }
            break;
            
          case(2):
            $msg = _m('The zip file is not valid');
            osc_add_flash_error_message($msg, 'admin');
            break;
            
          case(3):
            $msg = _m('No file was uploaded');
            osc_add_flash_warning_message($msg, 'admin');
            $this->redirectTo(osc_admin_base_url(true) . '?page=languages&action=add');
            break;
            
          case(-1):
          default:
            $msg = _m('There was a problem adding the language');
            osc_add_flash_error_message($msg, 'admin');
            break;
        }

        $this->redirectTo(osc_admin_base_url(true) . '?page=languages');
        break;
        
      case('edit'):         // editing a language
        $sLocale = Params::getParam('id');
        if(!preg_match('/.{2}_.{2}/', $sLocale)) {
          osc_add_flash_error_message(_m('Language id isn\'t in the correct format'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=languages');
        }

        $aLocale = $this->localeManager->findByPrimaryKey($sLocale);

        if(count($aLocale) == 0) {
          osc_add_flash_error_message(_m('Language id doesn\'t exist'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=languages');
        }

        $this->_exportVariableToView('aLocale', $aLocale);
        $this->doView('languages/frm.php');
        break;
        
      case('edit_post'):      // edit language post
        osc_csrf_check();
        $iUpdated = 0;
        $languageCode = Params::getParam('pk_c_code');
        $enabledWebstie = Params::getParam('b_enabled');
        $enabledBackoffice = Params::getParam('b_enabled_bo');
        $enabledLocationsNative = Params::getParam('b_locations_native');
        $isRtl = Params::getParam('b_rtl');
        $languageName = Params::getParam('s_name');
        $languageShortName = Params::getParam('s_short_name');
        $languageDescription = Params::getParam('s_description');
        $languageCurrencyFormat = Params::getParam('s_currency_format');
        $languageDecPoint = Params::getParam('s_dec_point');
        $languageNumDec = Params::getParam('i_num_dec');
        $languageThousandsSep = Params::getParam('s_thousands_sep');
        $languageDateFormat = Params::getParam('s_date_format');
        $languageStopWords = Params::getParam('s_stop_words');
        $currency = Params::getParam('fk_c_currency_code');


        // formatting variables
        if(!preg_match('/.{2}_.{2}/', $languageCode)) {
          osc_add_flash_error_message(_m('Language id isn\'t in the correct format'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=languages');
        }
        
        $enabledWebstie = ($enabledWebstie != '');
        $enabledBackoffice = ($enabledBackoffice != '');
        $enabledLocationsNative = ($enabledLocationsNative != '');
        $isRtl = ($isRtl != '');
        $languageName = trim(strip_tags($languageName));
        $languageShortName = trim(strip_tags($languageShortName));
        $languageDescription = trim(strip_tags($languageDescription));
        $languageCurrencyFormat = trim(strip_tags($languageCurrencyFormat));
        $languageDateFormat = trim(strip_tags($languageDateFormat));
        $languageStopWords = trim(strip_tags($languageStopWords));
        $currency = trim(strip_tags($currency));

        $msg = '';
        if(!osc_validate_text($languageName)) {
          $msg .= _m('Language name field is required') . '<br/>';
        }
        if(!osc_validate_text($languageShortName)) {
          $msg .= _m('Language short name field is required') . '<br/>';
        }
        if(!osc_validate_text($languageDescription)) {
          $msg .= _m('Language description field is required') . '<br/>';
        }
        if(!osc_validate_text($languageCurrencyFormat)) {
          $msg .= _m('Currency format field is required') . '<br/>';
        }
        if(!osc_validate_int($languageNumDec)) {
          $msg .= _m('Number of decimals must only contain numeric characters') . '<br/>';
        }
        if($msg != '') {
          osc_add_flash_error_message($msg, 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=languages&action=edit&id=' . $languageCode);
        }

        $array = array(
          'b_enabled' => $enabledWebstie,
          'b_enabled_bo' => $enabledBackoffice,
          'b_locations_native' => $enabledLocationsNative,
          'b_rtl' => $isRtl,
          's_name' => $languageName,
          's_short_name' => $languageShortName,
          's_description' => $languageDescription,
          's_currency_format' => $languageCurrencyFormat,
          's_dec_point' => $languageDecPoint,
          'i_num_dec' => $languageNumDec,
          's_thousands_sep' => $languageThousandsSep,
          's_date_format' => $languageDateFormat,
          's_stop_words' => $languageStopWords,
          'fk_c_currency_code' => $currency
        );

        $iUpdated = $this->localeManager->update($array, array('pk_c_code' => $languageCode));
        
        if($iUpdated > 0) {
          osc_add_flash_ok_message(sprintf(_m('%s has been updated'), $languageShortName), 'admin');
        }
        
        //$this->redirectTo(osc_admin_base_url(true) . '?page=languages');
        $this->redirectTo(osc_admin_base_url(true) . '?page=languages&action=edit&id=' . $languageCode);

        break;
        
      case('enable_selected'):
        osc_csrf_check();
        $msg = _m('Selected languages have been enabled for the website');
        $iUpdated = 0;
        $aValues = array('b_enabled' => 1);

        $id = Params::getParam('id');

        if(!is_array($id)) {
          osc_add_flash_warning_message(_m("The language ids aren't in the correct format"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=languages');
        }

        foreach($id as $i) {
          osc_translate_categories($i);
          $iUpdated += $this->localeManager->update($aValues, array('pk_c_code' => $i));
        }

        if($iUpdated > 0) {
          osc_add_flash_ok_message($msg, 'admin');
        }

        $this->redirectTo(osc_admin_base_url(true) . '?page=languages');
        break;
        
      case('disable_selected'):
        osc_csrf_check();
        $msg = _m('Selected languages have been disabled for the website');
        $msg_warning = '';
        $iUpdated = 0;
        $aValues = array('b_enabled' => 0);

        $id = Params::getParam('id');

        if(!is_array($id)) {
          osc_add_flash_warning_message(_m("The language ids aren't in the correct format"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=languages');
        }

        foreach($id as $i) {
          if(osc_language() == $i) {
            $msg_warning = sprintf(_m("%s can't be disabled because it's the default language"), osc_language());
            continue;
          }
          
          $iUpdated += $this->localeManager->update($aValues, array('pk_c_code' => $i));
        }

        if($msg_warning != '') {
          if($iUpdated > 0) {
            osc_add_flash_warning_message($msg . '</p><p>' . $msg_warning, 'admin');
          } else {
            osc_add_flash_warning_message($msg_warning, 'admin');
          }
        } else {
          osc_add_flash_ok_message($msg, 'admin');
        }

        $this->redirectTo(osc_admin_base_url(true) . '?page=languages');
        break;
        
      case('enable_bo_selected'):
        osc_csrf_check();
        $msg = _m('Selected languages have been enabled for the backoffice (oc-admin)');
        $iUpdated = 0;
        $aValues = array('b_enabled_bo' => 1);

        $id = Params::getParam('id');

        if(!is_array($id)) {
          osc_add_flash_warning_message(_m("The language ids aren't in the correct format"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=languages');
        }

        foreach($id as $i) {
          osc_translate_categories($i);
          $iUpdated += $this->localeManager->update($aValues, array('pk_c_code' => $i));
        }

        if($iUpdated > 0) {
          osc_add_flash_ok_message($msg, 'admin');
        }

        $this->redirectTo(osc_admin_base_url(true) . '?page=languages');
        break;
        
      case('disable_bo_selected'):
        osc_csrf_check();
        $msg = _m('Selected languages have been disabled for the backoffice (oc-admin)');
        $msg_warning = '';
        $iUpdated = 0;
        $aValues = array('b_enabled_bo' => 0);

        $id = Params::getParam('id');

        if(!is_array($id)) {
          osc_add_flash_warning_message(_m("The language ids aren't in the correct format"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=languages');
        }

        foreach($id as $i) {
          if(osc_language() == $i) {
            $msg_warning = sprintf(_m("%s can't be disabled because it's the default language"), osc_language());
            continue;
          }
          
          $iUpdated += $this->localeManager->update($aValues, array('pk_c_code' => $i));
        }

        if($msg_warning != '') {
          if($iUpdated > 0) {
            osc_add_flash_warning_message($msg . '</p><p>' . $msg_warning, 'admin');
          } else {
            osc_add_flash_warning_message($msg_warning, 'admin');
          }
        } else {
          osc_add_flash_ok_message($msg, 'admin');
        }

        $this->redirectTo(osc_admin_base_url(true) . '?page=languages');
        break;
        
      case('delete'):
        osc_csrf_check();
        if(is_array(Params::getParam('id'))) {
          $default_lang = osc_language();
          foreach(Params::getParam('id') as $code) {
            if($default_lang != $code) {
              if($this->localeManager->deleteLocale($code)) {
                if(!osc_deleteDir(osc_translations_path() . $code)) {
                  osc_add_flash_error_message(sprintf(_m("Directory '%s' couldn't be removed"), $code), 'admin');
                } else {
                  osc_add_flash_ok_message(
                    sprintf(_m('Directory "%s" has been successfully removed'), $code),
                    'admin'
                  );
                }
              } else {
                osc_add_flash_error_message(sprintf(_m("Directory '%s' couldn't be removed;)"), $code), 'admin');
              }
            } else {
              osc_add_flash_error_message(
                sprintf(
                  _m(
                    "Directory '%s' couldn't be removed because it's the default language. <a href=\"%s\">Set another language</a> as default first and try again"
                  ),
                  $code,
                  osc_admin_base_url(true) . '?page=settings'
                ),
                'admin'
              );
            }
          }
        }
        
        $this->redirectTo(osc_admin_base_url(true) . '?page=languages');
        break;
        
      default:
        if(Params::getParam('marketError') > 0) {
          osc_add_flash_warning_message(sprintf(__('There was problem with update: [%s] %s. You may download update manually at: %s'), Params::getParam('marketError'), Params::getParam('message'), Params::getParam('slug')), 'admin');
        }
        
        if(Params::getParam('checkUpdated') != '') {
          osc_admin_toolbar_update_languages(true);
        }

        if(Params::getParam('action') != '') {
          osc_run_hook('language_bulk_' . Params::getParam('action'), Params::getParam('id'));
        }

        if(Params::getParam('iDisplayLength') == '') {
          Params::setParam('iDisplayLength', 25);
        }
        
        $this->_exportVariableToView('iDisplayLength', Params::getParam('iDisplayLength'));

        $p_iPage = 1;
        if(is_numeric(Params::getParam('iPage')) && Params::getParam('iPage') >= 1) {
          $p_iPage = Params::getParam('iPage');
        }
        
        Params::setParam('iPage', $p_iPage);

        $aLanguages = OSCLocale::newInstance()->listAll();

        // pagination
        $start = ($p_iPage - 1) * Params::getParam('iDisplayLength');
        $limit = Params::getParam('iDisplayLength');
        $count = count($aLanguages);

        $displayRecords = $limit;
        if(($start + $limit) > $count) {
          $displayRecords = ($start + $limit) - $count;
        }
        
        $aLanguagesToUpdate = json_decode(osc_get_preference('languages_to_update'));
        $bLanguagesToUpdate = is_array($aLanguagesToUpdate) ? true : false;
        
        $aData = array();
        $max = ($start + $limit);
        
        if($max > $count) {
          $max = $count;
        }
        
        for ($i = $start; $i < $max; $i++) {
          $l = $aLanguages[$i];
          $row = array();
          
          $status = ($l['b_enabled'] ? 'F' : '') . ($l['b_enabled_bo'] ? 'B' : '');

          $row['status-border'] = '';

          if($status == 'F') {
            $row['status'] = __('Enabled in FO');
            $row['class'] = 'status-active';
          } else if($status == 'FB') {
            $row['status'] = __('Enabled in FO');
            $row['class'] = 'status-active';
          } else if($status == 'B') {
            $row['status'] = __('Enabled in BO');
            $row['class'] = 'status-warning';
          } else if($status == '') {
            $row['status'] = __('Disabled');
            $row['class'] = 'status-inactive';
          }  
          
          $row['bulkactions'] = '<input type="checkbox" name="id[]" value="' . $l['pk_c_code'] . '" />';

          $options = array();
          $options[] = '<a href="' . osc_admin_base_url(true) . '?page=languages&amp;action=edit&amp;id=' . $l['pk_c_code'] . '">' . __('Edit') . '</a>';
          $options[] = '<a href="' . osc_admin_base_url(true) . '?page=languages&amp;action=' . ($l['b_enabled'] == 1 ? 'disable_selected' : 'enable_selected') . '&amp;id[]=' . $l['pk_c_code'] . '&amp;' . osc_csrf_token_url() . '">' . ($l['b_enabled'] == 1 ? __('Disable in Front-office') : __('Enable in Front-office')) . '</a> ';
          $options[] = '<a href="' . osc_admin_base_url(true) . '?page=languages&amp;action=' . ($l['b_enabled_bo'] == 1 ? 'disable_bo_selected' : 'enable_bo_selected') . '&amp;id[]=' . $l['pk_c_code'] . '&amp;' . osc_csrf_token_url() . '">' . ($l['b_enabled_bo'] == 1 ? __('Disable in Back-office') : __('Enable in Back-office')) . '</a>';
          $options[] = '<a onclick="return delete_dialog(\'' . $l['pk_c_code'] . '\');"  href="' . osc_admin_base_url(true) . '?page=languages&amp;action=delete&amp;id[]=' . $l['pk_c_code'] . '&amp;' . osc_csrf_token_url() . '">' . __('Delete') . '</a>';

          $auxOptions = '<ul>' . PHP_EOL;
          
          foreach($options as $actual) {
            $auxOptions .= '<li>' . $actual . '</li>' . PHP_EOL;
          }
          
          $actions = '<div class="actions">' . $auxOptions . '</div>' . PHP_EOL;

          $sUpdate = '';
          // get languages to update from t_preference
          if($bLanguagesToUpdate && in_array($l['pk_c_code'], $aLanguagesToUpdate)) {
            $sUpdate = '<a class="btn-market-update btn-market-popup btn-lang-update btn" href="#' . htmlentities($l['pk_c_code']) . '">' . __("Update") . '</a>';
          }
          
          $row['name'] = $l['s_name'] . $sUpdate . $actions;
          $row['short_name'] = $l['s_short_name'];
          $row['description'] = $l['s_description'];
          $row['enabled_fo'] = ($l['b_enabled'] ? __('Enabled') : __('Disabled'));
          $row['enabled_bo'] = ($l['b_enabled_bo'] ? __('Enabled') : __('Disabled'));
          $row['locations_native'] = ($l['b_locations_native'] ? __('Enabled') : __('Disabled'));
          $row['rtl'] = ($l['b_rtl'] ? __('RTL') : __('LTR'));

          $aData[] = $row;
        }
        
        $array['iTotalRecords'] = $displayRecords;
        $array['iTotalDisplayRecords'] = count($aLanguages);
        $array['iDisplayLength'] = $limit;
        $array['aaData'] = $aData;

        $page = (int)Params::getParam('iPage');
        
        if(is_array($array['aaData']) && count($array['aaData']) == 0 && $page != 1) {
          $total = $array['iTotalDisplayRecords'];
          $maxPage = ceil($total / (int)$array['iDisplayLength']);

          $url = osc_admin_base_url(true) . '?' . Params::getServerParam('QUERY_STRING', false, false);

          if($maxPage == 0) {
            $url = preg_replace('/&iPage=(\d)+/', '&iPage=1', $url);
            $this->redirectTo($url);
          }

          if($page > 1) {
            $url = preg_replace('/&iPage=(\d)+/', '&iPage=' . $maxPage, $url);
            $this->redirectTo($url);
          }
        }

        $this->_exportVariableToView('aLanguages', $array);

        $bulk_options = array(
          array('value' => '', 'data-dialog-content' => '', 'label' => __('Bulk actions')),
          array(
            'value' => 'enable_selected',
            'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected languages?'), strtolower(__('Enable in Front-office'))),
            'label' => __('Enable in Front-office')
          ),
          array(
            'value' => 'disable_selected',
            'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected languages?'), strtolower(__('Disable in Front-office'))),
            'label' => __('Disable in Front-office')
          ),
          array(
            'value' => 'enable_bo_selected',
            'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected languages?'), strtolower(__('Enable in Back-office'))),
            'label' => __('Enable in Back-office')
          ),
          array(
            'value' => 'disable_bo_selected',
            'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected languages?'), strtolower(__('Disable in Back-office'))),
            'label' => __('Disable in Back-office')
          ),
          array(
            'value' => 'delete',
            'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected languages?'), strtolower(__('Delete'))),
            'label' => __('Delete')
          )
        );
        
        $bulk_options = osc_apply_filter('language_bulk_filter', $bulk_options);
        $this->_exportVariableToView('bulk_options', $bulk_options);

        $this->doView('languages/index.php');
        break;
    }
  }

  /**
   * @param $file
   */
  public function doView($file) {
    osc_run_hook('before_admin_html');
    osc_current_admin_theme_path($file);
    Session::newInstance()->_clearVariables();
    osc_run_hook('after_admin_html');
  }
}

/* file end: ./oc-admin/languages.php */