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



osc_add_filter('render-wrapper','render_offset');
function render_offset() {
  return 'row-offset';
}


if(!function_exists('addBodyClass')){
  function addBodyClass($array){
    $array[] = 'translations';
    return $array;
  }
}

osc_add_filter('admin_body_class','addBodyClass');


function customPageHeader() { 
  ?>
  <h1>
    <?php _e('Translations'); ?>
    <a href="<?php echo osc_admin_base_url(true) . '?page=translations&action=settings'; ?>" class="btn btn-green ico float-right"><?php _e('Settings'); ?></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return __('Translations') . ' - ' . $string;
}

osc_add_filter('admin_title', 'customPageTitle');

function customHead() {

}
osc_add_hook('admin_header', 'customHead', 10);

osc_current_admin_theme_path('parts/header.php'); 

$plugins = __get('plugins');
$themes = __get('themes');
$languages = __get('languages');
?>


<div class="newflash">
  <div class="flashmessage flashmessage-info">
    <p class="info"><?php echo sprintf(__('More effecient way to perform translations can be using of poedit (free program, download at poedit.net). Please follow %s to understand how to work with .po and .mo files. Osclass use only .mo files to extract translations.'), '<a target="_blank" href="https://docs.osclass-classifieds.com/translations-i22">' . __('documentation') . '</a>'); ?></p>
  </div>
</div>

<?php osc_run_hook('admin_translations_list_top'); ?>

