<?php
if(!defined('ABS_PATH')) exit('ABS_PATH is not loaded. Direct access is not allowed.');

function addHelp() {
  echo '<p>' . __('Manage categories and subcategories. Use Quick management for drag-and-drop nesting.') . '</p>';
}
osc_add_hook('help_box','addHelp');

function customPageHeader() {
  $addUrl = osc_categories_admin_list_url(array('action' => 'add'));
  ?>
  <h1><?php _e('Categories'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
    <a href="<?php echo osc_esc_html($addUrl); ?>" class="btn btn-green ico ico-add-white float-right"><?php _e('Add category'); ?></a>
    <a href="<?php echo osc_esc_html(osc_admin_base_url(true) . '?page=categories&amp;action=reorder'); ?>" class="btn btn-white float-right"><?php _e('Quick management'); ?></a>
  </h1>
  <?php
}
osc_add_hook('admin_page_header','customPageHeader');

function customPageTitle($string) {
  return sprintf(__('Categories - %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');

function customHead() {
  ?>
  <script type="text/javascript">
    function categoriesReloadAfterReorder(id) {
      var url = window.location.href.split('#')[0];
      url = url.replace(/([?&])reorderId=\d+/g, '$1').replace(/[?&]$/, '');
      var sep = (url.indexOf('?') >= 0 ? '&' : '?');
      window.location.href = url + sep + 'reorderId=' + parseInt(id, 10);
    }

    function order_up(id) {
      $('#datatables_list_processing').show();
      $.ajax({
        url: "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=order_category&id="+id+"&order=up&<?php echo osc_csrf_token_url(); ?>",
        success: function(res) {
          categoriesReloadAfterReorder(id);
        },
        error: function(){
          $('#datatables_list_processing').hide();
        }
      });
    }

    function order_down(id) {
      $('#datatables_list_processing').show();
      $.ajax({
        url: "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=order_category&id="+id+"&order=down&<?php echo osc_csrf_token_url(); ?>",
        success: function(res) {
          categoriesReloadAfterReorder(id);
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
        $('.col-bulkactions input').each(function() {
          this.checked = (isChecked == 1);
        });
      });

      $("#dialog-category-delete").dialog({ autoOpen: false, modal: true });
      $("#dialog-bulk-actions").dialog({ autoOpen: false, modal: true });
      $("#bulk-actions-submit").click(function() { $("#datatablesForm").submit(); });
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

    function delete_dialog(id) {
      $("#dialog-category-delete input[name='id']").val(id);
      $("#dialog-category-delete").dialog('open');
      return false;
    }
  </script>
  <?php
}
osc_add_hook('admin_header','customHead', 10);

$aData = __get('aData');
$aRawRows = __get('aRawRows');
$parent = (int)__get('parent');
$breadcrumb = __get('breadcrumb');
$listUrl = __get('list_url');
if($listUrl == '') {
  $listUrl = osc_categories_admin_list_url();
}
$sort = Params::getParam('sort');
$direction = Params::getParam('direction');
$reorderId = (int)Params::getParam('reorderId');
$columns = $aData['aColumns'];
$columnSources = (isset($aData['aColumnSources']) ? $aData['aColumnSources'] : array());
$sortableColumns = (isset($aData['aSortableColumns']) ? $aData['aSortableColumns'] : array());
$rows = $aData['aRows'];
$withFilters = __get('withFilters');
$resetUrl = osc_categories_admin_list_url(array('sSearch' => '', 'iPage' => 1));
?>

<?php osc_current_admin_theme_path('parts/header.php'); ?>

<h2 class="render-title">
  <?php
  $crumbs = array();
  $crumbs[] = '<a href="' . osc_esc_html(osc_categories_admin_list_url(array('parent' => '', 'iPage' => 1))) . '">' . __('All categories') . '</a>';
  if(is_array($breadcrumb) && count($breadcrumb) > 0) {
    $last = count($breadcrumb) - 1;
    foreach($breadcrumb as $i => $cat) {
      $name = osc_category_row_name($cat);
      if($i < $last) {
        $crumbs[] = '<a href="' . osc_esc_html(osc_categories_admin_list_url(array('parent' => (int)$cat['pk_i_id'], 'iPage' => 1))) . '">' . osc_esc_html($name) . '</a>';
      } else {
        $crumbs[] = osc_esc_html($name);
      }
    }
  }
  echo implode(' &gt; ', $crumbs);
  ?>
</h2>

<div class="relative">
  <div id="categories-toolbar" class="table-toolbar">
    <div class="float-right">
      <form method="get" action="<?php echo osc_admin_base_url(true); ?>" class="inline nocsrf">
        <?php foreach(Params::getParamsAsArray('get') as $key => $value) { ?>
        <?php if($key != 'iDisplayLength') { ?>
        <input type="hidden" name="<?php echo osc_esc_html(strip_tags($key)); ?>" value="<?php echo osc_esc_html(strip_tags($value)); ?>" />
        <?php } } ?>
        <select name="iDisplayLength" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();">
          <option value="10" <?php if(Params::getParam('iDisplayLength') == 10) echo 'selected'; ?>><?php printf(__('%d categories'), 10); ?></option>
          <option value="25" <?php if(Params::getParam('iDisplayLength') == 25) echo 'selected'; ?>><?php printf(__('%d categories'), 25); ?></option>
          <option value="50" <?php if(Params::getParam('iDisplayLength') == 50) echo 'selected'; ?>><?php printf(__('%d categories'), 50); ?></option>
          <option value="100" <?php if(Params::getParam('iDisplayLength') == 100) echo 'selected'; ?>><?php printf(__('%d categories'), 100); ?></option>
        </select>
      </form>
      <form method="get" action="<?php echo osc_admin_base_url(true); ?>" id="shortcut-filters" class="inline nocsrf">
        <input type="hidden" name="page" value="categories" />
        <?php if($parent > 0) { ?><input type="hidden" name="parent" value="<?php echo $parent; ?>" /><?php } ?>
        <?php foreach(Params::getParamsAsArray('get') as $key => $value) { ?>
        <?php if(!in_array($key, array('page', 'sSearch', 'action'), true)) { ?>
        <input type="hidden" name="<?php echo osc_esc_html(strip_tags($key)); ?>" value="<?php echo osc_esc_html(strip_tags($value)); ?>" />
        <?php } } ?>
        <?php if($withFilters) { ?>
        <a id="btn-hide-filters" href="<?php echo osc_esc_html($resetUrl); ?>" class="btn btn-hide-filters"><?php _e('Reset search'); ?></a>
        <?php } ?>
        <input name="sSearch" type="text" class="input-text input-actions" value="<?php echo osc_esc_html(Params::getParam('sSearch')); ?>" placeholder="<?php echo osc_esc_html(__('Search categories')); ?>" />
        <input type="submit" class="btn submit-right" value="<?php echo osc_esc_html(__('Find')); ?>">
      </form>
    </div>
  </div>

  <form id="datatablesForm" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <?php echo osc_csrf_token_form(); ?>
    <input type="hidden" name="page" value="categories" />
    <?php if($parent > 0) { ?><input type="hidden" name="parent" value="<?php echo $parent; ?>" /><?php } ?>
    <?php if(Params::getParam('iDisplayLength') != '') { ?><input type="hidden" name="iDisplayLength" value="<?php echo (int)Params::getParam('iDisplayLength'); ?>" /><?php } ?>
    <?php if(Params::getParam('sort') != '') { ?><input type="hidden" name="sort" value="<?php echo osc_esc_html(Params::getParam('sort')); ?>" /><?php } ?>
    <?php if(Params::getParam('direction') != '') { ?><input type="hidden" name="direction" value="<?php echo osc_esc_html(Params::getParam('direction')); ?>" /><?php } ?>
    <?php if(Params::getParam('iPage') != '') { ?><input type="hidden" name="iPage" value="<?php echo (int)Params::getParam('iPage'); ?>" /><?php } ?>
    <?php if(Params::getParam('sSearch') != '') { ?><input type="hidden" name="sSearch" value="<?php echo osc_esc_html(Params::getParam('sSearch')); ?>" /><?php } ?>

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
          <?php foreach($rows as $key => $row) {
            $rowId = (isset($aRawRows[$key]['pk_i_id']) ? (int)$aRawRows[$key]['pk_i_id'] : 0);
            $rowClass = osc_apply_filter('datatable_categories_class', array(), isset($aRawRows[$key]) ? $aRawRows[$key] : array(), $row);
            if($reorderId > 0 && $rowId === $reorderId) {
              $rowClass[] = 'row-reordered';
            }
            $rowClassAttr = trim(implode(' ', $rowClass));
          ?>
            <tr<?php if($rowClassAttr != '') { echo ' class="' . osc_esc_html($rowClassAttr) . '"'; } ?> data-row-id="<?php echo $rowId; ?>">
              <?php foreach($row as $k => $v) { ?>
                <td class="col-<?php echo $k; ?>"><?php echo $v; ?></td>
              <?php } ?>
            </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td colspan="<?php echo max(1, count($columns)); ?>" class="text-center"><p><?php _e('No data available in table'); ?></p></td>
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

<div id="dialog-category-delete" class="has-form-actions hide" title="<?php echo osc_esc_html(__('Delete category')); ?>">
  <form method="get" action="<?php echo osc_admin_base_url(true); ?>">
    <input type="hidden" name="page" value="categories" />
    <input type="hidden" name="action" value="delete" />
    <input type="hidden" name="id" value="" />
    <?php echo osc_csrf_token_form(); ?>
    <?php if($parent > 0) { ?><input type="hidden" name="parent" value="<?php echo $parent; ?>" /><?php } ?>
    <?php if(Params::getParam('iDisplayLength') != '') { ?><input type="hidden" name="iDisplayLength" value="<?php echo (int)Params::getParam('iDisplayLength'); ?>" /><?php } ?>
    <?php if(Params::getParam('sort') != '') { ?><input type="hidden" name="sort" value="<?php echo osc_esc_html(Params::getParam('sort')); ?>" /><?php } ?>
    <?php if(Params::getParam('direction') != '') { ?><input type="hidden" name="direction" value="<?php echo osc_esc_html(Params::getParam('direction')); ?>" /><?php } ?>
    <?php if(Params::getParam('iPage') != '') { ?><input type="hidden" name="iPage" value="<?php echo (int)Params::getParam('iPage'); ?>" /><?php } ?>
    <?php if(Params::getParam('sSearch') != '') { ?><input type="hidden" name="sSearch" value="<?php echo osc_esc_html(Params::getParam('sSearch')); ?>" /><?php } ?>
    <div class="form-horizontal">
      <div class="form-row"><?php _e('<strong>WARNING</strong>: This will also delete the listings under that category. This action cannot be undone. Are you sure you want to continue?'); ?></div>
      <div class="form-actions">
        <div class="wrapper">
          <input type="submit" class="btn btn-red" value="<?php echo osc_esc_html(__('Delete')); ?>" />
          <a class="btn" href="javascript:void(0);" onclick="$('#dialog-category-delete').dialog('close');"><?php _e('Cancel'); ?></a>
        </div>
      </div>
    </div>
  </form>
</div>

<div id="dialog-bulk-actions" title="<?php _e('Bulk actions'); ?>" class="has-form-actions hide">
  <div class="form-horizontal">
    <div class="form-row"></div>
    <div class="form-actions">
      <div class="wrapper">
        <a id="bulk-actions-submit" href="javascript:void(0);" class="btn btn-submit"><?php echo osc_esc_html(__('Apply')); ?></a>
        <a id="bulk-actions-cancel" class="btn" href="javascript:void(0);"><?php _e('Cancel'); ?></a>
      </div>
    </div>
  </div>
</div>

<?php osc_current_admin_theme_path('parts/footer.php');
