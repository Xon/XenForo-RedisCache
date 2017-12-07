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
                /** @var Credis_Client $credis */
                $useLua = method_exists($registry, 'useLua') && $registry->useLua($cache);
                /** @var SV_RedisCache_ControllerHelper_Redis $helper */
                $helper = $this->getHelper('SV_RedisCache_ControllerHelper_Redis');
                $helper->addRedisInfo($response, $credis->info(), $useLua);
            }
        }

        return $response;
    }
}
