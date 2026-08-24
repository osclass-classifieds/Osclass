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
 * Class CategoryForm
 */
class CategoryForm extends Form
{
  /**
  * @param $category
  */
  public static function primary_input_hidden( $category )
  {
    parent::generic_input_hidden( 'id' , $category[ 'pk_i_id' ]);
  }

  /**
  * @param    $categories
  * @param    $category
  * @param null   $default_item
  * @param string $name
  */
  public static function category_select( $categories , $category , $default_item = null , $name = 'sCategory' )
  {
    echo '<select name="' . $name . '" id="' . $name . '">';
    if(isset($default_item)) {
      echo '<option value="">' . $default_item . '</option>';
    }

    if(!is_array($category) || !isset($category['pk_i_id'])) {
      $category = array('pk_i_id' => null);
    }

    if(is_array($categories) && count($categories) > 0) {
      foreach($categories as $c) {
        echo '<option value="' . $c['pk_i_id'] . '"' . ( ($category['pk_i_id'] == $c['pk_i_id']) ? 'selected="selected"' : '' ) . '>' . $c['s_name'] . '</option>';

        if(isset($c['categories']) && is_array($c['categories'])) {
          self::subcategory_select( $c[ 'categories' ] , $category , $default_item , 1 );
        }
      }
    }
    echo '</select>';
  }

  /**
  * @param    $categories
  * @param    $category
  * @param null $default_item
  * @param int  $deep
  */
  public static function subcategory_select( $categories , $category , $default_item = null , $deep = 0 )
  {
    $deep_string = '';

    for($var = 0;$var<$deep;$var++) {
      $deep_string .= '&nbsp;&nbsp;';
    }

    $deep++;

    if(!is_array($category) || !isset($category['pk_i_id'])) {
      $category = array('pk_i_id' => null);
    }

    if(is_array($categories) && count($categories) > 0) {
      foreach($categories as $c) {
        echo '<option value="' . $c['pk_i_id'] . '"' . ( ($category['pk_i_id'] == $c['pk_i_id']) ? 'selected="selected"' : '' ) . '>' . $deep_string.$c['s_name'] . '</option>';

        if(isset($c['categories']) && is_array($c['categories'])) {
          self::subcategory_select( $c[ 'categories' ] , $category , $default_item , $deep );
        }
      }
    }
  }

  /**
  * @param null $categories
  * @param null $selected
  * @param int  $depth
  */
  public static function categories_tree( $categories = null , $selected = null , $depth = 0 )
  {
    if(( $categories != null ) && is_array($categories) ) {
      echo '<ul id="cat' . $categories[0]['fk_i_parent_id'] . '">';

      $d_string = '';
      // disabled in 440
      //for($var_d = 0; $var_d < $depth; $var_d++) {
      //  $d_string .= '&nbsp;&nbsp;&nbsp;&nbsp;';
      //}

      foreach($categories as $c) {
        echo '<li>';
        echo $d_string . '<input type="checkbox" name="categories[]" value="' . $c['pk_i_id'] . '" onclick="javascript:checkCat(\'' . $c['pk_i_id'] . '\', this.checked);" ' . ( in_array($c['pk_i_id'], $selected) ? 'checked="checked"' : '' ) . ' />' . ( ( $depth >= 0 ) ? '<span>' : '' ) . $c['s_name'] . ( ( $depth >= 0 ) ? '</span>' : '' );
        self::categories_tree( $c[ 'categories' ] , $selected , $depth + 1 );
        echo '</li>';
      }
      echo '</ul>';
    }
  }

  /**
  * @param null $category
  */
  public static function expiration_days_input_text( $category = null )
  {
    parent::generic_input_text( 'i_expiration_days' , ( isset($category) && isset($category['i_expiration_days'])) ? $category[ 'i_expiration_days' ] : '' , 3);
  }

  public static function icon_input_text( $category = null )
  {
    parent::generic_input_text( 's_icon' , ( isset($category) && isset($category['s_icon'])) ? $category[ 's_icon' ] : '');
  }

