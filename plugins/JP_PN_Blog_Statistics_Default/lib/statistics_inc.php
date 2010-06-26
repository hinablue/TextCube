<?php
function _get_t($str, $getMode){
	$getMenu = isset($_GET['menu']) ? $_GET['menu'] : "entry";
	$targetStr = ($getMenu == $getMode)?"<b><font color='#444444'><u>".$str."</u></font></b>":$str;
	return $targetStr;
}

function getEntriesTotalCountDB($blogid){
	global $database, $configVal;
	$data = Misc::fetchConfigVal($configVal);
	if(is_null($data)){
		$data['privateChk'] = 2;
	}
	$getVisibility =($data['privateChk'] == 2)?" AND visibility > 0 ":"";
	return POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft = 0 {$getVisibility} AND category >= 0");
}

function getCommentCountDB($blogid){
	global $database, $configVal;
	$data = Misc::fetchConfigVal($configVal);
	if(is_null($data)) $data['repliesChk'] = 2;
	$commenterOutSql = ($data['repliesChk'] == 2)?" AND replier is NULL ":"";
	$commenterOut = ($data['commenterout'])?explode("|",$data['commenterout']):"";
	if(!empty($commenterOut) && count($commenterOut) > 0){
		foreach ($commenterOut as $item){
			if(!empty($item)) $commenterOutSql .= " AND name NOT LIKE '%{$item}%' ";
		}
	}
	return POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Comments WHERE blogid = {$blogid} AND entry > 0 AND isfiltered = 0 {$commenterOutSql}");
}

function getGuestbookCountDB($blogid){
	global $database, $configVal;
	$data = Misc::fetchConfigVal($configVal);
	$repliesChk = ($data['repliesChk'] == 2)?" AND replier is NULL ":"";
	return POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Comments WHERE blogid = {$blogid} AND entry = 0 AND isfiltered = 0 {$commenterOutSql}");
}

function getTrackbackCountDB($blogid){
	global $database;
	return POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}RemoteResponses WHERE blogid = {$blogid} AND entry > 0 AND type = 'trackback' AND isfiltered = 0");
}

