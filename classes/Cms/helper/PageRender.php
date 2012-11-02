<?php

class Cms_PageRenderHelper extends App_ViewHelper_Abstract
{
    /**
     * show contents of the page with meta data
     * @param string $strSlug
     */
    public function pageRender( $strSlug, $strLang = '' )
    {
        $objPage = null;
        if ( $strSlug == '' ) {
            $objPage = $this->getView()->object;
        } else {
            $objPage = Cms_Page::Table()->findBySlug( $strSlug, $strLang);
        }
        if ( is_object( $objPage ) ) {
            $this->getView()->broker()->HeadTitle()->set( $objPage->getMetaTitle() );
            $this->getView()->broker()->HeadMeta()->addName( 'meta-keywords',    $objPage->getMetaKeys() );
            $this->getView()->broker()->HeadMeta()->addName( 'meta-description', $objPage->getMetaDescr() );
            return $objPage->getContent();
        } else {
            throw new Cms_Page_Exception('page not found');
        }

    }
}