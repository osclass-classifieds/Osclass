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

if(isset($comment['pk_i_id'])) {
  //editing...
  $title = __("Edit comment");
  $action_frm = "comment_edit_post";
  $btn_text = osc_esc_html( __("Update comment"));
} else {
  //adding...
  $title = __("Add comment");
  $action_frm = "add_comment_post";
  $btn_text = osc_esc_html( __('Add'));
}

function customPageHeader() { 
  ?>
  <h1><?php _e('Listing'); ?></h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


function customPageTitle($string) {
  return sprintf(__('Edit comment - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');


//customize Head
function customHead() {
  CommentForm::js_validation(true);
}

osc_add_hook('admin_header','customHead', 10);

$comment = __get('comment');
$reply = __get('reply');
$item = __get('item');
?>

<?php osc_current_admin_theme_path( 'parts/header.php' ); ?>
<h2 class="render-title"><?php echo $title; ?></h2>
<div id="language-form">
  <ul id="error_list"></ul>
  <form name="language_form" action="<?php echo osc_admin_base_url(true); ?>" method="post">
    <input type="hidden" name="action" value="<?php echo $action_frm; ?>" />
    <input type="hidden" name="page" value="comments" />
    <input type="hidden" name="id" value="<?php echo (isset($comment['pk_i_id'])) ? $comment['pk_i_id'] : '' ?>" />
    
    <div class="form-horizontal">
      <div class="form-row">
        <div class="form-label"><?php _e('Author'); ?></div>
        <div class="form-controls">
          <?php CommentForm::author_input_text($comment); ?>

          <div class="regu">
            <?php if(isset($comment['fk_i_user_id']) && $comment['fk_i_user_id']!='') {
              _e("Registered user"); ?>
              <a href="<?php echo osc_admin_base_url(true); ?>?page=users&action=edit&id=<?php echo $comment['fk_i_user_id']; ?>"><?php _e('Edit user'); ?></a>
            <?php }?>
          </div>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-label"><?php _e("Author's e-mail"); ?></div>
        <div class="form-controls">
          <?php CommentForm::email_input_text($comment); ?>
        </div>
      </div>

      <?php if($item !== false) { ?>
        <div class="form-row">
          <div class="form-label"><?php _e('Listing'); ?></div>
          <div class="form-controls">
            <div class="form-label-checkbox">
              <a href="<?php echo osc_item_url(); ?>"><?php echo osc_item_title(); ?> (#<?php echo osc_item_id(); ?>)</a>
            </div>
          </div>
        </div>
      <?php } ?>
      
      <div class="form-row">
        <div class="form-label"><?php _e('Validated'); ?></div>
        <div class="form-controls">
          <div class="form-label-checkbox">
            <?php echo ( $comment['b_active'] ? __('Active') : __('Inactive') ); ?> ( <a href="<?php echo osc_admin_base_url( true ); ?>?page=comments&action=status&id=<?php echo $comment['pk_i_id']; ?>&value=<?php echo ( ( $comment['b_active'] == 1) ? 'INACTIVE' : 'ACTIVE' ); ?>"><?php echo ( ( $comment['b_active'] == 1 ) ? __('Deactivate') : __('Activate') ); ?></a> )
          </div>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-label"><?php _e('Enabled'); ?></div>
        <div class="form-controls">
          <div class="form-label-checkbox">
            <?php echo ( $comment['b_enabled'] ? __('Unblocked') : __('Blocked') ); ?> ( <a href="<?php echo osc_admin_base_url( true ); ?>?page=comments&action=status&id=<?php echo $comment['pk_i_id']; ?>&value=<?php echo ( ( $comment['b_enabled'] == 1) ? 'DISABLE' : 'ENABLE' ); ?>"><?php echo ( ( $comment['b_enabled'] == 1 ) ? __('Block') : __('Unblock') ); ?></a> )
          </div>
        </div>
      </div>
      
      <div class="form-row rating">
        <div class="form-label"><?php _e('Rating'); ?></div>
        <div class="form-controls input-description-wide">
          <?php CommentForm::rating_input_text($comment); ?>
          
          <div class="regu">
            <?php _e("Rating value between 0 and 5"); ?>
          </div>
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
          
          <div class="regu">
            <?php if($reply !== false && isset($reply['pk_i_id'])) {
              _e("Comment is reply to"); ?>
              <a target="_blank" href="<?php echo osc_admin_base_url(true); ?>?page=comments&action=comment_edit&id=<?php echo $reply['pk_i_id']; ?>"><?php echo $reply['s_title'] . ' (#' . $reply['pk_i_id'] . ')'; ?></a>
            <?php } else { 
              _e("Comment has no parent comment (is not reply).");
            } ?>
            
            <br>
            <?php echo ($comment['i_reply_count'] == 1 ? __('Comment has 1 reply.') : sprintf(__('Comment has %d replies.'), $comment['i_reply_count'])); ?>
          </div>          
        </div>
      </div>    
    </div>

    <div class="form-actions">
      <a href="javascript:history.go(-1)" class="btn"><?php _e('Cancel'); ?></a>
      <input type="submit" value="<?php echo $btn_text; ?>" class="btn btn-submit" />
    </div>
  </form>
</div>
<?php osc_current_admin_theme_path( 'parts/footer.php' ); ?>