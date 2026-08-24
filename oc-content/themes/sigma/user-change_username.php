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
    osc_add_hook('header','sigma_nofollow_construct');

    osc_enqueue_script('jquery-validate');

    sigma_add_body_class('user user-profile');
    osc_add_hook('before-main','sidebar');
    function sidebar(){
        osc_current_web_theme_path('user-sidebar.php');
    }
    osc_add_filter('meta_title_filter','custom_meta_title');
    function custom_meta_title($data){
        return __('Change username', 'sigma');;
    }
    osc_current_web_theme_path('header.php') ;
    $osc_user = osc_user();
?>
<h1><?php _e('Change username', 'sigma'); ?></h1>
<script type="text/javascript">
$(document).ready(function() {
    $('form#change-username').validate({
        rules: {
            s_username: {
                required: true
            }
        },
        messages: {
            s_username: {
                required: '<?php echo osc_esc_js(__("Username: this field is required", "sigma")); ?>.'
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

    var cInterval;
    $("#s_username").keydown(function(event) {
        if($("#s_username").attr("value")!='') {
            clearInterval(cInterval);
            cInterval = setInterval(function(){
                $.getJSON(
                    "<?php echo osc_base_url(true); ?>?page=ajax&action=check_username_availability",
                    {"s_username": $("#s_username").attr("value")},
                    function(data){
                        clearInterval(cInterval);
                        if(data.exists==0) {
                            $("#available").text('<?php echo osc_esc_js(__("The username is available", "sigma")); ?>');
                        } else {
                            $("#available").text('<?php echo osc_esc_js(__("The username is NOT available", "sigma")); ?>');
                        }
                    }
                );
            }, 1000);
        }
    });

});
</script>
<div class="form-container form-horizontal">
    <div class="resp-wrapper">
        <ul id="error_list"></ul>
        <form action="<?php echo osc_base_url(true); ?>" method="post" id="change-username">
            <input type="hidden" name="page" value="user" />
            <input type="hidden" name="action" value="change_username_post" />
            <div class="control-group">
                <label class="control-label" for="s_username"><?php _e('Username', 'sigma'); ?></label>
                <div class="controls">
                    <input type="text" name="s_username" id="s_username" value="" />
                    <div id="available"></div>
                </div>
            </div>
            <div class="control-group bts">
                <div class="controls">
                    <button type="submit" class="btn btn-primary"><?php _e("Update", 'sigma');?></button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php osc_current_web_theme_path('footer.php') ; ?>