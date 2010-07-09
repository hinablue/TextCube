<?php
function MT_Cover_getRecentEntries($parameters){
	global $database, $blog, $service, $serviceURL, $suri, $configVal, $defaultURL, $skin;
	requireModel("blog.entry");
	requireModel("blog.tag");
	$data = Setting::fetchConfigVal($configVal);
	$data['coverMode']	= !isset($data['coverMode']) ? 1 : $data['coverMode'];
	if(Misc::isMetaBlog() != true) $data['coverMode'] = 1;
	$data['screenshot']	= !isset($data['screenshot']) ? 1 : $data['screenshot'];
	$data['screenshotSize']	= !isset($data['screenshotSize']) ? 90 : $data['screenshotSize'];
	$data['paging'] = !isset($data['paging'])?'2':$data['paging'];
	$data['contentLength']	= !isset($data['contentLength']) ? 250 : $data['contentLength'];
    $data['cachelifetime'] = !isset($data['cachelifetime']) ? 300 : $data['cachelifetime'];

	if (isset($parameters['preview'])) {
		// preview mode
		$retval = _t('표지에 최신 글 목록을 추가합니다.');
		return htmlspecialchars($retval);
	}
	$entryLength = isset($parameters['entryLength'])?$parameters['entryLength']:10;

	if (!is_dir(ROOT."/cache/thumbnail")) {
		@mkdir(ROOT."/cache/thumbnail");
		@chmod(ROOT."/cache/thumbnail", 0777);
	}
	if (!is_dir(ROOT."/cache/thumbnail/" . getBlogId())) {
		@mkdir(ROOT."/cache/thumbnail/" . getBlogId());
		@chmod(ROOT."/cache/thumbnail/" . getBlogId(), 0777);
	}
	if (!is_dir(ROOT."/cache/thumbnail/" . getBlogId() . "/coverPostThumbnail/")) {
		@mkdir(ROOT."/cache/thumbnail/" . getBlogId() . "/coverPostThumbnail/");
		@chmod(ROOT."/cache/thumbnail/" . getBlogId() . "/coverPostThumbnail/", 0777);
	}

	$page = ($data['paging'] == '1' && !empty($_GET['page'])) ? intval($_GET['page']) : 1;

	$cache = new PageCache;
	$cache->name = 'MT_Cover_RecentPS';
	if($cache->load()) { //If successful loads
		$cache->contents = unserialize($cache->contents);
		// If coverpage is single mode OR coverpage is coverblog and cache is not expired, return cache contents.
		if(($data['coverMode']==1 || $data['coverMode']==2) && array_key_exists($page, $cache->contents) && (Timestamp::getUNIXtime() - $cache->dbContents < $data['cachelifetime'])) {
			return $cache->contents[$page];
		}
	}

	if((Misc::isMetaBlog() == true) && doesHaveOwnership() && $service['type'] != 'single') {
		$visibility = 'AND e.visibility > 1 AND (c.visibility > 1 OR e.category = 0)';
	} else {
		$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 1 AND (c.visibility > 1 OR e.category = 0)';
	}
	$multiple = ($data['coverMode']==2) ? '' : 'e.blogid = ' . getBlogId() . ' AND';
	$privateBlogId = POD::queryColumn("SELECT blogid 
		FROM {$database['prefix']}BlogSettings
		WHERE name = 'visibility'
		AND value < 2");
	if(!empty($privateBlogId)) $privateBlogs = ' AND e.blogid NOT IN ('.implode(',',$privateBlogId).')';
	else $privateBlogs = '';
	list($entries, $paging) = Paging::fetch("SELECT e.blogid, e.id, e.userid, e.title, e.content, e.slogan, e.category, e.published, e.contentformatter, c.label
		FROM {$database['prefix']}Entries e
		LEFT JOIN {$database['prefix']}Categories c ON e.blogid = c.blogid AND e.category = c.id
		WHERE $multiple e.draft = 0 $visibility AND e.category >= 0 $privateBlogs AND published <= {$_SERVER['REQUEST_TIME']} 
		ORDER BY published DESC", $page, $entryLength);

	$html = '';
	foreach ((array)$entries as $entry){
		$tagLabelView = "";
		$blogid = ($data['coverMode']==2) ? $entry['blogid'] : getBlogId();
		$entryTags = getTags($blogid, $entry['id']);
		$defaultURL = getDefaultURL($blogid);
		if (sizeof($entryTags) > 0) {
			$tags = array();
			foreach ($entryTags as $entryTag) {
				$tags[$entryTag['name']] = "<a href=\"{$defaultURL}/tag/" . (Setting::getBlogSettingGlobal('useSloganOnTag',true) ? URL::encode($entryTag['name'],$service['useEncodedURL']) : $entryTag['id']) . '">' . htmlspecialchars($entryTag['name']) . '</a>';
			}
			$tagLabelView = "<div class=\"post_tags\"><span>TAG : </span>".implode(",\r\n", array_values($tags))."</div>";
		}
		
		if (empty($entry['category'])) {
			$entry['label'] = _t('분류없음');
			$entry['link'] = "{$defaultURL}/category";
		} else {
			$entry['link'] = "{$defaultURL}/category/" . (Setting::getBlogSettingGlobal('useSloganOnCategory',true) ? URL::encode($entry['label'],$service['useEncodedURL']) : $entry['category']);
		}
		$permalink = "{$defaultURL}/" . (Setting::getBlogSettingGlobal('useSloganOnPost',true) ? "entry/" . URL::encode($entry['slogan'],$service['useEncodedURL']) : $entry['id']);

		$html .= '<div class="coverpost">'.CRLF;
		if($imageName = MT_Cover_getAttachmentExtract($entry['content'])){
			if(($tempImageSrc = MT_Cover_getImageResizer($blogid, $imageName, $data['screenshotSize'])) && ($data['screenshot'] == 1)){
				$html .= '<div class="img_preview"><a href="'.$permalink.'"><img src="'.$tempImageSrc.'" alt="" /></a></div>'.CRLF;
			}
		}
		$html .= '	<div class="content_box">';
		$html .= '		<h2><a href="'.$permalink.'">'.htmlspecialchars($entry['title']).'</a></h2>'.CRLF;
		$html .= '		<div class="post_info">'.CRLF;
		$html .= '			<span class="category"><a href="'.htmlspecialchars($entry['link']).'">'.htmlspecialchars($entry['label']).'</a></span>'.CRLF;
		$html .= '			<span class="date">'.Timestamp::format5($entry['published']).'</span>'.CRLF;
		$html .= '			<span class="author"><span class="preposition">by </span>'.User::getName($entry['userid']).'</span>'.CRLF;
		$html .= '		</div>'.CRLF;
		$html .= '		<div class="post_content">'.htmlspecialchars(UTF8::lessenAsEm(removeAllTags(stripHTML($entry['content'])), $data['contentLength'])).'</div>'.CRLF;
		$html .=		$tagLabelView;
		$html .= '		<div class="clear"></div>'.CRLF;
		$html .= '	</div>';
		$html .= '</div>'.CRLF;
	}

	if ($data['paging'] == '1') {

		$paging['page'] = $page;
		$paging['total'] = POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Entries e WHERE $multiple e.draft = 0 $visibility AND e.category >= 0");

		$html .= getPagingView($paging, $skin->paging, $skin->pagingItem).CRLF;

		$html .= '<script type="text/javascript">'.CRLF;
		$html .= '//<![CDATA['.CRLF;
		if ($paging['page'] > 1) {
			$html .= 'var prevURL = "'.$paging['url'].'?page='.($paging['page'] - 1).'"'.CRLF;
		}
		if ($paging['page'] < $paging['total']) {
			$html .= 'var nextURL = "'.$paging['url'].'?page='.($paging['page'] + 1).'"'.CRLF;
		}
		$html .= '//]]>'.CRLF;
		$html .= '</script>';
	}

	$target = $html;
	$cache->contents[$page] = $target;
	$cache->contents = serialize($cache->contents);
	$cache->dbContents = Timestamp::getUNIXtime();
	$cache->update();
	unset($cache);

	return $target;
}

function MT_Cover_getRecentEntries_purgeCache($target, $mother) {
    global $database, $configVal;

    $data = Setting::fetchConfigVal($configVal);

    $blogId = getBlogId();
    $timestamp = 0;
    switch($data['purecache']) {
        case "3":
            $timestamp = $_SERVER['REQUEST_TIME'] - 30*60;
        break;
        case "2":
            $timestamp = $_SERVER['REQUEST_TIME'] - 10*60;
        break;
        case "1":
            $timestamp = $_SERVER['REQUEST_TIME'] - 5*60;
        break;
        case "0":
        default:
            $timestamp = 0;
    }

    if(POD::queryCount("SELECT `id` FROM `{$database['prefix']}Entries` WHERE `blogid`={$blogId} AND `modified`>={$timestamp}")) {
        MT_Cover_doRealPurgeCache();
    }

	return $target;
}

function MT_Cover_doRealPurgeCache() {
    $cache = new PageCache;
   	$cache->name = 'MT_Cover_RecentPS';
   	$cache->purge();

    return true;
}

function MT_Cover_getImageResizer($blogid, $filename, $cropSize){
	global $serviceURL;
	$tempFile = null;
	$thumbFilename = $filename;
	$imageURL = "{$serviceURL}/attach/{$blogid}/{$filename}";
	if (extension_loaded('gd')) {	
		if (stristr($filename, 'http://') ) {
			$thumbFilename = MT_Cover_getRemoteImageFilename($filename);
		}

		$thumbnailSrc = ROOT . "/cache/thumbnail/{$blogid}/coverPostThumbnail/th_{$thumbFilename}";
		if (!file_exists($thumbnailSrc)) {
			$imageURL = MT_Cover_getCropProcess($blogid, $filename, $cropSize);
		} else {
			$imageURL = "{$serviceURL}/thumbnail/{$blogid}/coverPostThumbnail/th_{$thumbFilename}";
			$imageInfo = getimagesize($thumbnailSrc);
			if ($imageInfo[0] != $cropSize) {
				$imageURL = MT_Cover_getCropProcess($blogid, $filename, $cropSize);
			}
		}
	} else {
		if(stristr($filename, 'http://') ){
			$imageURL = $filename;
		}
	}
	return $imageURL;
}

function MT_Cover_getCropProcess($blogid, $filename, $cropSize) {
	global $serviceURL;
	$tempFile = null;
	$imageURL = null;
	if(stristr($filename, 'http://') ){
		list($originSrc, $filename, $tempFile) = MT_Cover_getCreateRemoteImage($blogid, $filename);
	} else {
		$originSrc = ROOT . "/attach/{$blogid}/{$filename}";
	}

	$thumbnailSrc = ROOT . "/cache/thumbnail/{$blogid}/coverPostThumbnail/th_{$filename}";
	if (file_exists($originSrc)) {
		requireComponent('Textcube.Function.Image');
		$imageInfo = getimagesize($originSrc);

		$objThumbnail = new Image();
		if ($imageInfo[0] > $imageInfo[1])
			list($tempWidth, $tempHeight) = $objThumbnail->calcOptimizedImageSize($imageInfo[0], $imageInfo[1], NULL, $cropSize);
		else
			list($tempWidth, $tempHeight) = $objThumbnail->calcOptimizedImageSize($imageInfo[0], $imageInfo[1], $cropSize, null);

		$objThumbnail->imageFile = $originSrc;
		if ($objThumbnail->resample($tempWidth, $tempHeight) && $objThumbnail->cropRectBySize($cropSize, $cropSize)) {
			$imageURL = "{$serviceURL}/thumbnail/{$blogid}/coverPostThumbnail/th_{$filename}";
			$objThumbnail->saveAsFile($thumbnailSrc);
		}

		unset($objThumbnail);
		if($tempFile) unlink($originSrc);
	} else {
		$imageURL = null;
	}

	return $imageURL;
}

function MT_Cover_getCreateRemoteImage($blogid, $filename) {
	$fileObject = false;
	$tmpDirectory = ROOT . "/cache/thumbnail/{$blogid}/coverPostThumbnail/";
	$tempFilename = tempnam($tmpDirectory, "remote_");
	$fileObject = @fopen($tempFilename, "w");

	if ($fileObject) {
		$originSrc = $tempFilename;
		$remoteImage = MT_Cover_getHTTPRemoteImage($filename);
		$filename = MT_Cover_getRemoteImageFilename($filename);
		fwrite($fileObject, $remoteImage);
		fclose($fileObject);
		return array($originSrc, $filename, true);
	} else {
		return array(null, null, null);
	}
}

function MT_Cover_getHTTPRemoteImage($remoteImage) {
    $response = '';
	$remoteStuff = parse_url($remoteImage);
	$port = isset($remoteStuff['port']) ? $remoteStuff['port'] : 80;

	$socket = @fsockopen($remoteStuff['host'], $port);
    fputs($socket, "GET " . $remoteStuff['path'] . " HTTP/1.1\r\n");
    fputs($socket, "Host: " . $remoteStuff['host'] . "\r\n");
    fputs($socket, "User-Agent: Mozilla/4.0 (compatible; Textcube)\r\n");
    fputs($socket, "Accept-Encoding: identity\r\n");
    fputs($socket, "Connection: close\r\n");
    fputs($socket, "\r\n");

	while ($buffer = fread($socket, 1024)) {
		$response .= $buffer;
	}

	preg_match('/Content-Length: ([0-9]+)/', $response, $matches);
	return substr($response, - $matches[1]);
}

function MT_Cover_getRemoteImageFilename($filename) {
	$filename = md5($filename) . "." . Misc::getFileExtension($filename);
	return $filename;
}

function MT_Cover_getAttachmentExtract($content) {
	$result = null;
	if (preg_match_all('/\[##_(1R|1L|1C|2C|3C|iMazing|Gallery)\|[^|]*\.(gif|jpg|jpeg|png|bmp|GIF|JPG|JPEG|PNG|BMP)\|.*_##\]/si', $content, $matches)) {
		$split = explode("|", $matches[0][0]);
		$result = $split[1];
	} else if (preg_match_all('/<img[^>]+?src=("|\')?([^\'">]*?)("|\')/si', $content, $matches)) {
		if( stristr($matches[2][0], 'http://') ) {
			$result = $matches[2][0];
		} else if ( stristr($matches[2][0], '[##_ATTACH_PATH_##]')) {
            $result = str_replace('[##_ATTACH_PATH_##]/', '', $matches[2][0]);
        }
	}
	return $result;
}

function MT_Cover_getRecentEntryStyle($target){
	global $pluginURL, $configVal;
	$data = Setting::fetchConfigVal($configVal);
	$data['cssSelect']	= !isset($data['cssSelect'])?1:$data['cssSelect'];
	if($data['cssSelect'] == 1){
		$target .= '<link rel="stylesheet" media="screen" type="text/css" href="' . $pluginURL . '/style.css" />' . CRLF;
	}
	return $target;
}

function MT_Cover_getRecentEntries_DataSet($DATA){
	$cfg = Setting::fetchConfigVal($DATA);

	MT_Cover_getRecentEntries_purgeCache(null, null);
	return true;
}

function MT_Cover_getRecentEntries_ConfigOut($plugin) {

	$manifest = NULL;

	$manifest .= '<?xml version="1.0" encoding="utf-8"?>'.CRLF;
	$manifest .= '<config dataValHandler="MT_Cover_getRecentEntries_DataSet" >'.CRLF;
	$manifest .= '	<window width="500" height="345" />'.CRLF;
	$manifest .= '	<fieldset legend="Cover list setup">'.CRLF;
	$manifest .= '		<field title="List mode :" name="coverMode" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1" checked="checked"><![CDATA[Single user&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2">Multi user</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="Apply Pagination :" name="paging" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1"><![CDATA[Apply&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2" checked="checked">Not apply</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="Screenshot :" name="screenshot" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1" checked="checked"><![CDATA[Apply&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2">Not apply</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="Screenshot size:" name="screenshotSize" type="text" size="5" value="90" />'.CRLF;
	$manifest .= '		<field title="Apply CSS :" name="cssSelect" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1" checked="checked"><![CDATA[Apply&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2">Not apply</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="Content length :" name="contentLength" type="text" size="5" value="250" />'.CRLF;
    $manifest .= '		<field title="Cache setting :" name="purecache" type="radio"  >'.CRLF;
	$manifest .= '			<op value="0" checked="checked"><![CDATA[Real time&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="1"><![CDATA[5 &nbsp;]]></op>'.CRLF;
    $manifest .= '			<op value="2"><![CDATA[10 &nbsp;]]></op>'.CRLF;
    $manifest .= '			<op value="3"><![CDATA[30 mins.&nbsp;]]></op>'.CRLF;
    $manifest .= '		</field>'.CRLF;
    $manifest .= '      <field title="Cache Life Time(sec) :" name="cachelifetime" type="text" size="5" value="300" />'.CRLF;
	$manifest .= '	</fieldset>'.CRLF;
	$manifest .= '</config>'.CRLF;

	return $manifest;
}

function MT_Cover_getRecentEntries_ConfigOut_zh_TW($plugin) {
	$manifest = NULL;

	$manifest .= '<?xml version="1.0" encoding="utf-8"?>'.CRLF;
	$manifest .= '<config dataValHandler="MT_Cover_getRecentEntries_DataSet" >'.CRLF;
	$manifest .= '	<window width="500" height="345" />'.CRLF;
	$manifest .= '	<fieldset legend="封面清單設定">'.CRLF;
	$manifest .= '		<field title="清單模式 :" name="coverMode" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1" checked="checked"><![CDATA[單一使用者&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2">多位使用者</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="使用分頁 :" name="paging" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1"><![CDATA[使用&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2" checked="checked">不使用</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="使用截圖 :" name="screenshot" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1" checked="checked"><![CDATA[使用&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2">不使用</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="截圖尺寸 :" name="screenshotSize" type="text" size="5" value="90" />'.CRLF;
	$manifest .= '		<field title="使用 CSS :" name="cssSelect" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1" checked="checked"><![CDATA[使用&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2">不使用</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="內文長度 :" name="contentLength" type="text" size="5" value="250" />'.CRLF;
    $manifest .= '		<field title="快取設定 :" name="purecache" type="radio"  >'.CRLF;
	$manifest .= '			<op value="0" checked="checked"><![CDATA[即時&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="1"><![CDATA[每 5&nbsp;]]></op>'.CRLF;
    $manifest .= '			<op value="2"><![CDATA[10&nbsp;]]></op>'.CRLF;
    $manifest .= '			<op value="3"><![CDATA[30 分鐘]]></op>'.CRLF;
    $manifest .= '		</field>'.CRLF;
    $manifest .= '      <field title="快取存活時間(秒) :" name="cachelifetime" type="text" size="5" value="300" />'.CRLF;
	$manifest .= '	</fieldset>'.CRLF;
	$manifest .= '</config>'.CRLF;

	return $manifest;
}

function MT_Cover_getRecentEntries_ConfigOut_zh_CN($plugin) {
	$manifest = NULL;

	$manifest .= '<?xml version="1.0" encoding="utf-8"?>'.CRLF;
	$manifest .= '<config dataValHandler="MT_Cover_getRecentEntries_DataSet" >'.CRLF;
	$manifest .= '	<window width="500" height="345" />'.CRLF;
	$manifest .= '	<fieldset legend="封面清单设定">'.CRLF;
	$manifest .= '		<field title="清单模式 :" name="coverMode" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1" checked="checked"><![CDATA[单一使用者&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2">多位使用者</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="使用分页 :" name="paging" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1"><![CDATA[使用&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2" checked="checked">不使用</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="使用截图 :" name="screenshot" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1" checked="checked"><![CDATA[使用&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2">不使用</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="截图尺寸 :" name="screenshotSize" type="text" size="5" value="90" />'.CRLF;
	$manifest .= '		<field title="使用 CSS :" name="cssSelect" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1" checked="checked"><![CDATA[使用&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2">不使用</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="内文长度 :" name="contentLength" type="text" size="5" value="250" />'.CRLF;
    $manifest .= '		<field title="快取设定 :" name="purecache" type="radio"  >'.CRLF;
	$manifest .= '			<op value="0" checked="checked"><![CDATA[即时&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="1"><![CDATA[每 5&nbsp;]]></op>'.CRLF;
    $manifest .= '			<op value="2"><![CDATA[10&nbsp;]]></op>'.CRLF;
    $manifest .= '			<op value="3"><![CDATA[30 分钟]]></op>'.CRLF;
    $manifest .= '		</field>'.CRLF;
    $manifest .= '      <field title="快取存活时间(秒) :" name="cachelifetime" type="text" size="5" value="300" />'.CRLF;
	$manifest .= '	</fieldset>'.CRLF;
	$manifest .= '</config>'.CRLF;

	return $manifest;
}
?>
