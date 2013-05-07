<?php

class Cms_Subscribe_Table extends DBx_Table
{
    /** @var string */
    protected $_name = 'cms_subscribe';
    /** @var string */
    protected $_primary = 'subscribe_id';

}
class Cms_Subscribe_Form_Filter extends App_Form_Filter
{
    public function createElements()
    {
        $this->allowFiltering( array( 'subscribe_email', 'subscribe_event', 'subscribe_first', 'subscribe_last' ) );
        parent::createElements();
    }
}

class Cms_Subscribe_Form_Edit extends App_Form_Edit
{
    public function createElements()
    {
        $this->allowEditing( array(  'subscribe_email', 'subscribe_event', 'subscribe_first', 'subscribe_last' ) );
        parent::createElements();

    }
}

class Cms_Subscribe_List extends DBx_Table_Rowset
{
}

class Cms_Subscribe extends DBx_Table_Row
{
    public static function getClassName() { return 'Cms_Subscribe'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
    public static function FormClass( $name ) { return self::getClassName().'_Form_'.$name; }
    public static function Form( $name ) { $strClass = self::getClassName().'_Form_'.$name; return new $strClass; }

    protected function _insert()
    {
        $this->subscribe_dt = date('Y-m-d H:i:s');        
    }
    
/**
    subscribe_email     VARCHAR(250) NOT NULL,
    subscribe_first     VARCHAR(250) NOT NULL,
    subscribe_last      VARCHAR(250) NOT NULL,
    subscribe_dt        DATETIME     NOT NULL DEFAULT \'0000-00-00 00:00:00\',
    subscribe_event     INT(11)      NOT NULL DEFAULT 0,
 */    
}