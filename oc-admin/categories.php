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


class CAdminCategories extends AdminSecBaseModel {
  private $categoryManager;

  function __construct() {
    parent::__construct();
    $this->categoryManager = Category::newInstance(osc_current_admin_locale());
  }

  // Build redirect URL preserving full list state
  private function categoriesAdminListUrl($extra = array()) {
    return osc_categories_admin_list_url($extra);
  }

  // Enable or disable one category (root cascades to children)
  private function setCategoryEnabled($id, $enabled) {
    $id = (int)$id;
    $enabled = ((int)$enabled === 1 ? 1 : 0);
    $mCategory = Category::newInstance();
    $aCategory = $mCategory->findByPrimaryKey($id);

    if($aCategory == false) {
      return array('ok' => false, 'msg' => sprintf(__('No category with id %d exists'), $id));
    }

    if($aCategory['fk_i_parent_id'] == '' || $aCategory['fk_i_parent_id'] === null) {
      $mCategory->update(array('b_enabled' => $enabled), array('pk_i_id' => $id));
      $mCategory->update(array('b_enabled' => $enabled), array('fk_i_parent_id' => $id));
      $subCategories = $mCategory->findSubcategories($id);
      $aIds = array($id);
      foreach($subCategories as $subcategory) {
        $aIds[] = (int)$subcategory['pk_i_id'];
      }
      Item::newInstance()->enableByCategory($enabled, $aIds);
      if($enabled) {
        return array('ok' => true, 'msg' => __('The category as well as its subcategories have been enabled'));
      }
      return array('ok' => true, 'msg' => __('The category as well as its subcategories have been disabled'));
    }

    $parentCategory = $mCategory->findRootCategory($id);
    if($enabled && (!$parentCategory || !(int)$parentCategory['b_enabled'])) {
      return array('ok' => false, 'msg' => __('Parent category is disabled, you can not enable that category'));
    }

    $mCategory->update(array('b_enabled' => $enabled), array('pk_i_id' => $id));
    if($enabled) {
      return array('ok' => true, 'msg' => __('The subcategory has been enabled'));
    }
    return array('ok' => true, 'msg' => __('The subcategory has been disabled'));
  }

