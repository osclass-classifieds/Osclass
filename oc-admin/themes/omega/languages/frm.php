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


osc_enqueue_script('jquery-validate');

function customPageHeader() { 
  ?>
  <h1><?php _e('Settings'); ?></h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Edit language - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');


//customize Head
function customHead() {
  LanguageForm::js_validation();
}

osc_add_hook('admin_header','customHead', 10);

$aLocale = __get('aLocale');

osc_current_admin_theme_path( 'parts/header.php' ); 
?>

<h2 class="render-title"><?php _e('Edit language'); ?></h2>
<div id="language-form">
  <ul id="error_list"></ul>
  <form name="language_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="page" value="languages" />
    <input type="hidden" name="action" value="edit_post" />
    <?php LanguageForm::primary_input_hidden($aLocale); ?>

    <div class="form-horizontal">
      <div class="form-row">
        <div class="form-label"><?php _e('Name'); ?></div>
        <div class="form-controls">
          <?php LanguageForm::name_input_text($aLocale); ?>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-label"><?php _e('Short name'); ?></div>
        <div class="form-controls">
          <?php LanguageForm::short_name_input_text($aLocale); ?>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-label"><?php _e('Description'); ?></div>
        <div class="form-controls">
          <?php LanguageForm::description_input_text($aLocale); ?>
        </div>
      </div>

      <div class="form-row">
        <div class="form-label"><?php _e('Date format'); ?></div>
        <div class="form-controls">
          <?php LanguageForm::date_format_input_text($aLocale); ?>
        </div>
      </div>
      
      <div class="form-row has-blank-label">
        <div class="form-label blank"><?php _e('Font-office'); ?></div>
        <div class="form-controls">
          <div class="form-label-checkbox"><?php LanguageForm::enabled_input_checkbox($aLocale); ?> <?php _e('Enabled for the public website'); ?></div>
        </div>
      </div>
      
      <div class="form-row has-blank-label">
        <div class="form-label blank"><?php _e('Back-office'); ?></div>
        <div class="form-controls">
          <div class="form-label-checkbox"><?php LanguageForm::enabled_bo_input_checkbox($aLocale); ?> <?php _e('Enabled for the backoffice (oc-admin)'); ?></div>
        </div>
      </div>
      
      <h2 class="render-title separate-top"><?php _e('Price format settings'); ?></h2>

      <div class="form-row">
        <div class="form-label"><?php _e('Currency format'); ?></div>
        <div class="form-controls">
          <?php LanguageForm::currency_format_input_text($aLocale); ?>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-label"><?php _e('Number of decimals'); ?></div>
        <div class="form-controls">
          <?php LanguageForm::num_dec_input_text($aLocale); ?>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-label"><?php _e('Decimal point'); ?></div>
        <div class="form-controls">
          <?php LanguageForm::dec_point_input_text($aLocale); ?>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-label"><?php _e('Thousands separator'); ?></div>
        <div class="form-controls">
          <?php LanguageForm::thousands_sep_input_text($aLocale); ?>
        </div>
      </div>

      <div class="form-row">
        <div class="form-label"><?php _e('Default currency'); ?></div>
        <div class="form-controls">
          <?php LanguageForm::def_currency_select($aLocale); ?>
        </div>
      </div>

      <h2 class="render-title separate-top"><?php _e('Advanced settings'); ?></h2>

      <div class="form-row">
        <div class="form-label"><?php _e('Stopwords'); ?></div>
        <div class="form-controls">
          <?php LanguageForm::description_textarea($aLocale); ?>
        </div>
      </div>
     
      <div class="form-row has-blank-label">
        <div class="form-label blank"><?php _e('Native location names'); ?></div>
        <div class="form-controls">
          <div class="form-label-checkbox"><?php LanguageForm::locations_native_input_checkbox($aLocale); ?> <?php _e('Location names will be shown in their native form'); ?></div>
        </div>
      </div>
      
      <div class="form-row has-blank-label">
        <div class="form-label blank"><?php _e('Text direction'); ?></div>
        <div class="form-controls">
          <div class="form-label-checkbox"><?php LanguageForm::rtl_input_checkbox($aLocale); ?> <?php _e('Is RTL language'); ?></div>
        </div>
      </div>
      
      <div class="form-actions">
        <input type="submit" value="<?php echo osc_esc_html(__('Save changes')); ?>" class="btn btn-submit" />
      </div>
    </div>
  </form>
</div>
<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>