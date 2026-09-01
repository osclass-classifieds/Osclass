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


class CAdminSettingsComments extends AdminSecBaseModel {
  //Business Layer...
  function doModel() {
    switch($this->action) {
      case('comments'):
        //calling the comments settings view
        $this->doView('settings/comments.php');
        break;
        
      case('comments_post'):
        // updating comment
        osc_csrf_check();
        $iUpdated     = 0;
        $enabledComments  = Params::getParam('enabled_comments');
        $enabledComments  = (($enabledComments != '') ? true : false);
        $enabledRating  = Params::getParam('enable_comment_rating');
        $enabledRating  = (($enabledRating != '') ? true : false);
        $moderateComments = Params::getParam('moderate_comments');
        $moderateComments = (($moderateComments != '') ? true : false);
        $numModerateComments = Params::getParam('num_moderate_comments');
        $commentsPerPage  = Params::getParam('comments_per_page');
        $notifyNewComment = Params::getParam('notify_new_comment');
        $notifyNewComment = (($notifyNewComment != '') ? true : false);
        $notifyNewCommentUser = Params::getParam('notify_new_comment_user');
        $notifyNewCommentUser = (($notifyNewCommentUser != '') ? true : false);
        $regUserPostComments  = Params::getParam('reg_user_post_comments');
        $regUserPostComments  = (($regUserPostComments != '') ? true : false);

        $enableCommentReply  = Params::getParam('enable_comment_reply');
        $enableCommentReply  = (($enableCommentReply != '') ? true : false);
        $enableCommentReplyRating  = Params::getParam('enable_comment_reply_rating');
        $enableCommentReplyRating  = (($enableCommentReplyRating != '') ? true : false);

        $notifyNewCommentReply  = Params::getParam('notify_new_comment_reply');
        $notifyNewCommentReply  = (($notifyNewCommentReply != '') ? true : false);
        $notifyNewCommentReplyUser  = Params::getParam('notify_new_comment_reply_user');
        $notifyNewCommentReplyUser  = (($notifyNewCommentReplyUser != '') ? true : false);
        $commentRatingLimit  = (int)Params::getParam('comment_rating_limit');

        $commentReplyUserType  = Params::getParam('comment_reply_user_type');

        
        $msg = '';
        if(!osc_validate_int(Params::getParam("num_moderate_comments"))) {
          $msg .= _m("Number of moderate comments must only contain numeric characters")."<br/>";
        }
        
        if(!osc_validate_int(Params::getParam("comments_per_page"))) {
          $msg .= _m("Comments per page must only contain numeric characters")."<br/>";
        }
        
        if($msg!='') {
          osc_add_flash_error_message( $msg, 'admin');
          $this->redirectTo(osc_admin_base_url(true) . '?page=settings&action=comments');
        }

        $iUpdated += osc_set_preference('enabled_comments', $enabledComments);
        $iUpdated += osc_set_preference('enable_comment_rating', $enabledRating);
        
        if($moderateComments) {
          $iUpdated += osc_set_preference('moderate_comments', $numModerateComments);
        } else {
          $iUpdated += osc_set_preference('moderate_comments', '-1');
        }
        
        $iUpdated += osc_set_preference('notify_new_comment', $notifyNewComment);
        $iUpdated += osc_set_preference('notify_new_comment_user', $notifyNewCommentUser);
        $iUpdated += osc_set_preference('comments_per_page', $commentsPerPage);
        $iUpdated += osc_set_preference('reg_user_post_comments', $regUserPostComments);
        $iUpdated += osc_set_preference('enable_comment_reply', $enableCommentReply);
        $iUpdated += osc_set_preference('enable_comment_reply_rating', $enableCommentReplyRating);
        $iUpdated += osc_set_preference('notify_new_comment_reply', $notifyNewCommentReply);
        $iUpdated += osc_set_preference('notify_new_comment_reply_user', $notifyNewCommentReplyUser);
        $iUpdated += osc_set_preference('comment_reply_user_type', $commentReplyUserType);
        $iUpdated += osc_set_preference('comment_rating_limit', $commentRatingLimit);


        if($iUpdated > 0) {
          osc_add_flash_ok_message( _m("Comment settings have been updated"), 'admin');
        }
        
        $this->redirectTo(osc_admin_base_url(true) . '?page=settings&action=comments');
        break;
    }
  }
}

// EOF: ./oc-admin/controller/settings/comments.php