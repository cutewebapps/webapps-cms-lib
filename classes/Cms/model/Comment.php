<?php

class Cms_Comment_Table extends DBx_Table
{
    protected $_primary = 'pgc_id';

    protected $_name = 'cms_comment';
}

class Cms_Comment_List extends DBx_Table_Rowset
{
}

class Cms_Comment_Form_Filter extends App_Form_Filter
{
    public function createElements()
    {
        $this->allowFiltering( array( 'pgc_page_id', 'pgc_ip', 'pgc_agent', 'pgc_reviewed', 'pgc_enabled',
                'pgc_date', 'pgc_author_name', 'pgc_author_email', 'pgc_author_url' ));
    }
}

class Cms_Comment_Form_Edit extends App_Form_Edit
{
    public function createElements()
    {
        $this->allowEditing( array( 'pgc_page_id', 'pgc_ip', 'pgc_agent', 'pgc_reviewed', 'pgc_enabled',
                'pgc_date', 'pgc_author_name', 'pgc_author_email', 'pgc_author_url'  ));
    }
}


class Cms_Comment extends DBx_Table_Row
{
    public static function getClassName() { return 'Cms_Comment'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
    public static function FormClass( $name ) { return self::getClassName().'_Form_'.$name; }
    public static function Form( $name ) { $strClass = self::getClassName().'_Form_'.$name; return new $strClass; }
    
    public function getPageId()
    {
        return $this->pgc_page_id;
    }

    public function getCommentType()
    {
        return $this->pgc_type;
    }

    public function getAuthorId()
    {
        return $this->pgc_author_id;
    }

    /** @return User_Account */
    public function getUserObject()
    {
        return User_Account::Table()->find( $this->pgc_author_id )->current();
    }

    public function getDate()
    {
        return $this->pgc_date;
    }

    public function getText()
    {
        return $this->pgc_comment;
    }

}