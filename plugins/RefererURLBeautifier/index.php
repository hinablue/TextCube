<?php
	function RefererURLBeautifier_handler($target, $mother) {
		$keyword = false;
		if(preg_match('/\W(q|query|k|keyword|search|stext|nlia|aqa|wd)(?:=|%3D)([^&]+)/i', $mother['url'], $matches))
			$keyword = urldecode(rawurldecode($matches[2]));
		else if(strpos($mother['host'], 'images.google.') !== false && preg_match('/%3Fsearch%3D([^&]+)/i', $mother['url'], $matches))
			$keyword = urldecode(rawurldecode($matches[1]));
		else if(strpos($mother['host'], 'yahoo.') !== false && preg_match('/\Wp=([^&]+)/i', $mother['url'], $matches))
			$keyword = urldecode(rawurldecode($matches[1]));
		else if(preg_match('@/search/(?:\w+/)*([^/?]+)@i', $mother['url'], $matches))
			$keyword = urldecode(rawurldecode($matches[1]));
		if(!UTF8::validate($keyword))
			$keyword = UTF8::correct(UTF8::bring($keyword));
		$keyword = UTF16UrlDecode($keyword);
		$url = rawurldecode(substr($mother['url'], 7));
		if(!UTF8::validate($url))
			$url = UTF8::correct(UTF8::bring($url));
		//return '<img src="http://'.$mother['host'].'/favicon.ico" width="16" height="16" alt="Favicon" onerror="this.parentNode.removeChild(this)" style="vertical-align: middle"/> ' . (($keyword) ? '<span style="font-weight: bold; color: #594">['.htmlspecialchars($keyword).']</span> ' . UTF8::lessenAsEm($url, 65 - UTF8::lengthAsEm($keyword)) : UTF8::lessenAsEm($url, 65));
		return ($keyword) ? '<span style="font-weight: bold; color: #594">['.htmlspecialchars($keyword).']</span> ' . htmlspecialchars(UTF8::lessenAsEm($url, 70 - UTF8::lengthAsEm($keyword))) : htmlspecialchars(UTF8::lessenAsEm($url, 70));
	}

	if(!function_exists('UTF16UrlToString')) {
		function UTF16UrlToString($str) {
			return iconv('UTF-16LE', 'UTF-8', chr(hexdec(substr($str[1], 2, 2))).chr(hexdec(substr($str[1], 0, 2))));
		}
	}

	if(!function_exists('UTF16UrlDecode')) {
		function UTF16UrlDecode($str) {
			return rawurldecode(preg_replace_callback('/%u([0-9A-F]{4})/', 'UTF16UrlToString', $str));
		}
	}
?>
