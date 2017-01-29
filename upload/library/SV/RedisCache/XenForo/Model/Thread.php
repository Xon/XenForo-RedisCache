<?php

class SV_RedisCache_XenForo_Model_Thread extends XFCP_SV_RedisCache_XenForo_Model_Thread
{
    public function countThreadsInForum($forumId, array $conditions = array())
    {
        $options = XenForo_Application::getOptions();
        if ($options->sv_threadcount_caching && $cache = $this->_getCache(true))
        {
            // simplify the conditions, this will strip-out attributes which do not change
            // the forum thread count
            $conditionsSimplified = $conditions;
            if (!isset($conditionsSimplified['userId']))
            {
                unset($conditionsSimplified['readUserId']);
                unset($conditionsSimplified['watchUserId']);
                unset($conditionsSimplified['postCountUserId']);
                if (isset($conditionsSimplified['moderated']) && $conditionsSimplified['moderated'] !== true && !$options->sv_threadcount_moderated)
                {
                    // do not count moderated threads
                    $conditionsSimplified['moderated'] = -1;
                }
            }

            $forumIdKey = $forumId;
            if (is_array($forumIdKey))
            {
                $forumIdKey = implode('_', $forumId);
            }
            $key = 'forum_' . $forumIdKey . '_threadcount_' . md5(serialize($conditionsSimplified));
            $registry = $this->_getDataRegistryModel();
            $cache = $this->_getCache(true);
            if (method_exists($registry, 'getCredis') && $credis = $registry->getCredis($cache))
            {
                static $cachePrefix = null;
                if ($cachePrefix === null)
                {
                    $cachePrefix = $cache->getOption('cache_id_prefix');
                }
                $key = Cm_Cache_Backend_Redis::PREFIX_KEY . $cachePrefix . 'r_'. $key;

                $cacheData = $credis->get($key);
                if ($cacheData !== false)
                {
                    return intval($cacheData);
                }

                $data = parent::countThreadsInForum($forumId, $conditionsSimplified);

                $expiry = $data <= $options->sv_threadcount_short * $options->discussionsPerPage ? $options->sv_threadcountcache_short : $options->sv_threadcountcache_long;
                $credis->set($key, '' . $data,  intval($expiry));
            }
            else
            {
                $cacheData = $cache->load($key);
                if ($cacheData !== false)
                {
                    $data = @unserialize($cacheData);
                    if ($data !== false)
                    {
                        return $data;
                    }
                }

                $data = parent::countThreadsInForum($forumId, $conditionsSimplified);

                $expiry = $data <= $options->sv_threadcount_short * $options->discussionsPerPage ? $options->sv_threadcountcache_short : $options->sv_threadcountcache_long;
                $cache->save(serialize($data), $key, array(), $expiry);
            }

            return $data;
        }

        return parent::countThreadsInForum($forumId, $conditions);
    }
}