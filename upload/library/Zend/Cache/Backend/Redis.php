<?php

require('Redis/lib/Credis/Client.php');
//require('Redis/lib/Credis/Cluster.php');
require('Redis/lib/Credis/Sentinel.php');
require('Redis/Cm/Cache/Backend/Redis.php');
class Zend_Cache_Backend_Redis extends Cm_Cache_Backend_Redis
{
    public function getCompressThreshold()
    {
        return $this->_compressThreshold;
    }

    public function setCompressThreshold($value)
    {
        $this->_compressThreshold = $value;
    }

    public function DecodeData($data)
    {
        return $this->_decodeData($data);
    }

    public function getCredis($allowSlave = false)
    {
        if ($allowSlave && $this->_slave)
        {
            return $this->_slave;
        }
        return $this->_redis;
    }

    public function useLua()
    {
        return $this->_useLua;
    }
}