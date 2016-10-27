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
$config['cache']['backendOptions']['sentinel_master_set'] = 'mymaster';
'server' now points to a comma delimited list of sentinal servers to find the master

Licensing:

New BSD License:
- Cm_Cache_Backend_Redis
- Credis

Unlicense:
- XenForo Addon code