<div id="translations">
  <div class="grid-system">
    <div class="grid-row grid-100">
      <div class="row-wrapper">
      
        <div class="widget-box">
          <div class="widget-box-title">
            <h3><span><?php _e('Create or update translations'); ?></span></h3>
          </div>
          <div class="widget-box-content">
            <form name="update_translation" action="<?php echo osc_admin_base_url(true); ?>" method="GET" novalidate="novalidate" class="nocsrf">
              <input type="hidden" name="page" value="translations">
              <input type="hidden" name="action" value="edit">
              
              <div class="form-horizontal">
                <div class="form-row">
                  <div class="form-label"><?php _e('Choose your language'); ?></div>
                  <div class="form-controls">
                    <select name="language" id="language">
                      <option value="" selected="selected"><?php _e('Select a language...'); ?></option>

                      <?php if(is_array($languages) && count($languages) > 0) { ?>
                        <?php foreach($languages as $lang) { ?>
                          <option value="<?php echo $lang['pk_c_code']; ?>"><?php echo $lang['s_name']; ?></option>
                        <?php } ?>
                      <?php } ?>
                    </select>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-label"><?php _e('Type of translation'); ?></div>
                  <div class="form-controls">
                    <select name="type" id="type">
                      <option value="" selected="selected"><?php _e('Select a type...'); ?></option>
                      
                      <option value="CORE"><?php _e('Core'); ?></option>
                      <!--<option value="ADMIN"><?php _e('Backoffice (oc-admin)'); ?></option>-->
                      <option value="THEME"><?php _e('Theme'); ?></option>
                      <option value="PLUGIN"><?php _e('Plugin'); ?></option>
                    </select>
                  </div>
                </div>
                
                <div class="form-row type-level3 type-core" style="display:none;">
                  <div class="form-label"><?php _e('Choose a section'); ?></div>
                  <div class="form-controls">
                    <select name="section" id="section">
                      <option value="" selected="selected"><?php _e('Select a section...'); ?></option>
                      
                      <option value="CORE"><?php _e('Core & Backoffice'); ?></option>
                      <option value="MESSAGES"><?php _e('Flash messages'); ?></option>
                      <option value="THEME"><?php _e('Default theme'); ?></option>
                    </select>
                  </div>
                </div>
                
                <div class="form-row type-level3 type-theme" style="display:none;">
                  <div class="form-label"><?php _e('Choose a theme'); ?></div>
                  <div class="form-controls">
                    <select name="theme" id="theme">
                      <option value="" selected="selected"><?php _e('Select a theme...'); ?></option>

                      <?php if(is_array($themes) && count($themes) > 0) { ?>
                        <?php foreach($themes as $theme_id) { ?>
                          <?php $info = WebThemes::newInstance()->loadThemeInfo($theme_id); ?>
                          <option value="<?php echo $theme_id; ?>"><?php echo $info['name']; ?></option>
                        <?php } ?>
                      <?php } ?>
                    </select>
                  </div>
                </div>
                
                <div class="form-row type-level3 type-plugin" style="display:none;">
                  <div class="form-label"><?php _e('Choose a plugin'); ?></div>
                  <div class="form-controls">
                    <select name="plugin" id="plugin">
                      <option value="" selected="selected"><?php _e('Select a plugin...'); ?></option>

                      <?php if(is_array($plugins) && count($plugins) > 0) { ?>
                        <?php foreach($plugins as $plugin_id) { ?>
                          <?php 
                            $info = osc_plugin_get_info($plugin_id); 
                            $plugin_id = str_replace(array('/index.php', '/', '.'), '', $plugin_id);
                          ?>
                          <option value="<?php echo $plugin_id; ?>"><?php echo $info['plugin_name']; ?></option>
                        <?php } ?>
                      <?php } ?>
                    </select>
                  </div>
                </div>
                
                <div class="clear"></div>
                
                <div class="form-actions">
                  <div class="form-label">&nbsp;</div>
                  <div class="form-controls">
                    <button type="submit" id="edit-translation" class="btn btn-submit"><i class="fa fa-edit"></i> <?php _e('Edit translation'); ?></button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    
    <div class="grid-row grid-100">
      <div class="row-wrapper">
      
        <div class="widget-box">
          <div class="widget-box-title">
            <h3><span><?php _e('Copy translations'); ?></span></h3>
          </div>
          <div class="widget-box-content">
            <form name="update_translation" action="<?php echo osc_admin_base_url(true); ?>" method="GET" novalidate="novalidate">
              <input type="hidden" name="page" value="translations">
              <input type="hidden" name="action" value="copy">
              
              <div class="form-horizontal">
                <strong class="row"><?php _e('Source translations catalog'); ?></strong>
                <div class="row info-row"><?php _e('Osclass will take all translations from this catalog.'); ?></div>

                <div class="form-row">
                  <div class="form-label"><?php _e('Language'); ?></div>
                  <div class="form-controls">
                    <select name="source_language" id="source_language">
                      <option value="" selected="selected"><?php _e('Select a language...'); ?></option>

                      <?php if(is_array($languages) && count($languages) > 0) { ?>
                        <?php foreach($languages as $lang) { ?>
                          <option value="<?php echo $lang['pk_c_code']; ?>"><?php echo $lang['s_name']; ?></option>
                        <?php } ?>
                      <?php } ?>
                    </select>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-label"><?php _e('Type'); ?></div>
                  <div class="form-controls">
                    <select name="source_type" id="source_type">
                      <option value="" selected="selected"><?php _e('Select a type...'); ?></option>
                      
                      <option value="CORE"><?php _e('Core'); ?></option>
                      <!--<option value="ADMIN"><?php _e('Backoffice (oc-admin)'); ?></option>-->
                      <option value="THEME"><?php _e('Theme'); ?></option>
                      <option value="PLUGIN"><?php _e('Plugin'); ?></option>
                    </select>
                  </div>
                </div>
                
                <div class="form-row source-type-level3 source-type-core" style="display:none;">
                  <div class="form-label"><?php _e('Section'); ?></div>
                  <div class="form-controls">
                    <select name="source_section" id="source_section">
                      <option value="" selected="selected"><?php _e('Select a section...'); ?></option>
                      
                      <option value="CORE"><?php _e('Core & Backoffice'); ?></option>
                      <option value="MESSAGES"><?php _e('Flash messages'); ?></option>
                      <option value="THEME"><?php _e('Default theme'); ?></option>
                    </select>
                  </div>
                </div>
                
                <div class="form-row source-type-level3 source-type-theme" style="display:none;">
                  <div class="form-label"><?php _e('Theme'); ?></div>
                  <div class="form-controls">
                    <select name="source_theme" id="source_theme">
                      <option value="" selected="selected"><?php _e('Select a theme...'); ?></option>

                      <?php if(is_array($themes) && count($themes) > 0) { ?>
                        <?php foreach($themes as $theme_id) { ?>
                          <?php $info = WebThemes::newInstance()->loadThemeInfo($theme_id); ?>
                          <option value="<?php echo $theme_id; ?>"><?php echo $info['name']; ?></option>
                        <?php } ?>
                      <?php } ?>
                    </select>
                  </div>
                </div>
                
                <div class="form-row source-type-level3 source-type-plugin" style="display:none;">
                  <div class="form-label"><?php _e('Plugin'); ?></div>
                  <div class="form-controls">
                    <select name="source_plugin" id="source_plugin">
                      <option value="" selected="selected"><?php _e('Select a plugin...'); ?></option>

                      <?php if(is_array($plugins) && count($plugins) > 0) { ?>
                        <?php foreach($plugins as $plugin_id) { ?>
                          <?php 
                            $info = osc_plugin_get_info($plugin_id); 
                            $plugin_id = str_replace(array('/index.php', '/', '.'), '', $plugin_id);
                          ?>
                          <option value="<?php echo $plugin_id; ?>"><?php echo $info['plugin_name']; ?></option>
                        <?php } ?>
                      <?php } ?>
                    </select>
                  </div>
                </div>

                <div class="row">&nbsp;</div>
                
                <strong class="row"><?php _e('Target translation catalog'); ?></strong>
                <div class="row info-row"><?php _e('Translations withdrawn from source catalog will be updated in target translations catalog. Only translations those exists in target catalog and are empty (untranslated) will be updated. Translated strings in target catalog will not be updated or rewritten.'); ?></div>

                <div class="form-row">
                  <div class="form-label"><?php _e('Language'); ?></div>
                  <div class="form-controls">
                    <select name="target_language" id="target_language">
                      <option value="" selected="selected"><?php _e('Select a language...'); ?></option>

                      <?php if(is_array($languages) && count($languages) > 0) { ?>
                        <?php foreach($languages as $lang) { ?>
                          <option value="<?php echo $lang['pk_c_code']; ?>"><?php echo $lang['s_name']; ?></option>
                        <?php } ?>
                      <?php } ?>
                    </select>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-label"><?php _e('Type'); ?></div>
                  <div class="form-controls">
                    <select name="target_type" id="target_type">
                      <option value="" selected="selected"><?php _e('Select a type...'); ?></option>
                      
                      <option value="CORE"><?php _e('Core'); ?></option>
                      <!--<option value="ADMIN"><?php _e('Backoffice (oc-admin)'); ?></option>-->
                      <option value="THEME"><?php _e('Theme'); ?></option>
                      <option value="PLUGIN"><?php _e('Plugin'); ?></option>
                    </select>
                  </div>
                </div>
                
                <div class="form-row target-type-level3 target-type-core" style="display:none;">
                  <div class="form-label"><?php _e('Section'); ?></div>
                  <div class="form-controls">
                    <select name="target_section" id="target_section">
                      <option value="" selected="selected"><?php _e('Select a section...'); ?></option>
                      
                      <option value="CORE"><?php _e('Core & Backoffice'); ?></option>
                      <option value="MESSAGES"><?php _e('Flash messages'); ?></option>
                      <option value="THEME"><?php _e('Default theme'); ?></option>
                    </select>
                  </div>
                </div>
                
                <div class="form-row target-type-level3 target-type-theme" style="display:none;">
                  <div class="form-label"><?php _e('Theme'); ?></div>
                  <div class="form-controls">
                    <select name="target_theme" id="target_theme">
                      <option value="" selected="selected"><?php _e('Select a theme...'); ?></option>

                      <?php if(is_array($themes) && count($themes) > 0) { ?>
                        <?php foreach($themes as $theme_id) { ?>
                          <?php $info = WebThemes::newInstance()->loadThemeInfo($theme_id); ?>
                          <option value="<?php echo $theme_id; ?>"><?php echo $info['name']; ?></option>
                        <?php } ?>
                      <?php } ?>
                    </select>
                  </div>
                </div>
                
                <div class="form-row target-type-level3 target-type-plugin" style="display:none;">
                  <div class="form-label"><?php _e('Plugin'); ?></div>
                  <div class="form-controls">
                    <select name="target_plugin" id="target_plugin">
                      <option value="" selected="selected"><?php _e('Select a plugin...'); ?></option>

                      <?php if(is_array($plugins) && count($plugins) > 0) { ?>
                        <?php foreach($plugins as $plugin_id) { ?>
                          <?php 
                            $info = osc_plugin_get_info($plugin_id); 
                            $plugin_id = str_replace(array('/index.php', '/', '.'), '', $plugin_id);
                          ?>
                          <option value="<?php echo $plugin_id; ?>"><?php echo $info['plugin_name']; ?></option>
                        <?php } ?>
                      <?php } ?>
                    </select>
                  </div>
                </div>

                <div class="clear"></div>
              
                <div class="form-actions">
                  <div class="form-label">&nbsp;</div>
                  <div class="form-controls">
                    <button type="submit" id="copy-translation" class="btn btn-submit"><i class="fa fa-upload"></i> <?php _e('Update translation'); ?></button>
                  </div>
                </div>
              </div>
            </form>
          </div>
          
        </div>
      </div>
    </div>


    <div class="grid-row grid-100">
      <div class="row-wrapper">
        
        <div class="widget-box">
          <div class="widget-box-title">
            <h3><span><?php _e('List of existing translations'); ?></span></h3>
          </div>
          <div id="translation-exist" class="widget-box-content">
            <strong class="row"><?php _e('Core'); ?></strong>
            <div class="row">
              <?php if(is_array(__get('core_translations')) && count(__get('core_translations')) > 0) { ?>
                <?php foreach(__get('core_translations') as $t) { ?>
                  <?php $parts = explode('_', $t['language']); ?>
                  
                  <div class="elem">
                    <div class="img" style="background:#<?php echo substr(md5($t['language']), 0, 6); ?>;">
                      <div class="top"><?php echo strtoupper($parts[0]); ?></div>
                      <div class="bottom"><?php echo strtoupper($parts[1]); ?></div>
                    </div>
                    
                    <div class="data">
                      <div class="name"><?php echo $t['language_name']; ?></div>
                      <div class="files">
                        <?php if(is_array($t['files']) && count($t['files']) > 0) { ?>
                          <?php foreach($t['files'] as $f) { ?>
                            <a href="<?php echo osc_admin_base_url(); ?>?page=translations&action=edit&language=<?php echo $t['language']; ?>&type=CORE&section=<?php echo strtoupper(str_replace('.po', '', basename($f))); ?>&theme=&plugin="><?php echo ucwords(str_replace('.po', '', basename($f))); ?></a>
                          <?php } ?>
                        <?php } ?>
                      </div>
                      <div class="stats">
                        <?php
                          if($t['exists']) {
                            echo sprintf(__('%d files, %d strings'), $t['count'], $t['strings']);
                          } else {
                            echo '<em>' . __('Translation does not exists') . '</em>'; 
                          }
                        ?>
                      </div>
                    </div>
                  </div>
                <?php } ?>
              <?php } ?>
            </div>

            <?php if(1==2) { ?>
            <strong class="row"><?php _e('Backoffice'); ?></strong>
            <div class="row">
              <?php if(is_array(__get('backoffice_translations')) && count(__get('backoffice_translations')) > 0) { ?>
                <?php foreach(__get('backoffice_translations') as $t) { ?>
                  <?php $parts = explode('_', $t['language']); ?>
                  
                  <div class="elem">
                    <div class="img" style="background:#<?php echo substr(md5($t['language']), 0, 6); ?>;">
                      <div class="top"><?php echo strtoupper($parts[0]); ?></div>
                      <div class="bottom"><?php echo strtoupper($parts[1]); ?></div>
                    </div>
                    
                    <div class="data">
                      <div class="name"><?php echo $t['language_name']; ?></div>
                      <div class="files">
                        <?php if(is_array($t['files']) && count($t['files']) > 0) { ?>
                          <?php foreach($t['files'] as $f) { ?>
                            <a href="<?php echo osc_admin_base_url(); ?>?page=translations&action=edit&language=<?php echo $t['language']; ?>&type=ADMIN&section=&theme=&plugin="><?php echo ucwords(str_replace('.po', '', basename($f))); ?></a>
                          <?php } ?>
                        <?php } ?>
                      </div>
                      <div class="stats">
                        <?php
                          if($t['exists']) {
                            echo sprintf(__('%d files, %d strings'), $t['count'], $t['strings']);
                          } else {
                            echo '<em>' . __('Translation does not exists') . '</em>'; 
                          }
                        ?>
                      </div>
                    </div>
                  </div>
                <?php } ?>
              <?php } ?>
            </div>
            <?php } ?>
            
            <strong class="row"><?php _e('Themes'); ?></strong>
            <div class="row">
              <?php if(is_array(__get('themes_translations')) && count(__get('themes_translations')) > 0) { ?>
                <?php foreach(__get('themes_translations') as $t) { ?>
                  <?php $parts = explode('_', $t['language']); ?>
                  
                  <div class="elem">
                    <div class="img" style="background:#<?php echo substr(md5($t['language']), 0, 6); ?>;">
                      <div class="top"><?php echo strtoupper($parts[0]); ?></div>
                      <div class="bottom"><?php echo strtoupper($parts[1]); ?></div>
                    </div>
                    
                    <div class="data">
                      <div class="name"><?php echo ucwords(str_replace('_', ' ', $t['subject'])) . ' / ' . $t['language_name']; ?></div>
                      <div class="files">
                        <?php if(is_array($t['files']) && count($t['files']) > 0) { ?>
                          <?php foreach($t['files'] as $f) { ?>
                            <a href="<?php echo osc_admin_base_url(); ?>?page=translations&action=edit&language=<?php echo $t['language']; ?>&type=THEME&section=&theme=<?php echo $t['subject']; ?>&plugin="><?php echo ucwords(str_replace('.po', '', basename($f))); ?></a>
                          <?php } ?>
                        <?php } ?>
                      </div>
                      <div class="stats">
                        <?php
                          if($t['exists']) {
                            echo sprintf(__('%d files, %d strings'), $t['count'], $t['strings']);
                          } else {
                            echo '<em>' . __('Translation does not exists') . '</em>'; 
                          }
                        ?>
                      </div>
                    </div>
                  </div>
                <?php } ?>
              <?php } ?>
            </div>


            <strong class="row"><?php _e('Plugins'); ?></strong>
            <div class="row">
              <?php if(is_array(__get('plugins_translations')) && count(__get('plugins_translations')) > 0) { ?>
                <?php foreach(__get('plugins_translations') as $t) { ?>
                  <?php $parts = explode('_', $t['language']); ?>
                  
                  <div class="elem">
                    <div class="img" style="background:#<?php echo substr(md5($t['language']), 0, 6); ?>;">
                      <div class="top"><?php echo strtoupper($parts[0]); ?></div>
                      <div class="bottom"><?php echo strtoupper($parts[1]); ?></div>
                    </div>
                    
                    <div class="data">
                      <div class="name"><?php echo ucwords(str_replace('_', ' ', $t['subject'])) . ' / ' . $t['language_name']; ?></div>
                      <div class="files">
                        <?php if(is_array($t['files']) && count($t['files']) > 0) { ?>
                          <?php foreach($t['files'] as $f) { ?>
                            <a href="<?php echo osc_admin_base_url(); ?>?page=translations&action=edit&language=<?php echo $t['language']; ?>&type=PLUGIN&section=&theme=&plugin=<?php echo $t['subject']; ?>"><?php echo ucwords(str_replace('.po', '', basename($f))); ?></a>
                          <?php } ?>
                        <?php } ?>
                      </div>
                      <div class="stats">
                        <?php
                          if($t['exists']) {
                            echo sprintf(__('%d files, %d strings'), $t['count'], $t['strings']);
                          } else {
                            echo '<em>' . __('Translation does not exists') . '</em>'; 
                          }
                        ?>
                      </div>
                    </div>
                  </div>
                <?php } ?>
              <?php } ?>
            </div>

          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<?php osc_run_hook('admin_translations_list_bottom'); ?>

