<?php
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


  // meta tag robots
  if( osc_item_is_spam() || osc_premium_is_spam() ) {
    osc_add_hook('header','sigma_nofollow_construct');
  } else {
    osc_add_hook('header','sigma_follow_construct');
  }

  //osc_enqueue_script('fancybox');
  //osc_enqueue_style('fancybox', osc_current_web_theme_url('js/fancybox/jquery.fancybox.css'));
  osc_enqueue_style('fancybox', 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css');
  osc_enqueue_script('jquery-validate');

  sigma_add_body_class('item');
  osc_add_hook('after-main','sidebar');
  function sidebar(){
    osc_current_web_theme_path('item-sidebar.php');
  }

  $location = array();
  if( osc_item_city_area() !== '' ) {
    $location[] = osc_item_city_area();
  }
  if( osc_item_city() !== '' ) {
    $location[] = osc_item_city();
  }
  if( osc_item_region() !== '' ) {
    $location[] = osc_item_region();
  }
  if( osc_item_country() !== '' ) {
    $location[] = osc_item_country();
  }

  osc_current_web_theme_path('header.php');
?>
<?php osc_run_hook('item_top'); ?>

<div id="item-content">
  <h1><?php echo osc_item_title(); ?></h1>
  <?php osc_run_hook('item_title'); ?>

  <?php if(osc_price_enabled_at_items() && osc_item_category_price_enabled()) { ?><div class="price price-alt isMobile"><?php echo osc_item_formated_price(); ?></div><?php } ?>

  <div class="item-header">
    <div><?php if ( osc_item_pub_date() !== '' ) { printf( __('<strong class="publish">Published date:</strong> %1$s', 'sigma'), osc_format_date( osc_item_pub_date() ) ); } ?></div>
    <div><?php if ( osc_item_mod_date() !== '' ) { printf( __('<strong class="update">Modified date:</strong> %1$s', 'sigma'), osc_format_date( osc_item_mod_date() ) ); } ?></div>
    <?php if (count($location)>0) { ?>
      <ul id="item_location">
        <li><strong><?php _e("Location", 'sigma'); ?>:</strong> <?php echo implode(', ', $location); ?></li>
      </ul>
    <?php }; ?>

    <?php if(osc_is_web_user_logged_in() && osc_logged_user_id()==osc_item_user_id()) { ?>
      <p id="edit_item_view">
        <strong>
          <a href="<?php echo osc_item_edit_url(); ?>" rel="nofollow"><?php _e('Edit item', 'sigma'); ?></a>
        </strong>
      </p>
    <?php } ?>
  </div>


  <?php if( osc_images_enabled_at_items() ) { ?>
    <?php
    if( osc_count_item_resources() > 0 ) {
      $i = 0;
    ?>
    <div class="item-photos">
      <a href="javascript:;" data-fancybox-trigger="gallery" class="main-photo" title="<?php _e('Image', 'sigma'); ?> <?php echo $i+1;?> / <?php echo osc_count_item_resources();?>">
        <img src="<?php echo osc_resource_url(); ?>" alt="<?php echo osc_item_title(); ?>" title="<?php echo osc_item_title(); ?>" />
      </a>
      <div class="thumbs">
        <?php for ( $i = 0; osc_has_item_resources(); $i++ ) { ?>
        <a href="<?php echo osc_resource_url(); ?>" data-fancybox="gallery" class="fancybox" title="<?php _e('Image', 'sigma'); ?> <?php echo $i+1;?> / <?php echo osc_count_item_resources();?>">
          <img src="<?php echo osc_resource_thumbnail_url(); ?>" width="75" alt="<?php echo osc_item_title(); ?>" title="<?php echo osc_item_title(); ?>" />
        </a>
        <?php } ?>
      </div>
    </div>
    <?php } ?>
  <?php } ?>
  
  <?php osc_run_hook('item_images'); ?> 
  
  <div id="description">
    <div class="desc"><?php echo osc_item_description(); ?></div>

    <?php osc_run_hook('item_description_after'); ?>
    
    <?php if( osc_count_item_meta() >= 1 ) { ?>
      <div id="custom_fields">
        <div class="meta_list">
          <?php while ( osc_has_item_meta() ) { ?>
            <?php if(osc_item_meta_value()!='') { ?>
              <div class="meta">
                <strong><?php echo osc_item_meta_name(); ?>:</strong> <span><?php echo osc_item_meta_value(); ?></span>
              </div>
            <?php } ?>
          <?php } ?>
        </div>
      </div>
    <?php } ?>
    
    <?php osc_run_hook('item_meta'); ?>

    <div class="item-hook"><?php osc_run_hook('item_detail', osc_item() ); ?></div>


    <p class="contact_button">
      <?php if( !osc_item_is_expired () && 1==2) { ?>
        <?php if( !( ( osc_logged_user_id() == osc_item_user_id() ) && osc_logged_user_id() != 0 ) ) { ?>
          <?php if(osc_reg_user_can_contact() && osc_is_web_user_logged_in() || !osc_reg_user_can_contact() ) { ?>
            <a href="#contact-in" class="resp-toogle btn btn-secondary show-contact-btn"><?php _e('Contact seller', 'sigma'); ?></a>
          <?php } ?>
        <?php } ?>
      <?php } ?>

      <a href="#contact-in" class="isDesktop isTablet btn btn-secondary"><?php _e('Contact seller', 'sigma'); ?></a>
      
      <?php if(!osc_item_send_friend_form_disabled()) { ?>
        <a href="<?php echo osc_item_send_friend_url(); ?>" rel="nofollow" class="btn btn-secondary"><?php _e('Share', 'sigma'); ?></a>
      <?php } ?>
    </p>

    <?php osc_run_hook('location'); ?>
    
    <div class="item-views"><?php echo sprintf(__('%d page views', 'sigma'), osc_item_views()); ?></div>

  </div>
  <!-- plugins -->


  <?php related_listings(); ?>
  <?php if( osc_count_items() > 0 ) { ?>
    <div class="similar_ads">
      <h2><?php _e('Related listings', 'sigma'); ?></h2>
      <?php
        View::newInstance()->_exportVariableToView("listType", 'items');
        View::newInstance()->_exportVariableToView("listClass", 'listing-grid');

        osc_current_web_theme_path('loop.php');
      ?>
      <div class="clear"></div>
    </div>
  <?php } ?>


  <?php if(osc_comments_enabled()) { ?>
    <div id="comments">
      <h2><?php _e('Comments', 'sigma'); ?></h2>
      
      <ul id="comment_error_list"></ul>
      <?php CommentForm::js_validation(); ?>
      
      <?php if(osc_count_item_comments() <= 0) { ?>
        <div class="empty"><?php _e('No comments has been published yet', 'sigma'); ?></div>
        
      <?php } else { ?>
        <div class="comments_list">
          <?php while (osc_has_item_comments()) { ?>
            <div class="comment <?php if(osc_profile_img_users_enabled()) { ?>has-user-img<?php } ?>">
              <?php if(osc_profile_img_users_enabled()) { ?>
                <p class="user-img">
                  <img src="<?php echo osc_user_profile_img_url(osc_comment_user_id()); ?>" alt="<?php echo osc_esc_html(osc_comment_author_name()); ?>"/>
                </p> 
              <?php } ?>

              <h3><strong><?php echo osc_comment_title(); ?></strong> <em><?php _e("by", 'sigma'); ?> <?php echo osc_comment_author_name(); ?>:</em></h3>

              <?php if(osc_enable_comment_rating() && osc_comment_rating() > 0) { ?>
                <p class="comment-rating">
                  <?php for($i = 1; $i <= 5; $i++) { ?>
                    <?php
                      $class = '';
                      if(osc_comment_rating() >= $i) {
                        $class = ' fill';
                      }
                    ?>
                    <i class="fa fa-star<?php echo $class; ?>"></i>
                  <?php } ?>

                  <span>(<?php echo (osc_comment_rating() > 0 ? sprintf(__('%d of 5', 'sigma'), osc_comment_rating()) : __('not rated', 'sigma')); ?>)</span>
                </p>
              <?php } ?>

              <p><?php echo nl2br(osc_comment_body()); ?></p>
              
              <?php if (osc_comment_user_id() && (osc_comment_user_id() == osc_logged_user_id()) ) { ?>
                <p class="comment-delete-row"><a rel="nofollow" href="<?php echo osc_delete_comment_url(); ?>" title="<?php _e('Delete your comment', 'sigma'); ?>"><?php _e('Delete', 'sigma'); ?></a></p>
              <?php } ?>

              <?php if(
                osc_enable_comment_reply() 
                && (
                  osc_comment_reply_user_type() == ''
                  || osc_comment_reply_user_type() == 'LOGGED' && osc_is_web_user_logged_in()
                  || osc_comment_reply_user_type() == 'OWNER' && (osc_logged_user_id() == osc_item_user_id() && osc_item_user_id() > 0 || osc_logged_user_email() == osc_item_contact_email())
                  || osc_comment_reply_user_type() == 'ADMIN' && osc_is_admin_user_logged_in()
                )
              ) { ?>
                <p class="comment-reply-row"><a href="#" class="comment-reply" data-id="<?php echo osc_comment_id(); ?>" data-text="<?php echo osc_esc_html(sprintf(__('You are replying to: %s', 'sigma'), osc_highlight(implode(' - ', array_filter(array_map('trim', array(osc_comment_title(), osc_comment_body())))), 60))); ?>" data-rating="<?php echo osc_enable_comment_reply_rating() ? 1 : 0; ?>"><?php _e('Reply', 'sigma'); ?></a></p>
              <?php } ?>
              
              <?php if(osc_enable_comment_reply()) { ?>
                <?php osc_get_comment_replies(); ?>
                <?php if(osc_count_comment_replies() > 0) { ?>
                  <div id="comment-replies">
                    <?php while (osc_has_comment_replies()) { ?>
                      <div class="comment reply <?php if(osc_profile_img_users_enabled()) { ?>has-user-img<?php } ?>">
                        <?php if(osc_profile_img_users_enabled()) { ?>
                          <p class="user-img">
                            <img src="<?php echo osc_user_profile_img_url(osc_comment_reply_user_id()); ?>" alt="<?php echo osc_esc_html(osc_comment_reply_author_name()); ?>"/>
                          </p> 
                        <?php } ?>

                        <h3><strong><?php echo osc_comment_reply_title(); ?></strong> <em><?php _e("by", 'sigma'); ?> <?php echo osc_comment_reply_author_name(); ?>:</em></h3>

                        <?php if(osc_enable_comment_reply_rating() && osc_comment_reply_rating() > 0) { ?>
                          <p class="comment-rating">
                            <?php for($i = 1; $i <= 5; $i++) { ?>
                              <?php
                                $class = '';
                                if(osc_comment_reply_rating() >= $i) {
                                  $class = ' fill';
                                }
                              ?>
                              <i class="fa fa-star<?php echo $class; ?>"></i>
                            <?php } ?>

                            <span>(<?php echo sprintf(__('%d of 5', 'sigma'), osc_comment_reply_rating()); ?>)</span>
                          </p>
                        <?php } ?>

                        <p><?php echo nl2br(osc_comment_reply_body()); ?></p>
                        
                        <?php if ( osc_comment_reply_user_id() && (osc_comment_reply_user_id() == osc_logged_user_id()) ) { ?>
                          <p><a rel="nofollow" href="<?php echo osc_delete_comment_reply_url(); ?>" title="<?php _e('Delete your comment', 'sigma'); ?>"><?php _e('Delete', 'sigma'); ?></a></p>
                        <?php } ?>
                      </div>
                    <?php } ?>
                  </div>
                <?php } ?>
              <?php } ?>
            </div>
          <?php } ?>
        </div>

        <div class="paginate"><?php echo osc_comments_pagination(); ?></div>
      <?php } ?>

      <?php if(osc_reg_user_post_comments() && osc_is_web_user_logged_in() || !osc_reg_user_post_comments()) { ?>
        <div class="form-container form-horizontal new-comment">
          <div class="header">
            <h3><?php _e('Leave your comment (spam and offensive messages will be removed)', 'sigma'); ?></h3>
          </div>
          <div class="resp-wrapper">
            <form action="<?php echo osc_base_url(true); ?>" method="post" name="comment_form" id="comment_form">
              <fieldset>

                <input type="hidden" name="action" value="add_comment" />
                <input type="hidden" name="page" value="item" />
                <input type="hidden" name="id" value="<?php echo osc_item_id(); ?>" />
                <?php if(osc_enable_comment_reply()) { ?><input type="hidden" name="replyId" value="" /><?php } ?>
                <?php if(osc_is_web_user_logged_in()) { ?>
                  <input type="hidden" name="authorName" value="<?php echo osc_esc_html( osc_logged_user_name() ); ?>" />
                  <input type="hidden" name="authorEmail" value="<?php echo osc_logged_user_email();?>" />
                <?php } else { ?>
                  <div class="control-group">
                    <label class="control-label" for="authorName"><?php _e('Your name', 'sigma'); ?></label>
                    <div class="controls">
                      <?php CommentForm::author_input_text(); ?>
                    </div>
                  </div>
                  <div class="control-group">
                    <label class="control-label" for="authorEmail"><?php _e('Your email address', 'sigma'); ?></label>
                    <div class="controls">
                      <?php CommentForm::email_input_text(); ?>
                    </div>
                  </div>
                <?php }; ?>
                
                <?php if(osc_enable_comment_rating()) { ?>
                  <div class="control-group rating">
                    <label class="control-label" for="title"><?php _e('Rating', 'sigma'); ?></label>
                    <div class="controls">
                      <?php if(osc_comment_rating_limit_check()) { ?>
                        <?php //CommentForm::rating_input_text(); ?>
                        <input type="hidden" name="rating" value="" />

                        <div class="comment-leave-rating">
                          <i class="fa fa-star is-rating-item" data-value="1"></i> 
                          <i class="fa fa-star is-rating-item" data-value="2"></i> 
                          <i class="fa fa-star is-rating-item" data-value="3"></i> 
                          <i class="fa fa-star is-rating-item" data-value="4"></i> 
                          <i class="fa fa-star is-rating-item" data-value="5"></i> 
                        </div>
                        
                        <span class="comment-rating-selected"></span>
                      <?php } else { ?>
                        <div class="red"><?php echo sprintf(__('Not available, you have already rated this item %d time(s)', 'sigma'), osc_comment_rating_limit()); ?></div>
                      <?php } ?>    
                    </div>
                  </div>
                <?php } ?>
                
                <div class="control-group">
                  <label class="control-label" for="title"><?php _e('Title', 'sigma'); ?></label>
                  <div class="controls">
                    <?php CommentForm::title_input_text(); ?>
                  </div>
                </div>

                <div class="control-group reply-text" title="<?php echo osc_esc_html(__('Click to post as standard comment and not reply', 'sigma')); ?>"></div>
                
                <?php osc_run_hook('item_comment_form'); ?>
                
                <div class="control-group">
                  <label class="control-label" for="body"><?php _e('Comment', 'sigma'); ?></label>
                  <div class="controls textarea">
                    <?php CommentForm::body_input_textarea(); ?>
                  </div>
                </div>
                
                <div class="actions">
                  <button type="submit" class="btn btn-primary"><?php _e('Send', 'sigma'); ?></button>
                </div>

              </fieldset>
            </form>
          </div>
        </div>
        
      <?php } else { ?>
        <div class="empty"><?php _e('You must be logged in to comment', 'sigma'); ?></div>
      
      <?php } ?>
    </div>
  <?php } ?>
  
  <?php osc_run_hook('item_comment'); ?>
</div>

<?php osc_run_hook('item_bottom'); ?>

<script type="text/javascript">
  $(document).ready(function() {
    $('body').on('click', '.is-rating-item', function(e) {
      e.preventDefault();
      $('input[name="rating"]').val($(this).attr('data-value'));
      $('.comment-rating-selected').text('(' + $(this).attr('data-value') + ' <?php echo osc_esc_js(__('of', 'sigma')); ?> 5)');
      $(this).parent().find('i.is-rating-item').addClass('fill');
      $(this).nextAll('i.is-rating-item').removeClass('fill');
    })
  });
</script>
<?php osc_current_web_theme_path('footer.php') ; ?>