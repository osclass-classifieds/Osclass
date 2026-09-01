<?php
if(!defined('ABS_PATH')) exit('ABS_PATH is not loaded. Direct access is not allowed.');

/*
 * Copyright 2020 OsclassPoint.com
 *
 * Osclass maintained & developed by OsclassPoint.com
 * you may not use this file except in compliance with the License.
 * You may download copy of Osclass at
 *
 *    https://osclass-classifieds.com/download
 *
 * Software is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 */


/**
 * Class ItemForm
 */
class ItemForm extends Form {

  /**
  * @param $item
  */
  public static function primary_input_hidden($item) {
    if($item==null) { $item = osc_item(); }
    parent::generic_input_hidden('id' , $item[ 'pk_i_id' ]);
  }

  /**
  * @param null $categories
  * @param null $item
  * @param null $default_item
  * @param bool $parent_selectable
  *
  * @return bool
  * @throws \Exception
  */
  public static function category_select($categories = null , $item = null , $default_item = null , $parent_selectable = false) {
    // Did user select a specific category to post in?
    $catId = Params::getParam('catId');
    if(Session::newInstance()->_getForm('catId') > 0) {
      $catId = Session::newInstance()->_getForm('catId');
    }

    if($categories == null) {
      if(View::newInstance()->_exists('categories')) {
        $categories = View::newInstance()->_get('categories');
      } else {
        $categories = osc_get_categories();
      }
    }

    if ($item == null) { $item = osc_item(); }

    echo '<select name="catId" id="catId">';
    if(isset($default_item)) {
      echo '<option value="">' . $default_item . '</option>';
    } else {
      echo '<option value="">' . __('Select a category') . '</option>';
    }

    if(count($categories)==1) { $parent_selectable = 1; }

    foreach($categories as $c) {
      if (!osc_selectable_parent_categories() && !$parent_selectable) {
        echo '<optgroup label="' . $c['s_name'] . '">';
        if(isset($c['categories']) && is_array($c['categories'])) {
          self::subcategory_select($c[ 'categories' ] , $item , $default_item , 1);
        }
      } else {
        $selected = ((isset($item[ 'fk_i_category_id' ]) && $item[ 'fk_i_category_id' ] == $c['pk_i_id']) || (isset($catId) && $catId == $c['pk_i_id']));
        echo '<option value="' . $c['pk_i_id'] . '"' . ($selected ? ' selected="selected"' : ''). '>' . $c['s_name'] . '</option>';
        if(isset($c['categories']) && is_array($c['categories'])) {
          self::subcategory_select($c[ 'categories' ] , $item , $default_item , 1);
        }
      }
    }
    echo '</select>';
    return true;
  }

  /**
  * @param null $categories
  * @param null $item
  * @param null $default_item
  * @param bool $parent_selectable
  *
  * @throws \Exception
  */
  public static function category_two_selects($categories = null , $item = null , $default_item = null , $parent_selectable = false) {
    $categoryID = Params::getParam('catId');
    
    if(osc_item_category_id() != null) {
      $categoryID = osc_item_category_id();
    }

    if(Session::newInstance()->_getForm('catId') > 0) {
      $categoryID = Session::newInstance()->_getForm('catId');
    }

    $subcategoryID = '';
    if(!Category::newInstance()->isRoot($categoryID)) {
      $subcategoryID = $categoryID;
      $category    = Category::newInstance()->findRootCategory($categoryID);
      $categoryID  = $category['pk_i_id'];
    }

    if($categories == null) {
      if(View::newInstance()->_exists('categories')) {
        $categories = View::newInstance()->_get('categories');
      } else {
        $categories = osc_get_categories();
      }
    }

    if ($item == null) { $item = osc_item(); }

    $subcategory = array();
    ?>
    <select id="parentCategory" name="parentCatId">
      <option value=""><?php _e('Select Category'); ?></option>
      <?php foreach($categories as $_category) {
        $selected = ((isset($item[ 'fk_i_category_id' ]) && $item[ 'fk_i_category_id' ] == $_category['pk_i_id']) || (isset($categoryID) && $categoryID == $_category['pk_i_id']));
        if($selected) { $subcategory = $_category; }
        echo '<option value="'.$_category['pk_i_id'].'" '.($selected ? 'selected="selected"' : '').'>'.$_category['s_name'].'</option>';
      } ?>
    </select>
    <select id="catId" name="catId">
      <?php
      if(!empty($subcategory)) {
        if(count($subcategory['categories']) > 0) {
          echo '<option value="">'.__('Select Subcategory').'</option>';
          foreach($subcategory['categories'] as $c) {
            $selected = ((isset($item[ 'fk_i_category_id' ]) && $item[ 'fk_i_category_id' ] == $c['pk_i_id']) || (isset($subcategoryID) && $subcategoryID == $c['pk_i_id']));
            echo '<option value="'.$c['pk_i_id'].'" '.($selected ? 'selected="selected"' : '').'>'.$c['s_name'].'</option>';
          }
        } else {
          echo '<option value="'.$category['pk_i_id'].'" >'.__('No Subcategory').'</option>';
        }
      } else {
        echo '<option value="">'.__('Select Subcategory').'</option>';
      }
      ?>
    </select>
    
    <script type="text/javascript" charset="utf-8">
      <?php
        foreach($categories as $c) {
          if(count($c['categories']) > 0) {
            $subcategory = array();
            foreach ($c[ 'categories' ] as $i => $iValue) {
              $subcategory[] = array($c['categories'][$i]['pk_i_id'], $c['categories'][$i]['s_name']);
            }
            printf('var categories_%1$s = %2$s;', $c['pk_i_id'], json_encode($subcategory));
            echo PHP_EOL;
          }
        }
      ?>

      if(osc==undefined) { var osc = {}; }
      if(osc.langs==undefined) { osc.langs = {}; }
      if(osc.langs.select_subcategory==undefined) { osc.langs.select_subcategory = '<?php echo osc_esc_js(__('Select Subcategory')); ?>'; }
      if(osc.langs.no_subcategory==undefined) { osc.langs.no_subcategory = '<?php echo osc_esc_js(__('No Subcategory')); ?>'; }

      $(document).ready(function(){
        $("#parentCategory").bind('change', function(){
          var categoryID = $(this).val();
          if(categoryID == 0) {
            var options = '<option value="' + categoryID + '" selected="">' + osc.langs.no_subcategory + '</option>';
          }
          categories = window['categories_' + categoryID];
          if(categories==null || !$.isArray(categories)) {
            var options = '<option value="' + categoryID + '" >' + osc.langs.no_subcategory + '</option>';
          } else {
            var options = '<option value="' + categoryID + '" >' + osc.langs.select_subcategory + '</option>';
            $.each(categories, function(index, value){
              options += '<option value="' + value[0] + '">' + value[1] + '</option>';
            });
          };
          $('#catId').html(options);
          $("#catId").next("a").find(".select-box-label").text(osc.langs.select_subcategory);
          $("#catId").change();
        });
      });
    </script>
  <?php
  }

  /**
  * @param null $categories
  * @param null $item
  * @param null $default_item
  * @param bool $parent_selectable
  *
  * @throws \Exception
  */
  public static function category_multiple_selects($categories = null, $item = null, $default_item = null, $parent_selectable = false) {
    $categoryID = Params::getParam('catId');
    if( osc_item_category_id() != null ) {
      $categoryID = osc_item_category_id();
    }

    if( Session::newInstance()->_getForm('catId') > 0 ) {
      $categoryID = Session::newInstance()->_getForm('catId');
    }

    if ($item == null) { $item = osc_item(); }

    if(isset($item['fk_i_category_id'])) {
      $categoryID = $item['fk_i_category_id'];
    }

    $tmp_categories_tree = Category::newInstance()->toRootTree($categoryID);
    $categories_tree = array();
    
    foreach($tmp_categories_tree as $t) {
      $categories_tree[] = (isset($t['pk_i_id']) ? $t['pk_i_id'] : null);
    }
    
    unset($tmp_categories_tree);

    if($categories == null) {
      $categories = Category::newInstance()->listEnabled();
    }

    parent::generic_input_hidden('catId', $categoryID);
    ?>
    <div id="select_holder"></div>
    <script type="text/javascript" charset="utf-8">
      <?php
        $tmp_cat = array();
        foreach($categories as $c) {
          if($c['fk_i_parent_id']==null ) { 
            $c['fk_i_parent_id'] = 0;
          }
          
          $tmp_cat[$c['fk_i_parent_id']][] = array($c['pk_i_id'], $c['s_name']);
        }
        
        // List of subcategories for each "parent" category
        foreach($tmp_cat as $k => $v) {
          echo 'var categories_'.$k.' = '.json_encode($v).';'.PHP_EOL;
        }
      ?>

      if(osc == undefined) { 
        var osc = {}; 
      }
      
      if(osc.langs == undefined) { 
        osc.langs = {}; 
      }
      
      if(osc.langs.select_category == undefined) { 
        osc.langs.select_category = '<?php echo osc_esc_js(__('Select category')); ?>'; 
      }
      
      if(osc.langs.select_subcategory == undefined) { 
        osc.langs.select_subcategory = '<?php echo osc_esc_js(__('Select subcategory')); ?>';
      }
      
      osc.item_post = {};
      osc.item_post.category_id  = '<?php echo $categoryID; ?>';
      osc.item_post.category_tree_id  = <?php echo json_encode($categories_tree); ?>;

      $(document).ready(function(){
        <?php if($categoryID == array()) { ?>
        draw_select(1,0);
        <?php } else { ?>
          draw_select(1,0);
          <?php for($i=0; $i<count($categories_tree)-1; $i++) { ?>
          draw_select(<?php echo ($i+2); ?> ,<?php echo $categories_tree[$i]; ?>);
          <?php } ?>
          
          window.setTimeout(function() {
            $('#select_<?php echo ($i+2-1); ?>').trigger('change');
          }, 200);
        <?php } ?>
        
        
        // When chaning category, remove it's children select boxes
        $('body').on('change', '[name^="select_"]', function() {
          var depth = parseInt($(this).attr("depth"));
          var maxDepth = parseInt(Math.max(4, <?php echo osc_num_category_levels(); ?>));
          
          // Category select boxes has name by it's depts, select_1, select_2, select_3, ...
          for(var d=(depth+1); d<=maxDepth; d++) {
            $("#select_"+d).trigger('removed');
            $("#select_"+d).remove();
          }
          
          $("#catId").attr("value", $(this).val());
          $("#catId").change();
          
          if(catPriceEnabled[$('#catId').val()] == 1) {
            $('.price').show();
          } else {
            $('.price').hide();
            $('#price').val('') ;
          }
          
          if((depth==1 && $(this).val()!=0) || (depth>1 && $(this).val()!=$("#select_"+(depth-1)).val())) {
            draw_select(depth+1, $(this).val());
          }
          
          return true;
        });
      });

      // Generate category select box
      function draw_select(select, categoryID) {
        tmp_categories = window['categories_' + categoryID];    // list of subcategories for each "parent" category

        if(tmp_categories != null && $.isArray(tmp_categories)) {
          $("#select_holder").before('<select id="select_'+select+'" name="select_'+select+'" depth="'+select+'"></select>');

          if(categoryID==0) {
            var options = '<option value="' + categoryID + '" >' + osc.langs.select_category + '</option>';
          } else {
            var options = '<option value="' + categoryID + '" >' + osc.langs.select_subcategory + '</option>';
          }
          
          var hasSelected = false;
          
          $.each(tmp_categories, function(index, catRow){   // catRow[0] == cat ID, catRow[1] == cat name
            // Sufficient to check if category is in array only
            // options += '<option value="' + value[0] + '" '+(value[0]==osc.item_post.category_tree_id[select-1]?'selected="selected"':'')+'>' + value[1] + '</option>';
            options += '<option value="' + catRow[0] + '" ' + (osc.item_post.category_tree_id.indexOf(catRow[0]) >= 0 ? 'selected="selected"' : '') + '>' + catRow[1] + '</option>';
            
            if(osc.item_post.category_tree_id.indexOf(catRow[0]) >= 0) {
              hasSelected = true;
            }
          });
          
          //osc.item_post.category_tree_id[select-1] = null;
          
          $('#select_'+select).html(options);
          $('#select_'+select).next("a").find(".select-box-label").text(osc.langs.select_subcategory);
          $('#select_'+select).trigger("created");
        };

      }
    </script>
  <?php
  }


