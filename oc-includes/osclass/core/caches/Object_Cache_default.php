<?php
/**
 * Object_Cache_default class
 */
class Object_Cache_default implements iObject_Cache{

  /**
   * Holds the cached objects
   *
   * @var array
   * @access private
   * @since 3.4
   */
  public $cache = array();

  /**
   * The amount of times the cache data was already stored in the cache.
   *
   * @since 3.4
   * @access private
   * @var int
   */
  public $cache_hits = 0;

  /**
   * Amount of times the cache did not have the request in cache
   *
   * @var int
   * @access public
   * @since 3.4
   */
  public $cache_misses = 0;


  /**
   * Adds data to the cache if it doesn't already exist.
   * @since 3.4
   *
   * @param int|string $key What to call the contents in the cache
   * @param mixed $data The contents to store in the cache
   * @param int $expire When to expire the cache contents
   * @return bool False if cache key and group already exist, true on success
   */
  public function add( $key, $data, $expire = 0) {
    $id = $key;

    if ( $this->_exists( $id ) ) {
      return false;
    }

    return $this->set($key, $data, $expire);
  }

  /**
   * Remove the contents of the cache key
   * @since 3.4
   *
   * @param int|string $key What the contents in the cache are called
   * @return bool False if the contents weren't deleted and true on success
   */
  public function delete($key) {
    if ( ! $this->_exists( $key ) ) {
      return false;
    }

    unset( $this->cache[$key] );
    return true;
  }

  /**
   * Clears the object cache of all data
   * @since 3.4
   *
   * @return bool Always returns true
   */
  public function flush() {
  $this->cache = array ();

  return true;
  }

  /**
   * Retrieves the cache contents, if it exists
   * @since 3.4
   *
   * @param int|string $key What the contents in the cache are called
   * @param bool $found if can be retrieved from cache
   * @return bool|mixed False on failure to retrieve contents or the cache
   *  contents on success
   */
  public function get( $key, &$found = null ) {
    if ( $this->_exists( $key ) ) {
      $found = true;
      ++ $this->cache_hits;
      if ( is_object( $this->cache[ $key ] ) ) {
      return clone $this->cache[ $key ];
      } else {
      return $this->cache[ $key ];
      }
    }
    $found = false;
    ++ $this->cache_misses;
    return false;
  }

  /**
   * Sets the data contents into the cache
   * @since 3.4
   *
   * @param int|string $key What to call the contents in the cache
   * @param mixed $data The contents to store in the cache
   * @param int $expire Not Used
   * @return bool Always returns true
   */
  public function set($key, $data, $expire = 0) {
    if ( is_object( $data ) ) {
      $data = clone $data;
    }

    $this->cache[$key] = $data;
    return true;
  }

  /**
   * Echoes the stats of the caching.
   * Gives the cache hits, and cache misses.
   *
   * @since 3.4
   */
  public function stats() {
    echo '<fieldset id="osc-cache-logs" class="osc-cache-default" style="border:1px solid #000;line-height:1.4;padding:8px 10px 10px 10px;margin: 12px;width:calc(100% - 24px);background-color:#fff;">' . PHP_EOL;
    echo '<legend style="font-size:14px;font-weight:600;padding:4px 8px;border:1px solid #000;background:#fff;">' . ucwords($this->_get_cache()) . ' stats (Cache hits: ' . $this->cache_hits .' - Cache misses: ' . $this->cache_misses . ')</legend>' . PHP_EOL;
    echo '<table style="border-collapse: collapse;width:100%;font-size:13px;padding:0;border-spacing:0;font-family:monospace;line-height:1.4;">' . PHP_EOL;
    if (count($this->cache) == 0) {
      echo '<tr><td>No cache entries</td></tr>' . PHP_EOL;
    } else {
      foreach ($this->cache as $key => $data) {
        $row_style = '';
        if (1==2) {
          $row_style = 'style="background-color: #FFC2C2;"';
        }
        echo '<tr ' . $row_style . '>' . PHP_EOL;
        echo '<td style="padding:6px 8px;text-align:left;vertical-align:top;border: 1px solid #ccc;min-width:100px;">' . $key . '</td>' . PHP_EOL;
        echo '<td style="padding:6px 8px;text-align:left;vertical-align:top;border: 1px solid #ccc;">';
        
        if (1==2) {
          echo '<strong>Error number:</strong> ' . 'error_code' . '<br/>';
          echo '<strong>Error description:</strong> ' . 'error_desc' . '<br/><br/>';
        }
        echo json_encode($data);
        echo '</td>' . PHP_EOL;
        echo '</tr>' . PHP_EOL;
      }
    }
    echo '</table>' . PHP_EOL;
    echo '</fieldset>' . PHP_EOL;
  }

  /**
   * Utility function to determine whether a key exists in the cache.
   * @since  3.4
   *
   * @access protected
   *
   * @param $key
   *
   * @return bool
   */
  protected function _exists( $key ) {
    return isset( $this->cache[ $key ] );
  }

  /**
   * Return hash of a given key
   *
   * @param $key
   *
   * @return string
   */
  protected function _getKey( $key ) {
    return md5($key);
  }
  /**
   * Sets up object properties
   *
   * @since 2.4
   */
  public function __construct() {
  }

  /**
   * is_supported()
   *
   * Check to see if APC is available on this system, bail if it isn't.
   */
  public static function is_supported() {
    return true;
  }

  /**
   *
   */
  public function __destruct() {
    if(defined('OSC_DEBUG_CACHE') && OSC_DEBUG_CACHE === true && osc_is_admin_user_logged_in()) {
      $this->stats();
    }
    
    return true;
  }

  /**
   * @return string
   */
  public function _get_cache() {
    return 'default';
  }
}