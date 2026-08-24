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
  echo '<p>' . __("Install or uninstall the plugins available in your installation. In some cases, you'll have to configure the plugin in order to get it to work.") . '</p>';
}

osc_add_hook('help_box','addHelp');

if(!function_exists('addBodyClass')){
  function addBodyClass($array){
    $array[] = 'plugins-home';
    return $array;
  }
}

osc_add_filter('admin_body_class','addBodyClass');


function customPageHeader() { 
  ?>
  <h1><?php _e('Manage Plugins'); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
    <a href="<?php echo osc_admin_base_url(true); ?>?page=plugins&amp;action=add" class="btn btn-green ico ico-add-white float-right"><?php _e('Add plugin'); ?></a>
  </h1>
  </div>
  <?php osc_show_flash_message('admin'); ?>
  <?php if( Params::getParam('error') != '' ) { ?>
    <!-- flash message -->
    <div class="flashmessage flashmessage-error" style="display:block">
      <?php _e("Plugin couldn't be installed because it triggered a <strong>fatal error</strong>"); ?>
      <a class="btn ico btn-mini ico-close">x</a>
      <iframe style="border:0;" width="100%" height="60" src="<?php echo osc_admin_base_url(true); ?>?page=plugins&amp;action=error_plugin&amp;plugin=<?php echo Params::getParam('error'); ?>"></iframe>
    <!-- /flash message -->
  <?php } ?>
<?php
}
osc_add_hook('admin_page_header','customPageHeader');

function customPageTitle($string) {
  return sprintf(__('Plugins - %s'), $string);
}
osc_add_filter('admin_title', 'customPageTitle');


//customize Head
function customHead() { ?>
  <script type="text/javascript">
    $(document).ready(function(){
      omg_plugins_update_list();
      
      $('body').on('click', 'a.plugin-btn', function(e) {
        e.preventDefault();
        document.location.hash = $(this).attr('href');
        omg_plugins_update_list($(this).attr('href'));
      });

      $('input:hidden[name="installed"]').each(function() {
        $(this).parent().parent().children().css('background', 'none');
        if( $(this).val() == '1' ) {
          if( $(this).attr("enabled") == 1 ) {
            $(this).parent().parent().css('background-color', '#EDFFDF');
          } else {
            $(this).parent().parent().css('background-color', '#FFFFDF');
          }
        } else {
          $(this).parent().parent().css('background-color', '#FFF0DF');
        }
      });

      // dialog delete
      $("#dialog-uninstall").dialog({
        autoOpen: false,
        modal: true,
        title: '<?php echo osc_esc_js( __('Uninstall plugin') ); ?>'
      });

      $('.plugin-tooltip').each(function(){
        $(this).osc_tooltip('<?php echo osc_esc_js(__('Go to support forums')); ?>',{layout:'gray-tooltip',position:{x:'right',y:'middle'}});
      });


      // dialog bulk actions
      $("#dialog-bulk-actions").dialog({
        autoOpen: false,
        modal: true
      });
      
      $("#bulk-actions-submit").click(function() {
        if($("#bulk-actions-submit").prop("clicked")==false) {
          $("#bulk-actions-submit").prop("clicked", true);
          $("#datatablesFormPlugin").submit();
        }
      });
      
      $("#bulk-actions-cancel").click(function() {
        $("#datatablesFormPlugin").attr('data-dialog-open', 'false');
        $('#dialog-bulk-actions').dialog('close');
      });
      
      // dialog bulk actions function
      $("#datatablesFormPlugin").submit(function() {
        if( $("#bulk_actions option:selected").val() == "" ) {
          return false;
        }

        if( $("#datatablesFormPlugin").attr('data-dialog-open') == "true" ) {
          return true;
        }

        $("#dialog-bulk-actions .form-row").html($("#bulk_actions option:selected").attr('data-dialog-content'));
        $("#bulk-actions-submit").html($("#bulk_actions option:selected").text());
        $("#datatablesFormPlugin").attr('data-dialog-open', 'true');
        $("#bulk-actions-submit").prop("clicked", false);
        $("#dialog-bulk-actions").dialog('open');
        return false;
      });
      
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
    });

    // dialog delete function
    function uninstall_dialog(plugin, title) {
      $("#dialog-uninstall input[name='plugin']").attr('value', plugin);
      $("#dialog-uninstall").dialog('option', 'title', title);
      $("#dialog-uninstall").dialog('open');
      return false;
    }
    
    function omg_plugins_update_list(frag = -1) {
      var boxId = '';
      
      if(frag === -1) {
        var frag = window.location.hash;
      }
      
      if(window.location.hash !== '') {
        var boxId = (window.location.hash).substring(1);
      }
      
      if(boxId === 'update-plugins') {
        $('div.isTab#upload-plugins, a.update-plugins-button, #plg-show-bot').hide(0);
        $('div.isTab#update-plugins, a.upload-plugins-button').show(0);
      } else {
        $('div.isTab#upload-plugins, a.update-plugins-button, #plg-show-bot').show(0);
        $('div.isTab#update-plugins, a.upload-plugins-button').hide(0);      
      }
    }
  </script>
  <?php
}
osc_add_hook('admin_header','customHead', 10);

