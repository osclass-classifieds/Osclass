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

$action = Params::getParam('action');
$api_valid = __get('api_valid');

//customize Head
function customHead() { }
osc_add_hook('admin_header','customHead', 10);


function addHelp() {
  echo '<p>' . __("Download themes, plugins, languages or locations from OsclassPoint") . '</p>';
}
osc_add_hook('help_box','addHelp');

function customPageHeader2(){ 
  ?>
  <h1>
    <?php echo sprintf(__('%s on market'), __('Themes translations')); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader2');


function customPageTitle($string) {
  return sprintf(__('%s on market - %s'), __('Themes translations'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

$sort = Params::getParam('sort');
$sort = ($sort == '' ? 'latest' : $sort);
$pattern = urlencode(Params::getParam('pattern'));

$products_json = osc_file_get_contents(osc_language_url('', $pattern, $sort, 'themes'));
$products = json_decode($products_json, true);

$themes_all = WebThemes::newInstance()->getListThemes();

osc_current_admin_theme_path( 'market/header.php' ); 
?>

<div id="market-block" class="<?php echo osc_esc_html($action); ?>">
  <div id="mrkt" class="languages">
    <div class="search">
      <a href="<?php echo osc_admin_base_url(true); ?>?page=market&action=<?php echo $action; ?>&sort=latest" class="<?php if(Params::getParam('sort') == '' || Params::getParam('sort') == 'latest') { echo 'active'; } ?>"><?php _e('Latest'); ?></a>
      <a href="<?php echo osc_admin_base_url(true); ?>?page=market&action=<?php echo $action; ?>&sort=name" class="<?php if(Params::getParam('sort') == 'name') { echo 'active'; } ?>"><?php _e('By name'); ?></a>
      <a href="<?php echo osc_admin_base_url(true); ?>?page=market&action=<?php echo $action; ?>&sort=version" class="<?php if(Params::getParam('sort') == 'version') { echo 'active'; } ?>"><?php _e('By version'); ?></a>

      <form method="get" action="<?php echo osc_admin_base_url(true); ?>"  class="inline nocsrf">
        <input type="hidden" name="page" value="market" />

        <input type="text" name="pattern" class="input-text" value="<?php echo Params::getParam('pattern'); ?>" placeholder="<?php echo osc_esc_html(__('Search language...')); ?>"/>

        <select name="action" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();">
          <option value="languages" <?php if( Params::getParam('action') == 'languages' ) echo 'selected="selected"'; ?> ><?php _e('Osclass translations'); ?></option>
          <option value="languages-themes" <?php if( Params::getParam('action') == 'languages-themes' ) echo 'selected="selected"'; ?> ><?php _e('Themes translations'); ?></option>
          <option value="languages-plugins" <?php if( Params::getParam('action') == 'languages-plugins' ) echo 'selected="selected"'; ?> ><?php _e('Plugins translations'); ?></option>
        </select>

        <button type="submit" class="btn btn-submit"><?php _e('Filter'); ?></button>
      </form>
    </div>

    <?php if(is_array($products) && count($products) > 0) { ?>
      <div class="info"><?php echo sprintf(__('Translations helps you to localize website into language you need. You may automatically install languages from the %s or upload a translation in .zip format.'), '<a href="https://osclass-classifieds.com/translations">' . __('OsclassPoint Translations Directory') . '</a>'); ?></div>

      <?php foreach($products as $p) { ?>
        <?php
          $code = $p['prod_name'];

          if($code == '') { continue; }

          $info = WebThemes::newInstance()->loadThemeInfo($p['prod_name']);

          $prod_name = strtolower(trim($code));
          $prod_path = str_replace('-', '_', $prod_name);   // just in case  
          $prod_name = ucfirst(str_replace('_', ' ', $prod_name));

          $pstat = 'NOT';
          if(in_array($prod_path, $themes_all)) {
            $pstat = 'DOWNLOADED';
          }

          $vfrom = $p['s_version'];

          if($vfrom == '' || $vfrom == null || $vfrom == 'null') {
            $version_req = __('all versions');
          } else {
            $version_req = sprintf(__('%s or higher'), $p['s_version']);
          }
          
          $compatible_from = true;

          if($vfrom != '' && $vfrom != null && $vfrom != 'null' && @$info['version'] <> '') {
            $check_from = version_compare2($vfrom, $info['version']);
            if ($check_from == 1) {    // A > B
              $compatible_from = false;
            }
          }
        ?>

        <div class="box">
          <div class="img">
            <div style="background:#<?php echo substr(md5($p['code']), 0, 6); ?>;">
              <span class="top"><?php echo strtoupper(@explode('_', $p['code'])[0]); ?></span>
              <span class="bottom"><?php echo @explode('_', $p['code'])[1]; ?></span>
            </div>
          </div>

          <div class="data">
            <a href="<?php echo $p['url']; ?>" class="name" target="_blank"><?php echo $prod_name; ?> (<?php echo ($p['lang_name'] <> '' ? $p['lang_name'] : $p['lang_name_long']); ?>)</a>
            <div class="desc">
              <div class="line"><strong><?php _e('File name'); ?>:</strong> <span><?php echo $p['full_name']; ?></span></div>
              <div class="line"><strong><?php _e('Updated on'); ?>:</strong> <span><?php echo date('Y-m-d', strtotime($p['date'])); ?></span></div>
              <div class="line"><strong><?php _e('Size'); ?>:</strong> <span><?php echo $p['size']; ?></span></div> 
            </div>

            <div class="actions">
              <?php if($pstat != 'NOT' && $compatible_from ) { ?>
                <a class="mkt-update btn btn-gray" href="<?php echo $p['url']; ?>" data-product-key="<?php echo osc_esc_html($prod_path . '-' . $p['code']); ?>"><i class="fa fa-download"></i> <?php _e('Download'); ?></a>

              <?php } else if($pstat != 'NOT' && !$compatible_from ) { ?>
                <a href="#" class="btn btn-gray" onclick="return false;" title="<?php echo osc_esc_html(sprintf(__('Not compatible with your product version (Your version is %s)'), $info['version'])); ?>"><i class="fa fa-warning"></i> <?php _e('Incompatible'); ?></a>

              <?php } else { ?>
                <a href="#" class="btn btn-gray" onclick="return false;" title="<?php echo osc_esc_html(__('You do not have this product')); ?>"><i class="fa fa-ban"></i> <?php _e('Unavailable'); ?></a>

              <?php } ?>
            </div>
          </div>

          <div class="foot">
            <div class="rating">
              <div class="version"><strong>v<?php echo $p['s_version']; ?></strong>, <span><?php echo sprintf(__('updated %s'), osc_smart_date_diff($p['timestamp'])); ?></span></div>
            </div>

            <div class="about">
              <div class="compatibility">
                <?php if($pstat == 'NOT') { ?>
                  <i class="fa fa-times"></i> <?php _e('Product not installed'); ?>
                <?php } else if(!$compatible_from) { ?>
                  <i class="fa fa-times"></i> <?php echo sprintf(__('Not compatible, require at least %s'), $p['s_version']); ?>
                <?php } else { ?>
                  <i class="fa fa-check"></i> <?php _e('Compatible with your product'); ?> <span>(<?php echo $version_req; ?>)</span>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>
      <?php } ?>
    <?php } else { ?>
      <div class="err"><?php echo sprintf(__('No %s found. Try a different search.'), __('language')); ?></div>
    <?php } ?>
  </div>
</div>

<div id="market_installer" class="has-form-actions hide">
  <form name="mkti" action="<?php echo osc_admin_base_url(true); ?>?page=ajax&action=market&<?php echo osc_csrf_token_url(); ?>" method="post">
    <input type="hidden" name="market_type" value="themes" />
    <input type="hidden" name="market_product_key" id="market_product_key" value="" />
    <input type="hidden" name="market_prod_name" id="market_prod_name" value="" />
    <input type="hidden" name="market_filename" id="market_filename" value="" />
    <input type="hidden" name="market_download" id="market_download" value="" />

    <div class="osc-modal-content-market">
      <table class="table" cellpadding="0" cellspacing="0">
        <tbody>
          <tr class="table-first-row">
            <td><?php _e('Name'); ?></td>
            <td><span id="market_name"><?php _e("Loading data"); ?></span></td>
          </tr>
          <tr class="even">
            <td><?php _e('Language'); ?></td>
            <td><span id="market_lang"><?php _e("Loading data"); ?></span></td>
          </tr>
          <tr class="odd">
            <td><?php _e('Version'); ?></td>
            <td><span id="market_version"><?php _e("Loading data"); ?></span></td>
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
        <button id="market_cancel" class="btn btn-red" ><?php _e('Cancel'); ?></button>
        <button id="market_install" class="btn btn-submit" ><?php _e('Continue install'); ?></button>
      </div>
    </div>
  </form>
</div>


<div id="market_product" class="has-form-actions hide">
  <div class="mkt-info-loading"><img src="<?php echo osc_current_admin_theme_url(); ?>images/spinner-2x.gif" alt="loading..."/></div>
</div>

<script>
  var products = <?php echo $products_json; ?>;

  $(function() {
    $("#market_cancel").on("click", function(){
      $(".ui-dialog-content").dialog("close");
      return false;
    });

    $('a.btn.enable, a.btn.install').on('click', function(e){
      e.preventDefault();

      $(this).find('i').removeClass().addClass('fa').addClass('fa-spinner').addClass('fa-spin'); 

      $.ajax({
        url : $(this).attr('href'),
        type: "POST",
        success: function() {
          location.reload();
        }
      });

      return false;
    });

    $("#market_install").on("click", function(){
      var elem = $(this);
      $(".ui-dialog-content").dialog("close");
      $('<div id="downloading"><div class="osc-modal-content"><img class="ui-download-loading" src="<?php echo osc_current_admin_theme_url(); ?>images/spinner.gif" alt="loading..."/><?php echo osc_esc_js(__('Please wait until the download is completed')); ?></div></div>').dialog({title:'<?php echo osc_esc_js(__('Downloading')); ?>...',modal:true});
      $.getJSON(
      "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=languages-themes&<?php echo osc_csrf_token_url(); ?>",
      {"market_product_key" : $("#market_product_key").attr("value"), "market_download" : $("#market_download").attr("value"), "market_type": "themes", "market_prod_name": $("#market_prod_name").attr("value"), "market_filename": $("#market_filename").attr("value")},
      function(data){
        var content = '';

        if(data.error == 0) { // no errors
          content += oscEscapeHTML(data.message);

          if(elem.hasClass('is-update')) {
            content += '<h3><?php echo osc_esc_js(__('Product language has been updated correctly.')); ?></h3>';
          } else {
            content += '<h3><?php echo osc_esc_js(__('Product language has been downloaded correctly.')); ?></h3>';
          }
          
          content += "<p>";
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


  $('.mkt-update').on('click',function(e){
    e.preventDefault();

    var isUpdate = false;
    if($(this).hasClass('is-update')) {
      isUpdate = true;
    }

    var key = $(this).attr('data-product-key');
    var version_from = products[key].s_version;

    if(version_from == '') {
      var version_compatible = '<?php echo osc_esc_js(__('All versions')); ?>';
    } else if(version_from != '') {
      var version_compatible = version_from + ' <?php echo osc_esc_js(__('and higher')); ?>';
    }

    //$("#market_thumb").attr('src',data.s_thumbnail);
    $("#market_product_key").attr("value", key);
    $("#market_prod_name").attr("value", products[key].prod_name);
    $("#market_filename").attr("value", products[key].full_name);
    $("#market_download").attr("value", products[key].url);
 
    $("#market_name").text(products[key].prod_name_formatted);
    $("#market_lang").text(products[key].lang_name_long);
    $("#market_version").text(products[key].s_version);
    $("#market_osclass_version").text(version_compatible);
    $("#market_url").attr('href',products[key].url);

    if(isUpdate) {
      var modalTitle = '<?php echo osc_esc_js( __('Update language from OsclassPoint') ); ?>';
      $('#market_install').text("<?php echo osc_esc_js( __('Update now') ); ?>");
      $('#market_install').addClass('is-update');
    } else {
      var modalTitle = '<?php echo osc_esc_js( __('Download language from OsclassPoint') ); ?>';
      $('#market_install').text("<?php echo osc_esc_js( __('Download now') ); ?>");
    }

    var dialogWidth = 485;
    
    if($(window).width() < 525) {
      dialogWidth = $(window).width() - 40;
    }
    
    $('#market_installer').dialog({
      modal: true,
      title: modalTitle,
      width: dialogWidth
    });

    return false;
  });
</script>

<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>