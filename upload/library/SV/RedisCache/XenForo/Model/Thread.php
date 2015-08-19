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
                if ($conditionsSimplified['moderated'] !== true && !$options->sv_threadcount_moderated)
                {
                    // do not count moderated threads
                    $conditionsSimplified['moderated'] = -1;
                }
            }

            static $cachePrefix = null;
            if ($cachePrefix === null)
            {
                $cachePrefix = XenForo_Application::get('options')->cachePrefix;
            }
            $key = $cachePrefix . 'forum_' . $forumId . '_threadcount_' . md5(serialize($conditionsSimplified));

            if (method_exists($registry, 'getCredis'))
            {
                $cache = $this->_getCache(true);
                $credis = $registry->getCredis($cache);

                $cacheData = $credis->get($key);
                if ($cacheData !== false)
                {
                    return intval($cacheData);
                }

                $data = parent::countThreadsInForum($forumId, $conditionsSimplified);

                $expiry = $data <= $options->sv_threadcount_short * $options->discussionsPerPage ? $options->sv_threadcountcache_short : $options->sv_threadcountcache_long;
                $credis->set($key, $data, intcal($expiry));
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