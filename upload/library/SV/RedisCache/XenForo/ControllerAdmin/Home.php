<?php

class SV_RedisCache_XenForo_ControllerAdmin_Home extends XFCP_SV_RedisCache_XenForo_ControllerAdmin_Home
{
    public function actionIndex()
    {
        $response = parent::actionIndex();

        if ($response instanceof XenForo_ControllerResponse_View && $cache = XenForo_Application::getCache())
        {
            $registry = $this->getModelFromCache('XenForo_Model_DataRegistry');
            if (method_exists($registry, 'getCredis') && $credis = $registry->getCredis($cache))
            {
                $data = $credis->info();
                if (!empty($data))
                {
                    $config = XenForo_Application::getConfig()->toArray();
                    $database = 0;
                    if (!empty($config['cache']['backendOptions']['database']))
                    {
                        $database = (int)$config['cache']['backendOptions']['database'];
                    }
                    $db = array();
                    foreach($data as $key => &$value)
                    {
                        if (preg_match('/^db(\d+)$/i',$key, $matches))
                        {
                            $index = $matches[1];
                            unset($data[$key]);
                            $list = explode(',', $value);
                            $dbstats = array();
                            foreach($list as $item)
                            {
                                $parts = explode('=', $item);
                                $dbstats[$parts[0]] = $parts[1];
                            }

                            $db[$index] = $dbstats;
                        }
                    }
                    $data['db'] = $db;
                    $data['db_default'] = $database;
                }
                $response->params['redis'] = $data;
            }
        }

        return $response;
    }
}
