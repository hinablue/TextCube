<?php

function facebookSocialPlugins($target, $mother)
{
    global $database, $suri, $blog, $configVal;
    $data = Misc::fetchConfigVal($configVal);
    $output = '';

    $directive = array('archive','category','guestbook','imageResizer','link','login','logout','pannels','protected','search','tag','trackback','rss','atom','ientry','sync','m','commentcomment');

    if(in_array(str_replace('/','', $suri['directive']), $directive)) return $target;

    $permalink = ($blog['useSloganOnPost'])? 'entry/'.POD::queryCell("SELECT `slogan` FROM {$database['prefix']}Entries WHERE `id`={$mother} LIMIT 1") : $mother;
    $permalink = urlencode(getBlogURL() . '/' . $permalink);
    $title = urlencode(POD::queryCell("SELECT `title` FROM {$database['prefix']}Entries WHERE `id`={$mother} LIMIT 1"));

    $output = '<div style="margin-top:1em;width:auto;height:auto;clear:both;">';

    $__width = (!isset($data['content_width']) || (int) $data['content_width'] <=0 ) ? 520 : $data['content_width'];

    if($data['share']) $output .= '<div class="fb-send" data-href="'.$permalink.'"></div>';

    if($data['like_button'] && !$data['comments']) $output .= '<div class="fb-like" data-href="'.$permalink.'" data-send="true" data-width="'.$__width.'" data-show-faces="true"></div>';        
        
    if($data['comments']) $output .= '<div class="fb-comments" data-href="'.$permalink.'" data-num-posts="2" data-width="'.$__width.'"></div>';

    $output .= '</div><br />';

    return $target . $output;
}

function facebookFBRoot($target, $mother) {
    global $blog, $service, $suri, $pluginURL;

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
    $fbRoot = '<div id="fb-root"></div>';
    $fbRoot .= <<<FBJS
<script type="text/javascript">
//<![CDATA
    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) { return; }
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/$locate/all.js#xfbml=1";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
//]]>
</script>
FBJS;
   
    return $fbRoot . $target;
}

function getSocialPluginsSetting($DATA) {
    requireComponent('Textcube.Function.misc');
    $cfg = Misc::fetchConfigVal($DATA);

    return true;
}
?>
