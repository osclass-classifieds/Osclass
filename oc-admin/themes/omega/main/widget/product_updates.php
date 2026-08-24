<?php
if(!defined('ABS_PATH')) exit('ABS_PATH is not loaded. Direct access is not allowed.');
/*
 * Copyright 2020 OsclassPoint.com
 *
 * Osclass maintained & developed by OsclassPoint.com
 * you may not use this file except in compliance with the License.
 * You may download copy of Osclass at
 *
 *     https://osclass-classifieds.com/download
 *
 * Software is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */


$data = osc_get_preference('widget_data_product_updates', 'osclass');
$prepare = json_decode($data, true);

if(isset($prepare['date']) && strtotime('-3 day') < strtotime($prepare['date']) && @$prepare['data'] <> '') {
  echo '<div class="widget-cache cached" title="' . osc_esc_html(sprintf(__('Data were cached on %s'), $prepare['date'])) . '">C</div>';
  $updates = $prepare['data'];
} else {
  echo '<div class="widget-cache notcached" title="' . osc_esc_html(__('Uncached data')) . '">L</div>';
  $updates = osc_file_get_contents_json(osc_market_url('product_updates'));

  osc_set_preference('widget_data_product_updates', json_encode(array('date' => date('Y-m-d H:i:s'), 'data' => $updates)));
}

$list = isset($updates['updates']) ? $updates['updates'] : array();
?>

<?php if(!is_array($list) || count($list) <= 0) { ?>
  <div class="empty"><?php _e('No updates has been found'); ?></div>
<?php } else { ?>
  <?php foreach($list as $u) { ?>
    <?php
      if($u['s_osc_version_from'] == '') {
        if($u['s_osc_version_to'] == '') {
          $osc_comp = __('all osclass versions');
        } else {
          $osc_comp = sprintf(__('osclass %s or lower'), $u['s_osc_version_to']);
        }
      } else {
        if($u['s_osc_version_to'] == '') {
          $osc_comp = sprintf(__('osclass %s or higher'), $u['s_osc_version_from']);
        } else {
          $osc_comp = sprintf(__('osclass %s to %s'), $u['s_osc_version_from'], $u['s_osc_version_to']);
        }
      }
      
      $va = explode('.', $u['i_version']);
    ?>
    
    <div class="row" title="<?php echo osc_esc_html(sprintf(__('Updated on %s, compatible with %s'), date('Y-m-d', strtotime($u['dt_date'])), $osc_comp)); ?>">
      <div class="tit">
        <em class="ver v<?php echo @$va[0]; ?> sv<?php echo @$va[1]; ?>"><?php echo $u['i_version']; ?></em>
        <a href="<?php echo $u['s_url']; ?>"><?php echo $u['s_title']; ?></a>
      </div>
      <div class="detail"><?php echo str_replace(';', '.<br/>', str_replace('&amp;', '&', $u['s_comment'])); ?></div>
    </div>
  <?php } ?>
<?php } ?>

<div class="foot">
  <a href="<?php echo $updates['link_all']; ?>"><?php _e('All updates'); ?></a>
</div>