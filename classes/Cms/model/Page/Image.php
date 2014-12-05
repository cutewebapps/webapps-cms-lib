<?php

class Cms_Page_Image_Table extends DBx_Table
{
    /** @var string */
    protected $_name = 'cms_page_image';
    /** @var string */
    protected $_primary = 'img_id';
    
    public function getMaxSortOrder( $nPageId, $sType )
    {
        return $this->getIterator( 'img_sortorder', array( 'img_page_id' => $nPageId, 'img_type'  => $sType ) );
    }
    
    public function getPageImages( $nPage, $sType = '' )
    {
        $select = Cms_Page_Image::Table()->select()
                ->where( 'img_page_id = ?', $nPage )
                ->order( 'img_sortorder');
        if ( $sType !== '' ) {
            $select->where( 'img_type = ?', $sType );
        }
        return Cms_Page_Image::Table()->fetchAll( $select );
    }

}

class Cms_Page_Image_Form_Filter extends App_Form_Filter
{
    public function createElements()
    {
        $this->allowFiltering( array( 'image_type', 'image_page_id' ) );
    }
}

class Cms_Page_Image_Form_Edit extends App_Form_Edit
{
    public function createElements()
    {
        $this->allowEditing( array() );

    }
    /**
     * @return string
     */
    public function getObjectName()
    {                  
	return Lang_Hash::get( 'Page Image' );
    }
}

class Cms_Page_Image_List extends DBx_Table_Rowset
{
}

class Cms_Page_Image extends DBx_Table_Row
{
    public static function getClassName() { return 'Cms_Page_Image'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
    public static function FormClass( $name ) { return self::getClassName().'_Form_'.$name; }
    public static function Form( $name ) { $strClass = self::getClassName().'_Form_'.$name; return new $strClass; }

    /**
     * 
     * @return \App_ImageFile
     */
    public function getImageObject()
    {
        return new App_ImageFile( $this->img_path, $this->img_width, $this->img_height );
    }
    
    /**
     * getting group images instead of itself
     * @return Cms_Page_Image_List
     */
    public function getGroupImages()
    {
        $select = Cms_Page_Image::Table()->select()
                ->where( 'img_id <> ?', $this->getId() )
                ->where( 'img_page_id = ?', $this->img_page_id )
                ->where( 'img_group = ?', $this->img_group );
        return Cms_Page_Image::Table()->fetchAll( $select );
    }
    
    /**
     * getting group images instead of itself
     * @return Cms_Page_Image_List
     */
    public function getGroupImage( $sType )
    {
        $select = Cms_Page_Image::Table()->select()
                ->where( 'img_id <> ?', $this->getId() )
                ->where( 'img_page_id = ?', $this->img_page_id )
                ->where( 'img_group = ?', $this->img_group )
                ->where( 'img_type = ?', $sType );
        return Cms_Page_Image::Table()->fetchRow( $select );
    }
    
    protected function _delete()
    {
        $sExpectedPath = CWA_APPLICATION_DIR . $this->img_path;
        if (file_exists( $sExpectedPath ))  {
            unlink( $sExpectedPath );
        }
    }
}