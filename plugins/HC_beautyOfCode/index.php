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
<script type="text/javascript">
//<![CDATA[
    /*!
  * $script.js v1.3
  * https://github.com/ded/script.js
  * Copyright: @ded & @fat - Dustin Diaz, Jacob Thornton 2011
  * Follow our software http://twitter.com/dedfat
  * License: MIT
  */
!function(a,b,c){function s(a,c){var e=b.createElement("script"),f=j;e.onload=e.onerror=e[o]=function(){e[m]&&!/^c|loade/.test(e[m])||f||(e.onload=e[o]=null,f=1,h[a]=2,c())},e.async=1,e.src=a,d.insertBefore(e,d.firstChild)}function q(a,b){p(a,function(a){return!b(a)})}var d=b.getElementsByTagName("head")[0],e={},f={},g={},h={},i="string",j=!1,k="push",l="DOMContentLoaded",m="readyState",n="addEventListener",o="onreadystatechange",p=function(a,b){for(var c=0,d=a.length;c<d;++c)if(!b(a[c]))return j;return 1};!b[m]&&b[n]&&(b[n](l,function u(){b.removeEventListener(l,u,j),b[m]="complete"},j),b[m]="loading");var r=function(a,b,d){function o(){if(!--m){e[l]=1,j&&j();for(var a in g)p(a.split("|"),n)&&!q(g[a],n)&&(g[a]=[])}}function n(a){return a.call?a():e[a]}a=a[k]?a:[a];var i=b&&b.call,j=i?b:d,l=i?a.join(""):b,m=a.length;c(function(){q(a,function(a){h[a]?(l&&(f[l]=1),h[a]==2&&o()):(h[a]=1,l&&(f[l]=1),s(r.path?r.path+a+".js":a,o))})},0);return r};r.get=s,r.ready=function(a,b,c){a=a[k]?a:[a];var d=[];!q(a,function(a){e[a]||d[k](a)})&&p(a,function(a){return e[a]})?b():!function(a){g[a]=g[a]||[],g[a][k](b),c&&c(d)}(a.join("|"));return r};var t=a.$script;r.noConflict=function(){a.$script=t;return this},typeof module!="undefined"&&module.exports?module.exports=r:a.$script=r}(this,document,setTimeout);

    $script(['<?php echo $pluginURL; ?>/scripts/shBrushAS3.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushBash.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushCSharp.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushColdFusion.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushCpp.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushCss.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushDelphi.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushDiff.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushErlang.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushGroovy.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushJScript.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushJava.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushJavaFX.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushPerl.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushPhp.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushPlain.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushPowerShell.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushPython.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushRuby.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushScala.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushSql.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushVb.js',
        '<?php echo $pluginURL; ?>/scripts/shBrushXml.js'],
        function() {
            SyntaxHighlighter.config.clipboardSwf = '<?php echo $pluginURL; ?>/scripts/clipboard.swf';
            SyntaxHighlighter.all();
    });
//]]>
</script>
<?php
    $mother .= ob_get_contents();
    ob_end_clean();

	return $mother;
}

?>
