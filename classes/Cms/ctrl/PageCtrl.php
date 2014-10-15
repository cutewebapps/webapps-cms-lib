<?php

class Cms_PageCtrl extends App_DbTableCtrl
{
    protected function  _filterField($strFieldName, $strFieldValue) 
    {
        switch( $strFieldName ) {
            case 'pg_created_from':
                $dt = date('Y-m-d H:i:s', strtotime( $strFieldValue ) );
                $this->_select->where( 'pg_created >= ?', $dt );
                $this->_selectCount->where( 'pg_created >= ?', $dt );
                break;
            case 'pg_created_to':
                $dt = date('Y-m-d H:i:s', strtotime( $strFieldValue ) );
                $this->_select->where( 'pg_created <= ?', $dt );
                $this->_selectCount->where( 'pg_created <= ?', $dt );
                break;

            case 'pg_content':
                $this->_select->where( 'pg_content LIKE ?', '%'.$strFieldValue.'%');
                $this->_selectCount->where( 'pg_content LIKE ?', '%'.$strFieldValue.'%' );
                break;

            case 'has_image':
                $this->_select->where( 'pg_image <> ? ', '' );
                $this->_selectCount->where( 'pg_image <> ? ', '' );
                break;

            case 'category_slug':
                // include category in the filtering
                $objCategory = Cms_Category::Table()->findBySlug( $strFieldValue );
                if ( is_object( $objCategory ) ) {
                    $this->_select->joinInner( Cms_Category_Relation::TableName(), 'pgcat_page_id = pg_id' );
                    $this->_select->where( 'pgcat_cat_id = ?', $objCategory->getId() );
                    $this->_selectCount->joinInner( Cms_Category_Relation::TableName(), 'pgcat_page_id = pg_id' );
                    $this->_selectCount->where( 'pgcat_cat_id = ?', $objCategory->getId() );
                } else {
                    $this->_select->where( '0=1' );
                    $this->_selectCount->where( '0=1' );
                }
                break;

            case 'category':
                // include category in the filtering
                $this->_select->joinInner( Cms_Category_Relation::TableName(), 'pgcat_page_id = pg_id' );
                $this->_select->where( 'pgcat_cat_id = ?', intval( $strFieldValue ) );
                $this->_selectCount->joinInner( Cms_Category_Relation::TableName(), 'pgcat_page_id = pg_id' );
                $this->_selectCount->where( 'pgcat_cat_id = ?', intval( $strFieldValue ) );
                break;
            default:
                parent::_filterField($strFieldName, $strFieldValue);
        }
    }

    public function getlistAction()
    {
        parent::getlistAction();
//        die( $this->_select );
    }


    public function getDatesAction()
    {
        $arrDates = array();
        $tbl = Cms_Page::Table();

        $select = $tbl->select()
            ->from( $tbl->getTableName(), array( 'DATE(pg_created) date', 'count(*) total' ) )
            ->group( 'DATE( pg_created )' );
        if ( $this->_hasParam('pg_type_id') ) {
            $select->where( 'pg_type_id = ? ', $this->_getParam('pg_type_id'));
        }

        foreach( $tbl->fetchAll( $select ) as $objDateTotal ) {
            $arrDates[ $objDateTotal->date ] = $objDateTotal->total;
        }

        $this->view->arrDates = $arrDates;
        parent::getDatesAction();
    }

    public function editAction()
    {
        if ( !$this->_getParam('pg_id') &&
              $this->_getParam( 'pg_slug' ) ) {

            $objPage = Cms_Page::Table()->findBySlug( $this->_getParam( 'pg_slug' ), $this->_getParam( 'pg_lang' ) );
            if( is_object( $objPage )) {
                $this->_setParam( 'pg_id', $objPage->getId() );
            }
        }
        parent::editAction();

	// if ( $this->_isPost() ) Sys_Debug::alert( $this->view->object );
    }
    