function getStatisticsDB($getDate, $getCount, $getMode){
	global $database, $blogid, $configVal;
	$data = Misc::fetchConfigVal($configVal);
	if(is_null($data)){
		$data['repliesChk'] = 2;
		$data['privateChk'] = 2;
	}
	$getVisibility =($data['privateChk'] == 2)?" AND visibility > 0 ":"";
	$commenterOutSql = ($data['repliesChk'] == 2)?" AND replier is NULL ":"";
	$commenterOut = ($data['commenterout'])?explode("|",$data['commenterout']):"";
	if(!empty($commenterOut) && count($commenterOut) > 0){
		foreach ($commenterOut as $item){
			if(!empty($item)) $commenterOutSql .= " AND name NOT LIKE '%{$item}%' ";
		}
	}
	$getYear  = substr($getDate, 0, 4);
	$getMonth = substr($getDate,4);
	if($getMode == "entry"){$dateName = "published";}else{$dateName = "written";}
	if(strlen($getDate) == 6){
		if($getMode == "visit"){
			if(strlen($getCount) == 1) $getCount = "0".$getCount;
			$getDateSQL	 = ($getYear==9999)?"":" AND LEFT(datemark, 4) = '{$getYear}' ";
			$getDateSQL	.= " AND MID(datemark, 5,2) = '{$getMonth}' ";
			$getDateSQL	.= " AND MID(datemark, 7) = '{$getCount}' ";
			$getDateKey  = " MID(datemark, 7) period, ";
		}else{
			if(strlen($getCount) == 1) $getCount = "0".$getCount;
			$getDateSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME({$dateName})) = '{$getYear}' ";
			$getDateSQL .= " AND EXTRACT(MONTH FROM FROM_UNIXTIME({$dateName})) = '{$getMonth}' ";
			$getDateSQL .= " AND EXTRACT(DAY FROM FROM_UNIXTIME({$dateName})) = '{$getCount}' ";
			$getDateKey  = " EXTRACT(DAY FROM FROM_UNIXTIME({$dateName})) period, ";
		}
	}else{
		if($getMode == "visit"){
			if($getDate == "years" && !$getCount){
				if(strlen($getCount) == 1) $getCount = "0".$getCount;
				$getDateSQL	 = "";
				$getDateKey  = " LEFT(datemark, 4) period, ";
			}else{
				if(strlen($getCount) == 1) $getCount = "0".$getCount;
				$getDateSQL	 = ($getYear==9999)?"":" AND LEFT(datemark, 4) = '{$getYear}' ";
				$getDateSQL	.= " AND MID(datemark, 5,2) = '{$getCount}' ";
				$getDateKey  = " MID(datemark, 5,2) period, ";
			}
		}else{
			if($getDate == "years" && !$getCount){
				$getDateSQL  = "";
				$getDateKey  = " EXTRACT(YEAR FROM FROM_UNIXTIME({$dateName})) period, ";
			}else{
				$getDateSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME({$dateName})) = '{$getYear}' ";
				$getDateSQL .= " AND EXTRACT(MONTH FROM FROM_UNIXTIME({$dateName})) = '{$getCount}' ";
				$getDateKey  = " EXTRACT(MONTH FROM FROM_UNIXTIME({$dateName})) period, ";
			}
		}
	}
	switch($getMode){
		case "entry":
			if($getDate == "years" && !$getCount){
				$targetRow = POD::queryAll("SELECT {$getDateKey} COUNT(*) count FROM {$database['prefix']}Entries WHERE blogid = {$blogid} AND draft = 0 {$getVisibility} AND category >= 0 {$getDateSQL} GROUP BY period ORDER BY period ASC");
			}else{
				$targetRow = POD::queryRow("SELECT {$getDateKey} COUNT(*) count FROM {$database['prefix']}Entries WHERE blogid = {$blogid} AND draft = 0 {$getVisibility} AND category >= 0 {$getDateSQL} GROUP BY period ORDER BY period ASC");
			}
			break;
		case "comment":
			if($getDate == "years" && !$getCount){
				$targetRow = POD::queryAll("SELECT {$getDateKey} COUNT(*) count FROM {$database['prefix']}Comments WHERE blogid = {$blogid} AND entry > 0 AND isfiltered = 0 {$commenterOutSql} {$getDateSQL} GROUP BY period ORDER BY period ASC");
			}else{
				$targetRow = POD::queryRow("SELECT {$getDateKey} COUNT(*) count FROM {$database['prefix']}Comments WHERE blogid = {$blogid} AND entry > 0 AND isfiltered = 0 {$commenterOutSql} {$getDateSQL} GROUP BY period ORDER BY period ASC");
			}
			break;
		case "trackback":
			if($getDate == "years" && !$getCount){
				$targetRow = POD::queryAll("SELECT {$getDateKey} COUNT(*) count FROM {$database['prefix']}RemoteResponses WHERE blogid = {$blogid} AND entry > 0 AND type = 'trackback' AND isfiltered = 0 {$getDateSQL} GROUP BY period ORDER BY period ASC");
			}else{
				$targetRow = POD::queryRow("SELECT {$getDateKey} COUNT(*) count FROM {$database['prefix']}RemoteResponses WHERE blogid = {$blogid} AND entry > 0 AND type = 'trackback' AND isfiltered = 0 {$getDateSQL} GROUP BY period ORDER BY period ASC");
			}
			break;
		case "guestbook":
			if($getDate == "years" && !$getCount){
				$targetRow = POD::queryAll("SELECT {$getDateKey} COUNT(*) count FROM {$database['prefix']}Comments WHERE blogid = {$blogid} AND entry = 0 AND isfiltered = 0 {$commenterOutSql} {$getDateSQL} GROUP BY period ORDER BY period ASC");
			}else{
				$targetRow = POD::queryRow("SELECT {$getDateKey} COUNT(*) count FROM {$database['prefix']}Comments WHERE blogid = {$blogid} AND entry = 0 AND isfiltered = 0 {$commenterOutSql} {$getDateSQL} GROUP BY period ORDER BY period ASC");
			}
			break;
		case "visit":
			if($getDate == "years" && !$getCount){
				$targetRow = POD::queryAll("SELECT  {$getDateKey} SUM(visits) count FROM {$database['prefix']}DailyStatistics WHERE blogid = {$blogid} {$getDateSQL} GROUP BY period ORDER BY period ASC");
			}else{
				$targetRow = POD::queryRow("SELECT  {$getDateKey} SUM(visits) count FROM {$database['prefix']}DailyStatistics WHERE blogid = {$blogid} {$getDateSQL} GROUP BY period ORDER BY period ASC");
			}
			break;
		default:return false;
	}
	return $targetRow;
}

