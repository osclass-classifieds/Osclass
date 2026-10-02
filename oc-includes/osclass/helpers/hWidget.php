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
 * Helper Widgets
 * @package Osclass
 * @subpackage Helpers
 * @author Osclass
 */

// Sanitize a widget slug to [a-z0-9_-]
function osc_widget_sanitize_slug($name) {
  $name = strtolower(trim((string)$name));
  $name = preg_replace('/[^a-z0-9_-]/', '-', $name);
  $name = preg_replace('/-+/', '-', $name);
  $name = trim($name, '-_');
  if(strlen($name) > 50) {
    $name = substr($name, 0, 50);
    $name = rtrim($name, '-_');
  }
  return $name;
}

// Theme widget locations from the active theme index.php
function osc_widget_theme_sections() {
  $info = WebThemes::newInstance()->loadThemeInfo(osc_theme());
  $locations = (isset($info['locations']) && is_array($info['locations']) ? $info['locations'] : array());
  $out = array();
  foreach($locations as $location) {
    $slug = osc_widget_sanitize_slug($location);
    if($slug != '') {
      $out[] = $slug;
    }
  }
  return array_values(array_unique($out));
}

// Theme locations plus custom sections and unused DB locations
function osc_widget_sections() {
  $sections = array_merge(osc_widget_theme_sections(), osc_widget_custom_sections());
  if(class_exists('Widget')) {
    $db = Widget::newInstance()->listLocations();
    if(is_array($db)) {
      $sections = array_merge($sections, $db);
    }
  }

  $out = array();
  foreach($sections as $section) {
    $slug = osc_widget_sanitize_slug($section);
    if($slug != '') {
      $out[] = $slug;
    }
  }

  return osc_apply_filter('widget_sections', array_values(array_unique($out)));
}

// Resolve localized widget content
function osc_widget_locale_text($widget) {
  if(!is_array($widget)) {
    return '';
  }
  return Widget::newInstance()->resolveContent($widget);
}

// Current widget row from the iterator or view
function osc_get_widget() {
  if(View::newInstance()->_exists('widgets')) {
    $widget = View::newInstance()->_current('widgets');
    if(is_array($widget) && !empty($widget)) {
      return $widget;
    }
  }

  if(View::newInstance()->_exists('widget')) {
    $widget = View::newInstance()->_get('widget');
    if(is_array($widget)) {
      return $widget;
    }
  }

  return array();
}

// Widgets at a location; nonempty by default
function osc_get_widgets($location, $only_nonempty = true) {
  $widgets = Widget::newInstance()->findByLocation($location);
  $widgets = osc_apply_filter('widgets_at_location', $widgets, $location);

  if(!$only_nonempty) {
    return $widgets;
  }

  $out = array();
  foreach($widgets as $widget) {
    if(!osc_widget_is_empty($widget)) {
      $out[] = $widget;
    }
  }

  return $out;
}

// Count nonempty widgets in a section
function osc_count_widgets($location) {
  return count(osc_get_widgets($location, true));
}

// True when resolved content and code are both empty
function osc_widget_is_empty($widget = null) {
  if($widget === null) {
    $widget = osc_get_widget();
  }

  if(!is_array($widget) || empty($widget)) {
    return osc_apply_filter('widget_is_empty', true, $widget);
  }

  $content = trim((string)(isset($widget['s_content']) ? $widget['s_content'] : osc_widget_locale_text($widget)));
  $code = trim((string)(isset($widget['s_code']) ? $widget['s_code'] : ''));
  $empty = ($content == '' && $code == '');

  return osc_apply_filter('widget_is_empty', $empty, $widget);
}

// Iterator: load widgets for a location and advance
function osc_has_widgets($location = '') {
  if($location != '' || !View::newInstance()->_exists('widgets')) {
    View::newInstance()->_exportVariableToView('widgets', osc_get_widgets($location, true));
  }

  return View::newInstance()->_next('widgets');
}

// Reset the widget iterator
function osc_reset_widgets($location = '') {
  View::newInstance()->_erase('widgets');
  if($location != '') {
    View::newInstance()->_exportVariableToView('widgets', osc_get_widgets($location, true));
  }
}

// Field from the current widget
function osc_widget_field($field, $locale = '') {
  return osc_field(osc_get_widget(), $field, $locale);
}

// Current widget id
function osc_widget_id() {
  return osc_widget_field('pk_i_id');
}

// Admin label (s_description)
function osc_widget_name() {
  return osc_widget_field('s_description');
}

// Unique internal name
function osc_widget_internal_name() {
  return osc_widget_field('s_internal_name');
}

