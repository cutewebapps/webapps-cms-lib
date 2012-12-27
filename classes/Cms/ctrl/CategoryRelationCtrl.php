<?php

class Cms_CategoryRelationCtrl extends App_DbTableCtrl
{
    public function  getClassName() {
        return 'Cms_Category_Relation';
    }

    //TODO: this function should understand TYPE parameter!!!
    public function updatePageAction()
    {
        // page, and list of category id given
        if ( $this->_hasParam( 'pgcat_page_id' ) ) {

            $nPageId = $this->_getParam( 'pgcat_page_id' );

            $arrCatIds = explode( ',', $this->_getParam( 'values' ) );

            $arrRelsToDelete = array();
            $arrRelsToAdd = array();

            // filterInput
            foreach ( $arrCatIds as $strKey => $strValue ) {
                if ( trim( $strValue ) == '' ) {
                   unset( $arrCatIds[ $strKey ] );
                } else {
                    $arrRelsToAdd[ $strValue ] = $strValue;
                }
            }

            // 2. get relations,
            // go through existing relations, mark relations to delete
            $tbl = Cms_Category_Relation::Table();
            $select = $tbl->select()
                   ->where( 'pgcat_page_id = ?', $nPageId );
            $lstRelations = $tbl->fetchAll( $select );
            foreach( $lstRelations as $objRelation ) {
                $nValue = $objRelation->pgcat_cat_id;
                if ( isset( $arrRelsToAdd[ $nValue ] ) ) {
                    unset( $arrRelsToAdd[ $nValue ] );
                } else {
                    $arrRelsToDelete[ $nValue ] = $nValue;
                }
            }

            // Sys_Debug::dump( $arrRelsToAdd );
            // Sys_Debug::dump( $arrRelsToDelete );

            // 3. delete marked relations
            foreach ( $arrRelsToDelete as $nCatId ) {
                $select = $tbl->select()
                   ->where( 'pgcat_page_id = ?', $nPageId )
                   ->where( 'pgcat_cat_id = ?', $nCatId );

                $objRelation = $tbl->fetchRow( $select );
                if (is_object( $objRelation ) ) $objRelation->delete();
            }
            // 4. add marked relations
            foreach (                                                                                       $arrRelsToAdd as $nCatId ) {
                $select = $tbl->select()
                   ->where( 'pgcat_page_id = ?', $nPageId )
                   ->where( 'pgcat_cat_id = ?', $nCatId );

                $objRelation = $tbl->fetchRow( $select );
                if ( !is_object( $objRelation ) ) {
                    $objRelation = $tbl->createRow();
                    $objRelation->pgcat_page_id = $nPageId;
                    $objRelation->pgcat_cat_id = $nCatId;
                    $objRelation->save();
                }
            }

        }
    }

    public function addAction()
    {
        if ( 
            $this->_hasParam( 'pgcat_page_id' ) &&
            $this->_getParam( 'pgcat_cat_id' )
        ) {

            $nPageId = $this->_getIntParam( 'pgcat_page_id' );
            $nCatId = $this->_getIntParam( 'pgcat_cat_id' );
            
            $tbl = Cms_Category_Relation::Table();
            $select = $tbl->select()
                    ->where( 'pgcat_page_id = ?', $nPageId )
                    ->where( 'pgcat_cat_id = ?', $nCatId );
            $objRel = $tbl->fetchRow( $select );
            if ( !is_object( $objRel ) ) {
                $objRel = $tbl->createRow();
                $objRel->pgcat_cat_id = $nCatId;
                $objRel->pgcat_page_id = $nPageId;
                $objRel->save();
            }
            $this->view->object = $objRel;
        } else {
            $this->view->object = null;
        }
    }

    public function deleteAction()
    {
        
        //if ( !$this->_hasParam( 'pgcat_id') ) {

            $nPageId = $this->_getIntParam( 'pgcat_page_id' );
            $nCatId = $this->_getIntParam( 'pgcat_cat_id' );

            $tbl = Cms_Category_Relation::Table();
            $select = $tbl->select()
                    ->where( 'pgcat_page_id = ?', $nPageId )
                    ->where( 'pgcat_cat_id = ?', $nCatId );

            $objRow = $tbl->fetchRow( $select );
            if ( is_object($objRow ) ) {
                $objRow->delete();
            }
        }
    //}
        
    public function setOrderAction()
    {
        $strIds = $this->_getParam( 'order' );
        $tblDealProd = Cms_Category_Relation::Table();
        $nIterator = 0;

        foreach ( explode( '|', $strIds ) as $nId ) {
            $objDealProd = $tblDealProd->find( $nId )->current();
            if ( is_object( $objDealProd )) {
                $objDealProd->pgcat_sort_order = $nIterator;
                $objDealProd->save();
                $nIterator ++;
            }
        }
        $this->view->affected = $nIterator;
        $this->view->return = $this->_getParam( 'return' );
    }
}