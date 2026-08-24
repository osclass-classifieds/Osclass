<?php
/**
 * Object_Cache_redis class
 */
class Object_Cache_redis implements iObject_Cache {
  /**
   * Holds the redis object
   *
   * @var Redis
   * @access private
   * @since 8.1, rewritten in 8.3.2
   */
  private $_redis;

  protected $_redis_conf = array(
    'default' => array(
      'default_host' => '127.0.0.1',
      'default_port' => 6379,
      'default_password' => '',
      'default_database' => 0
    )
  );

  public $cache = array();

  /**
   * The amount of times the cache data was already stored in the cache.
   *
   * @since 8.1
   * @access private
   * @var int
   */
  public $cache_hits = 0;

  /**
   * Amount of times the cache did not have the request in cache
   *
   * @var int
   * @access public
   * @since 8.1
   */
  public $cache_misses = 0;

  /**
   * Default expiration in seconds
   *
   * @var int
   * @access public
   * @since 8.1
   */
  public $default_expiration = 60;

  /**
   * Adds data to the cache (will overwrite existing key).
   * @since 8.1
   *
   * @param int|string $key What to call the contents in the cache
   * @param mixed $data The contents to store in the cache
   * @param int $expire When to expire the cache contents
   * @return bool True on success, false on failure
   */
  public function add($key, $data, $expire = 0) {
    if(is_object($data)) {
      $data = clone $data;
    }

    $store_data = $data;
    if(is_array($data)) {
      $store_data = new ArrayObject($data);
    }

    $expire = ($expire == 0) ? $this->default_expiration : $expire;

    // Always write (overwrite if exists) as requested
    $result = $this->_redis->setex($key, $expire, serialize(array($store_data, time(), $expire)));

    if($result) {
      $this->cache[$key] = $data;
    }

    return (bool)$result;
  }

  /**
   * Remove the contents of the cache key
   * @since 8.1
   *
   * @param int|string $key What the contents in the cache are called
   * @return bool False if the contents weren't deleted and true on success
   */
  public function delete($key) {
    $result = (bool)$this->_redis->del($key);

    if($result) {
      unset($this->cache[$key]);
    }

    return $result;
  }

  /**
   * Clears the object cache of all data
   * @since 8.1
   *
   * @return bool Always returns true on success
   */
  public function flush() {
    $this->cache = array();
    return $this->_redis->flushDB(); // flush only selected database
  }

  /**
   * Retrieves the cache contents, if it exists
   * @since 8.1
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
      $value = is_object($this->cache[$key]) ? clone $this->cache[$key] : $this->cache[$key];
      ++$this->cache_hits;
      return $value;
    }

    $raw = $this->_redis->get($key);
    if($raw === false) {
      ++$this->cache_misses;
      return false;
    }

    $unserialized = @unserialize($raw);
    if(is_array($unserialized) && isset($unserialized[0])) {
      $value = $unserialized[0];
    } else {
      $value = $unserialized;
    }

    if(is_object($value) && 'ArrayObject' === get_class($value)) {
      $value = $value->getArrayCopy();
    }

    $this->cache[$key] = is_object($value) ? clone $value : $value;
    $found = true;
    ++$this->cache_hits;

    return $this->cache[$key];
  }

  /**
   * Sets the data contents into the cache
   * @since 8.1
   *
   * @param int|string $key What to call the contents in the cache
   * @param mixed $data The contents to store in the cache
   * @param int $expire When to expire the cache contents
   * @return bool True on success, false on failure
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

    return (bool)$this->_redis->setex($key, $expire, serialize($store_data));
  }

  /**
   * Echoes the stats of the caching.
   *
   * @since 8.1
   */
  public function stats() {
    echo '<fieldset id="osc-cache-logs" class="osc-cache-redis" style="border:1px solid #000;line-height:1.4;padding:8px 10px 10px 10px;margin: 12px;width:calc(100% - 24px);background-color:#fff;">' . PHP_EOL;
    echo '<legend style="font-size:14px;font-weight:600;padding:4px 8px;border:1px solid #000;background:#fff;">' . ucwords($this->_get_cache()) . ' stats (Cache hits: ' . $this->cache_hits .' - Cache misses: ' . $this->cache_misses . ')</legend>' . PHP_EOL;
    echo '<table style="border-collapse: collapse;width:100%;font-size:13px;padding:0;border-spacing:0;font-family:monospace;line-height:1.4;">' . PHP_EOL;
    if (count($this->cache) == 0) {
      echo '<tr><td>No cache entries</td></tr>' . PHP_EOL;
    } else {
      foreach ($this->cache as $key => $data) {
        echo '<tr>' . PHP_EOL;
        echo '<td style="padding:6px 8px;text-align:left;vertical-align:top;border: 1px solid #ccc;min-width:100px;">' . $key . '</td>' . PHP_EOL;
        echo '<td style="padding:6px 8px;text-align:left;vertical-align:top;border: 1px solid #ccc;">' . json_encode($data) . '</td>' . PHP_EOL;
        echo '</tr>' . PHP_EOL;
      }
    }
    echo '</table>' . PHP_EOL;
    echo '</fieldset>' . PHP_EOL;
  }

  /**
   * Utility function to determine whether a key exists in the cache (local only).
   *
   * @since  8.1.0
   *
   * @access protected
   *
   * @param $key
   *
   * @return bool
   */
  protected function _exists($key) {
    return isset($this->cache[$key]);
  }

  /**
   * Sets up object properties; PHP 5 style constructor
   *
   * @since 8.1
   */
  public function __construct() {
    $cache_server = array();
    global $_cache_config;

    if(!isset($_cache_config) || !is_array($_cache_config)) {
      $_t['hostname'] = $this->_redis_conf['default']['default_host'];
      $_t['port'] = $this->_redis_conf['default']['default_port'];
      $_t['password'] = $this->_redis_conf['default']['default_password'];
      $_t['database'] = $this->_redis_conf['default']['default_database'];
      $cache_server[] = $_t;
    } else {
      foreach($_cache_config as $_server) {
        $_array = array(
          'hostname' => $_server['default_host'],
          'port' => $_server['default_port'],
          'password' => (isset($_server['default_password']) ? $_server['default_password'] : ''),
          'database' => (isset($_server['default_database']) ? $_server['default_database'] : 0)
        );
        $cache_server[] = $_array;
      }
    }

    $this->_redis = new Redis();
    foreach($cache_server as $_config) {
      try {
        $this->_redis->connect($_config['hostname'], $_config['port']);
        if($_config['password'] != '') {
          $this->_redis->auth($_config['password']);
        }
        $this->_redis->select($_config['database']);
      } catch (RedisException $e) {
        error_log('Redis connection failed: ' . $e->getMessage());
      }
      break; // only first server used
    }
  }

  /**
   * is_supported()
   *
   * Check to see if redis is available on this system, bail if it isn't.
   */
  public static function is_supported() {
    if(!class_exists('Redis')) {
      error_log('The Redis Extension must be loaded to use Redis Cache.');
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
    return 'redis';
  }
}