function getStatisticsTotalDB($getDate, $getMenu, $getMode){
	global $database, $blogid, $configVal;
	requireComponent('Textcube.Function.misc');
	$data = Misc::fetchConfigVal($configVal);
	if(is_null($data)){
		$data['repliesChk'] = 2;
		$data['privateChk'] = 2;
	}
	$getVisibility =($data['privateChk'] == 2)?" AND visibility > 0 ":"";
	$commenterOutSql = ($data['repliesChk'] == 2)?" AND replier is NULL ":"";
	$commenterOut = (isset($data['commenterout']) && ($data['commenterout']))?explode("|",$data['commenterout']):"";
	if(!empty($commenterOut) && count($commenterOut) > 0){
		foreach ($commenterOut as $item){
			if(!empty($item)) $commenterOutSql .= " AND name NOT LIKE '%{$item}%' ";
		}
	}
	$getYear  = substr($getDate, 0, 4);
	$getMonth = substr($getDate,4);
	if($getMenu == "entry"){$dateName = "published";}else{$dateName = "written";}
	if(!$getMode){
		if($getMenu == "visit"){
			if(strlen($getDate) == 4){
				$getSQL = ($getYear==9999)?"":" AND LEFT(date, 4) = '{$getDate}'";
			}else{
				$getSQL = " AND LEFT(date, 6) = '{$getDate}'";
			}
		}else{
			if(strlen($getDate) == 4){
				$getSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME({$dateName})) = '{$getYear}' ";
			}else{
				$getSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME({$dateName})) = '{$getYear}' ";
				$getSQL .= " AND EXTRACT(MONTH FROM FROM_UNIXTIME({$dateName})) = '{$getMonth}' ";
			}
		}
	}
	switch($getMenu){
		case "entry":
			$totalRow = POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Entries WHERE blogid = {$blogid} AND draft = 0 {$getVisibility} AND category >= 0 {$getSQL}");
			break;
		case "comment":
			$totalRow = POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Comments WHERE blogid = {$blogid} AND entry > 0 AND isfiltered = 0 {$commenterOutSql} {$getSQL}");
			break;
		case "trackback":
			$totalRow = POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}RemoteResponses WHERE blogid = {$blogid} AND entry > 0 AND type = 'trackback' AND isfiltered = 0 {$getSQL}");
			break;
		case "guestbook":
			$totalRow = POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Comments WHERE blogid = {$blogid} AND entry = 0 AND isfiltered = 0 {$commenterOutSql} {$getSQL}");
			break;
		case "tag":
			$totalRow = POD::queryAll("SELECT name, COUNT(*) count FROM {$database['prefix']}Tags t, {$database['prefix']}TagRelations r WHERE t.id = r.tag and r.blogid = {$blogid} GROUP BY r.tag");
			$totalRow = count($totalRow);
			break;
		case "visit":
			$totalRow = POD::queryCell("SELECT SUM(visits) FROM {$database['prefix']}DailyStatistics WHERE blogid = {$blogid} {$getSQL}");
			break;
		case "referer":
			$totalRow = POD::queryCell("SELECT SUM(count) FROM {$database['prefix']}RefererStatistics WHERE blogid = {$blogid}");
			break;

		default:return false;
	}
	return $totalRow;
}

function getCategoryStatistics(){
	global $database, $blogid;
	$categories = array();
	$rows = POD::queryAll("SELECT * FROM {$database['prefix']}Categories WHERE blogid = {$blogid} AND id > 0 AND parent is Null ORDER BY parent, priority");
	foreach($rows as $child){
		array_push($categories, array('id'=>$child['id'],'name'=>$child['name']));
		$childrows = POD::queryAll("SELECT * FROM {$database['prefix']}Categories WHERE blogid = {$blogid} AND parent = {$child['id']} ORDER BY parent, priority");
		if($childrows){
			foreach($childrows as $leaf){
				array_push($categories, array('id'=>$leaf['id'],'name'=>$leaf['name']));
			}
		}
	}
	return $categories;
}

function getCategoryStatisticsTotal($id, $getDate){
	global $database, $blogid, $configVal;
	requireComponent('Textcube.Function.misc');
	$data = Misc::fetchConfigVal($configVal);
	if(is_null($data)){
		$data['privateChk'] = 2;
	}
	$getVisibility =($data['privateChk'] == 2)?" AND visibility > 0 ":"";
	$getYear  = substr($getDate, 0, 4);
	$getMonth = substr($getDate,4);
	if(strlen($getDate) == 4){
		$getSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME(published)) = '{$getYear}' ";
	}else{
		$getSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME(published)) = '{$getYear}' ";
		$getSQL .= " AND EXTRACT(MONTH FROM FROM_UNIXTIME(published)) = '{$getMonth}' ";
	}
	$totalRow = POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Entries WHERE blogid = {$blogid} AND draft = 0 {$getVisibility} AND category = {$id} {$getSQL}");
	return $totalRow;
}

