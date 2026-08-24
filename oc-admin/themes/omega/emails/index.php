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
  echo '<p>' . __("Modify the emails your site's users receive when they join your site, when someone shows interest in their ad, to recover their password... <strong>Be careful</strong>: don't modify any of the words that appear within brackets.") . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader(){
  ?>
  <h1><?php _e('Settings'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Email templates - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

function customHead() {
  ?>
  <script type="text/javascript">
    $(document).ready(function(){
      var testEmailId = 0;

      $('#dialog-test-it').dialog({
        autoOpen: false,
        modal: true,
        width: 360,
        minHeight: 42,
        title: '<?php echo osc_esc_js(__('Test email template')); ?>'
      });

      $('body').on('click', '.email-test-popup', function(e) {
        e.preventDefault();
        testEmailId = $(this).data('id');
        $('#dialog-test-it').dialog('open');
        return false;
      });

      $('#btn-test-it').click(function() {
        $.post('<?php echo osc_admin_base_url(true); ?>',
        {
          page: 'ajax',
          action: 'test_mail_template',
          id: testEmailId,
          email: $('input[name="test_email"]').val()
        },
        function(data) {
          alert(data.html);
        }, 'json');
        return false;
      });
    });
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

osc_current_admin_theme_path( 'parts/header.php' );
?>

<h2 class="render-title"><?php _e('Email templates'); ?></h2>
<div class="relative" id="emails-list">
  <div id="emails-toolbar" class="table-toolbar">
    <div class="float-right">
      <form method="get" action="<?php echo osc_admin_base_url(true); ?>" class="inline nocsrf">
        <?php foreach(Params::getParamsAsArray('get') as $key => $value) { ?>
          <?php if($key != 'iDisplayLength') { ?>
            <input type="hidden" name="<?php echo osc_esc_html(strip_tags($key)); ?>" value="<?php echo osc_esc_html(strip_tags($value)); ?>" />
          <?php } ?>
        <?php } ?>
        <select name="iDisplayLength" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();">
          <option value="10" <?php if(Params::getParam('iDisplayLength') == 10) echo 'selected'; ?>><?php printf(__('%d Templates'), 10); ?></option>
          <option value="25" <?php if(Params::getParam('iDisplayLength') == 25) echo 'selected'; ?>><?php printf(__('%d Templates'), 25); ?></option>
          <option value="50" <?php if(Params::getParam('iDisplayLength') == 50) echo 'selected'; ?>><?php printf(__('%d Templates'), 50); ?></option>
          <option value="100" <?php if(Params::getParam('iDisplayLength') == 100) echo 'selected'; ?>><?php printf(__('%d Templates'), 100); ?></option>
        </select>
      </form>
      <?php if($hasActiveFilters) { ?>
        <a id="btn-hide-filters" href="<?php echo osc_admin_base_url(true); ?>?page=emails" class="btn"><?php _e('Reset filters'); ?></a>
      <?php } ?>
      <form method="get" action="<?php echo osc_admin_base_url(true); ?>" id="shortcut-filters" class="inline nocsrf">
        <input type="hidden" name="page" value="emails" />
        <input type="hidden" name="iDisplayLength" value="<?php echo (int)$iDisplayLength; ?>" />
        <input id="fPattern" type="text" name="sSearch" value="<?php echo osc_esc_html(Params::getParam('sSearch')); ?>" class="input-text input-actions" placeholder="<?php echo osc_esc_html(__('Search template')); ?>" />
        <input type="submit" class="btn submit-right" value="<?php echo osc_esc_html(__('Find')); ?>">
      </form>
    </div>
  </div>

  <div class="table-contains-actions">
    <table class="table" cellpadding="0" cellspacing="0">
      <thead>
        <tr>
          <?php foreach($columns as $k => $v) {
            $sourceCol = (isset($columnSources[$k]) ? $columnSources[$k] : '');
            $isSortable = ((in_array($k, $sortableColumns, true) || strpos((string)$v, 'sort=') !== false) ? 'is-sortable' : '');
            echo '<th class="col-' . $k . ' ' . $isSortable . ' ' . ($sort == $k ? ($direction == 'desc' ? 'sort-desc' : 'sort-asc') : '') . '" data-source-col="' . osc_esc_html($sourceCol) . '">' . $v . '</th>';
          } ?>
        </tr>
      </thead>
      <tbody>
      <?php if(count($rows) > 0) { ?>
        <?php foreach($rows as $key => $row) { ?>
          <tr>
            <?php foreach($row as $k => $v) { ?>
              <?php if($k != 'id') { ?>
                <td class="col-<?php echo $k; ?>"><?php echo $v; ?></td>
              <?php } ?>
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
</div>
<?php
  function showingResultsEmails(){
    $aData = __get('aData');
    echo '<ul class="showing-results"><li><span>' . osc_pagination_showing((Params::getParam('iPage')-1) * $aData['iDisplayLength'] + 1, ((Params::getParam('iPage')-1) * $aData['iDisplayLength']) + count($aData['aRows']), $aData['iTotalDisplayRecords'], $aData['iTotalRecords']) . '</span></li></ul>';
  }
  osc_add_hook('before_show_pagination_admin', 'showingResultsEmails');
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
      <option value="10" <?php if(Params::getParam('iDisplayLength') == 10) echo 'selected'; ?>><?php printf(__('%d Templates'), 10); ?></option>
      <option value="25" <?php if(Params::getParam('iDisplayLength') == 25) echo 'selected'; ?>><?php printf(__('%d Templates'), 25); ?></option>
      <option value="50" <?php if(Params::getParam('iDisplayLength') == 50) echo 'selected'; ?>><?php printf(__('%d Templates'), 50); ?></option>
      <option value="100" <?php if(Params::getParam('iDisplayLength') == 100) echo 'selected'; ?>><?php printf(__('%d Templates'), 100); ?></option>
    </select>
  </form>
</div>
<div id="dialog-test-it" class="hide">
  <input type="text" name="test_email" class="input-actions" value="<?php echo osc_esc_html(osc_contact_email()); ?>"/>
  <input type="submit" id="btn-test-it" href="#" class="btn btn-green submit-right" value="<?php _e('Send email'); ?>"/>
</div>
<?php osc_current_admin_theme_path( 'parts/footer.php' );
