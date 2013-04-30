<?php


class Cms_Page_Table extends DBx_Table
{
    /** @var string */
    protected $_name = 'cms_page';
    /** @var string */
    protected $_primary = 'pg_id';

    /** @return Cms_Page */
    public function findBySlug( $strSlug, $strLang = '', $strTypeId = '' )
    {
        $select = $this->select()
            ->where( 'pg_slug = ? ', $strSlug );
        if ( $strLang != '' )
            $select->where( 'pg_lang = ? ', $strLang );
        if ( $strTypeId !== '' )
            $select->where( 'pg_type_id = ? ', $strTypeId );
        return $this->fetchRow( $select );
    }


    /** @return Cms_Page */
    public function importPage( $strSlug, $strContents, $arrOptions = array() ) 
    {    
        $strLang     = isset( $arrOptions['lang'] )      ? $arrOptions[ 'lang' ] : 'en';
        $nTypeId     = isset( $arrOptions['type'] )      ? $arrOptions[ 'type' ] : 0;
        $nStatus     = isset( $arrOptions['status'] )    ? $arrOptions[ 'status' ] : 0;
        $nPublished  = isset( $arrOptions['published'] ) ? $arrOptions[ 'published' ] : 1;
        $dtCreated   = isset( $arrOptions['created'] )   ? $arrOptions[ 'created' ] : date('Y-m-d H:i:s');
        $strTemplate = isset( $arrOptions['template'] )  ? $arrOptions[ 'template' ] : '';
        $strBrief    = isset( $arrOptions['brief'] )     ? $arrOptions[ 'brief' ] : '';

        $objRow = $this->findBySlug( $strSlug, $strLang );
        if ( !is_object( $objRow )) {
            $objRow = $this->createRow();
            $objRow->pg_slug        = $strSlug;
        }
        $objRow->pg_lang        = $strLang;
        $objRow->pg_type_id     = $nTypeId;
        $objRow->pg_status      = $nStatus;
        $objRow->pg_published   = $nPublished;
        $objRow->pg_template    = $strTemplate;
        $objRow->pg_created     = $dtCreated;

        $strMetaTitle = '';
        $strMetaKeys = '';
        $strMetaDescr = '';
        $strTitle = '';

        $arrTags = array( 'Title', 'MetaTitle', 'MetaKeys', 'MetaDescr' );
        foreach ( $arrTags as $strNeedle ) {
          
            if ( preg_match( '/<' . $strNeedle . '>(.*)<\/' . $strNeedle . '>/simU',
                    $strContents, $arrMatches ) ) {
                $strVarName = 'str'.$strNeedle;
                $$strVarName = $arrMatches[1];
                $strContents = str_replace( $arrMatches[0], '', $strContents );
            }
        }
        if ( isset( $arrOptions['title'] ) )      $strTitle     = $arrOptions[ 'title' ];
        if ( isset( $arrOptions['meta_title'] ) ) $strMetaTitle = $arrOptions[ 'meta_title' ];
        if ( isset( $arrOptions['meta_keys'] ) )  $strMetaKeys  = $arrOptions[ 'meta_keys' ];
        if ( isset( $arrOptions['meta_descr'] ) ) $strMetaDescr = $arrOptions[ 'meta_descr' ];

        $objRow->pg_meta_title = $strMetaTitle;
        $objRow->pg_meta_keys  = $strMetaKeys;
        $objRow->pg_meta_descr = $strMetaDescr;
        $objRow->pg_title      = $strTitle;
        $objRow->pg_content    = $strContents;
        $objRow->pg_brief      = $strBrief;
        return $objRow;
    }
}

class Cms_Page_Form_Filter extends App_Form_Filter
{
    public function createElements()
    {
        $this->allowFiltering( array( 'pg_type_id', 'pg_title', 'pg_content', 'pg_template', 'pg_slug',
            'pg_status', 'pg_published', 'pg_comment_allow', 'pg_created_from', 'pg_created_to',
            'pg_image', 'category', 'category_slug', 'pg_lang'
            ) );
        parent::createElements();
    }
}

