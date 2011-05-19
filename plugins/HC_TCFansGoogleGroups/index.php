<?php

function TCFans_Google_Group_Dashboard($target)
{
	global $service, $blogURL, $pluginURL, $blog;
	requireComponent("Eolin.PHP.Core");
	
	if (!isset($blog['blogLanguage'])) {
		$blog['blogLanguage'] = $service['language'];
	}
	// Locale language file support.
	$text = array();
	switch($blog['blogLanguage']) {
		case "zh-TW":
			$text[0] = '最新回覆';
			$text[1] = '最新主題';
		break;
		case "zh-CN":
			$text[0] = '最新回复';
			$text[1] = '最新主题';
		break;
		default:
			$text[0] = 'Recent reply';
			$text[1] = 'Recent Topic';
	}

	ob_start();
	?>
	<script type="text/javascript">
		function selectTCFansTab(obj1, obj2) {
			document.getElementById(obj1).className = "selected-tab";
			document.getElementById(obj2).className = "no-selected-tab";
		}
	</script>
	<div class="TCFans_center">
		<div class="TCFans_layout_menu_tab">
			<ul class="TCFans-tabs-box">
				<li id="TCFans-tab-1" class="selected-tab" onclick="selectTCFansTab('TCFans-tab-1', 'TCFans-tab-2');hideLayer('HC_TCFans2');showLayer('HC_TCFans1');"><?=$text[0];?></li>
				<li id="TCFans-tab-2" class="no-selected-tab" onclick="selectTCFansTab('TCFans-tab-2', 'TCFans-tab-1');hideLayer('HC_TCFans1');showLayer('HC_TCFans2');"><?=$text[1];?></li>
			</ul>
		</div>
        <div class="TCFans_layout_container">
	<?
	$target .= ob_get_contents();
	ob_end_clean();

	$recentRssURLS = array('http://groups.google.com/group/textcubefans/feed/rss_v2_0_msgs.xml', 'http://groups.google.com/group/textcubefans/feed/rss_v2_0_topics.xml');
	$OL_CNT = 0;
	foreach ($recentRssURLS as $recentRssURL){
		$OL_CNT++;
		if(!is_null(getServiceSetting('GoogleGroupTCFans'.$OL_CNT))) {
			$AddonEntries = unserialize(getServiceSetting('GoogleGroupTCFans'.$OL_CNT));
			$result = 0;
		} else {
			list($result, $feed, $xml, $AddonEntries) = HC_TCFans_getRemoteFeed($recentRssURL);
			setServiceSetting('GoogleGroupTCFans'.$OL_CNT,serialize($AddonEntries));
		}
		if ($result == 0) {
			if (count($AddonEntries) > 0) {
				$target .= '<ol id="HC_TCFans' . $OL_CNT . '" style="display:' . ($OL_CNT == 1 ? 'block' : 'none') . ';" class="TCFans_list">'.CRLF;
				$i = 0;
				foreach($AddonEntries as $item) {
					$target .= '<li>'.CRLF;
					$target .= '	<div class="entryInfo">'.CRLF;
					$target .= '		<span class="date">' . Timestamp::formatDate($item['written']) . '</span> ' . UTF8::lessenAsEm(htmlspecialchars($item['author']),33) . '<br/>'.CRLF;
					$target .= '		<a href="'. $item['permalink'] .'" onclick="return openLinkInNewWindow(this);" >'.CRLF;
					$target .= '			<span class="title">' . UTF8::lessenAsEm(htmlspecialchars($item['title']),33) . '</span><br />'.CRLF;
					$target .= '			<span class="description">' . UTF8::lessenAsEm(htmlspecialchars($item['description']),33) . '</span>'.CRLF;					
					$target .= '		</a>'.CRLF;
					$target .= '	</div>'.CRLF;
					$target .= '</li>'.CRLF;
					if($i>3) break;
					else $i++;
				}
				$target .= '</ol>'.CRLF;
			} else {
				$target .= '<ol id="HC_TCFans' . $OL_CNT . '" style="display:' . ($OL_CNT == 1 ? 'block' : 'none') . ';" class="TCFans_list">'.CRLF;
				$target .= '	<p style="height: 90px; padding-top: 65px; text-align: center;">'._t('데이터가 없습니다.').'</p>';
				$target .= '</ol>'.CRLF;
			}
		} else {
			$target .= '<ol id="HC_TCFans' . $OL_CNT . '" style="display:' . ($OL_CNT == 1 ? 'block' : 'none') . ';" class="TCFans_list">'.CRLF;
			$target .= '	<p style="height: 90px; padding-top: 65px; text-align: center;">'._t('데이터를 가져올 수 없습니다.').'<br />'._t('잠시 후 다시 시도해 주십시오.').'</p>'.CRLF;
			$target .= '</ol>'.CRLF;
		}
	}
	$target .= "</div>\n</div>";
	unset($feed);
	unset($xmls);
	unset($AddonEntries);
	unset($text);
	return $target;
}

