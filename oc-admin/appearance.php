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


class CAdminAppearance extends AdminSecBaseModel {
  function __construct() {
    parent::__construct();
  }

  //Business Layer...
  function doModel() {
    parent::doModel();
    //specific things for this class
    switch($this->action) {
      case('add'):
        $this->doView("appearance/add.php");
        break;

      case('add_post'):
        if(defined('DEMO') ) {
          osc_add_flash_warning_message( _m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=appearance');
        }

        osc_csrf_check();

        $filePackage = Params::getFiles('package');
        if(isset($filePackage['size']) && $filePackage['size']!=0) {
          $path = osc_themes_path();
          (int) $status = osc_unzip_file($filePackage['tmp_name'], $path);
          @unlink($filePackage['tmp_name']);
        } else {
          $status = 3;
        }

        osc_run_hook('admin_before_theme_add', $filePackage, $status);

        switch($status) {
          case(0):
            $msg = _m('The theme folder is not writable');
            osc_add_flash_error_message($msg, 'admin');
            break;

          case(1):
            $msg = _m('The theme has been installed correctly');
            osc_add_flash_ok_message($msg, 'admin');
            break;

          case(2):
            $msg = _m('The zip file is not valid');
            osc_add_flash_error_message($msg, 'admin');
            break;

          case(3):
            $msg = _m('No file was uploaded');
            osc_add_flash_error_message($msg, 'admin');
            $this->redirectTo(osc_admin_base_url(true)."?page=appearance&action=add");
            break;

          case(-1):
          default:
            $msg = _m('There was a problem adding the theme');
            osc_add_flash_error_message($msg, 'admin');
            break;

        }

        $this->redirectTo( osc_admin_base_url(true) . "?page=appearance" );
        break;

      case('delete'):
        if(defined('DEMO') ) {
          osc_add_flash_warning_message( _m("This action can't be done because it's a demo site"), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=appearance');
        }

        osc_csrf_check();

        $theme = Params::getParam('webtheme');

        osc_run_hook('admin_before_theme_delete', $theme);

        if($theme!='') {
          if($theme!=  osc_current_web_theme()) {
            if(file_exists(osc_content_path() . "themes/" . $theme . "/functions.php")) {
              include osc_content_path() . "themes/" . $theme . "/functions.php";
            }
            osc_run_hook("theme_delete_".$theme);
            if(osc_deleteDir(osc_content_path()."themes/".$theme."/")) {
              osc_add_flash_ok_message(_m("Theme removed successfully"), "admin");
            } else {
              osc_add_flash_error_message(_m("There was a problem removing the theme"), "admin");
            }
          } else {
            osc_add_flash_error_message(_m("Current theme can not be deleted"), "admin");
          }
        } else {
          osc_add_flash_error_message(_m("No theme selected"), "admin");
        }

        $this->redirectTo( osc_admin_base_url(true) . "?page=appearance" );
        break;

      /* widgets */
      case('widgets'):
        require_once osc_lib_path() . 'osclass/classes/datatables/WidgetsDataTable.php';

        if(Params::getParam('iDisplayLength') != '') {
          Cookie::newInstance()->push('listing_iDisplayLength', Params::getParam('iDisplayLength'));
          Cookie::newInstance()->set();
        } else {
          $listing_iDisplayLength = (int)Cookie::newInstance()->get_value('listing_iDisplayLength');
          if($listing_iDisplayLength == 0) $listing_iDisplayLength = 25;
          Params::setParam('iDisplayLength', $listing_iDisplayLength);
        }
        $this->_exportVariableToView('iDisplayLength', Params::getParam('iDisplayLength'));

        if(Params::getParam('sort') == '') {
          Params::setParam('sort', 'section');
        }
        if(Params::getParam('direction') == '') {
          Params::setParam('direction', 'asc');
        }

        $page = (int)Params::getParam('iPage');
        if($page == 0) { $page = 1; }
        Params::setParam('iPage', $page);

        $widgetsDataTable = new WidgetsDataTable();
        $widgetsDataTable->table(Params::getParamsAsArray());
        $aData = $widgetsDataTable->getData();

        if(count($aData['aRows']) == 0 && $page != 1) {
          $total = (int)$aData['iTotalDisplayRecords'];
          $maxPage = ceil($total / (int)$aData['iDisplayLength']);
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
        $this->_exportVariableToView('aRawRows', $widgetsDataTable->rawRows());

        $bulk_options = array(
          array('value' => '', 'data-dialog-content' => '', 'label' => __('Bulk actions')),
          array('value' => 'delete_widget', 'data-dialog-content' => sprintf(__('Are you sure you want to %s the selected widgets?'), strtolower(__('Delete'))), 'label' => __('Delete'))
        );
        $bulk_options = osc_apply_filter('widget_bulk_filter', $bulk_options);
        $this->_exportVariableToView('bulk_options', $bulk_options);
        $this->exportWidgetFormVars();

        $this->doView('appearance/widgets.php');
        break;

      case('widget_settings'):
        $this->exportWidgetFormVars();
        $this->doView('appearance/widget_settings.php');
        break;

      case('widgets_settings'):
        osc_csrf_check();
        $parts = preg_split('/[\s,]+/', (string)Params::getParam('widget_custom_sections'), -1, PREG_SPLIT_NO_EMPTY);
        $slugs = array();
        foreach($parts as $part) {
          $slug = osc_widget_sanitize_slug($part);
          if($slug != '') {
            $slugs[] = $slug;
          }
        }
        $slugs = array_values(array_unique($slugs));
        osc_set_preference('widget_custom_sections', implode(',', $slugs));
        osc_set_preference('widget_locale_strict', (Params::getParam('widget_locale_strict') != '' ? '1' : '0'));
        osc_add_flash_ok_message(_m('Widget settings have been updated'), 'admin');
        $this->redirectTo(osc_admin_base_url(true) . '?page=appearance&action=widget_settings');
        break;

      case('add_widget'):
        $this->exportWidgetFormVars();
        $this->_exportVariableToView('widget', array());
        $this->doView('appearance/add_widget.php');
        break;

      case('edit_widget'):
        $widget = Widget::newInstance()->findByPrimaryKey(Params::getParam('id'));
        if(empty($widget)) {
          osc_add_flash_error_message(_m('Widget cannot be found'), 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=appearance&action=widgets');
        }

        $this->exportWidgetFormVars();
        $this->_exportVariableToView('widget', $widget);
        $this->doView('appearance/add_widget.php');
        break;

      case('delete_widget'):
        osc_csrf_check();
        $id = Params::getParam('id');
        if(!is_array($id)) {
          $id = array($id);
        }

        $deleted = Widget::newInstance()->deleteByPrimaryKey($id);
        foreach($id as $_id) {
          osc_run_hook('delete_widget', $_id);
        }

        if($deleted > 1) {
          osc_add_flash_ok_message(sprintf(_m('%s widgets have been deleted correctly'), $deleted), 'admin');
        } else if($deleted == 1) {
          osc_add_flash_ok_message(_m('Widget removed correctly'), 'admin');
        } else {
          osc_add_flash_error_message(_m('Widget cannot be deleted'), 'admin');
        }
        $this->redirectTo(osc_admin_base_url(true) . '?page=appearance&action=widgets');
        break;

      case('edit_widget_post'):
        osc_csrf_check();
        $this->saveWidget(true);
        break;

      case('add_widget_post'):
        osc_csrf_check();
        $this->saveWidget(false);
        break;

      /* /widget */
      case('activate'):
        osc_csrf_check();
        osc_set_preference('theme', Params::getParam('theme'));
        osc_add_flash_ok_message( _m('Theme activated correctly'), 'admin');
        osc_run_hook("theme_activate", Params::getParam('theme'));
        $this->redirectTo( osc_admin_base_url(true) . "?page=appearance" );
        break;

      case('render'):
        if(Params::existParam('route')) {
          $routes = Rewrite::newInstance()->getRoutes();
          $rid = Params::getParam('route');
          $file = '../';
          if(isset($routes[$rid]) && isset($routes[$rid]['file'])) {
            $file = $routes[$rid]['file'];
          }
        } else {
          // DEPRECATED: Disclosed path in URL is deprecated, use routes instead
          // This will be REMOVED in 3.6
          $file = Params::getParam('file');
          // We pass the GET variables (in case we have somes)
          if(preg_match('|(.+?)\?(.*)|', $file, $match)) {
            $file = $match[1];
            if(preg_match_all('|&([^=]+)=([^&]*)|', urldecode('&'.$match[2].'&'), $get_vars)) {
              for($var_k=0;$var_k<count($get_vars[1]);$var_k++) {
                Params::setParam($get_vars[1][$var_k], $get_vars[2][$var_k]);
              }
            }
          } else {
            $file = Params::getParam('file');
          };
        }

        if(strpos($file, '../')!==false || strpos($file, '..\\')!==false || !file_exists(osc_base_path() . $file)) {
          osc_add_flash_warning_message(__('Error loading theme custom file'), 'admin');
        };
        $this->_exportVariableToView('file', osc_base_path() . $file);
        $this->doView('appearance/view.php');
        break;

      case('customization'):
        $this->doView('appearance/customization.php');
        break;

      case('customization_update'):
        osc_csrf_check();
        $error = '';
        $iUpdated = 0;
        $sCustomCss = Params::getParam('customCss', false, false);
        $sCustomCssHook = Params::getParam('customCssHook');
        $sCustomHtml = Params::getParam('customHtml', false, false);
        $sCustomHtmlHook = Params::getParam('customHtmlHook');
        $sCustomJs = Params::getParam('customJs', false, false);
        $sCustomJsHook = Params::getParam('customJsHook');

        $iUpdated += osc_set_preference('custom_css', $sCustomCss);
        $iUpdated += osc_set_preference('custom_css_hook', $sCustomCssHook);
        $iUpdated += osc_set_preference('custom_js', $sCustomJs);
        $iUpdated += osc_set_preference('custom_js_hook', $sCustomJsHook);
        $iUpdated += osc_set_preference('custom_html', $sCustomHtml);
        $iUpdated += osc_set_preference('custom_html_hook', $sCustomHtmlHook);


        if($iUpdated > 0 ) {
          if($error != '' ) {
            osc_add_flash_error_message( $error . "</p><p>" . _m('Customization settings have been updated'), 'admin');
          } else {
            osc_add_flash_ok_message( _m('Customization settings have been updated'), 'admin');
          }
        } else if($error != '') {
          osc_add_flash_error_message( $error , 'admin');
        }

        $this->redirectTo(osc_admin_base_url(true) . '?page=appearance&action=customization');
        break;

      default:
        if(Params::getParam('marketError') > 0) {
          osc_add_flash_warning_message(sprintf(__('There was problem with update: [%s] %s. You may download update manually at: %s'), Params::getParam('marketError'), Params::getParam('message'), Params::getParam('slug')), 'admin');
        }

        if(Params::getParam('checkUpdated') != '') {
          osc_admin_toolbar_update_themes(true);
        }

        $themes = WebThemes::newInstance()->getListThemes();

        //preparing variables for the view
        $this->_exportVariableToView("themes", $themes);

        $this->doView('appearance/index.php');
        break;

    }
  }

  // Export theme/custom section lists for widget views
  private function exportWidgetFormVars() {
    $info = WebThemes::newInstance()->loadThemeInfo(osc_theme());
    $this->_exportVariableToView('info', $info);
    $this->_exportVariableToView('theme_sections', osc_widget_theme_sections());
    $this->_exportVariableToView('custom_sections', osc_widget_custom_sections());
    $this->_exportVariableToView('widget_sections', osc_widget_sections());
  }

  // Append a non-theme section to the custom sections preference
  private function appendCustomSection($location) {
    $location = osc_widget_sanitize_slug($location);
    if($location == '') {
      return;
    }

    if(in_array($location, osc_widget_theme_sections(), true)) {
      return;
    }

    $custom = osc_widget_custom_sections();
    if(in_array($location, $custom, true)) {
      return;
    }

    $custom[] = $location;
    osc_set_preference('widget_custom_sections', implode(',', $custom));
  }

  // Save locale content rows; single-locale keeps only the admin locale
  private function saveWidgetLocales($id, $locales, $single) {
    $adminLocale = osc_current_admin_locale();
    if($single) {
      $content = '';
      if(isset($locales[$adminLocale])) {
        $content = $locales[$adminLocale];
      } else if(count($locales) > 0) {
        $content = reset($locales);
      }
      Widget::newInstance()->updateDescription($id, $adminLocale, $content);
      Widget::newInstance()->deleteOtherDescriptions($id, $adminLocale);
      return;
    }

    foreach($locales as $code => $content) {
      Widget::newInstance()->updateDescription($id, $code, $content);
    }
  }

  // Create or update a widget from POST
  private function saveWidget($edit) {
    $id = (int)Params::getParam('id');
    $description = trim((string)Params::getParam('description'));
    if(strlen($description) > 40) {
      $description = substr($description, 0, 40);
    }

    $location = osc_widget_sanitize_slug(Params::getParam('location'));
    $newSection = osc_widget_sanitize_slug(Params::getParam('new_section'));
    if($newSection != '') {
      $location = $newSection;
    }

    $internal = osc_widget_sanitize_slug(Params::getParam('s_internal_name'));
    if($internal == '') {
      $internal = osc_widget_sanitize_slug($description);
    }

    $device = Params::getParam('s_device_visibility');
    if($device != 'mobile' && $device != 'desktop') {
      $device = 'all';
    }

    $single = (Params::getParam('b_single_locale') != '' ? 1 : 0);
    $code = Params::getParam('s_code', false, false);
    $css = Params::getParam('s_css', false, false);

    $locales = array();
    $postParams = Params::getParamsAsArray('', false);
    foreach($postParams as $k => $v) {
      if(preg_match('|(.+?)#(.+)|', $k, $m)) {
        if($m[2] == 's_content') {
          $locales[$m[1]] = $v;
        }
      }
    }

    $backAdd = osc_admin_base_url(true) . '?page=appearance&action=add_widget';
    $backEdit = osc_admin_base_url(true) . '?page=appearance&action=edit_widget&id=' . $id;
    $back = ($edit ? $backEdit : $backAdd);

    if($location == '') {
      osc_add_flash_error_message(_m('Section field is required'), 'admin');
      $this->redirectTo($back);
    }

    if($description == '' || !osc_validate_text($description)) {
      osc_add_flash_error_message(_m('Description field is required'), 'admin');
      $this->redirectTo($back);
    }

    $this->appendCustomSection($location);

    $manager = Widget::newInstance();
    $fields = array(
      's_description' => $description,
      's_location' => $location,
      'e_kind' => 'html',
      's_code' => $code,
      's_device_visibility' => $device,
      's_css' => $css,
      'b_single_locale' => $single
    );

    if($edit) {
      $existing = $manager->findByPrimaryKey($id);
      if(empty($existing)) {
        osc_add_flash_error_message(_m('Widget cannot be found'), 'admin');
        $this->redirectTo(osc_admin_base_url(true) . '?page=appearance&action=widgets');
      }

      if($internal != '' && $manager->internalNameExists($id, $internal)) {
        osc_add_flash_error_message(_m('Internal name is already in use'), 'admin');
        $this->redirectTo($back);
      }

      $fields['s_internal_name'] = ($internal != '' ? $internal : 'widget-' . $id);
      if($existing['s_location'] != $location) {
        $fields['i_order'] = $manager->nextOrder($location);
      }

      $res = $manager->update($fields, array('pk_i_id' => $id));
      $this->saveWidgetLocales($id, $locales, $single);
      osc_run_hook('edit_widget', $id);

      if($res !== false) {
        osc_add_flash_ok_message(_m('Widget updated correctly'), 'admin');
      } else {
        osc_add_flash_error_message(_m('Widget cannot be updated correctly'), 'admin');
      }
      $this->redirectTo(osc_admin_base_url(true) . '?page=appearance&action=widgets');
    }

    if($internal != '' && $manager->internalNameExists(0, $internal)) {
      osc_add_flash_error_message(_m('Internal name is already in use'), 'admin');
      $this->redirectTo($back);
    }

    if($internal != '') {
      $fields['s_internal_name'] = $internal;
    } else {
      $fields['s_internal_name'] = 'w' . substr(md5(uniqid('', true)), 0, 12);
    }
    $fields['i_order'] = $manager->nextOrder($location);

    $id = $manager->insert($fields);
    if(!$id) {
      osc_add_flash_error_message(_m('Widget cannot be added'), 'admin');
      $this->redirectTo($back);
    }

    if($internal == '') {
      $manager->update(array('s_internal_name' => 'widget-' . $id), array('pk_i_id' => $id));
    }

    $this->saveWidgetLocales($id, $locales, $single);
    osc_run_hook('add_widget', $id);
    osc_add_flash_ok_message(_m('Widget added correctly'), 'admin');
    $this->redirectTo(osc_admin_base_url(true) . '?page=appearance&action=widgets');
  }

  //hopefully generic...
  function doView($file) {
    osc_run_hook("before_admin_html");
    osc_current_admin_theme_path($file);
    Session::newInstance()->_clearVariables();
    osc_run_hook("after_admin_html");
  }
}

/* file end: ./oc-admin/appearance.php */
