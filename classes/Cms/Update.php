<?php

// @TODO: CMS component: cleaning the installation as there were too many patcing versions

class Cms_Update extends App_Update 
{
    const VERSION = '0.1.11';

    public static function getClassName() { return 'Cms_Update'; }
    public static function TableClass() { return self::getClassName().'_Table'; }
    public static function Table() { $strClass = self::TableClass();  return new $strClass; }
    public static function TableName() { return self::Table()->getTableName(); }


    public function isEnabled( $strParam )
    {
        $objConfigCms = App_Application::getInstance()->getConfig()->cms;
        if ( !is_object( $objConfigCms ) )
            return false;
        return $objConfigCms->$strParam;
    }
    
    public function update() {

        $this->_install();

        if ($this->isVersionBelow('0.1.2')) {
            $tblPage = Cms_Page::Table();
            if ( !$tblPage->hasColumn( 'pg_type_sortorder' ) ) {
                $tblPage->addColumn(  'pg_type_sortorder','INT DEFAULT 0 NOT NULL');
            }
        }
        if ($this->isVersionBelow('0.1.3') && !$this->isEnabled('disable_comments')) {
            $tblComments = Cms_Comment::Table();
            // insert (ip, agent, published, reviewed)
            if ( !$tblComments->hasColumn( 'pgc_ip' ) ) {
                $tblComments->addColumn(  'pgc_ip','VARCHAR(20) DEFAULT \'\' NOT NULL');
            }
            if ( !$tblComments->hasColumn( 'pgc_agent' ) ) {
                $tblComments->addColumn(  'pgc_agent', 'VARCHAR(255) DEFAULT \'\' NOT NULL');
            }
            if ( !$tblComments->hasColumn( 'pgc_reviewed' ) ) {
                $tblComments->addColumn(  'pgc_reviewed', 'INT DEFAULT 0 NOT NULL');
            }
            if ( !$tblComments->hasColumn( 'pgc_enabled' ) ) {
                $tblComments->addColumn(  'pgc_enabled',  'INT DEFAULT 0 NOT NULL');
            }
        }
        if ($this->isVersionBelow('0.1.4') && !$this->isEnabled('disable_comments')) {
            $tblComments = Cms_Comment::Table();
            // insert (ip, agent, published, reviewed)
            if ( !$tblComments->hasColumn( 'pgc_author_name' ) ) {
                $tblComments->addColumn(  'pgc_author_name','VARCHAR(255) DEFAULT \'\' NOT NULL');
            }
            if ( !$tblComments->hasColumn( 'pgc_author_email' ) ) {
                $tblComments->addColumn(  'pgc_author_email', 'VARCHAR(255) DEFAULT \'\' NOT NULL');
            }
            if ( !$tblComments->hasColumn( 'pgc_author_url' ) ) {
                $tblComments->addColumn(  'pgc_author_url', 'VARCHAR(255) DEFAULT 0 NOT NULL');
            }
        }


        if ( $this->isVersionBelow('0.1.5') ) {
            $tblPage = Cms_Page::Table();
            if ( !$tblPage->hasColumn( 'pg_brief' ) ) {
                $tblPage->addColumn(  'pg_brief','MEDIUMTEXT NOT NULL');
            }
        }

        if ( $this->isVersionBelow('0.1.6') ) {
            $tblPage = Cms_Page::Table();
            if ( !$tblPage->hasColumn( 'pg_comment_allow' ) ) {
                $tblPage->addColumn(  'pg_comment_allow','INT DEFAULT \'1\' NOT NULL');
            }
        }
        if ( $this->isVersionBelow('0.1.7') ) {
            $tblPage = Cms_Page::Table();
            if ( !$tblPage->hasIndex( 'i_pg_created' ) ) {
                $tblPage->addIndex(  'i_pg_created','pg_created');
            }
        }
        if ( $this->isVersionBelow('0.1.8') ) {
            $tblPage = Cms_Page::Table();
            if ( !$tblPage->hasColumn( 'pg_image' ) ) {
                $tblPage->addColumn(  'pg_image', 'VARCHAR(200) DEFAULT \'\' NOT NULL');
            }
        }

        if (/* $this->isVersionBelow('0.1.9') &&*/ !$this->isEnabled('disable_links')) {
            if ( ! $this->getDbAdapterRead()->hasTable( 'cms_link' ) ) {
                Sys_Io::out('Creating Links Table');
                $this->getDbAdapterWrite()->addTableSql('cms_link', '
                    lnk_id           INT NOT NULL AUTO_INCREMENT,
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

                    KEY i_lnk_block_id ( lnk_block_id )
                ', 'lnk_id' );
            }
            if ( $this->getDbAdapterRead()->hasTable( 'cms_link' ) ) {

                $tblLinks = Cms_WebLink::Table();
                if ( !$tblLinks->hasColumn( 'lnk_group' )  ) {
                    Sys_Io::out('Extending Links Table');
                    $tblLinks->addColumn( 'lnk_group', 'CHAR(20) DEFAULT \'\' NOT NULL' );
                }
                if ( !$tblLinks->hasColumn( 'lnk_thumb' )  ) {
                    $tblLinks->addColumn( 'lnk_thumb', 'VARCHAR(255) DEFAULT \'\' NOT NULL' );
                    $tblLinks->addColumn( 'lnk_thumb_width', 'INT DEFAULT \'-1\' NOT NULL' );
                    $tblLinks->addColumn( 'lnk_thumb_height', 'INT DEFAULT \'-1\' NOT NULL' );
                }
                if ( !$tblLinks->hasColumn( 'lnk_class' )  ) {
                    Sys_Io::out('Cms: Links can have classes');
                    $tblLinks->addColumn( 'lnk_class', 'CHAR(50) DEFAULT \'\' NOT NULL' );
                }
            }
        }

        $this->save( self::VERSION );
    }

    /**
     * @return array
     */
    public static function getTables()
    {
        return array(
            Cms_Page::TableName(),
            Cms_Comment::TableName(),
            Cms_Bookmark::TableName(),
            Cms_Category::TableName(),
            Cms_Category_Relation::TableName(),
            Cms_Rating::TableName(),
        );
    }

    protected function _install() {

        if ( !$this->getDbAdapterRead()->hasTable( 'cms_page') ) {
            Sys_Io::out('Creating Page Table');
            $this->getDbAdapterWrite()->addTableSql('cms_page', '
                pg_id           INT NOT NULL AUTO_INCREMENT,
                pg_slug         VARCHAR(128) DEFAULT \'\' NOT NULL,
                pg_lang         CHAR(4) DEFAULT \'en\' NOT NULL,

                pg_type_id      INT DEFAULT 0 NOT NULL,
                pg_status       INT DEFAULT 0 NOT NULL,
                pg_published    INT DEFAULT 1 NOT NULL,
                pg_template     VARCHAR(30)  DEFAULT \'\' NOT NULL,

                pg_meta_title   VARCHAR(255) DEFAULT \'\' NOT NULL,
                pg_meta_keys    TEXT,
                pg_meta_descr   TEXT,

                pg_title        VARCHAR(255) DEFAULT \'\' NOT NULL,
                pg_content      MEDIUMTEXT,

                pg_created      DATETIME NOT NULL DEFAULT \'0000-00-00 0:00:00\',
                pg_modified     DATETIME NOT NULL DEFAULT \'0000-00-00 0:00:00\',
                pg_author_id    INT DEFAULT \'0\' NOT NULL,

                INDEX i_lang_slug ( pg_slug, pg_lang ),
                KEY i_pg_type_id( pg_type_id ),
                KEY i_pg_status( pg_status ),
                KEY i_pg_modified ( pg_modified ),
                PRIMARY KEY ( pg_id )
            ');
        }
        // comments can be configured to store counters in some table
        
        if ( !$this->isEnabled('disable_comments') &&
             !$this->getDbAdapterRead()->hasTable( 'cms_comment') ) {
            Sys_Io::out('Creating Comment Table');
            $this->getDbAdapterWrite()->addTableSql('cms_comment', '

                pgc_id           INT NOT NULL AUTO_INCREMENT,
                pgc_page_id      INT NOT NULL,
                pgc_type         INT DEFAULT 0  NOT NULL,
                pgc_author_id    INT DEFAULT \'0\' NOT NULL,
                pgc_date         DATETIME NOT NULL DEFAULT \'0000-00-00 0:00:00\',

                pgc_sort_order   INT NOT NULL DEFAULT 0,       -- sort order amount brothers
                pgc_parent_id    INT NOT NULL DEFAULT 0,       -- id of parent category
                pgc_sort_index   CHAR(30) NOT NULL DEFAULT \'\' ,  -- sort key - autocalculated field

                pgc_comment      MEDIUMTEXT,

                KEY i_pgc_page_id(pgc_page_id),
                KEY i_pgc_parent_id( pgc_parent_id ),
                KEY i_pgc_type( pgc_type ),
                KEY i_pgc_date( pgc_date ),
                KEY i_pgc_sort_index ( pgc_sort_index ),
                PRIMARY KEY ( pgc_id )
            ');
        } else {
            if ( $this->isEnabled('disable_comments') &&
                $this->getDbAdapterRead()->hasTable( 'cms_comment') ) {
                $this->getDbAdapterWrite()->dropTable( 'cms_comment' );
            }
        }

        // rating can be configured to store counters in some table
        if ( !$this->isEnabled('disable_rating') &&
             !$this->getDbAdapterRead()->hasTable( 'cms_rating') ) {
            Sys_Io::out('Creating Rating Table');
            $this->getDbAdapterWrite()->addTableSql('cms_rating', '

                pgr_id           INT NOT NULL AUTO_INCREMENT,
                pgr_page_id      INT NOT NULL,
                pgr_type         INT DEFAULT 0  NOT NULL,
                pgr_author_id    INT DEFAULT \'0\' NOT NULL,
                pgr_date         DATETIME NOT NULL DEFAULT \'0000-00-00 0:00:00\',
                pgr_rating       INT DEFAULT -1 NOT NULL,

                KEY i_pgr_page_id ( pgr_page_id, pgr_type ),
                KEY i_pgr_author_id( pgr_author_id ),
                KEY i_pgr_date ( pgr_date ),
                KEY i_pgr_rating ( pgr_rating ),
                
                PRIMARY KEY ( pgr_id )
            ');
        } else {
            if ( $this->isEnabled('disable_rating') &&
                $this->getDbAdapterRead()->hasTable( 'cms_rating') ) {
                $this->getDbAdapterWrite()->dropTable( 'cms_rating' );
            }
        }

        if ( !$this->isEnabled('disable_bookmarks') &&
             !$this->getDbAdapterRead()->hasTable( 'cms_bookmark') ) {
            Sys_Io::out('Creating Bookmarks Table (Favorites/WishList) ');
            $this->getDbAdapterWrite()->addTableSql('cms_bookmark', '

                bkmrk_id        INT     NOT NULL AUTO_INCREMENT,
                bkmrk_type_id   INT     NOT NULL DEFAULT \'0\',

                bkmrk_user_id   INT     NOT NULL,
                bkmrk_page_id   INT     NOT NULL,
                bkmrk_date      DATETIME NOT NULL DEFAULT \'0000-00-00 0:00:00\',
                
                UNIQUE i_bkmrk_tup (bkmrk_type_id, bkmrk_user_id, bkmrk_page_id),
                KEY i_bkmrk_date (bkmrk_date),
                PRIMARY KEY( bkmrk_id )
            ' );
        } else {
            if ( $this->isEnabled('disable_bookmarks') &&
                $this->getDbAdapterRead()->hasTable( 'cms_bookmark') ) {
                $this->getDbAdapterWrite()->dropTable( 'cms_bookmark' );
            }
        }

        if ( !$this->isEnabled('disable_categories') &&
              !$this->getDbAdapterRead()->hasTable( 'cms_category') ) {
            Sys_Io::out('Creating Category Table');
            $this->getDbAdapterWrite()->addTableSql('cms_category', '

                cat_id           INT NOT NULL AUTO_INCREMENT,
                cat_page_id      INT DEFAULT 0 NOT NULL, -- used in case 1 to 1 relation for categorizing pages and to store category page otherwise
                cat_type_id      INT DEFAULT 0 NOT NULL,
                cat_date         DATETIME NOT NULL DEFAULT \'0000-00-00 0:00:00\',

                cat_sort_order  INT DEFAULT 0 NOT NULL,       -- sort order amount brothers
                cat_parent_id   INT DEFAULT 0 NOT NULL,       -- id of parent category
                cat_sort_index  CHAR(30) NOT NULL,  -- sort key - autocalculated field

                KEY i_cat_page_id (cat_page_id),
                KEY i_cat_parent_id (cat_parent_id),
                PRIMARY KEY ( cat_id )
            ');
        } else {
            if ( $this->isEnabled('disable_categories') &&
                $this->getDbAdapterRead()->hasTable( 'cms_category') ) {
                $this->getDbAdapterWrite()->dropTable( 'cms_category' );
            }
        }

        if ( !$this->isEnabled('disable_relations') &&
             !$this->getDbAdapterRead()->hasTable( 'cms_page_cat') ) {
            Sys_Io::out('Creating Category Relations Table');
            $this->getDbAdapterWrite()->addTableSql('cms_page_cat', '

                pgcat_id          INT NOT NULL AUTO_INCREMENT,
                pgcat_page_id     INT NOT NULL,
                pgcat_cat_id      INT NOT NULL,
                pgcat_sort_order  INT DEFAULT 0 NOT NULL,
                pgcat_active      INT DEFAULT 1 NOT NULL,

                KEY i_pgcat_page_id (pgcat_page_id),
                KEY i_pgcat_cat_id (pgcat_cat_id),
                PRIMARY KEY ( pgcat_id )
            ');
        } else {
            if ( $this->isEnabled('disable_relations') &&
                $this->getDbAdapterRead()->hasTable( 'cms_page_cat') ) {
                $this->getDbAdapterWrite()->dropTable( 'cms_page_cat' );
            }
        }
        
        // create table for redirects
        if ( !$this->isEnabled('disable_redirects') &&
             !$this->getDbAdapterRead()->hasTable( 'cms_redirect') ) {
            Sys_Io::out('Creating Redirects Table');
            $this->getDbAdapterWrite()->addTableSql('cms_redirect', '
                redir_id        INT NOT NULL AUTO_INCREMENT,
                redir_uri       VARCHAR(250) NOT NULL,
                redir_dest      VARCHAR(250) NOT NULL,
                redir_enabled   INT DEFAULT 1 NOT NULL,
                KEY i_redir_enabled ( redir_enabled  ),
                PRIMARY KEY ( redir_id )
            ');
        } else {
            if ( $this->isEnabled('disable_redirects') &&
                $this->getDbAdapterRead()->hasTable( 'cms_redirect') ) {
                $this->getDbAdapterWrite()->dropTable( 'cms_redirect' );
            }
        }
    }

}
