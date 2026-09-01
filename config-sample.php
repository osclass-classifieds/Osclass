<?php
// MySql database host
define('DB_HOST', 'localhost');

// MySql database username
define('DB_USER', 'username');

// MySql database password
define('DB_PASSWORD', 'password');

// MySql database name
define('DB_NAME', 'database_name');

// MySql database table prefix
define('DB_TABLE_PREFIX', 'oc_');

// Relative web url
define('REL_WEB_URL', 'rel_here');

// Web address - modify here for SSL version of site
define('WEB_PATH', 'http://localhost');


// *************************************** //
// ** OPTIONAL CONFIGURATION PARAMETERS ** //
// *************************************** //

// Recommended php.ini settings to keep php sessions and cookies lifetime long
// ini_set('session.cookie_lifetime', 94608000);
// ini_set('session.gc_maxlifetime', 94608000);

// Enable debugging
// define('OSC_DEBUG', true);             // show PHP error logs and notices
// define('OSC_DEBUG_DB', true);          // show DB queries
// define('OSC_DEBUG_LOG', true);         // save PHP errors & logs to oc-content/debug.log
// define('OSC_DEBUG_DB_LOG', true);      // save DB logs into oc-content/queries.log
// define('OSC_DEBUG_DB_EXPLAIN', true);  // save DB explain logs into oc-content/explain_queries.log
// define('OSC_DEBUG_CACHE', true);       // show cache debug information, when cache is enabled
// define('OSC_DEBUG_DB_AJAX_PRINT', true);  // print errors on ajax calls


// Change backoffice folder (after re-naming /oc-admin/ folder)
// define('OC_ADMIN_FOLDER', 'oc-admin');


// Demo mode
//define('DEMO', true);
//define('DEMO_THEMES', true);
//define('DEMO_PLUGINS', true);


// PHP memory limit (ideally should be more than 128MB)
// define('OSC_MEMORY_LIMIT', '256M');

// Debug PHP mailer. OSC_DEBUG must be true. Error logs from PHPMailer goes to oc-content/debug.log automatically.
// Accepted debug level values:  1 - client only, 2 - client and server (default), 3 - client, server and connection, 4 - low-level information
// define('PHPMAILER_DEBUG_LEVEL', 1);

// Set cookies domain to transfer cookies & session across domain & subdomains. Enter 'yourdomain.com' to transfer cookies to subdomains.
// Only use when osclass subdomains are enabled.
// After setting COOKIE_DOMAIN, clean cache, cookies and restart browser!
// define('COOKIE_DOMAIN', 'yoursite.com');


// Cache options for OSC_CACHE: memcache, memcached, apc, apcu, default
// Default cache means dummy one - just imitates cache
// define('OSC_CACHE_TTL', 60);   // Cache refresh time in seconds

// MemCache caching option (database queries cache). Select only one $_cache_config option, TCP or Unix socket
// define('OSC_CACHE', 'memcache');
// $_cache_config[] = array('default_host' => '127.0.0.1', 'default_port' => 11211, 'default_weight' => 1);  // TCP option
// $_cache_config[] = array('default_host' => '/usr/local/var/run/memcache.sock', 'default_port' => 0, 'default_weight' => 1);  // Unix socket option

// MemCached caching option (database queries cache). Select only one $_cache_config option, TCP or Unix socket
// define('OSC_CACHE', 'memcached');
// $_cache_config[] = array('default_host' => '127.0.0.1', 'default_port' => 11211, 'default_weight' => 1);  // TCP option
// $_cache_config[] = array('default_host' => '/usr/local/var/run/memcached.sock', 'default_port' => 0, 'default_weight' => 1);  // Unix socket option

// Redis caching option (database queries cache). Only one $_cache_config option supported, TCP or Unix socket
// define('OSC_CACHE', 'redis');
// $_cache_config[] = array('default_host' => '127.0.0.1', 'default_port' => 6379, 'default_password' => '');  // TCP option
// $_cache_config[] = array('default_host' => '/usr/local/var/run/redis.sock', 'default_port' => -1, 'default_password' => '');  // Unix socket option


// Force disable URL encoding for non-latin characters
// define('OSC_FORCE_DISABLE_URL_ENCODING', true);

// Alpha & beta testing - experimental
// define('ALPHA_TEST', true);
// define('BETA_TEST', true);

// Increase default login time for user
// session_set_cookie_params(2592000);
// ini_set('session.gc_maxlifetime', 2592000);

// Enable Countries, Regions, Cities and Categories to be pre-loaded into PHP session. Recommended if there is just few hundreds of records
// define('OPTIMIZE_CATEGORIES', false);
// define('OPTIMIZE_CATEGORIES_LIMIT', 1000);
// define('OPTIMIZE_CITIES', false);
// define('OPTIMIZE_CITIES_LIMIT', 5000);
// define('OPTIMIZE_REGIONS', false);
// define('OPTIMIZE_REGIONS_LIMIT', 2000);
// define('OPTIMIZE_COUNTRIES', false);

// Disale tracing of user url history, so after login user is redirected back to original url before login
// define('DISABLE_URL_HISTORY', false);

// Premium search items randomization - higher value ignore effect of premium views. Default 2 means summarized premium views are divided by 2*1000, ceiled and sorted
// define('SEARCH_PREMIUM_RAND_PARAM', 2);

// Enable identification of custom route parameters at the end of url like: ?param1=xyz&param2=abc&...
// define('ROUTE_CUSTOM_QUERY_PARAMS', true);

// Support route super-param sParams in form: sParams=param1,value1/param2,value2/... where single params are exploded to it's values
// define('ROUTE_EXPLODE_SPARAMS', true);
// define('ROUTE_EXPLODE_SPARAMS_DELIMITER', ',');

// Import sql (plugin/theme installation) automatically change engine, charset and collate to expected ones
// define('IMPORTSQL_FORCE_ENGINE', 'InnoDB');
// define('IMPORTSQL_FORCE_CHARSET', 'utf8mb4');
// define('IMPORTSQL_FORCE_COLLATE', 'utf8mb4_unicode_ci');

// Upgrade - do just soft utf8mb4 upgrade
// define('UPGRADE_UTF8MB4_SOFT', true);


