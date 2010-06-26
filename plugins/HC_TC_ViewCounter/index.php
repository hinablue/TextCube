<?php
// ViewPostCounter 1.1 index.php
// Author: Tim (http://diary.tw/tim)
// Modified: Hina (http://blog.hinablue.me)

function SaveReadToCookie($blogid, $id)
{
	// return true for ok to add counter, false for not adding counter
	$articleid = "$blogid-$id,";
	if(!isset($_COOKIE['vc_viewed']))
	{
		$_COOKIE['vc_viewed'] = "$articleid";
		setcookie('vc_viewed', $_COOKIE['vc_viewed']);
		return true;
	}
	else
	{
		if(strstr($_COOKIE['vc_viewed'], $articleid))
			return false;
		else
		{
			$_COOKIE['vc_viewed'] .= "$articleid";			
			setcookie('vc_viewed', $_COOKIE['vc_viewed']);
			return true;
		}
	}
}

function CountAndShow($target, $mother)
{
	global $database, $service,$suri, $blogid;
	$now_timestamp = time();
	$output = '';
	if($suri['directive']!="/rss" && $suri['directive']!="/m" && $suri['directive']!="/i/entry" && $suri['directive']!="/atom" && $suri['directive']!="/sync") {
		if(SaveReadToCookie($blogid, $mother))
		{
		
			if(!POD::queryCount("UPDATE {$database['prefix']}EntryReadCount SET readcounts = readcounts + 1, lastaccess = $now_timestamp WHERE blogid=$blogid AND id=$mother"))
			{
				POD::query("INSERT INTO {$database['prefix']}EntryReadCount (blogid, id, readcounts, lastaccess) VALUES ($blogid, $mother, 1, $now_timestamp)");
			}
		
		}
		$readcount = POD::queryCell("SELECT readcounts FROM {$database['prefix']}EntryReadCount WHERE blogid=$blogid AND id=$mother");

		$output .= "<span class=\"c_cnt\">views: $readcount times </span><br />";
	}

	return $output . $target;
}


function ViewCounterList($target) 
{
	global $database, $blogid, $blogURL, $skinSetting;
	$result = POD::queryAll("SELECT b.id, b.title, a.readcounts FROM {$database['prefix']}EntryReadCount a INNER JOIN {$database['prefix']}Entries b ON a.blogid = b.blogid AND a.id = b.id WHERE a.blogid={$blogid} AND b.visibility >= 2 AND b.draft = 0 AND b.category >=0 ORDER BY a.readcounts DESC LIMIT 0,5");
	$target = "<ul>";	

	foreach((array)$result as $row) {
		$articleid = $row['id'];
		$articletitle = htmlspecialchars(UTF8::lessenAsEm($row['title'],$skinSetting['recentEntryLength']));
		$readcounts = $row['readcounts'];
		$rsurl="$blogURL/$articleid";
		$target .= "<li><a href=\"$rsurl\">$articletitle</a>&nbsp;<span class=\"cnt\">($readcounts)</span></li>\n";
	}

	$target .= "</ul>";
	return $target;
}

?>
