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
        $cachePattern = "xfCssCache_style_";
        if ($style_id)
        {
            $cachePattern .= $style_id . "_";
        }
        $cachePattern .= "*";
        
        $registry = $this->getModelFromCache('XenForo_Model_DataRegistry');
        if (method_exists($registry, 'deleteMulti'))
        {
            $registry->deleteMulti($cachePattern);
        }
    }
}