function getEntryTitleById($blogid, $id) {
	global $database;
	$title = POD::queryCell("SELECT title FROM {$database['prefix']}Entries WHERE blogid = {$blogid} AND id = {$id}");
	return $title;
}

function getEntrySloganById($blogid, $id) {
	global $database;
	$title = POD::queryCell("SELECT slogan FROM {$database['prefix']}Entries WHERE blogid = {$blogid} AND id = {$id}");
	return $title;
}

function getEntryHitsStatistics($getDate){
	global $database, $blogid, $configVal;
	requireComponent('Textcube.Function.misc');
	$data = Misc::fetchConfigVal($configVal);
	if(is_null($data)){
		$data['privateChk'] = 2;
	}
	$getVisibility =($data['privateChk'] == 2)?" AND e.visibility > 0 ":"";
	$getYear  = substr($getDate, 0, 4);
	$getMonth = substr($getDate,4);
	if(strlen($getDate) == 4){
		$getSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME(e.published)) = '{$getYear}' ";
	}else{
		$getSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME(e.published)) = '{$getYear}' ";
		$getSQL .= " AND EXTRACT(MONTH FROM FROM_UNIXTIME(e.published)) = '{$getMonth}' ";
	}
	$totalRow =  POD::queryAll("SELECT e.id, e.title, c.hits FROM {$database['prefix']}Entries e LEFT  JOIN {$database['prefix']}Entries_hits c ON e.id = c.entry WHERE e.blogid = $blogid AND e.draft = 0 {$getVisibility} AND e.category >= 0 AND c.entry IS NOT NULL {$getSQL} ORDER BY c.hits DESC LIMIT 0, 10");
	return $totalRow;
}

function getTimeStatistics($getDate, $getHour, $getMenu){
	global $database, $blogid, $configVal;
	requireComponent('Textcube.Function.misc');
	$data = Misc::fetchConfigVal($configVal);
	if(is_null($data)){
		$data['repliesChk'] = 2;
		$data['privateChk'] = 2;
	}
	$getVisibility =($data['privateChk'] == 2)?" AND visibility > 0 ":"";
	$commenterOutSql = ($data['repliesChk'] == 2)?" AND replier is NULL ":"";
	$commenterOut = ($data['commenterout'])?explode("|",$data['commenterout']):"";
	if(!empty($commenterOut) && count($commenterOut) > 0){
		foreach ($commenterOut as $item){
			if(!empty($item)) $commenterOutSql .= " AND name NOT LIKE '%{$item}%' ";
		}
	}
	if(strlen($getHour) == 1) $getHour = "0".$getHour;
	if($getMenu == "entry"){$dateName = "published";}else{$dateName = "written";}
	$getYear  = substr($getDate, 0, 4);
	$getMonth = substr($getDate,4);
	if(strlen($getDate) == 4){
		$getSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME({$dateName})) = '{$getYear}' ";
	}else{
		$getSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME({$dateName})) = '{$getYear}' ";
		$getSQL .= " AND EXTRACT(MONTH FROM FROM_UNIXTIME({$dateName})) = '{$getMonth}' ";
	}

	switch($getMenu){
		case "entry":
			$totalRow = POD::queryRow("SELECT FROM_UNIXTIME(published, '%H') AS period, COUNT(*) AS count FROM {$database['prefix']}Entries WHERE blogid = {$blogid} AND draft = 0 {$getVisibility} AND category >= 0 {$getSQL} AND FROM_UNIXTIME(published, '%H') = '{$getHour}' GROUP BY period ORDER BY period ASC");
			break;
		case "comment":
			$totalRow = POD::queryRow("SELECT FROM_UNIXTIME(written, '%H') AS period, COUNT(*) AS count FROM {$database['prefix']}Comments WHERE blogid = {$blogid} AND entry > 0 AND isfiltered = 0 {$commenterOutSql} {$getSQL} AND FROM_UNIXTIME(written, '%H') = '{$getHour}' GROUP BY period ORDER BY period ASC");
			break;
		case "trackback":
			$totalRow = POD::queryRow("SELECT FROM_UNIXTIME(written, '%H') AS period, COUNT(*) AS count FROM {$database['prefix']}RemoteResponses WHERE blogid = {$blogid} AND entry > 0 AND type = 'trackback' AND isfiltered = 0 {$getSQL} AND FROM_UNIXTIME(written, '%H') = '{$getHour}' GROUP BY period ORDER BY period ASC");
			break;
		case "guestbook":
			$totalRow = POD::queryRow("SELECT FROM_UNIXTIME(written, '%H') AS period, COUNT(*) AS count FROM {$database['prefix']}Comments WHERE blogid = {$blogid} AND entry = 0 AND isfiltered = 0 {$commenterOutSql} {$getSQL} AND FROM_UNIXTIME(written, '%H') = '{$getHour}' GROUP BY period ORDER BY period ASC");
			break;
		default:return false;
	}
	return $totalRow;
}

