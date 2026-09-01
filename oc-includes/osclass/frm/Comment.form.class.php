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


/**
 * Class CommentForm
 */
class CommentForm extends Form
{

  /**
  * @param null $comment
  */
  public static function primary_input_hidden( $comment = null )
  {
    $commentId = null;
    if( isset($comment['pk_i_id']) ) {
      $commentId = $comment['pk_i_id'];
    }
    if(Session::newInstance()->_getForm('commentId') > 0) {
      $commentId = Session::newInstance()->_getForm('commentId');
    }
    if ( null !== $commentId ) {
      parent::generic_input_hidden( 'id' , $commentId);
    }
  }

  /**
  * @param null $comment
  */
  public static function title_input_text( $comment = null )
  {
    $commentTitle = '';
    if( isset($comment['s_title']) ) {
      $commentTitle = $comment['s_title'];
    }
    if(Session::newInstance()->_getForm('commentTitle') != '') {
      $commentTitle = Session::newInstance()->_getForm('commentTitle');
    }
    parent::generic_input_text( 'title' , $commentTitle);
  }

  /**
  * @param null $comment
  */
  public static function author_input_text( $comment = null )
  {
    $commentAuthorName = '';
    if( isset($comment['s_author_name']) ) {
      $commentAuthorName = $comment['s_author_name'];
    }
    if(Session::newInstance()->_getForm('commentAuthorName') != '') {
      $commentAuthorName = Session::newInstance()->_getForm('commentAuthorName');
    }
    parent::generic_input_text( 'authorName' , $commentAuthorName);
  }

  /**
  * @param null $comment
  */
  public static function email_input_text( $comment = null )
  {
    $commentAuthorEmail = '';
    if( isset($comment['s_author_email']) ) {
      $commentAuthorEmail = $comment['s_author_email'];
    }
    if(Session::newInstance()->_getForm('commentAuthorEmail') != '') {
      $commentAuthorEmail = Session::newInstance()->_getForm('commentAuthorEmail');
    }
    parent::generic_input_text( 'authorEmail' , $commentAuthorEmail);
  }

  /**
  * @param null $comment
  */
  public static function rating_input_text( $comment = null )
  {
    $commentRating = '';
    if( isset($comment['i_rating']) ) {
      $commentRating = $comment['i_rating'];
    }
    if(Session::newInstance()->_getForm('commentRating') != '') {
      $commentRating = Session::newInstance()->_getForm('commentRating');
    }
    parent::generic_input_text( 'rating' , $commentRating);
  }

  /**
  * @param null $comment
  */
  public static function reply_input_text( $comment = null )
  {
    $commentReplyId = '';
    if( isset($comment['fk_i_reply_id']) && $comment['fk_i_reply_id'] !== null ) {
      $commentReplyId = $comment['fk_i_reply_id'];
    }
    if(Session::newInstance()->_getForm('commentReplyId') != '') {
      $commentReplyId = Session::newInstance()->_getForm('commentReplyId');
    }
    parent::generic_input_text( 'replyId' , $commentReplyId);
  }
  
  /**
  * @param null $comment
  */
  public static function body_input_textarea( $comment = null )
  {
    $commentBody = '';
    if( isset($comment['s_body']) ) {
      $commentBody = $comment['s_body'];
    }
    if(Session::newInstance()->_getForm('commentBody') != '') {
      $commentBody = Session::newInstance()->_getForm('commentBody');
    }
    parent::generic_textarea( 'body' , $commentBody);
  }

  /**
  * @param bool $admin
  */
  public static function js_validation( $admin = false ) {
    ?>
    <script type="text/javascript">
    $(document).ready(function(){
    // Code for form validation
      $("form[name=comment_form]").validate({
        rules: {
          body: {
            required: true,
            minlength: 1
          },
          authorEmail: {
            required: true,
            email: true
          }
        },
        messages: {
          authorEmail: {
            required: "<?php _e( 'Email: this field is required' ); ?>.",
            email: "<?php _e( 'Invalid email address' ); ?>."
          },
          body: {
            required: "<?php _e( 'Comment: this field is required' ); ?>.",
            minlength: "<?php _e( 'Comment: this field is required' ); ?>."
          }
        },
        wrapper: "li",
        <?php if($admin) { ?>
          errorLabelContainer: "#error_list",
          invalidHandler: function(form, validator) {
            $('html,body').animate({ scrollTop: $('h1').offset().top }, { duration: 250, easing: 'swing'});
          },
          submitHandler: function(form){
            $('button[type=submit], input[type=submit]').attr('disabled', 'disabled');
            form.submit();
          }
        <?php } else { ?>
          errorLabelContainer: "#comment_error_list",
          invalidHandler: function(form, validator) {
            $('html,body').animate({ scrollTop: $('#comment_error_list').offset().top }, { duration: 250, easing: 'swing'});
          },
          submitHandler: function(form){
            $('button[type=submit], input[type=submit]').attr('disabled', 'disabled');
            form.submit();
          }
        <?php } ?>
      });
    });
    </script>
    <?php
  }
}