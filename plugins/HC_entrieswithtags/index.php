<?php
/*
Plugin Name: Entries group with tags for TC
Version: 0.1
Author: Cain Chen, Hinablue.
Author URI: http://hina.ushiisland.net/blog/hinablue/
*/ 

function EntriesWithTags_Style($target) {
    global $suri;

    $directive = array('archive','category','guestbook','imageResizer','link','login','logout','pannels','protected','search','tag','trackback','rss','atom','ientry','sync','m','commentcomment');

    if(in_array(str_replace('/','', $suri['directive']), $directive)) return $target;

	ob_start();
?>
<style type="text/css">
<!--
.entries-with-tags-box { padding: 10px; margin: 4px 0px 4px 0px; }
.entries-with-tags-box strong { width: 100%;border-bottom: 1px solid #efefef; }
.entries-with-tags-box ul { margin:0;list-style:none !important; }
.entries-with-tags-box ul li { margin:0;padding: 2px 0px 0px 0px; background: none !important; }
.entries-with-tags-box ul li a { }
.entries-with-tags-box ul li span { font-size: 0.82em; color: #666; }
-->
</style>
<?php
	$target .= ob_get_contents();
	ob_end_clean();
	
	return $target;
}

function EntriesWithTags($target, $mother) {
	global $suri, $defaultURL, $blogURL, $pluginURL, $configVal, $blog, $service, $blogid, $database;
	requireComponent('Textcube.Function.misc');
	
	$html = '';
	if($suri['directive']!="/rss" && $suri['directive']!="/atom" && $suri['directive']!="/m" && $suri['directive']!="/i/entry" && $suri['directive']!="/sync") {

		$html = '
		<div class="entries-with-tags-box">
			<strong>&nbsp;'._t("Related entries").'&nbsp;</strong>
			<ul>';

		$data = misc::fetchConfigVal($configVal);
		$entries = isset($data['entries']) ? $data['entries'] : 5;
		
		$conEntry = POD::queryRow("SELECT userid, category FROM {$database['prefix']}Entries WHERE blogid = {$blogid} AND id = {$mother}");		
		$result = POD::query("SELECT DISTINCT e.id, e.slogan, e.title, e.created, e.comments, e.trackbacks
								FROM {$database['prefix']}TagRelations AS r
								LEFT JOIN {$database['prefix']}TagRelations AS t ON t.tag = r.tag 
								LEFT JOIN {$database['prefix']}Entries AS e ON e.id = t.entry 
								WHERE r.entry ={$mother}
								AND r.blogid ={$blogid}
								AND t.entry !={$mother}
								AND e.userid ={$conEntry['userid']} 
								AND e.category = {$conEntry['category']} 
								ORDER BY t.entry DESC
								LIMIT {$entries}");
		if(POD::num_rows($result)>0) {
			while($row = POD::fetch($result, 'array')) {
				$entry		= $row['id'];
				$slogan		= rawurlencode($row['slogan']);
				$title		= $row['title'];
				$created	= date("Y/m/d H:m", $row['created']);
				$comments	= $row['comments'];
				$trackbacks	= $row['trackbacks'];
				if ($suri['directive']=="/category") {
					$permalink	= ($blog['useSloganOnCategory']) ? "{$defaultURL}/entry/{$slogan}" : "{$defaultURL}/{$entry}";
				} elseif ($suri['directive']=="/tag") {
					$permalink	= ($blog['useSloganOnTag']) ? "{$defaultURL}/entry/{$slogan}" : "{$defaultURL}/{$entry}";
				} else {
					$permalink	= ($blog['useSloganOnPost']) ? "{$defaultURL}/entry/{$slogan}" : "{$defaultURL}/{$entry}";
				}
				$html .= "<li><a href=\"{$permalink}\">{$title}</a>,<span>Comments:&nbsp;{$comments}&nbsp;|&nbsp;Trackbacks:&nbsp;{$trackbacks}&nbsp;|&nbsp;{$created}</span></li>";
			}
		} else {
			$html .= "<li>"._t("No related entries.")."</li>";
		}	
	$html .= <<<EOF
		</ul>
	</div>
EOF;
	}
	return $target.$html;
}

function EntriesWithTags_DataSet($data) {
	requireComponent('Textcube.Function.misc');
	$cfg = misc::fetchConfigVal($data);
	if(!$cfg['entries'] || empty($cfg['entries']) || intval($cfg['entries']<=0)) $cfg['entries'] = 5;
	return true;
}
?>
