<?php

class Cms_Content_Youtube implements Cms_Content_Filter
{
    protected $_strContents;

    public function __construct( $strContents )
    {
        $this->_strContents = $strContents;
        return $this;
    }

    /** @return string */
    public function getResult() {
	$c = $this->_strContents;

        $m = array();
        $arrMatches = array(
             '@\[youtube\](.+)\[/youtube\]@simU',
             '@http://www\.youtube\.com/watch\?v=([-\w]+)\&feature=player_embedded@simU',
        );

        foreach ( $arrMatches as $strPattern )  {
            preg_match_all( $strPattern, $c, $m );
            for ( $i = 0; $i < count( $m[0] ); $i++ ) {
                $orig = $m[0][$i];
                $path = $m[1][$i];
                $out = '<div class="centered">'
                     .'<iframe width="550" height="339" src="http://www.youtube.com/embed/'
                     . $path
                     .'" frameborder="0" allowfullscreen=""></iframe>'
                .'</div>';
                $c = str_replace( $orig, $out, $c );
            }
        }
        return $c;
    }
}