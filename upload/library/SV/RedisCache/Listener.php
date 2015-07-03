<?php

class SV_RedisCache_Listener
{
    const AddonNameSpace = 'SV_RedisCache';

    public static function load_class($class, array &$extend)
    {
        $extend[] = self::AddonNameSpace.'_'.$class;
    }
}