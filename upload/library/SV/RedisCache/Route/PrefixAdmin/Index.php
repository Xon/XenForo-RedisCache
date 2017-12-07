<?php


class SV_RedisCache_Route_PrefixAdmin_Index implements XenForo_Route_Interface
{
    public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
    {
        $action = $router->resolveActionWithIntegerParam($routePath, $request, 'slave_id');
        return $router->getRouteMatch('SV_RedisCache_ControllerAdmin_Index', $action, 'setup');
    }
}
