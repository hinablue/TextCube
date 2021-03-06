<?php
/* 強制 UTF-8 */
global $blogid;
define('BASE_PATH', ROOT. DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR );
define('PLURK_COOKIE_PATH', BASE_PATH . 'plurk.'.$blogid.'.cookie');
define('PLURK_LOG_PATH', BASE_PATH . 'plurk.log');

define('PLURK_NOT_LOGIN', 'You are not login.');
define('PLURK_AGENT', 'php-plurk-api agent');


function myPlurk_ResponseStyle($target) {
	global $suri, $configVal, $pluginURL;

    $directive = array('archive','category','guestbook','imageResizer','link','login','logout','pannels','protected','search','tag','trackback','rss','atom','ientry','sync','m','commentcomment');

    if(in_array(str_replace('/','', $suri['directive']), $directive)) return $target;


	requireComponent('Textcube.Function.misc');
	$data = misc::fetchConfigVal($configVal);
	$attachResponses = (isset($data['attachResponses']) && $data['attachResponses']==1) ? true : false;
	
	if (!$attachResponses) {
		return $target;
	}
	
    $target .= "<link rel=\"stylesheet\" media=\"screen\" type=\"text/css\" href=\"{$pluginURL}/plurk.css\" />\n";

    return $target;
}

function myPlurk_ResponseJscript($target) {
	global $suri, $configVal, $pluginURL;
	requireComponent('Textcube.Function.misc');
	$data = misc::fetchConfigVal($configVal);
	$attachResponses = (isset($data['attachResponses']) && $data['attachResponses']==1) ? true : false;
	
    $directive = array('archive','category','guestbook','imageResizer','link','login','logout','pannels','protected','search','tag','trackback','rss','atom','ientry','sync','m','commentcomment');

    if(in_array(str_replace('/','', $suri['directive']), $directive) || !$attachResponses) return $target;

    $target .= '<script type="text/javascript">
//<![CDDA[
/*!
  * $script.js v1.3
  * https://github.com/ded/script.js
  * Copyright: @ded & @fat - Dustin Diaz, Jacob Thornton 2011
  * Follow our software http://twitter.com/dedfat
  * License: MIT
  */
!function(a,b,c){function s(a,c){var e=b.createElement("script"),f=j;e.onload=e.onerror=e[o]=function(){e[m]&&!/^c|loade/.test(e[m])||f||(e.onload=e[o]=null,f=1,h[a]=2,c())},e.async=1,e.src=a,d.insertBefore(e,d.firstChild)}function q(a,b){p(a,function(a){return!b(a)})}var d=b.getElementsByTagName("head")[0],e={},f={},g={},h={},i="string",j=!1,k="push",l="DOMContentLoaded",m="readyState",n="addEventListener",o="onreadystatechange",p=function(a,b){for(var c=0,d=a.length;c<d;++c)if(!b(a[c]))return j;return 1};!b[m]&&b[n]&&(b[n](l,function u(){b.removeEventListener(l,u,j),b[m]="complete"},j),b[m]="loading");var r=function(a,b,d){function o(){if(!--m){e[l]=1,j&&j();for(var a in g)p(a.split("|"),n)&&!q(g[a],n)&&(g[a]=[])}}function n(a){return a.call?a():e[a]}a=a[k]?a:[a];var i=b&&b.call,j=i?b:d,l=i?a.join(""):b,m=a.length;c(function(){q(a,function(a){h[a]?(l&&(f[l]=1),h[a]==2&&o()):(h[a]=1,l&&(f[l]=1),s(r.path?r.path+a+".js":a,o))})},0);return r};r.get=s,r.ready=function(a,b,c){a=a[k]?a:[a];var d=[];!q(a,function(a){e[a]||d[k](a)})&&p(a,function(a){return e[a]})?b():!function(a){g[a]=g[a]||[],g[a][k](b),c&&c(d)}(a.join("|"));return r};var t=a.$script;r.noConflict=function(){a.$script=t;return this},typeof module!="undefined"&&module.exports?module.exports=r:a.$script=r}(this,document,setTimeout);

    if(typeof jQuery === "undefined") {
        $script("http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js", function() {
            jQuery.noConflict();
            finalPlurkResponseAnimate();
        });
    } else {
        finalPlurkResponseAnimate();
    }

    function finalPlurkResponseAnimate() {
        (function($) {
            $(document).ready(function() {
                $(".plurkResponseMoreButton").click(function() {
                    var more = $(".plurkResponseLists", $(this).parent());
                    more.toggleClass("autoPlurkResponsesExtend");
                    if(more.hasClass("autoPlurkResponsesExtend")) {
                        $(this).html("'._t("LessPlurk...").'");
                    } else {
                        $(this).html("'._t("MorePlurk...").'");
                    }
                });
            });
        })(jQuery);
    }
//]]>
</script>';

	return $target;
}