  /**
  * @param    $categories
  * @param    $item
  * @param null $default_item
  * @param int  $deep
  */
  public static function subcategory_select($categories , $item , $default_item = null , $deep = 0) {
    // Did user select a specific category to post in?
    $catId = Params::getParam('catId');
    
    if(Session::newInstance()->_getForm('catId') > 0){
      $catId = Session::newInstance()->_getForm('catId');
    }
    
    // How many indents to add?
    $deep_string = '';
    for($var = 0;$var<$deep;$var++) {
      $deep_string .= '&nbsp;&nbsp;';
    }
    
    $deep++;

    foreach($categories as $c) {
      $selected = ((isset($item[ 'fk_i_category_id' ]) && $item[ 'fk_i_category_id' ] == $c['pk_i_id']) || (isset($catId) && $catId == $c['pk_i_id']));

      echo '<option value="' . $c['pk_i_id'] . '"' . ($selected ? ' selected="selected' . (isset($item['fk_i_category_id']) ? $item['fk_i_category_id'] : '') . '"' : '') . '>' . $deep_string . $c['s_name'] . '</option>';
      if(isset($c['categories']) && is_array($c['categories'])) {
        self::subcategory_select($c[ 'categories' ] , $item , $default_item , $deep);
      }
    }
  }

  /**
  * @param null $users
  * @param null $item
  * @param null $default_item
  *
  * @return bool
  */
  public static function user_select($users = null , $item = null , $default_item = null) {
    if($users==null) { $users = User::newInstance()->listAll(); }
    if($item==null) { $item = osc_item(); }
    
    $userId = '';
    if (Session::newInstance()->_getForm('userId') > 0) {
      $userId = Session::newInstance()->_getForm('userId');
    }
    
    echo '<select name="userId" id="userId">';
  
    if(isset($default_item)) {
      echo '<option value="">' . $default_item . '</option>';
    }
    
    foreach($users as $user) {
      $bool = false;
      if($userId > 0 && $userId == $user['pk_i_id']){$bool = true;}
      if(isset($item[ 'fk_i_user_id' ]) && $item[ 'fk_i_user_id' ] == $user['pk_i_id']){$bool = true;}
      
      echo '<option value="' . $user['pk_i_id'] . '"' . ($bool ? ' selected="selected"' : '') . '>';

      if(isset($user['s_name']) && !empty($user['s_name'])) {
        echo $user['s_name'];
      } else {
        echo $user['s_email'];
      }
      echo '</option>';
    }

    echo '</select>';
    
    return true;
  }


  /**
  * @param string $type
  * @param string $value
  *
  * @return bool
  */
  public static function expiration_input($type = 'add' , $value = '') {
    if($type === 'edit') {
      $value = '-1';  // default no change expiration date
    }
    
    echo '<input id="dt_expiration" type="text" name="dt_expiration" value="'.osc_esc_html(htmlentities($value, ENT_COMPAT, 'UTF-8')) . '" placeholder="yyyy-mm-dd HH:mm:ss" />';
    return true;
  }


  /**
  * @param null $options
  *
  * @return bool
  */
  public static function expiration_select($options = null) {
    if(OC_ADMIN) {
      if($options==null) { $options = array(-1,0,1,3,5,7,10,15,30); }
    } else {
      if($options==null) { $options = array(0,1,3,5,7,10,15,30); }
    }
    
    echo '<select name="dt_expiration" id="dt_expiration"></select>';
    $categories = Category::newInstance()->listEnabled();
    ?>
    <script type="text/javascript" >
      var exp_days = new Array();
      <?php foreach($categories as $c) {
        echo 'exp_days['.$c['pk_i_id'].'] = '.$c['i_expiration_days'].';';
      }?>
      
      $(document).ready(function(){
        $('body').on('change', '#catId', function(){
          draw_expiration(exp_days[this.value]);
        });
        draw_expiration(exp_days[$("#catId").value]);
      });
      
      if(osc==undefined) { var osc = {}; }
      if(osc.langs==undefined) { osc.langs = {}; }
      if(osc.langs.nochange_expiration==undefined) { osc.langs.nochange_expiration = '<?php echo osc_esc_js(__('No change expiration')); ?>'; }
      if(osc.langs.without_expiration==undefined) { osc.langs.without_expiration = '<?php echo osc_esc_js(__('Without expiration')); ?>'; }
      if(osc.langs.expiration_day==undefined) { osc.langs.expiration_day = '<?php echo osc_esc_js(__('1 day')); ?>'; }
      if(osc.langs.expiration_days==undefined) { osc.langs.expiration_days = '<?php echo osc_esc_js(__('%d days')); ?>'; }
      
      function draw_expiration(max_exp) {
        $('#dt_expiration').html("");
        var options = '';
        <?php foreach($options as $o) {
          if($o==-1) {?>
            options += '<option value="-1" >' + (osc.langs.nochange_expiration!=null?osc.langs.nochange_expiration:'<?php echo osc_esc_js(__('No change expiration')); ?>') + '</option>';
          <?php } else if($o==0) { ?>
            options += '<option value="" >' + (osc.langs.without_expiration!=null?osc.langs.without_expiration:'<?php echo osc_esc_js(__('Without expiration')); ?>') + '</option>';
          <?php } else if($o==1) { ?>
            options += '<option value="1" >' + (osc.langs.expiration_day!=null?osc.langs.expiration_day:'<?php echo osc_esc_js(__('1 day')); ?>')+ '</option>';
          <?php } else { ?>
            if(max_exp==0 || max_exp>=<?php echo $o; ?>) {
              options += '<option value="<?php echo $o; ?>" >' + (osc.langs.expiration_days!=null?osc.langs.expiration_days:'<?php echo osc_esc_js(__('%d days')); ?>').replace("%d", <?php echo $o; ?>) + '</option>';
            }
        <?php }
        } ?>
        $('#dt_expiration').html(options);
        $('#dt_expiration').change();
      }
    </script>
    
    <?php
    return true;
  }

  /**
  * @param    $name
  * @param string $locale
  * @param string $value
  *
  * @return bool
  */
  public static function title_input($name , $locale = 'en_US' , $value = '') {
    parent::generic_input_text($name . '[' . $locale . ']', $value);
    return true;
  }

  /**
  * @param    $name
  * @param string $locale
  * @param string $value
  *
  * @return bool
  */
  public static function description_textarea($name , $locale = 'en_US' , $value = '') {
    parent::generic_textarea($name . '[' . $locale . ']', $value);
    return true;
  }

  /**
  * @param null $locales
  * @param null $item
  */
  public static function multilanguage_title_description($locales = null , $item = null) {
    if($locales==null) { $locales = osc_get_locales(); }
    if($item==null) { $item = osc_item(); }
    $num_locales = count($locales);

    if($num_locales>1) { echo '<div class="tabber">'; }
    
    foreach($locales as $locale) {
      if($num_locales>1) { echo '<div class="tabbertab">'; }
      if($num_locales>1) { echo '<h2>' . $locale['s_name'] . '</h2>'; }
    
      echo '<div class="title">';
      echo '<div><label for="title">' . __('Title') . ' *</label></div>';
      $title = (isset($item) && isset($item['locale'][$locale['pk_c_code']]) && isset($item['locale'][$locale['pk_c_code']]['s_title'])) ? $item['locale'][$locale['pk_c_code']]['s_title'] : '';

      if(Session::newInstance()->_getForm('title') != '') {
        $title_ = Session::newInstance()->_getForm('title');
        if($title_[$locale['pk_c_code']] != ''){
          $title = $title_[$locale['pk_c_code']];
        }
      }
      
      self::title_input('title', $locale['pk_c_code'], $title);
      echo '</div>';
      echo '<div class="description">';
      echo '<div><label for="description">' . __('Description') . ' *</label></div>';
      
      $description = (isset($item) && isset($item['locale'][$locale['pk_c_code']]) && isset($item['locale'][$locale['pk_c_code']]['s_description'])) ? $item['locale'][$locale['pk_c_code']]['s_description'] : '';
      if(Session::newInstance()->_getForm('description') != '') {
        $description_ = Session::newInstance()->_getForm('description');
        if($description_[$locale['pk_c_code']] != ''){
          $description = $description_[$locale['pk_c_code']];
        }
      }
      
      self::description_textarea('description', $locale['pk_c_code'], $description);
      echo '</div>';
      if($num_locales>1) { echo '</div>'; }
    }
    
    if($num_locales>1) { echo '</div>'; }
  }

  /**
  * @param null $item
  * @param string $type (HTML input type, text or number)
  */
  public static function price_input_text($item = null, $type = 'text') {
    if($item==null) { $item = osc_item(); }
    if(Session::newInstance()->_getForm('price') != '') {
      $item['i_price'] = Session::newInstance()->_getForm('price');
    }
    parent::generic_input_text('price', isset($item['i_price']) ? osc_prepare_price($item['i_price']) : null, null, false, true, -1, $type);
  }

  /**
  * @param null $item
  * @param string $type (HTML input type, text or number)
  */
  public static function pub_date_input_text($item = null, $type = 'text', $placeholder = 'yyyy-mm-dd hh:mm:ss') {
    if($item==null) { $item = osc_item(); }
    if(Session::newInstance()->_getForm('dt_pub_date') != '') {
      $item['dt_pub_date'] = Session::newInstance()->_getForm('dt_pub_date');
    }

    parent::generic_input_text('dt_pub_date', isset($item['dt_pub_date']) ? $item['dt_pub_date'] : null, null, false, true, -1, $type, $placeholder);
  }
  
  /**
  * @param null $item
  * @param string $type (HTML input type, text or number)
  */
  public static function expiration_date_input_text($item = null, $type = 'text', $placeholder = 'yyyy-mm-dd hh:mm:ss') {
    if($item==null) { $item = osc_item(); }
    if(Session::newInstance()->_getForm('dt_expiration') != '') {
      $item['dt_expiration'] = Session::newInstance()->_getForm('dt_expiration');
    }

    $expire_date = '';
    
    if(isset($item['dt_expiration']) && $item['dt_expiration'] != '9999-12-31 23:59:59') {
      $expire_date = $item['dt_expiration'];
    }
    
    parent::generic_input_text('dt_expiration', $expire_date, null, false, true, -1, $type, $placeholder);
  }
  
  /**
  * @param array|null $currencies
  * @param array|null $item
  */
  public static function currency_select($currencies = null , $item = null) {
    if($currencies == null) { $currencies = osc_get_currencies(); }
    if($item == null) { $item = osc_item(); }
    
    if(Session::newInstance()->_getForm('currency') != '') {
      $item['fk_c_currency_code'] = Session::newInstance()->_getForm('currency');
    }
    
    if(count($currencies) > 1) {
      $default_key = null;
      $currency = osc_get_preference('currency');
      if(isset($item['fk_c_currency_code'])) {
        $default_key = $item['fk_c_currency_code'];
      } elseif(isset($currency)) {
        $default_key = $currency;
      }

      parent::generic_select('currency', $currencies, 'pk_c_code', 's_description', null, $default_key);
    } else if(count($currencies) == 1) {
      parent::generic_input_hidden('currency' , $currencies[0][ 'pk_c_code' ]);
      echo $currencies[0]['s_description'];
    }
  }

