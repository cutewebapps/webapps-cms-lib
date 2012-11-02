<?php

class Cms_Category_Table extends DBx_Table
{
    /** @var string */
    protected $_name = 'cms_category';
    /** @var string */
    protected $_primary = 'cat_id';

    /** @return Cms_Category */
    function findByPageId( $nId )
    {
        $selectPage = $this->select()->where( 'cat_page_id = ?', $nId );
        return $this->fetchRow( $selectPage );
    }
    /** @return Cms_Category */
    public function findBySlug( $strSlug, $strLang = '' )
    {
        $select = $this->select()
            ->setIntegrityCheck( false )
            ->from( Cms_Category::TableName() )
            ->joinInner( Cms_Page::TableName(), 'cat_page_id = pg_id' )
            ->where( 'pg_slug = ? ', $strSlug );
        if ( $strLang != '' )
            $select->where( 'pg_lang = ? ', $strLang );
        
        $objPage = $this->fetchRow( $select );
        if ( is_object( $objPage ))
            return $objPage->getJoinedObject( Cms_Category::Table(), 'cat_page_id', 'pg_id' );
        return null;
    }
}


class Cms_Category_Form_Filter extends App_Form_Filter
{
    public function createElements()
    {
        parent::createElements();

        $elemPageId = new App_Form_Element( 'cat_type_id', 'text' );
        $this->addElement( $elemPageId );

        $elemPageId = new App_Form_Element( 'cat_page_id', 'text' );
        $this->addElement( $elemPageId );

        $elemPageId = new App_Form_Element( 'cat_parent_id', 'text' );
        $this->addElement( $elemPageId );

    }
}

class Cms_Category_Form_Edit extends App_Form_Edit
{
    public function createElements()
    {
        parent::createElements();

        $elemPageId = new App_Form_Element( 'cat_type_id', 'text' );
        $this->addElement( $elemPageId );

        $elemPageId = new App_Form_Element( 'cat_page_id', 'text' );
        $this->addElement( $elemPageId );

        $elemPageId = new App_Form_Element( 'cat_parent_id', 'text' );
        $this->addElement( $elemPageId );
    }
}

class Cms_Category_List extends DBx_Table_Rowset
{
    public function getPagesTotal( $strWhere = '' )
    {
        if ( count( $this->getIds() ) == 0 )
                return array();

        $selectPagesTotal = Cms_Category_Relation::Table()->select()
            ->from( Cms_Category_Relation::TableName(), array( 'pgcat_cat_id', 'count(*) total' )  )
            ->group( 'pgcat_cat_id' )
            ->having( 'pgcat_cat_id IN ('. implode( ',', $this->getIds() ).')' );
        if ( $strWhere != '' )
            $selectPagesTotal->where( $strWhere );

        $lstCommentsStat = Cms_Category_Relation::Table()->fetchAll( $selectPagesTotal );

        $arrStat = array();
        foreach ( $lstCommentsStat as $objStat ) {
            $arrStat[ $objStat->pgcat_cat_id ] = $objStat->total;
        }
        return $arrStat;
    }
}

class Cms_Category extends DBx_Table_Row
    implements Cms_Page_Interface, Cms_Category_Interface
{
    public static function getClassName() { return 'Cms_Category'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
    public static function FormClass( $name ) { return self::getClassName().'_Form_'.$name; }
    public static function Form( $name ) { $strClass = self::getClassName().'_Form_'.$name; return new $strClass; }

    public function getTypeId()
    {
        return $this->cat_type_id;
    }

    public function getPageId()
    {
        return $this->cat_page_id;
    }
    public function getPage()
    {
        return $this->getJoinedObject( Cms_Page::Table(), 'pg_id', 'cat_page_id' );
    }

    public function getSortOrder()
    {
        return $this->cat_sort_order;
    }
    public function getParentId()
    {
        return $this->cat_parent_id;
    }
    public function getParent()
    {
        return Cms_Category::Table()->find( $this->getParentId() )->current();
    }
    public function getSortIndex()
    {
        return $this->cat_sort_index;
    }
    public function getDepth()
    {
        return strlen( $this->cat_sort_index ) / 3;
    }

    public function _insert()
    {
        if ( empty ( $this->cat_date ) )
            $this->cat_date = date('Y-m-d H:i:s');
        parent::_insert();
    }

    public function _delete()
    {
        // delete from categories relation
        $selectRelation  = Cms_Category_Relation::Table()->select()
            ->where( 'pgcat_cat_id = ?', $this->getId() );
        foreach( Cms_Category_Relation::Table()->fetchAll( $selectRelation ) as $objRelation ) {
            $objRelation->delete();
        }
        if ( $this->cat_page_id != 0 ) {
            $objPage = Cms_Page::Table()->find( $this->cat_page_id )->current();
            if ( is_object( $objPage )) $objPage->delete();
        }
        parent::_delete();
    }
}