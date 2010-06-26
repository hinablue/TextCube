<?php

function BlogIcon_Custom($mother = array(), $size) {
	global $database, $pluginURL, $serviceURL;
	requireComponent('Textcube.Function.misc');
	
	$replier = ($mother['replier']!=NULL && !empty($mother['replier'])) ? intval($mother['replier']) : 0;
	if ($replier>0) {
		$blogURI = getBlogURL();
		$row = POD::queryRow("SELECT image FROM {$database['prefix']}TeamUserSettings WHERE blogid =".getBlogId()." AND userid={$replier}");
		if(!empty($row['image'])) {
			$imageSrc = "{$serviceURL}/attach/".getBlogId()."/team/".$row['image'];
		} else {
			$imageSrc = "{$blogURI}/index.gif";
		}
		$imageStr = "<a href=\"{$blogURI}\"><img src=\"{$imageSrc}\" alt=\"\" width=\"{$size}\" height=\"{$size}\" onerror=\"this.src = '{$pluginURL}/images/default.png'\" border=\"0\" /></a>";
	} else {
		if (!empty($mother['homepage'])) {
			$slash = ($mother['homepage']{strlen($mother['homepage']) - 1} == '/' ? '' : '/');
			$imageStr = "<a href=\"{$mother['homepage']}\" target=\"_blank\"><img src=\"{$mother['homepage']}{$slash}index.gif\" alt=\"\" width=\"{$size}\" height=\"{$size}\" onerror=\"this.src = '{$pluginURL}/images/default.png'\" border=\"0\" /></a>";
		} else {
			$imageStr = "<img src=\"{$pluginURL}/images/default.png\" alt=\"\" width=\"{$size}\" height=\"{$size}\" border=\"0\" />";
		}
	}
	
	return $imageStr;
}

function BlogIcon_main($target, $mother) {  
	global $configVal, $pluginURL;
	requireComponent('Textcube.Function.misc');
	$data = setting::fetchConfigVal( $configVal);

	if (!is_null($data))	$ico_size = $data['ico_size'];
	if (!isset($ico_size) || is_null($ico_size))	$ico_size = 16;
	
	if ($mother['secret'] == 1) {
		if (empty($mother['homepage'])) {
			$imageStr = "<img src=\"{$pluginURL}/images/secret.png\" alt=\"\" width=\"{$ico_size}\" height=\"{$ico_size}\" />";
		} else {
			$imageStr = BlogIcon_Custom($mother, $ico_size);
		}
	} else {
		$imageStr = BlogIcon_Custom($mother, $ico_size);
	}
	
	return "{$imageStr} {$target}";
}

function BlogIcon_ConfigOut_ko($plugin) {
	global $service, $pluginURL;
	
	$manifest = NULL;
	
	$manifest .= '<?xml version="1.0" encoding="utf-8"?>'.CRLF;
	$manifest .= '<config dataValHandler="">'.CRLF;
	$manifest .= '	<window width="500" height="310" />'.CRLF;
	$manifest .= '		<fieldset legend="원하시는 블로그 아이콘 크기를 선택해주세요.">'.CRLF;
	$manifest .= '		<field title="블로그 아이콘을 " name="ico_size" type="select">'.CRLF;
	$manifest .= '			<op value="16" checked="checked">16x16 크기로 출력</op>'.CRLF;
	$manifest .= '			<op value="32">32x32 크기로 출력</op>'.CRLF;
	$manifest .= '			<op value="48">48x48 크기로 출력</op>'.CRLF;
	$manifest .= '			<caption>'.CRLF;
	$manifest .= '				<![CDATA['.CRLF;
	$manifest .= '				단위는 px, 기본값은 16x16 입니다.<br />'.CRLF;
	$manifest .= '				환경설정에서 블로그 아이콘을 업로드 해야 아이콘이 출력됩니다.'.CRLF;
	$manifest .= '				<p>'.CRLF;
	$manifest .= '					<img src="'.$pluginURL.'/images/default.png" alt="16x16 예제" width="16" height="16" /> (16x16),'.CRLF;
	$manifest .= '					<img src="'.$pluginURL.'/images/default.png" alt="32x32 예제" width="32" height="32" /> (32x32),'.CRLF;
	$manifest .= '					<img src="'.$pluginURL.'/images/default.png" alt="48x48 예제" width="48" height="48" /> (48x48)'.CRLF;
	$manifest .= '				</p>'.CRLF;
	$manifest .= '				]]>'.CRLF;
	$manifest .= '			</caption>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '	</fieldset>'.CRLF;
	$manifest .= '</config>';
	
	return $manifest;
}

