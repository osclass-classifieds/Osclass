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
  //specific for this class
  private $categoryManager;

  function __construct() {
    parent::__construct();

    //specific things for this class
    $this->categoryManager = Category::newInstance( osc_current_admin_locale() );
  }

  //Business Layer...
  function doModel() {
    parent::doModel();

    //specific things for this class
    switch ($this->action) {
      case('add_post_default'): // add default category and reorder parent categories
        osc_csrf_check();
        $fields['fk_i_parent_id'] = NULL;
        $fields['i_expiration_days'] = 0;
        $fields['i_position'] = 0;
        $fields['b_enabled'] = 1;
        $fields['b_price_enabled'] = 1;

        $default_locale = osc_language();
        $aFieldsDescription[$default_locale]['s_name'] = "NEW CATEGORY, EDIT ME!";

        $categoryId = $this->categoryManager->insert($fields, $aFieldsDescription);

        // reorder parent categories. NEW category first
        $rootCategories = $this->categoryManager->findRootCategories();
        foreach($rootCategories as $cat){
          $order = $cat['i_position'];
          $order++;
          $this->categoryManager->updateOrder($cat['pk_i_id'],$order);
        }
        $this->categoryManager->updateOrder($categoryId,'0');

        osc_run_hook('add_category', (int)($categoryId));

        $this->redirectTo(osc_admin_base_url(true).'?page=categories');
        break;

      default:        //
        $this->_exportVariableToView("categories", $this->categoryManager->toTreeAll() );
        $this->doView("categories/index.php");

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

/* file end: ./oc-admin/categories.php */