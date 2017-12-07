<?php


class SV_RedisCache_ControllerAdmin_Index extends XenForo_ControllerAdmin_Abstract
{
    public function actionIndex()
    {
        $slaveId = $this->_input->filterSingle('slave_id', XenForo_Input::UINT);

        $view = $this->responseView('XenForo_ViewAdmin_Home_Redis', 'SV_Redis_info_Wrapper', []);

        if ($cache = XenForo_Application::getCache())
        {
            /** @var SV_RedisCache_ControllerHelper_Redis $helper */
            $helper = $this->getHelper('SV_RedisCache_ControllerHelper_Redis');

            $registry = $this->getModelFromCache('XenForo_Model_DataRegistry');
            if (method_exists($registry, 'getCredis') && $credis = $registry->getCredis($cache))
            {
                /** @var Credis_Client $credis */
                $useLua = method_exists($registry, 'useLua') && $registry->useLua($cache);

                $helper->addRedisInfo($view, $credis->info(), $useLua);
                $redisInfo = $view->params['redis'];
                $slaves = $redisInfo['slaves'];

                $config = XenForo_Application::getConfig()->toArray();
                $database = empty($config['cache']['backendOptions']['database']) ? 0 : (int)$config['cache']['backendOptions']['database'];
                $password = empty($config['cache']['backendOptions']['password']) ? null : $config['cache']['backendOptions']['password'];
                $timeout = empty($config['cache']['backendOptions']['timeout']) ? null : $config['cache']['backendOptions']['timeout'];
                $persistent = empty($config['cache']['backendOptions']['persistent']) ? null : $config['cache']['backendOptions']['persistent'];
                $forceStandalone = empty($config['cache']['backendOptions']['force_standalone']) ? null : $config['cache']['backendOptions']['force_standalone'];

                if (isset($slaves[$slaveId]))
                {
                    $slaveDetails = $slaves[$slaveId];
                    // query the slave for stats
                    $slaveClient = new Credis_Client($slaveDetails['ip'], $slaveDetails['port'], $timeout, $persistent, $database, $password);
                    if ($forceStandalone)
                    {
                        $slaveClient->forceStandalone();
                    }
                    $helper->addRedisInfo($view, $slaveClient->info(), $useLua);

                    $view->params['redis']['slaveId'] = $slaveId;

                    return $view;
                }
            }
        }

        return $this->getNotFoundResponse();
    }
}