  /**
   * @param array|null $countries
   * @param array|null $item
   *
   * @return bool
   */
  public static function country_select($countries = null , $item = null) {
    if($countries==null) { 
      $countries = osc_get_countries(); 
    }
    
    if($item==null) { 
      $item = osc_item(); 
    }
    
    if(count($countries) >= 1) {
      if(Session::newInstance()->_getForm('countryId') != '') {
        $item['fk_c_country_code'] = Session::newInstance()->_getForm('countryId');
      }
      
      parent::generic_select('countryId', $countries, 'pk_c_code', 's_name', __('Select a country...'), isset($item['fk_c_country_code']) ? $item['fk_c_country_code'] : null);
      return true;
    } else {
      if(Session::newInstance()->_getForm('country') != '') {
        $item['s_country'] = Session::newInstance()->_getForm('country');
      }
      
      parent::generic_input_text('country', isset($item['s_country']) ? $item['s_country'] : null);
      return true;
    }
  }

  /**
   * @param null $item
   *
   * @return bool
   */
  public static function country_text($item = null) {
    if($item==null) { 
      $item = osc_item(); 
    }
    
    if(Session::newInstance()->_getForm('country') != '') {
      $item['s_country'] = Session::newInstance()->_getForm('country');
    }
    
    $readonly = false;
    if(!isset($item['s_country'])) {
      $countries = osc_get_countries();
      if(count($countries)==1) {
        $item['s_country'] = $countries[0]['s_name'];
        $item['fk_c_country_code'] = $countries[0]['pk_c_code'];
        $readonly = true;
      }
    }
    
    parent::generic_input_text('countryName', isset($item['s_country']) ? $item['s_country'] : null, null, $readonly);
    parent::generic_input_hidden('countryId', (isset($item['fk_c_country_code']) && $item['fk_c_country_code']!=null)?$item['fk_c_country_code']:'');
    return true;
  }

  /**
   * @param null $regions
   * @param null $item
   *
   * @return bool
   */
  public static function region_select($regions = null, $item = null, $generate_all = false) {
    if($item==null) { 
      $item = osc_item(); 
    }
    
    $country_id = (Session::newInstance()->_getForm('countryId') <> '' ? Session::newInstance()->_getForm('countryId') : @$item['fk_c_country_code']);

    $generate_blank = false;
    
    if($regions == null || !is_array($regions) || @empty($regions)) {
      if($country_id <> '') {
        $regions = Region::newInstance()->findByCountry($country_id);
      } else if(count(osc_get_countries()) <= 1 || $generate_all) {
        $regions = Region::newInstance()->listAll();
      } else {
        $generate_blank = true;
      }
    }
    
    if((is_array($regions) && count($regions) >= 1) || $generate_blank == true) {
      if(Session::newInstance()->_getForm('regionId') > 0) {
        $item['fk_i_region_id'] = Session::newInstance()->_getForm('regionId');
      }
      
      parent::generic_select('regionId', $regions, 'pk_i_id', 's_name', __('Select a region...'), isset($item['fk_i_region_id']) ? $item['fk_i_region_id'] : null);
      return true;
    } else {
      if(Session::newInstance()->_getForm('region') != '') {
        $item['s_region'] = Session::newInstance()->_getForm('region');
      }
      
      parent::generic_input_text('region', isset($item['s_region']) ? $item['s_region'] : null);
      return true;
    }
  }


  /**
   * @param null $cities
   * @param null $item
   *
   * @return bool
   */
  public static function city_select($cities = null, $item = null) {
    if($item==null) { 
      $item = osc_item(); 
    }
    
    $country_id = (Session::newInstance()->_getForm('countryId') <> '' ? Session::newInstance()->_getForm('countryId') : @$item['fk_c_country_code']);
    $region_id = (Session::newInstance()->_getForm('regionId') > 0 ? Session::newInstance()->_getForm('regionId') : @$item['fk_i_region_id']);

    $generate_blank = false;
    
    
    if($cities == null || !is_array($cities) || @empty($cities)) {
      if($region_id > 0) {
        $cities = City::newInstance()->findByRegion($region_id);
      } else {
        $generate_blank = true;
      }
    }
    

    if((is_array($cities) && count($cities) >= 1) || $generate_blank == true) {
      if(Session::newInstance()->_getForm('cityId') > 0) {
        $item['fk_i_city_id'] = Session::newInstance()->_getForm('cityId');
      }
     
      parent::generic_select('cityId', $cities, 'pk_i_id', 's_name', __('Select a city...'), isset($item['fk_i_city_id']) ? $item['fk_i_city_id'] : null);
      return true;
    } else {
      if(Session::newInstance()->_getForm('city') != '') {
        $item['s_city'] = Session::newInstance()->_getForm('city');
      }
      parent::generic_input_text('city', isset($item['s_city']) ? $item['s_city'] : null);
      return true;
    }
  }

  /**
  * @param null $item
  *
  * @return bool
  */
  public static function region_text($item = null) {
    if($item==null) { $item = osc_item(); }
    if(Session::newInstance()->_getForm('region') != '') {
      $item['s_region'] = Session::newInstance()->_getForm('region');
    }
    parent::generic_input_text('region', isset($item['s_region']) ? $item['s_region'] : null);
    parent::generic_input_hidden('regionId', (isset($item['fk_i_region_id']) && $item['fk_i_region_id']!=null)?$item['fk_i_region_id']:'');
    return true;
  }

  /**
  * @param null $item
  *
  * @return bool
  */
  public static function city_text($item = null) {
    if($item==null) { $item = osc_item(); }
    if(Session::newInstance()->_getForm('city') != '') {
      $item['s_city'] = Session::newInstance()->_getForm('city');
    }
    
    parent::generic_input_text('city', isset($item['s_city']) ? $item['s_city'] : null);
    parent::generic_input_hidden('cityId', (isset($item['fk_i_city_id']) && $item['fk_i_city_id']!=null)?$item['fk_i_city_id']:'');
    return true;
  }

  /**
  * @param null $item
  *
  * @return bool
  */
  public static function city_area_text($item = null) {
    if($item==null) { $item = osc_item(); }
    if(Session::newInstance()->_getForm('cityArea') != '') {
      $item['s_city_area'] = Session::newInstance()->_getForm('cityArea');
    }
    
    parent::generic_input_text('cityArea', isset($item['s_city_area']) ? $item['s_city_area'] : null);
    parent::generic_input_hidden('cityAreaId', (isset($item['fk_i_city_area_id']) && $item['fk_i_city_area_id']!=null)?$item['fk_i_city_area_id']:'');
    return true;
  }

  /**
  * @param null $item
  *
  * @return bool
  */
  public static function address_text($item = null) {
    if($item==null) { $item = osc_item(); }
    if(Session::newInstance()->_getForm('address') != '') {
      $item['s_address'] = Session::newInstance()->_getForm('address');
    }
    
    parent::generic_input_text('address', isset($item['s_address']) ? $item['s_address'] : null);
    return true;
  }
  
  /**
  * @param null $item
  *
  * @return bool
  */
  public static function latitude_text($item = null) {
    if($item==null) { $item = osc_item(); }
    if(Session::newInstance()->_getForm('latitude') != '') {
      $item['d_coord_lat'] = Session::newInstance()->_getForm('latitude');
    }
    
    parent::generic_input_text('latitude', isset($item['d_coord_lat']) ? $item['d_coord_lat'] : null);
    return true;
  }
  
  /**
  * @param null $item
  *
  * @return bool
  */
  public static function longitude_text($item = null) {
    if($item==null) { $item = osc_item(); }
    if(Session::newInstance()->_getForm('longitude') != '') {
      $item['d_coord_long'] = Session::newInstance()->_getForm('longitude');
    }
    
    parent::generic_input_text('longitude', isset($item['d_coord_long']) ? $item['d_coord_long'] : null);
    return true;
  }

  /**
  * @param null $item
  *
  * @return bool
  */
  public static function zip_text($item = null) {
    if($item==null) { $item = osc_item(); }
    if(Session::newInstance()->_getForm('zip') != '') {
      $item['s_zip'] = Session::newInstance()->_getForm('zip');
    }
    
    parent::generic_input_text('zip', isset($item['s_zip']) ? $item['s_zip'] : null);
    return true;
  }

  /**
  * @param null $item
  *
  * @return bool
  */
  public static function contact_name_text($item = null) {
    if($item==null) { $item = osc_item(); }
    if(Session::newInstance()->_getForm('contactName') != '') {
      $item['s_contact_name'] = Session::newInstance()->_getForm('contactName');
    }
    
    parent::generic_input_text('contactName', isset($item['s_contact_name']) ? $item['s_contact_name'] : null);
    return true;
  }

  public static function contact_phone_text($item = null) {
    if($item==null) { $item = osc_item(); }
    if(Session::newInstance()->_getForm('contactPhone') != '') {
      $item['s_contact_phone'] = Session::newInstance()->_getForm('contactPhone');
    }
    
    parent::generic_input_text('contactPhone', isset($item['s_contact_phone']) ? $item['s_contact_phone'] : null);
    return true;
  }

  public static function contact_other_text($item = null) {
    if($item==null) { $item = osc_item(); }
    if(Session::newInstance()->_getForm('contactOther') != '') {
      $item['s_contact_other'] = Session::newInstance()->_getForm('contactOther');
    }
    
    parent::generic_input_text('contactOther', isset($item['s_contact_other']) ? $item['s_contact_other'] : null);
    return true;
  }

  /**
  * @param null $item
  *
  * @return bool
  */
  public static function contact_email_text($item = null) {
    if($item==null) { $item = osc_item(); }
    if(Session::newInstance()->_getForm('contactEmail') != '') {
      $item['s_contact_email'] = Session::newInstance()->_getForm('contactEmail');
    }
    
    parent::generic_input_text('contactEmail', isset($item['s_contact_email']) ? $item['s_contact_email'] : null);
    return true;
  }
  // NOTHING TO DO

  /**
  * @return bool
  */
  public static function user_data_hidden() {
    if(isset($_SESSION['userId']) && $_SESSION['userId']!=null) {
      $user = osc_get_user_row($_SESSION['userId']);
      parent::generic_input_hidden('contactName', $user['s_name']);
      parent::generic_input_hidden('contactEmail', $user['s_email']);
      return true;
    } else {
      return false;
    }
  }

  /**
  * @param null $item
  *
  * @return bool
  */
  public static function show_email_checkbox($item = null) {
    if($item==null) { $item = osc_item(); }

    if(Session::newInstance()->_getForm('b_show_email') == 1) {
      $item['b_show_email'] = true;
    }
    
    parent::generic_input_checkbox('showEmail', '1', isset($item['b_show_email']) ? $item['b_show_email'] : false);
    return true;
  }


  public static function show_phone_checkbox($item = null) {
    if($item==null) { $item = osc_item(); }
    
    if(Session::newInstance()->_getForm('showPhone') == 1) {
      $item['b_show_phone'] = true;
    }
    
    parent::generic_input_checkbox('showPhone', '1', isset($item['b_show_phone']) ? $item['b_show_phone'] : true);
    return true;
  }




