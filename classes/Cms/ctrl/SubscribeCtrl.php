<?php

class Cms_SubscribeCtrl extends App_DbTableCtrl
{

	public function editAction()
	{
		if ( $this->_isPost() ) {
			$arrErrors = array();

			$this->_require( array(
				'field' => 'subscribe_email', 'message' => Lang_Hash::get('Please provide your email' ),
				'field' => 'subscribe_email', 'method' => 'email', 'message' => Lang_Hash::get('Please provide valid email' ),
			));
			
			if ( count( $arrErrors ) == 0 ) {
				// check email is unique for this event
				$objEmail = Cms_Subscribe::Table()->fetchByEmail( $this->_getParam( 'subscribe_email' ));
				if ( isset( $objEmail ) )  {
					array_push( $arrErrors, array( Lang_Hash::get( 'This email was already registered' ) ) );
				}
			}

			if ( count( $arrErrors ) > 0 ) { $this->view->arrError = $arrErrors(); return; }
		}
		parent::editAction();
	}

}