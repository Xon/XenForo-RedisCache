<?php

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