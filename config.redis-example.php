<?php
// setup redis caching
$config['cache']['backend'] = 'Redis';
// all keys and thier defaults
$config['cache']['backendOptions'] = array(
        'server' => '127.0.0.1',
        'port' => 6379,
        'timeout' => 2.5,
        'persistent' => null,
        'force_standalone' => false,
        'connect_retries' => 1,
        'read_timeout' => null,
        'password' => null,
        'database' => 0,
        'notMatchingTags' => false,
        'compress_tags' => 1,
        'compress_data' => 1,
        'lifetimelimit' => 2592000,
        'compress_threshold' => 20480,
        'automatic_cleaning_factor' => 0,
        'compression_lib' => null, // dynamically select first of; snappy,lzf,l4z,gzip IF EMPTY
        'use_lua' => false,
        'sunion_chunk_size' => 500,
        'lua_max_c_stack' => 5000,
        'enable_tags' => false,
        );
// single slave (has all the details of backendOptions:
$config['cache']['backendOptions']['load_from_slave'] = array(
        'server' => '127.0.0.1',
        'port' => 6378,
        'connect_retries' => 2,
        'use_lua' => true,
        'compress_data' => 2,
        'read_timeout' => 1,
        'timeout' => 1,
        );

// minimal case for sentinel support (aka HA)
$config['cache']['backendOptions']['sentinel_master'] = 'mymaster';
$config['cache']['backendOptions']['server'] = '127.0.0.1:26379';
$config['cache']['backendOptions']['load_from_slaves'] = false; // send readonly queries to the slaves
$config['cache']['backendOptions']['sentinel']['persistent'] = null; // persistent connection option for the sentinel, but not the master/slave
// preferLocalSlave|preferLocalSlaveLocalDisk|preferLocalSlaveAPCu|closure(array $slaves, $master) - how to select which slave to use. Cache to APCu (not recommended) or local disk (/tmp/local_ips)
$config['cache']['backendOptions']['slave_select_callable'] = null; 

// minimal case
$config['cache']['backendOptions'] = array(
        'server' => '127.0.0.1',
        'port' => 6379,
        'connect_retries' => 2,
        'use_lua' => true,
        'compress_data' => 2,
        'read_timeout' => 1,
        'timeout' => 1,
        );
// install Redis-aware XF Caching replacement. Will break in XF 2.0
require(XenForo_Application::getInstance()->getConfigDir().'/SV/RedisCache/Installer.php');        
        
// fetch the master
    // $registry = $this->getModelFromCache('XenForo_Model_DataRegistry');
    // if (method_exists($registry, 'getCredis') && $credis = $registry->getCredis($cache, false)) { ... }
// fetch a slave if possible
    // $registry = $this->getModelFromCache('XenForo_Model_DataRegistry');
    // if (method_exists($registry, 'getCredis') && $credis = $registry->getCredis($cache, true)) { ... }