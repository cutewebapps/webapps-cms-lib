<?php

class Cms_Content_PlainText implements Cms_Content_Filter
{
    protected $_strContents;

    function __construct( $strContents )
    {
        $this->_strContents = $strContents;
        return $this;
    }
    
    /** @return string */
    function getResult() {

	$c = $this->_strContents;
	// Empty space divides paragraphs
	if ( !preg_match( '/<p[\S]*>/sim', $c, $m ) ) {
		// if there is a plain text, not HTML

		if ( strstr( $c, "\r\n\r\n" ) ) {
			$para = explode( "\r\n\r\n", $c );
			$cleared = array();
			foreach( $para as $i => $paragraph ) {
				if ( trim( $paragraph ) != "" )  {
					$cleared[]= "<p>\n".trim( $paragraph )."</p>\n";
				}
			}
			$c = implode( "\n\n", $cleared );

		} else if ( strstr( $c, "\n\n" ) ) {
			$para = explode( "\n\n", $c );
			$cleared = array();
			foreach( $para as $i => $paragraph ) {
				if ( trim( $paragraph ) != "" )  {
					$cleared[]= "<p>\n".trim( $paragraph )."</p>\n";
				}
			}
			$c = implode( "\n\n", $cleared );
		}
		$c = preg_replace( '@<p>\s*(<div.+</div>)@simU', '$1<p>', $c );
		// Otherwise we get empty <p> tag at the beginning of the post
	} else {
            die( 'has tag in the content '.htmlspecialchars( $m[0] ) );
        }

	$c = preg_replace( '|<p>(<div.+</div>)</p>|simU', '$1', $c );
	$c = preg_replace( '|(</div>)<p>(<div.+)|simU', '', $c );
	$c = preg_replace( '|</p><p>(&nbsp;<a.+</a>)</p>|simU', '$1</p>', $c );
	//$c = preg_replace( '|<p><div>|simU', '<div>', $c );
	$c = preg_replace( '|<p><ul>(<p>)?|', '<ul>', $c );
	$c = preg_replace( "|<p></p>|simU",  "", $c );
        
        return $c;
    }
}