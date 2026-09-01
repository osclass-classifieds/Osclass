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


$data = osc_get_preference('widget_data_blog', 'osclass');
$prepare = json_decode($data, true);

if(isset($prepare['date']) && strtotime('-3 day') < strtotime($prepare['date']) && @$prepare['data'] <> '') {
  echo '<div class="widget-cache cached" title="' . osc_esc_html(sprintf(__('Data were cached on %s'), $prepare['date'])) . '">C</div>';
  $articles = $prepare['data'];
} else {
  echo '<div class="widget-cache notcached" title="' . osc_esc_html(__('Uncached data')) . '">L</div>';
  $articles = osc_file_get_contents_json(osc_market_url('blog'));

  osc_set_preference('widget_data_blog', json_encode(array('date' => date('Y-m-d H:i:s'), 'data' => $articles)));
}

?>

<?php if(!is_array($articles) || count($articles) <= 0) { ?>
  <div class="empty"><?php _e('No articles has been found'); ?></div>
<?php } else { ?>
  <?php foreach($articles as $a) { ?>
    <div class="row">
      <a href="<?php echo $a['link']; ?>"><?php echo $a['title']; ?></a>
      <div class="date"><?php echo osc_format_date($a['pub_date'], 'd. M Y'); ?></div>
      <div class="desc"><?php echo osc_highlight($a['short_description'], 135); ?></div>
    </div>
  <?php } ?>
<?php } ?>

<div class="foot">
  <a href="https://osclasspoint.com/blog/home"><?php _e('More news'); ?></a>
</div>