$iDisplayLength = __get('iDisplayLength');
$aData      = __get('aPlugins');

$tab_index = 2;
?>


<?php osc_current_admin_theme_path( 'parts/header.php' ); ?>

<div id="plugins-list">
  <?php
    $aPluginsToUpdate = json_decode(osc_get_preference('plugins_to_update'));
    $bPluginsToUpdate = is_array($aPluginsToUpdate) ? true : false;
    
    if($bPluginsToUpdate && count($aPluginsToUpdate) > 0) {
      $tab_index = 0;
    }
    
    $search_term = trim(osc_esc_html(Params::getParam('sSearch')));
    $search_term = ($search_term != '' ? $search_term : osc_get_session('pluginSearchTerm'));
  ?>
  
  <div class="display-select-top">
    <form method="get" action="<?php echo osc_admin_base_url(true); ?>" class="inline nocsrf" id="shortcut-filters">
      <input type="hidden" name="pluginSearchSubmit" value="1"/>
      
      <?php foreach(Params::getParamsAsArray('get') as $key => $value) { ?>
        <?php if(!in_array($key, array('pluginSearchSubmit', 'iDisplayLength', 'sSearch'))) { ?>
          <input type="hidden" name="<?php echo osc_esc_html(strip_tags($key)); ?>" value="<?php echo osc_esc_html(strip_tags($value)); ?>" />
        <?php } ?>
      <?php } ?>
      
      <input type="text" name="sSearch" id="fPattern" class="input-text input-actions" value="<?php echo $search_term; ?>" placeholder="<?php echo osc_esc_html(__('Search for plugin')); ?>" />
      <input type="submit" class="btn submit-right" value="<?php echo osc_esc_html(__('Find')); ?>">
      
      <select name="iDisplayLength" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();" >
        <option value="10" <?php if( Params::getParam('iDisplayLength') == 10 ) echo 'selected'; ?> ><?php printf(__('%d plugins'), 10); ?></option>
        <option value="25" <?php if( Params::getParam('iDisplayLength') == 25 ) echo 'selected'; ?> ><?php printf(__('%d plugins'), 25); ?></option>
        <option value="50" <?php if( Params::getParam('iDisplayLength') == 50 ) echo 'selected'; ?> ><?php printf(__('%d plugins'), 50); ?></option>
        <option value="100" <?php if( Params::getParam('iDisplayLength') == 100 ) echo 'selected'; ?> ><?php printf(__('%d plugins'), 100); ?></option>
        <option value="500" <?php if( Params::getParam('iDisplayLength') == 500 ) echo 'selected'; ?> ><?php printf(__('%d plugins'), 500); ?></option>
      </select>
    </form>
  </div>
    
  <form class="" id="datatablesFormPlugin" action="<?php echo osc_admin_base_url(true); ?>?page=plugins" method="post" data-dialog-open="false">
    <input type="hidden" name="action" value="bulk_actions" />
    
    <div id="bulk-actions">
      <label>
        <?php osc_print_bulk_actions('bulk_actions', 'bulk_actions', __get('bulk_options'), 'select-box-extra'); ?>
        <input type="submit" id="bulk_apply" class="btn" value="<?php echo osc_esc_html( __('Apply') ); ?>" />
      </label>
      
      <a href="#" class="btn btn-submit plugin-btn upload-plugins-button" style="display:none"><?php _e('Show all plugins'); ?></a>
      
      <?php if($bPluginsToUpdate && count($aPluginsToUpdate) > 0) { ?>
        <a href="#update-plugins" class="btn btn-submit plugin-btn update-plugins-button"><?php _e('Show plugins to update'); ?></a>
      <?php } ?>
    </div>  

    
    <div class="isTab" id="upload-plugins">
      <table class="table" cellpadding="0" cellspacing="0">
        <thead>
          <tr>
            <th class="col-bulkactions "><input id="check_all" type="checkbox"></th>
            <th><?php _e('Name'); ?></th>
            <th colspan=""><?php _e('Description'); ?></th>
            <th> &nbsp; </th>
            <th> &nbsp; </th>
            <th> &nbsp; </th>
            <th> &nbsp; </th>
            <th> &nbsp; </th>
          </tr>
        </thead>
        <tbody>
        <?php if(is_array($aData['aaData']) && count($aData['aaData'])>0) { ?>
          <?php foreach( $aData['aaData'] as $array) { ?>
            <tr>
              <?php foreach($array as $key => $value) { ?>
                <td class="col-<?php echo $key; ?>">
                  <?php echo $value; ?>
                </td>
              <?php } ?>
            </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td colspan="8" class="text-center">
              <p style="padding:20px 0;margin:0;"><?php _e('No plugins has been found'); ?></p>
            </td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
    </div>
  </form>
  
  
  <div id="plg-show-bot">
    <?php
      function showingResults(){
        $aData = __get('aPlugins');
        echo '<ul class="showing-results"><li><span>'.osc_pagination_showing((Params::getParam('iPage')-1)*$aData['iDisplayLength']+1, ((Params::getParam('iPage')-1)*$aData['iDisplayLength'])+count(is_array($aData['aaData']) ? $aData['aaData'] : array()), $aData['iTotalDisplayRecords']).'</span></li></ul>';
      }
      
      osc_add_hook('before_show_pagination_admin','showingResults');
      osc_show_pagination_admin($aData);
    ?>

    <div class="display-select-bottom">
      <form method="get" action="<?php echo osc_admin_base_url(true); ?>"  class="inline nocsrf">
        <?php foreach( Params::getParamsAsArray('get') as $key => $value ) { ?>
          <?php if( $key != 'iDisplayLength' ) { ?>
            <input type="hidden" name="<?php echo osc_esc_html(strip_tags($key)); ?>" value="<?php echo osc_esc_html(strip_tags($value)); ?>" />
          <?php } ?>
        <?php } ?>
        
        <select name="iDisplayLength" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();" >
          <option value="10" <?php if( Params::getParam('iDisplayLength') == 10 ) echo 'selected'; ?> ><?php printf(__('%d plugins'), 10); ?></option>
          <option value="25" <?php if( Params::getParam('iDisplayLength') == 25 ) echo 'selected'; ?> ><?php printf(__('%d plugins'), 25); ?></option>
          <option value="50" <?php if( Params::getParam('iDisplayLength') == 50 ) echo 'selected'; ?> ><?php printf(__('%d plugins'), 50); ?></option>
          <option value="100" <?php if( Params::getParam('iDisplayLength') == 100 ) echo 'selected'; ?> ><?php printf(__('%d plugins'), 100); ?></option>
          <option value="500" <?php if( Params::getParam('iDisplayLength') == 500 ) echo 'selected'; ?> ><?php printf(__('%d plugins'), 500); ?></option>
        </select>
      </form>
    </div>
  </div>
  
  <?php if($bPluginsToUpdate && count($aPluginsToUpdate) > 0) { ?>
    <div class="isTab" id="update-plugins" style="display:none;">
      <?php
        $aIndex = array();
        if($bPluginsToUpdate) {
          $array_aux  = array_filter(array_column($aData['aaInfo'], 'product_key'));

          foreach($aPluginsToUpdate as $product_key) {
            $key = array_search($product_key, $array_aux, true);

            if($key!==false) {
              $aIndex[]   = $aData['aaData'][$key];
            }
          }
        }
      ?>

      <table class="table" cellpadding="0" cellspacing="0">
        <thead>
          <tr>
            <th><?php _e('Name'); ?></th>
            <th colspan=""><?php _e('Description'); ?></th>
            <th> &nbsp; </th>
            <th> &nbsp; </th>
            <th> &nbsp; </th>
            <th> &nbsp; </th>
          </tr>
        </thead>
        <tbody>
          <?php if(is_array($aIndex) && count($aIndex)>0) { ?>
            <?php foreach($aIndex as $array) { ?>
              <tr>
                <?php foreach($array as $key => $value) { ?>
                  <?php if($key !== 'bulkactions') { ?>
                    <td>
                      <?php echo $value; ?>
                    </td>
                  <?php } ?>
                <?php } ?>
              </tr>
            <?php } ?>
          <?php } else { ?>
            <tr>
              <td colspan="8" class="text-center">
                <p style="padding:20px 0;margin:0;"><?php _e('No plugins has been found'); ?></p>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  <?php } ?>
  
  <div id="market_installer" class="has-form-actions hide">
    <form name="mkti" action="<?php echo osc_admin_base_url(true); ?>?page=ajax&action=market&<?php echo osc_csrf_token_url(); ?>" method="post">
      <input type="hidden" name="section" value="plugins" />
      <input type="hidden" name="redirect" value="1" />
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
              <td><?php _e('Changes'); ?></td>
              <td><span id="market_changes"><?php _e("Loading data"); ?></span></td>
            </tr>
            <tr class="even">
              <td><?php _e('Osclass'); ?></td>
              <td><span id="market_osclass_version"><?php _e("Loading data"); ?></span></td>
            </tr>
            <tr>
              <td><?php _e('URL'); ?></td>
              <td><span id="market_url_span"><a id="market_url" href="#"><?php _e("Download manually"); ?></a></span></td>
            </tr>
          </tbody>
        </table>
        <div class="clear"></div>
      </div>
      <div class="form-actions">
        <div class="wrapper">
          <button id="market_install" class="btn btn-submit"><?php _e('Continue install'); ?></button>
          <button id="market_cancel" class="btn"><?php _e('Cancel'); ?></button>
        </div>
      </div>
    </form>
  </div>
</div>
<form id="dialog-uninstall" method="get" action="<?php echo osc_admin_base_url(true); ?>" class="has-form-actions hide">
  <input type="hidden" name="page" value="plugins" />
  <input type="hidden" name="action" value="uninstall" />
  <input type="hidden" name="plugin" value="" />
  <div class="form-horizontal">
    <div class="form-row">
      <?php _e('This action can not be undone. Uninstalling plugins may result in a permanent loss of data. Are you sure you want to continue?'); ?>
    </div>
    <div class="form-actions">
      <div class="wrapper">
        <input id="uninstall-submit" type="submit" value="<?php echo osc_esc_html( __('Uninstall') ); ?>" class="btn btn-submit" />
        <a class="btn" href="javascript:void(0);" onclick="$('#dialog-uninstall').dialog('close');"><?php _e('Cancel'); ?></a>
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
<script>
  $(function() {
    var tab_id = decodeURI(self.document.location.hash.substring(1));
    if(tab_id != '') {
      $( "#tabs" ).tabs({ active: <?php echo $tab_index; ?> });
      $('html, body').animate({scrollTop:0}, 'slow');
    } else {
      $( "#tabs" ).tabs({ active: -1 });
    }

    $("#market_cancel").on("click", function(){
      $(".ui-dialog-content").dialog("close");
      return false;
    });

    $("#market_install").on("click", function(){
      $(".ui-dialog-content").dialog("close");
      $('<div id="downloading"><div class="osc-modal-content"><img class="ui-download-loading" src="<?php echo osc_current_admin_theme_url(); ?>images/spinner.gif" alt="loading..."/><?php echo osc_esc_js(__('Please wait until the download is completed')); ?></div></div>').dialog({title:'<?php echo osc_esc_js(__('Downloading')); ?>...',modal:true});
 
      $.ajax({
        url: "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=market&<?php echo osc_csrf_token_url(); ?>",
        data: {"market_product_key" : $("#market_product_key").attr("value"), "section" : 'plugins'},
        dataType: 'json',
        success: function(data){
          var content = '';

          if(data.error == 0) { // no errors
            content += oscEscapeHTML(data.message);
            content += '<h3><?php echo osc_esc_js(__('Plugin has been downloaded correctly.')); ?></h3><br/>';
            content += "<p>";
            content += '<a class="btn btn-mini btn-green" href="<?php echo osc_admin_base_url(true); ?>?page=plugins&marketError='+data.error+'&message='+oscEscapeHTML(data.message)+'&slug='+oscEscapeHTML(data.data['download'])+'"><?php echo osc_esc_js(__('Ok')); ?></a>';
            content += '<a class="btn btn-mini" href="javascript:location.reload(true)"><?php echo osc_esc_js(__('Close')); ?></a>';
            content += "</p>";
          } else {
            content += '<p>' + oscEscapeHTML(data.message) + '</p><p>&nbsp;</p>';
            content += '<a class="btn btn-mini" href="javascript:location.reload(true)"><?php echo osc_esc_js(__('Close')); ?></a>';
          }
          $("#downloading .osc-modal-content").html(content);          
        },
        error: function(data){
          console.log(data);
          var content = '';

          if(data.error == 0) { // no errors
            content += oscEscapeHTML(data.message);
            content += '<h3><?php echo osc_esc_js(__('Plugin has been downloaded correctly.')); ?></h3><br/>';
            content += "<p>";
            content += '<a class="btn btn-mini btn-green" href="<?php echo osc_admin_base_url(true); ?>?page=plugins&marketError='+data.error+'&message='+oscEscapeHTML(data.message)+'&slug='+oscEscapeHTML(data.data['download'])+'"><?php echo osc_esc_js(__('Ok')); ?></a>';
            content += '<a class="btn btn-mini" href="javascript:location.reload(true)"><?php echo osc_esc_js(__('Close')); ?></a>';
            content += "</p>";
          } else {
            content += '<p>' + oscEscapeHTML(data.message) + '</p><p>&nbsp;</p>';
            content += '<a class="btn btn-mini" href="javascript:location.reload(true)"><?php echo osc_esc_js(__('Close')); ?></a>';
          }
          $("#downloading .osc-modal-content").html(content);  
        }
      });
 
      return false;
    });
  });


  $('.market-popup').on('click',function(e){
    e.preventDefault();

    $.getJSON(
      "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=check_market",
      {"code" : $(this).attr('href').replace('#',''), 'section' : 'product'},
      function(data){
        if(data!=null) {
          if(data.s_osc_version_from == '' && data.s_osc_version_to == '') {
            var version_compatible = '<?php echo osc_esc_js(__('All versions')); ?>';
          } else if(data.s_osc_version_to == '') {
            var version_compatible = data.s_osc_version_from + ' <?php echo osc_esc_js(__('and higher')); ?>';
          } else if(data.s_osc_version_from == '') {
            var version_compatible = '<?php echo osc_esc_js(__('up to')); ?> ' + data.s_osc_version_to;
          }

          //$("#market_thumb").attr('src',data.s_thumbnail);
          $("#market_product_key").attr("value", data.product_key);
          $("#market_name").text(data.name);
          $("#market_version").text(data.s_version);
          $("#market_changes").text(data.s_comment);
          $("#market_osclass_version").text(version_compatible);
          $("#market_url").attr('href',data.download);
          $('#market_install').text("<?php echo osc_esc_js( __('Update') ); ?>");

          var dialogWidth = 485;
          
          if($(window).width() < 525) {
            dialogWidth = $(window).width() - 40;
          }
          
          $('#market_installer').dialog({
            modal: true,
            title: '<?php echo osc_esc_js( __('Update plugin from OsclassPoint') ); ?>',
            width: dialogWidth
          });
        }
      }
    );

    return false;
  });
  
  function delete_plugin(plugin) {
    var x = confirm('<?php echo osc_esc_js(__('You are about to delete the files of the plugin. Do you want to continue?'))?>');
    if(x) {
      window.location = '<?php echo osc_admin_base_url(true).'?page=plugins&action=delete&'.osc_csrf_token_url().'&plugin='; ?>'+plugin;
    }
  }
</script>

<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>