<?php

class Cms_CategoryCtrl extends App_DbTableCtrl
{

    protected function _filterList()
    {
        parent::_filterList();
        $this->_select->joinInner( Cms_Page::TableName(), 'pg_id = cat_page_id' );
        $this->_selectCount->joinInner( Cms_Page::TableName(), 'pg_id = cat_page_id' );
        $this->_select->order( 'cat_sort_order');
    }

    public function getlistAction()
    {
        parent::getlistAction();
    }

    public function getAction()
    {
        if ( $this->_getParam( 'pg_slug' ) ) {

            $select = Cms_Category::Table()->select()
                        ->setIntegrityCheck( false )
                        ->from( Cms_Category::TableName() )
                        ->joinInner( Cms_Page::TableName(), 'pg_id = cat_page_id' )
                        ->where( 'pg_slug = ?', $this->_getParam('pg_slug') );
            if ( $this->_getParam( 'pg_lang' ) )
                $select->where( 'pg_lang = ?', $this->_getParam('pg_lang') );

            $this->view->object = Cms_Category::Table()->fetchRow( $select );

        } else {
            parent::getAction();
        }

        if ( !is_object( $this->view->object )  )
            throw new App_PageNotFound_Exception ( 'Page Not Found' );

        // page can have subpages, pages for comments, pages for images, etc ...
        $this->view->page = $this->_getParam( 'page', 1 );
    }

    public function newAction()
    {
        // Sys_Debug:: dump( $this->_getAllParams() );

        $objPage = Cms_Page::Table()->createRow();
        $objPage->pg_slug       = $this->_getParam( 'cat_slug' );
        $objPage->pg_title      = $this->_getParam( 'cat_name' );
        $objPage->pg_meta_title = $this->_getParam( 'cat_name' );
        $objPage->pg_created    = date('Y-m-d H:i:s');
        $objPage->pg_type_id    = $this->_getIntParam( 'pg_type_id' );
        $objPage->pg_published  = 1;
        $objPage->save();

        $objCategory = Cms_Category::Table()->createRow();
        $objCategory->cat_parent_id = $this->_getIntParam( 'cat_parent_id' );
        $objCategory->cat_type_id = $this->_getIntParam( 'cat_type_id' );
        $objCategory->cat_page_id = $objPage->getId();
        $objCategory->save();

        $this->view->page = $objPage;
        $this->view->object = $objCategory;
    }
}