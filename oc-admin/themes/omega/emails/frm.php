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


osc_enqueue_script('tiny_mce');

function customPageHeader(){ 
  ?>
  <h1><?php _e('Settings'); ?></h1>
  <?php
}

osc_add_hook('admin_page_header','customPageHeader');


//customize Head
function customHead() { 
  ?>
  <script type="text/javascript">
    tinyMCE.init({
      mode : "textareas",
      width: "100%",
      height: "600px",
      language: 'en',
      theme_advanced_toolbar_align : "left",
      theme_advanced_toolbar_location : "top",
      
      content_style: "body {font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue',sans-serif;}",
      contextmenu: 'link linkchecker image editimage table spellchecker configurepermanentpen',
      plugins: 'paste print preview importcss searchreplace autolink autosave save directionality visualblocks visualchars fullscreen image link media code codesample table charmap emoticons hr pagebreak nonbreaking toc insertdatetime advlist lists wordcount imagetools textpattern noneditable help charmap quickbars',
      menubar: 'file edit view insert format tools table tc help',
      toolbar1: 'undo redo | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | fontselect fontsizeselect formatselect',
      toolbar2: 'outdent indent | numlist bullist checklist | forecolor backcolor removeformat | link image media blockquote | ltr rtl | pagebreak codesample charmap emoticons | print code fullscreen',
      image_caption: true,
      quickbars_selection_toolbar: 'bold italic underline strikethrough | quicklink h2 h3 h4 | blockquote quickimage quicktable',
      toolbar_mode: 'wrap',
      
      // plugins : [
        // "advlist autolink lists link image charmap preview anchor",
        // "searchreplace visualblocks code fullscreen",
        // "insertdatetime media table contextmenu paste"
      // ],
      entity_encoding : "raw",
      theme_advanced_buttons1_add : "forecolorpicker,fontsizeselect",
      theme_advanced_buttons2_add: "media",
      theme_advanced_buttons3: "fullscreen",
      theme_advanced_disable : "styleselect",
      relative_urls : false,
      remove_script_host : false,
      convert_urls : false,
      mobile: {
        menubar: true,
        toolbar_mode: true
      }
    });


    $(document).ready(function(){
      // dialog filters
      $('#dialog-test-it').dialog({
        autoOpen: false,
        modal: true,
        width: 360,
        minHeight: 42,
        title: '<?php echo osc_esc_js( __('Send email') ); ?>'
      });
      $('#btn-display-test-it').click(function(){
        $('#dialog-test-it').dialog('open');
        return false;
      });

      $('#btn-test-it').click(function(e) {
        var name   = $('input[name*="#s_title"]:visible').attr('name');
        var locale = name.replace("#s_title","");

        var idTinymce = locale+"#s_text";

        $.post('<?php echo osc_admin_base_url(true); ?>',
        {
          page: 'ajax',
          action: 'test_mail_template',
          email:  $('input[name="test_email"]:visible').val(),
          title:  $('input[name*="s_title"]:visible').val(),
          body: tinyMCE.get(idTinymce).getContent({format : 'html'})
        },
        function(data) {
          alert(data.html);
        }, 'json');
        return false;
      });
    });

  </script>
  <?php
}

osc_add_hook('admin_header','customHead', 10);

function customPageTitle($string) {
  return sprintf(__('Edit email template - %s'), $string);
}

osc_add_filter('admin_title', 'customPageTitle');


$email    = __get("email");
$aEmailVars = EmailVariables::newInstance()->getVariables( $email );

$locales = OSCLocale::newInstance()->listAllEnabled();

osc_current_admin_theme_path('parts/header.php'); 
?>

<div class="grid-row no-bottom-margin">
  <div class="row-wrapper">
    <h2 class="render-title"><?php _e('Edit email template'); ?></h2>
  </div>
</div>
<div id="pretty-form">
  <div class="grid-row grid-100">
    <div class="row-wrapper">
      <div id="item-form">
        <div id="mid-side">
          <?php printLocaleTabs(); ?>
           <form action="<?php echo osc_admin_base_url(true); ?>" method="post">
            <input type="hidden" name="page" value="emails" />
            <input type="hidden" name="action" value="edit_post" />
            <?php PageForm::primary_input_hidden($email); ?>
            <div id="left-side">
              <?php printLocaleTitlePage($locales, $email); ?>
              <div>
                <label><?php _e('Internal name'); ?></label>
                <?php PageForm::internal_name_input_text($email); ?>
                <div class="flashmessage flashmessage-warning flashmessage-inline">
                  <p><?php _e('Used to identify the email template'); ?></p>
                </div>
              </div>
              <div class="input-description-wide">
                <?php printLocaleDescriptionPage($locales, $email); ?>
              </div>
            </div>
            
            <div id="right-side" class="legend">
              <div class="well ui-rounded-corners">
                <h3 style="margin: 0;margin-bottom: 10px;text-align: center; color: #616161;"><?php _e('Legend'); ?></h3>
                <?php foreach($aEmailVars as $key => $value) { ?>
                <label><b><?php echo $key; ?></b><br/><?php echo $value;?></label><hr/>
                <?php } ?>
              </div>
            </div>

            <div class="clear"></div>
            <div class="form-actions form-inline">
              <input type="submit" value="<?php echo osc_esc_html(__('Save changes')); ?>" class="btn btn-submit" />
              <a id="btn-display-test-it" class="btn btn-submit"><?php _e('Test it'); ?></a>
            </div>
          </form>
        </div>

        <div class="clear"></div>
      </div>
    </div>
  </div>
</div>

<div id="dialog-test-it" class="hide">
  <input type="text" name="test_email" class="input-actions" value="<?php echo osc_esc_html(osc_contact_email()); ?>"/>
  <input type="submit" id="btn-test-it" href="#" class="btn btn-blue submit-right" value="<?php _e('Send email'); ?>"/>
</div>
<?php osc_current_admin_theme_path('parts/footer.php'); ?>