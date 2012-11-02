<?php

class Cms_Bookmark_Table extends DBx_Table
{
    protected $_name = 'cms_bookmark';
    protected $_primary = 'bkmrk_id';

    public function findByUidPageId( $nUserId, $nPageId, $nType = 0 )
    {
        $select = $this->select()
            ->where( 'bkmrk_user_id = ?', $nUserId )
            ->where( 'bkmrk_page_id = ?', $nPageId )
            ->where( 'bkmrk_');
        return $this->fetchRow( $select );
    }
}

class Cms_Bookmark_List extends DBx_Table_Rowset
{
}

class Cms_Bookmark_Form_Filter extends App_Form_Filter
{
    public function createElements()
    {
        $elemUser = new App_Form_Element_Text( 'user_login', 'text' );
        $this->addElement( $elemUser );

        $elemUser = new App_Form_Element_Text( 'bkmrk_user_id', 'text' );
        $this->addElement( $elemUser );

        $elemPage = new App_Form_Element_Text( 'bkmrk_page_id', 'text' );
        $this->addElement( $elemPage );
        
        $elemDate = new App_Form_Element_Text( 'bkmrk_date', 'text' );
        $this->addElement( $elemDate );

        $elemDate = new App_Form_Element_Text( 'with_user', 'text' );
        $this->addElement( $elemDate );

        $elemDate = new App_Form_Element_Text( 'with_page', 'text' );
        $this->addElement( $elemDate );

        $elemDate = new App_Form_Element_Text( 'user_login', 'text' );
        $this->addElement( $elemDate );

        // bkmrk_page_id
    }
}

class Cms_Bookmark_Form_Edit extends App_Form_Edit
{
    public function createElements()
    {
        $elemUser = new App_Form_Element_Text( 'bkmrk_user_id', 'text' );
        $this->addElement( $elemUser );

        $elemPage = new App_Form_Element_Text( 'bkmrk_page_id', 'text' );
        $this->addElement( $elemPage );
    }
}

class Cms_Bookmark extends DBx_Table_Row
{
    public static function getClassName() { return 'Cms_Bookmark'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
    public static function FormClass( $name ) { return self::getClassName().'_Form_'.$name; }
    public static function Form( $name ) { $strClass = self::getClassName().'_Form_'.$name; return new $strClass; }

    public function getUserId()
    {
        return $this->bkmrk_user_id;
    }
    public function getPageId()
    {
        return $this->bkmrk_page_id;
    }
    public function getDate()
    {
        return $this->bkmrk_date;
    }

    public function _insert()
    {
        if ( !isset($this->bkmrk_date ) || date( 'Y', strtotime( $this->bkmrk_date ) ) < 1980 )
            $this->bkmrk_date = date( 'Y-m-d H:i:s');
        
        parent::_insert();
    }

}