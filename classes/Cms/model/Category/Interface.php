<?php

interface Cms_Category_Interface
{
    // public function getClassName()
    public function getTypeId();
    public function getSortOrder();
    public function getParentId();
    public function getParent();
    public function getSortIndex();

}