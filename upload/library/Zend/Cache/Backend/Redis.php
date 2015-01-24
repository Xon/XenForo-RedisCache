<?php

require('Redis/lib/Credis/Client.php');
//require('Redis/lib/Credis/Cluster.php');
//require('Redis/lib/Credis/Sentinel.php');
require('Redis/Cm/Cache/Backend/Redis.php');
class Zend_Cache_Backend_Redis extends Cm_Cache_Backend_Redis
{

    function getCredis()
    {
        return $this->_redis;
    }
}