    public function submitAction()
    {
        $objConfigCms = App_Application::getInstance()->getConfig()->cms;
        if ( !is_object( $objConfigCms ) || ! $objConfigCms->enable_submit ) {
            throw new App_Exception( 'Submission to CMS is not enabled' );
        }
        
        // $strLog = new Sys_File( $objConfigCms->log_submission );? add a kind of logging
        parent::editAction();
        
        if ( is_object( $this->view->object) ) {
            foreach ( $this->_getAllFiles() as $strIndex => $arrFile ) {
                // attach a reference of an image to the post
                $sBaseName = mt_rand(100000,999999).'-'.$arrFile['name'];
                
                $this->_saveUploaded( $strIndex, CWA_APPLICATION_DIR.'/cdn/upload/'.date('Y').'/'.$sBaseName );
                $this->view->object->pg_content .= '<img src="'.'/cdn/upload/'.date('Y').'/'.$sBaseName.'" alt="" />';
                $this->view->object->save( false );
            }
        }
    }    

    public function getAction()
    {

        if ( $this->_getParam( 'pg_slug' ) ) {
            $tbl = Cms_Page::Table();
            $select = $tbl->select()
                        ->where( 'pg_slug = ?', $this->_getParam('pg_slug') );

            
            $strLangCookie = filter_input(INPUT_COOKIE, 'lang', FILTER_SANITIZE_SPECIAL_CHARS);
            if ( $this->_getParam( 'pg_lang' ) ) {
                $select->where( 'pg_lang = ?', $this->_getParam('pg_lang') );
            } else if ( $this->_getParam( 'lang' ) ) {
                $select->where( 'pg_lang = ?', $this->_getParam('lang') );
            } else if ( $strLangCookie ) { 
                $select->where( 'pg_lang = ?', $strLangCookie );
            }

            $this->view->object = $tbl->fetchRow( $select );

        } else {
            parent::getAction();
        }

        if ( !is_object( $this->view->object )  ) {
            throw new App_Exception_PageNotFound ( Lang_Hash::get( 'Page Not Found' ) );
        }

        // page can have subpages, pages for comments, pages for images, etc ...
        $this->view->page = $this->_getParam( 'page', 1 );
    }

   public function setOrderAction()
    {
        $strIds = $this->_getParam( 'order' );
        $tblPages = Cms_Page::Table();
        $nIterator = 0;

        foreach ( explode( '|', $strIds ) as $nImageId ) {
            $objImage = $tblPages->find( $nImageId )->current();
            if ( is_object( $objImage )) {
                $objImage->pg_type_sortorder = $nIterator;
                $objImage->save();
                $nIterator ++;
            }
        }
        $this->view->affected = $nIterator;
        $this->view->return = $this->_getParam( 'return' );
    }

    protected function _saveUrl( $url, $strOutput )
    {
        $ch = curl_init() ;
        // set URL and other appropriate options
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1) ;
        curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.186 Safari/535.1' );
        curl_setopt( $ch, CURLOPT_HEADER, 0 );
        $res = curl_exec ($ch) ;
        curl_close ($ch) ;

