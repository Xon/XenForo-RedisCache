<?php

if (!class_exists('XenForo_Model_DataRegistry',false))
{
    require(XenForo_Application::getInstance()->getConfigDir().'/SV/RedisCache/RedisDataRegistry.php');
}

