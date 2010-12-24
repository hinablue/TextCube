<?php
function CodeHighLighter_ContentReplacer($matches)
{
	if (strlen($matches[1]) == 0) {
		$matches[1] = "plain";
	}

	$content = preg_replace('@<br\\s*/?>@Ui' , "\r\n", str_replace(array("\r","\n"),"",$matches[2]));
	return "<pre class=\"brush: ".strtolower(trim($matches[1]))."\">\n".$content."\n</pre>\n";
}

function CodeHighLighter_DressContent($mother, $id)
{
	return preg_replace_callback('@\\[CODE([^\\]]*)\\](.*)\\[/CODE\\]@Usi', 'CodeHighLighter_ContentReplacer', $mother);
}

function CodeHighLighter_ApplyStyles($mother) {
    global $suri, $pluginURL;

    $directive = array('archive','category','guestbook','imageResizer','link','login','logout','pannels','protected','search','tag','trackback','rss','atom','ientry','sync','m');

    if(in_array(str_replace('/','', $suri['directive']), $directive)) return $mother;

    $mother .= '<link rel="stylesheet" type="text/css" href="'.$pluginURL.'/styles/shCore.css" />
<link rel="stylesheet" type="text/css" href="'.$pluginURL.'/styles/shThemeDefault.css" />';

    return $mother;
}

function CodeHighLighter_ApplyScripts($mother)
{
    global $suri, $pluginURL;

    $directive = array('archive','category','guestbook','imageResizer','link','login','logout','pannels','protected','search','tag','trackback','rss','atom','ientry','sync','m');

    if(in_array(str_replace('/','', $suri['directive']), $directive)) return $mother;

    ob_start();
?>
<script type="text/javascript" src="<?php echo $pluginURL; ?>/scripts/shCore.js"></script>
<script type="text/javascript" src="<?php echo $pluginURL; ?>/scripts/shAutoloader.js"></script>
<script type="text/javascript">
//<![CDATA[
    if(typeof document.getElementsByTagName === "undefined") {
        // I don't care.
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
                };
            } else {
                CodeHighlighterjQscript.onload = function() {
                    google.load("jquery", "1.4.2");
                    google.setOnLoadCallback(function() {
                        jQuery.noConflict();
                        finalBeautyOfCode();
                    });
                };
            }
        } else {
            finalBeautyOfCode();
        }
    }

    function finalBeautyOfCode() {
	function path()
	{
		var args = arguments, result = [];

		for(var i = 0; i < args.length; i++) {
			result.push(args[i].replace('@', '<?php echo $pluginURL; ?>/scripts/'));
		}
		return result
	};
        (function($) {
            $(document).ready(function() {
		SyntaxHighlighter.autoloader.apply(null, path(
			'applescript            @shBrushAppleScript.js',
			'actionscript3 as3      @shBrushAS3.js',
			'bash shell             @shBrushBash.js',
			'coldfusion cf          @shBrushColdFusion.js',
			'cpp c                  @shBrushCpp.js',
			'c# c-sharp csharp      @shBrushCSharp.js',
			'css                    @shBrushCss.js',
			'delphi pascal          @shBrushDelphi.js',
			'diff patch pas         @shBrushDiff.js',
			'erl erlang             @shBrushErlang.js',
			'groovy                 @shBrushGroovy.js',
			'java                   @shBrushJava.js',
			'jfx javafx             @shBrushJavaFX.js',
			'js jscript javascript  @shBrushJScript.js',
			'perl pl                @shBrushPerl.js',
			'php                    @shBrushPhp.js',
			'text plain             @shBrushPlain.js',
			'py python              @shBrushPython.js',
			'ruby rails ror rb      @shBrushRuby.js',
			'sass scss              @shBrushSass.js',
			'scala                  @shBrushScala.js',
			'sql                    @shBrushSql.js',
			'vb vbnet               @shBrushVb.js',
			'xml xhtml xslt html    @shBrushXml.js'
		));
                SyntaxHighlighter.all();
            });
        })(jQuery);
    }
//]]>
</script>
<?php
    $mother .= ob_get_contents();
    ob_end_clean();

	return $mother;
}

?>
