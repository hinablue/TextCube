<?php
function showFlashCounter($parameter){
	global $blog, $service, $pluginURL, $blogid, $configVal;
	
	if (!isset($blog['blogLanguage'])) {
		$blog['blogLanguage'] = $service['language'];
	}
	// Locale language file support.
	switch($blog['blogLanguage']) {
		case "zh-TW":
			$retval = 'Flash 計數器預覽。';
		break;
		case "zh-CN":
			$retval = 'Flash 计数器预览。';
		break;
		default:
			$retval = "블로그 카운트 기본 형태를 플래쉬 카운터로 보여 줍니다.";
	}
	if (isset($parameter['preview'])) {
		// preview mode

		return $retval;
	}
	requireComponent('Textcube.Function.misc');
	$data = misc::fetchConfigVal($configVal);
	if(is_null($data)){
		$data['maxdate']	 = 5;
		$data['flashsize']	 = 150;
		$data['flashalign']	 = "left";
		$data['flashcolor']  = "white";
		$data['flashmargin'] = "0px 0px 0px 0px";
	}
	$divbgcolor = "background-color:{$data['flashcolor']};text-align:{$data['flashalign']};margin:{$data['flashmargin']};";
	ob_start();
?>	
	<div id="flashCountContainer" style="<?=$divbgcolor?>">
		<script type="text/javascript">writeCode(getEmbedCode("<?=$pluginURL?>/data/counter_graph_tt_<?=$data['flashsize']?>_85_<?=$data['flashcolor']?>.swf","<?=$data['flashsize']?>","85","flashCount","<?=$data['flashcolor']?>","id=<?=$blogid?>&amp;counter=<?=$data['maxdate']?>","false","transparent"), "flashCountContainer")</script>
	</div>
<?php
	$target = ob_get_contents();
	ob_end_clean();
	return $target;
}

function FlashCounterViewDataSet($DATA){
	global $blog, $service;
	requireComponent('Textcube.Function.misc');
	$cfg = misc::fetchConfigVal($DATA);
	if (!isset($blog['blogLanguage'])) {
		$blog['blogLanguage'] = $service['language'];
	}
	// Locale language file support.
	switch($blog['blogLanguage']) {
		case "zh-TW":
			$retval = 'Flash 計數器尺寸 "205x85" 僅適用於黑色底色設定。';
		break;
		case "zh-CN":
			$retval = 'Flash 计数器尺寸 "205x85" 仅适用于黑色底色设定。';
		break;
		default:
			$retval = "플래쉬카운터 '205x85' 크기는 검정색만 설정가능합니다.";
	}
	
	if($cfg['flashsize']=='205'){
		if($cfg['flashcolor']=='white') return $retval;
	}
	return true;
}
?>
