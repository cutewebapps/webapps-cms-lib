<?php

interface Cms_Page_Interface
{
    /** @return int */
    public function getPageId();

    /** @return Cms_Page */
    public function getPage();
}