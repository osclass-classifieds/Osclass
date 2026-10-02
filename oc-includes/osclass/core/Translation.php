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


class Translation {
  private $translator;
  private $loader;
  private $files;
  private $array_generator;
  private static $instance;

  /**
   * @param bool $install
   *
   * @return \Translation
   */
  public static function newInstance($install = false) {
    if(!self::$instance instanceof self) {
      self::$instance = new self($install);
    }

    return self::$instance;
  }

  /**
   * @return \Translation
   */
  public static function init() {
    self::$instance = new self();
    return self::$instance;
  }

  /**
   * Translation constructor.
   *
   * @param bool $install
   */
  public function __construct($install = false) {
    $this->translator = new Gettext\Translator();
    $this->loader = new Gettext\Loader\MoLoader();
    $this->array_generator = new Gettext\Generator\ArrayGenerator();

    if(!$install) {
      // get user/admin locale
      if(OC_ADMIN) {
        $locale = osc_current_admin_locale();
      } else {
        $locale = osc_current_user_locale();
      }

      // load core
      $core_file = osc_apply_filter('mo_core_path', osc_translations_path() . $locale . '/core.mo', $locale);
      $this->_load($core_file, 'core');

      // load messages
      $domain = osc_apply_filter('theme', osc_theme());
      $messages_file = osc_apply_filter('mo_theme_messages_path', osc_themes_path() . $domain . '/languages/' . $locale . '/messages.mo', $locale, $domain);

      if(!file_exists($messages_file)) {
        $messages_file = osc_apply_filter('mo_core_messages_path', osc_translations_path() . $locale . '/messages.mo', $locale);
      }

      $this->_load($messages_file, 'messages');

      // update 420 - load parent theme translations, in case child theme is used
      $child_split = explode('_', $domain);
      $domain_parent = '';


      // load theme
      $theme_file = osc_apply_filter('mo_theme_path', osc_themes_path() . $domain . '/languages/' . $locale . '/theme.mo', $locale, $domain);

      if(!file_exists($theme_file)) {
        if(!file_exists(osc_themes_path() . $domain)) {
          $domain = osc_theme();
        }
        $theme_file = osc_translations_path() . $locale . '/theme.mo';
      }

      $this->_load($theme_file, $domain);


      // update 420 - load parent theme translations, in case child theme is used
      if(end($child_split) == 'child') {
        $domain_parent = str_replace('_child', '', $domain);
        $parent_theme_file = osc_apply_filter('mo_theme_path', osc_themes_path() . $domain_parent . '/languages/' . $locale . '/theme.mo', $locale, $domain_parent);

        if(file_exists($parent_theme_file)) {
          $this->_load($parent_theme_file, $domain_parent);
        }
      }


      // load plugins
      $aPlugins = Plugins::listEnabled();
      foreach($aPlugins as $plugin) {
        $domain = preg_replace('|/.*|', '', $plugin);
        $plugin_file = osc_apply_filter('mo_plugin_path', osc_plugins_path() . $domain . '/languages/' . $locale . '/messages.mo', $locale, $domain);

        if(file_exists($plugin_file)) {
          $this->_load($plugin_file, $domain);
        }
      }

    } else {
      $core_file = osc_translations_path() . osc_current_admin_locale() . '/core.mo';
      $this->_load($core_file, 'core');
    }
  }

  /**
   * @return \Gettext\Translator
   */
  public function _get() {
    return $this->translator;
  }


  public function _getFiles() {
    return $this->files;
  }

  /**
   * @param $file
   * @param $domain
   *
   * @return bool|\Translation
   */
  public function _load($file, $domain) {
    if(!file_exists($file)) {
      return false;
    }

    $gettext_translation = $this->loader->loadFile($file);
    $gettext_array = $this->array_generator->generateArray($gettext_translation);

    if(is_array($gettext_array)) {
      $gettext_array['domain'] = $domain;
    } else {
      return false;
    }

    $this->translator->addTranslations($gettext_array);

    $this->files[] = array(
      'domain' => $domain,
      'path' => $file,
      'last_mod' => date('Y-m-d H:i:s', filemtime($file)),
      'size' => filesize($file)
    );

    return $this;
  }


