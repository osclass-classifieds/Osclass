<?php
/**
 * Object_Cache_memcache class
 */
class Object_Cache_memcache implements iObject_Cache {
  /**
   * Holds the memcache object
   *
   * @var array
   * @access private
   * @since 3.4
   */
  private $_memcache;

  protected $_memcache_conf = array(
    'default' => array(
      'default_host' => '127.0.0.1',
      'default_port' => 11211,
      'default_weight' => 1
    )
  );

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
   * The blog prefix to prepend to keys in non-global groups.
   *
   * @var int
   * @access private
   * @since 3.4
   */
  public $default_expiration = 60;

  /**
   * Adds data to the cache if it doesn't already exist.
   * @since 3.4
   *
   * @param int|string $key What to call the contents in the cache
   * @param mixed $data The contents to store in the cache
   * @param int $expire When to expire the cache contents
   * @return bool False if cache key and group already exist, true on success
   */
  public function add($key, $data, $expire = 0) {
    $id = $key;

    if(is_object($data)) {
      $data = clone $data;
    }

    $store_data = $data;

    if(is_array($data)) {
      $store_data = new ArrayObject($data);
    }

    $expire = ($expire == 0) ? $this->default_expiration : $expire;
    $result = $this->_memcache->add($key, array($store_data, time(), $expire), 0, $expire);
    
    if(false !== $result) {
      $this->cache[$key] = $data;
    }

    return $result;
  }

  /**
   * Remove the contents of the cache key in the group
   * @since 3.4
   *
   * @param int|string $key What the contents in the cache are called
   * @return bool False if the contents weren't deleted and true on success
   */
  public function delete($key) {
    $result = $this->_memcache->delete($key);
    
    if(false !== $result) {
      unset($this->cache[$key]);
    }
    
    return $result;
  }

  /**
   * Clears the object cache of all data
   * @since 3.4
   *
   * @return bool Always returns true
   */
  public function flush() {
    $this->cache = array();
    return $this->_memcache->flush();
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
  public function get($key, &$found = null) {
    $found = false;

    if(isset($this->cache[$key])) {
      $found = true;
      
      if(is_object($this->cache[$key])) {
        $value = clone $this->cache[$key];
      } else {
        $value = $this->cache[$key];
      }
      
      ++ $this->cache_hits;
      $return = $value;
      
    } else {
      $found = true;
      $value = $this->_memcache->get($key);
      
      if(is_object($value) && 'ArrayObject' === get_class($value)) {
        $value = $value->getArrayCopy();
      }
      
      if(NULL === $value) {
        $found = false;
        $value = false;
      }

      $this->cache[$key] = is_object($value) ? clone $value : $value;
      
      if($found) {
        ++ $this->cache_hits;
        $return = $this->cache[$key];
      } else {
        ++ $this->cache_misses;
        $return = false;
      }
    }
    
    return $return;
  }

  /**
   * Sets the data contents into the cache
   * @since 3.4
   *
   * @param int|string $key What to call the contents in the cache
   * @param mixed $data The contents to store in the cache
   * @param int $expire Not Used
   * @return bool Always returns true on success, false on failure
   */
  public function set($key, $data, $expire = 0) {
    if(is_object($data)) {
      $data = clone $data;
    }

    $store_data = $data;

    if(is_array($data)) {
      $store_data = new ArrayObject($data);
    }

    $this->cache[$key] = $data;
    $expire = ($expire == 0) ? $this->default_expiration : $expire;

    return $this->_memcache->set($key , $store_data , 0 , $expire);
  }

  /**
   * Echoes the stats of the caching.
   * Gives the cache hits, and cache misses.
   *
   * @since 3.4
   */
  public function stats() {
    echo '<fieldset id="osc-cache-logs" class="osc-cache-memcache" style="border:1px solid #000;line-height:1.4;padding:8px 10px 10px 10px;margin: 12px;width:calc(100% - 24px);background-color:#fff;">' . PHP_EOL;
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
   *
   * @since  3.4.0
   *
   * @access protected
   *
   * @param $key
   *
   * @return bool
   */
  protected function _exists($key) {
    return isset($this->cache[ $key ]);
  }

  /**
   * Sets up object properties; PHP 5 style constructor
   *
   * @since 3.4
   */
  public function __construct() {
    $cache_server = array();
    global $_cache_config;
    
    if(!isset($_cache_config) && !is_array($_cache_config)) {
      $_t['hostname'] = $this->_memcache_conf['default']['default_host'];
      $_t['port'] = $this->_memcache_conf['default']['default_port'];
      $_t['weight'] = $this->_memcache_conf['default']['default_weight'];
      $cache_server[] = $_t;
      
    } else {
      foreach($_cache_config as $_server) {
        $_array = array(
          'hostname' => $_server['default_host'],
          'port' => $_server['default_port'],
          'weight' => $_server['default_weight']
        );
        
        $cache_server[] = $_array;
      }
    }

    $this->_memcache = new Memcache();
    foreach($cache_server as $_config) {
      $this->_memcache->addServer($_config['hostname'], $_config['port'], $_config['weight']);
    }
  }

  /**
   * is_supported()
   *
   * Check to see if Memcache is available on this system, bail if it isn't.
   */
  public static function is_supported() {
    if(!class_exists('Memcache')) {
      error_log('The Memcache Extension must be loaded to use Memcache Cache.');
      return false;
    }
    
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
    return 'memcache';
  }
}