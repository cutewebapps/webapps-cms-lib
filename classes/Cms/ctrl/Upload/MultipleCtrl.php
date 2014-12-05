<?php

class Cms_Upload_MultipleCtrl extends App_AbstractCtrl
{
    
    public function deleteAction()
    {
        $nPageId = $this->_getIntParam( "page_id");
        if ( ! $nPageId ) {
            throw new App_Exception( "Page Id was not provided");
        }
        $dirOfThePage = new Sys_Dir( CWA_APPLICATION_DIR.'/cdn/upload/page/' . $nPageId );
        $dirOfThePage->delete();
        
        $selectPostImages = Cms_Page_Image::Table()->select()
                ->where( 'img_page_id = ?', $nPageId );
        foreach ( Cms_Page_Image::Table()->fetchAll( $selectPostImages ) as $objImage ) {
            $objImage->delete();
        }
    }
    
    public function filesAction()
    {
        set_time_limit( 0 );
        ini_set( 'memory_limit', '2G' );
        try {
            
            $objConfigCms = App_Application::getInstance()->getConfig()->cms;
            
            $handler = new Cms_Upload_Handler();
            $nPageId = $this->_getIntParam( "page_id");
            if ( ! $nPageId ) {
                throw new App_Exception( "Page Id was not provided");
            }
            
            $dirOfThePage = new Sys_Dir( CWA_APPLICATION_DIR.'/cdn/upload/page/' . $nPageId.'/original', true );
            
            // slash at the end of the path is important, do not remove
            $arrResult = $handler->handleUpload( $dirOfThePage->getName(), false ); 
            if ( isset( $arrResult['error'] ) ) {
                throw new App_Exception( $arrResult['error'] );
            }
            $strFileName = $handler->getUploadName();
            // throw new App_Exception( 'filename: ' . $strFileName.', result = ' . print_r( $arrResult, true ) );
            
            $image = new App_ImageFile( '/cdn/upload/page/' . $nPageId.'/original/' . basename( $strFileName ) );
            
            $objPageImage = Cms_Page_Image::Table()->createRow();
            $objPageImage->img_page_id  = $nPageId;
            $objPageImage->img_group = basename( $strFileName );
            $objPageImage->img_sortorder = Cms_Page_Image::Table()->getMaxSortOrder( $nPageId, 'original' );
            $objPageImage->img_path      = '/cdn/upload/page/' . $nPageId.'/original/'.basename( $strFileName );
            $objPageImage->img_width     = $image->getWidth();
            $objPageImage->img_height    = $image->getHeight();
            $objPageImage->save( false );
           
            if ( is_object( $objConfigCms ) && $objConfigCms->page_image_thumbs ) {
                
                foreach( $objConfigCms->page_image_thumbs as $sThumbType => $arrDimensions ) {
                    
                    $dirOfThePage = new Sys_Dir( CWA_APPLICATION_DIR.'/cdn/upload/page/' . $nPageId.'/'.$sThumbType, true );
                    $thumb = $image->generateThumbnail( '/cdn/upload/page/' . $nPageId.'/'.$sThumbType.'/'.basename( $strFileName ), 
                            $arrDimensions->width, 
                            $arrDimensions->height);       
                    $objPageThumb = Cms_Page_Image::Table()->createRow();
                    $objPageThumb->img_page_id  = $nPageId;
                    $objPageThumb->img_group = basename( $strFileName );
                    $objPageThumb->img_type     = $sThumbType;
                    $objPageThumb->img_sortorder = Cms_Page_Image::Table()->getMaxSortOrder( $nPageId, $sThumbType );
                    $objPageThumb->img_path      = '/cdn/upload/page/' . $nPageId.'/'.$sThumbType.'/'.basename( $strFileName );
                    $objPageThumb->img_width     = $thumb->getWidth();
                    $objPageThumb->img_height    = $thumb->getHeight();
                    $objPageThumb->save( false );
                }
            }
            
        } catch ( Exception $e ) {
            $arrResult['error'] = $e->getMessage();
        }
        if ( isset( $arrResult['error'] )) {
            $this->view->result = array('success'  => FALSE, 'error'  => $arrResult['error'] );
            return;
        }
        $this->view->result = array('success'  => true );
    }
}