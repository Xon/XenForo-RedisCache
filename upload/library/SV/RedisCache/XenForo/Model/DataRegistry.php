<?php

class SV_RedisCache_XenForo_Model_DataRegistry extends XFCP_SV_RedisCache_XenForo_Model_DataRegistry
{
    public function getCredis($cache)
    {
        if (empty($cache))
        {
            return null;
        }
        $cacheBackend = $cache->getBackend();
        if (method_exists($cacheBackend, 'getCredis'))
        {
            return $cacheBackend->getCredis();
        }
        return null;
    }
}