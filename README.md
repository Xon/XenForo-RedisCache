# XenForo-Redis-cache
XenForo + Redis + Glue code

This "addon" is the required gluecode to use [Cm_Cache_Backend_Redis](https://github.com/colinmollenhour/Cm_Cache_Backend_Redis) to provide a Zend Cache target for [Redis](http://redis.io/).

Additionally, this addon implement caching of thread counts in a forum.


To prevent double encoding of cached data, it is strongly recommended to have the following configuration in XenForo's config.php:
```
$config['cache']['frontendOptions']['automatic_serialization'] = false;
```
*Warning*
You must manually flush the cache after changing this setting!

Optionally, [(pipelining](http://redis.io/topics/pipelining) or loading from a slave can be enabled in XenForo_Model_DataRegistry::getMulti by adding the following lines to config.php. Preferably after setting up the cache.
```
require(XenForo_Application::getInstance()->getConfigDir().'/SV/RedisCache/Installer.php');
```

For best performance use: [phpredis PECL extension](http://pecl.php.net/package/redis)

Sample Redis configuration for XenForo:
```
$config['cache']['backend'] = 'Redis';
$config['cache']['backendOptions'] = array(
        'server' => '127.0.0.1',
        'port' => 6379,
        );
```

Redis Sentinel support for high-availability. See http://redis.io/topics/sentinel for more information.
Enable with:
```
$config['cache']['backendOptions']['sentinel_master_set'] = 'mymaster';
$config['cache']['backendOptions']['server'] = '127.0.0.1:26379';
```
'server' now points to a comma delimited list of sentinal servers to find the master. Note; the port must be explicitly listed

To load data from slaves use;
```
$config['cache']['backendOptions']['load_from_slaves'] = true;
```

By default, a local slave is preferred, this can be changed by setting:
```
$config['cache']['backendOptions']['slave-select'] = function (array $slaves) { 
        $slaveKey = array_rand($slaves, 1);
        return $slaves[$slaveKey];
};
```
Setting to false (or some non-callable) will fall back to a random slave.

Licensing:

New BSD License:
- Cm_Cache_Backend_Redis
- Credis

MIT Licensed:
- XenForo Addon code
