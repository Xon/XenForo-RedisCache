<?php


$config['cache']['backend'] = 'Redis';
$config['cache']['backendOptions'] = array(
        'server' => '127.0.0.1',
        'port' => 6379,
        );
        
        
// $redis = XenForo_Application::getCache()->getBackend()->getCredis();        