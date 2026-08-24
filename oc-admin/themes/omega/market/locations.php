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
    <?php echo sprintf(__('%s on market'), __('Locations')); ?>
    <a href="#" class="btn ico ico-32 ico-help float-right"></a>
  </h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader2');


function customPageTitle($string) {
  return sprintf(__('%s on market - %s'), __('Locations'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');


$sort = Params::getParam('sort');
$sort = ($sort == '' ? 'latest' : $sort);
$pattern = urlencode(Params::getParam('pattern'));

$products_json = osc_file_get_contents(osc_location_url('', $pattern, $sort));
$products = json_decode($products_json, true);

$location_all = Country::newInstance()->listAll();

osc_current_admin_theme_path( 'market/header.php' ); 
?>

<div id="market-block" class="<?php echo osc_esc_html($action); ?>">
  <div id="mrkt" class="languages locations">
    <div class="search">
      <a href="<?php echo osc_admin_base_url(true); ?>?page=market&action=<?php echo $action; ?>&sort=latest" class="<?php if(Params::getParam('sort') == '' || Params::getParam('sort') == 'latest') { echo 'active'; } ?>"><?php _e('Latest'); ?></a>
      <a href="<?php echo osc_admin_base_url(true); ?>?page=market&action=<?php echo $action; ?>&sort=name" class="<?php if(Params::getParam('sort') == 'name') { echo 'active'; } ?>"><?php _e('By name'); ?></a>
      <a href="<?php echo osc_admin_base_url(true); ?>?page=market&action=<?php echo $action; ?>&sort=version" class="<?php if(Params::getParam('sort') == 'version') { echo 'active'; } ?>"><?php _e('By version'); ?></a>

      <form method="get" action="<?php echo osc_admin_base_url(true); ?>"  class="inline nocsrf">
        <input type="hidden" name="page" value="market" />
        <input type="hidden" name="action" value="<?php echo osc_esc_html($action); ?>" />

        <input type="text" name="pattern" class="input-text" value="<?php echo Params::getParam('pattern'); ?>" placeholder="<?php echo osc_esc_html(__('Search location...')); ?>"/>
        <button type="submit" class="btn btn-submit"><?php _e('Filter'); ?></button>
      </form>
    </div>

    <?php if(is_array($products) && count($products) > 0) { ?>
      <div class="info"><?php echo sprintf(__('Locations contains country, region and city data those are used in diffrent situations by osclass. You may automatically install locations from the %s or upload a location in .sql format.'), '<a href="https://osclass-classifieds.com/locations">' . __('OsclassPoint Locations Directory') . '</a>'); ?></div>

      <?php foreach($products as $p) { ?>
        <?php
          $code = $p['code'];

          if($code == '') { continue; }

          $info = Country::newInstance()->findByCode($code);
          //$info = @$info[0];

          $pstat = 'NOT';
          if(in_array($code, array_column($location_all, 'pk_c_code'))) {
            $pstat = 'INSTALLED';
          }

          $vfrom = $p['version'];

          if($vfrom == '' || $vfrom == null || $vfrom == 'null') {
            $version_req = __('all versions');
          } else {
            $version_req = sprintf(__('%s or higher'), $p['s_version']);
          }
          
          $compatible_from = true;

          if($vfrom != '' && $vfrom != null && $vfrom != 'null') {
            $check_from = version_compare2($vfrom, osc_version());
            if ($check_from == 1) {    // A > B
              $compatible_from = false;
            }
          }
        ?>

        <div class="box">
          <div class="img">
            <div style="background:#<?php echo substr(md5($p['code']), 0, 6); ?>;">
              <span class="middle"><?php echo $p['code']; ?></span>
            </div>
          </div>

          <div class="data">
            <a href="<?php echo $p['url']; ?>" class="name" target="_blank"><?php echo $p['name']; ?> (+<?php echo $p['phone_code']; ?>, <?php echo $p['currency']; ?>)</a>
            <div class="desc">
              <div class="line"><strong><?php _e('Contains'); ?>:</strong> <span><?php echo $p['region_count']; ?> <?php _e('regions'); ?>, <?php echo $p['city_count']; ?> <?php _e('cities'); ?></span></div>
              <div class="line"><strong><?php _e('File name'); ?>:</strong> <span><?php echo $p['file']; ?></span></div>
              <div class="line"><strong><?php _e('Updated on'); ?>:</strong> <span><?php echo date('Y-m-d', strtotime($p['date'])); ?></span></div> 
            </div>

            <div class="actions">
              <?php if($pstat == 'NOT' && $compatible_from) { ?>
                <a class="mkt-update btn btn-gray" href="<?php echo $p['url']; ?>" data-product-key="<?php echo osc_esc_html($p['code']); ?>"><i class="fa fa-download"></i> <?php _e('Download'); ?></a>

              <?php } else if(!$compatible_from) { ?>
                <a class="btn btn-gray" href="#" onclick="return false" title="<?php echo osc_esc_html(__('Not compatible with your osclass version')); ?>"><i class="fa fa-ban"></i> <?php _e('Can\'t download'); ?></a>

              <?php } else { ?>
                <a href="<?php echo osc_admin_base_url(true); ?>?page=locations&country_code=<?php echo $p['code']; ?>"" class="btn btn-gray" title="<?php echo osc_esc_html(__('Click to open country configuration')); ?>"><i class="fa fa-check"></i> <?php _e('Active'); ?></a>

              <?php } ?>
            </div>
          </div>

          <div class="foot">
            <div class="rating">
              <div class="version"><strong>v<?php echo $p['s_version']; ?></strong>, <span><?php echo sprintf(__('updated %s'), osc_smart_date_diff($p['date'])); ?></span></div>
            </div>

            <div class="about">
              <div class="compatibility">
                <?php if(!$compatible_from) { ?>
                  <i class="fa fa-times"></i> <?php echo sprintf(__('Not compatible, require at least osclass %s'), $p['s_version']); ?>
                <?php } else { ?>
                  <i class="fa fa-check"></i> <?php _e('Compatible with your osclass'); ?> <span>(<?php echo $version_req; ?>)</span>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>
      <?php } ?>
    <?php } else { ?>
      <div class="err"><?php echo sprintf(__('No %s found. Try a different search.'), __('location')); ?></div>
    <?php } ?>
  </div>
</div>

<div id="market_installer" class="has-form-actions hide">
  <form name="mkti" action="<?php echo osc_admin_base_url(true); ?>?page=ajax&action=locations_import&<?php echo osc_csrf_token_url(); ?>" method="post">
    <input type="hidden" name="section" value="locations" />
    <input type="hidden" name="market_product_key" id="market_product_key" value="" />
    <input type="hidden" name="market_file_name" id="market_file_name" value="" />
    
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
      "<?php echo osc_admin_base_url(true); ?>?page=ajax&action=locations_import&<?php echo osc_csrf_token_url(); ?>",
      {"market_product_key" : $("#market_product_key").attr("value"), "market_file_name" : $("#market_file_name").attr("value"), "section" : 'locations'},
      function(data){
        var content = '';

        if(data.error == 0) { // no errors
          content += oscEscapeHTML(data.message);

          if(elem.hasClass('is-update')) {
            content += '<h3><?php echo osc_esc_js(__('Location has been updated correctly.')); ?></h3>';
          } else {
            content += '<h3><?php echo osc_esc_js(__('Location has been downloaded correctly.')); ?></h3>';
          }
          
          content += "<p>";
          content += '<a class="btn btn-mini btn-green" href="<?php echo osc_admin_base_url(true); ?>?page=locations&marketError='+data.error+'&message='+oscEscapeHTML(data.message)+'"><?php echo osc_esc_js(__('Go to locations page')); ?></a>';
          content += '<a class="btn btn-mini" href="javascript:location.reload(true)"><?php echo osc_esc_js(__('Close')); ?></a>';
          content += "</p>";
        } else {
          content += '<p>' + oscEscapeHTML(data.error) + '</p><p>&nbsp;</p>';
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
    $("#market_file_name").attr("value", products[key].file);
    $("#market_name").text(products[key].name);
    $("#market_version").text(products[key].s_version);
    $("#market_osclass_version").text(version_compatible);
    $("#market_url").attr('href',products[key].url);

    if(isUpdate) {
      var modalTitle = '<?php echo osc_esc_js( __('Update location from OsclassPoint') ); ?>';
      $('#market_install').text("<?php echo osc_esc_js( __('Update now') ); ?>");
      $('#market_install').addClass('is-update');
    } else {
      var modalTitle = '<?php echo osc_esc_js( __('Download location from OsclassPoint') ); ?>';
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