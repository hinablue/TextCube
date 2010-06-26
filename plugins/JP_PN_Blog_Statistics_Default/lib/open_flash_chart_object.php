<?php
function open_flash_chart_object( $width, $height, $url, $use_swfobject=true ) {
	global $pluginURL;
	//
    // I think we may use swfobject for all browsers,
    // not JUST for IE...
    //
    //$ie = strstr(getenv('HTTP_USER_AGENT'), 'MSIE');
    
    //
    // escape the & and stuff:
    //
	global $open_flash_chart_seqno;
	//
	// if there are more than one charts on the
	// page, give each a different ID
	//

	$obj_id = 'chart';
	$div_name = 'flashcontent';
	
	if( !isset( $open_flash_chart_seqno ) )
	{
		$open_flash_chart_seqno = 1;
		echo '<script type="text/javascript" src="'.$pluginURL.'/lib/js/swfobject.js"></script>';
	}
	else
	{
		$open_flash_chart_seqno++;
		$obj_id .= '_'. $open_flash_chart_seqno;
		$div_name .= '_'. $open_flash_chart_seqno;
	}

	$url = urlencode($url);
	$tmpData = substr($url, -13);
	$dataCheck = explode("_", $tmpData);
	if($dataCheck[0] != "grpNoData"){
		
		if( $use_swfobject )
		{
			// Using library for auto-enabling Flash object on IE, disabled-Javascript proof
			
			echo '<div id="'. $div_name .'"></div>'.CRLF;
			
			echo '<script type="text/javascript">'.CRLF;
			echo '	var flashvars = {};'.CRLF;
			echo '	var params = {};'.CRLF;
			echo '	var attributes = {};'.CRLF;
			echo '	flashvars.data = "'. $url . '";'.CRLF;
			echo '	params.allowScriptAccess = "sameDomain";'.CRLF;
			echo '	params.quality = "high";'.CRLF;
			echo '	params.wmode = "transparent";'.CRLF;
			echo '	attributes.id = "ofc";'.CRLF;
			echo '	attributes.name = "ofc";'.CRLF;
			echo '	swfobject.embedSWF("'.$pluginURL.'/lib/open-flash-chart.swf", "'. $div_name .'", "'. $width . '", "' . $height . '", "9.0.0", "'.$pluginURL.'/lib/expressInstall.swf", flashvars, params, attributes);'.CRLF;
			echo '</script>'.CRLF;
			echo '<noscript>'.CRLF;
		}

		echo '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" '.CRLF;
		echo 'width="' . $width . '" height="' . $height . '" id="ie_'. $obj_id .'" align="middle">'.CRLF;
		echo '<param name="allowScriptAccess" value="sameDomain" />'.CRLF;
		echo '<param name="movie" value="'.$pluginURL.'/lib/open-flash-chart.swf?width='. $width .'&height='. $height . '&data='. $url .'" />'.CRLF;
		echo '<param name="quality" value="high" />'.CRLF;
		echo '<param name="bgcolor" value="#FFFFFF" />'.CRLF;
		echo '<param name="wmode" value="transparent" />'.CRLF;
		echo '<embed src="'.$pluginURL.'/lib/open-flash-chart.swf?data=' . $url .'" quality="high" bgcolor="#FFFFFF" width="'. $width .'" height="'. $height .'" name="open-flash-chart" align="middle" allowScriptAccess="sameDomain" wmode="transparent" '.CRLF;
		echo 'type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" id="'. $obj_id .'"/>'.CRLF;
		echo '</object>'.CRLF;

		if ( $use_swfobject ) {
			echo '</noscript>'.CRLF;
		}
	}else{
		echo '<div id="'. $div_name .'">';
		echo '	<div style="width:' . $width . 'px;height:' . $height . 'px;"><img src="'.$pluginURL.'/images/no_data_' . $dataCheck[1] . '.gif" width="' . $width . '" height="' . $height . '" border="0" /></div>';
		echo '</div>';
	}
	flush();
}
?>