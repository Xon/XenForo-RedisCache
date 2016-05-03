<?php

class SV_RedisCache_SV_SVGTemplate_SvgOutput extends XFCP_SV_RedisCache_SV_SVGTemplate_SvgOutput
{
    public function getCacheId()
    {
        return 'xfSvgCache' .
            '_style_' . $this->_styleId .
            '_d_' . $this->_inputModifiedDate .
            '_language_' . $this->_languageId .
            '_debug_' . (XenForo_Application::debugMode() ? '1' : '0') .
            '_svg_' . sha1($this->_svgRequested)
            ;
    }
}