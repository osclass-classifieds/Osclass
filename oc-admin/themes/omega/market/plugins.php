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
    <?php echo sprintf(__('%s on market'), __('Plugins')); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader2');


function customPageTitle($string) {
  return sprintf(__('%s on market - %s'), __('Plugins'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');


$type = Params::getParam('type');
$sort = Params::getParam('sort');
$sort = ($sort == '' ? 'rating' : $sort);
$pattern = urlencode(Params::getParam('pattern'));
$product = ($action == 'themes' ? 'theme' : 'plugin');

$products_json = osc_file_get_contents('https://osclasspoint.com/oc-content/plugins/market/api/v3/products.php?type=' . $type . '&product=' . $product . '&pattern=' . $pattern . '&sort=' . $sort . '&apiKey=' . osc_get_preference('osclasspoint_api_key', 'osclass'));
$products = json_decode($products_json, true);

$plugins_all = Plugins::listAll();
$plugins_enabled = Plugins::listEnabled();
$plugins_installed = Plugins::listInstalled();  // but disabled

osc_current_admin_theme_path( 'market/header.php' ); 
?>

<div id="market-block" class="<?php echo osc_esc_html($action); ?>">
  <div id="mrkt">
    <div class="search">
      <a href="<?php echo osc_admin_base_url(true); ?>?page=market&action=<?php echo $action; ?>&sort=rating" class="<?php if(Params::getParam('sort') == '' || Params::getParam('sort') == 'rating') { echo 'active'; } ?>"><?php _e('By rating'); ?></a>
      <a href="<?php echo osc_admin_base_url(true); ?>?page=market&action=<?php echo $action; ?>&sort=downloads" class="<?php if(Params::getParam('sort') == 'downloads') { echo 'active'; } ?>"><?php _e('By downloads'); ?></a>
      <a href="<?php echo osc_admin_base_url(true); ?>?page=market&action=<?php echo $action; ?>&sort=bestseller" class="<?php if(Params::getParam('sort') == 'bestseller') { echo 'active'; } ?>"><?php _e('Best seller'); ?></a>
      <a href="<?php echo osc_admin_base_url(true); ?>?page=market&action=<?php echo $action; ?>&sort=latest" class="<?php if(Params::getParam('sort') == 'latest') { echo 'active'; } ?>"><?php _e('Latest'); ?></a>

      <form method="get" action="<?php echo osc_admin_base_url(true); ?>"  class="inline nocsrf">
        <input type="hidden" name="page" value="market" />
        <input type="hidden" name="action" value="<?php echo osc_esc_html($action); ?>" />
        <input type="hidden" name="product" value="<?php echo osc_esc_html($product); ?>" />

        <input type="text" name="pattern" class="input-text" value="<?php echo Params::getParam('pattern'); ?>" placeholder="<?php echo osc_esc_html(__('Search plugin...')); ?>"/>
    
        <select name="type" class="select-box-extra select-box-medium float-left" onchange="this.form.submit();">
          <option value="" <?php if( Params::getParam('type') == '' ) echo 'selected="selected"'; ?> ><?php _e('Type of product'); ?></option>
          <option value="premium" <?php if( Params::getParam('type') == 'premium' ) echo 'selected="selected"'; ?> ><?php _e('Premium'); ?></option>
          <option value="free" <?php if( Params::getParam('type') == 'free' ) echo 'selected="selected"'; ?> ><?php _e('Free'); ?></option>
          <option value="purchased" <?php if( Params::getParam('type') == 'purchased' ) echo 'selected="selected"'; ?> ><?php _e('Purchased'); ?></option>
          <option value="expired" <?php if( Params::getParam('type') == 'expired' ) echo 'selected="selected"'; ?> ><?php _e('Expired'); ?></option>
          <option value="notpurchased" <?php if( Params::getParam('type') == 'notpurchased' ) echo 'selected="selected"'; ?> ><?php _e('Not purchased'); ?></option>
        </select>

        <button type="submit" class="btn btn-submit"><?php _e('Filter'); ?></button>
      </form>
    </div>

    <?php if(is_array($products) && count($products) > 0) { ?>
      <div class="info"><?php echo sprintf(__('Plugins extend and expand the functionality of Osclass. You may automatically install plugins from the %s or upload a plugin in .zip format.'), '<a href="https://osclasspoint.com/osclass-plugins">' . __('OsclassPoint Plugins Directory') . '</a>'); ?></div>

      <?php foreach($products as $p) { ?>
        <?php
          if($p['s_product_key'] == '') { continue; }

          $idname = osc_find_by_product_key($p['s_product_key'], 'plugin');
          $info = osc_plugin_get_info($idname);

          $pstat = 'NOT';
          if(in_array($idname, $plugins_enabled)) {
            $pstat = 'ENABLED';
          } else if(in_array($idname, $plugins_installed)) {
            $pstat = 'DISABLED';
          } else if(in_array($idname, $plugins_all)) {
            $pstat = 'DOWNLOADED';
          }

          $vfrom = $p['i_osc_version_from'];
          $vto = $p['i_osc_version_to'];

          if($vto == '' || $vto == null || $vto == 'null') {
            if($vfrom == '' || $vfrom == null || $vfrom == 'null') {
              $version_req = __('all versions');
            } else {
              $version_req = sprintf(__('%s or higher'), $vfrom);
            }
          } else {
            if($vfrom == '' || $vfrom == null || $vfrom == 'null') {
              $version_req = sprintf(__('up to %s'), $vto);
            } else {
              $version_req = sprintf(__('% - %s'), $vfrom, $vto);
            }
          }

          $compatible_from = true;
          $compatible_to = true;

          if($vfrom != '' && $vfrom != null && $vfrom != 'null') {
            $check_from = version_compare2($vfrom, osc_version());
            if ($check_from == 1) {    // A > B
              $compatible_from = false;
            }
          }

          if($vto != '' && $vto != null && $vto != 'null') {
            $check_to = version_compare2(osc_version(), $vto);
            if ($check_to == 1) {    // A > B
              $compatible_to = false;
            }
          }

          $need_update = false;
          if($info['version'] <> '') {
            $check_update = version_compare2($p['i_version'], $info['version']);
            if ($check_update == 1) {    // A > B
              $need_update = true;
            }
          }
          
          // Get class for box
          $box_class = '';
          
          if($p['b_purchased'] == 1) {
            $box_class = 'purchased';
            
          } else if ($p['b_expired'] == 1) {
            $box_class = 'expired';
            
          } else if ($p['i_price'] <= 0) {
            $box_class = 'free';
          }
        ?>

        <div class="box <?php echo $box_class ?> type-<?php echo (Params::getParam('type') == '' ? 'none' : Params::getParam('type')); ?>">
          <div class="img">
            <span><img src="<?php echo $p['s_thumbnail_url']; ?>" alt="<?php echo osc_esc_html($p['s_title']); ?>"/></span>
          </div>

          <div class="data">
            <a href="<?php echo $p['s_url']; ?>" class="name" target="_blank"><?php echo $p['s_title']; ?></a>
            <div class="desc"><?php echo osc_highlight($p['s_description'], 160); ?></div>
            <div class="price">
              <?php
                if($p['i_price'] <= 0) {
                  $price_label = __('Free');
                  
                } else if ($p['b_expired'] == 1) {
                  $price_label = $p['i_price_extend'] . '&euro;';
                  
                } else {
                  $price_label = $p['i_price'] . '&euro;';
                }
              ?>
              
              <span><?php echo $price_label; ?></span>

              <?php if ($p['b_purchased'] == 1) { ?>
                <em> (<?php _e('Purchased'); ?>)</em>
                
              <?php } else if ($p['b_expired'] == 1) { ?>
                <span class="full-price"><?php echo $p['i_price'] . '&euro;'; ?></span>
                <em> (<?php _e('Expired'); ?>)</em>
              <?php } ?>    
            </div>
            
            <div class="actions">
              <?php if($api_valid) { ?>
                <?php if ($pstat != 'NOT' && $need_update && ($p['b_purchased'] == 1 || $p['i_price'] <= 0) && $compatible_from && $compatible_to) { ?>
                  <a class="mkt-update btn btn-gray is-update" href="<?php echo $p['s_download_url']; ?>" data-product-key="<?php echo osc_esc_html($p['s_product_key']); ?>"><i class="fa fa-refresh"></i> <?php _e('Update'); ?></a>

                <?php } else if ($pstat != 'NOT' && $need_update && ($p['b_purchased'] == 1 || $p['i_price'] <= 0)) { ?>
                  <a href="#" onclick="return false;" class="btn btn-gray" title="<?php echo osc_esc_html(__('No compatible with your osclass version')); ?>"><i class="fa fa-exclamation-circle"></i> <?php _e('Can\'t update'); ?></a>

                <?php } else if(($p['b_purchased'] == 1 || $p['i_price'] <= 0) && $pstat == 'NOT') { ?>
                  <a class="mkt-update btn btn-gray" href="<?php echo $p['s_download_url']; ?>" data-product-key="<?php echo osc_esc_html($p['s_product_key']); ?>"><i class="fa fa-download"></i> <?php _e('Download'); ?></a>

                <?php } else if(($p['b_purchased'] == 1 || $p['i_price'] <= 0) && $pstat == 'DOWNLOADED') { ?>
                  <a href="<?php echo osc_admin_base_url(true) . '?page=plugins&action=install&plugin=' . $info['filename'] . '&' . osc_csrf_token_url(); ?>" class="btn btn-gray install"><i class="fa fa-check-circle-o"></i> <?php _e('Install'); ?></a>

                <?php } else if(($p['b_purchased'] == 1 || $p['i_price'] <= 0) && $pstat == 'DISABLED') { ?>
                  <a href="<?php echo osc_admin_base_url(true) . '?page=plugins&action=enable&plugin=' . $info['filename'] . '&' . osc_csrf_token_url(); ?>" class="btn btn-gray enable"><i class="fa fa-play"></i> <?php _e('Enable'); ?></a>

                <?php } else if(($p['b_purchased'] == 1 || $p['i_price'] <= 0) && $pstat == 'ENABLED') { ?>
                  <a href="<?php echo osc_admin_base_url(true) . '?page=plugins&action=admin&plugin=' . $info['filename'] . '&' . osc_csrf_token_url(); ?>" class="btn btn-gray" title="<?php echo osc_esc_html(__('Click to open plugin configuration')); ?>"><i class="fa fa-check"></i> <?php _e('Ready'); ?></a>

                <?php } else { ?>
                  <a href="<?php echo $p['s_purchase_url']; ?>" class="btn btn-gray" target="_blank">
                    <i class="fa fa-external-link"></i> 
                    <?php echo ($p['b_expired'] == 1 ? __('Extend support') : __('Purchase')); ?>
                  </a>

                <?php } ?>
              <?php } else { ?>
                <?php if($p['i_price'] > 0) { ?>
                  <a href="<?php echo $p['s_purchase_url']; ?>" class="btn btn-gray" target="_blank">
                    <i class="fa fa-external-link"></i> 
                    <?php echo ($p['b_expired'] == 1 ? __('Extend support') : __('Purchase')); ?>
                  </a>
                <?php } else { ?>
                  <a href="<?php echo $p['s_url']; ?>" class="btn btn-gray" target="_blank"><i class="fa fa-download"></i> <?php _e('Download'); ?></a>
                <?php } ?>
              <?php } ?>
          
              <a href="<?php echo osc_admin_base_url(true); ?>?page=market&action=overview&productId=<?php echo $p['pk_i_id']; ?>" class="mkt-more-details" data-product-id="<?php echo $p['pk_i_id']; ?>"><?php _e('More details'); ?></a>
            </div>
          </div>

          <div class="foot">
            <div class="rating">
              <div class="stars">
                <?php for($i = 1; $i <= 5; $i++) { ?>
                  <?php
                    if($p['i_rating'] >= $i) {
                      $class = 'fa-star';
                    } else {
                      $class = 'fa-star-o';
                    }
                  ?>
                  <i class="fa <?php echo $class; ?>"></i>
                <?php } ?>

                <span>(<?php echo $p['i_rating_count']; ?>)</span>
              </div>

              <div class="downloads">
                <?php 
                  if(Params::getParam('sort') == 'bestseller') {
                    echo sprintf(__('%s orders'), $p['i_order']);
                  } else {
                    echo sprintf(__('%s downloads'), $p['i_download']);
                  }
                ?>
              </div>
            </div>

            <div class="about">
              <div class="version"><strong>v<?php echo $p['i_version']; ?></strong>, <span><?php echo sprintf(__('updated %s'), osc_smart_date_diff($p['dt_update_date'])); ?></span></div>
              <div class="compatibility">
                <?php if(!$compatible_from) { ?>
                  <i class="fa fa-times"></i> <?php echo sprintf(__('Not compatible, require at least osclass %s'), $vfrom); ?>
                <?php } else if(!$compatible_to) { ?>
                  <i class="fa fa-times"></i> <?php echo sprintf(__('Not compatible, require osclass not higher than %s'), $vto); ?>
                <?php } else { ?>
                  <i class="fa fa-check"></i> <?php _e('Compatible with your osclass'); ?> <span>(<?php echo $version_req; ?>)</span>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>
      <?php } ?>
    <?php } else { ?>
      <div class="err"><?php echo sprintf(__('No %s found. Try a different search.'), __('plugin')); ?></div>
    <?php } ?>
  </div>
</div>

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
      "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=market&<?php echo osc_csrf_token_url(); ?>",
      {"market_product_key" : $("#market_product_key").attr("value"), "section" : 'plugins'},
      function(data){
        var content = '';

        if(data.error == 0) { // no errors
          content += oscEscapeHTML(data.message);

          if(elem.hasClass('is-update')) {
            content += '<h3><?php echo osc_esc_js(__('Plugin has been updated correctly.')); ?></h3>';
          } else {
            content += '<h3><?php echo osc_esc_js(__('Plugin has been downloaded correctly.')); ?></h3>';
          }
          
          content += "<p>";
          content += '<a class="btn btn-mini btn-green" href="<?php echo osc_admin_base_url(true); ?>?page=plugins&marketError='+data.error+'&message='+oscEscapeHTML(data.message)+'&slug='+oscEscapeHTML(data.data['download'])+'"><?php echo osc_esc_js(__('Go to plugins page')); ?></a>';
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
    var version_from = products[key].i_osc_version_from;
    var version_to = products[key].i_osc_version_to;

    if(version_from == '' && version_to == '') {
      var version_compatible = '<?php echo osc_esc_js(__('All versions')); ?>';
    } else if(version_to == '') {
      var version_compatible = version_from + ' <?php echo osc_esc_js(__('and higher')); ?>';
    } else if(version_from == '') {
      var version_compatible = '<?php echo osc_esc_js(__('up to')); ?> ' + version_to;
    }

    //$("#market_thumb").attr('src',data.s_thumbnail);
    $("#market_product_key").attr("value", key);
    $("#market_name").text(products[key].s_title);
    $("#market_version").text(products[key].i_version);
    $("#market_changes").text(products[key].s_update_comment);
    $("#market_osclass_version").text(version_compatible);
    $("#market_url").attr('href',products[key].s_download_url);

    if(isUpdate) {
      var modalTitle = '<?php echo osc_esc_js( __('Update plugin from OsclassPoint') ); ?>';
      $('#market_install').text("<?php echo osc_esc_js( __('Update now') ); ?>");
      $('#market_install').addClass('is-update');
    } else {
      var modalTitle = '<?php echo osc_esc_js( __('Download plugin from OsclassPoint') ); ?>';
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


  $('.mkt-more-details').on('click',function(e){
    e.preventDefault();

    $("#market_product.ui-dialog-content").html('<div class="mkt-info-loading"><img src="<?php echo osc_current_admin_theme_url(); ?>images/spinner-2x.gif" alt="loading..."/></div>');

    var dialogWidth = 780;
    var dialogHeight = 620;
    
    if($(window).width() < 820) {
      dialogWidth = $(window).width() - 40;
      dialogHeight = $(window).height() - 20;
    }
    
    $('.mkt-info-loading').height(dialogHeight);

    $('#market_product').dialog({
      dialogClass: "dialog-product",
      modal: true,
      width: dialogWidth,
      height: dialogHeight
    });

     $.ajax({
        url : $(this).attr('href'),
        type: "POST",
        success: function(data) {
          $("#market_product.ui-dialog-content").html(data);
          $("#market_product.ui-dialog-content .pdata").height(dialogHeight);
        },
        error: function() {
          $("#market_product.ui-dialog-content").html('<div class="pdata"><div class="err"><?php echo osc_esc_js(__('There was problem loading product details')); ?></div></div>');
        }
     });
  });
</script>

<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>