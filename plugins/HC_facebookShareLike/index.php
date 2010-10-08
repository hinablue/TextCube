<?php
function facebookShareLikeButton($target, $mother)
{
	global $database, $suri, $blog;
	$output = '';
	if($suri['directive']!="/rss" && $suri['directive']!="/m" && $suri['directive']!="/i/entry" && $suri['directive']!="/atom" && $suri['directive']!="/sync") {
        $permalink = ($blog['useSloganOnPost'])? POD::queryCell("SELECT `slogan` FROM {$database['prefix']}Entries WHERE `id`={$mother} LIMIT 1") : $mother;
        $permalink = urlencode(getBlogURL() . '/' . $permalink);
        $title = urlencode(POD::queryCell("SELECT `title` FROM {$database['prefix']}Entries WHERE `id`={$mother} LIMIT 1"));

        $output .= '<div style="margin-top:1em;width:100%;height:35px;overflow:none;clear:both;"><div>';
        $output .= '<a name="fb_share" type="button_count" href="http://www.facebook.com/sharer.php?u='.$permalink.'&t='.$title.'">'._t('Share').'</a><script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>';
        $output .= '</div><div style="margin-top:4px;">';
        $output .= '<iframe src="http://www.facebook.com/plugins/like.php?href='.$permalink.'&amp;layout=standard&amp;show_faces=false&amp;width=450&amp;action=like&amp;colorscheme=light&amp;height=35" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:35px;" allowTransparency="true"></iframe>';

        $output .= '</div></div><br />';
	}

	return $target . $output;
}
?>
