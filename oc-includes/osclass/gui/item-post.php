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
  //osc_enqueue_script('tabber');

  sigma_add_body_class('item item-post');
  $action = 'item_add_post';
  $edit = false;
  if(Params::getParam('action') == 'item_edit') {
    $action = 'item_edit_post';
    $edit = true;
  }
?>

<?php osc_current_web_theme_path('header.php') ; ?>

<?php
  if (sigma_default_location_show_as() == 'dropdown') {
    ItemForm::location_javascript();
  } else {
    ItemForm::location_javascript_new();
  }
?>

  <div class="form-container form-horizontal">
    <div class="resp-wrapper">
      <div class="header">
        <h1><?php _e('Publish a listing', 'sigma'); ?></h1>
      </div>
      <ul id="error_list"></ul>
        <form name="item" action="<?php echo osc_base_url(true);?>" method="post" enctype="multipart/form-data" id="item-post">
          <fieldset>
          <input type="hidden" name="action" value="<?php echo $action; ?>" />
            <input type="hidden" name="page" value="item" />
          <?php if($edit){ ?>
            <input type="hidden" name="id" value="<?php echo osc_item_id();?>" />
            <input type="hidden" name="secret" value="<?php echo osc_item_secret();?>" />
          <?php } ?>
            <h2 class="gen"><?php _e('General Information', 'sigma'); ?></h2>
            <div class="control-group categ">
              <label class="control-label" for="select_1"><?php _e('Category', 'sigma'); ?></label>
              <div class="controls">
                <?php ItemForm::category_select(null, null, __('Select a category', 'sigma')); ?>
              </div>
            </div>
            <div class="control-group title">
              <label class="control-label" for="title[<?php echo osc_current_user_locale(); ?>]"><?php _e('Title', 'sigma'); ?></label>
              <div class="controls">
                <?php ItemForm::title_input('title',osc_current_user_locale(), osc_esc_html( sigma_item_title() )); ?>
              </div>
            </div>
            <div class="control-group descr">
              <label class="control-label" for="description[<?php echo osc_current_user_locale(); ?>]"><?php _e('Description', 'sigma'); ?></label>
              <div class="controls">
                <?php ItemForm::description_textarea('description',osc_current_user_locale(), osc_esc_html( sigma_item_description() )); ?>
              </div>
            </div>
            <?php if( osc_price_enabled_at_items() ) { ?>
            <div class="control-group control-group-price">
              <label class="control-label" for="price"><?php _e('Price', 'sigma'); ?></label>
              <div class="controls">
                <?php ItemForm::price_input_text(); ?>
                <?php ItemForm::currency_select(); ?>
              </div>
            </div>
            <?php } ?>

            <div class="control-group img">
            <?php if( osc_images_enabled_at_items() ) {
              ItemForm::ajax_photos();
            } ?>
            </div>
            <div class="box location">
              <h2><?php _e('Listing Location', 'sigma'); ?></h2>
              <?php if(count(osc_get_countries()) > 1) { ?>
              <div class="control-group">
                <label class="control-label" for="country"><?php _e('Country', 'sigma'); ?></label>
                <div class="controls">
                  <?php ItemForm::country_select(osc_get_countries(), osc_user()); ?>
                </div>
              </div>
              <div class="control-group">
                <label class="control-label" for="regionId"><?php _e('Region', 'sigma'); ?></label>
                <div class="controls">
                  <?php
                  if (sigma_default_location_show_as() == 'dropdown') {
                    if($edit) {
                      ItemForm::region_select(osc_get_regions(osc_item_country_code()), osc_item());
                    } else {
                      ItemForm::region_select(osc_get_regions(osc_user_field('fk_c_country_code')), osc_user());
                    }
                  } else {
                    if($edit) {
                      ItemForm::region_text(osc_item());
                    } else {
                      ItemForm::region_text(osc_user());
                    }
                  }
                  ?>
                </div>
              </div>
              <?php
              } else {
                $aCountries = osc_get_countries();
                $aRegions = osc_get_regions($aCountries[0]['pk_c_code']);
                ?>
              <input type="hidden" id="countryId" name="countryId" value="<?php echo osc_esc_html($aCountries[0]['pk_c_code']); ?>"/>
              <div class="control-group">
                <label class="control-label" for="region"><?php _e('Region', 'sigma'); ?></label>
                <div class="controls">
                  <?php
                  if (sigma_default_location_show_as() == 'dropdown') {
                    if($edit) {
                      ItemForm::region_select(null, osc_item());
                    } else {
                      ItemForm::region_select(null, osc_user());
                    }
                  } else {
                    if($edit) {
                      ItemForm::region_text(osc_item());
                    } else {
                      ItemForm::region_text(osc_user());
                    }
                  }
                  ?>
                </div>
              </div>
              <?php } ?>

              <div class="control-group">
                <label class="control-label" for="city"><?php _e('City', 'sigma'); ?></label>
                <div class="controls">
                  <?php
                  if (sigma_default_location_show_as() == 'dropdown') {
                    if($edit) {
                      ItemForm::city_select(null, osc_item());
                    } else { // add new item
                      ItemForm::city_select(null, osc_user());
                    }
                  } else {
                    ItemForm::city_text(osc_user());
                  }
                  ?>
                </div>
              </div>
              <div class="control-group">
                <label class="control-label" for="cityArea"><?php _e('City Area', 'sigma'); ?></label>
                <div class="controls">
                  <?php ItemForm::city_area_text(osc_user()); ?>
                </div>
              </div>
              <div class="control-group">
                <label class="control-label" for="address"><?php _e('Address', 'sigma'); ?></label>
                <div class="controls">
                  <?php ItemForm::address_text(osc_user()); ?>
                </div>
              </div>
            </div>

            <!-- seller info -->
            <div class="box seller_info">
              <h2><?php _e("Seller's information", 'sigma'); ?></h2>

              <?php if(!osc_is_web_user_logged_in() ) { ?>
                <div class="control-group">
                  <label class="control-label" for="contactName"><?php _e('Name', 'sigma'); ?></label>
                  <div class="controls">
                    <?php ItemForm::contact_name_text(); ?>
                  </div>
                </div>

                <div class="control-group">
                  <label class="control-label" for="contactEmail"><?php _e('E-mail', 'sigma'); ?></label>
                  <div class="controls">
                    <?php ItemForm::contact_email_text(); ?>
                  </div>
                </div>

                <div class="control-group">
                  <div class="controls checkbox">
                    <?php ItemForm::show_email_checkbox(); ?> <label for="showEmail"><?php _e('Show e-mail on the listing page', 'sigma'); ?></label>
                  </div>
                </div>
              <?php } ?>

              <div class="control-group">
                <label class="control-label" for="contactPhone"><?php _e('Phone', 'sigma'); ?></label>
                <div class="controls">
                  <?php ItemForm::contact_phone_text(); ?>
                </div>
              </div>

              <div class="control-group">
                <div class="controls checkbox">
                  <?php ItemForm::show_phone_checkbox(); ?> <label for="showPhone"><?php _e('Show phone on the listing page', 'sigma'); ?></label>
                </div>
              </div>

              <div class="control-group">
                <label class="control-label" for="contactOther"><?php _e('Other contact', 'sigma'); ?></label>
                <div class="controls">
                  <?php ItemForm::contact_other_text(); ?>
                </div>
              </div>
            </div>

            <div class="hooks"><?php if($edit) { ItemForm::plugin_edit_item(); } else { ItemForm::plugin_post_item(); } ?></div>

            <div class="control-group">
              <?php if( osc_recaptcha_items_enabled() ) { ?>
                <div class="controls recpt"><?php osc_show_recaptcha(); ?></div>
              <?php }?>

              <div class="controls pblbt">
                <button type="submit" class="btn btn-primary pbl"><?php if($edit) { _e("Update", 'sigma'); } else { _e("Publish", 'sigma'); } ?></button>
              </div>
            </div>
          </fieldset>
        </form>
      </div>
    </div>
    <script type="text/javascript">
      $('#price').bind('hide-price', function(){
        $('.control-group-price').hide();
      });

      $('#price').bind('show-price', function(){
        $('.control-group-price').show();
      });

  <?php if(osc_locale_thousands_sep()!='' || osc_locale_dec_point() != '') { ?>
  $().ready(function(){
    $("#price").blur(function(event) {
      var price = $("#price").prop("value");
      <?php if(osc_locale_thousands_sep()!='') { ?>
      while(price.indexOf('<?php echo osc_esc_js(osc_locale_thousands_sep());  ?>')!=-1) {
        price = price.replace('<?php echo osc_esc_js(osc_locale_thousands_sep());  ?>', '');
      }
      <?php }; ?>
      <?php if(osc_locale_dec_point()!='') { ?>
      var tmp = price.split('<?php echo osc_esc_js(osc_locale_dec_point())?>');
      if(tmp.length>2) {
        price = tmp[0]+'<?php echo osc_esc_js(osc_locale_dec_point())?>'+tmp[1];
      }
      <?php }; ?>
      $("#price").prop("value", price);
    });
  });
  <?php }; ?>
</script>
<?php osc_current_web_theme_path('footer.php'); ?>
