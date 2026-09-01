<?php
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


define('SIGMA_THEME_VERSION', '133');


function sigma_home_latest() {
  if(osc_is_home_page()) {
    osc_reset_latest_items();
    
    if(osc_count_latest_items() > 0) { 
      ?>
      <div class="home-latest">
        <h2><?php _e('Latest Listings', 'sigma') ; ?></h2>
        <?php
          View::newInstance()->_exportVariableToView("listType", 'latestItems');
          View::newInstance()->_exportVariableToView("listClass", 'listing-grid');
          osc_current_web_theme_path('loop.php');
        ?>
      </div>
      <?php 
    } 
  }
}

osc_add_hook('before-main', 'sigma_home_latest');


if( (string)osc_get_preference('keyword_placeholder', 'sigma')=="" ) {
  Params::setParam('keyword_placeholder', __('ie. PHP Programmer', 'sigma') ) ;
}

function sigma_remove_styles() {
  osc_remove_style('font-open-sans');
  osc_remove_style('open-sans');
  osc_remove_style('fi_font-awesome');
  osc_remove_style('font-awesome44');
  osc_remove_style('font-awesome45');
  osc_remove_style('font-awesome47');
  osc_remove_style('font-awesome');
}

osc_add_hook('init', 'sigma_remove_styles');
osc_add_hook('header', 'sigma_remove_styles');

//osc_register_script('fancybox', osc_current_web_theme_url('js/fancybox/jquery.fancybox.pack.js'), array('jquery'));
//osc_enqueue_style('fancybox', osc_current_web_theme_url('js/fancybox/jquery.fancybox.css'));
//osc_enqueue_script('fancybox');
osc_enqueue_script('fancybox');


