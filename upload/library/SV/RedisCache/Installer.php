<?php

if (!class_exists('XenForo_Model_DataRegistry',false))
{
    require(XenForo_Application::getConfigDir().'/SV/RedisCache/RedisDataRegistry.php');
}

