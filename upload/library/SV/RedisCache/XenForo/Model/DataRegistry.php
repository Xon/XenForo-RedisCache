<?php

class SV_RedisCache_XenForo_Model_DataRegistry extends XFCP_SV_RedisCache_XenForo_Model_DataRegistry
{
    public function __construct()
    {
        parent::__construct();
        if ($cache = $this->_getCache(true))
        {
            $this->DisableLoadingFromSlave($cache, class_exists('XenForo_Dependencies_Public', false));
        }
    }

    /**
     * @param Zend_Cache_Core $cache
     * @param boolean $allowSlaveLoad
     * @return bool
     */
    public function DisableLoadingFromSlave($cache, $allowSlaveLoad)
    {
        if (is_callable('parent::DisableLoadingFromSlave'))
        {
            return parent::DisableLoadingFromSlave($cache, $allowSlaveLoad);
        }
        if (!$allowSlaveLoad)
        {
            $cacheBackend = $cache->getBackend();
            if (method_exists($cacheBackend, 'setSlaveCredis'))
            {
                $cacheBackend->setSlaveCredis(null);
                return true;
            }
        }
        return false;
    }

    /**
     * @param Zend_Cache_Core $cache
     * @param bool            $allowSlave
     * @return Credis_Client|null
     */
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

    /**
     * @param Zend_Cache_Core $cache
     * @return bool|null
     */
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
