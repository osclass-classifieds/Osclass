<?php if(!defined('OC_ADMIN')) exit('Direct access is not allowed.'); ?>
      </div></div><div class="clear"></div></div><!-- #grid-system -->
      </div><!-- #content-page -->
      <?php osc_run_hook('admin_footer_before'); ?>
      <div class="clear"></div>
      <div id="footer-wrapper">
        <div id="footer">
          <?php osc_run_hook('admin_footer_pre'); ?>
          
          <div class="float-left">
            <a title="<?php _e('Osclass'); ?>" href="https://osclass-classifieds.com/" target="_blank"><?php _e('Osclass'); ?> v<?php echo OSCLASS_VERSION; ?></a> / 
            <a title="<?php _e('Documentation'); ?>" href="https://docs.osclass-classifieds.com/" target="_blank"><?php _e('Docs'); ?></a> / 
            <a title="<?php _e('Forums'); ?>" href="https://forums.osclasspoint.com/" target="_blank"><?php _e('Forums'); ?></a> / 
            <a title="<?php _e('Official Market'); ?>" href="https://osclasspoint.com/" target="_blank" class="mrkt"><?php _e('Official Market'); ?></a>

            <div class="logged-admin" title="<?php echo osc_esc_html(sprintf(__('Logged in as %s'), osc_logged_admin_username())); ?>">
              <strong><?php echo osc_logged_admin_name(); ?></strong>
            </div>
          </div>
          
          <?php osc_run_hook('admin_footer_after'); ?>
          <div class="clear"></div>
        </div>
      </div>
    </div><!-- #content-render -->
  </div><!-- #content -->
  
  <?php osc_run_hook('admin_footer'); ?>

  <script type="text/javascript">
    $(document).ready(function() {
      $('body').on('click', '.ui-widget-overlay', function(){
        $(".ui-dialog-content").dialog("close");
      });
    });
  </script> 
  </body>
</html>