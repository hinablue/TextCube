<?php
/// Copyright (c) 2004-2007, Cain Chen / Hina :: 工程幼稚園
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/// Sitemap build and notify, read more on http://www.sitemaps.org/protocol.php
/// Sitemap XML format:
/// The Sitemap protocol format consists of XML tags. All data values in a Sitemap must be entity-escaped. The file itself must be UTF-8 encoded.

/// Your Sitemap file must be UTF-8 encoded (you can generally do this when you save the file). As with all XML files, any data values (including URLs) must use entity escape codes for the characters listed in the table below.
//////////////////////////////////////
///    Character   /// Escape Code ///
/// Ampersand    & /// &amp;       ///
/// Single Quote ' /// &apos;      ///
/// Double Quote " /// &quot;      ///
/// Greater Than > /// &gt;        ///
/// Less Than    < /// &lt;        ///
//////////////////////////////////////

/// In addition, all URLs (including the URL of your Sitemap) must be URL-escaped and encoded for readability by the web server on which they are located. However, if you are using any sort of script, tool, or log file to generate your URLs (anything except typing them in by hand), this is usually already done for you. Please check to make sure that your URLs follow the RFC-3986 standard for URIs, the RFC-3987 standard for IRIs, and the XML standard.

/// This plugin will build the sitemap file with $blogid, for example: sitemap_1.xml and sitemap_1.xml.gz. And create a index file called sitemap.xml that using Sitemap Index file rule(http://www.sitemaps.org/protocol.php#index) to include users's single sitemap file.

/// Sample Sitemap index files with blogid, this file called "sitemap.xml"
/*************************************************************************************
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
   <sitemap>
      <loc>http://www.example.com/blog/sitemap_1.xml.gz</loc>
      <lastmod>2004-10-01T18:23:17+00:00</lastmod>
   </sitemap>
   <sitemap>
      <loc>http://www.example.com/blog/sitemap_2.xml.gz</loc>
      <lastmod>2005-01-01</lastmod>
   </sitemap>
</sitemapindex>
*************************************************************************************/
/// Sample XML Sitemap (single file), this file called "sitemap_1.xml" and it will be zipped to .gz file using gzip.
/************************************************************************************
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
   <url>
      <loc>http://www.example.com/blog/</loc>
      <lastmod>2005-01-01</lastmod>
      <changefreq>monthly</changefreq>
      <priority>0.8</priority>
   </url>
</urlset>
*************************************************************************************/

requireModel( "common.setting" );

class HC_XMLSitemap
{
	public $filename = "sitemap.xml";
	protected $buildonchange;
	protected $notify;
	protected $notifyCheck;
	protected $runStart;
	protected $runEnd;

	public function __construct()
	{
		$this->buildonchange = false;
		$this->notify = array('google'=>false, 'yahoo'=>false, 'msn'=>false, 'ask'=>false, 'bing'=>false);
		$this->notifyCheck = array('google'=>false, 'yahoo'=>false, 'msn'=>false, 'ask'=>false, 'bing'=>false);
		$this->runStart = 0;
		$this->runEnd = 0;

		foreach($this->notify as $key => $bool)
		{
			$this->notify[$key] = $this->getNotify($key);
		}

		$this->getBuildOnChange();
	}

	public function getMicrotime()
	{
		list($usec, $sec) = explode(' ', microtime());
		return ((double)$usec + (double)$sec);
	}
	public function getRunOverTime()
	{
		return $this->runEnd - $this->runStart;
	}

	public function setBuildOnChange($onchange = false)
	{
		Setting::setServiceSetting( "SitemapBuildonChange", ($onchange) ? 1 : 0 );
		$this->buildonchange = (boolean) $onchange;
	}
	public function getBuildOnChange() {
		$this->buildonchange = (1 === (int) Setting::getServiceSetting( "SitemapBuildonChange" )) ? true : false;
		return $this->buildonchange;
	}