  /**
  * @param string $path
  */
  public static function phone_currency_autoload() {
    $countries = array();
    $list = (is_array(osc_get_countries()) ? osc_get_countries() : array());
    
    if(is_array($list) && count($list) > 0) {
      foreach($list as $c) {
        if(isset($c['pk_c_code'])) {
          $countries[$c['pk_c_code']] = array(
            's_phone_code' => '+' . trim(str_replace('+', '', (string)$c['s_phone_code'])),
            's_currency' => strtoupper(trim((string)$c['s_currency']))
          );
        }
      }
    }
  ?>
  
  <script type="text/javascript">
  var cList = JSON.parse('<?php echo json_encode($countries); ?>');
  $(document).ready(function(){
    $('body').on('change', '#countryId, input[name="countryId"], select[name="countryId"]', function(){
      var code = ($(this).val()).toUpperCase().trim();

      if(code != '') {
        if(cList.hasOwnProperty(code) && cList[code].hasOwnProperty('s_phone_code')) {
          var phoneCode = cList[code].s_phone_code;
          $('input[name="sPhone"], input[name="contactPhone"]').each(function() {
            if(($(this).val()).trim().length <= 6) {  // change only if phone is not completely filled yet
              $(this).val(phoneCode);
            }
          })
        }

        if(cList.hasOwnProperty(code) && cList[code].hasOwnProperty('s_currency')) {
          var currencyCode = cList[code].s_currency;
          if(currencyCode != '' && currencyCode !== undefined) {
            if(($('select[name="currency"] option[value="' + currencyCode + '"]')).length > 0) {
              $('select[name="currency"]').val(currencyCode);
            }
          }
        }
      }
    });
  });
  </script>
  <?php }