function fixed_chineseTimeStamp($timestamp)
{
	$timestamp = str_replace('月', '', $timestamp);
	$timestamp = str_replace('UT', 'UTC', $timestamp);
	$month_zh = array('一', '二', '三','四','五','六','七','八','九','十','十一','十二');
	$month_en = array('Jan', 'Feb', 'Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
	$timestamp = str_replace($month_zh, $month_en, $timestamp);
	
	return $timestamp;
}

function HC_TCFans_getRemoteFeed($url) {
	global $service;
	$xml = fireEvent('GetRemoteFeed', null, $url);
	if (empty($xml)) {
		requireComponent('Eolin.PHP.HTTPRequest');
		$request = new HTTPRequest($url);
		$request->timeout = 3;
		if (!$request->send())
			return array(2, null, null, null);
		$xml = $request->responseText;
	}
	$feed = array('xmlURL' => $url);
	$xmls = new XMLStruct();
	if (!$xmls->open($xml, $service['encoding'])) {
		if(preg_match_all('/<link .*?rel\s*=\s*[\'"]?alternate.*?>/i', $xml, $matches)) {
			foreach($matches[0] as $link) {
				$attributes = getAttributesFromString($link);
				if(isset($attributes['href'])) {
					$urlInfo = parse_url($url);
					$rssInfo = parse_url($attributes['href']);
					$rssURL = false;
					if(isset($rssInfo['scheme']) && $rssInfo['scheme'] == 'http')
						$rssURL = $attributes['href'];
					else if(isset($rssInfo['path'])) {
						if($rssInfo['path']{0} == '/')
							$rssURL = "{$urlInfo['scheme']}://{$urlInfo['host']}{$rssInfo['path']}";							
						else
							$rssURL = "{$urlInfo['scheme']}://{$urlInfo['host']}".(isset($urlInfo['path']) ? rtrim($urlInfo['path'], '/') : '').'/'.$rssInfo['path'];
					}
					if($rssURL && $url != $rssURL)
						return HC_TCFans_getRemoteFeed($rssURL);
				}
			}
		}
		return array(3, null, null, null);
	}
	if ($xmls->getAttribute('/rss', 'version')) {
		$feed['blogURL'] = $xmls->getValue('/rss/channel/link');
		$feed['title'] = $xmls->getValue('/rss/channel/title');
		$feed['description'] = $xmls->getValue('/rss/channel/description');
		if (Validator::language($xmls->getValue('/rss/channel/language')))
			$feed['language'] = $xmls->getValue('/rss/channel/language');
		else if (Validator::language($xmls->getValue('/rss/channel/dc:language')))
			$feed['language'] = $xmls->getValue('/rss/channel/dc:language');
		else
			$feed['language'] = 'en-US';
		$feed['modified'] = gmmktime();
		$items = array();
		for ($i = 0; $link = $xmls->getValue("/rss/channel/item[$i]/link"); $i++) {
			$item = array('permalink' => rawurldecode($link));
			$item['title'] = $xmls->getValue("/rss/channel/item[$i]/title");
			$item['image'] = $xmls->getValue("/rss/channel/item[$i]/image");
			$item['author'] = $xmls->getValue("/rss/channel/item[$i]/author");
			$item['author'] = str_replace(array('(',')'), array('',''), $item['author']);
			$item['description'] = $xmls->getValue("/rss/channel/item[$i]/description");
			if ($xmls->getValue("/rss/channel/item[$i]/pubDate"))
				$item['written'] = parseDate(fixed_chineseTimeStamp($xmls->getValue("/rss/channel/item[$i]/pubDate")));
			else if ($xmls->getValue("/rss/channel/item[$i]/dc:date"))
				$item['written'] = parseDate(fixed_chineseTimeStamp($xmls->getValue("/rss/channel/item[$i]/dc:date")));
			else
				$item['written'] = 0;
			array_push($items, $item);
		}
	} else
		return array(3, null, null, null);

	$feed['blogURL'] = POD::escapeString(UTF8::lessenAsEncoding(UTF8::correct($feed['blogURL'])));
	$feed['title'] = POD::escapeString(UTF8::lessenAsEncoding(UTF8::correct($feed['title'])));
	$feed['description'] = POD::escapeString(UTF8::lessenAsEncoding(UTF8::correct(stripHTML($feed['description']))));

	return array(0, $feed, $xml, $items);
}

function TCFans_Google_Group($parameters)
{
	global $blog, $service;
	
	if (!isset($blog['blogLanguage'])) {
		$blog['blogLanguage'] = $service['language'];
	}
	// Locale language file support.
	$text = array();
	switch($blog['blogLanguage']) {
		case "zh-TW":
			$text[0] = 'Google 網上論壇';
			$text[1] = '訂閱 TextCube 中文論壇';
			$text[2] = '電子郵件：';
			$text[3] = '訂閱';
			$text[4] = '造訪此群組';
			$img = '_zh-TW';
		break;
		case "zh-CN":
			$text[0] = 'Google 网上论坛';
			$text[1] = '订阅 TextCube 中文论坛';
			$text[2] = '电子邮件：';
			$text[3] = '订阅';
			$text[4] = '造访此群组';
			$img = '_zh-CN';
		break;
		default:
			$text[0] = 'Google Group';
			$text[1] = 'Subscribes TextCube Chinese Group';
			$text[2] = 'E-mail :';
			$text[3] = 'Subscribes';
			$text[4] = 'Visits this group';
			$img = '';
	}
	
	$__retval = "<h3>{$text[0]}</h3><div style=\"background-color: #fff; color:#666; padding: 5px; width: auto; height: auto; border: 1px solid #bababa;\"><form action=\"http://groups.google.com/group/textcubefans/boxsubscribe\">
	<input type=\"hidden\" name=\"hl\" value=\"{$blog['blogLanguage']}\" />
	<table border=\"0\" cellpadding=\"5\" cellspacing=\"0\" width=\"100%\">
	<tr><td><img src=\"http://groups.google.com.tw/groups/img/3nb/groups_bar{$img}.gif\" height=\"26\" width=\"132\" alt=\"{$text[0]}\" /></td></tr>
	<tr><td style=\"padding-left: 5px\"> <strong>{$text[1]}</strong> </td></tr>
	<tr><td style=\"padding-left: 5px;\"> {$text[2]} <input type=\"text\" name=\"email\" /><input type=\"submit\" name=\"sub\" value=\"{$text[3]}\" /></td></tr>
	<tr><td align=\"right\"> <a href=\"http://groups.google.com/group/textcubefans?hl={$blog['blogLanguage']}\">{$text[4]}</a> </td></tr>
	</table></form></div><br />";

	unset($text);
	unset($img);
	
	return $__retval;
}

function TCFans_Google_Group_Dashboard_sweep($target, $mother = true) {
	requireModel('common.setting'); 
	removeServiceSetting('GoogleGroupTCFans1'); 
	removeServiceSetting('GoogleGroupTCFans2');
	
	return $target;
}
?>
