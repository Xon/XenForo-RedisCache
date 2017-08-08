<?php

require('Redis/lib/Credis/Client.php');
//require('Redis/lib/Credis/Cluster.php');
require('Redis/lib/Credis/Sentinel.php');
require('Redis/Cm/Cache/Backend/Redis.php');
class Zend_Cache_Backend_Redis extends Cm_Cache_Backend_Redis
{
    protected $enableTags = false;

    public function __construct($options = array())
    {
        if (!isset($options['slave_select_callable']))
        {
            $options['slave_select_callable'] = array($this, 'preferLocalSlave');
        }
        // if it is a string, assume it is some method on this class
        if (isset($options['slave_select_callable']) && is_string($options['slave_select_callable']))
        {
            $options['slave_select_callable'] = array($this, $options['slave_select_callable']);
        }
        if (isset($options['enable_tags']))
        {
            $this->enableTags = $options['enable_tags'];
        }
        parent::__construct($options);
    }

    const LUA_SAVE_NOTAGS_SH1 = 'a7928cc661fdc42d01388a85371c057f3d995d87';

    public function save($data, $id, $tags = array(), $specificLifetime = false)
    {
        if ($this->enableTags) {
            return parent::save($data, $id, $tags, $specificLifetime);
        }

        $tags = array();
        $lifetime = $this->_getAutoExpiringLifetime($this->getLifetime($specificLifetime), $id);

        if ($this->_useLua) {
            $sArgs = array(
                self::PREFIX_KEY,
                self::FIELD_DATA,
                self::FIELD_TAGS,
                self::FIELD_MTIME,
                self::FIELD_INF,
                self::SET_TAGS,
                self::PREFIX_TAG_IDS,
                self::SET_IDS,
                $id,
                $this->_encodeData($data, $this->_compressData),
                '',
                time(),
                $lifetime ? 0 : 1,
                min($lifetime, self::MAX_LIFETIME),
                $this->_notMatchingTags ? 1 : 0
            );

            $res = $this->_redis->evalSha(self::LUA_SAVE_NOTAGS_SH1, $tags, $sArgs);
            if (is_null($res)) {
                $script =
                    "redis.call('HMSET', ARGV[1]..ARGV[9], ARGV[2], ARGV[10], ARGV[3], ARGV[11], ARGV[4], ARGV[12], ARGV[5], ARGV[13]) ".
                    "if (ARGV[13] == '0') then ".
                        "redis.call('EXPIRE', ARGV[1]..ARGV[9], ARGV[14]) ".
                    "end ".
                    "return '' ";
                $res = $this->_redis->eval($script, $tags, $sArgs);
            }

            // Process removed tags if cache entry already existed
            if ($res) {
                $oldTags = explode(',', $this->_decodeData($res));
                if ($remTags = ($oldTags ? array_diff($oldTags, $tags) : FALSE))
                {
                    // Update the id list for each tag
                    foreach($remTags as $tag)
                    {
                        $this->_redis->sRem(self::PREFIX_TAG_IDS . $tag, $id);
                    }
                }
            }

            return TRUE;
        }

        if ($lifetime) {
            $this->_redis->pipeline()->multi();
        }

        // Set the data
        $result = $this->_redis->hMSet(self::PREFIX_KEY.$id, array(
          self::FIELD_DATA => $this->_encodeData($data, $this->_compressData),
          self::FIELD_TAGS => '',
          self::FIELD_MTIME => time(),
          self::FIELD_INF => $lifetime ? 0 : 1,
        ));
        if( ! $result) {
            throw new CredisException("Could not set cache key $id");
        }

        // Set expiration if specified
        if ($lifetime) {
          $this->_redis->expire(self::PREFIX_KEY.$id, min($lifetime, self::MAX_LIFETIME));
          $this->_redis->exec();
        }

        return TRUE;
    }

    protected function getLocalIps(array $ips = null)
    {
        if (!is_array($ips))
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
            }
        }
        return $ips;
    }

    protected function selectLocalRedis(array $ips = null, array $slaves, $master)
    {
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

    public function preferLocalSlave(array $slaves, $master)
    {
        $ips = $this->getLocalIps();
        return $this->selectLocalRedis($ips, $slaves, $master);
    }

    protected function preferLocalSlaveLocalDisk(array $slaves, $master)
    {
        $output = @file_get_contents('/tmp/local_ips');
        if ($output === false)
        {
            try
            {
                $output = shell_exec("hostname --all-ip-addresses");
            }
            catch(Exception $e) { $output = ''; }
            if ($output !== false)
            {
                file_put_contents('/tmp/local_ips', $output);
            }
        }

        $ips = null;
        if ($output)
        {
            $ips = array_fill_keys(array_filter(array_map('trim', (explode(' ', $output)))), true);
        }
        return $this->selectLocalRedis($ips, $slaves, $master);
    }

    public function preferLocalSlaveAPCu(array $slaves, $master)
    {
        $ips = null;
        if (function_exists('apcu_fetch'))
        {
            $ips = apcu_fetch('localips', $hasIps);
        }
        if (!is_array($ips))
        {
            $ips = $this->getLocalIps();
            if (function_exists('apcu_store'))
            {
                // bit racing on the first connection, but local IPs rarely change.
                apcu_store('localips', $ips);
            }
        }
        return $this->selectLocalRedis($ips, $slaves, $master);
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

    public function getSlaveCredis()
    {
        return $this->_slave;
    }

    public function setSlaveCredis($slave)
    {
        $this->_slave = $slave;
    }

    public function useLua()
    {
        return $this->_useLua;
    }
}
