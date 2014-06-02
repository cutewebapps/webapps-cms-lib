<?php

class Cms_SubscribeCtrl extends App_DbTableCtrl {

    public function editAction() {
        $arrErrors = array();
        $strEmail = trim($this->_getParam('subscribe_email'));
        if ($this->_isPost()) {

            if (trim($strEmail) == '') {
                array_push($arrErrors, array('subscribe_email' => Lang_Hash::get('Please provide your email')));
            } else if (!Sys_String::isEmail($strEmail)) {
                array_push($arrErrors, array('subscribe_email' => Lang_Hash::get('Please provide valid email')));
            }

            if (count($arrErrors) == 0) {
                // check email is unique for this event
                $objEmail = Cms_Subscribe::Table()->fetchByEmail($strEmail, $this->_getParam( 'subscribe_event' ) );
                if ( is_object($objEmail)) {
                    array_push($arrErrors, array(Lang_Hash::get('This email was already registered')));
                }
            }

            if (count($arrErrors) > 0) {
                $this->view->arrError = $arrErrors;
                return;
            }
        }
        parent::editAction();
        
        if ( $this->_hasParam('subscribe_message') && $this->_getParam('subscribe_message') != '' )
            $this->view->lstMessages = array($this->_getParam('subscribe_message'));
        
        if ( $this->_isPost() && count($arrErrors) == 0 && is_object($this->view->object) &&
                is_object( App_Application::getInstance()->getConfig()->mail ) ) {
            
            $objConfig = App_Application::getInstance()->getConfig()->mail->subscribe;
            if ( is_object( $objConfig )) {
                // send mail about subscription (if configured)
                $mail = new App_Mail_Subscription();
                
                $arrLines = array();
                $arrLines []= 'Email: '.$strEmail;
                if ( $this->_hasParam( 'subscribe_event' ) )
                    $arrLines []= 'Event ID: '.$this->_getParam( 'subscribe_event' );
                if ( $this->_hasParam( 'subscribe_first' ) )
                    $arrLines []= 'First Name: '.$this->_getParam( 'subscribe_first' );
                if ( $this->_hasParam( 'subscribe_last' ) )
                    $arrLines []= 'Last Name: '.$this->_getParam( 'subscribe_last' );
                
                $mail->setBody( "New subscription: ".implode( "<br />", $arrLines ) );
                $mail->send();
            }
        }
    }

}