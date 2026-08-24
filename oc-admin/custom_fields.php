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


class CAdminCFields extends AdminSecBaseModel {
  private $fieldManager;

  function __construct() {
    parent::__construct();
    $this->fieldManager = Field::newInstance();
  }

  // Build redirect URL preserving custom fields list state
  private function fieldsAdminListUrl($extra = array()) {
    return Field::adminListUrl($extra);
  }

  function doModel() {
    parent::doModel();

    switch($this->action) {
      case('add'):
        $categories = Category::newInstance()->toTreeAll();
        $field = array(
          'pk_i_id' => '',
          's_name' => '',
          's_slug' => '',
          'e_type' => 'DROPDOWN',
          's_options' => '',
          'b_required' => 0,
          'b_searchable' => 0
        );

        $this->_exportVariableToView('field', $field);
        $this->_exportVariableToView('is_add', true);
        $this->_exportVariableToView('categories', $categories);
        $this->_exportVariableToView('selected', array());
        $this->_exportVariableToView('list_url', $this->fieldsAdminListUrl());
        $this->doView('fields/frm.php');
        break;

      case('edit'):
        $id = (int)Params::getParam('id');
        if($id <= 0) {
          $this->redirectTo($this->fieldsAdminListUrl());
        }

        $field = $this->fieldManager->findByPrimaryKey($id);
        if(empty($field['pk_i_id'])) {
          osc_add_flash_warning_message(__('Custom field not found'), 'admin');
          $this->redirectTo($this->fieldsAdminListUrl());
        }

        $categories = Category::newInstance()->toTreeAll();
        $selected = $this->fieldManager->categories($id);
        if($selected == null) {
          $selected = array();
        }

        $this->_exportVariableToView('field', $field);
        $this->_exportVariableToView('is_add', false);
        $this->_exportVariableToView('categories', $categories);
        $this->_exportVariableToView('selected', $selected);
        $this->_exportVariableToView('list_url', $this->fieldsAdminListUrl());
        $this->doView('fields/frm.php');
        break;

      case('edit_post'):
        osc_csrf_check();
        $id = (int)Params::getParam('id');
        if($id <= 0) {
          $this->redirectTo($this->fieldsAdminListUrl());
        }

        $field = $this->fieldManager->findByPrimaryKey($id);
        if(empty($field['pk_i_id'])) {
          osc_add_flash_warning_message(__('Custom field not found'), 'admin');
          $this->redirectTo($this->fieldsAdminListUrl());
        }

        $save = $this->fieldManager->saveAdminConfiguration($id);
        osc_run_hook('edited_field', $id, ($save['ok'] ? 0 : 1));

        if($save['ok']) {
          osc_add_flash_ok_message($save['message'], 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=custom_fields&action=edit&id=' . $id);
        }

        osc_add_flash_error_message($save['message'], 'admin');
        $this->redirectTo(osc_admin_base_url(true) . '?page=custom_fields&action=edit&id=' . $id);
        break;

      case('add_post'):
        osc_csrf_check();
        $save = $this->fieldManager->createAdminField();
        if($save['ok']) {
          osc_run_hook('added_field', (int)$save['field_id']);
          osc_add_flash_ok_message($save['message'], 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=custom_fields&action=edit&id=' . (int)$save['field_id']);
        }

        osc_add_flash_error_message($save['message'], 'admin');
        $categories = Category::newInstance()->toTreeAll();
        $field = array(
          'pk_i_id' => '',
          's_name' => Params::getParam('s_name'),
          's_slug' => Params::getParam('field_slug'),
          'e_type' => Params::getParam('field_type'),
          's_options' => Params::getParam('s_options'),
          'b_required' => (Params::getParam('field_required') == '1' ? 1 : 0),
          'b_searchable' => (Params::getParam('field_searchable') == '1' ? 1 : 0)
        );
        if(!in_array($field['e_type'], Field::allowedTypes(), true)) {
          $field['e_type'] = 'DROPDOWN';
        }
        $selected = Params::getParam('categories');
        if(!is_array($selected)) {
          $selected = array();
        }

        $this->_exportVariableToView('field', $field);
        $this->_exportVariableToView('is_add', true);
        $this->_exportVariableToView('categories', $categories);
        $this->_exportVariableToView('selected', $selected);
        $this->_exportVariableToView('list_url', $this->fieldsAdminListUrl());
        $this->doView('fields/frm.php');
        break;

      case('delete'):
        osc_csrf_check();
        $id = (int)Params::getParam('id');
        $res = $this->fieldManager->deleteByPrimaryKey($id);
        if($res > 0) {
          osc_add_flash_ok_message(__('The custom field has been deleted'), 'admin');
        } else {
          osc_add_flash_error_message(__('An error occurred while deleting'), 'admin');
        }
        $this->redirectTo($this->fieldsAdminListUrl());
        break;

      case('reorder'):
        $this->_exportVariableToView('fields', $this->fieldManager->listAll());
        $this->doView('fields/reorder.php');
        break;

      default:
        if(Params::getParam('action') != '') {
          osc_run_hook('field_bulk_' . Params::getParam('action'), Params::getParam('id'));
        }

        $bulkAction = Params::getParam('action');
        if($bulkAction != '' && is_array(Params::getParam('id'))) {
          $this->processBulkAction($bulkAction, Params::getParam('id'));
        }

        require_once osc_lib_path() . 'osclass/classes/datatables/FieldsDataTable.php';

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
          Params::setParam('sort', 'order');
        }
        if(Params::getParam('sort') == 'position') {
          Params::setParam('sort', 'order');
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
        $fieldsDataTable = new FieldsDataTable();
        $fieldsDataTable->table($params);
        $aData = $fieldsDataTable->getData();

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

        $bulk_options = array(
          array('value' => '', 'data-dialog-content' => '', 'label' => __('Bulk actions')),
          array('value' => 'delete', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected custom fields?'), strtolower(__('Delete'))), 'label' => __('Delete')),
          array('value' => 'make_required', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected custom fields?'), strtolower(__('Make required'))), 'label' => __('Make required')),
          array('value' => 'make_optional', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected custom fields?'), strtolower(__('Make optional'))), 'label' => __('Make optional')),
          array('value' => 'add_search', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected custom fields?'), strtolower(__('Add to search'))), 'label' => __('Add to search')),
          array('value' => 'remove_search', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected custom fields?'), strtolower(__('Remove from search'))), 'label' => __('Remove from search')),
        );
        $bulk_options = osc_apply_filter('field_bulk_filter', $bulk_options);

        $this->_exportVariableToView('aData', $aData);
        $this->_exportVariableToView('aRawRows', $fieldsDataTable->rawRows());
        $this->_exportVariableToView('withFilters', $fieldsDataTable->withFilters());
        $this->_exportVariableToView('bulk_options', $bulk_options);
        $this->_exportVariableToView('list_url', $this->fieldsAdminListUrl());

        $this->doView('fields/index.php');
        break;
    }
  }

  // Process datatable bulk actions
  private function processBulkAction($action, $ids) {
    if(!is_array($ids) || count($ids) == 0) {
      return;
    }

    osc_csrf_check();
    $changed = 0;

    switch($action) {
      case('delete'):
        foreach($ids as $id) {
          if($this->fieldManager->deleteByPrimaryKey((int)$id) > 0) {
            $changed++;
          }
        }
        if($changed > 0) {
          osc_add_flash_ok_message(sprintf(_n('One custom field has been deleted', '%d custom fields have been deleted', $changed), $changed), 'admin');
        }
        break;

      case('make_required'):
        foreach($ids as $id) {
          $this->fieldManager->update(array('b_required' => 1), array('pk_i_id' => (int)$id));
          $changed++;
        }
        if($changed > 0) {
          osc_add_flash_ok_message(__('Required status updated'), 'admin');
        }
        break;

      case('make_optional'):
        foreach($ids as $id) {
          $this->fieldManager->update(array('b_required' => 0), array('pk_i_id' => (int)$id));
          $changed++;
        }
        if($changed > 0) {
          osc_add_flash_ok_message(__('Required status updated'), 'admin');
        }
        break;

      case('add_search'):
        foreach($ids as $id) {
          $this->fieldManager->update(array('b_searchable' => 1), array('pk_i_id' => (int)$id));
          $changed++;
        }
        if($changed > 0) {
          osc_add_flash_ok_message(__('Searchable status updated'), 'admin');
        }
        break;

      case('remove_search'):
        foreach($ids as $id) {
          $this->fieldManager->update(array('b_searchable' => 0), array('pk_i_id' => (int)$id));
          $changed++;
        }
        if($changed > 0) {
          osc_add_flash_ok_message(__('Searchable status updated'), 'admin');
        }
        break;

      default:
        return;
    }

    $this->redirectTo($this->fieldsAdminListUrl());
  }

  function doView($file) {
    osc_run_hook('before_admin_html');
    osc_current_admin_theme_path($file);
    Session::newInstance()->_clearVariables();
    osc_run_hook('after_admin_html');
  }
}

/* file end: ./oc-admin/custom_fields.php */
