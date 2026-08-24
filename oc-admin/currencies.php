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


class CAdminCurrencies extends AdminSecBaseModel {
  //Business Layer...
  function doModel() {
    switch(Params::getParam('action')) {
      case('add'):
        // calling add currency view
        $aCurrency = array(
          'pk_c_code' => '',
          's_name' => '',
          's_description' => '',
          'd_exchange_rate' => ''
        );

        $this->_exportVariableToView('aCurrency', $aCurrency);
        $this->_exportVariableToView('typeForm', 'add_post');

        $this->doView('currencies/frm.php');
        break;

      case('add_post'):
        // adding a new currency
        osc_csrf_check();
        $currencyCode = Params::getParam('pk_c_code');
        $currencyName = Params::getParam('s_name');
        $currencyDescription = Params::getParam('s_description');

        // cleaning parameters
        $currencyName = trim(strip_tags($currencyName));
        $currencyDescription = trim(strip_tags($currencyDescription));
        $currencyCode = trim(strip_tags($currencyCode));

        if(!preg_match('/^.{1,3}$/', $currencyCode)) {
          osc_add_flash_error_message(_m('The currency code is not in the correct format'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=currencies&action=add');
        }

        $eraw = trim((string)Params::getParam('d_exchange_rate'));
        $dExchange = null;
        if($eraw !== '') {
          if(!is_numeric($eraw)) {
            osc_add_flash_error_message(_m('Exchange rate must be a number'), 'admin');
            $this->redirectTo(osc_admin_base_url(true) . '?page=currencies&action=add');
          }
          $dExchange = (float)$eraw;
        }

        $fields = array(
          'pk_c_code' => $currencyCode,
          's_name' => $currencyName,
          's_description' => $currencyDescription,
          'd_exchange_rate' => $dExchange,
          'b_enabled' => 1,
        );

        $isInserted = Currency::newInstance()->insert($fields);

        if($isInserted) {
          Currency::clearStaticCache();
          Currency::clearListAllRawCache();
          osc_add_flash_ok_message(_m('Currency added'), 'admin');
        } else {
          osc_add_flash_error_message(_m("Currency couldn't be added"), 'admin');
        }
        $this->redirectTo(osc_admin_base_url(true) . '?page=currencies');
        break;

      case('edit'):
        // calling edit currency view
        $currencyCode = Params::getParam('code');
        $currencyCode = trim(strip_tags($currencyCode));

        if($currencyCode == '') {
          osc_add_flash_warning_message(sprintf(_m("The currency code '%s' doesn't exist"), $currencyCode), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=currencies');
        }

        $aCurrency = Currency::newInstance()->findByPrimaryKey($currencyCode);

        if(!$aCurrency) {
          osc_add_flash_warning_message(sprintf(_m("The currency code '%s' doesn't exist"), $currencyCode), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=currencies');
        }

        $this->_exportVariableToView('aCurrency', $aCurrency);
        $this->_exportVariableToView('typeForm', 'edit_post');

        $this->doView('currencies/frm.php');
        break;

      case('edit_post'):
        // updating currency
        osc_csrf_check();
        $currencyName = Params::getParam('s_name');
        $currencyDescription = Params::getParam('s_description');
        $currencyCode = Params::getParam('pk_c_code');

        // cleaning parameters
        $currencyName = trim(strip_tags($currencyName));
        $currencyDescription = trim(strip_tags($currencyDescription));
        $currencyCode = trim(strip_tags($currencyCode));

        if(!preg_match('/.{1,3}/', $currencyCode)) {
          osc_add_flash_error_message(_m('Error: the currency code is not in the correct format'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=currencies');
        }

        $eraw = trim((string)Params::getParam('d_exchange_rate'));
        $dExchange = null;
        if($eraw !== '') {
          if(!is_numeric($eraw)) {
            osc_add_flash_error_message(_m('Exchange rate must be a number'), 'admin');
            $this->redirectTo(osc_admin_base_url(true) . '?page=currencies&action=edit&code=' . rawurlencode($currencyCode));
          }
          $dExchange = (float)$eraw;
        }

        $updated = Currency::newInstance()->update(
          array(
            's_name' => $currencyName,
            's_description' => $currencyDescription,
            'd_exchange_rate' => $dExchange,
          ),
          array('pk_c_code' => $currencyCode)
        );

        if(Params::getParam('b_set_default_currency') == '1') {
          osc_set_preference('currency', $currencyCode);
        }

        Currency::clearStaticCache();
        Currency::clearListAllRawCache();

        if($updated == 1 || Params::getParam('b_set_default_currency') == '1') {
          osc_add_flash_ok_message(_m('Currency updated'), 'admin');
        } else {
          osc_add_flash_info_message(_m('No changes were made'), 'admin');
        }
        $this->redirectTo(osc_admin_base_url(true) . '?page=currencies');
        break;

      case('delete'):
        // deleting a currency
        osc_csrf_check();
        $rowChanged = 0;
        $aCurrencyCode = Params::getParam('code');

        if(!is_array($aCurrencyCode)) {
          $aCurrencyCode = array($aCurrencyCode);
        }

        $msg_current = '';
        foreach($aCurrencyCode as $currencyCode) {
          if(preg_match('/.{1,3}/', $currencyCode) && $currencyCode != osc_currency()) {
            $rowChanged += Currency::newInstance()->delete(array('pk_c_code' => $currencyCode));
          }

          // foreign key error
          if(Currency::newInstance()->getErrorLevel() == '1451') {
            $msg_current .= sprintf('</p><p>' . _m("%s couldn't be deleted because it has listings associated to it"), $currencyCode);
          } else if($currencyCode == osc_currency()) {
            $msg_current .= sprintf('</p><p>' . _m("%s couldn't be deleted because it's the default currency"), $currencyCode);
          }
        }

        $msg = '';
        $status = '';
        switch($rowChanged) {
          case('0'):
            $msg = _m('No currencies have been deleted');
            $status = 'error';
            break;
          case('1'):
            $msg = _m('One currency has been deleted');
            $status = 'ok';
            break;
          default:
            $msg = sprintf(_m('%s currencies have been deleted'), $rowChanged);
            $status = 'ok';
            break;
        }

        if($status == 'ok' && $msg_current != '') {
          $status = 'warning';
        }

        switch($status) {
          case('error'):
            osc_add_flash_error_message($msg . $msg_current, 'admin');
            break;
          case('warning'):
            osc_add_flash_warning_message($msg . $msg_current, 'admin');
            break;
          case('ok'):
            osc_add_flash_ok_message($msg, 'admin');
            break;
        }

        Currency::clearStaticCache();
        Currency::clearListAllRawCache();

        $this->redirectTo(osc_admin_base_url(true) . '?page=currencies');
        break;

      case('enable_selected'):
        osc_csrf_check();
        $aCurrencyCode = Params::getParam('code');
        if(!is_array($aCurrencyCode)) {
          $aCurrencyCode = array($aCurrencyCode);
        }
        $rowChanged = 0;
        foreach($aCurrencyCode as $currencyCode) {
          $currencyCode = trim(strip_tags((string)$currencyCode));
          if(preg_match('/^.{1,3}$/', $currencyCode) && $currencyCode != '') {
            $rowChanged += (int)Currency::newInstance()->update(array('b_enabled' => 1), array('pk_c_code' => $currencyCode));
          }
        }
        Currency::clearStaticCache();
        Currency::clearListAllRawCache();
        if($rowChanged > 0) {
          osc_add_flash_ok_message(_m('Selected currencies have been enabled'), 'admin');
        } else {
          osc_add_flash_info_message(_m('No currencies have been updated'), 'admin');
        }
        $this->redirectTo(osc_admin_base_url(true) . '?page=currencies');
        break;

      case('disable_selected'):
        osc_csrf_check();
        $aCurrencyCode = Params::getParam('code');
        if(!is_array($aCurrencyCode)) {
          $aCurrencyCode = array($aCurrencyCode);
        }
        $rowChanged = 0;
        $msg_current = '';
        foreach($aCurrencyCode as $currencyCode) {
          $currencyCode = trim(strip_tags((string)$currencyCode));
          if(!preg_match('/^.{1,3}$/', $currencyCode) || $currencyCode == '') {
            continue;
          }
          if($currencyCode == osc_currency()) {
            $msg_current .= sprintf('</p><p>' . _m("%s can't be disabled because it's the default currency"), $currencyCode);
            continue;
          }
          $rowChanged += (int)Currency::newInstance()->update(array('b_enabled' => 0), array('pk_c_code' => $currencyCode));
        }
        $msg = '';
        $status = '';
        if($rowChanged > 0) {
          $msg = ($rowChanged == 1 ? _m('One currency has been disabled') : sprintf(_m('%s currencies have been disabled'), $rowChanged));
          $status = 'ok';
        } else {
          $msg = _m('No currencies have been disabled');
          $status = ($msg_current != '' ? 'warning' : 'error');
        }
        if($status == 'ok' && $msg_current != '') {
          $status = 'warning';
        }
        Currency::clearStaticCache();
        Currency::clearListAllRawCache();
        switch($status) {
          case('error'):
            osc_add_flash_error_message($msg . $msg_current, 'admin');
            break;
          case('warning'):
            osc_add_flash_warning_message($msg . $msg_current, 'admin');
            break;
          default:
            osc_add_flash_ok_message($msg, 'admin');
            break;
        }
        $this->redirectTo(osc_admin_base_url(true) . '?page=currencies');
        break;

      default:
        require_once osc_lib_path() . 'osclass/classes/datatables/CurrenciesDataTable.php';

        if(Params::getParam('iDisplayLength') != '') {
          Cookie::newInstance()->push('listing_iDisplayLength', Params::getParam('iDisplayLength'));
          Cookie::newInstance()->set();
        } else {
          if(Cookie::newInstance()->get_value('listing_iDisplayLength') != '') {
            Params::setParam('iDisplayLength', Cookie::newInstance()->get_value('listing_iDisplayLength'));
          } else {
            Params::setParam('iDisplayLength', 25);
          }
        }
        $this->_exportVariableToView('iDisplayLength', Params::getParam('iDisplayLength'));

        if(Params::getParam('sort') == '') {
          Params::setParam('sort', 'name');
        }
        if(Params::getParam('direction') == '') {
          Params::setParam('direction', 'asc');
        }

        $page = (int)Params::getParam('iPage');
        if($page == 0) {
          $page = 1;
        }
        Params::setParam('iPage', $page);

        $params = Params::getParamsAsArray();
        $currenciesDataTable = new CurrenciesDataTable();
        $currenciesDataTable->table($params);
        $aData = $currenciesDataTable->getData();

        if(count($aData['aRows']) == 0 && $page != 1) {
          $total = (int)$aData['iTotalDisplayRecords'];
          $maxPage = (int)ceil($total / (int)$aData['iDisplayLength']);
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

        $this->_exportVariableToView('aData', $aData);
        $this->_exportVariableToView('aRawRows', $currenciesDataTable->rawRows());
        $this->_exportVariableToView('withFilters', $currenciesDataTable->withFilters());

        $bulk_options = array(
          array('value' => '', 'data-dialog-content' => '', 'label' => __('Bulk actions')),
          array('value' => 'enable_selected', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected currencies?'), strtolower(__('Enable'))), 'label' => __('Enable')),
          array('value' => 'disable_selected', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected currencies?'), strtolower(__('Disable'))), 'label' => __('Disable')),
          array('value' => 'delete', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected currencies?'), strtolower(__('Delete'))), 'label' => __('Delete')),
        );
        $this->_exportVariableToView('bulk_options', $bulk_options);

        $this->doView('currencies/index.php');
        break;
    }
  }

  //hopefully generic...
  function doView($file) {
    osc_run_hook("before_admin_html");
    osc_current_admin_theme_path($file);
    Session::newInstance()->_clearVariables();
    osc_run_hook("after_admin_html");
  }
}

/* file end: ./oc-admin/currencies.php */
