<?php

class Cms_ContentFilterHelper extends App_ViewHelper_Abstract
{
    /**
     * show contents of the page with meta data
     * @param string $strClass
     * @param string $strContents
     */
    public function contentFilter( $strClass, $strContents )
    {
        if ( class_exists( $strClass ) ) {
            $objFilter = new $strClass( $strContents );
            if ( !$objFilter instanceof Cms_Content_Filter ) {
                throw new App_Exception( $strClass.' class was not inherited from Cms_Content_Filter' );
            }
            return $objFilter;
        } else {
            throw new App_Exception( $strClass.' class was not defined to be used as content filter' );
        }
        return '';
    }
}