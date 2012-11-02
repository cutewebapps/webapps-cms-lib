<?php

class Cms_PageImageCtrl extends App_AbstractCtrl
{

    public function preprocessAction()
    {
        $strImage = $this->_getParam( 'image', '' );
        if ( $strImage == '' )
            throw new App_Exception('Image was not given for preprocessing');

        $nPageId = $this->_getIntParam( 'pg_id' );
        $objPage = Cms_Page::Table()->find( $nPageId )->current();
        if ( $nPageId == 0 || !is_object( $objPage ) )
            throw new App_Exception('Page was not found for preprocessing');

        $objImage = new Cms_ImageFile( $strImage );
        $this->view->objPage = $objPage;
        $this->view->image   = $strImage;
        $this->view->width   = $objImage->getWidth();
        $this->view->height  = $objImage->getHeight();
    }
    
    public function createAction()
    {
        // @param pg_id
        $nPageId = $this->_getIntParam( 'pg_id' );
        $objPage = Cms_Page::Table()->find( $nPageId )->current();
        if ( $nPageId == 0 || !is_object( $objPage ) )
            throw new App_Exception('Page was not found for updating');

        // @param dest_x, dest_y
        $nDestWidth  = $this->_getIntParam( 'dest_x', 0 );
        $nDestHeight = $this->_getIntParam( 'dest_y', 0 );
        if ( $nDestWidth <= 0 || $nDestHeight <= 0 )
            throw new App_Exception('Dimensions for image generation are incorrect' );

        // @param image, x, y, w, h
        $nCropX          = $this->_getIntParam('x');
        $nCropY          = $this->_getIntParam('y');
        $nCropWidth      = $this->_getIntParam('w');
        $nCropHeight     = $this->_getIntParam('h');

        $strTempDir = App_Application::getInstance()->getTempDir();

        $strPathOriginal = $strTempDir . '/'.$nPageId.'_'.mt_rand(1000,9999).'.jpg';
        $strPathThumb    = $strTempDir . '/cropped_' . basename( $strPathOriginal );

        $strImagePath = $this->_getParam( 'image' );
        // download remote file and save
        copy( $strImagePath, $strPathOriginal );

        $img_r = imagecreatefromjpeg( $strPathOriginal );
        $dst_r = ImageCreateTrueColor( $nDestWidth, $nDestHeight );
        imagecopyresampled($dst_r,
                    $img_r,0,0, $nCropX, $nCropY,
                    $nDestWidth,$nDestHeight,$nCropWidth,$nCropHeight);
        imagejpeg($dst_r, $strPathThumb, 90);
        // 1) create cropped image from the given and upload it

        $bVerbose = 1;
        $cdn = new App_Cdn( $this->_getParam( 'cdn_name' ), $bVerbose );
        $strCdnPath = $this->_getParam( 'cdn_folder', '/' );
        
        $strDestPath = $strCdnPath.'/'.basename( $strPathOriginal );
        $cdn->putFile( $strPathThumb, $strDestPath );

        // 2) update pg_image for the page
        $objPage->pg_image = $cdn->getHttpPath() . str_replace( '//', '/', $strCdnPath.'/'. basename( $strDestPath ) );
        $objPage->save();
        $this->view->object = $objPage;
        $this->view->return = $this->_getParam( 'return' );
    }


}