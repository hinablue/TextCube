<?php
function AddNewWindowLinks($target, $mother) {
	global $pluginURL, $configVal, $blogid;

	if (is_null($configVal)) return $target;

	requireComponent('Textcube.Function.misc');
	$config = Misc::fetchConfigVal($configVal);
	
	switch ($config['type']) {
	case 'hack':
		$replacement = '<a href="$1"$2>$4</a><a href="$1" onclick="window.open(\'$1\');return false;" ';
		$replacement .= 'style="border:none; text-decoration:none; padding-left:15px; margin-right: -0.5em; background: transparent url('.$pluginURL.'/newwindow.gif) no-repeat 0px 50%;" title="'._t("다음 링크를 새 창으로 엽니다.").' : \'$4\'">&nbsp;</a>';
		break;
	case 'img':
		$replacement = '<a href="$1"$2>$4</a><a href="$1" onclick="window.open(\'$1\');return false;">';
		$replacement .= '<img src="'.$pluginURL.'/newwindow.gif" style="margin-left:0.1em; margin-right:0.1em; vertical-align:middle;" alt="'._t('(새 창으로 열기)').'"></a>';
		break;
	case 'text':
	default:
		$replacement = '<a href="$1"$2>$4</a> <a href="$1" onclick="window.open(\'$1\');return false;" title="Open \'$4\' link in a new window">'._t('(새 창으로 열기)').'</a>';
	}
	$target = preg_replace('/<a href="(http:\/\/[^"]+)"(( \w+="[^"]+")*)>([^<]+)<\/a>/i', $replacement, $target);
	return $target;
}
?>
