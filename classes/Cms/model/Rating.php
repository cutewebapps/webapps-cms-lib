<?php

class Cms_Rating_Table extends DBx_Table
{
    protected $_name = 'cms_rating';
    protected $_primary = 'pgr_id';
}

class Cms_Rating_List extends DBx_Table_Rowset
{
}

class Cms_Rating extends DBx_Table_Row
{
    public static function getClassName() { return 'Cms_Rating'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
    public static function FormClass( $name ) { return self::getClassName().'_Form_'.$name; }
    public static function Form( $name ) { $strClass = self::getClassName().'_Form_'.$name; return new $strClass; }

    public function getPageId()
    {
        return $this->pgr_page_id;
    }

    public function getRatingType()
    {
        return $this->pgr_type;
    }

    public function getAuthorId()
    {
        return $this->pgr_author_id;
    }

    public function getDate()
    {
        return $this->pgr_date;
    }

    public function getValue()
    {
        return $this->pgr_rating;
    }

}