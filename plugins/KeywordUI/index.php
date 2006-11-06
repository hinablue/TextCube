<?php
/* KeywordUI for Tattertools 1.1
   ----------------------------------
   Version 1.0
   Tatter and Friends development team.

   Creator          : inureyes
   Maintainer       : inureyes

   Created at       : 2006.10.3
   Last modified at : 2006.10.26
 
 This plugin enables keyword / keylog feature in Tattertools.
 For the detail, visit http://forum.tattertools.com/ko


 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/
function KeywordUI_bindKeyword($target,$mother) {
	global $blogURL, $configVal;
	requireComponent('Tattertools.Function.misc');
	$data = misc::fetchConfigVal($configVal);
	$target = "<a class=\"key1\" onclick=\"openKeyword('$blogURL/keylog/" . rawurlencode($target) . "')\">{$target}</a>";

	return $target;
}

function KeywordUI_setSkin($target,$mother) {
	global $pluginPath;
	return $pluginPath."/keylogSkin.html";
}
?>