function BlogIcon_ConfigOut_en($plugin) {
	global $service, $pluginURL;
	
	$manifest = NULL;
	
	$manifest .= '<?xml version="1.0" encoding="utf-8"?>'.CRLF;
	$manifest .= '<config dataValHandler="">'.CRLF;
	$manifest .= '	<window width="500" height="310" />'.CRLF;
	$manifest .= '		<fieldset legend="Select a size of blog icons for displaying.">'.CRLF;
	$manifest .= '		<field title="Size : " name="ico_size" type="select">'.CRLF;
	$manifest .= '			<op value="16" checked="checked">16x16</op>'.CRLF;
	$manifest .= '			<op value="32">32x32</op>'.CRLF;
	$manifest .= '			<op value="48">48x48</op>'.CRLF;
	$manifest .= '			<caption>'.CRLF;
	$manifest .= '				<![CDATA['.CRLF;
	$manifest .= '				A defualt value is 16x16 (pixel by pixel).<br />'.CRLF;
	$manifest .= '				<p>'.CRLF;
	$manifest .= '					<img src="'.$pluginURL.'/images/default.png" alt="16x16 Example" width="16" height="16" /> (16x16),'.CRLF;
	$manifest .= '					<img src="'.$pluginURL.'/images/default.png" alt="32x32 Example" width="32" height="32" /> (32x32),'.CRLF;
	$manifest .= '					<img src="'.$pluginURL.'/images/default.png" alt="48x48 Example" width="48" height="48" /> (48x48)'.CRLF;
	$manifest .= '				</p>'.CRLF;
	$manifest .= '				]]>'.CRLF;
	$manifest .= '			</caption>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '	</fieldset>'.CRLF;
	$manifest .= '</config>';
	
	return $manifest;
}

function BlogIcon_ConfigOut_tw($plugin) {
	global $service, $pluginURL;
	
	$manifest = NULL;
	
	$manifest .= '<?xml version="1.0" encoding="utf-8"?>'.CRLF;
	$manifest .= '<config dataValHandler="">'.CRLF;
	$manifest .= '	<window width="500" height="310" />'.CRLF;
	$manifest .= '		<fieldset legend="選擇顯示的 Blog Icon 大小">'.CRLF;
	$manifest .= '		<field title="尺寸 : " name="ico_size" type="select">'.CRLF;
	$manifest .= '			<op value="16" checked="checked">16x16</op>'.CRLF;
	$manifest .= '			<op value="32">32x32</op>'.CRLF;
	$manifest .= '			<op value="48">48x48</op>'.CRLF;
	$manifest .= '			<caption>'.CRLF;
	$manifest .= '				<![CDATA['.CRLF;
	$manifest .= '				預設顯示 16x16 (畫素).<br />'.CRLF;
	$manifest .= '				<p>'.CRLF;
	$manifest .= '					<img src="'.$pluginURL.'/images/default.png" alt="16x16 Example" width="16" height="16" /> (16x16),'.CRLF;
	$manifest .= '					<img src="'.$pluginURL.'/images/default.png" alt="32x32 Example" width="32" height="32" /> (32x32),'.CRLF;
	$manifest .= '					<img src="'.$pluginURL.'/images/default.png" alt="48x48 Example" width="48" height="48" /> (48x48)'.CRLF;
	$manifest .= '				</p>'.CRLF;
	$manifest .= '				]]>'.CRLF;
	$manifest .= '			</caption>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '	</fieldset>'.CRLF;
	$manifest .= '</config>';
	
	return $manifest;
}

function BlogIcon_ConfigOut_cn($plugin) {
	global $service, $pluginURL;
	
	$manifest = NULL;
	
	$manifest .= '<?xml version="1.0" encoding="utf-8"?>'.CRLF;
	$manifest .= '<config dataValHandler="">'.CRLF;
	$manifest .= '	<window width="500" height="310" />'.CRLF;
	$manifest .= '		<fieldset legend="选择显示的 Blog Icon 大小">'.CRLF;
	$manifest .= '		<field title="尺寸 : " name="ico_size" type="select">'.CRLF;
	$manifest .= '			<op value="16" checked="checked">16x16</op>'.CRLF;
	$manifest .= '			<op value="32">32x32</op>'.CRLF;
	$manifest .= '			<op value="48">48x48</op>'.CRLF;
	$manifest .= '			<caption>'.CRLF;
	$manifest .= '				<![CDATA['.CRLF;
	$manifest .= '				默认显示 16x16 (画素).<br />'.CRLF;
	$manifest .= '				<p>'.CRLF;
	$manifest .= '					<img src="'.$pluginURL.'/images/default.png" alt="16x16 Example" width="16" height="16" /> (16x16),'.CRLF;
	$manifest .= '					<img src="'.$pluginURL.'/images/default.png" alt="32x32 Example" width="32" height="32" /> (32x32),'.CRLF;
	$manifest .= '					<img src="'.$pluginURL.'/images/default.png" alt="48x48 Example" width="48" height="48" /> (48x48)'.CRLF;
	$manifest .= '				</p>'.CRLF;
	$manifest .= '				]]>'.CRLF;
	$manifest .= '			</caption>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '	</fieldset>'.CRLF;
	$manifest .= '</config>';
	
	return $manifest;
}
?>