if (!class_exists("googleURLShortner")) {
    class googleURLShortner
    {
        private $url;
        private $urlToken;

        public function __construct() {
            $this->url = "";
            $this->urlToken = "";
        }

        public function shortner($url = "") {
            $this->url = urlencode($url);
            if ($this->isValidURL())
            {
                $this->urlToken = $this->googlToken($url);
                $curl = curl_init(); 
                curl_setopt($curl, CURLOPT_URL, 'http://goo.gl/api/url');   //goo.gl api url
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
                curl_setopt($curl, CURLOPT_POST, 1); 
                curl_setopt($curl, CURLOPT_POSTFIELDS, 'user=toolbar@google.com&url='.$this->url.'&auth_token='.$this->urlToken); 
                $response = curl_exec($curl); 
                curl_close($curl);
                if($response) {
                    $json = json_decode($response);
                    return $json->short_url;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        private function isValidURL()
        {
            return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', urldecode($this->url));
        }

        private function googlToken($b)
        {
            $i = $this->tke($b);
            $i = $i >> 2 & 1073741823;
            $i = $i >> 4 & 67108800 | $i & 63;
            $i = $i >> 4 & 4193280 | $i & 1023;
            $i = $i >> 4 & 245760 | $i & 16383;
            $j = "7";
            $h = $this->tkf($b);
            $k = ($i >> 2 & 15) << 4 | $h & 15;
            $k |= ($i >> 6 & 15) << 12 | ($h >> 8 & 15) << 8;
            $k |= ($i >> 10 & 15) << 20 | ($h >> 16 & 15) << 16;
            $k |= ($i >> 14 & 15) << 28 | ($h >> 24 & 15) << 24;
            $j .= $this->tkd($k);

            return $j;
        }

        private function tkc()
        {
            $l = 0;
            foreach (func_get_args() as $val) {
                $val &= 4294967295;
                $val += $val > 2147483647 ? -4294967296 : ($val < -2147483647 ? 4294967296 : 0);
                $l   += $val;
                $l   += $l > 2147483647 ? -4294967296 : ($l < -2147483647 ? 4294967296 : 0);
            }
            return $l;
        }
        private function tkd($l)
        {
            $l = $l > 0 ? $l : $l + 4294967296;
            $m = "$l";  //must be a string
            $o = 0;
            $n = false;
            for($p = strlen($m) - 1; $p >= 0; --$p) {
                $q = $m[$p];
                if($n) {
                    $q *= 2;
                    $o += floor($q / 10) + $q % 10;
                } else {
                    $o += $q;
                }
                $n = !$n;
            }
            $m = $o % 10;
            $o = 0;
            if($m != 0) {
                $o = 10 - $m;
                if(strlen($l) % 2 == 1) {
                    if ($o % 2 == 1) {
                        $o += 9;
                    }
                    $o /= 2;
                }
            }
            return "$o$l";
        }
        private function tke($l)
        {
            $m = 5381;
            for($o = 0; $o < strlen($l); $o++){
                $m = $this->tkc($m << 5, $m, ord($l[$o]));
            }
            return $m;
        }

        private function tkf($l)
        {
            $m = 0;
            for($o = 0; $o < strlen($l); $o++){
                $m = $this->tkc(ord($l[$o]), $m << 6, $m << 16, -$m);
            }
            return $m;
        }
    }
}


function myPlurk_UpdatePlurk($target, $mother) {
    global $blogid, $service, $database, $configVal;
    requireComponent('Textcube.Function.misc');
    require_once ("libs/plurk_api.php");

    $data = misc::fetchConfigVal($configVal);
    $autoPlurkEntries = (int) ($data['autoPlurkEntries']);
    $plurkwithslogan = (int) ($data['plurkwithslogan']);
    $plurklang = Setting::getBlogSettingGlobal('blogLanguage','');

    $plurk = new plurk_api();
    $googl = new googleURLShortner();

    $plurkNickname = isset($data['plurknickname']) ? $data['plurknickname'] : "";
    $plurkPassword = isset($data['plurkpassword']) ? $data['plurkpassword'] : "";
    $plurk_api = 'iMCH3JDDda7c4bs0qiOchZcxAx7t8PA7';

    if ($mother['category']>=0 && 
        (($autoPlurkEntries==1 && $mother['visibility']>=2) || ($autoPlurkEntries==2 && $mother['visibility']>=3)) &&
        !empty($plurkNickname) && !empty($plurkPassword) && $plurk->login($plurk_api, $plurkNickname, $plurkPassword) && 
        !POD::queryCount("UPDATE {$database['prefix']}PlurkEntries SET lastaccess = UNIX_TIMESTAMP() WHERE blogid={$blogid} AND id={$target}"))
    {
        $slogan = POD::queryCell("SELECT `slogan` FROM `{$database['prefix']}Entries` WHERE `id`={$target}");
        $permalink = getBlogURL() . "/" . (Setting::getBlogSettingGlobal('useSloganOnPost',true) ? "entry/" . URL::encode($slogan, $service['useEncodedURL']) : $target);

        $url = $googl->shortner( $permalink );
        $url = ($url !== false) ? $url : $permalink;

        $lang = "en";
        switch($plurklang) {
            case "zh-TW":
                $lang = "tr_ch";
            break;
            case "zh-CN":
                $lang = "cn";
            default:
                $lang = "en";
        }
        if (1===$plurkwithslogan) {
            $content = $url . " (" . $mother['title'] . ") via ".Setting::getBlogSettingGlobal('title', '')." with Textcube Plurk-API.";
        } else {
    		$content = $mother['title'] . " via ". $url . " (".Setting::getBlogSettingGlobal('title', '').") with Textcube Plurk-API.";
        }
		$acceptComment = ($mother['acceptComment']==1) ? true : false;
		$response = $plurk->add_plurk($lang, "shares", $content, NULL, $acceptComment);

		if (isset($response->plurk_id) && $response->plurk_id > 0) {
			$plurk_id = $response->plurk_id;
			POD::query("INSERT INTO {$database['prefix']}PlurkEntries (blogid, id, plurkid, lastaccess) VALUES ('{$blogid}', '{$target}', '{$plurk_id}', UNIX_TIMESTAMP())");
		}
	}

    return $target;
}

function myPlurk_AddPlurkIcon($target, $mother) {
	global $blogid, $service, $database, $suri, $blogURL, $pluginURL, $configVal;
	requireComponent('Textcube.Function.misc');

	$data = misc::fetchConfigVal($configVal);
	$attachResponses = (isset($data['attachResponses']) && $data['attachResponses']==1) ? true : false;
    $plurklang = Setting::getBlogSettingGlobal('blogLanguage','');
	
	$plurkIcon = "";
	$responsePlurks = "";
	if($suri['directive']!="/rss" && $suri['directive']!="/m" && $suri['directive']!="/i/entry" && $suri['directive']!="/atom" && $suri['directive']!="/sync"
		&& POD::queryCount("SELECT id FROM {$database['prefix']}PlurkEntries WHERE blogid={$blogid} AND id={$mother}") > 0)
	{
		$plurk_id = intval(POD::queryCell("SELECT plurkid FROM {$database['prefix']}PlurkEntries WHERE blogid={$blogid} AND id={$mother}"));
		$plurkLink = "http://www.plurk.com/p/" . base_convert($plurk_id, 10, 36);
		if (!empty($plurkLink)) {
			$plurkIcon = '<div id="plurkthis"><img src="'.$pluginURL.'/images/plurkicon.png" border="0" width="16" height="16" alt="Plurk This!" />&nbsp;PLURK: <a href="'.$plurkLink.'" target="_blank">'.$plurkLink.'</a></div><br />';
		}
		
		if (!$attachResponses) return $plurkIcon . $target;
	
        $cache = new PageCache;
        $cache->name = 'HC_TCPlurkCache';

        if($cache->load()) {
            $cache->contents = unserialize($cache->contents);
            if(array_key_exists($mother, $cache->contents) && (Timestamp::getUNIXtime() - $cache->dbContents < 600)) {
                return $plurkIcon . $target . $cache->contents[$mother];
            }
        }

        require_once ("libs/plurk_api.php");
		
		$plurk = new plurk_api();
    
        $plurkNickname = isset($data['plurknickname']) ? $data['plurknickname'] : "";
        $plurkPassword = isset($data['plurkpassword']) ? $data['plurkpassword'] : "";
        $plurk_api = 'iMCH3JDDda7c4bs0qiOchZcxAx7t8PA7';
        if (!$plurk->login($plurk_api, $plurkNickname, $plurkPassword))
        {
            return $plurkIcon . $target;
        }
        $responsePlurks = "";
		$response = $plurk->get_responses($plurk_id);

        if ($response->responses_seen > 0) {
			$qualifiers = array(
				"loves", "likes", "shares", "gives", "hates", "wants", "wishes", "needs", "will",
				"hopes", "asks", "has", "was", "wonders", "feels", "thinks", "says", "is"
			);
            $qualifiers_locale = array('en' => $qualifiers,
                    'zh-TW' => array(
			        	"愛", "喜歡", "推", "給", "討厭", "想要", "希望", "需要", "打算",
        				"希望", "問", "已經", "曾經", "好奇", "覺得", "想", "說", "正在"
		        	), 
                    'zh-CN' => array(
                        "爱", "喜欢", "推", "给", "讨厌", "想要", "希望", "需要", "打算",
                        "希望", "问", "已经", "曾经", "好奇", "觉得", "想", "说", "正在"
                    )
            );
            $lang = "en";
            switch($plurklang) {
                case "zh-TW":
                case "zh-CN":
                    $lang = $plurklang;
                break;
                default:
                    $lang = "en";
            }

			$friends = array();
            $nick2displayname = array('nickname'=>array(), 'displayname'=>array());
			foreach($response->friends as $friend) {
				$friends[$friend->uid]['display_name'] = $friend->display_name;
				$friends[$friend->uid]['nick_name'] = $friend->nick_name;
				$friends[$friend->uid]['has_profile_image'] = ($friend->has_profile_image==1) ? true : false;
				$friends[$friend->uid]['avatar'] = ($friend->avatar==null) ? "" : $friend->avatar;

                if(!in_array($friend->nick_name, $nick2displayname['nickname']))
                {
                    array_push($nick2displayname['nickname'], $friend->nick_name);
                    array_push($nick2displayname['displayname'], $friend->display_name);
                }
			}
			
			ob_start();
			echo "<div class=\"plurkResponse\" id=\"plurkResponse_{$mother}\">\n";
			echo "<h3>"._f("%1 Responses to this Plurk", $response->responses_seen)."</h3>\n";
			echo "<div class=\"plurkResponseLists\">\n<table cellpadding=\"2\" cellspacing=\"2\" border=\"0\">\n";
			foreach($response->responses as $commentObj) {
				$comment = (array)$commentObj;
				$userIcon = ($friends[$comment['user_id']]['has_profile_image']) ? "http://avatars.plurk.com/{$comment['user_id']}-medium{$friends[$comment['user_id']]['avatar']}.gif" : "";
				$display_name = $friends[$comment['user_id']]['display_name'];
				$nick_name = $friends[$comment['user_id']]['nick_name'];
				$qualifier = (in_array($comment['qualifier'], $qualifiers)) ? $comment['qualifier'] : "";
				$qualifierKey = array_keys($qualifiers, $comment['qualifier']);
				$qualifier_trans = (isset($qualifiers_locale[$lang][$qualifierKey[0]])) ? $qualifiers_locale[$lang][$qualifierKey[0]] : '';

				if (preg_match_all('/<a href="http:\/\/www.plurk.com\/(.*?)" class="ex_link">(.*?)<\/a>/ms', $comment['content'], $matches)) {
                    $mlen = count($matches[1]);
					for($i=$mlen-1; $i>=0; $i--) {
                        if (in_array($matches[1][$i], $nick2displayname['nickname']))
                        {
                            $replydisplayname = $nick2displayname['displayname'][array_search($matches[1][$i], $nick2displayname['nickname'])];
                            $comment['content'] = str_replace('<a href="http://www.plurk.com/'.$matches[1][$i].'" class="ex_link">'.$matches[2][$i].'</a>',
														'<a href="http://www.plurk.com/'.$matches[1][$i].'" class="ex_link">'.$replydisplayname.'</a>',
														$comment['content']);
                        }
					}
				}
				echo "<tr><td class=\"user_icon\"><a href=\"http://www.plurk.com/{$nick_name}\" target=\"_blank\"><img src=\"{$userIcon}\" border=\"0\" width=\"45\" height=\"45\" alt=\"{$display_name}\" title=\"{$display_name}\" onerror=\"this.src='{$pluginURL}/images/nonusericon.gif'\" /></a></td>\n";
				echo "<td class=\"plurkcontent\"><a href=\"http://www.plurk.com/{$nick_name}\" target=\"_blank\">{$display_name}</a>&nbsp;\n";
				echo "<span class=\"qualifier_{$qualifier}\">{$qualifier_trans}</span>&nbsp;<span class=\"plurkcomment\">{$comment['content']}</span></td></tr>\n";
			}
			echo "</table>\n</div>\n<p style=\"text-align:right;line-height:1em;\" class=\"plurkResponseMoreButton\">"._t('MorePlurk...')."</p>\n";
			echo "</div>\n\n";
			$responsePlurks = ob_get_contents();
			ob_end_clean();
        } else {
            // no response
        }

        $cache->contents[$mother] = $responsePlurks;
    	$cache->contents = serialize($cache->contents);
    	$cache->dbContents = Timestamp::getUNIXtime();
	    $cache->update();
	    unset($cache);
    }
		
	return $plurkIcon . $target . $responsePlurks;
}

function getPlurkDataSet($data) {
	requireComponent('Textcube.Function.misc');
	$cfg = misc::fetchConfigVal($data);
	if(!$cfg['plurknickname'] || empty($cfg['plurknickname']) || !$cfg['plurkpassword'] || empty($cfg['plurkpassword'])) return "::Input error::\n\n Plurk's nickname and password must input certainly.";
	return true;
}
?>