osc_enqueue_style('font-awesome-sigma', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css');
// used for date/dateinterval custom fields
osc_enqueue_script('php-date');
if(!OC_ADMIN) {
  osc_enqueue_style('fine-uploader-css', osc_assets_url('js/fineuploader/fineuploader.css'));
  if(getPreference('rtl','sigma')=='0') {
    osc_enqueue_style('sigma-fine-uploader-css', osc_current_web_theme_url('css/ajax-uploader.css'));
  } else {
    osc_enqueue_style('sigma-fine-uploader-css', osc_current_web_theme_url('css/ajax-uploader-rtl.css'));
  }
}
osc_enqueue_script('jquery-fineuploader');


/**

FUNCTIONS

*/

// install options
if( !function_exists('sigma_theme_install') ) {
  function sigma_theme_install() {
    osc_set_preference('logo', 'sigma_logo.png', 'sigma');
    osc_set_preference('keyword_placeholder', Params::getParam('keyword_placeholder'), 'sigma');
    osc_set_preference('version', SIGMA_THEME_VERSION, 'sigma');
    osc_set_preference('footer_link', '1', 'sigma');
    osc_set_preference('donation', '0', 'sigma');
    osc_set_preference('defaultShowAs@all', 'list', 'sigma');
    osc_set_preference('defaultShowAs@search', 'list');
    osc_set_preference('defaultLocationShowAs', 'dropdown', 'sigma'); // dropdown / autocomplete
    osc_set_preference('rtl', '0', 'sigma');
    osc_reset_preferences();
  }
}
// update options
if( !function_exists('sigma_theme_update') ) {
  function sigma_theme_update($current_version) {
    osc_set_preference('version', SIGMA_THEME_VERSION, 'sigma');
  }
}
if(!function_exists('check_install_sigma_theme')) {
  function check_install_sigma_theme() {
    $current_version = osc_get_preference('version', 'sigma');
    //check if current version is installed or need an update<
    if( $current_version=='' ) {
      sigma_theme_install();
    } else if($current_version < SIGMA_THEME_VERSION){
      sigma_theme_update($current_version);
    }
  }
}

// RTL LANGUAGE SUPPORT
function sigma_is_rtl() {
  $current_lang = strtolower(osc_current_user_locale());

  if(in_array($current_lang, sigma_rtl_languages())) {
    return true;
  } else {
    return false;
  }
}


function sigma_rtl_languages() {
  $langs = array('ar_DZ','ar_BH','ar_EG','ar_IQ','ar_JO','ar_KW','ar_LY','ar_MA','ar_OM','ar_SA','ar_SY','fa_IR','ar_TN','ar_AE','ar_YE','ar_TD','ar_CO','ar_DJ','ar_ER','ar_MR','ar_SD');
  return array_map('strtolower', $langs);
}


if(!function_exists('sigma_add_body_class_construct')) {
  function sigma_add_body_class_construct($classes){
    $sigmaBodyClass = sigmaBodyClass::newInstance();
    $classes = array_merge($classes, $sigmaBodyClass->get());
    return $classes;
  }
}
if(!function_exists('sigma_body_class')) {
  function sigma_body_class($echo = true){
    /**
    * Print body classes.
    *
    * @param string $echo Optional parameter.
    * @return print string with all body classes concatenated
    */
    osc_add_filter('sigma_bodyClass','sigma_add_body_class_construct');
    $classes = osc_apply_filter('sigma_bodyClass', array());
    if($echo && count($classes)){
      echo 'class="'.implode(' ',$classes).'"';
    } else {
      return $classes;
    }
  }
}
if(!function_exists('sigma_add_body_class')) {
  function sigma_add_body_class($class){
    /**
    * Add new body class to body class array.
    *
    * @param string $class required parameter.
    */
    $sigmaBodyClass = sigmaBodyClass::newInstance();
    $sigmaBodyClass->add($class);
  }
}
if(!function_exists('sigma_nofollow_construct')) {
  /**
  * Hook for header, meta tags robots nofollos
  */
  function sigma_nofollow_construct() {
    echo '<meta name="robots" content="noindex, nofollow, noarchive" />' . PHP_EOL;
    echo '<meta name="googlebot" content="noindex, nofollow, noarchive" />' . PHP_EOL;

  }
}
if( !function_exists('sigma_follow_construct') ) {
  /**
  * Hook for header, meta tags robots follow
  */
  function sigma_follow_construct() {
    echo '<meta name="robots" content="index, follow" />' . PHP_EOL;
    echo '<meta name="googlebot" content="index, follow" />' . PHP_EOL;

  }
}
/* logo */
if( !function_exists('logo_header') ) {
  function logo_header() {
     $logo = osc_get_preference('logo','sigma');
     
     $html = '<a href="'.osc_base_url().'"><img border="0" alt="' . osc_page_title() . '" src="' . sigma_logo_url() . '"></a>';
     if( $logo!='' && file_exists( osc_uploads_path() . $logo ) ) {
      return $html;
     } else {
      return '<a href="'.osc_base_url().'">'.osc_page_title().'</a>';
    }
  }
}
/* logo */
if( !function_exists('sigma_logo_url') ) {
  function sigma_logo_url() {
    $logo = osc_get_preference('logo','sigma');

    if($logo) {
      return osc_uploads_url($logo);
    }
    return false;
  }
}
if( !function_exists('sigma_draw_item') ) {
  function sigma_draw_item($class = false,$admin = false, $premium = false) {
    $filename = 'loop-single';
    if($premium){
      $filename .='-premium';
    }
    require WebThemes::newInstance()->getCurrentThemePath().$filename.'.php';
  }
}
if( !function_exists('sigma_show_as') ){
  function sigma_show_as(){

    $p_sShowAs  = Params::getParam('sShowAs');
    $aValidShowAsValues = array('list', 'gallery');
    if (!in_array($p_sShowAs, $aValidShowAsValues)) {
      $p_sShowAs = sigma_default_show_as();
    }

    return $p_sShowAs;
  }
}
if( !function_exists('sigma_default_direction') ){
  function sigma_default_direction(){
    return getPreference('rtl','sigma');
  }
}
if( !function_exists('sigma_default_show_as') ){
  function sigma_default_show_as(){
    return getPreference('defaultShowAs@all','sigma');
  }
}
if( !function_exists('sigma_default_location_show_as') ){
  function sigma_default_location_show_as(){
    return osc_get_preference('defaultLocationShowAs','sigma');
  }
}
if( !function_exists('sigma_draw_categories_list') ) {
  function sigma_draw_categories_list(){ ?>
  <?php if(!osc_is_home_page()){ echo '<div class="resp-wrapper">'; } ?>
   <?php
   //cell_3
  $total_categories   = osc_count_categories();
  $col1_max_cat     = ceil($total_categories/3);

   osc_goto_first_category();
   $i    = 0;

   while ( osc_has_categories() ) {
   ?>
  <?php
    if($i%$col1_max_cat == 0){
      if($i > 0) { echo '</div>'; }
      if($i == 0) {
         echo '<div class="cell_3 first_cel">';
      } else {
        echo '<div class="cell_3">';
      }
    }
  ?>
  <ul class="r-list">
     <li>
       <h1>
        <?php
        $_slug    = osc_category_slug();
        $_url     = osc_search_category_url();
        $_name    = osc_category_name();
        $_total_items = osc_category_total_items();
        if ( osc_count_subcategories() > 0 ) { ?>
        <span class="collapse resp-toogle"><i class="fa fa-caret-right fa-lg"></i></span>
        <?php } ?>
        <?php if($_total_items > 0) { ?>
        <a class="category <?php echo $_slug; ?>" href="<?php echo $_url; ?>"><?php echo $_name ; ?></a> <span>(<?php echo $_total_items ; ?>)</span>
        <?php } else { ?>
        <a class="category <?php echo $_slug; ?>" href="#"><?php echo $_name ; ?></a> <span>(<?php echo $_total_items ; ?>)</span>
        <?php } ?>
       </h1>
       <?php if ( osc_count_subcategories() > 0 ) { ?>
         <ul>
           <?php while ( osc_has_subcategories() ) { ?>
             <li>
             <?php if( osc_category_total_items() > 0 ) { ?>
               <a class="category sub-category <?php echo osc_category_slug() ; ?>" href="<?php echo osc_search_category_url() ; ?>"><?php echo osc_category_name() ; ?></a> <span>(<?php echo osc_category_total_items() ; ?>)</span>
             <?php } else { ?>
               <a class="category sub-category <?php echo osc_category_slug() ; ?>" href="#"><?php echo osc_category_name() ; ?></a> <span>(<?php echo osc_category_total_items() ; ?>)</span>
             <?php } ?>
             </li>
           <?php } ?>
         </ul>
       <?php } ?>
     </li>
  </ul>
  <?php
      $i++;
    }
    echo '</div>';
  ?>
  <?php if(!osc_is_home_page()){ echo '</div>'; } ?>
  <?php
  }
}
if( !function_exists('sigma_search_number') ) {
  /**
    *
    * @return array
    */
  function sigma_search_number() {
    $search_from = ((osc_search_page() * osc_default_results_per_page_at_search()) + 1);
    $search_to   = ((osc_search_page() + 1) * osc_default_results_per_page_at_search());
    if( $search_to > osc_search_total_items() ) {
      $search_to = osc_search_total_items();
    }

    return array(
      'from' => $search_from,
      'to'   => $search_to,
      'of'   => osc_search_total_items()
    );
  }
}
/*
 * Helpers used at view
 */
if( !function_exists('sigma_item_title') ) {
  function sigma_item_title() {
    $title = osc_item_title();
    foreach( osc_get_locales() as $locale ) {
      if( Session::newInstance()->_getForm('title') != "" ) {
        $title_ = Session::newInstance()->_getForm('title');
        if( @$title_[$locale['pk_c_code']] != "" ){
          $title = $title_[$locale['pk_c_code']];
        }
      }
    }
    return $title;
  }
}
if( !function_exists('sigma_item_description') ) {
  function sigma_item_description() {
    $description = osc_item_description();
    foreach( osc_get_locales() as $locale ) {
      if( Session::newInstance()->_getForm('description') != "" ) {
        $description_ = Session::newInstance()->_getForm('description');
        if( @$description_[$locale['pk_c_code']] != "" ){
          $description = $description_[$locale['pk_c_code']];
        }
      }
    }
    return $description;
  }
}
if( !function_exists('related_listings') ) {
  function related_listings() {
    View::newInstance()->_exportVariableToView('items', array());

    $mSearch = new Search();
    $mSearch->addCategory(osc_item_category_id());
    $mSearch->addRegion(osc_item_region());
    $mSearch->addItemConditions(sprintf("%st_item.pk_i_id < %s ", DB_TABLE_PREFIX, osc_item_id()));
    $mSearch->limit('0', '3');

    $aItems    = $mSearch->doSearch();
    $iTotalItems = count($aItems);
    if( $iTotalItems == 3 ) {
      View::newInstance()->_exportVariableToView('items', $aItems);
      return $iTotalItems;
    }
    unset($mSearch);

    $mSearch = new Search();
    $mSearch->addCategory(osc_item_category_id());
    $mSearch->addItemConditions(sprintf("%st_item.pk_i_id != %s ", DB_TABLE_PREFIX, osc_item_id()));
    $mSearch->limit('0', '3');

    $aItems = $mSearch->doSearch();
    $iTotalItems = count($aItems);
    if( $iTotalItems > 0 ) {
      View::newInstance()->_exportVariableToView('items', $aItems);
      return $iTotalItems;
    }
    unset($mSearch);

    return 0;
  }
}

if( !function_exists('osc_is_contact_page') ) {
  function osc_is_contact_page() {
    if( Rewrite::newInstance()->get_location() === 'contact' ) {
      return true;
    }

    return false;
  }
}

if( !function_exists('get_breadcrumb_lang') ) {
  function get_breadcrumb_lang() {
    $lang = array();
    $lang['item_add']         = __('Publish a listing', 'sigma');
    $lang['item_edit']        = __('Edit your listing', 'sigma');
    $lang['item_send_friend']     = __('Send to a friend', 'sigma');
    $lang['item_contact']       = __('Contact publisher', 'sigma');
    $lang['search']         = __('Search results', 'sigma');
    $lang['search_pattern']     = __('Search results: %s', 'sigma');
    $lang['user_dashboard']     = __('Dashboard', 'sigma');
    $lang['user_dashboard_profile'] = __("%s's profile", 'sigma');
    $lang['user_account']       = __('Account', 'sigma');
    $lang['user_items']       = __('Listings', 'sigma');
    $lang['user_alerts']      = __('Alerts', 'sigma');
    $lang['user_profile']       = __('Update account', 'sigma');
    $lang['user_change_email']    = __('Change email', 'sigma');
    $lang['user_change_username']   = __('Change username', 'sigma');
    $lang['user_change_password']   = __('Change password', 'sigma');
    $lang['login']          = __('Login', 'sigma');
    $lang['login_recover']      = __('Recover password', 'sigma');
    $lang['login_forgot']       = __('Change password', 'sigma');
    $lang['register']         = __('Create a new account', 'sigma');
    $lang['contact']        = __('Contact', 'sigma');
    return $lang;
  }
}

if(!function_exists('user_dashboard_redirect')) {
  function user_dashboard_redirect() {
    $page   = Params::getParam('page');
    $action = Params::getParam('action');
    if($page=='user' && $action=='dashboard') {
      if(ob_get_length()>0) {
        ob_end_flush();
      }
      header("Location: ".osc_user_list_items_url(), TRUE,301);
    }
  }
  osc_add_hook('init', 'user_dashboard_redirect');
}

if( !function_exists('get_user_menu') ) {
  function get_user_menu() {
    $options   = array();
    $options[] = array(
      'name' => __('Public Profile'),
       'url' => osc_user_public_profile_url(),
       'class' => 'opt_publicprofile'
    );
    $options[] = array(
      'name'  => __('Listings', 'sigma'),
      'url'   => osc_user_list_items_url(),
      'class' => 'opt_items'
    );
    $options[] = array(
      'name' => __('Alerts', 'sigma'),
      'url' => osc_user_alerts_url(),
      'class' => 'opt_alerts'
    );
    $options[] = array(
      'name'  => __('Account', 'sigma'),
      'url'   => osc_user_profile_url(),
      'class' => 'opt_account'
    );
    $options[] = array(
      'name'  => __('Change email', 'sigma'),
      'url'   => osc_change_user_email_url(),
      'class' => 'opt_change_email'
    );
    $options[] = array(
      'name'  => __('Change username', 'sigma'),
      'url'   => osc_change_user_username_url(),
      'class' => 'opt_change_username'
    );
    $options[] = array(
      'name'  => __('Change password', 'sigma'),
      'url'   => osc_change_user_password_url(),
      'class' => 'opt_change_password'
    );
    $options[] = array(
      'name'  => __('Delete account', 'sigma'),
      'url'   => '#',
      'class' => 'opt_delete_account'
    );

    return $options;
  }
}

if( !function_exists('delete_user_js') ) {
  function delete_user_js() {
    $location = Rewrite::newInstance()->get_location();
    $section  = Rewrite::newInstance()->get_section();
    if( ($location === 'user' && in_array($section, array('dashboard', 'profile', 'alerts', 'change_email', 'change_username',  'change_password', 'items'))) || (Params::getParam('page') ==='custom' && Params::getParam('in_user_menu')==true ) ) {
      osc_enqueue_script('delete-user-js');
    }
  }
  osc_add_hook('header', 'delete_user_js', 1);
}

if( !function_exists('user_info_js') ) {
  function user_info_js() {
    $location = Rewrite::newInstance()->get_location();
    $section  = Rewrite::newInstance()->get_section();

    if( $location === 'user' && in_array($section, array('dashboard', 'profile', 'alerts', 'change_email', 'change_username',  'change_password', 'items')) ) {
      $user = User::newInstance()->findByPrimaryKey( Session::newInstance()->_get('userId') );
      View::newInstance()->_exportVariableToView('user', $user);
      ?>
<script type="text/javascript">
sigma.user = {};
sigma.user.id = '<?php echo osc_user_id(); ?>';
sigma.user.secret = '<?php echo osc_user_field("s_secret"); ?>';
</script>
    <?php }
  }
  osc_add_hook('header', 'user_info_js');
}

function theme_sigma_actions_admin() {
  //if(OC_ADMIN)
  if( Params::getParam('file') == 'oc-content/themes/sigma/admin/settings.php' ) {
    if( Params::getParam('donation') == 'successful' ) {
      osc_set_preference('donation', '1', 'sigma');
      osc_reset_preferences();
    }
  }

  switch( Params::getParam('action_specific') ) {
    case('settings'):
      $footerLink  = Params::getParam('footer_link');

      osc_set_preference('keyword_placeholder', Params::getParam('keyword_placeholder'), 'sigma');
      osc_set_preference('footer_link', ($footerLink ? '1' : '0'), 'sigma');
      osc_set_preference('defaultShowAs@all', Params::getParam('defaultShowAs@all'), 'sigma');
      osc_set_preference('defaultShowAs@search', Params::getParam('defaultShowAs@all'));

      osc_set_preference('defaultLocationShowAs', Params::getParam('defaultLocationShowAs'), 'sigma');

      osc_set_preference('header-728x90',     trim(Params::getParam('header-728x90', false, false, false)),          'sigma');
      osc_set_preference('homepage-728x90',     trim(Params::getParam('homepage-728x90', false, false, false)),        'sigma');
      osc_set_preference('sidebar-300x250',     trim(Params::getParam('sidebar-300x250', false, false, false)),        'sigma');
      osc_set_preference('search-results-top-728x90',   trim(Params::getParam('search-results-top-728x90', false, false, false)),      'sigma');
      osc_set_preference('search-results-middle-728x90',  trim(Params::getParam('search-results-middle-728x90', false, false, false)),     'sigma');

      osc_set_preference('rtl', (Params::getParam('rtl') ? '1' : '0'), 'sigma');

      osc_add_flash_ok_message(__('Theme settings updated correctly', 'sigma'), 'admin');
      osc_redirect_to(osc_admin_render_theme_url('oc-content/themes/sigma/admin/settings.php'));
    break;
    case('upload_logo'):
      $package = Params::getFiles('logo');
      if( $package['error'] == UPLOAD_ERR_OK ) {
        $img = ImageProcessing::fromFile($package['tmp_name']);
        $ext = $img->getExt();
        $logo_name   = 'sigma_logo';
        $logo_name  .= '.'.$ext;
        $path = osc_uploads_path() . $logo_name ;
        $img->saveToFile($path);

        osc_set_preference('logo', $logo_name, 'sigma');

        osc_add_flash_ok_message(__('The logo image has been uploaded correctly', 'sigma'), 'admin');
      } else {
        osc_add_flash_error_message(__("An error has occurred, please try again", 'sigma'), 'admin');
      }
      osc_redirect_to(osc_admin_render_theme_url('oc-content/themes/sigma/admin/header.php'));
    break;
    case('remove'):
      $logo = osc_get_preference('logo','sigma');
      $path = osc_uploads_path() . $logo ;
      if(file_exists( $path ) ) {
        @unlink( $path );
        osc_delete_preference('logo','sigma');
        osc_reset_preferences();
        osc_add_flash_ok_message(__('The logo image has been removed', 'sigma'), 'admin');
      } else {
        osc_add_flash_error_message(__("Image not found", 'sigma'), 'admin');
      }
      osc_redirect_to(osc_admin_render_theme_url('oc-content/themes/sigma/admin/header.php'));
    break;
  }
}

function sigma_redirect_user_dashboard()
{
  if( (Rewrite::newInstance()->get_location() === 'user') && (Rewrite::newInstance()->get_section() === 'dashboard') ) {
    header('Location: ' .osc_user_list_items_url());
    exit;
  }
}

function sigma_delete() {
  Preference::newInstance()->delete(array('s_section' => 'sigma'));
}

osc_add_hook('init', 'sigma_redirect_user_dashboard', 2);
osc_add_hook('init_admin', 'theme_sigma_actions_admin');
osc_add_hook('theme_delete_sigma', 'sigma_delete');

function sigma_admin_menu_links() {
  osc_admin_menu_appearance(__('Header logo', 'sigma'), osc_admin_render_theme_url('oc-content/themes/sigma/admin/header.php'), 'header_sigma');
  osc_admin_menu_appearance(__('Theme settings', 'sigma'), osc_admin_render_theme_url('oc-content/themes/sigma/admin/settings.php'), 'settings_sigma');
}

osc_add_hook('init_admin', 'sigma_admin_menu_links');




//TRIGGER FUNCTIONS
check_install_sigma_theme();

// if(osc_is_home_page()){
//   osc_add_hook('inside-main','sigma_draw_categories_list');
// } else if( osc_is_static_page() || osc_is_contact_page() ){
//   osc_add_hook('before-content','sigma_draw_categories_list');
// }

if(osc_is_home_page() || osc_is_search_page()){
  sigma_add_body_class('has-searchbox');
}


function sigma_sidebar_category_search($catId = null)
{
  $aCategories = array();
  if($catId==null || $catId <= 0) {
    $aCategories[] = Category::newInstance()->findRootCategoriesEnabled();
  } else {
    // if parent category, only show parent categories
    $aCategories = Category::newInstance()->toRootTree($catId);
    end($aCategories);
    $cat = current($aCategories);
    // if is parent of some category
    $childCategories = Category::newInstance()->findSubcategoriesEnabled($cat['pk_i_id']);
    if(count($childCategories) > 0) {
      $aCategories[] = $childCategories;
    }
  }

  if(count($aCategories) == 0) {
    return "";
  }

  sigma_print_sidebar_category_search($aCategories, $catId);
}

function sigma_print_sidebar_category_search($aCategories, $current_category = null, $i = 0)
{
  $class = '';
  if(!isset($aCategories[$i])) {
    return null;
  }

  if($i===0) {
    $class = 'class="category"';
  }

  $c   = $aCategories[$i];
  $i++;
  if(!isset($c['pk_i_id'])) {
    echo '<ul '.$class.'>';
    if($i==1) {
      echo '<li><a href="'.osc_esc_html(osc_update_search_url(array('sCategory'=>null, 'iPage'=>null))).'">'.__('All categories', 'sigma')."</a></li>";
    }
    foreach($c as $key => $value) {
  ?>
      <li>
        <a id="cat_<?php echo osc_esc_html($value['pk_i_id']);?>" href="<?php echo osc_esc_html(osc_update_search_url(array('sCategory'=> $value['pk_i_id'], 'iPage'=>null))); ?>">
        <?php if(isset($current_category) && $current_category == $value['pk_i_id']){ echo '<strong>'.$value['s_name'].'</strong>'; }
        else{ echo $value['s_name']; } ?>
        </a>

      </li>
  <?php
    }
    if($i==1) {
    echo "</ul>";
    } else {
    echo "</ul>";
    }
  } else {
  ?>
  <ul <?php echo $class;?>>
    <?php if($i==1) { ?>
    <li><a href="<?php echo osc_esc_html(osc_update_search_url(array('sCategory'=>null, 'iPage'=>null))); ?>"><?php _e('All categories', 'sigma'); ?></a></li>
    <?php } ?>
      <li>
        <a id="cat_<?php echo osc_esc_html($c['pk_i_id']);?>" href="<?php echo osc_esc_html(osc_update_search_url(array('sCategory'=> $c['pk_i_id'], 'iPage'=>null))); ?>">
        <?php if(isset($current_category) && $current_category == $c['pk_i_id']){ echo '<strong>'.$c['s_name'].'</strong>'; }
            else{ echo $c['s_name']; } ?>
        </a>
        <?php sigma_print_sidebar_category_search($aCategories, $current_category, $i); ?>
      </li>
    <?php if($i==1) { ?>
    <?php } ?>
  </ul>
<?php
  }
}

/**

CLASSES

*/
class sigmaBodyClass
{
  /**
  * Custom Class for add, remove or get body classes.
  *
  * @param string $instance used for singleton.
  * @param array $class.
  */
  private static $instance;
  private $class;

  private function __construct()
  {
    $this->class = array();
  }

  public static function newInstance()
  {
    if (  !self::$instance instanceof self)
    {
      self::$instance = new self;
    }
    return self::$instance;
  }

  public function add($class)
  {
    $this->class[] = $class;
  }
  public function get()
  {
    return $this->class;
  }
}


function osc_theme_check_compatibility_branch() {
  $osclass_version = (int)str_replace('.', '', OSCLASS_VERSION);
  $osclass_author = (!defined('OSCLASS_AUTHOR') ? 'NONE' : strtoupper(OSCLASS_AUTHOR));
  
  if($osclass_version >= 420 && $osclass_author <> 'OSCLASSPOINT') {
    osc_add_flash_error_message('Theme is not compatible with your osclass version or branch! You cannot use this theme as it would generate errors on your installation. Download and install supported osclass version: <a href="https://osclass-classifieds.com/download">https://osclass-classifieds.com/download</a>');
  }
} 

osc_add_hook('header', 'osc_theme_check_compatibility_branch', 1);


/**

HELPERS

*/
if( !function_exists('osc_uploads_url')) {
  function osc_uploads_url($item = '') {
    $logo = osc_get_preference('logo', 'sigma');
    if ($logo != '' && file_exists(osc_uploads_path() . $logo)) {
      $path = str_replace(ABS_PATH, '', osc_uploads_path() . '/');
      $path = str_replace('//', '/', $path);
      return osc_base_url() . $path . $item;
    }
  }
}

/*

  ads  SEARCH

 */
if (!function_exists('search_ads_listing_top_fn')) {
  function search_ads_listing_top_fn() {
    if(osc_get_preference('search-results-top-728x90', 'sigma')!='') {
      echo '<div class="clear"></div>' . PHP_EOL;
      echo '<div class="ads_728">' . PHP_EOL;
      echo osc_get_preference('search-results-top-728x90', 'sigma');
      echo '</div>' . PHP_EOL;
    }
  }
}
//osc_add_hook('search_ads_listing_top', 'search_ads_listing_top_fn');

if (!function_exists('search_ads_listing_medium_fn')) {
  function search_ads_listing_medium_fn() {
    if(osc_get_preference('search-results-middle-728x90', 'sigma')!='') {
      echo '<div class="clear"></div>' . PHP_EOL;
      echo '<div class="ads_728">' . PHP_EOL;
      echo osc_get_preference('search-results-middle-728x90', 'sigma');
      echo '</div>' . PHP_EOL;
    }
  }
}
osc_add_hook('search_ads_listing_medium', 'search_ads_listing_medium_fn');
?>