  // PRINT TRANSLATIONS FOR DEBUG
  public function printTranslations() {
    $arr = (array)$this->_get();
    $files = $this->_getFiles();
    $dictionary = $arr["\0*\0dictionary"] ?? [];
    $domains = array_keys($dictionary);

    echo '<fieldset id="osc-database-logs" style="border:1px solid #000;line-height:1.4;padding:8px 10px 10px 10px;margin: 12px;width:calc(100% - 24px);background-color:#fff;">' . PHP_EOL;
    echo '<legend style="font-size:14px;font-weight:600;padding:4px 8px;border:1px solid #000;background:#fff;">Translation strings (Total domains: ' . count($domains) .')</legend>' . PHP_EOL;
    echo '<table style="border-collapse: collapse;width:100%;font-size:13px;padding:0;border-spacing:0;font-family:monospace;line-height:1.4;">' . PHP_EOL;

    foreach($domains as $domain) {
      $strings = $dictionary[$domain][''] ?? [];

      echo '<tr>' . PHP_EOL;
      echo '<td style="padding:6px 8px;text-align:left;vertical-align:top;border: 1px solid #ccc;min-width:100px;">' . $domain . '</td>' . PHP_EOL;
      echo '<td style="padding:6px 8px;text-align:left;vertical-align:top;border: 1px solid #ccc;">' . count($strings) . ' strings</td>' . PHP_EOL;
      echo '<td style="padding:6px 8px;text-align:left;vertical-align:top;border: 1px solid #ccc;"></td>';
      echo '</tr>';
    }

    echo '<tr><td>&nbsp;</td></tr>' . PHP_EOL;

    echo '<tr style="font-weight:bold;">' . PHP_EOL;
    echo '<td style="padding:6px 8px;text-align:left;vertical-align:top;border: 1px solid #ccc;min-width:100px;">Files / Catalogues</td>' . PHP_EOL;
    echo '<td style="padding:6px 8px;text-align:left;vertical-align:top;border: 1px solid #ccc;">.mo files loaded by Osclass core with original load order</td>' . PHP_EOL;
    echo '<td style="padding:6px 8px;text-align:left;vertical-align:top;border: 1px solid #ccc;"></td>';
    echo '</tr>';

    foreach($files as $file) {
      echo '<tr>' . PHP_EOL;
      echo '<td style="padding:6px 8px;text-align:left;vertical-align:top;border: 1px solid #ccc;min-width:100px;">' . $file['domain'] . '</td>' . PHP_EOL;
      echo '<td style="padding:6px 8px;text-align:left;vertical-align:top;border: 1px solid #ccc;">' . $file['path'] . '</td>' . PHP_EOL;
      echo '<td style="padding:6px 8px;text-align:left;vertical-align:top;border: 1px solid #ccc;">' . $file['last_mod'] . ' / ' . $file['size'] . '</td>';
      echo '</tr>';
    }

    echo '<tr><td>&nbsp;</td></tr>' . PHP_EOL;

    foreach($domains as $domain) {
      $strings = $dictionary[$domain][''] ?? [];

      echo '<tr style="font-weight:bold;">' . PHP_EOL;
      echo '<td style="padding:6px 8px;text-align:left;vertical-align:top;border: 1px solid #ccc;min-width:100px;">' . $domain . '</td>' . PHP_EOL;
      echo '<td style="padding:6px 8px;text-align:left;vertical-align:top;border: 1px solid #ccc;">List of domain translations</td>' . PHP_EOL;
      echo '<td style="padding:6px 8px;text-align:left;vertical-align:top;border: 1px solid #ccc;">' . count($strings) . '</td>';
      echo '</tr>';

      if(is_array($strings) && !empty($strings)) {
        foreach($strings as $orig => $trans) {
          $row_style = '';

          if($trans == '') {
            $row_style = 'style="background-color: #FFC2C2;"';
          }

          echo '<tr ' . $row_style . '>' . PHP_EOL;
          echo '<td style="padding:6px 8px;text-align:left;vertical-align:top;border: 1px solid #ccc;min-width:100px;">' . $domain . '</td>' . PHP_EOL;
          echo '<td style="padding:6px 8px;text-align:left;vertical-align:top;border: 1px solid #ccc;">' . htmlspecialchars((string)$orig) . '</td>' . PHP_EOL;
          echo '<td style="padding:6px 8px;text-align:left;vertical-align:top;border: 1px solid #ccc;">' . htmlspecialchars((string)$trans) . '</td>';
          echo '</tr>';
        }

        echo '<tr><td>&nbsp;</td></tr>' . PHP_EOL;
      }
    }

    echo '</table>' . PHP_EOL;
    echo '</fieldset>' . PHP_EOL;
  }

}


/* file end: ./oc-includes/osclass/core/Translation.php */
