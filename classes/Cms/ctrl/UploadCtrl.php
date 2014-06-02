<?php


class Cms_UploadCtrl extends App_AbstractCtrl 
{
    /** @return boolean */
    protected function _isImage( $strMime )
    {
        return strstr( $strMime, 'image/' );
    }
    /**
     * @return boolean
     * @todo: valid type check
     */
    protected function _isValidType( $strMime, $strTypesAllowed )
    {
        if ( $strTypesAllowed == '' or $strTypesAllowed == '*' )
            return true;
        $arrTypes = explode( ',', $strTypesAllowed );
        return in_array( $strMime, $arrTypes );
    }

    /**
     * 
     * @return string
     */
    protected function _getRoot()
    {
        $sResult = CWA_APPLICATION_DIR . '/cdn/upload';
        $objConfig = App_Application::getInstance()->getConfig()->cms;
        if ( is_object($objConfig) && is_object( $objConfig->upload ) && $objConfig->upload->path ) {
           $sResult =  $objConfig->upload->path;
        }
        return $sResult;
    }
   
    public function getFilesAction()
    {
        $arrDirectories = array();
        if ( is_object( App_Application::getInstance()->getConfig()->cdn ) ) {
            $arrDirectories = App_Application::getInstance()->getConfig()->cdn->toArray();
        } else {
            $arrDirectories = array( '' =>  $this->_getRoot() );
	    foreach (  $arrDirectories as $strDir ) {
		$dir = new Sys_Dir( $strDir ); 
		if ( ! $dir->exists() ) { $dir->create ( '', true ); }
            }
        }

        $strDestination = $this->_getParam( 'destination', '' );
        if ( !isset( $arrDirectories[ $strDestination ] ) ) {
            throw new App_Exception( 'Unknown destination for CDN uploader' );
        }

        // this is the parameter to display listing
        $this->view->arrDestination = $arrDirectories;
        // show information about max file size that is allowed for upload...
        $this->view->max_size = ini_get(  'upload_max_filesize' );

        $dest = $arrDirectories[ $strDestination ];
        
        $this->view->url = trim( $this->getParam( 'url' ), "/" );
        $this->view->sPath = rtrim( $dest.'/'.$this->view->url, "/" );
        $this->view->bTop = $this->view->url == '' || $this->view->url == '.' || $this->view->url == '/';
        if ( ! $this->view->bTop ) {
            $this->view->sUpperPath = dirname( $this->view->url );
            
        }
        

        $dir = new Sys_Dir( $this->view->sPath );
        $this->view->files = $dir->getFiles();

    }

    public function uploadAction()
    {
	$this->view->return = $this->_getParam( 'return' );
        $this->view->url = $this->_getParam( 'url' );
        
        if ( count( $_FILES ) > 0 ) {
            for ( $i = 0; $i< 10; $i ++ ) {
                if ( isset( $_FILES[ 'upload' . $i ] ) && $_FILES[ 'upload'. $i ][ 'tmp_name' ] != '' ) {

                    $strName = $this->_getParam( 'name'. $i, '' );
                    if ( $strName == '' ) {
                        $strName = $_FILES[ 'upload' . $i ]['name'];
                    }


                    if ( $this->_hasParam( 'accept_type') ) {
                        // TODO: check mime file type
                        if ( !$this->_isValidType( $_FILES[ 'upload'. $i ]['type'],
                                $this->_getParam( 'accept_type') ) ) {
                            $this->view->lstErrors = array( $strName. ' : '
                                .$this->view->translate('Invalid file type'));
                            return;
                        }
                    }


                    // echo '<br />Uploading Name: '.$strName.'<br />';
                    if ( $this->_isImage( $_FILES[ 'upload'. $i ]['type'] ) ) {
                        if ( strstr( $strName, '.' ) === false )  $strName .= '.jpg';

                        list( $nImageWidth, $nImageHeight ) = getimagesize( $_FILES[ 'upload'.$i ][ 'tmp_name' ] );
                        if ( $this->_hasParam( 'accept_max_height') ) {
                            if ( $nImageHeight > $this->_getIntParam( 'accept_max_height') ) {
                                 $this->view->lstErrors = array( $strName. ' : '
                                         .$this->view->translate('Image height cannot exceed')
                                         .' '.$this->_getIntParam( 'accept_max_height')
                                         .', '.$this->view->translate( 'Now' ).': '
                                         .$nImageHeight.' '.$this->view->translate('pixels')
                                         );
                                 return;
                            }
                        }
                        if ( $this->_hasParam( 'accept_max_width') ) {
                            if ( $nImageWidth > $this->_getIntParam( 'accept_max_width') ) {
                                 $this->view->lstErrors = array( $strName. ' : '
                                         .$this->view->translate('Image width cannot exceed')
                                         .' '.$this->_getIntParam( 'accept_max_width')
                                         .', '.$this->view->translate( 'Now' ).': '
                                         .$nImageWidth.' '.$this->view->translate('pixels')
                                         );
                                 return;
                            }
                        }
                    }

                    move_uploaded_file(
                        $_FILES[ 'upload'.$i ][ 'tmp_name' ],
                        $this->_getRoot().'/'.$this->view->url.'/'.$strName
                    );
                }
            }
        }
    }
    
    public function deleteAction()
    {
        $strName = $this->_getParam( 'name' );
        if ( $strName != '' && !strstr( $strName, '..' ) ) {
            $strFullPath = $this->_getRoot().'/'.$strName;
            if ( file_exists( $strFullPath ) ) {
            unlink( $strFullPath );
            }
        }
        $this->view->return = $this->_getParam( 'return' );
    }
}