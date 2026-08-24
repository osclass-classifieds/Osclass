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
 * Class ContactForm
 */
class ContactForm extends Form {

  /**
  * @return bool
  */
  public static function primary_input_hidden() {
    parent::generic_input_hidden( 'id' , osc_item_id() );
    return true;
  }

  /**
  * @return bool
  */
  public static function page_hidden() {
    parent::generic_input_hidden( 'page' , 'item');
    return true;
  }

  /**
  * @return bool
  */
  public static function action_hidden() {
    parent::generic_input_hidden( 'action' , 'contact_post');
    return true;
  }

  /**
  * @return bool
  */
  public static function your_name() {
    if( Session::newInstance()->_getForm( 'yourName' ) != '' ) {
      $name = Session::newInstance()->_getForm( 'yourName' );
      parent::generic_input_text( 'yourName' , $name);
    } else {
      parent::generic_input_text( 'yourName' , osc_logged_user_name());
    }
    return true;
  }

  /**
  * @return bool
  */
  public static function your_email() {
     if( Session::newInstance()->_getForm( 'yourEmail' ) != '' ) {
      $email = Session::newInstance()->_getForm( 'yourEmail' );
      parent::generic_input_text( 'yourEmail' , $email);
    } else {
      parent::generic_input_text( 'yourEmail' , osc_logged_user_email());
    }
    return true;
  }

  /**
  * @return bool
  */
  public static function your_phone_number() {
    if( Session::newInstance()->_getForm( 'phoneNumber' ) != '' ) {
      $phoneNumber = Session::newInstance()->_getForm( 'phoneNumber' );
      parent::generic_input_text( 'phoneNumber' , $phoneNumber);
    } else {
      parent::generic_input_text( 'phoneNumber' , osc_logged_user_phone());
    }
    return true;
  }

  /**
  * @return bool
  */
  public static function the_subject() {
    if( Session::newInstance()->_getForm( 'subject' ) != '' ) {
      $subject = Session::newInstance()->_getForm( 'subject' );
      parent::generic_input_text( 'subject' , $subject);
    } else {
      parent::generic_input_text( 'subject' , '');
    }
    return true;
  }

  /**
  * @return bool
  */
  public static function your_message() {
    if( Session::newInstance()->_getForm( 'message_body' ) != '' ) {
      $message = Session::newInstance()->_getForm( 'message_body' );
      parent::generic_textarea( 'message' , $message);
    } else {
      parent::generic_textarea( 'message' , '' );
    }
    return true;
  }

  public static function your_attachment() {
    echo '<input type="file" name="attachment" />';
  }

  public static function js_validation() {
    ?>
    <script type="text/javascript">
    $(document).ready(function(){
      // Code for form validation
      $("form[name=contact_form]").validate({
        rules: {
          message: {
            required: true,
            minlength: 1
          },
          yourEmail: {
            required: true,
            email: true
          }
        },
        messages: {
          yourEmail: {
            required: "<?php _e( 'Email: this field is required' ); ?>.",
            email: "<?php _e( 'Invalid email address' ); ?>."
          },
          message: {
            required: "<?php _e( 'Message: this field is required' ); ?>.",
            minlength: "<?php _e( 'Message: this field is required' ); ?>."
          }
        },
        errorLabelContainer: "#error_list",
        wrapper: "li",
        invalidHandler: function(form, validator) {
          $('html,body').animate({ scrollTop: $('form[name=contact_form]').offset().top }, { duration: 250, easing: 'swing'});
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