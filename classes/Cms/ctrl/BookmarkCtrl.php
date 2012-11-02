<?php

class Cms_BookmarkCtrl extends App_DbTableCtrl
{
    protected function _filterField( $strFieldName, $strFieldValue )
    {
        switch ( $strFieldName ) {
            case 'with_user': {
                $this->_select->joinInner( User_Account::TableName(), 'ucac_id = bkmrk_user_id' );
                break;
            }
            case 'user_login': {
                $this->_select->joinInner( User_Account::TableName(), 'ucac_id = bkmrk_user_id' );
                $this->_select->where( 'ucac_login = ?', $strFieldName );
                break;
            }
            case 'with_page': {
                $this->_select->joinInner( Cms_Page::TableName(), 'pg_id = bkmrk_page_id' );
                break;
            }

            default: {
                parent::_filterField( $strFieldName, $strFieldValue );
            }
        }
    }
}