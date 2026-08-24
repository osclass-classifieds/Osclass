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
  echo '<p>' . __("Add, edit, or remove site languages for the front office and backoffice. Set locale, date format, and default currency per language.") . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader(){
  ?>
  <h1><?php _e('International'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
    <a href="<?php echo osc_admin_base_url(true); ?>?page=languages&action=add" class="btn btn-green ico ico-add-white float-right" ><?php _e('Add language'); ?></a>
    <a href="<?php echo osc_admin_base_url(true); ?>?page=languages&action=sync" class="btn btn-white ico ico-sync float-right" ><?php _e('Synchronize'); ?></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Manage languages - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');


function customHead() {
  ?>
  <script type="text/javascript">
  $(document).ready(function(){
    $("#check_all").change(function(){
      var isChecked = $(this).prop("checked");
      $('.col-bulkactions input').each(function() {
        if(isChecked == 1) {
          this.checked = true;
        } else {
          this.checked = false;
        }
      });
    });

    $("#dialog-language-delete").dialog({
      autoOpen: false,
      modal: true,
      title: '<?php echo osc_esc_js(__('Delete language')); ?>'
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
      if($("#bulk_actions option:selected").val() == "") {
        return false;
      }

      if($("#datatablesForm").attr('data-dialog-open') == "true") {
        return true;
      }

      $("#dialog-bulk-actions .form-row").html($("#bulk_actions option:selected").attr('data-dialog-content'));
      $("#bulk-actions-submit").html($("#bulk_actions option:selected").text());
      $("#datatablesForm").attr('data-dialog-open', 'true');
      $("#dialog-bulk-actions").dialog('open');
      return false;
    });
  });

  function delete_dialog(item_id) {
    $("#dialog-language-delete input[name='id[]']").attr('value', item_id);
    $("#dialog-language-delete").dialog('open');
    return false;
  }
  </script>
  <?php
}
osc_add_hook('admin_header','customHead', 10);


$aData = __get('aData');
$aRawRows = __get('aRawRows');
$iDisplayLength = __get('iDisplayLength');
$sort = Params::getParam('sort');
$direction = Params::getParam('direction');

$columns = $aData['aColumns'];
$columnSources = (isset($aData['aColumnSources']) ? $aData['aColumnSources'] : array());
$sortableColumns = (isset($aData['aSortableColumns']) ? $aData['aSortableColumns'] : array());
$rows = $aData['aRows'];
$withFilters = __get('withFilters');

osc_current_admin_theme_path('parts/header.php');
?>

<h2 class="render-title">
  <?php _e('Manage languages'); ?>
  <a href="<?php echo osc_admin_base_url(true); ?>?page=languages&action=add" class="btn btn-mini"><?php _e('Add new'); ?></a>
</h2>
<div class="relative">
  <div id="language-toolbar" class="table-toolbar">
    <div class="float-right">
      <form method="get" action="<?php echo osc_admin_base_url(true); ?>" class="inline nocsrf">
        <?php foreach(Params::getParamsAsArray('get') as $key => $value) { ?>
        <?php if($key != 'iDisplayLength') { ?>
        <input type="hidden" name="<?php echo osc_esc_html(strip_tags($key)); ?>" value="<?php echo osc_esc_html(strip_tags($value)); ?>" />
        <?php } } ?>
        <select name="iDisplayLength" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();" >
          <option value="10" <?php if(Params::getParam('iDisplayLength') == 10) echo 'selected'; ?> ><?php printf(__('%d Languages'), 10); ?></option>
          <option value="25" <?php if(Params::getParam('iDisplayLength') == 25) echo 'selected'; ?> ><?php printf(__('%d Languages'), 25); ?></option>
          <option value="50" <?php if(Params::getParam('iDisplayLength') == 50) echo 'selected'; ?> ><?php printf(__('%d Languages'), 50); ?></option>
          <option value="100" <?php if(Params::getParam('iDisplayLength') == 100) echo 'selected'; ?> ><?php printf(__('%d Languages'), 100); ?></option>
        </select>
      </form>
      <form method="get" action="<?php echo osc_admin_base_url(true); ?>" id="shortcut-filters" class="inline nocsrf">
        <input type="hidden" name="page" value="languages" />
        <?php foreach(Params::getParamsAsArray('get') as $key => $value) { ?>
        <?php if(!in_array($key, array('page', 'sSearch', 'action'), true)) { ?>
        <input type="hidden" name="<?php echo osc_esc_html(strip_tags($key)); ?>" value="<?php echo osc_esc_html(strip_tags($value)); ?>" />
        <?php } } ?>
        <?php if($withFilters) { ?>
        <a id="btn-hide-filters" href="<?php echo osc_admin_base_url(true); ?>?page=languages" class="btn"><?php _e('Reset search'); ?></a>
        <?php } ?>
        <input id="fLanguageSearch" name="sSearch" type="text" class="input-text input-actions" value="<?php echo osc_esc_html(Params::getParam('sSearch')); ?>" placeholder="<?php echo osc_esc_html(__('Search languages')); ?>" />
        <input type="submit" class="btn submit-right" value="<?php echo osc_esc_html(__('Find')); ?>">
      </form>
    </div>
  </div>

  <form class="" id="datatablesForm" action="<?php echo osc_admin_base_url(true); ?>" method="post" data-dialog-open="false">
    <?php echo osc_csrf_token_form(); ?>
    <input type="hidden" name="page" value="languages" />

    <div id="bulk-actions">
      <label>
        <?php osc_print_bulk_actions('bulk_actions', 'action', __get('bulk_options'), 'select-box-extra'); ?>
        <input type="submit" id="bulk_apply" class="btn" value="<?php echo osc_esc_html(__('Apply')); ?>" />
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
        <?php if(count($rows) > 0) { ?>
          <?php foreach($rows as $key => $row) { ?>
            <tr class="<?php echo implode(' ', osc_apply_filter('datatable_languages_class', array(), isset($aRawRows[$key]) ? $aRawRows[$key] : array(), $row)); ?>">
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
    <?php foreach(Params::getParamsAsArray('get') as $key => $value) { ?>
      <?php if($key != 'iDisplayLength') { ?>
        <input type="hidden" name="<?php echo osc_esc_html(strip_tags($key)); ?>" value="<?php echo osc_esc_html(strip_tags($value)); ?>" />
      <?php } ?>
    <?php } ?>
    <select name="iDisplayLength" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();" >
      <option value="10" <?php if(Params::getParam('iDisplayLength') == 10) echo 'selected'; ?> ><?php printf(__('%d Languages'), 10); ?></option>
      <option value="25" <?php if(Params::getParam('iDisplayLength') == 25) echo 'selected'; ?> ><?php printf(__('%d Languages'), 25); ?></option>
      <option value="50" <?php if(Params::getParam('iDisplayLength') == 50) echo 'selected'; ?> ><?php printf(__('%d Languages'), 50); ?></option>
      <option value="100" <?php if(Params::getParam('iDisplayLength') == 100) echo 'selected'; ?> ><?php printf(__('%d Languages'), 100); ?></option>
    </select>
  </form>
</div>

<form id="dialog-language-delete" method="get" action="<?php echo osc_admin_base_url(true); ?>" class="has-form-actions hide" title="<?php echo osc_esc_html(__('Delete language')); ?>">
  <input type="hidden" name="page" value="languages" />
  <input type="hidden" name="action" value="delete" />
  <input type="hidden" name="id[]" value="" />
  <div class="form-horizontal">
    <div class="form-row">
      <?php _e('Are you sure you want to delete this language?'); ?>
    </div>
    <div class="form-actions">
      <div class="wrapper">
      <input id="language-delete-submit" type="submit" value="<?php echo osc_esc_html(__('Delete')); ?>" class="btn btn-submit" />
      <a class="btn" href="javascript:void(0);" onclick="$('#dialog-language-delete').dialog('close');"><?php _e('Cancel'); ?></a>
      </div>
    </div>
  </div>
</form>

<div id="dialog-bulk-actions" title="<?php echo osc_esc_html(__('Bulk actions')); ?>" class="has-form-actions hide">
  <div class="form-horizontal">
    <div class="form-row"></div>
    <div class="form-actions">
      <div class="wrapper">
        <a id="bulk-actions-submit" href="javascript:void(0);" class="btn btn-submit" ><?php echo osc_esc_html(__('Delete')); ?></a>
        <a id="bulk-actions-cancel" class="btn" href="javascript:void(0);"><?php _e('Cancel'); ?></a>
        <div class="clear"></div>
      </div>
    </div>
  </div>
</div>

<div id="market_installer" class="has-form-actions hide">
  <form name="mkti" action="<?php echo osc_admin_base_url(true); ?>?page=ajax&action=market&<?php echo osc_csrf_token_url(); ?>" method="post">
    <input type="hidden" name="section" value="languages" />
    <input type="hidden" name="market_product_key" id="market_product_key" value="" />

    <div class="osc-modal-content-market">
      <table class="table" cellpadding="0" cellspacing="0">
        <tbody>
          <tr class="table-first-row">
            <td><?php _e('Name'); ?></td>
            <td><span id="market_name"><?php _e('Loading data'); ?></span></td>
          </tr>
          <tr class="even">
            <td><?php _e('Version'); ?></td>
            <td><span id="market_version"><?php _e('Loading data'); ?></span></td>
          </tr>
          <tr>
            <td><?php _e('Date'); ?></td>
            <td><span id="market_date"><?php _e('Loading data'); ?></span></td>
          </tr>
          <tr class="even">
            <td><?php _e('URL'); ?></td>
            <td><span id="market_url_span"><a id="market_url" href="#"><?php _e('Download manually'); ?></a></span></td>
          </tr>
        </tbody>
      </table>

      <div class="clear"></div>
    </div>

    <div class="form-actions">
      <div class="wrapper">
        <button id="market_install" class="btn btn-submit" ><?php _e('Update'); ?></button>
        <button id="market_cancel" class="btn" ><?php _e('Cancel'); ?></button>
      </div>
    </div>
  </form>
</div>

<script type="text/javascript">
  $(function() {
    $("#market_cancel").on("click", function(){
      $(".ui-dialog-content").dialog("close");
      return false;
    });

    $("#market_install").on("click", function(){
      $(".ui-dialog-content").dialog("close");
      $('<div id="downloading"><div class="osc-modal-content"><img class="ui-download-loading" src="<?php echo osc_current_admin_theme_url(); ?>images/spinner.gif" alt="loading..."/><?php echo osc_esc_js(__('Please wait until the download is completed')); ?></div></div>').dialog({title:'<?php echo osc_esc_js(__('Downloading')); ?>...',modal:true});
      $.getJSON(
      "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=market&<?php echo osc_csrf_token_url(); ?>",
      {"market_product_key" : $("#market_product_key").attr("value"), "section" : 'languages'},
      function(data){
        var content = '';

        if(data.error == 0) {
          content += oscEscapeHTML(data.message);
          content += '<h3><?php echo osc_esc_js(__('Language has been downloaded correctly.')); ?></h3>';
          content += "<p>";
          content += '<a class="btn btn-mini btn-green" href="<?php echo osc_admin_base_url(true); ?>?page=languages&marketError='+data.error+'&slug='+oscEscapeHTML(data.data['url'])+'"><?php echo osc_esc_js(__('Ok')); ?></a>';
          content += '<a class="btn btn-mini" href="javascript:location.reload(true)"><?php echo osc_esc_js(__('Close')); ?></a>';
          content += "</p>";
        } else {
          content += '<p>' + oscEscapeHTML(data.message) + '</p><p>&nbsp;</p>';
          content += '<a class="btn btn-mini" href="javascript:location.reload(true)"><?php echo osc_esc_js(__('Close')); ?></a>';
        }
        $("#downloading .osc-modal-content").html(content);
      });
      return false;
    });
  });

  $('.btn-market-popup').on('click',function(){
    $.getJSON(
      "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=check_market",
      {"code" : $(this).attr('href').replace('#',''), 'section' : 'languages'},
      function(data){
        if(data!=null) {
          if("error_msg" in data) {
            $("#market_name").closest('form').find('.flashmessage').remove();
            $("#market_name").closest('.osc-modal-content-market').before('<div class="flashmessage flashmessage-warning flashmessage-inline">' + data.error_msg + '</div>');
            $("#market_name").closest('form').find('button#market_install').fadeOut(0);
          } else {
            $("#market_name").closest('form').find('.flashmessage').remove();
            $("#market_name").closest('form').find('button#market_install').fadeIn(0);

            $("#market_product_key").attr("value", data.code);
            $("#market_name").text(data.full_name);
            $("#market_version").text(data.s_version);
            $("#market_date").text(data.date);
            $("#market_url").attr('href',data.url);
          }

          var dialogWidth = 485;

          if($(window).width() < 525) {
            dialogWidth = $(window).width() - 40;
          }

          $('#market_installer').dialog({
            modal: true,
            title: '<?php echo osc_esc_js(__('Update language from OsclassPoint')); ?>',
            width: dialogWidth
          });
        }
      }
    );

    return false;
  });
</script>

<?php osc_current_admin_theme_path('parts/footer.php');
