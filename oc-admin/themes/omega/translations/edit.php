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
    $array[] = 'translations-edit';
    return $array;
  }
}

function translation_issues($original, $translation) {
  $issues = array();
  $chars = array(' ', '.', ',', ';', ':', '!', '?', "'", '"', '/', '_', '%', '*', '^', '$', '#', '@', '^', '&', '(', ')', '[', ']', '{', '}', '-', '=');
  
  if($original != '' && trim($translation) != '') {
    if(in_array(substr($original, 0, 1), $chars) && substr($translation, 0, 1) != substr($original, 0, 1)) {
      $issues[] = sprintf(__('Original string starts with "%s", but translation does not.'), substr($original, 0, 1));
    } 
    
    if(in_array(substr($translation, 0, 1), $chars) && substr($translation, 0, 1) != substr($original, 0, 1)) {
      $issues[] = sprintf(__('Translation string starts with "%s", but original does not.'), substr($translation, 0, 1));
    } 
    
    if(in_array(substr($original, -1), $chars) && substr($translation, -1) != substr($original, -1)) {
      $issues[] = sprintf(__('Original string ends with "%s", but translation does not.'), substr($original, -1));
    } 
    
    if(in_array(substr($translation, -1), $chars) && substr($translation, -1) != substr($original, -1)) {
      $issues[] = sprintf(__('Translation string ends with "%s", but original does not.'), substr($translation, -1));
    }

    foreach(array('%s', '%d', '%f') as $magic) {
      if(substr_count($original, $magic) != substr_count($translation, $magic)) {
        $issues[] = sprintf(__('Original string contains magic word "%s" %d times, but translation string contains it %d times.'), $magic, substr_count($original, $magic), substr_count($translation, $magic));
      }
    }
  }
  
  return $issues;
}

osc_add_filter('admin_body_class','addBodyClass');


