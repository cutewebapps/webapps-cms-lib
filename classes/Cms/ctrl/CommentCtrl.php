<?php

class Cms_CommentCtrl extends App_DbTableCtrl
{
//    protected function _filterField( $strField, $strValue )
//    {
//        parent::_filterField( $strField, $strValue );
//    }
    
    protected function _filterList()
    {
        parent::_filterList();
        $this->_select->joinInner( Cms_Page::TableName(), 'pg_id = pgc_page_id' );
        $this->_selectCount->joinInner( Cms_Page::TableName(), 'pg_id = pgc_page_id' );

        $this->_select->order( 'pgc_date DESC');
    }

    public function  getlistAction() {
        parent::getlistAction();
    }

    public function addWordpressAction() {
        // add comments in wordpress-spam format

       $strAuthor  = $this->_getParam( 'author' );
       $strEmail   = $this->_getParam( 'email' );
       $strUrl     = $this->_getParam( 'url' );
       $strComment = $this->_getParam( 'comment' );
       $strPageId  = $this->_getIntParam( 'comment_post_ID' );

       $this->view->page = null;
       if ( $strAuthor != '' && $strComment != '' ) {
          
           $objPage = Cms_Page::Table()->find( $strPageId )->current();
           if ( is_object( $objPage ) && $objPage->getCommentsAllowed() ) {

                $bEnabled = 1;
                if ( strstr( $strComment, 'http://' ) ) $bEnabled = 0;
                else {
                    $selectExistingComment = Cms_Comment::Table()->select()
                            ->where( 'pgc_page_id = ?', $objPage->getId() )
                            ->where( 'pgc_comment = ?', $strComment );
                    $objExistingComment = Cms_Comment::Table()->fetchRow( $selectExistingComment );
                    if ( is_object( $objExistingComment )) $bEnabled = 0;
                }

                $objComment = Cms_Comment::Table()->createRow();
                $objComment->pgc_date = date('Y-m-d H:i:s');
                $objComment->pgc_page_id = $objPage->getId();
                $objComment->pgc_ip = $_SERVER['REMOTE_ADDR'];
                $objComment->pgc_comment = $strComment;
                $objComment->pgc_author_name = $strAuthor;
                $objComment->pgc_author_email = $strEmail;
                $objComment->pgc_author_url   = $strUrl;
                $objComment->pgc_reviewed = 0;
                $objComment->pgc_enabled = $bEnabled;
                $objComment->save();

                $this->view->objComment = $objComment;
           }
           $this->view->page = $objPage;
       }
       // it a normal world, you can get to a page
       $this->view->return = isset( $_SERVER[ 'HTTP_REFERER' ] ) ? $_SERVER[ 'HTTP_REFERER' ] : '';
    }
}