function getCommentEntryMaxCount($getDate) {
	global $database, $blogid, $configVal;
	requireComponent('Textcube.Function.misc');
	$data = Misc::fetchConfigVal($configVal);
	if(is_null($data)){
		$data['repliesChk'] = 2;
		$data['privateChk'] = 2;
	}
	$getVisibility =($data['privateChk'] == 2)?" AND e.visibility > 0 ":"";
	$commenterOutSql = ($data['repliesChk'] == 2)?" AND c.replier is NULL ":"";
	$commenterOut = ($data['commenterout'])?explode("|",$data['commenterout']):"";
	if(!empty($commenterOut) && count($commenterOut) > 0){
		foreach ($commenterOut as $item){
			if(!empty($item)) $commenterOutSql .= " AND c.name NOT LIKE '%{$item}%' ";
		}
	}

	$getYear  = substr($getDate, 0, 4);
	$getMonth = substr($getDate,4);
	if(strlen($getDate) == 4){
		$getSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME(c.written)) = '{$getYear}' ";
	}else{
		$getSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME(c.written)) = '{$getYear}' ";
		$getSQL .= " AND EXTRACT(MONTH FROM FROM_UNIXTIME(c.written)) = '{$getMonth}' ";
	}
	$totalRow = POD::queryAll("SELECT COUNT(c.entry) as comments, e.title as title, e.id as id FROM {$database['prefix']}Comments c LEFT JOIN {$database['prefix']}Entries e ON c.blogid = e.blogid AND c.entry = e.id AND e.draft = 0 WHERE c.blogid = $blogid AND c.entry > 0 AND c.isfiltered = 0 {$commenterOutSql} {$getVisibility} {$getSQL} GROUP BY c.entry ORDER BY comments DESC LIMIT 0, 10");
	return $totalRow;
}

function getCommenterMaxCount($getDate, $getMenu) {
	global $database, $blogid, $configVal;
	requireComponent('Textcube.Function.misc');
	$data = Misc::fetchConfigVal($configVal);
	if(is_null($data)) $data['repliesChk'] = 2;
	$commenterOutSql = ($data['repliesChk'] == 2)?" AND replier is NULL ":"";
	$commenterOut = ($data['commenterout'])?explode("|",$data['commenterout']):"";
	if(!empty($commenterOut) && count($commenterOut) > 0){
		foreach ($commenterOut as $item){
			if(!empty($item)) $commenterOutSql .= " AND name NOT LIKE '%{$item}%' ";
		}
	}
	$getYear  = substr($getDate, 0, 4);
	$getMonth = substr($getDate,4);
	if(strlen($getDate) == 4){
		$getSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME(written)) = '{$getYear}' ";
	}else{
		$getSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME(written)) = '{$getYear}' ";
		$getSQL .= " AND EXTRACT(MONTH FROM FROM_UNIXTIME(written)) = '{$getMonth}' ";
	}
	switch($getMenu){
		case "comment":
			$totalRow = POD::queryAll("SELECT name, COUNT(name) as namecnt FROM {$database['prefix']}Comments WHERE blogid = {$blogid} AND entry > 0 AND isfiltered = 0 {$commenterOutSql} {$getSQL} GROUP BY name ORDER BY namecnt DESC LIMIT 0, 10");
			break;
		case "guestbook":
			$totalRow = POD::queryAll("SELECT name, COUNT(name) as namecnt FROM {$database['prefix']}Comments WHERE blogid = {$blogid} AND entry = 0 AND isfiltered = 0 {$commenterOutSql} {$getSQL} GROUP BY name ORDER BY namecnt DESC LIMIT 0, 10");
			break;
		case "commenter":
			$totalRow = POD::queryAll("SELECT name, Max(homepage) as home, COUNT(name) as namecnt FROM {$database['prefix']}Comments WHERE blogid = {$blogid} AND entry > 0 AND isfiltered = 0 {$commenterOutSql} {$getSQL} GROUP BY name ORDER BY namecnt DESC");
			break;
		case "guestbookcommenter":
			$totalRow = POD::queryAll("SELECT name, Max(homepage) as home, COUNT(name) as namecnt FROM {$database['prefix']}Comments WHERE blogid = {$blogid} AND entry = 0 AND isfiltered = 0 {$commenterOutSql} {$getSQL} GROUP BY name ORDER BY namecnt DESC");
			break;
		default:return false;
	}
	return $totalRow;
}