function customPageHeader() { 
  $file = str_replace(OC_CONTENT_FOLDER . '/', '', __get('file'));
  ?>
  <h1>
    <?php echo __('Edit translations catalog') . ' ' . $file; ?>
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

$exists = __get('exists');
$path = __get('path');
$link = __get('link');
$translations = __get('translations');

if(is_array($translations)) {
  $required_input_count = count($translations)*2 + 20;
  $available_input_count = (int)@ini_get('max_input_vars');
  
  if($available_input_count <= $required_input_count) {
  ?>
  <div class="newflash">
    <div class="flashmessage flashmessage-warning">
      <p class="info"><?php echo sprintf(__('To complete this translation, it may be required to use at least %d input fields. Your current PHP setting "max_input_vars" is set to limit %d. Increase this value in your PHP settings to avoid problems with translations!'), $required_input_count, $available_input_count); ?></p>
    </div>
  </div>
  <?php
  }
}
?>

<?php osc_run_hook('admin_translations_edit_top'); ?>

<div id="translations-edit">
  <div class="grid-system">
    <div class="grid-row grid-80">
      <div id="translation-main" class="row-wrapper">
        <div id="translations-search">
          <div class="c1">
            <i class="fa fa-search"></i>
            <input type="text" id="search-for-translation" value="" placeholder="<?php echo osc_esc_html(__('Search translation...')); ?>"/>
            <i class="clear fa fa-times-circle"></i>
          </div>
          <div class="c2">
            <a href="#" class="missing tbtn" title="<?php echo osc_esc_html(__('Show only rows those miss translations')); ?>" data-alt="<?php echo osc_esc_html(__('All strings')); ?>"><i class="fa fa-binoculars"></i> <span><?php _e('Only untranslated'); ?></span></a>
            <a href="#" class="issue tbtn" title="<?php echo osc_esc_html(__('Show only rows with issue')); ?>" data-alt="<?php echo osc_esc_html(__('All strings')); ?>"><i class="fa fa-warning"></i> <span><?php _e('Only with issue'); ?></span></a>
            <a href="#" class="add tbtn" title="<?php echo osc_esc_html(__('Add a new line into translation')); ?>"><i class="fa fa-plus-circle"></i> <span><?php _e('Add line'); ?></span></a>
            <?php osc_run_hook('admin_translations_edit_actions'); ?>
          </div>
        </div>
        
        <form id="translations-list" action="<?php echo osc_admin_base_url(true); ?>" method="POST">
          <input type="hidden" name="page" value="translations"/>
          <input type="hidden" name="action" value="edit_post"/>
          <input type="hidden" name="path" value="<?php echo __get('path'); ?>"/>
          <input type="hidden" name="file" value="<?php echo __get('file'); ?>"/>
          <input type="hidden" name="file_name" value="<?php echo __get('file_name'); ?>"/>
          <input type="hidden" name="language" value="<?php echo __get('language'); ?>"/>
          <input type="hidden" name="type" value="<?php echo osc_esc_html(Params::getParam('type')); ?>"/>
          <input type="hidden" name="section" value="<?php echo osc_esc_html(Params::getParam('section')); ?>"/>
          <input type="hidden" name="theme" value="<?php echo osc_esc_html(Params::getParam('theme')); ?>"/>
          <input type="hidden" name="plugin" value="<?php echo osc_esc_html(Params::getParam('plugin')); ?>"/>

          <?php osc_run_hook('admin_translations_edit_form'); ?>
          
          <?php 
            $i = 0; 
            $missing = 0;  
          ?>
          <?php if(is_array($translations) && count($translations) > 0) { ?>
            <?php foreach($translations as $key => $value) { ?>
              <?php
                $issues = translation_issues($value->getOriginal(), $value->getTranslation());
                $comments = $value->getComments();
                $references = $value->getReferences();
                $flags_ = $value->getFlags();
                $flags = array();
                
                if(count($flags_) > 0) {
                  foreach($flags_ as $flag) {
                    if($flag != 'php-format') {
                      $flags[] = $flag;
                    }
                  }
                }
              ?>
              
              <div class="row<?php if(count($translations)-1 == $i) { ?> last<?php } ?>" data-id="<?php echo $i; ?>">
                <input type="hidden" disabled name="source[<?php echo $i; ?>]" value="<?php echo osc_esc_html($value->getOriginal()); ?>"/>
                <input type="hidden" disabled name="translation[<?php echo $i; ?>]" value="<?php echo osc_esc_html($value->getTranslation()); ?>"/>

                <?php if(count($comments) > 0) { ?>
                  <div class="comment-note">
                    <i class="fa fa-comment" title="<?php echo osc_esc_html(__('This translation has comments')); ?>"></i>
                  </div>
                <?php } else if(!empty($issues)) { ?>
                  <div class="issues">
                    <i class="fa fa-warning show"></i>
                    
                    <div class="list">
                      <?php foreach($issues as $issue) { ?>
                        <div class="line"><?php echo $issue; ?></div>
                      <?php } ?>
                    </div>
                  </div>
                <?php } ?>
                
                <div class="source<?php if(count($flags) > 0) { ?> has-flag<?php } ?>" title="<?php echo implode('&#013;', $flags); ?>"><?php echo osc_esc_html($value->getOriginal()); ?></div>
                <div class="translation"><?php echo osc_esc_html($value->getTranslation()); ?></div>

                <div class="options-wrap">
                  <i class="more fa fa-ellipsis-v"></i>
                  <div class="more-options">
                    <div class="line"><a href="#" class="remove" data-alt="<?php echo osc_esc_html(__('Undo removal')); ?>"><i class="fa fa-trash"></i> <span><?php _e('Remove translation'); ?></span></a></div>
                    <div class="line"><a href="#" class="comment" data-alt="<?php echo osc_esc_html(__('Hide comments')); ?>"><i class="fa fa-comments"></i> <span><?php _e('Comments'); ?></span></a></div>
                    <div class="line"><a href="#" class="reference" data-alt="<?php echo osc_esc_html(__('Hide references')); ?>"><i class="fa fa-code"></i> <span><?php _e('References in code'); ?></span></a></div>
                    <?php osc_run_hook('admin_translations_edit_options'); ?>
                  </div>
                </div>
              </div>

              <div class="references" data-id="<?php echo $i; ?>">
                <?php 
                  if(count($references) > 0) {
                    foreach ($references as $file => $line_array) {
                      if(count($line_array) > 0) {
                        foreach($line_array as $line) {
                          ?><div class="line"><?php echo $file . ':' . $line; ?></div><?php
                        }
                      }
                    }
                  } else {
                    ?><div class="line none"><?php _e('No references found'); ?></div><?php 
                  }
                ?>
              </div>

              <div class="comments" data-id="<?php echo $i; ?>">
                <?php 
                  if(count($comments) > 0) {
                    foreach ($comments as $ckey => $cvalue) {
                      ?><div class="line"><?php echo $cvalue; ?></div><?php
                    }
                  } else {
                    ?><div class="line none"><?php _e('No comments found'); ?></div><?php 
                  }
                ?>
              </div>
              
              <?php 
                if(osc_esc_html($value->getTranslation()) == '') {
                  $missing++;  
                }
                
                $i++; 
              ?>
            <?php } ?>
          <?php } else { ?>
            <div class="empty">
              <div>
              <?php 
                if(!$exists) { 
                  _e('File does not exists');
                } else {
                  _e('No translations has been found');
                }
              ?>
              </div>
              
              <div>
                <?php if(!$exists) { ?>
                  <a href="<?php echo osc_admin_base_url(true) . '?' . osc_csrf_token_url() . '&page=translations&action=update_from_source&language=' . Params::getParam('language') . '&type=' . Params::getParam('type') . '&section=' . Params::getParam('section') . '&theme=' . Params::getParam('theme') . '&plugin=' . Params::getParam('plugin'); ?>" class="create btn btn-submit"><?php _e('Create file from source code'); ?></a>
                <?php } ?>
              </div>
            </div>
          <?php } ?>
        </form>
        
        <div id="translations-form">
          <div class="row" data-id="">
            <div class="source">
              <label><?php _e('Source text'); ?></label>
              <textarea name="source" id="source" readonly></textarea>
            </div>

            <div class="translation">
              <label><?php _e('Translation'); ?></label>
              <textarea name="translation" id="translation" readonly></textarea>
            </div>
          </div>
        </div>
        
        <div id="translations-new-placeholder">
          <div class="row new" data-id="{ID}">
            <input type="hidden" disabled name="source_new[{ID}]" value=""/>
            <input type="hidden" disabled name="translation_new[{ID}]" value=""/>

            <div class="source"><em><?php _e('Original text...'); ?></em></div>
            <div class="translation"><em><?php _e('Translation text...'); ?></em></div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="grid-row grid-20">
      <div id="translation-actions" class="row-wrapper">
        <div class="row">
          <?php osc_run_hook('admin_translations_edit_buttons_top'); ?>
          <a class="save btn btn-submit" href="#" title="<?php echo osc_esc_html(__('Save translations into .po file and update .mo file')); ?>"><i class="fa fa-save"></i> <?php _e('Save translation'); ?></a>
          <?php osc_run_hook('admin_translations_edit_buttons_middle'); ?>
          <a class="update btn " href="<?php echo osc_admin_base_url(true) . '?' . osc_csrf_token_url() . '&page=translations&action=update_from_source&language=' . Params::getParam('language') . '&type=' . Params::getParam('type') . '&section=' . Params::getParam('section') . '&theme=' . Params::getParam('theme') . '&plugin=' . Params::getParam('plugin'); ?>" title="<?php echo osc_esc_html(__('Update translations from source code. It may take a while.')); ?>"><i class="fa fa-upload"></i> <?php if($exists) { _e('Update from source code'); } else { _e('Create from source code'); } ?></a>
          <a class="btn <?php if(!$exists) { ?>disabled<?php } ?>" <?php if(!$exists) { ?>title="<?php echo osc_esc_html(__('Translation does not exists')); ?>"<?php } ?> href="<?php echo $link; ?>" target="_blank"><i class="fa fa-external-link-square"></i> <?php _e('Open in browser'); ?></a>
          <a class="btn" href="<?php echo __get('market_search_url'); ?>"><i class="fa fa-search"></i> <?php _e('Search on market'); ?></a>
          <a class="btn <?php if(!$exists) { ?>disabled<?php } ?>" <?php if(!$exists) { ?>title="<?php echo osc_esc_html(__('Translation does not exists')); ?>"<?php } ?> href="<?php echo osc_admin_base_url(true); ?>?<?php echo osc_csrf_token_url(); ?>&page=translations&action=download&language=<?php echo Params::getParam('language'); ?>&type=<?php echo Params::getParam('type'); ?>&section=<?php echo Params::getParam('section'); ?>&theme=<?php echo Params::getParam('theme'); ?>&plugin=<?php echo Params::getParam('plugin'); ?>"><i class="fa fa-download"></i> <?php _e('Download as archive'); ?></a>
          <a class="btn <?php if(!$exists) { ?>disabled<?php } ?>" <?php if(!$exists || $i == 0 || $missing > 0) { ?>title="<?php echo osc_esc_html(__('Translation does not exists')); ?>"<?php } ?> href="<?php echo osc_admin_base_url(true); ?>?<?php echo osc_csrf_token_url(); ?>&page=translations&action=remove&language=<?php echo Params::getParam('language'); ?>&type=<?php echo Params::getParam('type'); ?>&section=<?php echo Params::getParam('section'); ?>&theme=<?php echo Params::getParam('theme'); ?>&plugin=<?php echo Params::getParam('plugin'); ?>" onclick="return <?php if(!$exists) { ?>false<?php } else { ?>confirm('<?php echo osc_esc_js(__('Are you sure you want to remove this translation? Action cannot be undone!')); ?>')<?php } ?>;"><i class="fa fa-trash"></i> <?php _e('Remove this translation'); ?></a>
          <?php osc_run_hook('admin_translations_edit_buttons_bottom'); ?>

          <?php 
            $share_available = true;
            
            if(!$exists || $i == 0 || ($i > 0 && floatval($missing/$i) > 0.1)) {
              $share_available = false;
            }
          ?>
          
          <a class="btn btn-share-tr <?php if(!$share_available) { ?>disabled<?php } ?>" <?php if(!$share_available) { ?>title="<?php echo osc_esc_html(__('Only complete translation catalogs can be submitted to Osclass team and shared with community')); ?>"<?php } else { ?>title="<?php echo osc_esc_html(__('Share your translation catalog with community and help others with translations!')); ?>"<?php } ?> <?php if(!$share_available) { ?>onclick="return false;"<?php } ?> href="<?php echo osc_admin_base_url(true); ?>?<?php echo osc_csrf_token_url(); ?>&page=translations&action=send&language=<?php echo Params::getParam('language'); ?>&type=<?php echo Params::getParam('type'); ?>&section=<?php echo Params::getParam('section'); ?>&theme=<?php echo Params::getParam('theme'); ?>&plugin=<?php echo Params::getParam('plugin'); ?>"><i class="fa fa-users"></i> <?php _e('Provide to community'); ?></a>
        </div>

        <strong class="heading"><?php _e('Catalog'); ?></strong>

        <div class="row props">
          <div class="line"><strong><?php _e('Language'); ?>:</strong> <?php echo __get('language_name') . ' (' . __get('language') . ')'; ?></div>
          <div class="line">
            <strong><?php _e('Type'); ?>:</strong>
            <?php 
              if(Params::getParam('type') == 'CORE') {
                _e('Core');
              } else if(Params::getParam('type') == 'ADMIN') {
                _e('Backoffice (oc-admin)');
              } else if(Params::getParam('type') == 'THEME') {
                _e('Theme');
              } else if(Params::getParam('type') == 'PLUGIN') {
                _e('Plugin');
              } else {
                _e('Unknown');
              } 
            ?>
          </div>
          
          <?php if(Params::getParam('section') != '') { ?>
            <div class="line">
              <strong><?php _e('Section'); ?>:</strong>
              <?php 
                if(Params::getParam('section') == 'CORE') {
                  _e('Core & Backoffice');
                } else if(Params::getParam('section') == 'MESSAGES') {
                  _e('Flash messages');
                } else if(Params::getParam('section') == 'THEME') {
                  _e('Default theme');
                } else {
                  _e('Unknown');
                } 
              ?>
            </div>
          <?php } ?>

          <?php if(Params::getParam('type') == 'CORE') { ?>
            <div class="line core-files">
              <a class="btn core-link<?php if(Params::getParam('section') == 'CORE') { ?> btn-active<?php } ?>" href="<?php echo osc_admin_base_url(true) . '?' . osc_csrf_token_url() . '&page=translations&action=edit&language=' . Params::getParam('language') . '&type=' . Params::getParam('type') . '&section=CORE&theme=&plugin='; ?>"><?php _e('Core'); ?></a>
              <a class="btn core-link<?php if(Params::getParam('section') == 'MESSAGES') { ?> btn-active<?php } ?>" href="<?php echo osc_admin_base_url(true) . '?' . osc_csrf_token_url() . '&page=translations&action=edit&language=' . Params::getParam('language') . '&type=' . Params::getParam('type') . '&section=MESSAGES&theme=&plugin='; ?>"><?php _e('Messages'); ?></a>
              <a class="btn core-link<?php if(Params::getParam('section') == 'THEME') { ?> btn-active<?php } ?>" href="<?php echo osc_admin_base_url(true) . '?' . osc_csrf_token_url() . '&page=translations&action=edit&language=' . Params::getParam('language') . '&type=' . Params::getParam('type') . '&section=THEME&theme=&plugin='; ?>"><?php _e('Theme'); ?></a>
            </div>
          <?php } ?>

          <?php if(Params::getParam('theme') != '') { ?>
            <div class="line"><strong><?php _e('Theme'); ?>:</strong> <?php echo ucwords(str_replace('_', ' ', Params::getParam('theme'))); ?></div>
          <?php } ?>

          <?php if(Params::getParam('plugin') != '') { ?>
            <div class="line"><strong><?php _e('Plugin'); ?>:</strong> <?php echo ucwords(str_replace('_', ' ', Params::getParam('plugin'))); ?></div>
          <?php } ?>
          
          <?php osc_run_hook('admin_translations_edit_catalog'); ?>
        </div>
        
        <strong class="heading"><?php _e('Statistics'); ?></strong>

        <div class="row">
          <?php if($exists) { ?>
            <?php if($i > 0) { ?>
              <div class="bubble green">
                <?php 
                  $perc = number_format(($i - $missing)/$i*100, 2);
                  echo sprintf(__('%s%% translated'), $perc); 
                ?>
              </div>
            <?php } ?>

            <div class="bubble red">
              <?php 
                if($missing > 0) { 
                  echo sprintf(__('%d missing'), $missing); 
                } else {
                  echo __('No missing tranlsations');
                }
              ?>
            </div>
            
            <div class="bubble"><?php echo sprintf(__('%d translations'), $i); ?></div>
          <?php } else { ?>
            <div class="bubble red"><?php _e('File does not exists'); ?></div>
          <?php } ?>
          
          <?php osc_run_hook('admin_translations_edit_stats'); ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php osc_run_hook('admin_translations_edit_bottom'); ?>

<div class="clear"></div>


<script>
$(document).ready(function() {
  
  // Next focus after pressing "Tab" on translation textarea is next row
  $('body').on('keydown', '#translations-form .row textarea#translation', function(e) {
    var keyCode = e. keyCode || e. which;
    
    if(keyCode == 9) {
      e.preventDefault();
    
      var current = $('#translations-list .row.active');
      $('#translations-list .row').removeClass('active');
      current.next('.row').click();
    }
  });
  
  
  // Show only missing translations
  $('body').on('click', '#translations-search a.missing', function(e) {
    e.preventDefault();
    var text = $(this).find('span').text();
    var alt = $(this).attr('data-alt');

    $('#translations-list .comments, #translations-list .references').hide(0);
    
    if($(this).hasClass('active')) {
      $('#translations-list .row').removeClass('hidden-missing');
    } else {
      var filteredDivs = $('#translations-list .row').filter(function() { return $(this).find('.translation').text() != ''; });
      filteredDivs.addClass('hidden-missing');
    }
    
    $(this).find('span').text(alt);
    $(this).attr('data-alt', text);
    $(this).toggleClass('active');
  });
  
  
  // Show only missing translations
  $('body').on('click', '#translations-search a.issue', function(e) {
    e.preventDefault();
    var text = $(this).find('span').text();
    var alt = $(this).attr('data-alt');

    $('#translations-list .comments, #translations-list .references').hide(0);

    if($(this).hasClass('active')) {
      $('#translations-list .row').removeClass('hidden-issue');
    } else {
      var filteredDivs = $('#translations-list .row').filter(function() { return !$(this).find('.issues').length; });
      filteredDivs.addClass('hidden-issue');
    }
    
    $(this).find('span').text(alt);
    $(this).attr('data-alt', text);
    $(this).toggleClass('active');
  });


  // Remove translation
  $('body').on('click', '#translations-list .more-options a.remove', function(e) {
    e.preventDefault();
    var text = $(this).find('span').text();
    var alt = $(this).attr('data-alt');
    var id = $(this).closest('.row').attr('data-id');
    
    $(this).find('span').text(alt);
    $(this).attr('data-alt', text);
    
    $(this).closest('.row').toggleClass('remove');

    if($(this).closest('.row').hasClass('remove')) {
      var sourceVal = $(this).closest('.row').find('input[name="source[' + id + ']"]').val();
      $(this).closest('.row').prepend('<input type="hidden" name="source_remove[' + id + ']" value="' + sourceVal + '"/>');
    } else {
      $(this).closest('.row').find('input[name="source_remove[' + id + ']"]').remove();
    }
  });
  


  // Show comments
  $('body').on('click', '#translations-list .more-options a.comment', function(e) {
    e.preventDefault();
    var text = $(this).find('span').text();
    var alt = $(this).attr('data-alt');
    var id = $(this).closest('.row').attr('data-id');
    
    $(this).find('span').text(alt);
    $(this).attr('data-alt', text);
    
    $('#translations-list .comments[data-id="' + id + '"]').slideToggle(0);
  });


  // Show references
  $('body').on('click', '#translations-list .more-options a.reference', function(e) {
    e.preventDefault();
    var text = $(this).find('span').text();
    var alt = $(this).attr('data-alt');
    var id = $(this).closest('.row').attr('data-id');
    
    $(this).find('span').text(alt);
    $(this).attr('data-alt', text);
    
    $('#translations-list .references[data-id="' + id + '"]').slideToggle(0);
  });
  
  // Submit form
  $('body').on('click', '#translation-actions a.save', function(e) {
    e.preventDefault();
    formModified = false;
    $('form#translations-list').submit();
  });


  // Add new translation
  var newCounter = 100000;
  $('body').on('click', '#translations-search a.add', function(e) {
    e.preventDefault();
    var row = $('#translations-new-placeholder').html().replace('{ID}', newCounter).replace('{ID}', newCounter).replace('{ID}', newCounter).replace('{ID}', newCounter);
    $('#translations-list').append(row);
    $('#translations-list .row.new[data-id="' + newCounter + '"]');
    
    $('#translations-list').scrollTop($('#translations-list')[0].scrollHeight);
    $('#translations-list .empty').hide(0);

    newCounter++;
  });
  
  
  // Form was modified, show warning to user before leaving site
  var formModified = false;

  $(window).bind('beforeunload', function(e){
    if(formModified) { return true; }
    e = null;
  });


  // Click on translation row
  $('body').on('click', '#translations-list .row', function(e) {
    e.preventDefault();
    //var row = $(this).closest('.row');
    var row = $(this);
    
    if($(e.target).hasClass('row') || $(e.target).hasClass('source') || $(e.target).hasClass('translation')) {
      $('textarea#translation').prop('readonly', false);
      
      if(row.hasClass('new')) {
        $('textarea#source').prop('readonly', false);
      } else {
        $('textarea#source').prop('readonly', true);
      }
      
      $('#translations-list .row').removeClass('active');
      row.addClass('active');
      row.find('em').remove();
      
      var id = row.attr('data-id');
      var source = row.find('.source').text();
      var translation = row.find('.translation').text();
      
      $('#translations-form > .row').attr('data-id', id);
      $('#translations-form textarea#source').val(source);
      $('#translations-form textarea#translation').val(translation);
      
      if(row.hasClass('new')) {
        $('#translations-form textarea#source').focus();
      } else {
        $('#translations-form textarea#translation').focus();
      }
    }
  });


  // Search for translation
  var oldSearchString = '';
  $('body').on('keyup', 'input#search-for-translation', function() {
    var text = $(this).val();
    
    if(text == oldSearchString) {
      return false;
    }
    
    $('#translations-list .row').removeClass('match').removeClass('notmatch');
    $('#translations-search i.clear').hide(0);
    
    if(text != '') {
      $('#translations-search i.clear').show(0);
      $('#translations-list .row').addClass('notmatch');
      var filteredDivs = $('#translations-list .row').filter(function() { var reg = new RegExp(text, "i"); return reg.test($(this).text()); });
      filteredDivs.addClass('match').removeClass('notmatch');
    }
    
    oldSearchString = text;
  });

  
  // Translation text
  $('body').on('keyup', 'textarea#translation', function() {
    var id = $(this).closest('.row').attr('data-id');
    var text = $(this).val();
    $('#translations-list .row[data-id="' + id + '"] .translation').text(text);
    
    if($('#translations-list .row[data-id="' + id + '"]').hasClass('new')) {
      $('input[name="translation_new[' + id + ']"]').val(text);
      $('input[name="source_new[' + id + ']"], input[name="translation_new[' + id + ']"]').prop('disabled', false);      
    } else {
      $('input[name="translation[' + id + ']"]').val(text);
      $('input[name="source[' + id + ']"], input[name="translation[' + id + ']"]').prop('disabled', false);
    }
    
    formModified = true;
  });
  
  // Source text
  $('body').on('keyup', 'textarea#source', function() {
    var id = $(this).closest('.row').attr('data-id');
    var text = $(this).val();
    $('#translations-list .row[data-id="' + id + '"] .source').text(text);
    
    if($('#translations-list .row[data-id="' + id + '"]').hasClass('new')) {
      $('input[name="source_new[' + id + ']"]').val(text);
      $('input[name="source_new[' + id + ']"], input[name="translation_new[' + id + ']"]').prop('disabled', false);
    } else {
      $('input[name="source[' + id + ']"]').val(text);
      $('input[name="source[' + id + ']"], input[name="translation[' + id + ']"]').prop('disabled', false);
    }
    
    formModified = true;
  });
  
  // Clean search form
  $('body').on('click', '#translations-search i.clear', function(e) {
    e.preventDefault();
    $(this).hide();
    $('#translations-search input#search-for-translation').val('').keyup();
  });

});
</script>

<?php osc_current_admin_theme_path('parts/footer.php'); ?>