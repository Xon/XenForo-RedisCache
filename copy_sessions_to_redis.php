<?php

$startTime = microtime(true);
$fileDir = dirname(__FILE__) . '/html';

@set_time_limit(0);
ignore_user_abort(true);

require($fileDir . '/library/XenForo/Autoloader.php');
XenForo_Autoloader::getInstance()->setupAutoloader($fileDir . '/library');

XenForo_Application::initialize($fileDir . '/library', $fileDir);
XenForo_Application::set('page_start_time', $startTime);

$dependencies = new XenForo_Dependencies_Public();
$dependencies->preLoadData();

$db = XenForo_Application::get('db');

$cache = XenForo_Application::getCache();
if (empty($cache))
{
  echo "no cache object\n";
  return;
}

$sessions = $db->fetchAll("
select *
from xf_session;
");

foreach($sessions as $session)
{
    $cache->save(
        serialize($session['session_data']),
        'session_' . $session['session_id'],
        array(), XenForo_Application::$time - $session['expiry_date']
    );
}
