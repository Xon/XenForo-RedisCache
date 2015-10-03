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
                $response->params['redis'] = $credis->info();
                if ($response->params['redis'])
                {
                    $list = explode(',', $response->params['redis']['db0']);
                    $dbstats = array();
                    foreach($list as $item)
                    {
                        $parts = explode('=', $item);
                        $dbstats[$parts[0]] = $parts[1];
                    }
                    $response->params['redis']['db0'] = $dbstats;
                }
            }
        }

        return $response;
    }
}