        if ( strlen( $strOutput ) > 0 )
        {
            $file = new Sys_File( $strOutput );
            $file->save( $res );
        }
    }

    public function newAction()
    {
        // @param list of URLs to generate thumbnail and put
        // @param list of local files for upload
        // @param list of remote URLs to create a local copy
        // @param pg_slug
        // @param pg_title
        
        // Sys_Debug::dump( $this->_getAllParams() );

        $strContent = '';
        $strBriefPrefix = '';
        
        $bVerbose = 1;
        $cdn = null;
        if (  $this->_getParam( 'cdn_name' ) != '' ) {
            $cdn = new App_Cdn( $this->_getParam( 'cdn_name' ), $bVerbose );
        }
        $strCdnPath = $this->_getParam( 'cdn_folder', '/' );
        
        // copy to CDN downloaded files
        if ( $this->_getParam('new_url') != '' ) {
            $files = explode( "\n", $this->_getParam('new_url') );
            foreach( $files as $line ) if  ( trim( $line ) != '' && substr( trim( $line ), 0, 4 ) == 'http' ) {

                $strBaseName = strtolower( basename( trim( $line ) ));
                if ( !strstr( $strBaseName, '.jpg' ) ) {
                    $strBaseName .= '.jpg';
                }
                $strBaseName  = preg_replace( '@\?.*$@', '', $strBaseName );

                $strTempFile = App_Application::getInstance()->getTempDir() . '/temp.jpg';
                $this->_saveUrl( trim( $line ), $strTempFile );

                // at this point we have IMAGE file saved,
                // if the size of a file is greater than 640px (should be set up in the config)
                // should be scaled
                // 
                // TEMPORARY ! should be enabled on config option only!!
                $szImage = getimagesize( $strTempFile );
                $nMaxAllowedWidth = 640;
                if ( $szImage[ 0 ] > $nMaxAllowedWidth ) {
                    $strCommand = '/usr/bin/convert '.$strTempFile.' -resize '.$nMaxAllowedWidth.' '.$strTempFile;
                    if ( $this->_getParam( 'verbose' ) )
                        Sys_Io::out( 'EXECUTING: '. $strCommand );
                    Sys_Cmd::run( $strCommand );
                }
                // end of temporary

                $strDestPath = $strCdnPath.'/'.$strBaseName;

                if ( $this->_getParam( 'verbose' ) )
                    Sys_Io::out( 'IMAGE: '.trim($line).' ... '.$strDestPath );

                if ( is_object( $cdn ) )
                    $cdn->putFile( $strTempFile, $strDestPath );
                $strContent .= str_replace( '{FILE}', $strDestPath, $this->_getParam('wrapper_image') )."\n";
            }
        }
        // 
        // copy to CDN uploaded local files
        foreach( $_FILES as $arrLocalFile ) {
            // TODO: uploading local images to CDN network
        }

        $nPageThumbWidth = $this->_getIntParam( 'new_thumb_width', 300 );
        $nPageThumbHeight= $this->_getIntParam( 'new_thumb_height', 300 );

        if ( $this->_getParam( 'new_thumb' ) != '' ) {

            // create thumb from URL
            $strTempFile = str_replace( '//', '/', App_Application::getInstance()->getTempDir() . '/temp.jpg' );
            $strThumbFile = str_replace( '//', '/', App_Application::getInstance()->getTempDir() . '/thumb.jpg');
            $this->_saveUrl( trim( $this->_getParam( 'new_thumb' )), $strTempFile );

            $strBaseName = strtolower( basename( trim( $this->_getParam( 'new_thumb' ) ) ));
            
            if ( !strstr( $strBaseName, '.jpg' ) ) {
                $strBaseName .= '_thumb.jpg';
            } else {
                $strBaseName = str_replace( '.jpg', '_thumb.jpg', $strBaseName );
            }
            $strDestPath = $strCdnPath.'/'.$strBaseName;
            
            $img = new Cms_ImageFile( 'file://' .$strTempFile );
            $img->generateThumbnail( 'file://'.$strThumbFile, $nPageThumbWidth, $nPageThumbHeight );

             if ( $this->_getParam( 'verbose' ) )
                 Sys_Io::out( 'THUMB: '. $strThumbFile.' ... '.$strDestPath );

            if ( is_object( $cdn ))
                $cdn->putFile( $strThumbFile, $strDestPath );
            $strBriefPrefix = str_replace( '{FILE}', $strDestPath, $this->_getParam('wrapper_thumb') )."\n";
        }

        // create page
        $objPage = Cms_Page::Table()->findBySlug( $this->_getParam( 'pg_slug' ) );
        if ( !is_object( $objPage ) ) {
            $objPage = Cms_Page::Table()->createRow();
            $objPage->pg_slug = $this->_getParam( 'pg_slug' );
            $objPage->pg_title = $this->_getParam( 'pg_title' );
            $objPage->pg_type_id = $this->_getParam( 'pg_type_id', 0 );
            $objPage->pg_published = 0; // not published yet
            $objPage->pg_brief = $strBriefPrefix . $this->_getParam( 'pg_brief' );
            $objPage->pg_meta_title = $this->_getParam( 'pg_meta_title' );
            $objPage->pg_created = $this->_getParam( 'pg_created', date('Y-m-d H:i:s' ) );
            $objPage->pg_content = $strContent . $this->_getParam( 'pg_content' );
            $objPage->save();
        }

        foreach ( $this->_getAllParams() as $strKey => $value ) {
            if ( preg_match( '/^add2category_(\d+)$/i', $strKey, $arrMatch ) ) {

                Sys_Io::out('Add relation: page=' . $objPage->getId().', cat: '.$arrMatch[1] );
                
                $objRelation = Cms_Category_Relation::Table()->createRow();
                $objRelation->pgcat_cat_id = $arrMatch[1];
                $objRelation->pgcat_page_id = $objPage->getId();
                $objRelation->save();
            }
        }

        $this->view->object = $objPage;
    }
}