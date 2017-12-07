<?php


class SV_RedisCache_ControllerHelper_Redis
{
    public function addRedisInfo(XenForo_ControllerResponse_View $response, array $data, $useLua = true)
    {
        $database = 0;
        $slaves = array();
        $db = array();

        if (!empty($data))
        {
            $config = XenForo_Application::getConfig()->toArray();
            if (!empty($config['cache']['backendOptions']['database']))
            {
                $database = (int)$config['cache']['backendOptions']['database'];
            }

            foreach ($data as $key => &$value)
            {
                if (preg_match('/^db(\d+)$/i', $key, $matches))
                {
                    $index = $matches[1];
                    unset($data[$key]);
                    $list = explode(',', $value);
                    $dbStats = array();
                    foreach ($list as $item)
                    {
                        $parts = explode('=', $item);
                        $dbStats[$parts[0]] = $parts[1];
                    }

                    $db[$index] = $dbStats;
                }
            }
            // got slaves
            if (isset($data['connected_slaves']) && isset($data['master_repl_offset']))
            {
                foreach ($data as $key => &$value)
                {
                    if (preg_match('/^slave(\d+)$/i', $key, $matches))
                    {
                        $index = $matches[1];
                        unset($data[$key]);
                        $list = explode(',', $value);
                        $dbStats = array();
                        foreach ($list as $item)
                        {
                            $parts = explode('=', $item);
                            $dbStats[$parts[0]] = $parts[1];
                        }

                        $slaves[$index] = $dbStats;
                    }
                }
            }
        }

        $data['slaves'] = $slaves;
        $data['db'] = $db;
        $data['db_default'] = $database;
        $data['lua'] = $useLua;
        $data['phpredis'] = phpversion('redis');
        $data['HasIOStats'] = isset($data['instantaneous_input_kbps']) && isset($data['instantaneous_output_kbps']);
        $response->params['redis'] = $data;
    }
}
