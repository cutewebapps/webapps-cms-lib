<?php

interface Cms_Category_Relation_Interface
{
    public function getCategoryId();
    public function getCategory();
    public function getPageId();
    public function getPage();
    public function getSortOrder();
    public function isActive();
}