  /**
  * @param string $path
  */
  public static function location_javascript_new($path = 'front') {
  ?>
    <script type="text/javascript">
    $(document).ready(function(){
      $('#countryName').attr("autocomplete", "off");
      $('#region').attr("autocomplete", "off");
      $('#city').attr("autocomplete", "off");

      $('body').on('change', '#countryId', function(){
        $('#regionId').val('');
        $('#region').val('');
        $('#cityId').val('');
        $('#city').val('');
      });

      $('#countryName').on('keyup.autocomplete', function(){
        $('#countryId').val('');
        $(this).autocomplete({
          source: "<?php echo osc_base_url(true); ?>?page=ajax&action=location_countries",
          minLength: 0,
          select: function(event, ui) {
            $('#countryId').val(ui.item.id);
            $('#regionId').val('');
            $('#region').val('');
            $('#cityId').val('');
            $('#city').val('');
          }
        });
      });

      $('#region').on('keyup.autocomplete', function(){
        $('#regionId').val('');
        if($('#countryId').val()!='' && $('#countryId').val()!=undefined) {
          var country = $('#countryId').val();
        } else {
          var country = $('#country').val();
        }
        
        $(this).autocomplete({
          source: "<?php echo osc_base_url(true); ?>?page=ajax&action=location_regions&country="+country,
          minLength: 2,
          select: function(event, ui) {
            $('#cityId').val('');
            $('#city').val('');
            $('#regionId').val(ui.item.id);
          }
        });
      });

      $('#city').on('keyup.autocomplete', function(){
        $('#cityId').val('');
        if($('#regionId').val()!='' && $('#regionId').val()!=undefined) {
          var region = $('#regionId').val();
        } else {
          var region = $('#region').val();
        }
        
        $(this).autocomplete({
          source: "<?php echo osc_base_url(true); ?>?page=ajax&action=location_cities&region="+region,
          minLength: 2,
          select: function(event, ui) {
            $('#cityId').val(ui.item.id);
          }
        });
      });

      $('.ui-autocomplete').css('zIndex', 10000);


      // VALIDATE FORM
      // Validate description without HTML.
      $.validator.addMethod(
        "minstriptags",
        function(value, element) {
          altered_input = strip_tags(value);
          if (altered_input.length < 3) {
            return false;
          } else {
            return true;
          }
        },
        '<?php echo osc_esc_js(__('Description needs to be longer')); ?>.'
      );
      
      <?php osc_run_hook('item_form_new_validation_top'); ?>

      // Code for form validation
      $("form[name=item]").validate({
        rules: {
          "title[<?php echo osc_current_user_locale(); ?>]": {
              required: true,
              minlength: 5
          },
          "description[<?php echo osc_current_user_locale(); ?>]": {
              required: true,
              minlength: 10
          },
          catId: {
            required: true,
            digits: true
          },
          <?php if(osc_price_enabled_at_items()) { ?>
          price: {
            maxlength: 25
          },
          currency: "required",
          <?php } ?>
          <?php if(osc_images_enabled_at_items()) { ?>
          "photos[]": {
            accept: "<?php echo osc_esc_js(osc_allowed_extension()); ?>"
          },
          <?php } ?>
          <?php if($path === 'front') { ?>
          contactName: {
            minlength: 3,
            maxlength: 35
          },
          contactEmail: {
            required: true,
            email: true
          },
          <?php } ?>
          address: {
            minlength: 3,
            maxlength: 100
          }
          <?php osc_run_hook('item_form_new_validation_rules'); ?>
        },
        
        messages: {
          "title[<?php echo osc_current_user_locale(); ?>]": {
              required: '<?php echo osc_esc_js(__('Title: this field is required')); ?>',
              minlength: '<?php echo osc_esc_js(__('Title: enter at least 5 characters')); ?>'
          },
          "description[<?php echo osc_current_user_locale(); ?>]": {
              required: '<?php echo osc_esc_js(__('Description: this field is required')); ?>',
              minlength: '<?php echo osc_esc_js(__('Description: enter at least 10 characters')); ?>'
          },
          catId: "<?php echo osc_esc_js(__('Choose one category')); ?>.",
          <?php if(osc_price_enabled_at_items()) { ?>
          price: {
            maxlength: "<?php echo osc_esc_js(__('Price: no more than 25 characters')); ?>."
          },
          currency: "<?php echo osc_esc_js(__('Currency: make your selection')); ?>.",
          <?php } ?>
          <?php if(osc_images_enabled_at_items()) { ?>
          "photos[]": {
            accept: "<?php echo osc_esc_js(sprintf(__('Photo: must be %s'), osc_allowed_extension())); ?>."
          },
          <?php } ?>
          <?php if($path === 'front') { ?>
          contactName: {
            minlength: "<?php echo osc_esc_js(__('Name: enter at least 3 characters')); ?>.",
            maxlength: "<?php echo osc_esc_js(__('Name: no more than 35 characters')); ?>."
          },
          contactEmail: {
            required: "<?php echo osc_esc_js(__('Email: this field is required')); ?>.",
            email: "<?php echo osc_esc_js(__('Invalid email address')); ?>."
          },
          <?php } ?>
          address: {
            minlength: "<?php echo osc_esc_js(__('Address: enter at least 3 characters')); ?>.",
            maxlength: "<?php echo osc_esc_js(__('Address: no more than 100 characters')); ?>."
          }
          <?php osc_run_hook('item_form_new_validation_messages'); ?>
        },
        
        <?php osc_run_hook('item_form_new_validation_attributes'); ?>
        errorLabelContainer: "#error_list",
        ignore: ":disabled, :hidden, .ignore",
        ignoreTitle: false,
        wrapper: "li",
        invalidHandler: function(form, validator) {
          $('html,body').animate({ scrollTop: $('h1').offset().top }, { duration: 250, easing: 'swing'});
        },
        
        submitHandler: function(form){
          form.find('button[type=submit], input[type=submit], .submit-item-form').addClass('disabled').attr('disabled', 'disabled');
          setTimeout("form.find('button[type=submit], input[type=submit], .submit-item-form').removeClass('disabled').removeAttr('disabled')", 5000);
          form.submit();
        }
      });
    });

    // Strip HTML tags to count number of visible characters.
    function strip_tags(html) {
      if (arguments.length < 3) {
        html=html.replace(/<\/?(?!\!)[^>]*>/gi, '');
      } else {
        var allowed = arguments[1];
        var specified = eval("["+arguments[2]+"]");
        if (allowed){
          var regex='</?(?!(' + specified.join('|') + '))\b[^>]*>';
          html=html.replace(new RegExp(regex, 'gi'), '');
        } else{
          var regex='</?(' + specified.join('|') + ')\b[^>]*>';
          html=html.replace(new RegExp(regex, 'gi'), '');
        }
      }
      
      return html;
    }

    function delete_image(id, item_id,name, secret) {
      //alert(id + " - "+ item_id + " - "+name+" - "+secret);
      var result = confirm('<?php echo osc_esc_js(__("This action can't be undone. Are you sure you want to continue?")); ?>');
      if(result) {
        $.ajax({
          type: "POST",
          url: '<?php echo osc_base_url(true); ?>?page=ajax&action=delete_image&id='+id+'&item='+item_id+'&code='+name+'&secret='+secret,
          dataType: 'json',
          success: function(data){
            var class_type = "error";
            if(data.success) {
              $("div[name="+name+"]").remove();
              class_type = "ok";
            }
            var flash = $("#flash_js");
            var message = $('<div>').addClass('pubMessages').addClass(class_type).attr('id', 'flashmessage').html(data.msg);
            flash.html(message);
            $("#flashmessage").slideDown('slow').delay(3000).slideUp('slow');
          }
        });
      }
    }
    </script>
  <?php
  }


  /**
  * @param string $path
  */
  public static function location_javascript($path = 'front') {
  ?>
    <script type="text/javascript">
    $(document).ready(function(){
      $('body').on('change', '#countryId', function(){
        var pk_c_code = $(this).val();
        <?php if($path === 'admin') { ?>
          var url = '<?php echo osc_admin_base_url(true) . '?page=ajax&action=regions&countryId='; ?>' + pk_c_code;
        <?php } else { ?>
          var url = '<?php echo osc_base_url(true) . '?page=ajax&action=regions&countryId='; ?>' + pk_c_code;
        <?php } ?>
        var result = '';

        if(pk_c_code != '') {
          $("#regionId").attr('disabled',false);
          $("#cityId").attr('disabled',true);

          $.ajax({
            type: "POST",
            url: url,
            dataType: 'json',
            success: function(data){
              var length = data.length;
              var locationsNative = "<?php echo osc_get_current_user_locations_native(); ?>";

              if(length > 0) {
                result += '<option selected value=""><?php echo osc_esc_js(__('Select a region...')); ?></option>';
                for(key in data) {
                  var vname = data[key].s_name;
                  if(data[key].hasOwnProperty('s_name_native')) { 
                    if(data[key].s_name_native != '' && data[key].s_name_native != 'null' && data[key].s_name_native != null && locationsNative == "1") {
                      vname = data[key].s_name_native;
                    }
                  }
                  result += '<option value="' + data[key].pk_i_id + '">' + vname + '</option>';
                }

                $("#region").before('<select name="regionId" id="regionId" ></select>');
                $("#region").remove();
                $("#city").before('<select name="cityId" id="cityId" ></select>');
                $("#city").remove();
                $("#regionId").val("");
              } else {
                $("#regionId").before('<input type="text" name="region" id="region" />');
                $("#regionId").remove();
                $("#cityId").before('<input type="text" name="city" id="city" />');
                $("#cityId").remove();
              }

              $("#regionId").html(result);
              $("#cityId").html('<option selected value=""><?php echo osc_esc_js(__('Select a city...')); ?></option>');
              $("#regionId").trigger('change');
              $("#cityId").trigger('change');
            }
           });

         } else {

           // add empty select
           $("#region").before('<select name="regionId" id="regionId" ><option value=""><?php echo osc_esc_js(__('Select a region...')); ?></option></select>');
           $("#region").remove();

           $("#city").before('<select name="cityId" id="cityId" ><option value=""><?php echo osc_esc_js(__('Select a city...')); ?></option></select>');
           $("#city").remove();

           if($("#regionId").length > 0){
             $("#regionId").html('<option value=""><?php echo osc_esc_js(__('Select a region...')); ?></option>');
           } else {
             $("#region").before('<select name="regionId" id="regionId" ><option value=""><?php echo osc_esc_js(__('Select a region...')); ?></option></select>');
             $("#region").remove();
           }
           if($("#cityId").length > 0){
             $("#cityId").html('<option value=""><?php echo osc_esc_js(__('Select a city...')); ?></option>');
           } else {
             $("#city").before('<select name="cityId" id="cityId" ><option value=""><?php echo osc_esc_js(__('Select a city...')); ?></option></select>');
             $("#city").remove();
           }
           $("#regionId").attr('disabled',true);
           $("#cityId").attr('disabled',true);
         }
      });

      $('body').on('change', '#regionId', function(){
        var pk_c_code = $(this).val();
        <?php if($path === 'admin') { ?>
          var url = '<?php echo osc_admin_base_url(true) . '?page=ajax&action=cities&regionId='; ?>' + pk_c_code;
        <?php } else { ?>
          var url = '<?php echo osc_base_url(true) . '?page=ajax&action=cities&regionId='; ?>' + pk_c_code;
        <?php } ?>

        var result = '';
        if(pk_c_code != '') {
          $("#cityId").attr('disabled',false);
          $.ajax({
            type: "POST",
            url: url,
            dataType: 'json',
            success: function(data){
              var length = data.length;
              var locationsNative = "<?php echo osc_get_current_user_locations_native(); ?>";

              if(length > 0) {
                result += '<option selected value=""><?php echo osc_esc_js(__('Select a city...')); ?></option>';
                for(key in data) {
                  var vname = data[key].s_name;
                  if(data[key].hasOwnProperty('s_name_native')) {
                    if(data[key].s_name_native != '' && data[key].s_name_native != 'null' && data[key].s_name_native != null && locationsNative == "1") {
                      vname = data[key].s_name_native;
                    }
                  }

                  result += '<option value="' + data[key].pk_i_id + '">' + vname + '</option>';
                }

                $("#city").before('<select name="cityId" id="cityId" ></select>');
                $("#city").remove();
              } else {
                result += '<option value=""><?php echo osc_esc_js(__('No results')); ?></option>';
                $("#cityId").before('<input type="text" name="city" id="city" />');
                $("#cityId").remove();
              }
              $("#cityId").html(result);
              $("#cityId").trigger('change');
            }
           });
         } else {
          $("#cityId").attr('disabled',true);
         }
      });

      if($("#regionId").attr('value') == "") {
        $("#cityId").attr('disabled',true);
      }

      if($("#countryId").length != 0) {
        if($("#countryId").prop('type').match(/select-one/)) {
          if($("#countryId").attr('value') == "") {
            $("#regionId").attr('disabled',true);
          }
        }
      }

      // VALIDATE FORM
      // Validate description without HTML.
      $.validator.addMethod(
        "minstriptags",
        function(value, element) {
          altered_input = strip_tags(value);
          if (altered_input.length < 3) {
            return false;
          } else {
            return true;
          }
        },
        "<?php echo osc_esc_js(__('Description needs to be longer')); ?>."
      );
      
      <?php osc_run_hook('item_form_validation_top'); ?>

      // Code for form validation
      $("form[name=item]").validate({
        rules: {
          "title[<?php echo osc_current_user_locale(); ?>]": {
              required: true,
              minlength: 5
          },
          "description[<?php echo osc_current_user_locale(); ?>]": {
              required: true,
              minlength: 10
          },
          catId: {
            required: true,
            digits: true
          },
          <?php if(osc_price_enabled_at_items()) { ?>
          price: {
            maxlength: 25
          },
          currency: "required",
          <?php } ?>
          <?php if(osc_images_enabled_at_items()) { ?>
          "photos[]": {
            accept: "<?php echo osc_allowed_extension(); ?>"
          },
          <?php } ?>
          <?php if($path === 'front') { ?>
          contactName: {
            minlength: 3,
            maxlength: 35
          },
          contactEmail: {
            required: true,
            email: true
          },
          <?php } ?>
          regionId: {
            required: true,
            digits: true
          },
          cityId: {
            required: true,
            digits: true
          },
          cityArea: {
            minlength: 3,
            maxlength: 50
          },
          address: {
            minlength: 3,
            maxlength: 100
          }
          <?php osc_run_hook('item_form_validation_rules'); ?>
        },
        
        messages: {
          "title[<?php echo osc_current_user_locale(); ?>]": {
              required: '<?php echo osc_esc_js(__('Title: this field is required')); ?>',
              minlength: '<?php echo osc_esc_js(__('Title: enter at least 5 characters')); ?>'
          },
          "description[<?php echo osc_current_user_locale(); ?>]": {
              required: '<?php echo osc_esc_js(__('Description: this field is required')); ?>',
              minlength: '<?php echo osc_esc_js(__('Description: enter at least 10 characters')); ?>'
          },
          catId: "<?php echo osc_esc_js(__('Choose one category')); ?>.",
          <?php if(osc_price_enabled_at_items()) { ?>
          price: {
            maxlength: "<?php echo osc_esc_js(__('Price: no more than 25 characters')); ?>."
          },
          currency: "<?php echo osc_esc_js(__('Currency: make your selection')); ?>.",
          <?php } ?>
          <?php if(osc_images_enabled_at_items()) { ?>
          "photos[]": {
            accept: "<?php echo osc_esc_js(sprintf(__('Photo: must be %s'), osc_allowed_extension())); ?>."
          },
          <?php } ?>
          <?php if($path === 'front') { ?>
          contactName: {
            minlength: "<?php echo osc_esc_js(__('Name: enter at least 3 characters')); ?>.",
            maxlength: "<?php echo osc_esc_js(__('Name: no more than 35 characters')); ?>."
          },
          contactEmail: {
            required: "<?php echo osc_esc_js(__('Email: this field is required')); ?>.",
            email: "<?php echo osc_esc_js(__('Invalid email address')); ?>."
          },
          <?php } ?>
          regionId: "<?php echo osc_esc_js(__('Select a region')); ?>.",
          cityId: "<?php echo osc_esc_js(__('Select a city')); ?>.",
          cityArea: {
            minlength: "<?php echo osc_esc_js(__('City area: enter at least 3 characters')); ?>.",
            maxlength: "<?php echo osc_esc_js(__('City area: no more than 50 characters')); ?>."
          },
          address: {
            minlength: "<?php echo osc_esc_js(__('Address: enter at least 3 characters')); ?>.",
            maxlength: "<?php echo osc_esc_js(__('Address: no more than 100 characters')); ?>."
          }
          <?php osc_run_hook('item_form_validation_messages'); ?>
        },
        
        <?php osc_run_hook('item_form_validation_attributes'); ?>
        errorLabelContainer: "#error_list",
        ignore: ":disabled, :hidden, .ignore",
        ignoreTitle: false,
        wrapper: "li",
        invalidHandler: function(form, validator) {
          $('html,body').animate({ scrollTop: $('h1').offset().top }, { duration: 250, easing: 'swing'});
        },
        
        submitHandler: function(form){
          $('button[type=submit], input[type=submit]').attr('disabled', 'disabled');
          setTimeout("$('button[type=submit], input[type=submit]').removeAttr('disabled')", 5000);
          form.submit();
        }
      });
    });

    // Strip HTML tags to count number of visible characters.
    function strip_tags(html) {
      if (arguments.length < 3) {
        html=html.replace(/<\/?(?!\!)[^>]*>/gi, '');
      } else {
        var allowed = arguments[1];
        var specified = eval("["+arguments[2]+"]");
        if (allowed){
          var regex='</?(?!(' + specified.join('|') + '))\b[^>]*>';
          html=html.replace(new RegExp(regex, 'gi'), '');
        } else{
          var regex='</?(' + specified.join('|') + ')\b[^>]*>';
          html=html.replace(new RegExp(regex, 'gi'), '');
        }
      }
      
      return html;
    }

    function delete_image(id, item_id,name, secret) {
      //alert(id + " - "+ item_id + " - "+name+" - "+secret);
      var result = confirm('<?php echo osc_esc_js(__("This action can't be undone. Are you sure you want to continue?")); ?>');
      if(result) {
        $.ajax({
          type: "POST",
          url: '<?php echo osc_base_url(true); ?>?page=ajax&action=delete_image&id='+id+'&item='+item_id+'&code='+name+'&secret='+secret,
          dataType: 'json',
          success: function(data){
            var class_type = "error";
            if(data.success) {
              $("div[name="+name+"]").remove();
              class_type = "ok";
            }
            var flash = $("#flash_js");
            var message = $('<div>').addClass('pubMessages').addClass(class_type).attr('id', 'flashmessage').html(data.msg);
            flash.html(message);
            $("#flashmessage").slideDown('slow').delay(3000).slideUp('slow');
          }
        });
      }
    }
    </script>
    <?php
  }


  /**
  * @param boolean $is_edit
  * @param integer $item_id
  * @param string $variant_hook
  * @param string $variant_param
  */
  public static function plugin_post_edit_item($is_edit = false, $item_id = NULL, $variant_hook = '', $variant_param = '') {
    // Generate categories just once
    $categories = array();
    if($variant_hook == '' && $variant_param == '') {
      $categories = Category::newInstance()->listAll(false);
    }
    
    $hook = ($is_edit ? 'item_edit' : 'item_form');
    $item_param = ($is_edit ? '&itemId=' . $item_id : '');

    $variant_hook = trim((string)$variant_hook);
    $variant_param = trim((string)$variant_param);
    
    $variant_hook_mod = ($variant_hook != '' ? '_' . $variant_hook : '');
    $variant_param_mod = ($variant_param != '' ? '&variant=' . $variant_param : '');
  ?>
  <script type="text/javascript">
    <?php 
      $cat_data = array();

      if(is_array($categories) && count($categories) > 0) { 
        foreach($categories as $c) {
          $cat_data[$c['pk_i_id']] = ($c['b_price_enabled'] == 1 ? 1 : 0);
        }
        
        echo 'var catPriceEnabled = ' . json_encode($cat_data) . ';' . PHP_EOL;
      }
    ?>

    // Get plugin data on category change
    $('body').on('change', '#catId, [name="catId"], .cat-id-input', function(e){
      var catId = $(this).val();
      var url = '<?php echo (OC_ADMIN ? osc_admin_base_url(true) : osc_base_url(true)); ?>';
      var urlData = 'page=ajax&action=runhook&hook=<?php echo $hook . $variant_hook_mod . $item_param . $variant_param_mod; ?>&catId=' + catId;
      var variantHook = '<?php echo osc_esc_js($variant_hook); ?>';
      var variantParam = '<?php echo osc_esc_js($variant_param); ?>';
      var result = '';

      if(catId > 0) {
        if(catPriceEnabled[catId] == 1) {
          $('#price').closest('div').show();
          $('.block-with-price').show();
          $('#price').trigger('show-price');
          
        } else {
          $('#price').closest('div').hide();
          $('.block-with-price').hide();
          $('#price').val('');
          $('#price').trigger('hide-price');
        }

        $.ajax({
          type: "POST",
          url: url,
          data: urlData,
          dataType: 'html',
          success: function(data){
            if(variantHook == '' && variantParam == '') {
              $('#plugin-hook, .plugin-hook-data:not([data-variant-hook]):not([data-variant-param])').html(data);
              
            } else if(variantHook != '') {
              $('#plugin-hook-' + variantHook + ', .plugin-hook-data[data-variant-hook="' + variantHook + '"], .plugin-hook-data.variant-hook-' + variantHook).html(data);

            } else if(variantParam != '') {
              $('#plugin-hook-' + variantParam + ', .plugin-hook-data[data-variant-param="' + variantParam + '"], .plugin-hook-data.variant-param-' + variantParam).html(data);

            }
          }
        });
      }
    });
    
    
    // Get plugin data on page load
    $(document).ready(function(){
      var catId = $('#catId, [name="catId"], .cat-id-input').val();
      var url = '<?php echo (OC_ADMIN ? osc_admin_base_url(true) : osc_base_url(true)); ?>';
      var urlData = 'page=ajax&action=runhook&hook=<?php echo $hook . $variant_hook_mod . $item_param . $variant_param_mod; ?>&catId=' + catId;
      var variantHook = '<?php echo osc_esc_js($variant_hook); ?>';
      var variantParam = '<?php echo osc_esc_js($variant_param); ?>';
      var result = '';

      if(catId > 0) {
        if(catPriceEnabled[catId] == 1) {
          $('#price').closest('div').show();
          $('.block-with-price').show();
        } else {
          $('#price').closest('div').hide();
          $('.block-with-price').hide();
          $('#price').val('') ;
        }

        $.ajax({
          type: "POST",
          url: url,
          data: urlData,
          dataType: 'html',
          success: function(data){
            if(variantHook == '' && variantParam == '') {
              $('#plugin-hook, .plugin-hook-data:not([data-variant-hook]):not([data-variant-param])').html(data);
              
            } else if(variantHook != '') {
              $('#plugin-hook-' + variantHook + ', .plugin-hook-data[data-variant-hook="' + variantHook + '"], .plugin-hook-data.variant-hook-' + variantHook).html(data);

            } else if(variantParam != '') {
              $('#plugin-hook-' + variantParam + ', .plugin-hook-data[data-variant-param="' + variantParam + '"], .plugin-hook-data.variant-param-' + variantParam).html(data);

            }
          }
        });
      }
    });
  </script>

  <?php if($variant_hook == '' && $variant_param == '') { ?>
    <div id="plugin-hook" class="plugin-hook-data"></div>
  <?php } else if ($variant_hook != '') { ?>
    <div id="plugin-hook-<?php echo osc_esc_html($variant_hook); ?>" class="plugin-hook-data" data-variant-hook="<?php echo osc_esc_html($variant_hook); ?>"></div>
  <?php } else if ($variant_param != '') { ?>
    <div id="plugin-hook-<?php echo osc_esc_html($variant_param); ?>" class="plugin-hook-data" data-variant-param="<?php echo osc_esc_html($variant_param); ?>"></div>
  <?php } ?>
  
  <?php
  }


  // PUBLISH PAGE
  public static function plugin_post_item($variant_hook = '', $variant_param = '') {
    self::plugin_post_edit_item(false, null, $variant_hook, $variant_param);
  }
  
  // EDIT PAGE
  public static function plugin_edit_item($variant_hook = '', $variant_param = '') {
    self::plugin_post_edit_item(true, osc_item_id(), $variant_hook, $variant_param);
  }



  /************************************************ IMAGE UPLOADER SECTION ************************************************/
  /**
  * @param array $resources
  */
  public static function photos($resources = null) {
    if($resources==null) { 
      $resources = osc_get_item_resources(); 
    }

    if($resources!=null && is_array($resources) && count($resources)>0) { 
    ?>
      <div class="photos_div">
        <?php foreach($resources as $_r) { ?>
          <div id="<?php echo $_r['pk_i_id'];?>" fkid="<?php echo $_r['fk_i_item_id'];?>" name="<?php echo $_r['s_name'];?>" class="photo-card">
            <img src="<?php echo osc_apply_filter('resource_thumbnail_url', osc_apply_filter('resource_path', osc_base_url() . $_r['s_path']) . $_r['pk_i_id'] . '_thumbnail.' . $_r['s_extension']); ?>" />
            <a href="javascript:delete_image(<?php echo $_r['pk_i_id'] . ', ' . $_r['fk_i_item_id'] . ", '" . $_r['s_name'] . "', '" . osc_item_secret() . "'";?>);" class="delete" title="<?php echo osc_esc_html(__('Delete')); ?>"><span><?php _e('Delete'); ?></span></a>
          </div>
        <?php } ?>
      </div>
    <?php 
    }
  }
  
  
  /**
  * @param null $resources
  * @throws \Exception
  */
  public static function ajax_photos($resources = null) {
    if (osc_image_upload_library() == 'UPPY') {
      $is_debug = false;
      
      if(defined('OSC_DEBUG') && OSC_DEBUG === true) {
        $is_debug = true;
      }
      
      if($resources==null) { 
        $resources = osc_get_item_resources(); 
      }
      
      $aImages = array();
      
      if(Session::newInstance()->_getForm('photos') != '') {
        $aImages = Session::newInstance()->_getForm('photos');
        
        if (isset($aImages['name'])) {
          $aImages = $aImages['name'];
        } else {
          $aImages = array();
        }
        
        Session::newInstance()->_drop('photos');
        Session::newInstance()->_dropKeepForm('photos');
      }

      $aExt = explode(',', osc_allowed_extension());
      
      foreach($aExt as $key => $val) {
        $aExt[$key] = "'image/" . $val . "'";
      }

      $allowed_extensions = implode(',', $aExt);
      $images_size_limit = (int)(osc_max_size_kb() * 1024);
      $images_limit = (int)osc_max_images_per_item();
      
      $note_extensions = '.' . implode(', .', explode(',', osc_allowed_extension()));
      $note_message = sprintf(__('Allowed image extensions: %s. Max. image size: %skb. Limit: %s image(s).'), $note_extensions, osc_max_size_kb(), osc_max_images_per_item());
    ?>
      <style>.qq-upload-list li.qq-placeholder:before {content:"<?php echo osc_esc_html(__('Image will be moved to this position')); ?>";}</style>
      
      <div id="restricted-fine-uploader" class="uppy-uploader">
        <div class="qq-uploader">
          <div class="qq-upload-drop-area">
            <span><?php _e('Drop files here to upload'); ?></span>
          </div>

          <div id="uppy-upload-button" class="qq-upload-button">
            <div><?php _e('Upload images'); ?></div>
            <!--<input multiple="multiple" type="file" name="qqfile" style="display:none">-->
          </div>

          <input type="hidden" name="ajax_order_list" value="">

          <ul id="uppy-gallery" class="qq-upload-list">
            <?php foreach($resources as $_r) {
              $img = $_r['pk_i_id'].'.'.$_r['s_extension']; ?>
              <li class="qq-upload-success qq-img-stored" id="res-<?php echo $_r['pk_i_id']; ?>">
                <span class="qq-upload-file"><?php echo $img; ?></span>
                <a class="qq-upload-delete" href="#" photoid="<?php echo $_r['pk_i_id']; ?>" itemid="<?php echo $_r['fk_i_item_id']; ?>" photoname="<?php echo $_r['s_name']; ?>" photosecret="<?php echo Params::getParam('secret'); ?>" style="display: inline; cursor:pointer;"><?php _e('Delete'); ?></a>
                <div class="ajax_preview_img">
                  <?php if(osc_image_upload_reorder()) { ?>
                    <span class="qq-upload-move" title="<?php echo osc_esc_html(__('Reorder')); ?>"><i class="fa fa-arrows-alt"></i></span>
                  <?php } ?>
                  
                  <img src="<?php echo osc_apply_filter('resource_thumbnail_url', osc_apply_filter('resource_path', osc_base_url().$_r['s_path']).$_r['pk_i_id'].'_thumbnail.'.$_r['s_extension']); ?>" alt="<?php echo osc_esc_html($img); ?>">
                </div>
              </li>
            <?php } ?>
            
            <?php foreach($aImages as $img){ ?>
              <?php $img = trim(osc_esc_html($img)); ?>

              <?php if($img != '') { ?>
                <li class="qq-upload-success qq-from-session" id="<?php echo $img; ?>">
                  <span class="qq-upload-file"><?php echo $img; ?></span>
                  <a class="qq-upload-delete" href="#" ajaxfile="<?php echo $img; ?>" style="display: inline; cursor:pointer;"><?php _e('Delete'); ?></a>
                  <div class="ajax_preview_img">
                    <?php if(osc_image_upload_reorder()) { ?>
                      <span class="qq-upload-move" title="<?php echo osc_esc_html(__('Reorder')); ?>"><i class="fa fa-arrows-alt"></i></span>
                    <?php } ?>
                    
                    <img src="<?php echo osc_base_url() . OC_CONTENT_FOLDER; ?>/uploads/temp/<?php echo $img; ?>" alt="<?php echo $img; ?>">
                  </div>
                  <input type="hidden" name="ajax_photos[]" value="new-<?php echo $img; ?>">
                </li>
              <?php } ?>
            <?php } ?>
          </ul>
        </div>
      </div>


      <?php osc_image_uploader_js_locale('UPPY'); ?>

      <script type="text/javascript">
        var maxItems = parseInt(<?php echo $images_limit; ?>);
        var maxItemsUppy = parseInt(<?php echo $images_limit; ?>) - parseInt($(".qq-upload-list input[name='ajax_photos[]']").length);
        var uppyDisabled = (maxItemsUppy <= 0 ? true : false);
        maxItemsUppy = Math.max(maxItemsUppy, 1);
        
        const uppy = new Uppy.Uppy({ 
          locale: osLocale,
          autoProceed: false,
          restrictions: {
            maxFileSize: <?php echo $images_size_limit; ?>,   
            maxNumberOfFiles: maxItemsUppy,
            allowedFileTypes: [<?php echo $allowed_extensions; ?>]
          },

          onBeforeFileAdded: (currentFile, files) => {
            // runs check each time before file is added to uploader (not uploaded!)
            var alreadyAdded = parseInt(Object.keys(files).length);

            if(maxItemsUppy - alreadyAdded > 0) { 
              return true;
            } else {
              <?php if($is_debug) { ?>console.log('[onBeforeFileAdded] Image limit reached: ' + maxItems);<?php } ?>
              uppy.info('<?php echo osc_esc_js(__('Some images were not added. Image limit reached:')); ?>' + ' ' + maxItems, 'error', 5000);
              return false;
            }
          },
          
          onBeforeUpload: (files) => {
            // runs check each time before file is uploaded to osclass temp folder
            var alreadyUploaded = parseInt(Object.keys(files).length);

            if(maxItemsUppy - (alreadyUploaded-1) > 0) { 
              return true;
            } else {
              <?php if($is_debug) { ?>console.log('[onBeforeUpload] Image limit reached: ' + maxItems);<?php } ?>
              uppy.info('<?php echo osc_esc_js(__('Some images were not uploaded. Image limit reached:')); ?>' + ' ' + maxItems, 'error', 5000);
              return false;
            }
          }
          
        }).use(Uppy.Dashboard, { 
          inline: false,
          trigger: '#uppy-upload-button',
          showProgressDetails: true,
          proudlyDisplayPoweredByUppy: false,
          note: '<?php echo osc_esc_js($note_message); ?>',
          singleFileFullScreen: false,
          closeAfterFinish: true,
          disabled: uppyDisabled
          
        }).use(Uppy.ImageEditor, { 
          target: Uppy.Dashboard
          
        }).use(Uppy.XHRUpload, {
          endpoint: '<?php echo osc_base_url(true) . '?page=ajax&action=ajax_upload'; ?>',
          method: 'post',
          fieldName: 'uppyfile',
          limit: 3,
          responseUrlFieldName: 'uploadUrl',
          getResponseError(responseText, response) {
            console.log('[getResponseError] ' + JSON.parse(responseText).error);
            return new Error(JSON.parse(responseText).error);
          }
          
        }).use(Uppy.Webcam, { 
          target: Uppy.Dashboard,
          modes: ['picture'],
          countdown: 3
          
        <?php if(osc_optimize_uploaded_images() == 1) { ?>
        }).use(Uppy.Compressor, {
          <?php if(osc_uploader_max_image_size() !== false) { ?>
          maxWidth: <?php echo osc_uploader_max_image_size()['w']; ?>,
          maxHeight: <?php echo osc_uploader_max_image_size()['h']; ?>,
          <?php } ?>
          quality: <?php echo osc_apply_filter('image_uploader_compression', 0.85, 'uppy'); ?>
        <?php } ?>
        
        }).on('upload-success', (file, response) => {

          // Create new image box
          var elem = '<li class="qq-upload-success qq-new-img" id="' + response.body.uploadName + '">';
          elem += '<span class="qq-upload-file">' + file.name + '</span>';
          elem += '<a class="qq-upload-delete" href="#" ajaxfile="' + response.body.uploadName + '" style="display: inline; cursor:pointer;"><?php echo osc_esc_js(__('Delete')); ?></a>';
          elem += '<div class="ajax_preview_img">';
          
          <?php if(osc_image_upload_reorder()) { ?>
          elem += '<span class="qq-upload-move" title="<?php echo osc_esc_js(__('Reorder')); ?>"><i class="fa fa-arrows-alt"></i></span>';
          <?php } ?>

          elem += '<img src="' + response.uploadURL + '">';
          elem += '</div>';
          elem += '<input type="hidden" name="ajax_photos[]" value="' + response.body.uploadName + '"/>';
          elem += '</li>';
          
          $('.qq-upload-list').append(elem);
          
          <?php if(osc_image_upload_reorder()) { ?>
          // Trigger sort update, once element is added
          if(typeof $.fn.sortable !== 'undefined') {
            $('ul.qq-upload-list').trigger('sortupdate')
          }
          <?php } ?>

          //setUppyLimit(uppy);

        }).on('dashboard:modal-open', () => {
          var itemsUploaded = setUppyLimit(uppy, 'MODALOPEN');
          maxItemsUppy = parseInt(itemsUploaded);  // - parseInt($(".qq-upload-list input[name='ajax_photos[]']").length);
          <?php if($is_debug) { ?>console.log('[dashboard:modal-open] Remaining images limit: ' + maxItemsUppy);<?php } ?>
          
        }).on('complete', (result) => {
          uppy.cancelAll();
          
          <?php if($is_debug) { ?>
          console.log('Successful files:', result.successful);
          console.log('Failed files:', result.failed);
          <?php } ?>
        });
        
      
        // Remove image
        $('body').on('click', '.qq-upload-delete', function(e) {
          e.preventDefault();
          var parent = $(this).parent()
          var result = confirm('<?php echo osc_esc_js(__("This action can't be undone. Are you sure you want to continue?")); ?>');
          var urlrequest = '';
          
          if($(this).attr('ajaxfile') != undefined) {
            urlrequest = 'ajax_photo='+$(this).attr('ajaxfile');
          } else {
            urlrequest = 'id='+$(this).attr('photoid')+'&item='+$(this).attr('itemid')+'&code='+$(this).attr('photoname')+'&secret='+$(this).attr('photosecret');
          }
        
          if(result) {
            $.ajax({
              type: "POST",
              url: '<?php echo osc_base_url(true); ?>?page=ajax&action=delete_image&'+urlrequest,
              dataType: 'json',
              success: function(data){
                parent.remove();
                
                var itemsUploaded = setUppyLimit(uppy);
                maxItemsUppy = parseInt(itemsUploaded); 
                <?php if($is_debug) { ?>console.log('[delete_image] Recalculate remaining images limit: ' + maxItemsUppy);<?php } ?>
                
                <?php if(osc_image_upload_reorder()) { ?>
                // Trigger sort update, once element is completely removed
                if(typeof $.fn.sortable !== 'undefined') {
                  $('ul.qq-upload-list').trigger('sortupdate')
                }
                <?php } ?>
              }
            });
          }
        });
        
        // Calculate remaining images to upload
        function caclRemainingImages() {
          var checkJson = uploadCanContinue(0);
          
          if(checkJson.success) {
            var totalUploaded = parseInt(checkJson.count) + $(".qq-upload-list input[name='ajax_photos[]']").length;
            return Math.max(parseInt(maxItems - totalUploaded), 0);
          } else {
            var totalUploaded = $(".qq-upload-list input[name='ajax_photos[]']").length;
            return Math.max(parseInt(maxItems - totalUploaded), 0);
          }
        }
        
        // Set limit to uppy
        function setUppyLimit(uppy, type = '') {
          var limit = caclRemainingImages();
          var block = (limit <= 0 ? true : false);
          
          uppy.setOptions({
            restrictions: {maxNumberOfFiles: Math.max(limit, 1)}
          });
          
          if(block) {
            (uppy.getPlugin('Dashboard')).setOptions({disabled: true});
            
            if(type == 'MODALOPEN') {
              uppy.info('<?php echo osc_esc_js(__('You cannot upload more images. Image limit reached!')); ?>', 'warning', 5000);
            }
          } else {
            (uppy.getPlugin('Dashboard')).setOptions({disabled: false});
          }
          
          return limit;
        }
        
        // Check if upload can continue
        function uploadCanContinue(numUpload) {
          var checkUrl = "<?php echo osc_base_url(true) . '?page=ajax&action=ajax_validate&id=' . osc_item_id() . '&secret=' . osc_item_secret(); ?>";
          var response = {};
          
          jQuery.ajax({
            type: 'GET',
            cache: false,
            url: checkUrl,
            timeout: 5000,
            success: function(html) {
              response = html;
            },
            async:false
          });
          
          var json = JSON.parse(response);
          var total = parseInt(json.count) + $(".qq-upload-list input[name='ajax_photos[]']").length + (numUpload);
          
          <?php if($images_limit>0) { ?>
            if(total<=<?php echo $images_limit;?>) {
              json.success = true;
            } else {
              json.success = false;
              //$('.qq-upload-list .qq-uploader').after($('<div class="alert alert-error"><?php echo osc_esc_js(sprintf(__('Too many items were uploaded. Item limit is %d.'), $images_limit)); ?></div>'));
            }
          <?php } else { ?>
            json.success = true;
          <?php } ?>
          return json;
        }
        
        <?php if(osc_image_upload_reorder()) { ?>
        // Enable sorting of images
        $(document).ready(function() {
          if(typeof $.fn.sortable !== 'undefined') {
            $('ul.qq-upload-list').sortable({
              placeholder: "qq-placeholder",
              handle: ".qq-upload-move",
              //containment: ".upload-photos",     // boundary element, ideal especially on mobiles to avoid oversizing page
              
              create: function(event, ui) {
                $('input[name="ajax_order_list"]').val($(this).sortable('toArray').toString());
                <?php if($is_debug) { ?>console.log('Initial order of images: ' + $(this).sortable('toArray').toString());<?php } ?>
              },
              update: function(event, ui) {
                $('input[name="ajax_order_list"]').val($(this).sortable('toArray').toString());
                //console.log('New order of images: ' + $(this).sortable('toArray').toString());
              }
            });
            
            $('ul.qq-upload-list').on('sortupdate',function() {
              $('input[name="ajax_order_list"]').val($(this).sortable('toArray').toString());
              <?php if($is_debug) { ?>console.log('New order of images: ' + $(this).sortable('toArray').toString());<?php } ?>
            });
            
            $('ul.qq-upload-list').disableSelection();
          } else {
            <?php if($is_debug) { ?>console.log('Sortable function not available, jQuery UI is not embedded');<?php } ?>
          }
        });
        <?php } ?>
      </script>
      
    <?php
    } else if(osc_image_upload_library() == 'LEGACY') {
    ?>
      <?php if(osc_is_edit_page()) { ?>
        <div id="photos-gallery">
          <?php self::photos(); ?>
        </div>
      <?php } ?>
      
      <div id="photos" class="legacy upload-photo">
        <?php if(osc_is_publish_page() || (osc_is_edit_page() && (osc_max_images_per_item()==0 || (osc_max_images_per_item()!=0 && osc_count_item_resources()<  osc_max_images_per_item())))) { ?>
          <div class="row">
            <input type="file" name="photos[]" id="photos" class="legacy"/>
          </div>
        <?php } ?>
      </div>
      
      <a href="#" onclick="addNewPhoto(); if (typeof uniform_input_file == 'function') { uniform_input_file(); }; return false;"><?php _e('Add a new photo'); ?></a>

    <?php
    } else {                    
      if($resources==null) { 
        $resources = osc_get_item_resources(); 
      }
      
      $aImages = array();
      
      if(Session::newInstance()->_getForm('photos') != '') {
        $aImages = Session::newInstance()->_getForm('photos');
        
        if (isset($aImages['name'])) {
          $aImages = $aImages['name'];
        } else {
          $aImages = array();
        }
        
        Session::newInstance()->_drop('photos');
        Session::newInstance()->_dropKeepForm('photos');
      }
      ?>
      
      <div id="restricted-fine-uploader"></div>
      <div style="clear:both;"></div>
      
      <?php if(count($aImages)>0 || ($resources!=null && is_array($resources) && count($resources)>0)) { ?>
        <h3><?php _e('Images already uploaded');?></h3>
        
        <ul class="qq-upload-list">
          <?php foreach($resources as $_r) {
            $img = $_r['pk_i_id'].'.'.$_r['s_extension']; ?>
            <li class=" qq-upload-success">
              <span class="qq-upload-file"><?php echo $img; ?></span>
              <a class="qq-upload-delete" href="#" photoid="<?php echo $_r['pk_i_id']; ?>" itemid="<?php echo $_r['fk_i_item_id']; ?>" photoname="<?php echo $_r['s_name']; ?>" photosecret="<?php echo Params::getParam('secret'); ?>" style="display: inline; cursor:pointer;"><?php _e('Delete'); ?></a>
              <div class="ajax_preview_img"><img src="<?php echo osc_apply_filter('resource_thumbnail_url', osc_apply_filter('resource_path', osc_base_url().$_r['s_path']).$_r['pk_i_id'].'_thumbnail.'.$_r['s_extension']); ?>" alt="<?php echo osc_esc_html($img); ?>"></div>
            </li>
          <?php } ?>

          <?php foreach($aImages as $img){ ?>
            <li class=" qq-upload-success">
              <span class="qq-upload-file"><?php echo $img; $img = osc_esc_html($img); ?></span>
              <a class="qq-upload-delete" href="#" ajaxfile="<?php echo $img; ?>" style="display: inline; cursor:pointer;"><?php _e('Delete'); ?></a>
              <div class="ajax_preview_img"><img src="<?php echo osc_base_url() . OC_CONTENT_FOLDER; ?>/uploads/temp/<?php echo $img; ?>" alt="<?php echo $img; ?>"></div>
              <input type="hidden" name="ajax_photos[]" value="<?php echo $img; ?>">
            </li>
          <?php } ?>
        </ul>
      <?php } ?>

      <div style="clear:both;"></div>

      <?php
      $aExt = explode(',', osc_allowed_extension());
      
      foreach($aExt as $key => $value) {
        $aExt[$key] = "'".$value."'";
      }

      $allowedExtensions = implode(',', $aExt);
      $maxSize = osc_max_size_kb() * 1024;
      $maxImages = osc_max_images_per_item();
      ?>

      <script type="text/javascript">
        $(document).ready(function() {
          $('.qq-upload-delete').on('click', function(evt) {
            evt.preventDefault();
            var parent = $(this).parent()
            var result = confirm('<?php echo osc_esc_js(__("This action can't be undone. Are you sure you want to continue?")); ?>');
            var urlrequest = '';
            
            if($(this).attr('ajaxfile')!=undefined) {
              urlrequest = 'ajax_photo='+$(this).attr('ajaxfile');
            } else {
              urlrequest = 'id='+$(this).attr('photoid')+'&item='+$(this).attr('itemid')+'&code='+$(this).attr('photoname')+'&secret='+$(this).attr('photosecret');
            }
            
            if(result) {
              $.ajax({
                type: "POST",
                url: '<?php echo osc_base_url(true); ?>?page=ajax&action=delete_image&'+urlrequest,
                dataType: 'json',
                success: function(data){
                  parent.remove();
                }
              });
            }
          });


          // Rotate image
          $('body').on('click', '.qq-upload-rotate-img', function(e){
            e.preventDefault();

            var img = $(this).parent().find('.ajax_preview_img img');
            angle = parseInt(img.attr('data-angle'));

            var imgWidth = img.width();
            var imgHeight = img.height();
            var ratio = imgWidth/imgHeight;
            var boxWidth = img.parent().width();

            if(!img.hasClass('disabled')) {
              if(isNaN(angle)){ angle = 0; }
              angle += 90;
              img.addClass('disabled');

              img.rotate({ 
                animateTo: angle,
                duration: 300,
                callback: function() {
                  $.ajax({
                    url: '<?php echo osc_base_url(true); ?>?page=ajax&action=rotate_image&file_name=' + img.attr('alt'),
                    type: 'POST',
                    success: function(response) {
                      img.removeClass('disabled');
                      //console.log(response);
                    },
                    error: function(response) {
                      img.removeClass('disabled');
                      //console.log(response);
                    }
                  });
                }
              });
            }
            
            img.attr('data-angle', angle);
          });


          // Mark image as primary
          $('#restricted-fine-uploader').on('click', '.primary_image', function(event){
            if(parseInt($("div.primary_image").index(this))>0){

              var a_src = $(this).parent().find('.ajax_preview_img img').attr('src');
              var a_title = $(this).parent().find('.ajax_preview_img img').attr('alt');
              var a_input = $(this).parent().find('input').attr('value');
              // info
              var a1 = $(this).parent().find('span.qq-upload-file').text();
              var a2 = $(this).parent().find('span.qq-upload-size').text();

              var li_first = $('ul.qq-upload-list li').get(0);

              var b_src = $(li_first).find('.ajax_preview_img img').attr('src');
              var b_title = $(li_first).find('.ajax_preview_img img').attr('alt');
              var b_input = $(li_first).find('input').attr('value');
              var b1 = $(li_first).find('span.qq-upload-file').text();
              var b2 = $(li_first).find('span.qq-upload-size').text();

              $(li_first).find('.ajax_preview_img img').attr('src', a_src);
              $(li_first).find('.ajax_preview_img img').attr('alt', a_title);
              $(li_first).find('input').attr('value', a_input);
              $(li_first).find('span.qq-upload-file').text(a1);
              $(li_first).find('span.qq-upload-size').text(a2);

              $(this).parent().find('.ajax_preview_img img').attr('src', b_src);
              $(this).parent().find('.ajax_preview_img img').attr('alt', b_title);
              $(this).parent().find('input').attr('value', b_input);
              $(this).parent().find('span.qq-upload-file').text(b1);
              $(this).parent().find('span.qq-upload-file').text(b2);
            }
          });

          $('#restricted-fine-uploader').on('click', '.primary_image', function(event){
            $(this).addClass('over primary');
          });

          $('#restricted-fine-uploader').on('mouseenter mouseleave', '.primary_image', function(event){
            if(event.type=='mouseenter') {
              if(!$(this).hasClass('primary')) {
                $(this).addClass('primary');
              }
            } else {
              if(parseInt($("div.primary_image").index(this))>0){
                $(this).removeClass('primary');
              }
            }
          });

          $('#restricted-fine-uploader').on('mouseenter mouseleave','li.qq-upload-success', function(event){
            if(parseInt($("li.qq-upload-success").index(this))>0){
              if(event.type=='mouseenter') {
                $(this).find('div.primary_image').addClass('over');
              } else {
                $(this).find('div.primary_image').removeClass('over');
              }
            }
          });

          window.removed_images = 0;
          $('#restricted-fine-uploader').on('click', 'a.qq-upload-delete', function(event) {
            window.removed_images = window.removed_images+1;
            $('#restricted-fine-uploader .flashmessage-error').remove();
          });

          $('#restricted-fine-uploader').fineUploader({
            request: {
              endpoint: '<?php echo osc_base_url(true) . '?page=ajax&action=ajax_upload'; ?>'
            },
            multiple: true,
            validation: {
              allowedExtensions: [<?php echo $allowedExtensions; ?>],
              sizeLimit: <?php echo $maxSize; ?>,
              itemLimit: <?php echo $maxImages; ?>
            },
            messages: {
              tooManyItemsError: '<?php echo osc_esc_js(__('Too many items ({netItems}) would be uploaded. Item limit is {itemLimit}.'));?>',
              onLeave: '<?php echo osc_esc_js(__('The files are being uploaded, if you leave now the upload will be cancelled.'));?>',
              typeError: '<?php echo osc_esc_js(__('{file} has an invalid extension. Valid extension(s): {extensions}.'));?>',
              sizeError: '<?php echo osc_esc_js(__('{file} is too large, maximum file size is {sizeLimit}.'));?>',
              emptyError: '<?php echo osc_esc_js(__('{file} is empty, please select files again without it.'));?>'
            },
            deleteFile: {
              enabled: true,
              method: "POST",
              forceConfirm: false,
              endpoint: '<?php echo osc_base_url(true) . '?page=ajax&action=delete_ajax_upload'; ?>'
            },
            retry: {
              showAutoRetryNote : true,
              showButton: true
            },
            text: {
              uploadButton: '<?php echo osc_esc_js(__('Click or Drop for upload images')); ?>',
              waitingForResponse: '<?php echo osc_esc_js(__('Processing...')); ?>',
              rotateButton: '<?php echo osc_esc_js(__('Rotate')); ?>',
              retryButton: '<?php echo osc_esc_js(__('Retry')); ?>',
              cancelButton: '<?php echo osc_esc_js(__('Cancel')); ?>',
              failUpload: '<?php echo osc_esc_js(__('Upload failed')); ?>',
              deleteButton: '<?php echo osc_esc_js(__('Delete')); ?>',
              deletingStatusText: '<?php echo osc_esc_js(__('Deleting...')); ?>',
              formatProgress: '<?php echo osc_esc_js(__('{percent}% of {total_size}')); ?>'
            }
          }).on('error', function (event, id, name, errorReason, xhrOrXdr) {
              $('#restricted-fine-uploader .flashmessage-error').remove();
              $('#restricted-fine-uploader').append('<div class="flashmessage flashmessage-error">' + errorReason + '<a class="close" onclick="javascript:$(\'.flashmessage-error\').remove();" >X</a></div>');
          }).on('statusChange', function(event, id, old_status, new_status) {
            $(".alert.alert-error").remove();
          }).on('complete', function(event, id, fileName, responseJSON) {
            if (responseJSON.success) {
              var new_id = id - removed_images;
              var li = $('.qq-upload-list li')[new_id];
              <?php if(Params::getParam('action') === 'item_add') { ?>
              $(li).append('<div class="qq-upload-rotate-img"><a title="<?php echo osc_esc_js(osc_esc_html(__('Rotate'))); ?>"><span><?php echo osc_esc_js(osc_esc_html(__('Rotate image'))); ?></span></a></div>');

              if(parseInt(new_id)==0) {
                $(li).append('<div class="primary_image primary"></div>');
              } else {
                $(li).append('<div class="primary_image"><a title="<?php echo osc_esc_js(osc_esc_html(__('Make primary image'))); ?>"><?php echo osc_esc_js(osc_esc_html(__('Make primary image'))); ?></a></div>');
              }
              <?php }
              // @TOFIX @FIXME escape $responseJSON_uploadName below
              // need a js function similar to osc_esc_js(osc_esc_html()) ?>
              $(li).append('<div class="ajax_preview_img"><img src="<?php echo osc_base_url() . OC_CONTENT_FOLDER; ?>/uploads/temp/'+responseJSON.uploadName+'" alt="' + responseJSON.uploadName + '"></div>');
              $(li).append('<input type="hidden" name="ajax_photos[]" value="'+responseJSON.uploadName+'"></input>');
            }
            <?php if(Params::getParam('action') === 'item_edit') { ?>
          }).on('validateBatch', function(event, fileOrBlobDataArray) {
            // clear alert messages
            if($('#restricted-fine-uploader .alert-error').length>0) {
              $('#restricted-fine-uploader .alert-error').remove();
            }

            var len = fileOrBlobDataArray.length;
            var result = canContinue(len);
            return result.success;
          });

          function canContinue(numUpload) {
            // strUrl is whatever URL you need to call
            var strUrl = "<?php echo osc_base_url(true) . '?page=ajax&action=ajax_validate&id=' . osc_item_id() . '&secret=' . osc_item_secret(); ?>";
            var strReturn = {};

            jQuery.ajax({
              url: strUrl,
              success: function(html) {
                strReturn = html;
              },
              async:false
            });
            
            var json = JSON.parse(strReturn);
            var total = parseInt(json.count) + $("#restricted-fine-uploader input[name='ajax_photos[]']").length + (numUpload);
            
            <?php if($maxImages>0) { ?>
              if(total<=<?php echo $maxImages;?>) {
                json.success = true;
              } else {
                json.success = false;
                $('#restricted-fine-uploader .qq-uploader').after($('<div class="alert alert-error"><?php echo osc_esc_js(sprintf(__('Too many items were uploaded. Item limit is %d.'), $maxImages)); ?></div>'));
              }
            <?php } else { ?>
              json.success = true;
            <?php } ?>
            return json;
          }

          <?php } else { ?>
        });
        <?php } ?>
        });
      </script>
    <?php
    }
  }


  // Add new photo functionality for legacy image uploader
  public static function photos_javascript() {
    if(osc_image_upload_library() == 'LEGACY' || osc_is_backoffice()) {
    ?>
      <script type="text/javascript">
        var photoIndex = 0;
        function gebi(id) { return document.getElementById(id); }
        function ce(name) { return document.createElement(name); }
        
        function re(id) {
          var e = gebi(id);
          e.parentNode.removeChild(e);
        }
        
        function addNewPhoto() {
          var max = <?php echo osc_max_images_per_item(); ?>;
          var num_img = $('input[name="photos[]"]').length + $("a.delete").length;
          if((max!=0 && num_img<max) || max==0) {
            var id = 'p-' + photoIndex++;
            var i = ce('input');
            
            i.setAttribute('type', 'file');
            i.setAttribute('name', 'photos[]');

            var a = ce('a');
            a.style.fontSize = 'x-small';
            a.style.paddingLeft = '10px';
            a.setAttribute('href', '#');
            a.setAttribute('divid', id);
            a.onclick = function() { re(this.getAttribute('divid')); return false; }
            a.appendChild(document.createTextNode('<?php echo osc_esc_js(__('Remove')); ?>'));

            var d = ce('div');
            d.setAttribute('id', id);
            d.setAttribute('style','padding: 4px 0;')

            d.appendChild(i);
            d.appendChild(a);

            gebi('photos').appendChild(d);
          } else {
            alert('<?php echo osc_esc_js(__('Sorry, you have reached the maximum number of images per listing')); ?>');
          }
        }
        
        // Listener: automatically add new file field when the visible ones are full.
        setInterval("add_file_field()", 250);
        
        // Timed: if there are no empty file fields, add new file field.
        function add_file_field() {
          var count = 0;
          $('input[name="photos[]"]').each(function(index) {
            if ($(this).val() == '') {
              count++;
            }
          });
          
          var max = <?php echo osc_max_images_per_item(); ?>;
          var num_img = $('input[name="photos[]"]').length + $("a.delete").length;
          
          if (count == 0 && (max==0 || (max!=0 && num_img<max))) {
            addNewPhoto();
          }
        }
      </script>
      <?php
    }
  }
}