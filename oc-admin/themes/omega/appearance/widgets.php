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


function addHelp() {
  echo '<p>' . __('Widgets print HTML or code in theme sections such as header, footer, or a custom name you add.') . '</p>';
  echo '<p><strong>' . __('Sections') . '</strong></p>';
  echo '<p>' . __('Theme sections come from the active theme (usually header and footer). Older themes print them with osc_show_widgets(\'header\') or osc_show_widgets(\'footer\').') . '</p>';
  echo '<p>' . sprintf(__('Custom sections are added in widget settings (gear icon). Put %s in the theme where that block should appear. If the section has nothing to show, that call prints nothing.'), '<code>&lt;?php osc_widget(\'xyz\'); ?&gt;</code>') . '</p>';
  echo '<p><strong>' . __('Content') . '</strong></p>';
  echo '<p>' . __('HTML content can differ per locale. Code (scripts, ads) and CSS are the same for all locales. A widget with empty content and empty code is skipped: no wrapper and no empty style tag.') . '</p>';
  echo '<p><strong>' . __('This page') . '</strong></p>';
  echo '<p>' . __('Filter by section, search, change position within the same section, or delete. Device visibility hides a widget with CSS: mobile 0-767px, desktop 768px and up.') . '</p>';
}
osc_add_hook('help_box','addHelp');


function customPageHeader() {
  ?>
  <h1><?php _e('Appearance'); ?>
    <a href="<?php echo osc_admin_base_url(true); ?>?page=appearance&amp;action=widget_settings" class="btn ico ico-32 ico-engine float-right"></a>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
    <a href="<?php echo osc_admin_base_url(true); ?>?page=appearance&amp;action=add_widget" class="btn btn-green ico ico-add-white float-right"><?php _e('Add widget'); ?></a>
  </h1>
  <?php
}
osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Manage widgets - %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');


function customHead() { ?>
  <script type="text/javascript">
    function pagesReloadAfterReorder(id) {
      var url = window.location.href.split('#')[0];
      url = url.replace(/([?&])reorderId=\d+/g, '$1').replace(/[?&]$/, '');
      var sep = (url.indexOf('?') >= 0 ? '&' : '?');
      window.location.href = url + sep + 'reorderId=' + parseInt(id, 10);
    }

    function order_up(id) {
      $('#datatables_list_processing').show();
      $.ajax({
        url: "<?php echo osc_admin_base_url(true)?>?page=ajax&action=order_widgets&id="+id+"&order=up&<?php echo osc_csrf_token_url(); ?>",
        success: function(res) {
          pagesReloadAfterReorder(id);
        },
        error: function(){
          $('#datatables_list_processing').hide();
        }
      });
    }

    function order_down(id) {
      $('#datatables_list_processing').show();
      $.ajax({
        url: "<?php echo osc_admin_base_url(true)?>?page=ajax&action=order_widgets&id="+id+"&order=down&<?php echo osc_csrf_token_url(); ?>",
        success: function(res){
          pagesReloadAfterReorder(id);
        },
        error: function(){
          $('#datatables_list_processing').hide();
        }
      });
    }

    $(document).ready(function(){
      var $reorderedRow = $('.table tr.row-reordered');
      if($reorderedRow.length) {
        setTimeout(function() {
          $reorderedRow.removeClass('row-reordered');
          var url = window.location.href.split('#')[0];
          url = url.replace(/([?&])reorderId=\d+/g, '$1').replace(/[?&]$/, '');
          if(window.history && window.history.replaceState) {
            window.history.replaceState({}, document.title, url);
          }
        }, 3000);
      }
      $("#check_all").change(function(){
        var isChecked = $(this).prop("checked");
        $('.col-bulkactions input').each( function() {
          this.checked = (isChecked == 1);
        });
      });

      $("#dialog-widget-delete").dialog({
        autoOpen: false,
        modal: true
      });

      $("#dialog-bulk-actions").dialog({
        autoOpen: false,
        modal: true
      });

      $("#bulk-actions-submit").click(function() {
        $("#datatablesForm").submit();
      });

      $("#bulk-actions-cancel").click(function() {
        $("#datatablesForm").attr('data-dialog-open', 'false');
        $('#dialog-bulk-actions').dialog('close');
      });

      $("#datatablesForm").submit(function() {
        if($("#bulk_actions option:selected").val() == "" ) {
          return false;
        }

        if($("#datatablesForm").attr('data-dialog-open') == "true" ) {
          return true;
        }

        var dialogContent = $("#bulk_actions option:selected").attr('data-dialog-content') || '';
        if($.trim(dialogContent) == '') {
          return true;
        }

        $("#dialog-bulk-actions .form-row").html(dialogContent);
        $("#bulk-actions-submit").html($("#bulk_actions option:selected").text());
        $("#datatablesForm").attr('data-dialog-open', 'true');
        $("#dialog-bulk-actions").dialog('open');
        return false;
      });
    });

    function delete_dialog(item_id) {
      $("#dialog-widget-delete input[name='id']").attr('value', item_id);
      $("#dialog-widget-delete").dialog('open');
      return false;
    }
  </script>
  <?php
}
osc_add_hook('admin_header','customHead', 10);

