<?php

class Cms_WebLinkCtrl extends App_DbTableCtrl
{
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

    public function uploadAction()
    {
        // upload document and store it in a local CDN
        $this->view->return = $this->_getParam( 'return' );

        if ( count( $_FILES ) > 0 ) {
            $nBlock  = $this->_getParam( 'lnk_block',   0 );
            $nType   = $this->_getParam( 'lnk_type_id', 0 );
            $nStatus = $this->_getParam( 'lnk_status',  1 );

	    for ( $i = 0; $i< 10; $i ++ ) if (
		isset( $_FILES[ 'upload' . $i ] ) &&
		$_FILES[ 'upload'. $i ][ 'tmp_name' ] != '' ) {

		$strCaption = $this->_getParam( 'lnk_title'. $i, '' );
                $strFileName = basename( $_FILES[ 'upload' . $i ]['name'] );
		if ( $strCaption == '' ) {
    		    $strCaption = $_FILES[ 'upload' . $i ]['name'];
		}
		move_uploaded_file(
			$_FILES[ 'upload'.$i ][ 'tmp_name' ],
			str_replace( '//', '/', WC_APPLICATION_DIR.'/cdn/upload/'.$strFileName)
		);

                $weblink = Cms_WebLink::Table()->createRow();
                $weblink->lnk_type_id  = $nType;
                $weblink->lnk_status   = $nStatus;
                $weblink->lnk_block_id = $nBlock;
                $weblink->lnk_title    = $strCaption;
                $weblink->lnk_href     = str_replace( '//', '/', App_Application::getInstance()->getConfig()->base.'/cdn/upload/'.$strFileName );

                $weblink->save();
            }
        }
    }

    public function setOrderAction()
    {
        $strIds = $this->_getParam( 'order' );
        $tblPages = Cms_WebLink::Table();
        $nIterator = 0;

        foreach ( explode( '|', $strIds ) as $nImageId ) {
            $objImage = $tblPages->find( $nImageId )->current();
            if ( is_object( $objImage )) {
                $objImage->lnk_sortorder = $nIterator;
                $objImage->save();
                $nIterator ++;
            }
        }
        $this->view->affected = $nIterator;
        $this->view->return = $this->_getParam( 'return' );
    }
}