class Cms_Page_Form_Edit extends App_Form_Edit
{
    public function createElements()
    {
        $this->allowEditing( array( 'pg_slug', 'pg_meta_title', 'pg_meta_keys', 'pg_meta_descr', 
            'pg_template', 'pg_brief', 'pg_title', 'pg_content', 'pg_type_id', 'pg_type_sortorder',
            'pg_status', 'pg_published', 'pg_comment_allow', 'pg_created', 'pg_lang', 
            'pg_image' ) );
        parent::createElements();

    }
}

class Cms_Page_List extends DBx_Table_Rowset
{
}

class Cms_Page extends DBx_Table_Row
{
    public static function getClassName() { return 'Cms_Page'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }
    public static function FormClass( $name ) { return self::getClassName().'_Form_'.$name; }
    public static function Form( $name ) { $strClass = self::getClassName().'_Form_'.$name; return new $strClass; }

    /** @return string */
    public function getSlug()
    {
        return $this->pg_slug;
    }

    /** @return string */
    public function getLang()
    {
        return $this->pg_lang;
    }

    /** @return integer */
    public function getType()
    {
        return $this->pg_type_id;
    }

    /** @return integer */
    public function getTypeSortOrder()
    {
        return $this->pg_type_sortorder;
    }
    
    /** @return integer */
    public function getStatus()
    {
        return $this->pg_status;
    }

    /** @retrun boolean */
    public function isPublished()
    {
        return $this->pg_published;
    }

    /** @return string */
    public function getLayout()
    {
        return $this->pg_template;
    }

    /** @return string */
    public function getMetaTitle()
    {
        return $this->pg_meta_title;
    }

    /** @return string */
    public function getMetaKeys()
    {
        return $this->pg_meta_keys;
    }

    /** @return string */
    public function getMetaDescr()
    {
        return $this->pg_meta_descr;
    }

    /** @return string */
    public function getTitle()
    {
        return $this->pg_title;
    }

    /** @return string */
    public function getContent()
    {
        return $this->pg_content;
    }

    /** @return datetime */
    public function getDateCreated() {
        return $this->pg_created;
    }

    /** @return string */
    public function getImage()
    {
        return $this->pg_image;
    }

    /**
     * this date format is ofter required in blogs
     * @return string
     */
    public function getDateCreatedRfc2822()
    {
        $year = substr( $this->getDateCreated(), 0, 4);
        $month = substr($this->getDateCreated(), 5, 2);
        $day = substr($this->getDateCreated(), 8, 2);
        $hour = substr($this->getDateCreated(), 11, 2);
        $min = substr($this->getDateCreated(), 14, 2);
        $sec = substr($this->getDateCreated(), 17, 2);

        $post_date = mktime((int)$hour, (int)$min, (int)$sec, (int)$month, (int)$day, (int)$year);
        return date( 'r', $post_date);
    }

    /** @return datetime */
    public function getDateModified() {
        return $this->pg_modified;
    }

    /** @return int */
    public function getAuthorId() {
        return $this->pg_author_id;
    }

    /** @return boolean */
    public function getCommentsAllowed() {
        return $this->pg_comment_allow;
    }

    /** @return int */
    public function getCommentsNum() {

        $objConfigCms = App_Application::getInstance()->getConfig()->cms;
        if ( is_object( $objConfigCms ) ) {
            // if comments are disabled by config file, return 0
            if ( $objConfigCms->disable_comments == 1 )
                return 0;
        }

        $selectCommentsStat = Cms_Comment::Table()->select()
            ->from( Cms_Comment::TableName(), array( 'pgc_page_id', 'count(*) stat' )  )
            ->group( 'pgc_page_id' )
            ->having( 'pgc_page_id = ? ', $this->getId() );
        $objCommentsStat = Cms_Comment::Table()->fetchRow( $selectCommentsStat );
        if ( is_object( $objCommentsStat ) ) return $objCommentsStat->stat;
        
        return 0;
    }

    protected function _insert() {
        $this->pg_modified = date('Y-m-d H:i:s');
        parent::_insert();
    }

    protected function _update() {
        $this->pg_modified = date('Y-m-d H:i:s');
        parent::_update();
    }
}