<?php

class SV_RedisCache_XenForo_SvgOutput extends XFCP_SV_RedisCache_XenForo_SvgOutput
{
    public function getCacheId()
    {
        return 'xfSvgCache' .
            '_style_' . $this->_styleId .
            '_d_' . $this->_inputModifiedDate .
            '_language_' . $this->_languageId .
            '_debug_' . (XenForo_Application::debugMode() ? 'debug' : '') .
            '_svg_' . $this->_svgRequested
            );
    }
}