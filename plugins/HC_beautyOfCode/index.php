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
    /**
     * scripts.js
     */
    (function(c,j){var h=j.getElementsByTagName("script")[0],e={},a={},d=false,k=function(){},l="string",b=function(){return Array.every||function(f,p,o){for(var n=0,m=f.length;n<m;++n){if(!p(f[n],n,f)){return d}}return 1}}(),i=function(f,n,m){b(f,function(q,p,o){n(q,p,o);return true},m)};if(j.readyState==null&&j.addEventListener){j.addEventListener("DOMContentLoaded",function g(){j.removeEventListener("DOMContentLoaded",g,d);j.readyState="complete"},d);j.readyState="loading"}c.$script=function(p,m,o){var n=typeof m=="function"?m:(o||k),p=typeof p==l?[p]:p,r=typeof m==l?m:p.join(""),f=p.length,q=function(){if(!--f){e[r]=1;n();for(dset in a){b(dset.split("|"),function(s){return(s in e)})&&b(a[dset],function(s){s();a[dset].shift()})}}};b(p,function(s){setTimeout(function(){var u=j.createElement("script"),t=d;u.onload=u.onreadystatechange=function(){if((u.readyState&&u.readyState!=="complete"&&u.readyState!=="loaded")||t){return d}u.onload=u.onreadystatechange=null;t=true;q()};u.async=true;u.src=s;h.insertBefore(u,h.firstChild)},0);return true});return c};$script.ready=function(o,m,n){n=n||k;o=(typeof o==l)?[o]:o;var f=[];i(o,function(p){(p in e)||f.push(p)})&&b(o,function(p){return(p in e)})?m():(function(p){a[p]=a[p]||[];a[p].push(m);n(f)}(o.join("|")));return $script}}(window,document));

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
