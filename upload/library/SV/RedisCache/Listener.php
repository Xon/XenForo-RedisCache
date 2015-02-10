<?php

class SV_RedisCache_Listener
{
    const AddonNameSpace = 'SV_RedisCache';
    
    public static function load_class($class, array &$extend)
    {
        switch ($class)
        {
            case 'XenForo_Model_Style':
            case 'XenForo_CssOutput':
                $extend[] = self::AddonNameSpace.'_'.$class;
                break;
        }
    }
}