<div class="clear"></div>


<script>
// Edit box
$(document).ready(function() {
  omgEnableEditButton();
  omgTypeChange();
  $('button#edit-translation').css('opacity', '');
  
  $('body').on('change', 'select#type', function() {
    omgTypeChange();
  });

  $('body').on('change', 'select#language, select#plugin, select#theme, select#section', function() {
    omgEnableEditButton();
  });
});

function omgEnableEditButton() {
  var disabled = false;

  if($('select#language').val() == '') {
    disabled = true;
  } else if ($('select#type').val() == '') {
    disabled = true;
  } else if ($('select#type').val() != 'ADMIN') {
    if($('select#type').val() == 'CORE' && $('select#section').val() == '') {
      disabled = true;
    } else if($('select#type').val() == 'THEME' && $('select#theme').val() == '') {
      disabled = true;
    } else if($('select#type').val() == 'PLUGIN' && $('select#plugin').val() == '') {
      disabled = true;
    }
  }

  $('button#edit-translation').prop('disabled', disabled).css('opacity', '');
}

function omgTypeChange() {
  var type = $('select#type').val();
  $('.form-row.type-level3').hide(0);
  
  if(type == 'CORE') {
    $('.form-row.type-core').show(0);
  } else if (type == 'ADMIN') {
  } else if (type == 'THEME') {
    $('.form-row.type-theme').show(0);
  } else if (type == 'PLUGIN') {
    $('.form-row.type-plugin').show(0);
  }
  
  omgEnableEditButton();
}



