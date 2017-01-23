<?php

class MyXenForo_Dependencies_Public extends XenForo_Dependencies_Public
{
    public $data;

    protected function _handleCustomPreloadedData(array &$data)
    {
        parent::_handleCustomPreloadedData($data);
        $this->data = $data;
    }
}

class SV_RedisCache_XenForo_CssOutput extends XFCP_SV_RedisCache_XenForo_CssOutput
{
    public function getCacheId()
    {
        return 'xfCssCache' .
            '_style_' . $this->_styleId .
            '_d_' . $this->_inputModifiedDate .
            '_dir_' . $this->_textDirection .
            '_minify_' . (XenForo_Application::get('options')->minifyCss ? '1' : '0') .
            '_debug_' . (XenForo_Application::debugMode() ? '1' : '0') .
            '_css_' . sha1(join("",$this->_cssRequested)) ;
    }

    public function renderCss()
    {
      // re-implement XenForo_CssOutput::renderCss() so we can change how caching works
      $cacheId = $this->getCacheId();

      if ($cacheObject = XenForo_Application::getCache())
      {
          if ($cacheCss = $cacheObject->load($cacheId, true))
          {
              return $cacheCss . "\n/* CSS returned from cache. */";
          }
          // ensure loading from the slave(s) is disabled during a cache-miss operation
          // this ensures critical style properties aren't garbled for some reason when generating css
          $cacheBackend = $cacheObject->getBackend();
          if (method_exists($cacheBackend, 'setSlaveCredis') && method_exists($cacheBackend, 'getSlaveCredis'))
          {
              if ($cacheBackend->getSlaveCredis() !== null)
              {
                  $cacheBackend->setSlaveCredis(null);
                  // prevent init_dependencies from being called twice
                  $config = XenForo_Application::get('config');
                  $enableListeners = $config->enableListeners;
                  $config->enableListeners = false;
                  XenForo_CodeEvent::setListeners(null);
                  // force a reload of state to prevent more-stale data being used.
                  $dependencies = new MyXenForo_Dependencies_Public();
		          $dependencies->preLoadData();
                  // re-populate lisenters from the data we just (re)loaded
                  if ($enableListeners)
                  {
                      $config->enableListeners = true;
                      $data = $dependencies->data;
                      if (!is_array($data['codeEventListeners']))
                      {
                          $data['codeEventListeners'] = XenForo_Model::create('XenForo_Model_CodeEvent')->rebuildEventListenerCache();
                      }
                      XenForo_CodeEvent::setListeners($data['codeEventListeners']);
                  }
              }
          }
      }

      $this->_prepareForOutput();

      if (XenForo_Application::isRegistered('bbCode'))
      {
          $bbCodeCache = XenForo_Application::get('bbCode');
      }
      else
      {
          $bbCodeCache = XenForo_Model::create('XenForo_Model_BbCode')->getBbCodeCache();
      }

      $params = array(
          'displayStyles' => $this->_displayStyles,
          'smilieSprites' => $this->_smilieSprites,
          'customBbCodes' => !empty($bbCodeCache['bbCodes']) ? $bbCodeCache['bbCodes'] : array(),
          'xenOptions' => XenForo_Application::get('options')->getOptions(),
          'dir' => $this->_textDirection,
          'pageIsRtl' => ($this->_textDirection == 'RTL')
      );

      $templates = array();
      foreach ($this->_cssRequested AS $cssName)
      {
          $cssName = trim($cssName);
          if (!$cssName)
          {
              continue;
          }

          $templateName = $cssName . '.css';
          if (!isset($templates[$templateName]))
          {
              $templates[$templateName] = new XenForo_Template_Public($templateName, $params);
          }
      }

      $css = self::renderCssFromObjects($templates, XenForo_Application::debugMode());
      $css = self::prepareCssForOutput(
        $css,
          $this->_textDirection,
          (XenForo_Application::get('options')->minifyCss && $cacheObject)
      );

      if ($cacheObject)
      {
          $cacheObject->save($css, $cacheId, array(), 86400);
      }

      return $css;
    }
}