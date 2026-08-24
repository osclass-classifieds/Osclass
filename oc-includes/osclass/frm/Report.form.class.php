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
 * Class ReportForm
 */
class ReportForm extends Form {

  // jquery-validate rules for report submission form
  public static function js_validation() {
    ?>
    <script type="text/javascript">
      $(document).ready(function(){
        $("form[name=report]").validate({
          rules: {
            reason: {
              required: true
            },
            comment: {
              required: true,
              minlength: 3
            }
          },
          messages: {
            reason: {
              required: "<?php echo osc_esc_js(__('Reason: this field is required')); ?>."
            },
            comment: {
              required: "<?php echo osc_esc_js(__('Comment: this field is required')); ?>.",
              minlength: "<?php echo osc_esc_js(__('Comment: enter at least 3 characters')); ?>."
            }
          },
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

  // jquery-validate rules for report reply form
  public static function js_validation_reply() {
    ?>
    <script type="text/javascript">
      $(document).ready(function(){
        $("form[name=report_reply]").validate({
          rules: {
            comment: {
              required: true,
              minlength: 3
            }
          },
          messages: {
            comment: {
              required: "<?php echo osc_esc_js(__('Reply: this field is required')); ?>.",
              minlength: "<?php echo osc_esc_js(__('Reply: enter at least 3 characters')); ?>."
            }
          },
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
