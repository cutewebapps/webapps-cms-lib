<?php

class Cms_Upload_FileCtrl extends App_AbstractCtrl 
{

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
    

    public function deleteAction() 
    {
        $strRoot = $this->_getRoot();
        $strUrl = $this->_getParam('url');
        $this->view->url = $strUrl;
        $this->view->root = $strRoot . '/' . $strUrl;
        
        $strFileName = trim($this->_getParam('file'));

        if (file_exists($this->view->root. '/'.$strFileName)) {
            unlink($this->view->root . '/'. $strFileName);
        }
        
    }
        
    public function editAction() {
        $strRoot = $this->_getRoot();
        $strUrl = $this->_getParam('url').'/';
        $this->view->url = $strUrl;
        $this->view->root = $strRoot . '/' . $strUrl;
        
      
        $strFile_old_name = basename(trim($this->_getParam('file_old_name')));
        $strFile_new_name = ( trim($this->_getParam('file_new_name')));
        if ($this->isPost()) {
            if (file_exists($this->view->root. '/'.$strFile_old_name)) {
                rename($this->view->root. '/'.$strFile_old_name, $this->view->root. '/'.$strFile_new_name);
            }
        }
    }


}