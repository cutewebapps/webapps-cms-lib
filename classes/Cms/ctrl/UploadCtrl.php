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


    public function getFilesAction()
    {
        $arrDirectories = array();
        if ( is_object( App_Application::getInstance()->getConfig()->cdn ) ) {
            $arrDirectories = App_Application::getInstance()->getConfig()->cdn->toArray();
        } else {
            $arrDirectories = array( '' => CWA_APPLICATION_DIR . '/cdn/upload' );
        }

        $strDestination = $this->_getParam( 'destination', '' );
        if ( !isset( $arrDirectories[ $strDestination ] ) )
            throw new App_Exception( 'Unknown destination for CDN uploader' );

        // this is the parameter to display listing
        $this->view->arrDestination = $arrDirectories;
        // show information about max file size that is allowed for upload...
        $this->view->max_size = ini_get(  'upload_max_filesize' );

        $dest = $arrDirectories[ $strDestination ];
        if ( is_array( $dest ) ) {
         

        } else {
            $dir = new Sys_Dir( $dest );
            $this->view->files = $dir->getFiles();
        }
        
    }

    public function uploadAction()
    {
	    $this->view->return = $this->_getParam( 'return' );

        if ( count( $_FILES ) > 0 ) {

            for ( $i = 0; $i< 10; $i ++ ) if ( isset( $_FILES[ 'upload' . $i ] ) && $_FILES[ 'upload'. $i ][ 'tmp_name' ] != '' ) {

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
                    WC_APPLICATION_DIR.'/cdn/upload/'.$strName
                );
            }
        }
    }
    
    public function deleteAction()
    {
        $strName = $this->_getParam( 'name' );
        if ( $strName != '' && !strstr( $strName, '..' ) ) {
            $strFullPath = WC_APPLICATION_DIR . '/cdn/upload/'.$strName;
            if ( file_exists( $strFullPath ) ) {
            unlink( $strFullPath );
            }
        }
        $this->view->return = $this->_getParam( 'return' );
    }
}