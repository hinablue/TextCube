<?php

function facebookSocialPlugins($target, $mother)
{
	global $database, $suri, $blog, $configVal;
	$data = Misc::fetchConfigVal($configVal);
	$output = '';

    $directive = array('archive','category','guestbook','imageResizer','link','login','logout','pannels','protected','search','tag','trackback','rss','atom','ientry','sync','m','commentcomment');

    if(in_array(str_replace('/','', $suri['directive']), $directive)) return $target;

    $permalink = ($blog['useSloganOnPost'])? POD::queryCell("SELECT `slogan` FROM {$database['prefix']}Entries WHERE `id`={$mother} LIMIT 1") : $mother;
    $permalink = urlencode(getBlogURL() . '/' . $permalink);
    $title = urlencode(POD::queryCell("SELECT `title` FROM {$database['prefix']}Entries WHERE `id`={$mother} LIMIT 1"));

    $output = '<div style="margin-top:1em;width:auto;height:auto;clear:both;">';

    $__width = (!isset($data['content_width']) || (int) $data['content_width'] <=0 ) ? 520 : $data['content_width'];

    if($data['share']) $output .= '<div><a name="fb_share" type="button_count" href="http://www.facebook.com/sharer.php?u='.$permalink.'&t='.$title.'">'._t('Share').'</a><script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script></div>';

    if($data['like_button'] && !$data['comments']) $output .= '<div><iframe src="http://www.facebook.com/plugins/like.php?href='.$permalink.'&amp;layout=standard&amp;show_faces=true&amp;width='.$__width.'&amp;action=like&amp;font=lucida+grande&amp;colorscheme=light&amp;height=80" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:'.$__width.'px; height:80px;" allowTransparency="true"></iframe></div>';

    if($data['comments']) $output .= '<div><fb:comments xid="'.$permalink.'" numposts="10" width="'.$__width.'" publish_feed="true"></fb:comments></div>';

    /*if($data['live_stream']) $output .= '<div><iframe src="http://www.facebook.com/plugins/live_stream_box.php?app_id=128920980016&amp;width='.$__width.'&amp;hieght=500&amp;via_url='.$permalink.'&alyays_post_to_friend=true" scrolling="no" frameborder="0" style="border:none;overflow:hidden;width:'.$__width.'px;height:500px;" allowTransparency="true"></iframe></div>';*/

    $output .= '</div><br />';

	return $target . $output;
}

function facebookFBRoot($target, $mother) {
    global $blog, $service, $suri;

    $directive = array('archive','category','guestbook','imageResizer','link','login','logout','pannels','protected','search','tag','trackback','rss','atom','ientry','sync','m','commentcomment');

    if(in_array(str_replace('/','', $suri['directive']), $directive)) return $target;

    if (!isset($blog['blogLanguage'])) {
        $blog['blogLanguage'] = $service['language'];
    }
    $locate = 'en_US';
    switch($blog['blogLanguage']) {
        case 'zh-TW':
            $locate = 'zh_TW';
        break;
        case 'zh-CN':
            $locate = 'zh_CN';
        break;
        case 'ko':
            $locate = 'ko_KR';
        break;
        case 'ja':
            $locate = 'ja_JP';
        break;
        case 'vi':
            $locate = 'vi_VN';
        break;
        default:
            $locate = 'en_US';
    }
    return '<div id="fb-root"></div><script src="http://connect.facebook.net/'.$locate.'/all.js#appId=128920980016&amp;xfbml=1"></script>' . $target;
}

function getSocialPluginsSetting($DATA) {
	requireComponent('Textcube.Function.misc');
	$cfg = Misc::fetchConfigVal($DATA);

	return true;
}
?>
