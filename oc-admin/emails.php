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


class CAdminEmails extends AdminSecBaseModel {
  //specific for this class
  private $emailManager;

  function __construct() {
    parent::__construct();

    //specific things for this class
    $this->emailManager = Page::newInstance();

    // Check and remove duplicated email templates
    $this->emailManager->deleteDuplicatedInternalNames();
  }

  //Business Layer...
  function doModel() {
    parent::doModel();

    //specific things for this class
    switch($this->action) {

      case 'edit':
        $id = (int)Params::getParam('id');
        if($id <= 0 || !$this->emailManager->isIndelible($id)) {
          $this->redirectTo(osc_admin_base_url(true)."?page=emails");
        }

        $form   = count(Session::newInstance()->_getForm());
        $keepForm = count(Session::newInstance()->_getKeepForm());
        if($form == 0 || $form == $keepForm) {
          Session::newInstance()->_dropKeepForm();
        }

        $this->_exportVariableToView("email", $this->emailManager->findByPrimaryKey($id));
        $this->doView("emails/frm.php");
        break;

      case 'edit_post':
        osc_csrf_check();
        $id = (int)Params::getParam('id');
        if($id <= 0 || !$this->emailManager->isIndelible($id)) {
          osc_add_flash_error_message(_m('Selected page is not an email template'), 'admin');
          $this->redirectTo(osc_admin_base_url(true)."?page=emails");
        }

        $s_internal_name = Params::getParam("s_internal_name");

        $aFieldsDescription = array();
        $postParams = Params::getParamsAsArray('', false);
        $not_empty = false;
        foreach($postParams as $k => $v) {
          if(preg_match('|(.+?)#(.+)|', $k, $m)) {
            if($m[2]=='s_title' && $v!='') { $not_empty = true; }
            $aFieldsDescription[$m[1]][$m[2]] = $v;
          }
        }

        Session::newInstance()->_setForm('s_internal_name',$s_internal_name);
        Session::newInstance()->_setForm('aFieldsDescription',$aFieldsDescription);

        if($not_empty) {
          foreach($aFieldsDescription as $k => $_data) {
            $this->emailManager->updateDescription($id, $k, $_data['s_title'], $_data['s_text']);
          }

          if(!$this->emailManager->internalNameExists($id, $s_internal_name)) {
            if(!$this->emailManager->isIndelible($id)) {
              $this->emailManager->updateInternalName($id, $s_internal_name);
            }
            Session::newInstance()->_clearVariables();
            osc_add_flash_ok_message( _m('The email/alert has been updated'), 'admin' );
            $this->redirectTo(osc_admin_base_url(true)."?page=emails");
          }
          osc_add_flash_error_message( _m('You can\'t repeat internal name'), 'admin');
        } else {
          osc_add_flash_error_message( _m('The email couldn\'t be updated, at least one title should not be empty'), 'admin');
        }
        $this->redirectTo(osc_admin_base_url(true)."?page=emails&action=edit&id=" . $id);
        break;

      default:
        require_once osc_lib_path()."osclass/classes/datatables/EmailTemplatesDataTable.php";

        if(Params::getParam('iDisplayLength') == '') {
          Params::setParam('iDisplayLength', 25);
        }

        if(Params::getParam('sort') == '') {
          Params::setParam('sort', 'internal_name');
        }

        if(Params::getParam('direction') == '') {
          Params::setParam('direction', 'asc');
        }

        $p_iPage = 1;
        if(is_numeric(Params::getParam('iPage')) && Params::getParam('iPage') >= 1) {
          $p_iPage = Params::getParam('iPage');
        }
        Params::setParam('iPage', $p_iPage);

        $params = Params::getParamsAsArray();
        $emailTemplatesDataTable = new EmailTemplatesDataTable();
        $emailTemplatesDataTable->table($params);
        $aData = $emailTemplatesDataTable->getData();

        if(count($aData['aRows']) == 0 && $p_iPage != 1) {
          $total = (int)$aData['iTotalDisplayRecords'];
          $maxPage = ceil($total / (int)$aData['iDisplayLength']);
          $url = osc_admin_base_url(true) . '?' . Params::getServerParam('QUERY_STRING', false, false);

          if($maxPage == 0) {
            $url = preg_replace('/&iPage=(\d)+/', '&iPage=1', $url);
            $this->redirectTo($url);
          }

          if($p_iPage > 1) {
            $url = preg_replace('/&iPage=(\d)+/', '&iPage=' . $maxPage, $url);
            $this->redirectTo($url);
          }
        }

        $this->_exportVariableToView('aData', $aData);
        $this->_exportVariableToView('aRawRows', $emailTemplatesDataTable->rawRows());
        $this->_exportVariableToView('iDisplayLength', Params::getParam('iDisplayLength'));

        $this->doView("emails/index.php");
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

/* file end: ./oc-admin/emails.php */
