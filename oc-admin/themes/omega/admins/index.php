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
  echo '<p>' . __('Add users who can access the backoffice. Administrators have full access; moderators can manage listings, comments, media, and statistics.') . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageTitle($string) {
  return sprintf(__('Manage admins - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');


function customPageHeader() {
  ?>
  <h1>
    <?php _e('Admins'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
    <a href="<?php echo osc_admin_base_url(true); ?>?page=admins&amp;action=add" class="btn btn-green ico ico-add-white float-right"><?php _e('Add admin'); ?></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customHead() { ?>
  <script type="text/javascript">
    $(document).ready(function(){
      var dialogWidth = 680;

      if($(window).width() < 720) {
        dialogWidth = $(window).width() - 40;
      }

      $('#display-filters').dialog({
        autoOpen: false,
        modal: true,
        width: dialogWidth,
        title: '<?php echo osc_esc_js(__('Filters')); ?>'
      });

      $('#btn-display-filters').click(function(){
        $('#display-filters').dialog('open');
        return false;
      });

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

      $("#dialog-admin-delete").dialog({
        autoOpen: false,
        modal: true,
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
      $("#dialog-admin-delete input[name='id[]']").attr('value', item_id);
      $("#dialog-admin-delete").dialog('open');
      return false;
    }
  </script>
  <?php
}

osc_add_hook('admin_header','customHead', 10);

$iDisplayLength = __get('iDisplayLength');
$aData = __get('aData');
$aRawRows = __get('aRawRows');
$sort = Params::getParam('sort');
$direction = Params::getParam('direction');

$columns = $aData['aColumns'];
$columnSources = (isset($aData['aColumnSources']) ? $aData['aColumnSources'] : array());
$sortableColumns = (isset($aData['aSortableColumns']) ? $aData['aSortableColumns'] : array());
$rows = $aData['aRows'];
$withFilters = __get('withFilters');

osc_current_admin_theme_path('parts/header.php');
?>

<form method="get" action="<?php echo osc_admin_base_url(true); ?>" id="display-filters" class="has-form-actions hide nocsrf">
  <input type="hidden" name="page" value="admins" />
  <input type="hidden" name="iDisplayLength" value="<?php echo $iDisplayLength;?>" />
  <input type="hidden" name="sort" value="<?php echo $sort; ?>" />
  <input type="hidden" name="direction" value="<?php echo $direction; ?>" />
  <div class="form-horizontal">
    <div class="grid-system">
      <div class="grid-row grid-50">
        <div class="row-wrapper">
          <div class="form-row">
            <div class="form-label"><?php _e('Username'); ?></div>
            <div class="form-controls">
              <input id="s_username" name="s_username" type="text" value="<?php echo osc_esc_html(Params::getParam('s_username')); ?>" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-label"><?php _e('Name'); ?></div>
            <div class="form-controls">
              <input id="s_name" name="s_name" type="text" value="<?php echo osc_esc_html(Params::getParam('s_name')); ?>" />
            </div>
          </div>
        </div>
      </div>
      <div class="grid-row grid-50">
        <div class="row-wrapper">
          <div class="form-row">
            <div class="form-label"><?php _e('E-mail'); ?></div>
            <div class="form-controls">
              <input id="s_email" name="s_email" type="text" value="<?php echo osc_esc_html(Params::getParam('s_email')); ?>" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-label"><?php _e('Type'); ?></div>
            <div class="form-controls">
              <select id="b_moderator" name="b_moderator">
                <option value="" <?php echo ((Params::getParam('b_moderator') == '') ? 'selected="selected"' : ''); ?>><?php _e('Choose an option'); ?></option>
                <option value="0" <?php echo ((Params::getParam('b_moderator') === '0') ? 'selected="selected"' : ''); ?>><?php _e('Administrator'); ?></option>
                <option value="1" <?php echo ((Params::getParam('b_moderator') === '1') ? 'selected="selected"' : ''); ?>><?php _e('Moderator'); ?></option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="clear"></div>
      <div class="form-row form-search-help"><?php _e('Use double quotes for exact match (ie "John"). By default search is wildcard'); ?></div>
      <div class="clear"></div>
    </div>
  </div>
  <div class="form-actions">
    <div class="wrapper">
      <input id="show-filters" type="submit" value="<?php echo osc_esc_html(__('Apply filters')); ?>" class="btn btn-submit" />
      <a class="btn" href="<?php echo osc_admin_base_url(true).'?page=admins'; ?>"><?php _e('Reset filters'); ?></a>
    </div>
  </div>
</form>

<h2 class="render-title"><?php _e('Manage admins'); ?> <a href="<?php echo osc_admin_base_url(true); ?>?page=admins&amp;action=add" class="btn btn-mini"><?php _e('Add new'); ?></a></h2>
<div class="relative">
  <div id="admins-toolbar" class="table-toolbar">
    <div class="float-right">
      <form method="get" action="<?php echo osc_admin_base_url(true); ?>" class="inline nocsrf">
        <?php foreach(Params::getParamsAsArray('get') as $key => $value) { ?>
          <?php if($key != 'iDisplayLength') { ?>
            <input type="hidden" name="<?php echo osc_esc_html(strip_tags($key)); ?>" value="<?php echo osc_esc_html(strip_tags($value)); ?>" />
          <?php } ?>
        <?php } ?>
        <select name="iDisplayLength" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();">
          <option value="10" <?php if(Params::getParam('iDisplayLength') == 10) echo 'selected'; ?>><?php printf(__('%d admins'), 10); ?></option>
          <option value="25" <?php if(Params::getParam('iDisplayLength') == 25) echo 'selected'; ?>><?php printf(__('%d admins'), 25); ?></option>
          <option value="50" <?php if(Params::getParam('iDisplayLength') == 50) echo 'selected'; ?>><?php printf(__('%d admins'), 50); ?></option>
          <option value="100" <?php if(Params::getParam('iDisplayLength') == 100) echo 'selected'; ?>><?php printf(__('%d admins'), 100); ?></option>
          <option value="500" <?php if(Params::getParam('iDisplayLength') == 500) echo 'selected'; ?>><?php printf(__('%d admins'), 500); ?></option>
        </select>
      </form>
      <form method="get" action="<?php echo osc_admin_base_url(true); ?>" id="shortcut-filters" class="inline nocsrf">
        <input type="hidden" name="page" value="admins" />
        <?php if($withFilters) { ?>
          <a id="btn-hide-filters" href="<?php echo osc_admin_base_url(true).'?page=admins'; ?>" class="btn"><?php _e('Reset filters'); ?></a>
        <?php } ?>
        <a id="btn-display-filters" href="#" class="btn <?php if($withFilters) { echo 'btn-red'; } ?>"><?php _e('Show filters'); ?></a>
        <input id="fSearch" name="sSearch" type="text" class="input-text input-actions" value="<?php echo osc_esc_html(Params::getParam('sSearch')); ?>" placeholder="<?php echo osc_esc_html(__('Search admin')); ?>" />
        <input type="submit" class="btn submit-right" value="<?php echo osc_esc_html(__('Find')); ?>">
      </form>
    </div>
  </div>
  <form class="" id="datatablesForm" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="page" value="admins" />
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
            <tr>
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
    $aData = __get("aData");
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
    <select name="iDisplayLength" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();">
      <option value="10" <?php if(Params::getParam('iDisplayLength') == 10) echo 'selected'; ?>><?php printf(__('%d admins'), 10); ?></option>
      <option value="25" <?php if(Params::getParam('iDisplayLength') == 25) echo 'selected'; ?>><?php printf(__('%d admins'), 25); ?></option>
      <option value="50" <?php if(Params::getParam('iDisplayLength') == 50) echo 'selected'; ?>><?php printf(__('%d admins'), 50); ?></option>
      <option value="100" <?php if(Params::getParam('iDisplayLength') == 100) echo 'selected'; ?>><?php printf(__('%d admins'), 100); ?></option>
      <option value="500" <?php if(Params::getParam('iDisplayLength') == 500) echo 'selected'; ?>><?php printf(__('%d admins'), 500); ?></option>
    </select>
  </form>
</div>
<form id="dialog-admin-delete" method="get" action="<?php echo osc_admin_base_url(true); ?>" class="has-form-actions hide" title="<?php echo osc_esc_html(__('Delete admin')); ?>">
  <input type="hidden" name="page" value="admins" />
  <input type="hidden" name="action" value="delete" />
  <input type="hidden" name="id[]" value="" />
  <div class="form-horizontal">
    <div class="form-row">
      <?php _e('Are you sure you want to delete this admin?'); ?>
    </div>
    <div class="form-actions">
      <div class="wrapper">
      <input id="admin-delete-submit" type="submit" value="<?php echo osc_esc_html(__('Delete')); ?>" class="btn btn-submit" />
      <a class="btn" href="javascript:void(0);" onclick="$('#dialog-admin-delete').dialog('close');"><?php _e('Cancel'); ?></a>
      </div>
    </div>
  </div>
</form>
<div id="dialog-bulk-actions" title="<?php _e('Bulk actions'); ?>" class="has-form-actions hide">
  <div class="form-horizontal">
    <div class="form-row"></div>
    <div class="form-actions">
      <div class="wrapper">
        <a id="bulk-actions-submit" href="javascript:void(0);" class="btn btn-submit"><?php echo osc_esc_html(__('Delete')); ?></a>
        <a id="bulk-actions-cancel" class="btn" href="javascript:void(0);"><?php _e('Cancel'); ?></a>
        <div class="clear"></div>
      </div>
    </div>
  </div>
</div>
<?php osc_current_admin_theme_path('parts/footer.php');
