<?php
// check requirements
if(!is_writable( ABS_PATH . OC_CONTENT_FOLDER . '/downloads/')) {
  osc_add_flash_error_message( sprintf(_m('<b>downloads</b> folder has to be writable, i.e.: <b>chmod a+w %s%s/downloads/</b>'), ABS_PATH, OC_CONTENT_FOLDER), 'admin');
}


if(ini_get('allow_url_fopen')==="0") {
  osc_add_flash_error_message(__('You need to enable <b>allow_url_fopen = On</b> at php.ini, if you need more information please contact your hosting provider.'), 'admin');
}

function customPageHeader() {

}

osc_add_hook('admin_page_header','customPageHeader');

osc_current_admin_theme_path( 'parts/header.php' );
?>