function getTrackbackEntryMaxCount($getDate) {
	global $database, $blogid;
	$getYear  = substr($getDate, 0, 4);
	$getMonth = substr($getDate,4);
	if(strlen($getDate) == 4){
		$getSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME(t.written)) = '{$getYear}' ";
	}else{
		$getSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME(t.written)) = '{$getYear}' ";
		$getSQL .= " AND EXTRACT(MONTH FROM FROM_UNIXTIME(t.written)) = '{$getMonth}' ";
	}
	$totalRow = POD::queryAll("SELECT COUNT(t.entry) as trackbacks, e.title as title, e.id as id FROM {$database['prefix']}RemoteResponses t LEFT JOIN {$database['prefix']}Entries e ON t.blogid = e.blogid AND t.entry = e.id AND e.draft = 0 WHERE t.blogid = $blogid AND t.entry > 0 AND t.type = 'trackback' AND t.isfiltered = 0 {$getSQL} GROUP BY t.entry ORDER BY trackbacks DESC LIMIT 0, 10");
	return $totalRow;
}

function getTrackbackCallEntryMaxCount($getDate) {
	global $database, $blogid, $configVal;
	requireComponent('Textcube.Function.misc');
	$data = Misc::fetchConfigVal($configVal);
	if(is_null($data)){
		$data['privateChk'] = 2;
	}
	$getVisibility =($data['privateChk'] == 2)?" AND e.visibility > 0 ":"";
	$getYear  = substr($getDate, 0, 4);
	$getMonth = substr($getDate,4);
	if(strlen($getDate) == 4){
		$getSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME(t.written)) = '{$getYear}' ";
	}else{
		$getSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME(t.written)) = '{$getYear}' ";
		$getSQL .= " AND EXTRACT(MONTH FROM FROM_UNIXTIME(t.written)) = '{$getMonth}' ";
	}
	$totalRow = POD::queryAll("SELECT COUNT(t.entry) as trackbacklogs, e.title as title, e.id as id FROM {$database['prefix']}RemoteResponseLogs t LEFT JOIN {$database['prefix']}Entries e ON t.blogid = e.blogid AND t.entry = e.id AND e.draft = 0 WHERE t.blogid = $blogid AND t.entry > 0 AND t.type = 'trackback' {$getVisibility} {$getSQL} GROUP BY t.entry ORDER BY trackbacklogs DESC LIMIT 0, 10");
	return $totalRow;
}

function getRefererMaxCount() {
	global $database, $blogid;
	$totalRow = POD::queryAll("SELECT host, count FROM {$database['prefix']}RefererStatistics WHERE blogid = {$blogid} ORDER BY count DESC LIMIT 0, 10");
	return $totalRow;
}

function getTagMaxCount() {
	global $database, $blogid;
	$totalRow = POD::queryAll("SELECT name, COUNT(*) count FROM {$database['prefix']}Tags t, {$database['prefix']}TagRelations r WHERE t.id = r.tag and r.blogid = {$blogid} GROUP BY r.tag ORDER BY count DESC LIMIT 0, 10");
	return $totalRow;
}