$aData    = __get('aData');
$aRawRows   = __get('aRawRows');
$sort     = Params::getParam('sort');
$direction  = Params::getParam('direction');
$widget_sections = __get('widget_sections');

$columns  = $aData['aColumns'];
$columnSources = (isset($aData['aColumnSources']) ? $aData['aColumnSources'] : array());
$sortableColumns = (isset($aData['aSortableColumns']) ? $aData['aSortableColumns'] : array());
$rows     = $aData['aRows'];
$hasActiveFilters = false;
$filterExclude = array('page', 'action', 'iDisplayLength', 'sort', 'direction', 'iPage', 'reorderId');
$reorderId = (int)Params::getParam('reorderId');

foreach(Params::getParamsAsArray('get') as $key => $value) {
  if(in_array($key, $filterExclude, true)) {
    continue;
  }

  if(is_array($value)) {
    foreach($value as $v) {
      if(trim((string)$v) != '') {
        $hasActiveFilters = true;
        break 2;
      }
    }
  } else if(trim((string)$value) != '') {
    $hasActiveFilters = true;
    break;
  }
}

osc_current_admin_theme_path( 'parts/header.php' );
?>

<h2 class="render-title"><?php _e('Manage widgets'); ?> <a href="<?php echo osc_admin_base_url(true); ?>?page=appearance&amp;action=add_widget" class="btn btn-mini"><?php _e('Add new'); ?></a></h2>
<div class="relative">
  <div id="widgets-toolbar" class="table-toolbar">
    <div class="float-right">
      <form method="get" action="<?php echo osc_admin_base_url(true); ?>" class="inline nocsrf">
        <?php foreach(Params::getParamsAsArray('get') as $key => $value ) { ?>
          <?php if($key != 'iDisplayLength' ) { ?>
            <input type="hidden" name="<?php echo osc_esc_html(strip_tags($key)); ?>" value="<?php echo osc_esc_html(strip_tags($value)); ?>" />
          <?php } ?>
        <?php } ?>
        <select name="iDisplayLength" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();" >
          <option value="10" <?php if(Params::getParam('iDisplayLength') == 10 ) echo 'selected'; ?> ><?php printf(__('%d Widgets'), 10); ?></option>
          <option value="25" <?php if(Params::getParam('iDisplayLength') == 25 ) echo 'selected'; ?> ><?php printf(__('%d Widgets'), 25); ?></option>
          <option value="50" <?php if(Params::getParam('iDisplayLength') == 50 ) echo 'selected'; ?> ><?php printf(__('%d Widgets'), 50); ?></option>
          <option value="100" <?php if(Params::getParam('iDisplayLength') == 100 ) echo 'selected'; ?> ><?php printf(__('%d Widgets'), 100); ?></option>
        </select>
      </form>
      <?php if($hasActiveFilters) { ?>
        <a id="btn-hide-filters" href="<?php echo osc_admin_base_url(true); ?>?page=appearance&action=widgets" class="btn"><?php _e('Reset filters'); ?></a>
      <?php } ?>
      <form method="get" action="<?php echo osc_admin_base_url(true); ?>" id="shortcut-filters" class="inline nocsrf">
        <input type="hidden" name="page" value="appearance" />
        <input type="hidden" name="action" value="widgets" />
        <input type="hidden" name="iDisplayLength" value="<?php echo (int)Params::getParam('iDisplayLength'); ?>" />
        <select name="s_location" id="widget-filter-section" class="select-box-extra select-box-medium" onchange="this.form.submit();">
          <option value=""><?php _e('All sections'); ?></option>
          <?php if(is_array($widget_sections)) { foreach($widget_sections as $section) { ?>
            <option value="<?php echo osc_esc_html($section); ?>" <?php if(Params::getParam('s_location') === $section) echo 'selected'; ?>><?php echo osc_esc_html($section); ?></option>
          <?php } } ?>
        </select>
        <input id="fPattern" type="text" name="sSearch" value="<?php echo osc_esc_html(Params::getParam('sSearch')); ?>" class="input-text input-actions" placeholder="<?php echo osc_esc_html(__('Search widget')); ?>" />
        <input type="submit" class="btn submit-right" value="<?php echo osc_esc_html(__('Find')); ?>">
      </form>
    </div>
  </div>
  <form class="" id="datatablesForm" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="page" value="appearance" />
    <div id="bulk-actions">
      <label>
        <?php osc_print_bulk_actions('bulk_actions', 'action', __get('bulk_options'), 'select-box-extra'); ?>
        <input type="submit" id="bulk_apply" class="btn" value="<?php echo osc_esc_html( __('Apply') ); ?>" />
      </label>
    </div>
    <div class="table-contains-actions">
      <table class="table" cellpadding="0" cellspacing="0">
        <thead>
          <tr>
            <?php foreach($columns as $k => $v) {
              $sourceCol = (isset($columnSources[$k]) ? $columnSources[$k] : '');
              $isSortable = ((in_array($k, $sortableColumns, true) || strpos((string)$v, 'sort=') !== false) ? 'is-sortable' : '');
              echo '<th class="col-'.$k.' '.$isSortable.' '.($sort==$k?($direction=='desc'?'sort-desc':'sort-asc'):'').'" data-source-col="' . osc_esc_html($sourceCol) . '">' . $v . '</th>';
            } ?>
          </tr>
        </thead>
        <tbody>
        <?php if(count($rows) > 0 ) { ?>
          <?php foreach($rows as $key => $row) {
            $rowId = (isset($aRawRows[$key]['pk_i_id']) ? (int)$aRawRows[$key]['pk_i_id'] : 0);
            $rowClass = ($reorderId > 0 && $rowId === $reorderId ? 'row-reordered' : '');
          ?>
            <tr<?php if($rowClass != '') { echo ' class="' . osc_esc_html($rowClass) . '"'; } ?> data-row-id="<?php echo $rowId; ?>">
              <?php foreach($row as $k => $v) { ?>
                <td class="col-<?php echo $k; ?>"><?php echo $v; ?></td>
              <?php } ?>
            </tr>
          <?php } ?>
        <?php } else { ?>
        <tr>
          <td colspan="<?php echo max(1, count($columns)); ?>" class="text-center">
          <p><?php _e('No data available in table'); ?></p>
          </td>
        </tr>
        <?php } ?>
        </tbody>
      </table>
      <div id="table-row-actions"></div>
    </div>
  </form>
