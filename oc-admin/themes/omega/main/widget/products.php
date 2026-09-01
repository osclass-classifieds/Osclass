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


$data = osc_get_preference('widget_data_product', 'osclass');
$prepare = json_decode($data, true);

if(isset($prepare['date']) && strtotime('-3 day') < strtotime($prepare['date']) && @$prepare['data'] <> '') {
  echo '<div class="widget-cache cached" title="' . osc_esc_html(sprintf(__('Data were cached on %s'), $prepare['date'])) . '">C</div>';
  $products = $prepare['data'];
} else {
  echo '<div class="widget-cache notcached" title="' . osc_esc_html(__('Uncached data')) . '">L</div>';
  $products = osc_file_get_contents_json('https://osclasspoint.com/oc-content/plugins/market/api/v3/products.php?sort=latest&limit=5&apiKey=' . osc_get_preference('osclasspoint_api_key', 'osclass'));

  osc_set_preference('widget_data_product', json_encode(array('date' => date('Y-m-d H:i:s'), 'data' => $products)));
}
?>

<?php if(!is_array($products) || count($products) <= 0) { ?>
  <div class="empty"><?php _e('No products has been found'); ?></div>
<?php } else { ?>
  <?php foreach($products as $p) { ?>
    <div class="row">
      <a href="<?php echo $p['s_url']; ?>"><?php echo $p['s_title']; ?></a>
      <div class="desc"><?php echo osc_highlight($p['s_description'], 120); ?></div>
    </div>
  <?php } ?>
<?php } ?>

<div class="foot">
  <a href="https://osclasspoint.com/search"><?php _e('More products'); ?></a>
</div>