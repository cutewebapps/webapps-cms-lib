<?php

class Cms_WebLink_Table extends DBx_Table
{
    /** @var string */
    protected $_name = 'cms_link';
    /** @var string */
    protected $_primary = 'lnk_id';

}

/*
    lnk_id           INT NOT NULL AUTO_INCREMENT,
    lnk_slug         VARCHAR(128) DEFAULT \'\' NOT NULL,
    lnk_lang         CHAR(4) DEFAULT \'en\' NOT NULL,

    lnk_type_id      INT DEFAULT 0 NOT NULL,
    lnk_status       INT DEFAULT 1 NOT NULL, -- 1 is published

    lnk_block_id     INT DEFAULT 0 NOT NULL,
    lnk_sortorder    INT DEFAULT 0 NOT NULL,
    lnk_dt_start        DATETIME DEFAULT \'0000-00-00 00:00:00\' NOT NULL,
    lnk_dt_finish       DATETIME DEFAULT \'0000-00-00 00:00:00\' NOT NULL,

    lnk_title        VARCHAR(255) DEFAULT \'\' NOT NULL,
    lnk_href         VARCHAR(255) DEFAULT \'\' NOT NULL,
    lnk_target       VARCHAR(20)  DEFAULT \'\' NOT NULL,
    lnk_onclick      VARCHAR(255) DEFAULT \'\' NOT NULL,
 */

class Cms_WebLink_Form_Filter extends App_Form_Filter
{
    public function createElements()
    {
        $this->allowFiltering( array( 'lnk_block_id', 'lnk_slug', 'lnk_lang', 'lnk_type_id', 'lnk_status',
            'lnk_dt_start', 'lnk_dt_start_from', 'lnk_dt_start_to',
            'lnk_dt_finish', 'lnk_dt_finish_from', 'lnk_dt_finish_to',
            'lnk_href', 'lnk_title', 'lnk_target', 'lnk_onclick'
            ) );
        parent::createElements();
    }
}

class Cms_WebLink_Form_Edit extends App_Form_Edit
{
    public function createElements()
    {
        $this->allowEditing( array(  'lnk_block_id', 'lnk_slug', 'lnk_lang', 'lnk_type_id', 'lnk_status',
            'lnk_dt_start', 'lnk_dt_finish',
            'lnk_href', 'lnk_title', 'lnk_target', 'lnk_onclick' ) );
        parent::createElements();

    }
}

class Cms_WebLink_List extends DBx_Table_Rowset
{
}

class Cms_WebLink extends DBx_Table_Row
{
    public static function getClassName() { return 'Cms_WebLink'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
    public static function FormClass( $name ) { return self::getClassName().'_Form_'.$name; }
    public static function Form( $name ) { $strClass = self::getClassName().'_Form_'.$name; return new $strClass; }

    protected function _insert()
    {
        $this->lnk_dt_start = date('Y-m-d H:i:s');        
    }
}