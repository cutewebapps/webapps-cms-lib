<?php

class Cms_SubscribeCtrl extends App_DbTableCtrl
{

	public function editAction()
	{
		if ( $this->_isPost() ) {
			$arrErrors = array();

			$strEmail = trim( $this->_getParam( 'subscribe_email' ));
                        
                        if ( trim( $strEmail ) == '' ) {
				array_push( $arrErrors, array( 'subscribe_email' => Lang_Hash::get('Please provide your email' )));
                        } else if ( !Sys_String::isEmail( $strEmail ) ) {
				array_push( $arrErrors, array( 'subscribe_email' => Lang_Hash::get('Please provide valid email' )));
                        }
			
			if ( count( $arrErrors ) == 0 ) {
				// check email is unique for this event
				$objEmail = Cms_Subscribe::Table()->fetchByEmail( $strEmail );
				if ( isset( $objEmail ) )  {
					array_push( $arrErrors, array( Lang_Hash::get( 'This email was already registered' ) ) );
				}
			}

			if ( count( $arrErrors ) > 0 ) { $this->view->arrError = $arrErrors; return; }
		}
		parent::editAction();
	}

}