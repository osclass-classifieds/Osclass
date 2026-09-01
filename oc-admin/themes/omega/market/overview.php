<?php
  if(!defined('ABS_PATH')) {
    define('ABS_PATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/');
  }
  
  require_once ABS_PATH . 'oc-load.php';
  $api_valid = __get('api_valid');

  $id = Params::getParam('productId');

  $p = osc_file_get_contents_json('https://osclasspoint.com/oc-content/plugins/market/api/v3/products.php?productId=' . $id . '&apiKey=' . osc_get_preference('osclasspoint_api_key', 'osclass'));
?>
<link href="<?php echo osc_current_admin_theme_url('css/new.css?v=' . date('YmdHis')); ?>" rel="stylesheet" type="text/css" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />

<?php if(!isset($p['pk_i_id'])) { ?>
  <div class="pdata"><div class="err"><?php echo osc_esc_js(__('There was problem loading product details')); ?></div></div>
  <?php exit; ?>
<?php } ?>

<div class="pdata">
  <div class="main">
    <h1><?php echo $p['s_title']; ?></h1>
    <div class="desc"><?php echo $p['s_description_long']; ?></div>

    <h2><?php _e('Pictures'); ?></h2>
    <div class="images">
      <?php $counter = 0; ?>
      <?php if(is_array($p['thumb_list']) && count($p['thumb_list']) > 0) { ?>
        <?php foreach($p['thumb_list'] as $i) { ?>
          <a href="<?php echo $p['large_list'][$counter]; ?>" target="_blank"><img src="<?php echo $i; ?>" /></a>
          <?php $counter++; ?>
        <?php } ?>
      <?php } ?>
    </div>
  </div>

  <div class="side">
    <div class="line price">
      <span><?php echo ($p['i_price'] <= 0 ? __('Free') : $p['i_price'] . '&euro;'); ?></span>
    </div>
    
    <div class="line">
      <strong><?php _e('Author'); ?>:</strong>
      <span><?php echo $p['s_contact_name']; ?></span>
    </div>

    <div class="line">
      <strong><?php _e('Version'); ?>:</strong>
      <span>v<?php echo $p['i_version']; ?></span>
    </div>

    <div class="line">
      <strong><?php _e('Require Osclass version'); ?>:</strong>
      <span>
        <?php 
          if($p['i_osc_version_from'] == '' && $p['i_osc_version_to'] == '') {
            echo __('All versions');
          } else if ($p['i_osc_version_from'] == '') {
            echo sprintf(__('up to %s'), $p['i_osc_version_to']);
          } else {
            echo sprintf(__('%s or higher'), $p['i_osc_version_from']);
          }
        ?>      
      </span>
    </div>

    <div class="line">
      <strong><?php _e('Downloads'); ?>:</strong>
      <span><?php echo $p['i_download']; ?>x</span>
    </div>

    <?php if($p['i_price'] > 0) { ?>
      <div class="line">
        <strong><?php _e('Orders'); ?>:</strong>
        <span><?php echo $p['i_order']; ?>x</span>
      </div>
    <?php } ?>

    <div class="line">
      <strong><?php _e('Last update'); ?>:</strong>
        <span><?php echo $p['dt_update_date']; ?></span>
    </div>

    <div class="line">
      <strong><?php _e('Update comment'); ?>:</strong>
        <span><?php echo $p['s_update_comment']; ?></span>
    </div>

    <div class="line">
      <a href="<?php echo $p['s_url']; ?>" target="_blank"><?php echo ($p['type'] == 'theme' ? __('Theme home page') : ('Plugin home page')); ?> &raquo;</a>

      <?php if($p['s_forum'] <> '') { ?>
        <a href="<?php echo $p['s_forum']; ?>" target="_blank"><?php _e('Support forums'); ?> &raquo;</a>
      <?php } ?>

      <?php if($p['s_demo_front'] <> '') { ?>
        <a href="<?php echo $p['s_demo_front']; ?>" target="_blank"><?php _e('Front-office demo'); ?> &raquo;</a>
      <?php } ?>

      <?php if($p['s_demo_back'] <> '') { ?>
        <a href="<?php echo $p['s_demo_back']; ?>" target="_blank"><?php _e('Back-office demo'); ?> &raquo;</a>
      <?php } ?>
      
    </div>

    <h3><?php _e('Average rating'); ?></h3>

    <div class="line stars">
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

      <span>(<?php echo sprintf(__('Based on %d ratings'), $p['i_rating_count']); ?>)</span>
    </div>

    <?php if($api_valid) { ?>}
      <?php if($p['b_purchased'] <> 1 && $p['i_price'] > 0) { ?>
        <div class="line purch">
          <a href="<?php echo $p['s_purchase_url']; ?>" target="_blank" class="btn btn-submit purchase"><?php echo ($p['type'] == 'theme' ? __('Purchase theme') : __('Purchase plugin')); ?></a>
        </div>
      <?php } else { ?>
        <div class="line purch">
          <a href="<?php echo $p['s_download_url']; ?>" target="_blank" class="btn btn-submit purchase"><?php echo ($p['type'] == 'theme' ? __('Download theme') : __('Download plugin')); ?></a>
        </div>
      <?php } ?>
    <?php } ?>
  </div>
</div>