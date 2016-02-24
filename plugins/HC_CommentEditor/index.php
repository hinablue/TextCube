<?php

function BBCode_Style($target) {
	global $suri, $pluginURL;
	
    $directive = array('archive','category','imageResizer','link','login','logout','pannels','protected','search','tag','trackback','rss','atom','ientry','sync','m');

    if(in_array(str_replace('/','', $suri['directive']), $directive)) return $target;

	ob_start();
?>
<!-- markItUp! skin -->
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $pluginURL;?>/markitup/src/skins/simple/style.css" />
<!--  markItUp! toolbar skin -->
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $pluginURL;?>/markitup/src/sets/bbcode/style.css" />
<?php
	$header = ob_get_contents();
	ob_end_clean();
	
	return $target . "\n" . $header . "\n";
}

function BBCode_footerScript($target) {
	global $suri, $pluginURL;

    $directive = array('archive','category','imageResizer','link','login','logout','pannels','protected','search','tag','trackback','rss','atom','ientry','sync','m');

    if(in_array(str_replace('/','', $suri['directive']), $directive)) return $target;

	$target .= '<script type="text/javascript">
//<![CDDA[
    if (typeof loadCommentCallback === "undefined") {
	    var loadCommentCallback = [];
    }
    if (typeof addCommentCallback === "undefined") {
        var addCommentCallback = [];
    }
    var BBCodeloadCommentCallback = function() {
        BBCodeEntryWriteComment(arguments[0]);
        return false;
    };
    var BBCodeaddCommentCallback = function() {
        BBCodeEntryWriteComment(arguments[1]);
        return false;
    };
    var BBCodeEntryWriteComment = function(entryId) {
    	(function($) {
	        $("textarea", "#entry"+entryId+"WriteComment").markItUp(mySettings);
    	})(jQuery);
	    return false;
    };

    loadCommentCallback.push(BBCodeloadCommentCallback);
    addCommentCallback.push(BBCodeaddCommentCallback);

/*!
  * $script.js v1.3
  * https://github.com/ded/script.js
  * Copyright: @ded & @fat - Dustin Diaz, Jacob Thornton 2011
  * Follow our software http://twitter.com/dedfat
  * License: MIT
  */
!function(a,b,c){function s(a,c){var e=b.createElement("script"),f=j;e.onload=e.onerror=e[o]=function(){e[m]&&!/^c|loade/.test(e[m])||f||(e.onload=e[o]=null,f=1,h[a]=2,c())},e.async=1,e.src=a,d.insertBefore(e,d.firstChild)}function q(a,b){p(a,function(a){return!b(a)})}var d=b.getElementsByTagName("head")[0],e={},f={},g={},h={},i="string",j=!1,k="push",l="DOMContentLoaded",m="readyState",n="addEventListener",o="onreadystatechange",p=function(a,b){for(var c=0,d=a.length;c<d;++c)if(!b(a[c]))return j;return 1};!b[m]&&b[n]&&(b[n](l,function u(){b.removeEventListener(l,u,j),b[m]="complete"},j),b[m]="loading");var r=function(a,b,d){function o(){if(!--m){e[l]=1,j&&j();for(var a in g)p(a.split("|"),n)&&!q(g[a],n)&&(g[a]=[])}}function n(a){return a.call?a():e[a]}a=a[k]?a:[a];var i=b&&b.call,j=i?b:d,l=i?a.join(""):b,m=a.length;c(function(){q(a,function(a){h[a]?(l&&(f[l]=1),h[a]==2&&o()):(h[a]=1,l&&(f[l]=1),s(r.path?r.path+a+".js":a,o))})},0);return r};r.get=s,r.ready=function(a,b,c){a=a[k]?a:[a];var d=[];!q(a,function(a){e[a]||d[k](a)})&&p(a,function(a){return e[a]})?b():!function(a){g[a]=g[a]||[],g[a][k](b),c&&c(d)}(a.join("|"));return r};var t=a.$script;r.noConflict=function(){a.$script=t;return this},typeof module!="undefined"&&module.exports?module.exports=r:a.$script=r}(this,document,setTimeout);

    if(typeof jQuery === "undefined") {
        $script("http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js", function() {
            jQuery.noConflict();
            finalBBCode();
        });
    } else {
        finalBBCode();
    }

    function finalBBCode() {
        $script(["'.$pluginURL.'/markitup/src/jquery.markitup.pack.js","'.$pluginURL.'/markitup/src/sets/bbcode/set.js","'.$pluginURL.'/jquery.expr.regex.js"], function() {
            (function($) {
                $(document).ready(function() {
                    if (typeof $("form:regex(id, entry[0-9]+WriteComment)") === "object") {
                        $("textarea", $("form:regex(id, entry[0-9]+WriteComment)")).markItUp(mySettings);
                    }
                });

            })(jQuery);
        });
    }
//]]>
</script>';
	
	return $target;	
}

