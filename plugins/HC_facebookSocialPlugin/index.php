<?php

function facebookSocialPlugins($target, $mother)
{
	global $database, $suri, $blog, $configVal;
	$data = Misc::fetchConfigVal($configVal);
	$output = '';
	if($suri['directive']!="/rss" && $suri['directive']!="/m" && $suri['directive']!="/i/entry" && $suri['directive']!="/atom" && $suri['directive']!="/sync") {
		$permalink = ($blog['useSloganOnPost'])? POD::queryCell("SELECT `slogan` FROM {$database['prefix']}Entries WHERE `id`={$mother} LIMIT 1") : $mother;
		$permalink = urlencode(getBlogURL() . '/' . $permalink);
		$title = urlencode(POD::queryCell("SELECT `title` FROM {$database['prefix']}Entries WHERE `id`={$mother} LIMIT 1"));

		$output = '<div style="margin-top:1em;width:auto;height:auto;clear:both;">';

        $__width = (!isset($data['content_width']) || (int) $data['content_width'] <=0 ) ? 520 : $data['content_width'];

		if($data['share']) $output .= '<div><a name="fb_share" type="button_count" href="http://www.facebook.com/sharer.php?u='.$permalink.'&t='.$title.'">'._t('Share').'</a><script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script></div>';

		if($data['like_button'] && !$data['comments']) $output .= '<div><iframe src="http://www.facebook.com/plugins/like.php?href='.$permalink.'&amp;layout=standard&amp;show_faces=true&amp;width='.$__width.'&amp;action=like&amp;font=lucida+grande&amp;colorscheme=light&amp;height=80" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:'.$__width.'px; height:80px;" allowTransparency="true"></iframe></div>';

		if($data['comments']) $output .= '<div><fb:comments xid="'.$permalink.'" numposts="10" width="'.$__width.'" publish_feed="true"></fb:comments></div>';

		if($data['live_stream']) $output .= '<div><iframe src="http://www.facebook.com/plugins/live_stream_box.php?app_id=128920980016&amp;width='.$__width.'&amp;hieght=500&amp;via_url&alyays_post_to_friend=true" scrolling="no" frameborder="0" style="border:none;overflow:hidden;width:'.$__width.'px;height:500px;" allowTransparency="true"></iframe></div>';

		$output .= '</div><br />';
	}

	return $target . $output;
}

function facebookFBRoot($target, $mother) {
	return '<div id="fb-root"></div><script src="http://connect.facebook.net/zh_TW/all.js#appId=128920980016&amp;xfbml=1"></script>' . $target;
}

function getSocialPluginsSetting($DATA) {
	requireComponent('Textcube.Function.misc');
	$cfg = Misc::fetchConfigVal($DATA);

	return true;
}
?>
