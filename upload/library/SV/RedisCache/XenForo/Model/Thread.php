<?php

class SV_RedisCache_XenForo_Model_Thread extends XFCP_SV_RedisCache_XenForo_Model_Thread
{
    public function countThreadsInForum($forumId, array $conditions = array())
    {
        $options = XenForo_Application::getOptions();
        if ($options->sv_threadcount_caching && $cache = $this->_getCache(true))
        {
            static $cachePrefix = null;
            if ($cachePrefix === null)
            {
                $cachePrefix = XenForo_Application::get('options')->cachePrefix;
            }
            $key = $cachePrefix . 'forum_' . $forumId . '_threadcount_' . md5(serialize($conditions));

            $cacheData = $cache->load($key);
            if ($cacheData !== false)
            {
                return unserialize($cacheData);
            }

            $data = parent::countThreadsInForum($forumId, $conditions);

            if ($data !== false)
            {
                $cache->save(serialize($data), $key, array(), $data <= $options->sv_threadcount_short * $options->discussionsPerPage ? $options->sv_threadcountcache_short : $options->sv_threadcountcache_long);
            }
            else
            {
                $cache->remove($key);
            }
            return $data;
        }

        return parent::countThreadsInForum($forumId, $conditions);
    }
}