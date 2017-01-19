<?php

class SV_RedisCache_XenForo_Model_DataRegistry extends XFCP_SV_RedisCache_XenForo_Model_DataRegistry
{
    public function getCredis($cache, $allowSlave = false)
    {
        if (empty($cache))
        {
            return null;
        }
        $cacheBackend = $cache->getBackend();
        if (method_exists($cacheBackend, 'getCredis'))
        {
            return $cacheBackend->getCredis($allowSlave);
        }
        return null;
    }

    public function useLua($cache)
    {
        if (empty($cache))
        {
            return null;
        }
        $cacheBackend = $cache->getBackend();
        if (method_exists($cacheBackend, 'useLua'))
        {
            return $cacheBackend->useLua();
        }
        return null;
    }
}