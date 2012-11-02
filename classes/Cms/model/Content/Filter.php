<?php

interface Cms_Content_Filter
{
    /**
     * @param  string $strContents 
     * @return Cms_Content_Filter
     */
    public function __construct( $strContents );

    /**
     * @return string
     */
    public function getResult();
}