function getTagEntryMaxCount($getDate, $getMode) {
	global $database, $blogid, $configVal;
	requireComponent('Textcube.Function.misc');
	$data = Misc::fetchConfigVal($configVal);
	if(is_null($data)){
		$data['privateChk'] = 2;
	}
	$getVisibility =($data['privateChk'] == 2)?" AND e.visibility > 0 ":"";
	$getYear  = substr($getDate, 0, 4);
	$getMonth = substr($getDate,4);
	if(strlen($getDate) == 4){
		$getSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME(e.published)) = '{$getYear}' ";
	}else{
		$getSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME(e.published)) = '{$getYear}' ";
		$getSQL .= " AND EXTRACT(MONTH FROM FROM_UNIXTIME(e.published)) = '{$getMonth}' ";
	}
	if($getMode){
		$totalRow = POD::queryAll("SELECT e.title, e.id, COUNT(*) count FROM {$database['prefix']}Entries e LEFT JOIN {$database['prefix']}TagRelations t ON e.blogid = t.blogid WHERE e.blogid = {$blogid}  AND e.id = t.entry {$getVisibility} {$getSQL} GROUP BY t.entry ORDER BY count DESC LIMIT 0, 10");
	}else{
		$totalRow = POD::queryAll("SELECT COUNT(*) FROM {$database['prefix']}Entries e LEFT JOIN {$database['prefix']}TagRelations t ON e.blogid = t.blogid WHERE e.blogid = {$blogid}  AND e.id = t.entry {$getVisibility} {$getSQL} GROUP BY t.entry");
		$totalRow= count($totalRow);
	}
	return $totalRow;
}

function getQuartersStatistics($getDate, $quarters, $getMode){
	global $database, $blogid, $configVal;
	requireComponent('Textcube.Function.misc');
	$data = Misc::fetchConfigVal($configVal);
	if(is_null($data)){
		$data['repliesChk'] = 2;
		$data['privateChk'] = 2;
	}
	$getVisibility =($data['privateChk'] == 2)?" AND visibility > 0 ":"";
	$commenterOutSql = ($data['repliesChk'] == 2)?" AND replier is NULL ":"";
	$commenterOut = ($data['commenterout'])?explode("|",$data['commenterout']):"";
	if(!empty($commenterOut) && count($commenterOut) > 0){
		foreach ($commenterOut as $item){
			if(!empty($item)) $commenterOutSql .= " AND name NOT LIKE '%{$item}%' ";
		}
	}

	$getYear  = substr($getDate, 0, 4);
	if($getMode == "visit"){
		switch($quarters){
			case 1:
				$quartersSQL  = ($getYear==9999)?"":" AND LEFT(date, 4) = '{$getYear}' ";
				$quartersSQL .= " AND MID(date, 5, 2) >= 01 AND MID(date, 5, 2) <= 03 ";
				break;
			case 2:
				$quartersSQL  = ($getYear==9999)?"":" AND LEFT(date, 4) = '{$getYear}' ";
				$quartersSQL .= " AND MID(date, 5, 2) >= 04 AND MID(date, 5, 2) <= 06 ";
				break;
			case 3:
				$quartersSQL  = ($getYear==9999)?"":" AND LEFT(date, 4) = '{$getYear}' ";
				$quartersSQL .= " AND MID(date, 5, 2) >= 07 AND MID(date, 5, 2) <= 09 ";
				break;
			case 4:
				$quartersSQL  = ($getYear==9999)?"":" AND LEFT(date, 4) = '{$getYear}' ";
				$quartersSQL .= " AND MID(date, 5, 2) >= 10 AND MID(date, 5, 2) <= 12 ";
				break;
			default:return false;
		}
	}else{
		if($getMode == "entry"){$dateName = "published";}else{$dateName = "written";}
		switch($quarters){
			case 1:
				$quartersSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME({$dateName})) = '{$getYear}'";
				$quartersSQL .= " AND EXTRACT(MONTH FROM FROM_UNIXTIME({$dateName})) >=  1 AND EXTRACT(MONTH FROM FROM_UNIXTIME({$dateName})) <=  3 ";
				break;
			case 2:
				$quartersSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME({$dateName})) = '{$getYear}'";
				$quartersSQL .= " AND EXTRACT(MONTH FROM FROM_UNIXTIME({$dateName})) >=  4 AND EXTRACT(MONTH FROM FROM_UNIXTIME({$dateName})) <=  6 ";
				break;
			case 3:
				$quartersSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME({$dateName})) = '{$getYear}'";
				$quartersSQL .= " AND EXTRACT(MONTH FROM FROM_UNIXTIME({$dateName})) >=  7 AND EXTRACT(MONTH FROM FROM_UNIXTIME({$dateName})) <=  9 ";
				break;
			case 4:
				$quartersSQL  = ($getYear==9999)?"":" AND EXTRACT(YEAR FROM FROM_UNIXTIME({$dateName})) = '{$getYear}'";
				$quartersSQL .= " AND EXTRACT(MONTH FROM FROM_UNIXTIME({$dateName})) >= 10 AND EXTRACT(MONTH FROM FROM_UNIXTIME({$dateName})) <= 12 ";
				break;
			default:return false;
		}
	}

	switch($getMode){
		case "entry":
			$totalRow = POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Entries WHERE blogid = {$blogid} AND draft = 0 {$getVisibility} AND category >= 0 {$quartersSQL}");
			break;
		case "comment":
			$totalRow = POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Comments WHERE blogid = {$blogid} AND entry > 0 AND isfiltered = 0 {$commenterOutSql} {$quartersSQL}");
			break;
		case "trackback":
			$totalRow = POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}RemoteResponses WHERE blogid = {$blogid} AND entry > 0 AND type = 'trackback' AND isfiltered = 0 {$quartersSQL}");
			break;
		case "guestbook":
			$totalRow = POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Comments WHERE blogid = {$blogid} AND entry = 0 AND isfiltered = 0 {$commenterOutSql} {$quartersSQL}");
			break;
		case "visit":
			$totalRow = POD::queryCell("SELECT SUM(visits) FROM {$database['prefix']}DailyStatistics WHERE blogid = {$blogid} {$quartersSQL}");
			break;
		default:return false;
	}
	return $totalRow;
}

