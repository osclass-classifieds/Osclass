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
 * Class SendFriendForm
 */
class SendFriendForm extends Form {
  /*static public function primary_input_hidden($page) {
    parent::generic_input_hidden("id", $page["pk_i_id"]);
  }*/

  /**
   * @return bool
   */
  public static function your_name() {
    if(Session::newInstance()->_getForm( 'yourName' ) != '' ){
      $yourName = Session::newInstance()->_getForm( 'yourName' );
      parent::generic_input_text( 'yourName' , $yourName);
    } else {
      parent::generic_input_text( 'yourName' , '');
    }
    return true;
  }

  /**
   * @return bool
   */
  public static function your_email() {
    if(Session::newInstance()->_getForm( 'yourEmail' ) != '' ){
      $yourEmail = Session::newInstance()->_getForm( 'yourEmail' );
      parent::generic_input_text( 'yourEmail' , $yourEmail);
    } else {
      parent::generic_input_text( 'yourEmail' , '');
    }
    return true;
  }

  /**
   * @return bool
   */
  public static function friend_name() {
    if(Session::newInstance()->_getForm( 'friendName' ) != '' ){
      $friendName = Session::newInstance()->_getForm( 'friendName' );
      parent::generic_input_text( 'friendName' , $friendName);
    } else {
      parent::generic_input_text( 'friendName' , '');
    }
    return true;
  }

  /**
   * @return bool
   */
  public static function friend_email() {
    if(Session::newInstance()->_getForm( 'friendEmail' ) != '' ){
      $friendEmail = Session::newInstance()->_getForm( 'friendEmail' );
      parent::generic_input_text( 'friendEmail' , $friendEmail);
    } else {
      parent::generic_input_text( 'friendEmail' , '');
    }
    return true;
  }

  /**
   * @return bool
   */
  public static function your_message() {
    if(Session::newInstance()->_getForm( 'message_body' ) != '' ){
      $message_body = Session::newInstance()->_getForm( 'message_body' );
      parent::generic_textarea( 'message' , $message_body );
    } else {
      parent::generic_textarea( 'message' , '' );
    }
    return true;
  }

  public static function js_validation() {
    ?>
    <script type="text/javascript">
      $(document).ready(function(){
        // Code for form validation
        $("form[name=sendfriend]").validate({
          rules: {
            yourName: {
              required: true
            },
            yourEmail: {
              required: true,
              email: true
            },
            friendName: {
              required: true
            },
            friendEmail: {
              required: true,
              email: true
            },
            message:  {
              required: true
            }
          },
          messages: {
            yourName: {
              required: "<?php echo sprintf(__('%s is required'), __('Your name')); ?>."
            },
            yourEmail: {
              email: "<?php _e( 'The email is not valid' ); ?>.",
              required: "<?php echo sprintf(__('%s is required'), __('Email')); ?>."
            },
            friendName: {
              required: "<?php echo sprintf(__('%s is required'), __("Friend's name")); ?>."
            },
            friendEmail: {
              required: "<?php echo sprintf(__('%s is required'), __("Friend's email")); ?>.",
              email: "<?php _e("Invalid friend's email address"); ?>."
            },
            message: "<?php echo sprintf(__('%s is required'), __('Message')); ?>."

          },
          //onfocusout: function(element) { $(element).valid(); },
          errorLabelContainer: "#error_list",
          wrapper: "li",
          invalidHandler: function(form, validator) {
            $('html,body').animate({ scrollTop: $('h1').offset().top }, { duration: 250, easing: 'swing'});
          },
          submitHandler: function(form){
            $('button[type=submit], input[type=submit]').attr('disabled', 'disabled');
            form.submit();
          }
        });
      });
    </script>
    <?php
  }
}