</div>
<?php
  function showingResults(){
    $aData = __get('aData');
    echo '<ul class="showing-results"><li><span>'.osc_pagination_showing((Params::getParam('iPage')-1)*$aData['iDisplayLength']+1, ((Params::getParam('iPage')-1)*$aData['iDisplayLength'])+count($aData['aRows']), $aData['iTotalDisplayRecords'], $aData['iTotalRecords']).'</span></li></ul>';
  }
  osc_add_hook('before_show_pagination_admin','showingResults');
  osc_show_pagination_admin($aData);
?>
<div class="display-select-bottom">
  <form method="get" action="<?php echo osc_admin_base_url(true); ?>" class="inline nocsrf">
    <?php foreach(Params::getParamsAsArray('get') as $key => $value ) { ?>
      <?php if($key != 'iDisplayLength' ) { ?>
        <input type="hidden" name="<?php echo osc_esc_html(strip_tags($key)); ?>" value="<?php echo osc_esc_html(strip_tags($value)); ?>" />
      <?php } ?>
    <?php } ?>
    <select name="iDisplayLength" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();" >
      <option value="10" <?php if(Params::getParam('iDisplayLength') == 10 ) echo 'selected'; ?> ><?php printf(__('%d Widgets'), 10); ?></option>
      <option value="25" <?php if(Params::getParam('iDisplayLength') == 25 ) echo 'selected'; ?> ><?php printf(__('%d Widgets'), 25); ?></option>
      <option value="50" <?php if(Params::getParam('iDisplayLength') == 50 ) echo 'selected'; ?> ><?php printf(__('%d Widgets'), 50); ?></option>
      <option value="100" <?php if(Params::getParam('iDisplayLength') == 100 ) echo 'selected'; ?> ><?php printf(__('%d Widgets'), 100); ?></option>
    </select>
  </form>
</div>

<form id="dialog-widget-delete" method="get" action="<?php echo osc_admin_base_url(true); ?>" class="has-form-actions hide" title="<?php echo osc_esc_html(__('Delete widget')); ?>">
  <input type="hidden" name="page" value="appearance" />
  <input type="hidden" name="action" value="delete_widget" />
  <input type="hidden" name="id" value="" />
  <div class="form-horizontal">
    <div class="form-row">
      <?php _e('Are you sure you want to delete this widget?'); ?>
    </div>
    <div class="form-actions">
      <div class="wrapper">
      <input id="widget-delete-submit" type="submit" value="<?php echo osc_esc_html( __('Delete') ); ?>" class="btn btn-submit" />
      <a class="btn" href="javascript:void(0);" onclick="$('#dialog-widget-delete').dialog('close');"><?php _e('Cancel'); ?></a>
      </div>
    </div>
  </div>
</form>
<div id="dialog-bulk-actions" title="<?php _e('Bulk actions'); ?>" class="has-form-actions hide">
  <div class="form-horizontal">
    <div class="form-row"></div>
    <div class="form-actions">
      <div class="wrapper">
        <a id="bulk-actions-submit" href="javascript:void(0);" class="btn btn-submit" ><?php echo osc_esc_html( __('Delete') ); ?></a>
        <a id="bulk-actions-cancel" class="btn" href="javascript:void(0);"><?php _e('Cancel'); ?></a>
        <div class="clear"></div>
      </div>
    </div>
  </div>
</div>
<?php osc_current_admin_theme_path( 'parts/footer.php' );