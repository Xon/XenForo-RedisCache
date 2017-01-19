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
        $hasIps = false;
        $ips = array();
        if (function_exists('apcu_fetch'))
        {
            $ips = apcu_fetch('localips', $hasIps);
        }
        if (!$hasIps || !is_array($ips))
        {
            // I can't believe there isn't a better way
            try
            {
                $output = shell_exec("hostname --all-ip-addresses");
            }
            catch(Exception $e) { $output = ''; }
            if ($output)
            {
                $ips = array_fill_keys(array_filter(array_map('trim', (explode(' ', $output)))), true);
                if (function_exists('apcu_store'))
                {
                    // bit racing on the first connection, but local IPs rarely change.
                    apcu_store('localips', $ips);
                }
            }
        }

        if ($ips)
        {
            /* @var $slave Credis_Client */
            foreach($slaves as $slave)
            {
                // slave host is just an ip
                $host = $slave->getHost();
                if (isset($ips[$host]))
                {
                    return $slave;
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