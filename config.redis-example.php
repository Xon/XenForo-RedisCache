<?php
// setup redis caching
$config['cache']['backend'] = 'Redis';
$config['cache']['backendOptions'] = array(
        'server' => '127.0.0.1',
        'port' => 6379,
        );
// install Redis-aware XF Caching replacement. Will break in XF 2.0
require(XenForo_Application::getConfigDir().'/SV/RedisCache/Installer.php');        
        
// $redis = XenForo_Application::getCache()->getBackend()->getCredis();        