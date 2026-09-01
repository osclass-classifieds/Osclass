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
  echo '<p>' . __("Add, edit or delete the language in which your Osclass is displayed, both the part that's viewable by users and the admin panel.") . '</p>';
}

osc_add_hook('help_box','addHelp');


function customPageHeader(){ 
  ?>
  <h1><?php _e('Settings'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
    <a href="<?php echo osc_admin_base_url(true); ?>?page=languages&action=add" class="btn btn-green ico ico-add-white float-right" ><?php _e('Add language'); ?></a>
    <a href="<?php echo osc_admin_base_url(true); ?>?page=languages&action=sync" class="btn btn-white ico ico-sync float-right" ><?php _e('Synchronize'); ?></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Languages - %s'), $string);
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
          if( isChecked == 1 ) {
            this.checked = true;
          } else {
            this.checked = false;
          }
        });
      });

      // dialog delete
      $("#dialog-language-delete").dialog({
        autoOpen: false,
        modal: true,
        title: '<?php echo osc_esc_js( __('Delete language') ); ?>'
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
        if( $("#bulk_actions option:selected").val() == "" ) {
          return false;
        }

        if( $("#datatablesForm").attr('data-dialog-open') == "true" ) {
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
    function delete_dialog(item_id) {
      $("#dialog-language-delete input[name='id[]']").attr('value', item_id);
      $("#dialog-language-delete").dialog('open');
      return false;
    }
  </script>
  <?php
}
osc_add_hook('admin_header','customHead', 10);

$iDisplayLength = __get('iDisplayLength');
$aData      = __get('aLanguages');

osc_current_admin_theme_path( 'parts/header.php' );
?>

<div class="flashmessage flashmessage-info static-flash">
  <p class="info"><?php echo sprintf(__('Your default language is %s. Default language cannot be removed or disabled and can be changed in %s section.'), '<b>' . osc_language() . '</b>', '<a href="' . osc_admin_base_url(true) . '?page=settings">' . __('Settings > General') . '</a>'); ?></p>
</div>


<h2 class="render-title">
  <?php _e('Manage Languages'); ?> 
  <a href="<?php echo osc_admin_base_url(true); ?>?page=languages&action=add" class="btn btn-mini"><?php _e('Add new'); ?></a>
</h2>
<div class="relative">
  <div id="language-toolbar" class="table-toolbar">
    <div class="float-right">

    </div>
  </div>
  
  <form class="" id="datatablesForm" action="<?php echo osc_admin_base_url(true); ?>" method="post" data-dialog-open="false">
    <input type="hidden" name="page" value="languages" />
    
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
            <th>&nbsp;</th>
            <th><?php _e('Status'); ?></th>
            <th class="col-bulkactions"><input id="check_all" type="checkbox" /></th>
            <th><?php _e('Name'); ?></th>
            <th><?php _e('Short name'); ?></th>
            <th><?php _e('Description'); ?></th>
            <th><?php _e('Front-office'); ?></th>
            <th><?php _e('Back-office'); ?></th>
            <th><?php _e('Native loc.'); ?></th>
            <th><?php _e('Direction'); ?></th>
          </tr>
        </thead>
        
        <tbody>
          <?php if(count($aData['aaData'])>0) { ?>
            <?php foreach($aData['aaData'] as $array) { ?>
              <tr class="<?php echo $array['class']; unset($array['class']); ?>">
                <?php foreach($array as $key => $value) { ?>
                  <td class="col-<?php echo ($key <> '' ? $key : 'default'); ?>">
                    <?php echo $value; ?>
                  </td>
                <?php } ?>
              </tr>
            <?php } ?>
          <?php } else { ?>
            <tr>
              <td colspan="7" class="text-center">
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

<?php osc_show_pagination_admin($aData); ?>

<form id="dialog-language-delete" method="get" action="<?php echo osc_admin_base_url(true); ?>" class="has-form-actions hide">
  <input type="hidden" name="page" value="languages" />
  <input type="hidden" name="action" value="delete" />
  <input type="hidden" name="id[]" value="" />
  <div class="form-horizontal">
    <div class="form-row">
      <?php _e('Are you sure you want to delete this language?'); ?>
    </div>
    <div class="form-actions">
      <div class="wrapper">
      <input id="language-delete-submit" type="submit" value="<?php echo osc_esc_html( __('Delete') ); ?>" class="btn btn-submit" />
      <a class="btn" href="javascript:void(0);" onclick="$('#dialog-language-delete').dialog('close');"><?php _e('Cancel'); ?></a>
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

<div id="market_installer" class="has-form-actions hide">
  <form name="mkti" action="<?php echo osc_admin_base_url(true); ?>?page=ajax&action=market&<?php echo osc_csrf_token_url(); ?>" method="post">
    <input type="hidden" name="section" value="languages" />
    <input type="hidden" name="market_product_key" id="market_product_key" value="" />

    <div class="osc-modal-content-market">
      <table class="table" cellpadding="0" cellspacing="0">
        <tbody>
          <tr class="table-first-row">
            <td><?php _e('Name'); ?></td>
            <td><span id="market_name"><?php _e("Loading data"); ?></span></td>
          </tr>
          <tr class="even">
            <td><?php _e('Version'); ?></td>
            <td><span id="market_version"><?php _e("Loading data"); ?></span></td>
          </tr>
          <tr>
            <td><?php _e('Date'); ?></td>
            <td><span id="market_date"><?php _e("Loading data"); ?></span></td>
          </tr>
          <tr class="even">
            <td><?php _e('URL'); ?></td>
            <td><span id="market_url_span"><a id="market_url" href="#"><?php _e("Download manually"); ?></a></span></td>
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

        if(data.error == 0) { // no errors
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
            title: '<?php echo osc_esc_js( __('Update language from OsclassPoint') ); ?>',
            width: dialogWidth
          });
        }
      }
    );

    return false;
  });
</script>

<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>