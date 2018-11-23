<?php


/**
 * Library of functions used in the tutor block.
 *
 */


/**
 * Checks if a the name of a resource has different translations. If so, it returns the first of them.
 * 
 * @param string $var
 */
function checkTranslation($var)
{
    	$findme   = '{mlang}';
	$pos = strpos($var, $findme);
	if ($pos !== false) {
		$cad= explode("}", $var);
		$res = explode("{", $cad[1]);
		return $res[0];
	}
	else{
		return $var;
	}
}

?>
