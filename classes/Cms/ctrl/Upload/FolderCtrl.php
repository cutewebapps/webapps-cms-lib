<?php


class Cms_Upload_FolderCtrl extends App_AbstractCtrl {

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
   
    public function createAction()
    {
        $arrErrors = array();
        
        $strRoot = $this->_getRoot();
        $strUrl = $this->_getParam('url');

        $this->view->url = $strUrl;
        $this->view->root = $strRoot . '/' . $strUrl;
        
        $strFolderName = trim($this->_getParam('folder'));
        $newFolder = $this->view->root . '/'.$strFolderName;

        if ($this->isPost()) {
            if (!is_dir($newFolder)) {
                mkdir($newFolder, 0777);
                $this->view->lstMessages = 'Folder ' . $strFolderName . ' was created';
            } else {
                $arrErrors['folder'] = 'Please provide uniq folder name!';
            }
        }
        if (!$strFolderName && $strFolderName == '') {
            $arrErrors['folder'] = 'Please provide new folder name';
        }
        
        $this->view->arrError = $arrErrors;
    }

    public function deleteAction() 
    {
        $strRoot = $this->_getRoot();
        $strUrl = $this->_getParam('url');

        $this->view->url = $strUrl;
        $this->view->root = $strRoot . '/' . $strUrl;
        
        $strFolderName = trim($this->_getParam('folder'));
        if ($this->isPost()) {
            if (is_dir($this->view->root. '/'. $strFolderName)) {
                rmdir($this->view->root. '/'. $strFolderName);
            }
        }
    }

    public function editAction() 
    {

        $strRoot = $this->_getRoot();
        $strUrl = $this->_getParam('url').'/';
        $this->view->url = $strUrl;
        $this->view->root = $strRoot . '/' . $strUrl;
        $strFolder_old_name = basename(trim($this->_getParam('dir_old_name')));
        $strFolder_new_name = ( trim($this->_getParam('dir_new_name')));

        
        if ($this->isPost()) {
            if (is_dir($this->view->root. '/'.$strFolder_old_name)) {
                rename($this->view->root. '/'.$strFolder_old_name, $this->view->root. $strFolder_new_name);
            }
        }
    }

}