function getRefererKeywordStatistics(){
	$more = false;
	$refereres = getRefererLogsDB();
	$keywordlist = array();
	$record = array();

	for ($i=0; $i<sizeof($refereres); $i++) {
		$record = $refereres[$i];
		if ($i==0) $referredend = $record['referred'];
			$keyword = "";
			if(preg_match('/\W(q|query|k|keyword|search|stext|nlia|aqa|wd)(?:=|%3D)([^&]+)/i', $record['url'], $matches))
				$keyword = urldecode(rawurldecode($matches[2]));
			else if(strpos($record['url'], 'yahoo.') !== false && preg_match('/\Wp=([^&]+)/i', $record['url'], $matches))
				$keyword = urldecode(rawurldecode($matches[1]));
			else if(preg_match('@/search/(?:\w+/)*([^/?]+)@i', $record['url'], $matches))
				$keyword = urldecode(rawurldecode($matches[1]));
			if(!UTF8::validate($keyword))
				$keyword = UTF8::correct(UTF8::bring($keyword));

		if (array_key_exists($keyword, $keywordlist)) {
			$keywordlist[$keyword]++;
		}
		elseif ($keyword) { $keywordlist[$keyword] = 1; }

	}
	$referredstart = array_key_exists('referred', $record) ? $record['referred'] : '';
	$keywordlist = RefererKeywordArraySort($keywordlist,'desc');
	$keywordkeys = array_keys($keywordlist);
	$beforekeywordvalue = '';
	$rank = 0;
	$keywordArray = array();
	for ($i=0; $i<sizeof($keywordlist); $i++) {
		$keywordkey = $keywordkeys[$i];
		$keywordvalue = $keywordlist[$keywordkey];
		$keywordkey = str_replace("\"", "&quot;",$keywordkeys[$i]);
		if ($keywordvalue != $beforekeywordvalue){
			$rank++;
			$beforekeywordvalue = $keywordvalue;
		}
		array_push($keywordArray, array('keyword' => $keywordkey, 'count' => $keywordvalue, 'total' => count($keywordlist), 'rank' => $rank, 'dateStart' => Timestamp::formatDate($referredstart), 'dateEnd' => Timestamp::formatDate($referredend)));
	}
	return $keywordArray;
}

function getRefererLogsDB() {
	global $database, $blogid;
	return POD::queryAll("SELECT host, url, referred FROM {$database['prefix']}RefererLogs WHERE blogid = $blogid ORDER BY referred DESC LIMIT 1500");
}

function RefererKeywordArraySort($array, $type='asc'){
   $result=array();
   foreach($array as $var => $val){
       $set=false;
       foreach($result as $var2 => $val2){
           if($set==false){
               if($val>$val2 && $type=='desc' || $val<$val2 && $type=='asc'){
                   $temp=array();
                   foreach($result as $var3 => $val3){
                       if($var3==$var2) $set=true;
                       if($set){
                           $temp[$var3]=$val3;
                           unset($result[$var3]);
                       }
                   }
                   $result[$var]=$val;
                   foreach($temp as $var3 => $val3){
                       $result[$var3]=$val3;
                   }
               }
           }
       }
       if(!$set){
           $result[$var]=$val;
       }
   }
   return $result;
}
?>