  function doModel() {
    parent::doModel();

    switch($this->action) {
      case('add_post_default'):
        osc_csrf_check();
        $parentId = (int)Params::getParam('parent');
        $fields['fk_i_parent_id'] = NULL;
        if($parentId > 0) {
          $parentCat = $this->categoryManager->findByPrimaryKey($parentId);
          if($parentCat) {
            $fields['fk_i_parent_id'] = $parentId;
          } else {
            $parentId = 0;
          }
        }
        $fields['i_expiration_days'] = 0;
        $fields['i_position'] = 1;
        $fields['b_enabled'] = 1;
        $fields['b_price_enabled'] = 1;

        $default_locale = osc_language();
        $aFieldsDescription[$default_locale]['s_name'] = "NEW CATEGORY, EDIT ME!";

        $categoryId = $this->categoryManager->insert($fields, $aFieldsDescription);

        if($parentId > 0) {
          $siblings = $this->categoryManager->findSubcategories($parentId);
        } else {
          $siblings = $this->categoryManager->findRootCategories();
        }
        foreach($siblings as $cat) {
          if((int)$cat['pk_i_id'] == (int)$categoryId) {
            continue;
          }
          $this->categoryManager->updateOrder($cat['pk_i_id'], (int)$cat['i_position'] + 1);
        }
        $this->categoryManager->updateOrder($categoryId, 1);
        $this->categoryManager->normalizeOrdersByParent($parentId);

        osc_run_hook('add_category', (int)($categoryId));

        $this->redirectTo(osc_admin_base_url(true) . '?page=categories&action=reorder');
        break;

      case('add'):
        $parentId = (int)Params::getParam('parent');
        $category = array(
          'pk_i_id' => 0,
          'fk_i_parent_id' => null,
          'i_expiration_days' => 0,
          'b_price_enabled' => 1,
          's_icon' => '',
          's_color' => '',
          'locale' => array()
        );
        if($parentId > 0 && $this->canSetParentForNewCategory($parentId)) {
          $category['fk_i_parent_id'] = $parentId;
        }

        $maxLevels = (int)osc_num_category_levels();
        if($maxLevels <= 0) {
          $maxLevels = 4;
        }

        $allCats = $this->categoryManager->toTreeAll();
        $disabledIds = $this->collectMaxDepthCategoryIds($allCats, $maxLevels);

        $this->_exportVariableToView('category', $category);
        $this->_exportVariableToView('categories_tree', $allCats);
        $this->_exportVariableToView('disabled_parent_ids', $disabledIds);
        $this->_exportVariableToView('has_subcategories', false);
        $this->_exportVariableToView('locales', OSCLocale::newInstance()->listAllEnabled());
        $this->doView('categories/frm.php');
        break;

      case('add_post'):
        osc_csrf_check();

        $parentParam = Params::getParam('fk_i_parent_id');
        $newParentId = ($parentParam === '' || $parentParam === null ? 0 : (int)$parentParam);
        if(!$this->canSetParentForNewCategory($newParentId)) {
          osc_add_flash_error_message(__('Invalid parent category'), 'admin');
          $this->redirectTo($this->categoriesAdminListUrl(array('action' => 'add')));
        }

        $fields = array();
        $fields['s_icon'] = Params::getParam('s_icon');
        $fields['s_color'] = Params::getParam('s_color');
        $fields['i_expiration_days'] = (Params::getParam('i_expiration_days') != '') ? (int)Params::getParam('i_expiration_days') : 0;
        $fields['b_price_enabled'] = ((int)Params::getParam('b_price_enabled') === 1 ? 1 : 0);
        $fields['fk_i_parent_id'] = ($newParentId > 0 ? $newParentId : null);
        $fields['i_position'] = 1;
        $fields['b_enabled'] = 1;

        $error = 0;
        $has_one_title = 0;
        $aFieldsDescription = array();
        $postParams = Params::getParamsAsArray();
        foreach($postParams as $k => $v) {
          if(preg_match('|(.+?)#(.+)|', $k, $m)) {
            if($m[2] == 's_name') {
              if($v != "") {
                $has_one_title = 1;
                $aFieldsDescription[$m[1]][$m[2]] = $v;
              } else {
                $aFieldsDescription[$m[1]][$m[2]] = NULL;
                $error = 1;
              }
            } else {
              $aFieldsDescription[$m[1]][$m[2]] = $v;
            }
          }
        }

        if($has_one_title != 1) {
          osc_add_flash_error_message(__('Sorry, including at least a title is mandatory'), 'admin');
          $this->redirectTo($this->categoriesAdminListUrl(array('action' => 'add')));
        }

        $aFieldsDescriptionInsert = array();
        foreach($aFieldsDescription as $locale => $fieldsDescription) {
          if(isset($fieldsDescription['s_name']) && $fieldsDescription['s_name'] != '') {
            $aFieldsDescriptionInsert[$locale] = $fieldsDescription;
          }
        }

        if(count($aFieldsDescriptionInsert) == 0) {
          osc_add_flash_error_message(__('Sorry, including at least a title is mandatory'), 'admin');
          $this->redirectTo($this->categoriesAdminListUrl(array('action' => 'add')));
        }

        $categoryId = $this->categoryManager->insert($fields, $aFieldsDescriptionInsert);
        if(!$categoryId) {
          osc_add_flash_error_message(__('An error occurred while adding'), 'admin');
          $this->redirectTo($this->categoriesAdminListUrl(array('action' => 'add')));
        }

        if($newParentId > 0) {
          $siblings = $this->categoryManager->findSubcategories($newParentId);
        } else {
          $siblings = $this->categoryManager->findRootCategories();
        }
        foreach($siblings as $cat) {
          if((int)$cat['pk_i_id'] == (int)$categoryId) {
            continue;
          }
          $this->categoryManager->updateOrder($cat['pk_i_id'], (int)$cat['i_position'] + 1);
        }
        $this->categoryManager->updateOrder($categoryId, 1);
        $this->categoryManager->normalizeOrdersByParent($newParentId);

        osc_run_hook('add_category', (int)$categoryId);

        if($error == 0) {
          osc_add_flash_ok_message(__('Category added correctly'), 'admin');
        } else {
          osc_add_flash_warning_message(__('Category added correctly, but some titles are empty'), 'admin');
        }

        $redirectParent = ($newParentId > 0 ? $newParentId : (int)Params::getParam('parent'));
        $extra = array();
        if($redirectParent > 0) {
          $extra['parent'] = $redirectParent;
        }
        $this->redirectTo($this->categoriesAdminListUrl($extra));
        break;

      case('edit'):
        $id = (int)Params::getParam('id');
        if($id <= 0) {
          $this->redirectTo($this->categoriesAdminListUrl());
        }

        $category = $this->categoryManager->findByPrimaryKey($id, 'all');
        if(!$category) {
          osc_add_flash_warning_message(__('Category not found'), 'admin');
          $this->redirectTo($this->categoriesAdminListUrl());
        }

        $maxLevels = (int)osc_num_category_levels();
        if($maxLevels <= 0) {
          $maxLevels = 4;
        }

        $disabledIds = array($id);
        $disabledIds = array_merge($disabledIds, $this->categoryManager->getDescendantIds($id));
        $allCats = $this->categoryManager->toTreeAll();
        $flatDisabled = $this->collectMaxDepthCategoryIds($allCats, $maxLevels);
        $disabledIds = array_unique(array_merge($disabledIds, $flatDisabled));

        $hasSubcats = (count($this->categoryManager->findSubcategories($id)) > 0);

        $this->_exportVariableToView('category', $category);
        $this->_exportVariableToView('categories_tree', $allCats);
        $this->_exportVariableToView('disabled_parent_ids', $disabledIds);
        $this->_exportVariableToView('has_subcategories', $hasSubcats);
        $this->_exportVariableToView('locales', OSCLocale::newInstance()->listAllEnabled());
        $this->doView('categories/frm.php');
        break;

      case('edit_post'):
        osc_csrf_check();
        $id = (int)Params::getParam('id');
        if($id <= 0) {
          $this->redirectTo($this->categoriesAdminListUrl());
        }

        $category = $this->categoryManager->findByPrimaryKey($id);
        if(!$category) {
          osc_add_flash_warning_message(__('Category not found'), 'admin');
          $this->redirectTo($this->categoriesAdminListUrl());
        }

        $parentParam = Params::getParam('fk_i_parent_id');
        $newParentId = ($parentParam === '' || $parentParam === null ? 0 : (int)$parentParam);
        if(!$this->categoryManager->canSetParent($id, $newParentId)) {
          osc_add_flash_error_message(__('Invalid parent category'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=categories&action=edit&id=' . $id);
        }

        $fields = array();
        $fields['s_icon'] = Params::getParam('s_icon');
        $fields['s_color'] = Params::getParam('s_color');
        $fields['i_expiration_days'] = (Params::getParam('i_expiration_days') != '') ? (int)Params::getParam('i_expiration_days') : 0;
        $fields['b_price_enabled'] = ((int)Params::getParam('b_price_enabled') === 1 ? 1 : 0);
        $fields['fk_i_parent_id'] = ($newParentId > 0 ? $newParentId : null);

        $apply_changes_to_subcategories = (Params::getParam('apply_changes_to_subcategories') == 1);

        $error = 0;
        $has_one_title = 0;
        $aFieldsDescription = array();
        $postParams = Params::getParamsAsArray();
        foreach($postParams as $k => $v) {
          if(preg_match('|(.+?)#(.+)|', $k, $m)) {
            if($m[2] == 's_name') {
              if($v != "") {
                $has_one_title = 1;
                $aFieldsDescription[$m[1]][$m[2]] = $v;
              } else {
                $aFieldsDescription[$m[1]][$m[2]] = NULL;
                $error = 1;
              }
            } else {
              $aFieldsDescription[$m[1]][$m[2]] = $v;
            }
          }
        }

        if($error == 0 || ($error == 1 && $has_one_title == 1)) {
          $res = $this->categoryManager->updateByPrimaryKey(array('fields' => $fields, 'aFieldsDescription' => $aFieldsDescription), $id);
          $this->categoryManager->updateExpiration($id, $fields['i_expiration_days'], $apply_changes_to_subcategories);
          $this->categoryManager->updatePriceEnabled($id, $fields['b_price_enabled'], $apply_changes_to_subcategories);
          if(is_bool($res)) {
            $error = 2;
          }
        }

        osc_run_hook('edited_category', (int)($id), $error);

        if($error == 0) {
          osc_add_flash_ok_message(__('Category updated correctly'), 'admin');
        } else if($error == 1 && $has_one_title == 1) {
          osc_add_flash_warning_message(__('Category updated correctly, but some titles are empty'), 'admin');
        } else if($error == 2) {
          osc_add_flash_error_message(__('An error occurred while updating'), 'admin');
        } else {
          osc_add_flash_error_message(__('Sorry, including at least a title is mandatory'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=categories&action=edit&id=' . $id);
        }

        $redirectParent = ($newParentId > 0 ? $newParentId : (int)Params::getParam('parent'));
        $extra = array();
        if($redirectParent > 0) {
          $extra['parent'] = $redirectParent;
        }
        $this->redirectTo($this->categoriesAdminListUrl($extra));
        break;

      case('enable'):
        osc_csrf_check();
        $id = (int)Params::getParam('id');
        $res = $this->setCategoryEnabled($id, 1);
        if($res['ok']) {
          osc_add_flash_ok_message($res['msg'], 'admin');
        } else {
          osc_add_flash_error_message($res['msg'], 'admin');
        }
        $this->redirectTo($this->categoriesAdminListUrl());
        break;

      case('disable'):
        osc_csrf_check();
        $id = (int)Params::getParam('id');
        $res = $this->setCategoryEnabled($id, 0);
        if($res['ok']) {
          osc_add_flash_ok_message($res['msg'], 'admin');
        } else {
          osc_add_flash_error_message($res['msg'], 'admin');
        }
        $this->redirectTo($this->categoriesAdminListUrl());
        break;

      case('delete'):
        osc_csrf_check();
        $id = (int)Params::getParam('id');
        $res = $this->categoryManager->deleteByPrimaryKey($id);
        if($res > 0) {
          osc_add_flash_ok_message(__('The categories have been deleted'), 'admin');
        } else {
          osc_add_flash_error_message(__('An error occurred while deleting'), 'admin');
        }
        $this->redirectTo($this->categoriesAdminListUrl());
        break;

      case('enable_price'):
        osc_csrf_check();
        $id = (int)Params::getParam('id');
        $this->categoryManager->updatePriceEnabled($id, 1, false);
        osc_add_flash_ok_message(__('Price field enabled'), 'admin');
        $this->redirectTo($this->categoriesAdminListUrl());
        break;

      case('disable_price'):
        osc_csrf_check();
        $id = (int)Params::getParam('id');
        $this->categoryManager->updatePriceEnabled($id, 0, false);
        osc_add_flash_ok_message(__('Price field disabled'), 'admin');
        $this->redirectTo($this->categoriesAdminListUrl());
        break;

      case('reorder'):
        $this->_exportVariableToView("categories", $this->categoryManager->toTreeAll());
        $this->doView("categories/reorder.php");
        break;

      default:
        if(Params::getParam('action') != '') {
          osc_run_hook('category_bulk_' . Params::getParam('action'), Params::getParam('id'));
        }

        $bulkAction = Params::getParam('action');
        if($bulkAction != '' && is_array(Params::getParam('id'))) {
          $this->processBulkAction($bulkAction, Params::getParam('id'));
        }

        require_once osc_lib_path() . 'osclass/classes/datatables/CategoriesDataTable.php';

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

        $parent = (int)Params::getParam('parent');
        $breadcrumb = array();
        if($parent > 0) {
          $breadcrumb = $this->categoryManager->toRootTree($parent);
        }

        $params = Params::getParamsAsArray();
        $categoriesDataTable = new CategoriesDataTable();
        $categoriesDataTable->table($params);
        $aData = $categoriesDataTable->getData();

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
          array('value' => 'enable', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected categories?'), strtolower(__('Enable'))), 'label' => __('Enable')),
          array('value' => 'disable', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected categories?'), strtolower(__('Disable'))), 'label' => __('Disable')),
          array('value' => 'delete', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected categories?'), strtolower(__('Delete'))), 'label' => __('Delete')),
          array('value' => 'expiration_30', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected categories?'), strtolower(__('Set expiration to 30 days'))), 'label' => __('Expiration 30 days')),
          array('value' => 'expiration_90', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected categories?'), strtolower(__('Set expiration to 90 days'))), 'label' => __('Expiration 90 days')),
          array('value' => 'disable_expiration', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected categories?'), strtolower(__('Disable expiration'))), 'label' => __('Disable expiration')),
          array('value' => 'enable_price', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected categories?'), strtolower(__('Enable price'))), 'label' => __('Enable price')),
          array('value' => 'disable_price', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected categories?'), strtolower(__('Disable price'))), 'label' => __('Disable price')),
        );
        $bulk_options = osc_apply_filter('category_bulk_filter', $bulk_options);

        $this->_exportVariableToView('aData', $aData);
        $this->_exportVariableToView('aRawRows', $categoriesDataTable->rawRows());
        $this->_exportVariableToView('withFilters', $categoriesDataTable->withFilters());
        $this->_exportVariableToView('bulk_options', $bulk_options);
        $this->_exportVariableToView('parent', $parent);
        $this->_exportVariableToView('breadcrumb', $breadcrumb);
        $this->_exportVariableToView('list_url', $this->categoriesAdminListUrl());

        $this->doView('categories/index.php');
        break;
    }
  }

  // Validate parent for a new category (subtree height = 1)
  private function canSetParentForNewCategory($newParentId) {
    $maxLevels = (int)osc_num_category_levels();
    if($maxLevels <= 0) {
      $maxLevels = 4;
    }

    $newParentId = ($newParentId === '' || $newParentId === null ? 0 : (int)$newParentId);
    if($newParentId <= 0) {
      return true;
    }

    if(!$this->categoryManager->findByPrimaryKey($newParentId)) {
      return false;
    }

    return ($this->categoryManager->getCategoryDepth($newParentId) < $maxLevels);
  }

  // Collect category ids at or below max depth (cannot be parent)
  private function collectMaxDepthCategoryIds($categories, $maxLevels, $depth = 1) {
    $ids = array();
    if(!is_array($categories)) {
      return $ids;
    }
    foreach($categories as $c) {
      if($depth >= $maxLevels) {
        $ids[] = (int)$c['pk_i_id'];
      }
      if(isset($c['categories']) && is_array($c['categories'])) {
        $ids = array_merge($ids, $this->collectMaxDepthCategoryIds($c['categories'], $maxLevels, $depth + 1));
      }
    }
    return $ids;
  }

  // Process datatable bulk actions
  private function processBulkAction($action, $ids) {
    if(!is_array($ids) || count($ids) == 0) {
      return;
    }

    osc_csrf_check();
    $changed = 0;

    switch($action) {
      case('enable'):
        foreach($ids as $id) {
          $res = $this->setCategoryEnabled((int)$id, 1);
          if($res['ok']) {
            $changed++;
          }
        }
        if($changed > 0) {
          osc_add_flash_ok_message(sprintf(_n('One category has been enabled', '%d categories have been enabled', $changed), $changed), 'admin');
        }
        break;

      case('disable'):
        foreach($ids as $id) {
          $res = $this->setCategoryEnabled((int)$id, 0);
          if($res['ok']) {
            $changed++;
          }
        }
        if($changed > 0) {
          osc_add_flash_ok_message(sprintf(_n('One category has been disabled', '%d categories have been disabled', $changed), $changed), 'admin');
        }
        break;

      case('delete'):
        foreach($ids as $id) {
          if($this->categoryManager->deleteByPrimaryKey((int)$id) > 0) {
            $changed++;
          }
        }
        if($changed > 0) {
          osc_add_flash_ok_message(sprintf(_n('One category has been deleted', '%d categories have been deleted', $changed), $changed), 'admin');
        }
        break;

      case('expiration_30'):
        foreach($ids as $id) {
          $this->categoryManager->update(array('i_expiration_days' => 30), array('pk_i_id' => (int)$id));
          $this->categoryManager->updateExpiration((int)$id, 30, false);
          $changed++;
        }
        if($changed > 0) {
          osc_add_flash_ok_message(__('Expiration updated'), 'admin');
        }
        break;

      case('expiration_90'):
        foreach($ids as $id) {
          $this->categoryManager->update(array('i_expiration_days' => 90), array('pk_i_id' => (int)$id));
          $this->categoryManager->updateExpiration((int)$id, 90, false);
          $changed++;
        }
        if($changed > 0) {
          osc_add_flash_ok_message(__('Expiration updated'), 'admin');
        }
        break;

      case('disable_expiration'):
      case('expiration_default'):
        foreach($ids as $id) {
          $this->categoryManager->update(array('i_expiration_days' => 0), array('pk_i_id' => (int)$id));
          $this->categoryManager->updateExpiration((int)$id, 0, false);
          $changed++;
        }
        if($changed > 0) {
          osc_add_flash_ok_message(__('Expiration updated'), 'admin');
        }
        break;

      case('enable_price'):
        foreach($ids as $id) {
          $this->categoryManager->updatePriceEnabled((int)$id, 1, false);
          $changed++;
        }
        if($changed > 0) {
          osc_add_flash_ok_message(__('Price field enabled'), 'admin');
        }
        break;

      case('disable_price'):
        foreach($ids as $id) {
          $this->categoryManager->updatePriceEnabled((int)$id, 0, false);
          $changed++;
        }
        if($changed > 0) {
          osc_add_flash_ok_message(__('Price field disabled'), 'admin');
        }
        break;

      default:
        return;
    }

    $this->redirectTo($this->categoriesAdminListUrl());
  }

  function doView($file) {
    osc_run_hook("before_admin_html");
    osc_current_admin_theme_path($file);
    Session::newInstance()->_clearVariables();
    osc_run_hook("after_admin_html");
  }
}

/* file end: ./oc-admin/categories.php */
