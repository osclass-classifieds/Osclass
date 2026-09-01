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


class CAdminSettingsOptimization extends AdminSecBaseModel
{
  //Business Layer...
  function doModel()
  {
    switch($this->action) {
      case('optimization'):
        if (!is_dir(osc_uploads_path() . 'minify/')) {   // optimization dir
          if(!mkdir(osc_uploads_path() . 'minify/')) {
            osc_add_flash_error_message(sprintf('Optimization directory "%s" was not created, create it in order to use optimization services.', osc_uploads_path() . 'minify/'), 'admin');
          }
        }
        
        if(osc_css_merge() || osc_js_merge()) {
          osc_add_flash_warning_message(__('You have enabled optimization of CSS or JS, make sure everything works properly on your site before using it on live website. Optimized files are auto-refreshed once something has changed.'), 'admin'); 
        }
        
        //calling the comments settings view
        $this->doView('settings/optimization.php');
        break;
        
      case('optimization_post'):
        // updating comment
        osc_csrf_check();
        $iUpdated       = 0;
        $cssMerge       = Params::getParam('css_merge');
        $cssMerge       = (($cssMerge != '') ? true : false);
        $cssMinify      = Params::getParam('css_minify');
        $cssMinify      = (($cssMinify != '') ? true : false);
        $cssBannedWords = trim(strtolower(Params::getParam('css_banned_words')));
        $cssBannedPages = trim(strtolower(Params::getParam('css_banned_pages')));
        $jsMerge        = Params::getParam('js_merge');
        $jsMerge        = (($jsMerge != '') ? true : false);
        $jsMinify       = Params::getParam('js_minify');
        $jsMinify       = (($jsMinify != '') ? true : false);
        $jsBannedWords  = trim(strtolower(Params::getParam('js_banned_words')));
        $jsBannedPages  = trim(strtolower(Params::getParam('js_banned_pages')));

        $iUpdated += osc_set_preference('css_merge', $cssMerge);
        $iUpdated += osc_set_preference('css_minify', $cssMinify);
        $iUpdated += osc_set_preference('css_banned_words', $cssBannedWords);
        $iUpdated += osc_set_preference('css_banned_pages', $cssBannedPages);
        $iUpdated += osc_set_preference('js_merge', $jsMerge);
        $iUpdated += osc_set_preference('js_minify', $jsMinify);
        $iUpdated += osc_set_preference('js_banned_words', $jsBannedWords);
        $iUpdated += osc_set_preference('js_banned_pages', $jsBannedPages);

        if($iUpdated > 0) {
          osc_add_flash_ok_message( _m("Optimization settings have been updated"), 'admin');
        }
        
        $this->redirectTo(osc_admin_base_url(true) . '?page=settings&action=optimization');
        break;
        
      case('optimization_clean'):
        osc_clean_optimization_files();
        
        osc_add_flash_ok_message(_m("Optimized CSS & JS files has been removed"), 'admin');
        $this->redirectTo(osc_admin_base_url(true) . '?page=settings&action=optimization');
      break;
    }
  }
}

// EOF: ./oc-admin/controller/settings/comments.php