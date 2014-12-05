<?php

class Cms_Page_ImageCtrl extends App_DbTableCtrl
{
    /**
     * 
     * @return string
     */
    public function getClassName()
    {
        return 'Cms_Page_Image';
    }
 
    public function removeGroupAction()
    {
        $this->init();
    	$paramID = $this->_getParam($this->_model->getIdentityName());
        if ( $paramID == '' ){ $paramID = $this->_getParam( "_id"); }
        if ( $paramID == '' ) {
            throw new App_Exception('Param ID should be defined for deleteAction');
        }
        
        $this->view->object = Cms_Page_Image::Table()->find( $this->getParam( $paramID ))->current();
        if ( is_object( $this->view->object )) {
            
            // removing other images of the groups
            $lstGroupImages = $this->view->object->getGroupImages();
            foreach ( $lstGroupImages as $objImage ) {
                $objImage->delete();
            }
            // removing the images itself
            parent::deleteAction();
        }
    }
}