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

    $directive = array('archive','category','guestbook','imageResizer','link','login','logout','pannels','protected','search','tag','trackback','rss','atom','ientry','sync','m','commentcomment');

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
// script.js
(function(c,j){var h=j.getElementsByTagName("script")[0],e={},a={},d=false,k=function(){},l="string",b=function(){return Array.every||function(f,p,o){for(var n=0,m=f.length;n<m;++n){if(!p(f[n],n,f)){return d}}return 1}}(),i=function(f,n,m){b(f,function(q,p,o){n(q,p,o);return true},m)};if(j.readyState==null&&j.addEventListener){j.addEventListener("DOMContentLoaded",function g(){j.removeEventListener("DOMContentLoaded",g,d);j.readyState="complete"},d);j.readyState="loading"}c.$script=function(p,m,o){var n=typeof m=="function"?m:(o||k),p=typeof p==l?[p]:p,r=typeof m==l?m:p.join(""),f=p.length,q=function(){if(!--f){e[r]=1;n();for(dset in a){b(dset.split("|"),function(s){return(s in e)})&&b(a[dset],function(s){s();a[dset].shift()})}}};b(p,function(s){setTimeout(function(){var u=j.createElement("script"),t=d;u.onload=u.onreadystatechange=function(){if((u.readyState&&u.readyState!=="complete"&&u.readyState!=="loaded")||t){return d}u.onload=u.onreadystatechange=null;t=true;q()};u.async=true;u.src=s;h.insertBefore(u,h.firstChild)},0);return true});return c};$script.ready=function(o,m,n){n=n||k;o=(typeof o==l)?[o]:o;var f=[];i(o,function(p){(p in e)||f.push(p)})&&b(o,function(p){return(p in e)})?m():(function(p){a[p]=a[p]||[];a[p].push(m);n(f)}(o.join("|")));return $script}}(window,document));

    if(typeof jQuery === "undefined") {
        $script('http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js', function() {
            jQuery.noConflict();
            finalBeautyOfCode();
        });
    } else {
        finalBeautyOfCode();
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