// Copy box
$(document).ready(function() {
  omgEnableCopyButton();
  omgTargetTypeChange();
  omgSourceTypeChange();

  $('button#copy-translation').css('opacity', '');
  
  $('body').on('change', 'select#source_type', function() {
    omgSourceTypeChange();
  });
  
  $('body').on('change', 'select#target_type', function() {
    omgTargetTypeChange();
  });

  $('body').on('change', 'select#source_language, select#source_plugin, select#source_theme, select#source_section, select#target_language, select#target_plugin, select#target_theme, select#target_section', function() {
    omgEnableCopyButton();
  });
});


function omgEnableCopyButton() {
  var disabled = false;

  if($('select#source_language').val() == '' || $('select#target_language').val() == '') {
    disabled = true;
  } else if ($('select#source_type').val() == '' || $('select#target_type').val() == '') {
    disabled = true;
  } else if ($('select#source_type').val() != 'ADMIN') {
    if($('select#source_type').val() == 'CORE' && $('select#source_section').val() == '') {
      disabled = true;
    } else if($('select#source_type').val() == 'THEME' && $('select#source_theme').val() == '') {
      disabled = true;
    } else if($('select#source_type').val() == 'PLUGIN' && $('select#source_plugin').val() == '') {
      disabled = true;
    }
  } else if ($('select#target_type').val() != 'ADMIN') {
    if($('select#target_type').val() == 'CORE' && $('select#target_section').val() == '') {
      disabled = true;
    } else if($('select#target_type').val() == 'THEME' && $('select#target_theme').val() == '') {
      disabled = true;
    } else if($('select#target_type').val() == 'PLUGIN' && $('select#target_plugin').val() == '') {
      disabled = true;
    }
  }

  $('button#copy-translation').prop('disabled', disabled).css('opacity', '');
}

function omgSourceTypeChange() {
  var type = $('select#source_type').val();
  $('.form-row.source-type-level3').hide(0);
  
  if(type == 'CORE') {
    $('.form-row.source-type-core').show(0);
  } else if (type == 'ADMIN') {
  } else if (type == 'THEME') {
    $('.form-row.source-type-theme').show(0);
  } else if (type == 'PLUGIN') {
    $('.form-row.source-type-plugin').show(0);
  }
  
  omgEnableCopyButton();
}

function omgTargetTypeChange() {
  var type = $('select#target_type').val();
  $('.form-row.target-type-level3').hide(0);
  
  if(type == 'CORE') {
    $('.form-row.target-type-core').show(0);
  } else if (type == 'ADMIN') {
  } else if (type == 'THEME') {
    $('.form-row.target-type-theme').show(0);
  } else if (type == 'PLUGIN') {
    $('.form-row.target-type-plugin').show(0);
  }
  
  omgEnableCopyButton();
}
</script>

<?php osc_current_admin_theme_path('parts/footer.php'); ?>