function BBCode_Print($target, $mother) {
	return BBCode2Html($target);
}

function BBCode2Html($text) {
	global $pluginURL;
	
	$text = trim($text);

	// BBCode [code]
	if (!function_exists('escape')) {
		function escape($s) {
			global $text;
			$text = strip_tags($text);
			$code = $s[1];
			$code = htmlspecialchars($code);
			$code = str_replace("[", "&#91;", $code);
			$code = str_replace("]", "&#93;", $code);
			return '<pre><code>'.$code.'</code></pre>';
		}	
	}
	$text = preg_replace_callback('/\[code\](.*?)\[\/code\]/ms', "escape", $text);
	
	// BBCode to find...
	$in = array( 	 '/\[b\](.*?)\[\/b\]/ms',	
					 '/\[i\](.*?)\[\/i\]/ms',
					 '/\[u\](.*?)\[\/u\]/ms',
					 '/\[img\](.*?)\[\/img\]/ms',
					 '/\[email\](.*?)\[\/email\]/ms',
					 '/\[url\="?(.*?)"?\](.*?)\[\/url\]/ms',
					 '/\[size\="?(.*?)"?\](.*?)\[\/size\]/ms',
					 '/\[color\="?(.*?)"?\](.*?)\[\/color\]/ms',
					 '/\[quote](.*?)\[\/quote\]/ms',
					 '/\[list\=(.*?)\](.*?)\[\/list\]/ms',
					 '/\[list\](.*?)\[\/list\]/ms',
					 '/\[\*\]\s?(.*?)\n/ms',
					 '/\[smile\="?(.*?)"?\](.*?)\[\/smile\]/ms'
	);
	// And replace them by...
	$out = array(	 '<strong>\1</strong>',
					 '<em>\1</em>',
					 '<u>\1</u>',
					 '<img src="\1" alt="\1" />',
					 '<a href="mailto:\1">\1</a>',
					 '<a href="\1">\2</a>',
					 '<span style="font-size:\1%">\2</span>',
					 '<span style="color:\1">\2</span>',
					 '<blockquote>\1</blockquote>',
					 '<ol start="\1">\2</ol>',
					 '<ul>\1</ul>',
					 '<li>\1</li>',
					 '<img src="'.$pluginURL.'/images/emoticons/\1.gif" width="50" height="50" border="0" alt="[\2]" />'
	);
	$text = preg_replace($in, $out, $text);
	
	// paragraphs
	$text = str_replace("\r", "", $text);
	$text = ereg_replace("(\n){2,}", "</p><p>", $text);
	$text = nl2br($text);
	
	// clean some tags to remain strict
	// not very elegant, but it works. No time to do better ;)
	if (!function_exists('removeBr')) {
		function removeBr($s) {
			return str_replace("<br />", "", $s[0]);
		}
	}	
	$text = preg_replace_callback('/<pre>(.*?)<\/pre>/ms', "removeBr", $text);
	$text = preg_replace('/<p><pre>(.*?)<\/pre><\/p>/ms', "<pre>\\1</pre>", $text);
	
	$text = preg_replace_callback('/<ul>(.*?)<\/ul>/ms', "removeBr", $text);
	$text = preg_replace('/<p><ul>(.*?)<\/ul><\/p>/ms', "<ul>\\1</ul>", $text);
	
	return $text;
}
?>