	public function setNotify($type)
	{
		$type = strtolower($type);
		switch($type) {
			case "google":
				Setting::setServiceSetting( "SitemapNotifyGoogle", ($onchange) ? 1 : 0 );
				$this->notify[$type] = $onchange;
			break;
			case "yahoo":
				Setting::setServiceSetting( "SitemapNotifyYahoo", ($onchange) ? 1 : 0 );
				$this->notify[$type] = $onchange;
			break;
			case "msn":
				Setting::setServiceSetting( "SitemapNotifyMSN", ($onchange) ? 1 : 0 );
				$this->notify[$type] = $onchange;
			break;
			case "ask":
				Setting::setServiceSetting( "SitemapNotifyASK", ($onchange) ? 1 : 0 );
				$this->notify[$type] = $onchange;
			break;
			case "bing":
				Setting::setServiceSetting( "SitemapNotifyBing", ($onchange) ? 1 : 0 );
				$this->notify[$type] = $onchange;
			break;
			default:
		}
	}
	public function getNotify($type) {
		$type = strtolower($type);
		switch($type) {
			case "google":
				return (1 === (int) Setting::getServiceSetting( "SitemapNotifyGoogle" )) ? true : false;
			break;
			case "yahoo":
				return (1 === (int) Setting::getServiceSetting( "SitemapNotifyYahoo" )) ? true : false;
			break;
			case "msn":
				return (1 === (int) Setting::getServiceSetting( "SitemapNotifyMSN" )) ? true : false;
			break;
			case "ask":
				return (1 === (int) Setting::getServiceSetting( "SitemapNotifyASK" )) ? true : false;
			break;
			case "bing":
				return (1 === (int) Setting::getServiceSetting( "SitemapNotifyBing" )) ? true : false;
			break;
			default:
			return false;
		}
	}
    public function getNotifyStatus($type) {
        $type = strtolower($type);
        switch($type) {
			case "google":
			case "yahoo":
			case "msn":
			case "ask":
			case "bing":
                return $this->notifyCheck[$type];
			break;
			default:
                return false;
		}
    }

	public function buildSitemapRootIndex()
    {
        global $database, $blog, $service, $hostURL;

        $blogids = POD::query("SELECT `blogid` FROM `{$database['prefix']}BlogSettings` GROUP BY `blogid`");

        ob_start();
        echo '<?xml version="1.0" encoding="UTF-8"?>', CRLF;
        echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', CRLF;
        while($row = POD::fetch($blogids, 'array')) {
            if(file_exists(ROOT.'/cache/sitemap/'.$row['blogid'].'/'.$this->filename.'.gz')) {
                $fmtime = date("Y-m-d", filemtime(ROOT.'/cache/sitemap/'.$row['blogid'].'/'.$this->filename.'.gz'));
                echo '  <sitemap>', CRLF;
                echo '      <loc>', $hostURL, $service['path'], '/cache/sitemap/', $row['blogid'], '/', $this->filename, '.gz</loc>', CRLF;
                echo '      <lastmod>', $fmtime, '</lastmod>', CRLF;
                echo '  </sitemap>', CRLF;
            }
        }
        echo '</sitemapindex>', CRLF;
        $sitemapIndex = ob_get_contents();
        ob_end_clean();

        $rootIndex = ROOT.'/cache/sitemap/'.$this->filename;
        if (file_exists($rootIndex)) @unlink($rootIndex);
        $fp = fopen($rootIndex, "w+");
        if (fwrite($fp, $sitemapIndex)) {
            fclose($fp);
            chmod($rootIndex, 0644);
            return true;
        } else {
            fclose($fp);
            return false;
        }
    }

