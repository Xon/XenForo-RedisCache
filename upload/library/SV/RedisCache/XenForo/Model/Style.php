<?php

class SV_RedisCache_XenForo_Model_Style extends XFCP_SV_RedisCache_XenForo_Model_Style
{
    public function rebuildStyleCache()
    {
        $styles = parent::rebuildStyleCache();
        $this->styleCachePurge();
        return $styles;
    }

    public function styleCachePurge($style_id = null)
    {
        $registry = $this->_getDataRegistryModel();
        $cache = $this->_getCache(true);
        if (!method_exists($registry, 'getCredis') || !($credis = $registry->getCredis($cache)))
        {
            return;
        }

        static $cachePrefix = null;
        if ($cachePrefix === null)
        {
            $cachePrefix = $cache->getOption('cache_id_prefix');
        }

        $pattern = Cm_Cache_Backend_Redis::PREFIX_KEY . $cachePrefix . "xfCssCache_style_";
        if ($style_id)
        {
            $pattern .= $style_id . "_";
        }
        $pattern .= "*";
        $expiry = 5*60;
        // indicate to the redis instance would like to process X items at a time.
        $count = 1000;
        // find indexes matching the pattern
        $cursor = null;
        $keys = array();
        while(true)
        {
            $next_keys = $credis->scan($cursor, $pattern, $count);
            // scan can return an empty array
            if($next_keys)
            {
                $keys += $next_keys;
            }
            if (empty($cursor) || $next_keys === false)
            {
                break;
            }
        }
        if ($keys)
        {
            // adjust TTL them, use pipe-lining
            $credis->pipeline();
            foreach($keys as $key)
            {
                if ($key)
                {
                    $credis->expire($key, $expiry);
                }
            }
            $credis->exec();
        }
    }
}