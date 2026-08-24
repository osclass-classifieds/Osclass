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
  echo '<p>' . __('Manage the images that users have uploaded along with their listings. You can delete them without deleting the whole listing if the image is inappropriate or doesn’t match the listing.') . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader(){
  ?>
  <h1>
    <?php _e('Listings'); ?>
    <a href="<?php echo osc_admin_base_url(true) . '?page=settings&action=media'; ?>" class="btn ico ico-32 ico-engine float-right"></a>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Manage media - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');


//customize Head
function customHead() {
  ?>
  <script type="text/javascript">
    $(document).ready(function(){
      // check_all bulkactions
      $("#check_all").change(function(){
        var isChecked = $(this).prop("checked");
        $('.col-bulkactions input').each( function() {
          if(isChecked == 1 ) {
            this.checked = true;
          } else {
            this.checked = false;
          }
        });
      });

      // dialog delete
      $("#dialog-media-delete").dialog({
        autoOpen: false,
        modal: true,
        title: '<?php echo osc_esc_js( __('Delete media') ); ?>'
      });

      // dialog bulk actions
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
      // dialog bulk actions function
      $("#datatablesForm").submit(function() {
        if($("#bulk_actions option:selected").val() == "" ) {
          return false;
        }

        if($("#datatablesForm").attr('data-dialog-open') == "true" ) {
          return true;
        }

        $("#dialog-bulk-actions .form-row").html($("#bulk_actions option:selected").attr('data-dialog-content'));
        $("#bulk-actions-submit").html($("#bulk_actions option:selected").text());
        $("#datatablesForm").attr('data-dialog-open', 'true');
        $("#dialog-bulk-actions").dialog('open');
        return false;
      });
    });

    // dialog delete function
    function delete_dialog(media_id) {
      $("#dialog-media-delete input[name='id[]']").attr('value', media_id);
      $("#dialog-media-delete").dialog('open');
      return false;
    }
  </script>
  <?php
}

osc_add_hook('admin_header','customHead', 10);


$aData = __get('aData');
$aRawRows = __get('aRawRows');
$sort = Params::getParam('sort');
$direction = Params::getParam('direction');

$columns = $aData['aColumns'];
$columnSources = (isset($aData['aColumnSources']) ? $aData['aColumnSources'] : array());
$sortableColumns = (isset($aData['aSortableColumns']) ? $aData['aSortableColumns'] : array());
$rows = $aData['aRows'];
$hasActiveFilters = false;
$filterExclude = array('page', 'action', 'iDisplayLength', 'sort', 'direction', 'iPage');

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
?>

<?php osc_current_admin_theme_path( 'parts/header.php' ); ?>
<h2 class="render-title"><?php _e('Manage media'); ?></h2>
<div class="relative">
  <div id="media-toolbar" class="table-toolbar">
    <div class="float-right">
      <form method="get" action="<?php echo osc_admin_base_url(true); ?>" class="inline nocsrf">
        <?php foreach(Params::getParamsAsArray('get') as $key => $value ) { ?>
          <?php if($key != 'iDisplayLength') { ?>
            <input type="hidden" name="<?php echo osc_esc_html(strip_tags($key)); ?>" value="<?php echo osc_esc_html(strip_tags($value)); ?>" />
          <?php } ?>
        <?php } ?>
        <select name="iDisplayLength" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();" >
          <option value="10" <?php if(Params::getParam('iDisplayLength') == 10 ) echo 'selected'; ?> ><?php printf(__('%d Files'), 10); ?></option>
          <option value="25" <?php if(Params::getParam('iDisplayLength') == 25 ) echo 'selected'; ?> ><?php printf(__('%d Files'), 25); ?></option>
          <option value="50" <?php if(Params::getParam('iDisplayLength') == 50 ) echo 'selected'; ?> ><?php printf(__('%d Files'), 50); ?></option>
          <option value="100" <?php if(Params::getParam('iDisplayLength') == 100 ) echo 'selected'; ?> ><?php printf(__('%d Files'), 100); ?></option>
        </select>
      </form>
      <?php if($hasActiveFilters) { ?>
        <a id="btn-hide-filters" href="<?php echo osc_admin_base_url(true); ?>?page=media" class="btn"><?php _e('Reset filters'); ?></a>
      <?php } ?>
      <form method="get" action="<?php echo osc_admin_base_url(true); ?>" id="shortcut-filters" class="inline nocsrf">
        <input type="hidden" name="page" value="media" />
        <?php if(Params::getParam('itemId') != '') { ?><input type="hidden" name="itemId" value="<?php echo (int)Params::getParam('itemId'); ?>" /><?php } else if(Params::getParam('resourceId') != '') { ?><input type="hidden" name="itemId" value="<?php echo (int)Params::getParam('resourceId'); ?>" /><?php } ?>
        <?php if(Params::getParam('extension') != '') { ?><input type="hidden" name="extension" value="<?php echo osc_esc_html(Params::getParam('extension')); ?>" /><?php } ?>
        <input type="hidden" name="iDisplayLength" value="<?php echo (int)Params::getParam('iDisplayLength'); ?>" />
        <input id="fPattern" type="text" name="sSearch" value="<?php echo osc_esc_html(Params::getParam('sSearch')); ?>" class="input-text input-actions" placeholder="<?php echo osc_esc_html(__('Search media')); ?>" />
        <input type="submit" class="btn submit-right" value="<?php echo osc_esc_html(__('Find')); ?>">
      </form>
      <?php if(Params::getParam('itemId') != '' || Params::getParam('resourceId') != '') { ?>
        <a href="<?php echo osc_admin_base_url(true); ?>?page=media" class="btn"><?php _e('All media'); ?></a>
      <?php } ?>
    </div>
  </div>
  <form class="manage-media" id="datatablesForm" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="page" value="media" />
    <input type="hidden" name="action" value="bulk_actions" />
    <div id="bulk-actions">
      <label>
        <?php osc_print_bulk_actions('bulk_actions', 'bulk_actions', __get('bulk_options'), 'select-box-extra'); ?>
        <input type="submit" id="bulk_apply" class="btn" value="<?php echo osc_esc_html( __('Apply') ); ?>" />
      </label>
    </div>

    <div class="table-contains-actions">
      <table class="table media-table" cellpadding="0" cellspacing="0">
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
      <div id="table-row-actions"></div> <!-- used for table actions -->
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
      <?php if($key != 'iDisplayLength') { ?>
        <input type="hidden" name="<?php echo osc_esc_html(strip_tags($key)); ?>" value="<?php echo osc_esc_html(strip_tags($value)); ?>" />
      <?php } ?>
    <?php } ?>
    <select name="iDisplayLength" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();" >
      <option value="10" <?php if(Params::getParam('iDisplayLength') == 10 ) echo 'selected'; ?> ><?php printf(__('%d Files'), 10); ?></option>
      <option value="25" <?php if(Params::getParam('iDisplayLength') == 25 ) echo 'selected'; ?> ><?php printf(__('%d Files'), 25); ?></option>
      <option value="50" <?php if(Params::getParam('iDisplayLength') == 50 ) echo 'selected'; ?> ><?php printf(__('%d Files'), 50); ?></option>
      <option value="100" <?php if(Params::getParam('iDisplayLength') == 100 ) echo 'selected'; ?> ><?php printf(__('%d Files'), 100); ?></option>
    </select>
  </form>
</div>
<form id="dialog-media-delete" method="get" action="<?php echo osc_admin_base_url(true); ?>" class="has-form-actions hide">
  <input type="hidden" name="page" value="media" />
  <input type="hidden" name="action" value="delete" />
  <input type="hidden" name="id[]" value="" />
  <div class="form-horizontal">
    <div class="form-row">
      <?php _e('Are you sure you want to delete this media file?'); ?>
    </div>
    <div class="form-actions">
      <div class="wrapper">
        <input id="media-delete-submit" type="submit" value="<?php echo osc_esc_html( __('Delete') ); ?>" class="btn btn-submit" />
        <a class="btn" href="javascript:void(0);" onclick="$('#dialog-media-delete').dialog('close');"><?php _e('Cancel'); ?></a>
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