// Section / location
function osc_widget_location() {
  return osc_widget_field('s_location');
}

// Device visibility: all, mobile, desktop
function osc_widget_device() {
  $device = osc_widget_field('s_device_visibility');
  if($device != 'mobile' && $device != 'desktop') {
    return 'all';
  }
  return $device;
}

// Localized HTML content
function osc_widget_content($locale = '') {
  if($locale != '') {
    return (string)osc_widget_field('s_content', $locale);
  }

  $widget = osc_get_widget();
  if(isset($widget['s_content'])) {
    return (string)$widget['s_content'];
  }

  return osc_widget_locale_text($widget);
}

// Locale-independent code (JS/HTML)
function osc_widget_code() {
  return (string)osc_widget_field('s_code');
}

// Locale-independent CSS
function osc_widget_css() {
  return (string)osc_widget_field('s_css');
}

// CSS classes for a widget wrapper
function osc_widget_class($widget = null) {
  if($widget === null) {
    $widget = osc_get_widget();
  }

  $classes = array('widget');
  $name = (isset($widget['s_internal_name']) ? osc_widget_sanitize_slug($widget['s_internal_name']) : '');
  if($name != '') {
    $classes[] = 'wdg-' . $name;
  }

  $device = (isset($widget['s_device_visibility']) ? $widget['s_device_visibility'] : 'all');
  if($device === 'mobile') {
    $classes[] = 'widget-hide-desktop';
  } else if($device === 'desktop') {
    $classes[] = 'widget-hide-mobile';
  }

  return osc_apply_filter('widget_class', implode(' ', $classes), $widget);
}

// HTML id for a widget wrapper
function osc_widget_html_id($widget = null) {
  if($widget === null) {
    $widget = osc_get_widget();
  }

  $id = (isset($widget['pk_i_id']) ? (int)$widget['pk_i_id'] : 0);
  return osc_apply_filter('widget_html_id', 'widget-' . $id, $widget);
}

// Wrap filtered content (and code) in the default widget markup
function osc_widget_content_wrap($content = '', $widget = array()) {
  $code = osc_apply_filter('widget_code', isset($widget['s_code']) ? $widget['s_code'] : '', $widget);
  $inner = $content . $code;
  if(trim($inner) == '') {
    return '';
  }

  $device = (isset($widget['s_device_visibility']) ? $widget['s_device_visibility'] : 'all');
  if($device === 'mobile' || $device === 'desktop') {
    osc_widget_print_device_css();
  }

  $css = osc_apply_filter('widget_css', isset($widget['s_css']) ? $widget['s_css'] : '', $widget);
  $html = '';
  if(trim((string)$css) != '') {
    $html .= '<style type="text/css">' . $css . '</style>';
  }

  $html .= '<div id="' . osc_esc_html(osc_widget_html_id($widget)) . '" class="' . osc_esc_html(osc_widget_class($widget)) . '" data-id="' . osc_esc_html(isset($widget['pk_i_id']) ? $widget['pk_i_id'] : '') . '" data-location="' . osc_esc_html(isset($widget['s_location']) ? strtolower(trim((string)$widget['s_location'])) : '') . '" data-kind="' . osc_esc_html(isset($widget['e_kind']) ? strtolower(trim((string)$widget['e_kind'])) : '') . '" data-device="' . osc_esc_html(isset($widget['s_device_visibility']) ? $widget['s_device_visibility'] : 'all') . '">' . $inner . '</div>';

  return osc_apply_filter('widget_content_wrap', $html, $content, $widget);
}

// Print one widget; current row if omitted
function osc_show_widget($widget = null) {
  if($widget === null) {
    $widget = osc_get_widget();
  }

  if(!is_array($widget) || osc_widget_is_empty($widget)) {
    return;
  }

  osc_run_hook('before_show_widget', $widget);
  $html = osc_apply_filter('widget_content', isset($widget['s_content']) ? $widget['s_content'] : '', $widget);
  $html = osc_apply_filter('widget_html', $html, $widget);
  if(trim((string)$html) != '') {
    echo $html;
  }
  osc_run_hook('after_show_widget', $widget);
}

// Alias of osc_show_widgets for new theme code
function osc_widget($location) {
  osc_show_widgets($location);
}

// Device visibility CSS, printed once on header
function osc_widget_print_device_css() {
  static $printed = false;
  if($printed) {
    return;
  }
  $printed = true;

  $css = '<style type="text/css">@media screen and (max-width: 767px) { .widget-hide-mobile { display: none !important; } } @media screen and (min-width: 768px) { .widget-hide-desktop { display: none !important; } }</style>';
  echo osc_apply_filter('widget_device_css', $css);
}
