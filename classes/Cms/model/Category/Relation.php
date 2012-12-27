<?php
class Cms_Category_Relation_Table extends DBx_Table
{
    /** @var string */
    protected $_name = 'cms_page_cat';
    /** @var string */
    protected $_primary = 'pgcat_id';
}


class Cms_Category_Relation_Form_Filter extends App_Form_Filter
{
    public function createElements()
    {
        $this->allowFiltering(array( "pgcat_cat_id", "pgcat_page_id", "pgcat_active" ) );
        //parent::createElements();
    }
}

class Cms_Category_Relation_Form_Edit extends App_Form_Edit
{
    public function createElements()
    {
        $this->allowEditing(array( "pgcat_cat_id", "pgcat_page_id", "pgcat_active" ) );
        //parent::createElements();
        // $elemPageId = new App_Form_Element( 'pg_content', 'text' );
        // $this->addElement( $elemPageId );
    }
}

class Cms_Category_Relation_List extends DBx_Table_Rowset
{
}

class Cms_Category_Relation extends DBx_Table_Row
    implements Cms_Category_Relation_Interface
{
    public static function getClassName() { return 'Cms_Category_Relation'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
    public static function FormClass( $name ) { return self::getClassName().'_Form_'.$name; }
    public static function Form( $name ) { $strClass = self::getClassName().'_Form_'.$name; return new $strClass; }

    public function getCategoryId()
    {
        return $this->pgcat_cat_id;
    }
    public function getCategory()
    {
        return $this->getJoinedObject( Cms_Category::Table(), 'cat_id', 'pgcat_cat_id' );
    }
    public function getPageId()
    {
        return $this->pgcat_page_id;
    }
    public function getPage()
    {
        return $this->getJoinedObject( Cms_Page::Table(), 'pg_id', 'pgcat_page_id' );
    }
    public function getSortOrder()
    {
        return $this->pgcat_sort_order;
    }
    public function isActive()
    {
        return $this->pgcat_active;
    }
}