<?php
function CodeHighLighter_ContentReplacer($matches)
{
	global $suri;
	if (strlen($matches[1]) == 0) {
		$matches[1] = "plain";
	}

	if($suri['directive']!="/rss" && $suri['directive']!="/m" && $suri['directive']!="/i/entry") {
		$content = preg_replace('@<br\\s*/?>@Ui' , "\r\n", str_replace(array("\r","\n"),"",$matches[2]));
		return "<pre class=\"code\">\n\t<code class=\"".strtolower(trim($matches[1]))."\">\n".$content."\n\t</code>\n</pre>\n";
	} else {
		return $matches[2];
	}
}

function CodeHighLighter_DressContent($mother, $id)
{
	return preg_replace_callback('@\\[CODE([^\\]]*)\\](.*)\\[/CODE\\]@Usi', 'CodeHighLighter_ContentReplacer', $mother);
}

function CodeHighLighter_ApplyScript($mother)
{
	global $pluginURL;

	$mother .= '<script type="text/javascript">
//<![CDDA[
    if(typeof document.getElementsByTagName === "undefined") {
        // I don\'t care.
    } else {
        if(typeof jQuery === "undefined") {
            var CodeHighlighterjQscript = document.createElement("script");
            CodeHighlighterjQscript.src = "http://www.google.com/jsapi";
            CodeHighlighterjQscript.type = "text/javascript";
            document.getElementsByTagName("head")[0].appendChild(CodeHighlighterjQscript);

            if (CodeHighlighterjQscript.readyState) {
                CodeHighlighterjQscript.onreadystatechange = function () {
                    if (CodeHighlighterjQscript.readyState == "loaded" || CodeHighlighterjQscript.readyState == "complete") {
                        finalInitjQuery();
                    }
                    return;
                };
            } else {
                CodeHighlighterjQscript.onload = function() {
                    finalInitjQuery();
                    return;
                };
            }
        } else {
            finalBeautyOfCode();
        }
    }

    function finalInitjQuery() {
        google.load("jquery", "1.4.2");
        google.setOnLoadCallback(function() {
            jQuery.noConflict();
            finalBeautyOfCode();
        });
    }

    function finalBeautyOfCode() {
        (function($) {
            $(\'<script />\')
            .attr(\'type\',\'text/javascript\')
            .attr(\'src\', \''.$pluginURL.'/script/jquery.beautyOfCode.min.js\')
            .appendTo($(\'head\'));

            $(document).ready(function() {
                $.beautyOfCode.init({
                    defaults: {
                        \'class-name\': \'position-example\',
                        \'html-script\': true
                    }
                });
            });
        })(jQuery);
    }
//]]>
</script>';

	return $mother;
}

?>
