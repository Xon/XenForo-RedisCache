<?php
// setup redis caching
$config['cache']['backend'] = 'Redis';
// all keys and thier defaults
$config['cache']['backendOptions'] = array(
        'server' => '127.0.0.1',
        'port' => 6379,
        'timeout' => 2.5,
        'persistent' : '',
        'force_standalone' => false,
        'connect_retries' => 1,
        'read_timeout' => '',
        'password' => '',
        'database' => 0,
        'notMatchingTags' => false,
        'compress_tags' => 1,
        'compress_data' => 1,
        'lifetimelimit' => 2592000,
        'compress_threshold' => 20480,
        'automatic_cleaning_factor' => 0,
        'compression_lib' => '', // dynamically select first of; snappy,lzf,l4z,gzip
        'use_lua' => false,
        'sunion_chunk_size' => 500,
        'lua_max_c_stack' => 5000,
        );
// minimal case for sentinel support (aka HA)
$config['cache']['backendOptions']['sentinel_master_set'] = 'mymaster';
$config['cache']['backendOptions']['server'] = '127.0.0.1:26379';
// minimal case
$config['cache']['backendOptions'] = array(
        'server' => '127.0.0.1',
        'port' => 6379,
        );
// install Redis-aware XF Caching replacement. Will break in XF 2.0
require(XenForo_Application::getInstance()->getConfigDir().'/SV/RedisCache/Installer.php');        
        
// $redis = XenForo_Application::getCache()->getBackend()->getCredis();        