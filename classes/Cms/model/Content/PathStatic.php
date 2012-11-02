<?php


class Cms_Content_PathStatic implements Cms_Content_Filter
{
    protected $_strContents;
    protected $_strCdnHttp;

    function __construct( $strContents )
    {
        $this->_strContents = $strContents;
        return $this;
    }

    function setCdn( $strCdnName )
    {
        $this->_strCdnHttp = App_Application::getInstance()->getConfig()->cdn->$strCdnName->http;
        return $this;
    }

    /** @return string */
    function getResult() {
	$c = $this->_strContents;
        
        return str_replace( '%%PathStatic%%', $this->_strCdnHttp, $c );
    }
}