    public function buildSitemap()
    {
        global $blog, $database, $service, $hostURL, $blogid;

        $this->runStart = $this->getMicrotime();

        if(!is_dir(ROOT.'/cache/sitemap')) {
            mkdir(ROOT.'/cache/sitemap');
            chmod(ROOT.'/cache/sitemap', 0755);
        }

        if(!is_dir(ROOT.'/cache/sitemap/'.$blogid)) {
            mkdir(ROOT.'/cache/sitemap/'.$blogid);
            chmod(ROOT.'/cache/sitemap/'.$blogid, 0755);
        }

        $userid = getUserId();
        $userBlogUrl = getBlogURL();

        $cacheFile = ROOT.'/cache/sitemap/'.$blogid.'/'.$this->filename;
        $gzcacheFile = $cacheFile.'.gz';
        $rootgzFile = ROOT.'/cache/sitemap/'.$this->filename.'.gz';

       	/* xml-sitemap schemas */
		ob_start();
		echo '<?xml version="1.0" encoding="UTF-8"?>', CRLF;
		echo '<urlset', CRLF;
		echo '	  xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"', CRLF;
		echo '	  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"', CRLF;
		echo '	  xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9', CRLF;
		echo '			http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">', CRLF, CRLF;

		/// build blog root, subroot
		echo '<url>', CRLF;
		echo '  <loc>', $userBlogUrl, '/</loc>', CRLF;
		echo '  <priority>0.8</priority>', CRLF;
		echo '  <changefreq>daily</changefreq>', CRLF;
		echo '</url>', CRLF;
		echo '<url>', CRLF;
		echo '  <loc>', $userBlogUrl, '/cover</loc>', CRLF;
		echo '  <priority>0.8</priority>', CRLF;
		echo '  <changefreq>daily</changefreq>', CRLF;
		echo '</url>', CRLF;
		echo '<url>', CRLF;
		echo '  <loc>', $userBlogUrl, '/notice</loc>', CRLF;
		echo '  <priority>0.8</priority>', CRLF;
		echo '  <changefreq>daily</changefreq>', CRLF;
		echo '</url>', CRLF;
		echo '<url>', CRLF;
		echo '  <loc>', $userBlogUrl, '/tag</loc>', CRLF;
		echo '  <priority>0.8</priority>', CRLF;
		echo '  <changefreq>daily</changefreq>', CRLF;
		echo '</url>', CRLF;
		echo '<url>', CRLF;
		echo '  <loc>', $userBlogUrl, '/location</loc>', CRLF;
		echo '  <priority>0.8</priority>', CRLF;
		echo '  <changefreq>daily</changefreq>', CRLF;
		echo '</url>', CRLF;
		echo '<url>', CRLF;
		echo '  <loc>', $userBlogUrl, '/keylog</loc>', CRLF;
		echo '  <priority>0.8</priority>', CRLF;
		echo '  <changefreq>daily</changefreq>', CRLF;
		echo '</url>', CRLF;
		echo '<url>', CRLF;
		echo '  <loc>', $userBlogUrl, '/guestbook</loc>', CRLF;
		echo '  <priority>0.8</priority>', CRLF;
		echo '  <changefreq>daily</changefreq>', CRLF;
		echo '</url>', CRLF;
		echo '<url>', CRLF;
		echo '  <loc>', $userBlogUrl, '/rss</loc>', CRLF;
		echo '  <priority>0.8</priority>', CRLF;
		echo '  <changefreq>daily</changefreq>', CRLF;
		echo '</url>', CRLF;

        $notices = POD::query("SELECT `id`,`slogan` FROM {$database['prefix']}Entries WHERE `category`=-2 AND `visibility`>=2 AND `userid`={$userid} AND `blogid`={$blogid} ORDER BY `created` ASC");
        while($row = POD::fetch($notices, 'array')) {
            $permalink = ($blog['useSloganOnPost'])? urlencode($row['slogan']) : $row['id'];
            echo '<url>', CRLF;
    		echo '  <loc>', $userBlogUrl, '/notice/', $permalink, '</loc>', CRLF;
    		echo '  <priority>0.8</priority>', CRLF;
    		echo '  <changefreq>daily</changefreq>', CRLF;
    		echo '</url>', CRLF;
        }
        $entries = POD::query("SELECT `id`,`slogan` FROM {$database['prefix']}Entries WHERE `category`>=0 AND `visibility`>=2 AND `userid`={$userid} AND `blogid`={$blogid} ORDER BY `created` ASC");
        while($row = POD::fetch($entries, 'array')) {
            $permalink = ($blog['useSloganOnPost'])? urlencode($row['slogan']) : $row['id'];
            echo '<url>', CRLF;
    		echo '  <loc>', $userBlogUrl, '/', ($blog['useSloganOnPost'] ? 'entry/' : ''), $permalink, '</loc>', CRLF;
    		echo '  <priority>0.5</priority>', CRLF;
    		echo '  <changefreq>daily</changefreq>', CRLF;
    		echo '</url>', CRLF;
        }
        echo '</urlset>', CRLF;

        $sitemapIndexes = ob_get_contents();
        ob_end_clean();

        $this->runEnd = $this->getMicrotime();

        $fp = fopen($cacheFile, "w+");
        if (fwrite($fp, $sitemapIndexes))
        {
            fclose($fp);
            chmod($cacheFile, 0644);
            $zp = gzopen($gzcacheFile, "w9");
            if (gzwrite($zp, $sitemapIndexes))
            {
                gzclose($zp);
                chmod($gzcacheFile, 0644);
                if(copy($gzcacheFile, $rootgzFile)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                gzclose($zp);
                return false;
            }
        } else {
            fclose($fp);
            return false;
        }
    }

    public function notify()
    {
        global $service, $hostURL;

        $notifyURL = urlencode($hostURL.$service['path'].'/cache/sitemap/'.$this->filename);

        if ($this->notify['google'])
        {
            if (function_exists("curl_init"))
            {
                $url = "http://www.google.com/webmasters/tools/ping?sitemap=".$notifyURL;
                $c = curl_init();
                curl_setopt($c, CURLOPT_URL, $url);
                curl_setopt($c, CURLOPT_HEADER, 1);
                curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($c);
                if (!curl_errno($c)) $this->notifyCheck['google'] = true;
                curl_close($c);
            } else {
                $url = "GET /webmasters/tools/ping?sitemap=".$notifyURL." HTTP/1.0\r\n\r\n";
                if( true === ($socket = socket_create(AF_INET, SOCK_STREAM, 0)))
                {
                    socket_connect ( $socket, "www.google.com", 80 );
                    socket_write ( $socket, $url, strlen($url) );
                    if( "200" === substr(socket_read( $socket, 15 ), 9, 3)) $this->notifyCheck['google'] = true;
                    socket_close($socket);
                }
            }
        }

        if ($this->notify['yahoo'])
        {
            if (function_exists("curl_init"))
            {
                $url = "http://search.yahooapis.com/SiteExplorerService/V1/ping?sitemap=".$notifyURL;
                $c = curl_init();
                curl_setopt($c, CURLOPT_URL, $url);
                curl_setopt($c, CURLOPT_HEADER, 1);
                curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($c);
                if (!curl_errno($c)) $this->notifyCheck['yahoo'] = true;
                curl_close($c);
            } else {
                $url = "GET /SiteExplorerService/V1/ping?sitemap=".$notifyURL." HTTP/1.0\r\n\r\n";
                if( true === ($socket = socket_create(AF_INET, SOCK_STREAM, 0)))
                {
                    socket_connect ( $socket, "search.yahooapis.com", 80 );
                    socket_write ( $socket, $url, strlen($url) );
                    if( "200" === substr(socket_read( $socket, 15 ), 9, 3)) $this->notifyCheck['yahoo'] = true;
                    socket_close($socket);
                }
            }
        }

        if ($this->notify['msn'])
        {
            if (function_exists("curl_init"))
            {
                $url = "http://webmaster.live.com/ping.aspx?sitemap=".$notifyURL;
                $c = curl_init();
                curl_setopt($c, CURLOPT_URL, $url);
                curl_setopt($c, CURLOPT_HEADER, 1);
                curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($c);
                if (!curl_errno($c)) $this->notifyCheck['msn'] = true;
                curl_close($c);
            } else {
                $url = "GET /ping.aspx?sitemap=".$notifyURL." HTTP/1.0\r\n\r\n";
                if( true === ($socket = socket_create(AF_INET, SOCK_STREAM, 0)))
                {
                    socket_connect ( $socket, "webmaster.live.com", 80 );
                    socket_write ( $socket, $url, strlen($url) );
                    if( "200" === substr(socket_read( $socket, 15 ), 9, 3)) $this->notifyCheck['msn'] = true;
                    socket_close($socket);
                }
            }
        }

        if ($this->notify['ask'])
        {
            if (function_exists("curl_init"))
            {
                $url = "http://submissions.ask.com/ping?sitemap=".$notifyURL;
                $c = curl_init();
                curl_setopt($c, CURLOPT_URL, $url);
                curl_setopt($c, CURLOPT_HEADER, 1);
                curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($c);
                if (!curl_errno($c)) $this->notifyCheck['ask'] = true;
                curl_close($c);
            } else {
                $url = "GET /ping?sitemap=".$notifyURL." HTTP/1.0\r\n\r\n";
                if( true === ($socket = socket_create(AF_INET, SOCK_STREAM, 0)))
                {
                    socket_connect ( $socket, "submissions.ask.com", 80 );
                    socket_write ( $socket, $url, strlen($url) );
                    if( "200" === substr(socket_read( $socket, 15 ), 9, 3)) $this->notifyCheck['ask'] = true;
                    socket_close($socket);
                }
            }
        }

        if ($this->notify['bing'])
        {
            if (function_exists("curl_init"))
            {
                $url = "http://www.bing.com/webmaster/ping.aspx?sitemap=".$notifyURL;
                $c = curl_init();
                curl_setopt($c, CURLOPT_URL, $url);
                curl_setopt($c, CURLOPT_HEADER, 1);
                curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($c);
                if (!curl_errno($c)) $this->notifyCheck['bing'] = true;
                curl_close($c);
            } else {
                $url = "GET /webmaster/ping.aspx?sitemap=".$notifyURL." HTTP/1.0\r\n\r\n";
                if( true === ($socket = socket_create(AF_INET, SOCK_STREAM, 0)))
                {
                    socket_connect ( $socket, "www.bing.com", 80 );
                    socket_write ( $socket, $url, strlen($url) );
                    if( "200" === substr(socket_read( $socket, 15 ), 9, 3)) $this->notifyCheck['bing'] = true;
                    socket_close($socket);
                }
            }
        }

        return true;
    }
}

function sitemap_buildonchange()
{
	requireModel('common.setting');

	header('Content-Type: text/xml; charset=utf-8');
	if( !Acl::check( array("group.administrators") ) ) {
		getRespondResultPage(0);
		return;
	}
	if( Setting::setServiceSetting("SitemapBuildonChange", (empty($_GET['mode']) ? 0 : 1) ) ) {
		getRespondResultPage(1);
	} else {
		getRespondResultPage(0);
	}
}

function sitemap_notification()
{
	requireModel('common.setting');

	header('Content-Type: text/xml; charset=utf-8');
	if( !Acl::check( array("group.administrators") ) ) {
		getRespondResultPage(0);
		return;
	}
    if(isset($_GET) && isset($_GET['mode']) && isset($_GET['setup']))
    {
        $mode = strtolower($_GET['mode']);
        $setup = ((int) $_GET['setup'] === 1) ? true : false;

        switch($mode)
        {
            case "google":
                Setting::setServiceSetting( "SitemapNotifyGoogle", $setup);
            break;
            case "yahoo":
                Setting::setServiceSetting( "SitemapNotifyYahoo", $setup);
            break;
            case "msn":
                Setting::setServiceSetting( "SitemapNotifyMSN", $setup);
            break;
            case "ask":
                Setting::setServiceSetting( "SitemapNotifyASK", $setup);
            break;
            case "bing":
                Setting::setServiceSetting( "SitemapNotifyBing", $setup);
            break;
        }

        getRespondResultPage(1);
    } else {
        getRespondResultPage(0);
    }
}

function sitemap_rebuildcheck()
{
    global $blog, $blogid;

    $force = (isset($_GET['force']) && (1 === (int) $_GET['force'])) ? true : false;

    $lastModify = (!is_null(Setting::getServiceSetting('TextcubeXMLSitemapAddon'))) ? Setting::getServiceSetting('TextcubeXMLSitemapAddon') : 0;

    $sitemap = new HC_XMLSitemap();
    if ($_SERVER['REQUEST_TIME'] - $lastModify > 600 || $force === true)
    {
        $cacheFile = ROOT."/cache/sitemap/".$blogid."/".$sitemap->filename;
        $gzcacheFile = $cacheFile.'.gz';
        $rootgzFile = ROOT."/cache/sitemap/".$sitemap->filename.'.gz';

        if(file_exists($cacheFile)) unlink($cacheFile);
        if(file_exists($gzcacheFile)) unlink($gzcacheFile);
        if(file_exists($rootgzFile)) unlink($rootgzFile);

        if ($sitemap->buildSitemap())
        {
            if($sitemap->buildSitemapRootIndex())
            {
                $sitemap->notify();
                Setting::setServiceSetting('TextcubeXMLSitemapAddon', $_SERVER['REQUEST_TIME']);
                getRespondResultPage(1);
            } else {
                getRespondResultPage(0);
            }
        } else {
            getRespondResultPage(0);
        }
    }
}
function getRespondResultPage( $error = 0 ) {
    header ("Content-Type: text/xml");
	if ( 1 === $error)
    {
		print ("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<response>\n<error>0</error>\n</response>");
	} elseif (0 === $error)
    {
		print ("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<response>\n<error>1</error>\n</response>");
	}
	exit;
}

function sitemap_lastModify($target)
{
    global $blogid;

    $userid = getUserId();
    $lastModify = (!is_null(Setting::getServiceSetting('TextcubeXMLSitemapAddon'))) ? Setting::getServiceSetting('TextcubeXMLSitemapAddon') : 0;

    $sitemap = new HC_XMLSitemap();
    if ($_SERVER['REQUEST_TIME'] - $lastModify > 600)
    {
        $cacheFile = ROOT."/cache/sitemap/".$blogid."/".$sitemap->filename;
        $gzcacheFile = $cacheFile.'.gz';
        $rootgzFile = ROOT."/cache/sitemap/".$sitemap->filename.'.gz';

        if(file_exists($cacheFile)) unlink($cacheFile);
        if(file_exists($gzcacheFile)) unlink($gzcacheFile);
        if(file_exists($rootgzFile)) unlink($rootgzFile);

        if ($sitemap->buildSitemap())
        {
            if($sitemap->buildSitemapRootIndex())
            {
                $sitemap->notify();
                Setting::setServiceSetting('TextcubeXMLSitemapAddon',$_SERVER['REQUEST_TIME']);
            }
        }
    }
    return $target;
}

function sitemap_Manage()
{
	global $blog, $service, $database, $pluginURL, $hostURL, $blogURL, $blogid;

	$userid = getUserId();

	$version = explode('.', trim((file_get_contents(ROOT.'/cache/CHECKUP'))));
    if ($version[0] < 1 || ($version[0] === '1' && (int) $version[1] < 8)) {
		$_text = _t('Alert ::\r\n - This plugin must be Textcube 1.8 or above. \r\n Please update your textcube and try again.');
		$setupAlert = '<script type="text/javascript">alert("'.$_text.'"); history.go(-1);</script>';
		exit;
	}

    $sitemap = new HC_XMLSitemap();

	$indexonTime = Timestamp::format5(filemtime(ROOT."/cache/sitemap/".$sitemap->filename));
	$gzonTime = Timestamp::format5(filemtime(ROOT."/cache/sitemap/".$blogid."/".$sitemap->filename.".gz"));
?>
	<style type="text/css">
	<!--
	th div {
		text-align: left;
		font-weight: bold;
	}
	.site div {
		text-align: left;
	}
	.site div blockquote {
		margin-left: 2.0em;
	}
	.site ul li {
		line-height: 2.0em;
	}
	ul.status li {
		list-style-type: circle !important;
	}
	button.rebuild-sitemap {
		color: #0066CC;
		cursor: pointer;
	}
	li dfn {
		color: #FF0000;
		font-style: normal;
	}
	-->
	</style>

	<script type="text/javascript">
	//<![CDATA[
	function toggle_buildonchange() {
		try {
			var oo = document.getElementById( 'buildonchange' );
			if( ! oo ) {
				return false;
			}
			oo = oo.checked ? "1" : "0";
			var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/plugin/sitemap/buildonchange?mode=" + oo);
			request.onSuccess = function() {
				PM.removeRequest(this);
				PM.showMessage("<?php echo _t('Data save success.');?>", "center", "bottom");
			}
			request.onError = function() {
				PM.removeRequest(this);
				PM.showErrorMessage("<?php echo _t('Data save error.');?>", "center", "bottom");
			}
			request.send("");
		} catch(e) {
		}
	}
	function toggle_notification(target) {
		try {
			var targetId = 'notify'+target;
			var oo = document.getElementById( targetId );
			if( ! oo ) {
				return false;
			}
			oo = oo.checked ? "1" : "0";
			var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/plugin/sitemap/notification?mode=" + target + "&setup=" + oo);
			request.onSuccess = function() {
				PM.removeRequest(this);
				PM.showMessage("<?php echo _t('Notification update success.');?>", "center", "bottom");
			}
			request.onError = function() {
				PM.removeRequest(this);
				PM.showErrorMessage("<?php echo _t('Notification update error.');?>", "center", "bottom");
			}
			request.send("");
		} catch(e) {
		}
	}
	function toggle_rebuildsitemap() {
		try {
			var request = new HTTPRequest("GET", "<?php echo $blogURL;?>/plugin/sitemap/rebuildsitemap?force=1");
			request.onSuccess = function() {
				PM.removeRequest(this);
				PM.showMessage("<?php echo _t('Sitemap rebuild completed.');?>", "center", "bottom");
			}
			request.onError = function() {
				PM.removeRequest(this);
				PM.showErrorMessage("<?php echo _t('Sitemap no need to rebuild.');?>", "center", "bottom");
			}
			request.send("");
		} catch(e) {
		}
	}
	//]]>
	</script>

	<div id="sitemap-status" calss="part">
		<h2 class="caption"><span class="main-text"><?php echo _t('Status.');?></span></h2>
		<table class="data-inbox" cellspacing="0" cellpadding="0">
			<tbody>
				<tr class="site">
					<td><div><blockquote><ul class="status">
						<li><?php echo _f('Your sitemap was last built on %1', $indexonTime);?></li>
						<li><?php echo _f('Your sitemap(zipped, .gz) was last built on %1', $gzonTime);?></li>
						<li><?php echo _f('The building process took about %1 seconds to complete.', $sitemap->getRunOverTime());?></li>
						<li><?php echo _f('If you changed something on your server or blog, your should %1rebuild the sitemap%2 manually.', '&nbsp;<button class="rebuild-sitemap" onclick="toggle_rebuildsitemap();">','</button>');?></li>
					</ul></blockquote></div></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div id="sitemap-basic-options" calss="part">
		<h2 class="caption"><span class="main-text"><?php echo _t('Basic Options.');?></span></h2>
		<table class="data-inbox" cellspacing="0" cellpadding="0">
			<thead>
				<tr><th><div><?php echo _t('Building mode:');?></div></th></tr>
			</thead>
			<tbody>
				<tr class="site">
					<td><div><blockquote><ul>
						<li><input id="buildonchange" type="checkbox" name="buildonchange" onclick="toggle_buildonchange();"<?php echo ($sitemap->getBuildOnChange()) ? ' checked="checked"' : ''; ?> />&nbsp;<label for="buildonchange"><?php echo _t('Rebuild sitemap if you change the content of your blog.');?></label></li>
					</ul></blockquote></div></td>
				</tr>
			</tbody>
		</table>
		<table class="data-inbox" cellspacing="0" cellpadding="0">
			<thead>
				<tr><th><div><?php echo _t('Update notification:');?></div></th></tr>
			</thead>
			<tbody>
				<tr class="site">
					<td><div><blockquote><ul>
						<li><input id="notifygoogle" type="checkbox" name="notifygoogle" onclick="toggle_notification('google');"<?php echo ($sitemap->getNotify('google')) ? ' checked="checked"' : ''; ?> />&nbsp;<label for="notifyGoogle"><?php echo _t('Notify Google about updates of your blog.');?></label></li>
						<li><input id="notifyyahoo" type="checkbox" name="notifyyahoo" onclick="toggle_notification('yahoo');" <?php echo ($sitemap->getNotify('yahoo')) ? ' checked="checked"' : ''; ?> />&nbsp;<label for="notifyYahoo"><?php echo _t('Notify Yahoo! about updates of your blog.');?></label></li>
						<li><input id="notifymsn" type="checkbox" name="notifymsn" onclick="toggle_notification('msn');" <?php echo ($sitemap->getNotify('msn')) ? ' checked="checked"' : ''; ?> />&nbsp;<label for="notifyMSN"><?php echo _t('Notify MSN about updates of your blog.');?></label></li>
						<li><input id="notifyask" type="checkbox" name="notifyask" onclick="toggle_notification('ask');" <?php echo ($sitemap->getNotify('ask')) ? ' checked="checked"' : ''; ?> />&nbsp;<label for="notifyASK"><?php echo _t('Notify ASK.com about updates of your blog.');?></label></li>
						<li><input id="notifybing" type="checkbox" name="notifybing" onclick="toggle_notification('bing');" <?php echo ($sitemap->getNotify('bing')) ? ' checked="checked"' : ''; ?> />&nbsp;<label for="notifyBing"><?php echo _t('Notify Bing about updates of your blog.');?></label></li>
					</ul></blockquote></div></td>
				</tr>
			</tbody>
		</table>
	</div>
<?php
}
?>
