<?php

require('Redis/lib/Credis/Client.php');
//require('Redis/lib/Credis/Cluster.php');
require('Redis/lib/Credis/Sentinel.php');
require('Redis/Cm/Cache/Backend/Redis.php');
class Zend_Cache_Backend_Redis extends Cm_Cache_Backend_Redis
{
    public function __construct($options = array())
    {
        if (!isset($options['slave-select']))
        {
            $options['slave-select'] = array($this, 'preferLocalSlave');
        }
        parent::__construct($options);
    }

    public function preferLocalSlave(array $slaves)
    {
        // I can't believe there isn't a better way
        $output = shell_exec("hostname --all-ip-addresses");
        if ($output)
        {
            $ips = explode(' ', $output);
            /* @var $slave Credis_Client */
            foreach($slaves as $slave)
            {
                // slave host is just an ip
                $host = $slave->getHost();
                if (in_array($host, $ips))
                {
                    return $host;
                }
            }
        }

        $slaveKey = array_rand($slaves, 1);
        return $slaves[$slaveKey];
    }

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