<?php

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class Cms_Upload_FileForm {
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)){
            return false;
        }
        return true;
    }
    function getName() {
        return $_FILES['qqfile']['name'];
    }
    function getSize() {
        return $_FILES['qqfile']['size'];
    }
    
    function getErrorMessage()
    {
        $message = '';
        if ( $_FILES['qqfile']['error'] != 0 && $_FILES['qqfile']['error'] != UPLOAD_ERR_NO_FILE ) {
            switch( $_FILES['qqfile']['error']) {
                case UPLOAD_ERR_INI_SIZE: 
                    $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini"; 
                    break; 
                case UPLOAD_ERR_FORM_SIZE: 
                    $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"; 
                    break; 
                case UPLOAD_ERR_PARTIAL: 
                    $message = "The uploaded file was only partially uploaded"; 
                    break; 
                case UPLOAD_ERR_NO_FILE: 
                    $message = "No file was uploaded"; 
                    break; 
                case UPLOAD_ERR_NO_TMP_DIR: 
                    $message = "Missing a temporary folder"; 
                    break; 
                case UPLOAD_ERR_CANT_WRITE: 
                    $message = "Failed to write file to disk"; 
                    break; 
                case UPLOAD_ERR_EXTENSION: 
                    $message = "File upload stopped by extension"; 
                    break; 
                default: 
                    $message = "Unknown upload error"; 
                    break; 
            }
        }
        return $message;
    }
}
