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

    if(typeof document.getElementsByTagName === "undefined") {
        // I don\'t care.
    } else {
        if(typeof jQuery === "undefined") {
            var BBCodejQscript = document.createElement("script");
            BBCodejQscript.src = "http://www.google.com/jsapi";
            BBCodejQscript.type = "text/javascript";
            document.getElementsByTagName("head")[0].appendChild(BBCodejQscript);

            if (BBCodejQscript.readyState) {
                BBCodejQscript.onreadystatechange = function () {
                    if (BBCodejQscript.readyState == "loaded" || BBCodejQscript.readyState == "complete") {
                        finalInitjQuery();
                    }
                    return;
                };
            } else {
                BBCodejQscript.onload = function() {
                    google.load("jquery", "1.4.2");
                    google.setOnLoadCallback(function() {
                        jQuery.noConflict();
                        finalBBCode();
                    });
                };
            }
        } else {
            finalBBCode();
        }
    }

    function finalBBCode() {
        (function($) {
            $(\'<script />\')
            .attr(\'type\',\'text/javascript\')
            .attr(\'src\', \''.$pluginURL.'/markitup/src/jquery.markitup.pack.js\')
            .appendTo($(\'head\'));
            $(\'<script />\')
            .attr(\'type\',\'text/javascript\')
            .attr(\'src\', \''.$pluginURL.'/markitup/src/sets/bbcode/set.js\')
            .appendTo($(\'head\'));
            $(\'<script />\')
            .attr(\'type\',\'text/javascript\')
            .attr(\'src\', \''.$pluginURL.'/jquery.expr.regex.js\')
            .appendTo($(\'head\'));

	        $(document).ready(function() {
                if (typeof $("form:regex(id, entry[0-9]+WriteComment)") === "object") {
                    $("textarea", $("form:regex(id, entry[0-9]+WriteComment)")).markItUp(mySettings);
                }
	        });

        })(jQuery);
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
