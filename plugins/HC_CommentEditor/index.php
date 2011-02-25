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

    //script.js
    (function(c,j){var h=j.getElementsByTagName("script")[0],e={},a={},d=false,k=function(){},l="string",b=function(){return Array.every||function(f,p,o){for(var n=0,m=f.length;n<m;++n){if(!p(f[n],n,f)){return d}}return 1}}(),i=function(f,n,m){b(f,function(q,p,o){n(q,p,o);return true},m)};if(j.readyState==null&&j.addEventListener){j.addEventListener("DOMContentLoaded",function g(){j.removeEventListener("DOMContentLoaded",g,d);j.readyState="complete"},d);j.readyState="loading"}c.$script=function(p,m,o){var n=typeof m=="function"?m:(o||k),p=typeof p==l?[p]:p,r=typeof m==l?m:p.join(""),f=p.length,q=function(){if(!--f){e[r]=1;n();for(dset in a){b(dset.split("|"),function(s){return(s in e)})&&b(a[dset],function(s){s();a[dset].shift()})}}};b(p,function(s){setTimeout(function(){var u=j.createElement("script"),t=d;u.onload=u.onreadystatechange=function(){if((u.readyState&&u.readyState!=="complete"&&u.readyState!=="loaded")||t){return d}u.onload=u.onreadystatechange=null;t=true;q()};u.async=true;u.src=s;h.insertBefore(u,h.firstChild)},0);return true});return c};$script.ready=function(o,m,n){n=n||k;o=(typeof o==l)?[o]:o;var f=[];i(o,function(p){(p in e)||f.push(p)})&&b(o,function(p){return(p in e)})?m():(function(p){a[p]=a[p]||[];a[p].push(m);n(f)}(o.join("|")));return $script}}(window,document));

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