  public static function color_input_text( $category = null )
  {
    parent::generic_input_text( 's_color' , ( isset($category) && isset($category['s_color'])) ? $category[ 's_color' ] : '');
  }

  /**
  * @param null $category
  */
  public static function position_input_text( $category = null )
  {
    parent::generic_input_text( 'i_position' , ( isset($category) && isset($category['i_position'])) ? $category[ 'i_position' ] : '' , 3);
  }

  /**
  * @param null $category
  */
  public static function enabled_input_checkbox( $category = null )
  {
    parent::generic_input_checkbox( 'b_enabled' , '1' , ( isset( $category ) && isset( $category[ 'b_enabled' ] ) && $category[ 'b_enabled' ] == 1 ) );
  }

  /**
  * @param null $category
  */
  public static function apply_changes_to_subcategories( $category = null )
  {
    echo '<input id="apply_changes_to_subcategories" type="checkbox" name="apply_changes_to_subcategories" value="1" />';
  }

  // Render category icon for admin list cells
  public static function admin_icon_display($icon) {
    if($icon === null || trim((string)$icon) === '') {
      return '-';
    }

    $icon = trim((string)$icon);
    $escaped = osc_esc_html($icon);

    if(stripos($icon, '<svg') !== false) {
      $svg = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $icon);
      $svg = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $svg);
      if(stripos($svg, '<svg') !== false && stripos($svg, 'style=') === false) {
        $svg = preg_replace('/<svg/i', '<svg style="height:20px;width:auto;max-width:40px;"', $svg, 1);
      }
      return '<span class="category-icon-cell">' . $svg . '<span class="category-icon-value">' . $escaped . '</span></span>';
    }

    if(preg_match('/\bfa[srb]?\s+fa-|\bfa\s+fa-|\bfa-[a-z0-9-]+/i', $icon)) {
      return '<span class="category-icon-cell"><i class="' . osc_esc_html($icon) . '"></i><span class="category-icon-value">' . $escaped . '</span></span>';
    }

    if(preg_match('/^https?:\/\//i', $icon) || preg_match('/\.(png|jpe?g|gif|svg|webp)(\?.*)?$/i', $icon)) {
      return '<span class="category-icon-cell"><img src="' . osc_esc_html($icon) . '" height="20" alt="" /><span class="category-icon-value">' . $escaped . '</span></span>';
    }

    return '<span class="category-icon-value">' . $escaped . '</span>';
  }

  /**
  * @param null $category
  */
  public static function price_enabled_for_category( $category = null )
  {
    parent::generic_input_checkbox( 'b_price_enabled' , '1' , ( isset( $category ) && isset( $category[ 'b_price_enabled' ] ) && $category[ 'b_price_enabled' ] == 1 ) );
  }

  // Price enabled/disabled select for admin forms
  public static function price_enabled_select($category = null) {
    $enabled = (isset($category) && isset($category['b_price_enabled']) && (int)$category['b_price_enabled'] === 1);
    echo '<select name="b_price_enabled" id="b_price_enabled">';
    echo '<option value="1"' . ($enabled ? ' selected="selected"' : '') . '>' . __('Enabled') . '</option>';
    echo '<option value="0"' . (!$enabled ? ' selected="selected"' : '') . '>' . __('Disabled') . '</option>';
    echo '</select>';
  }

  // Parent category select for admin edit
  public static function parent_select_admin($categories, $category, $disabledIds = array()) {
    if(!is_array($disabledIds)) {
      $disabledIds = array();
    }
    $parentId = (isset($category['fk_i_parent_id']) && $category['fk_i_parent_id'] !== '' && $category['fk_i_parent_id'] !== null ? (int)$category['fk_i_parent_id'] : 0);
    echo '<select name="fk_i_parent_id" id="fk_i_parent_id">';
    echo '<option value=""' . ($parentId <= 0 ? ' selected="selected"' : '') . '>' . __('None') . '</option>';
    self::parent_select_admin_options($categories, $parentId, $disabledIds, 0);
    echo '</select>';
  }

  // Render parent select options recursively
  private static function parent_select_admin_options($categories, $selectedId, $disabledIds, $depth) {
    if(!is_array($categories) || count($categories) == 0) {
      return;
    }

    $prefix = str_repeat('&nbsp;&nbsp;', $depth);
    foreach($categories as $c) {
      $id = (int)$c['pk_i_id'];
      $disabled = in_array($id, $disabledIds, true) ? ' disabled="disabled"' : '';
      $sel = ($selectedId === $id ? ' selected="selected"' : '');
      echo '<option value="' . $id . '"' . $sel . $disabled . '>' . $prefix . osc_esc_html($c['s_name']) . '</option>';
      if(isset($c['categories']) && is_array($c['categories'])) {
        self::parent_select_admin_options($c['categories'], $selectedId, $disabledIds, $depth + 1);
      }
    }
  }

  // Localized category name fields (admin edit)
  public static function printLocaleName($locales, $category = null) {
    if(!is_array($locales)) {
      return;
    }
    foreach($locales as $locale) {
      $code = $locale['pk_c_code'];
      $value = (isset($category['locale'][$code]['s_name']) ? $category['locale'][$code]['s_name'] : '');
      $name = $code . '#s_name';
      echo '<div class="input-has-placeholder locale-' . osc_esc_html($code) . '">';
      echo '<input id="' . osc_esc_html($name) . '" type="text" name="' . osc_esc_html($name) . '" value="' . osc_esc_html(htmlentities($value, ENT_COMPAT, 'UTF-8')) . '" />';
      echo '</div>';
    }
  }

  // Localized category description fields (admin edit)
  public static function printLocaleDescription($locales, $category = null) {
    if(!is_array($locales)) {
      return;
    }
    foreach($locales as $locale) {
      $code = $locale['pk_c_code'];
      $value = (isset($category['locale'][$code]['s_description']) ? $category['locale'][$code]['s_description'] : '');
      $name = $code . '#s_description';
      echo '<div class="input-has-placeholder locale-' . osc_esc_html($code) . '">';
      echo '<textarea id="' . osc_esc_html($name) . '" name="' . osc_esc_html($name) . '" rows="8">' . osc_esc_html($value) . '</textarea>';
      echo '</div>';
    }
  }

  /**
  * @param    $locales
  * @param null $category
  */
  public static function multilanguage_name_description( $locales , $category = null )
  {
    $tabs = array();
    $content = array();
    foreach($locales as $locale) {
        $value = isset($category['locale'][$locale['pk_c_code']]) ? $category['locale'][$locale['pk_c_code']]['s_name'] : '';
        $name = $locale['pk_c_code'] . '#s_name';
        $nameTextarea = $locale['pk_c_code'] . '#s_description';
        $valueTextarea = isset($category['locale'][$locale['pk_c_code']]) ? $category['locale'][$locale['pk_c_code']]['s_description'] : '';

        $contentTemp  = '<div id="'.$category['pk_i_id'].'-'.$locale['pk_c_code'].'" class="category-details-form">';
        $contentTemp .= '<div class="FormElement"><label>' . __('Name') . '</label><input id="' . $name .'" type="text" name="' . $name .'" value="' . osc_esc_html(htmlentities( $value, ENT_COMPAT, 'UTF-8' )) . '"/></div>';
        $contentTemp .= '<div class="FormElement"><label>' . __('Description') . '</label>';
        $contentTemp .= '<textarea id="' . $nameTextarea . '" name="' . $nameTextarea . '" rows="10">' . $valueTextarea . '</textarea>';
        $contentTemp .= '</div></div>';
        $tabs[] = '<li><a href="#'.$category['pk_i_id'].'-'.$locale['pk_c_code'].'">' . $locale['s_name'] . '</a></li>';
        $content[] = $contentTemp;
     }

     echo '<div class="ui-osc-tabs osc-tab">';
     echo '<ul>' . implode( '', $tabs) . '</ul>';
     echo implode( '', $content);
     echo '</div>';
  }
}

/* file end: ./oc-includes/osclass/frm/Category.form.class.php */
