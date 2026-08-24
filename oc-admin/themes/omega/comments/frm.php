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


osc_enqueue_script('jquery-validate');

$comment = __get('comment');
$is_edit = isset($comment['pk_i_id']);

if($is_edit) {
  $action_frm = "comment_edit_post";
  $btn_text = osc_esc_html(__('Update comment'));
} else {
  $action_frm = "add_comment_post";
  $btn_text = osc_esc_html(__('Add'));
}

function customPageHeader() {
  ?>
  <h1><?php _e('Comments'); ?></h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');

function customPageTitle($string) {
  $comment = __get('comment');
  if(isset($comment['pk_i_id'])) {
    return sprintf(__('Edit comment #%s - %s'), $comment['pk_i_id'], $string);
  }
  return sprintf(__('Add comment - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');

function customHead() {
  CommentForm::js_validation(true);
}

osc_add_hook('admin_header','customHead', 10);

$comment = __get('comment');
$reply = __get('reply');
$item = __get('item');
$actions = __get('actions');
if(!is_array($actions)) {
  $actions = array();
}

$prefill_item_id = '';
if(Params::getParam('itemId') > 0) {
  $prefill_item_id = (int)Params::getParam('itemId');
} else if($item !== false && isset($item['pk_i_id'])) {
  $prefill_item_id = (int)$item['pk_i_id'];
}
?>

<?php osc_current_admin_theme_path('parts/header.php'); ?>

<?php if($is_edit) { ?>
<div class="grid-row no-bottom-margin">
  <div class="row-wrapper row-render-title-item">
    <h2 class="render-title">
      <?php _e('Edit comment'); ?> #<?php echo $comment['pk_i_id']; ?>
      <?php if($item !== false) { ?>
        <span class="front-link"><a href="<?php echo osc_item_url(); ?>" target="_blank"><?php _e('View listing on front'); ?> <i class="fa fa-external-link"></i></a></span>
      <?php } ?>
    </h2>
  </div>
</div>

<div class="grid-row no-bottom-margin float-right">
  <div class="row-wrapper">
    <?php if(count($actions) > 0) { ?>
    <ul id="item-action-list">
      <?php foreach($actions as $action) { ?>
      <li><?php echo $action; ?></li>
      <?php } ?>
    </ul>
    <div class="clear"></div>
    <?php } ?>
  </div>
</div>
<div class="clear"></div>
<?php } else { ?>
<h2 class="render-title"><?php _e('Add comment'); ?></h2>
<?php } ?>

<div id="language-form">
  <ul id="error_list"></ul>
  <form name="comment_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="action" value="<?php echo $action_frm; ?>" />
    <input type="hidden" name="page" value="comments" />
    <?php if($is_edit) { ?>
    <input type="hidden" name="id" value="<?php echo $comment['pk_i_id']; ?>" />
    <?php } ?>

    <div class="form-horizontal">
      <?php if(!$is_edit) { ?>
      <div class="form-row">
        <div class="form-label"><?php _e('Listing ID'); ?></div>
        <div class="form-controls">
          <input type="number" name="itemId" value="<?php echo $prefill_item_id; ?>" min="1" step="1" />
        </div>
      </div>
      <?php } ?>

      <div class="form-row">
        <div class="form-label"><?php _e('Author'); ?></div>
        <div class="form-controls">
          <?php CommentForm::author_input_text($comment); ?>
          <?php if(!$is_edit && isset($comment['fk_i_user_id']) && $comment['fk_i_user_id'] != '') { ?>
          <span class="help-inline"><?php _e('Registered user'); ?></span>
          <?php } ?>
        </div>
      </div>

      <div class="form-row">
        <div class="form-label"><?php _e("Author's e-mail"); ?></div>
        <div class="form-controls">
          <?php CommentForm::email_input_text($comment); ?>
        </div>
      </div>

      <div class="form-row rating">
        <div class="form-label"><?php _e('Rating'); ?></div>
        <div class="form-controls input-description-wide">
          <?php CommentForm::rating_input_text($comment); ?>
          <span class="help-inline"><?php _e('Rating value between 0 and 5'); ?></span>
        </div>
      </div>

      <div class="form-row">
        <div class="form-label"><?php _e('Title'); ?></div>
        <div class="form-controls">
          <?php CommentForm::title_input_text($comment); ?>
        </div>
      </div>

      <div class="form-row">
        <div class="form-label"><?php _e('Comment'); ?></div>
        <div class="form-controls input-description-wide">
          <?php CommentForm::body_input_textarea($comment); ?>
        </div>
      </div>

      <div class="form-row rating">
        <div class="form-label"><?php _e('Parent comment ID'); ?></div>
        <div class="form-controls input-description-wide">
          <?php CommentForm::reply_input_text($comment); ?>
          <?php if($is_edit) { ?>
          <span class="help-inline">
            <?php if($reply !== false && isset($reply['pk_i_id'])) {
              _e('Comment is reply to'); ?>
              <a target="_blank" href="<?php echo osc_admin_base_url(true); ?>?page=comments&amp;action=comment_edit&amp;id=<?php echo $reply['pk_i_id']; ?>"><?php echo $reply['s_title'] . ' (#' . $reply['pk_i_id'] . ')'; ?></a>
            <?php } else {
              _e('Comment has no parent comment (is not reply).');
            } ?>
            <?php if(isset($comment['i_reply_count'])) { ?>
            <br>
            <?php echo ($comment['i_reply_count'] == 1 ? __('Comment has 1 reply.') : sprintf(__('Comment has %d replies.'), $comment['i_reply_count'])); ?>
            <?php } ?>
          </span>
          <?php } ?>
        </div>
      </div>
    </div>

    <div class="form-actions">
      <a href="javascript:history.go(-1)" class="btn"><?php _e('Cancel'); ?></a>
      <input type="submit" value="<?php echo $btn_text; ?>" class="btn btn-submit" />
    </div>
  </form>
</div>
<?php osc_current_admin_theme_path('parts/footer.php');
