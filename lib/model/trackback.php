<?
require 'correctTT.php';

function getTrackbacksWithPagingForOwner($owner, $category, $site, $ip, $search, $page, $count) {
	global $database;
	$sql = "SELECT t.*, c.name categoryName FROM {$database['prefix']}Trackbacks t LEFT JOIN {$database['prefix']}Entries e ON t.owner = e.owner AND t.entry = e.id AND e.draft = 0 LEFT JOIN {$database['prefix']}Categories c ON t.owner = c.owner AND e.category = c.id WHERE t.owner = $owner";
	if ($category > 0) {
		$categories = fetchQueryColumn("SELECT id FROM {$database['prefix']}Categories WHERE owner = $owner AND parent = $category");
		array_push($categories, $category);
		$sql .= ' AND e.category IN (' . implode(', ', $categories) . ')';
	} else
		$sql .= ' AND e.category >= 0';
	if (!empty($site))
		$sql .= ' AND t.site = \'' . mysql_escape_string($site) . '\'';
	if (!empty($ip))
		$sql .= ' AND t.ip = \'' . mysql_escape_string($ip) . '\'';
	if (!empty($search)) {
		$search = mysql_escape_string($search);
		$sql .= " AND (t.site LIKE '%$search%' OR t.subject LIKE '%$search%' OR t.excerpt LIKE '%$search%')";
	}
	$sql .= ' ORDER BY t.written DESC';
	return fetchWithPaging($sql, $page, $count);
}

function getTrackbacks($entry) {
	global $database, $owner;
	$trackbacks = array();
	$result = mysql_query("select * from {$database['prefix']}Trackbacks where owner = $owner AND entry = $entry order by written");
	while ($trackback = mysql_fetch_array($result))
		array_push($trackbacks, $trackback);
	return $trackbacks;
}

function getRecentTrackbacks($owner) {
	global $database;
	global $skinSetting;
	$trackbacks = array();
	$sql = doesHaveOwnership() ? "SELECT * FROM {$database['prefix']}Trackbacks WHERE owner = $owner ORDER BY written DESC LIMIT {$skinSetting['trackbacksOnRecent']}" : "SELECT t.* FROM {$database['prefix']}Trackbacks t, {$database['prefix']}Entries e WHERE t.owner = $owner AND t.owner = e.owner AND t.entry = e.id AND e.draft = 0 AND e.visibility >= 2 ORDER BY t.written DESC LIMIT {$skinSetting['trackbacksOnRecent']}";
	if ($result = mysql_query($sql)) {
		while ($trackback = mysql_fetch_array($result))
			array_push($trackbacks, $trackback);
	}
	return $trackbacks;
}

function receiveTrackback($owner, $entry, $title, $url, $excerpt, $blog_name) {
	global $database;
	$title = mysql_escape_string(correctTTForXmlText($title));
	$url = mysql_escape_string($url);
	$excerpt = mysql_escape_string(correctTTForXmlText($excerpt));
	$blog_name = mysql_escape_string($blog_name);
	requireComponent('Tattertools.Data.Filter');
	if (Filter::isFiltered('url', $url))
		return 1;
	$result = mysql_query("SELECT * FROM {$database['prefix']}Entries WHERE owner = $owner AND id = $entry AND draft = 0 AND visibility > 0 AND acceptTrackback = 1");
	if (mysql_num_rows($result) == 0)
		return 3;
	if (Filter::isFiltered('content', $excerpt))
		return 1;
	$trackbacks = mysql_fetch_array($result);
	if (fetchQueryCell("SELECT count(*) FROM {$database['prefix']}Trackbacks WHERE entry=$entry AND url='$url' AND owner=$owner") == 0) {
		mysql_query("INSERT INTO {$database['prefix']}Trackbacks VALUES ('', $owner, $entry, '$url', NULL, '$blog_name', '$title', '$excerpt', '{$_SERVER['REMOTE_ADDR']}', UNIX_TIMESTAMP())");
	} else {
		return 4;
	}
	updateTrackbacksOfEntry($owner, $entry);
	return 0;
}

function deleteTrackback($owner, $id) {
	global $database;
	$entry = fetchQueryCell("SELECT entry FROM {$database['prefix']}Trackbacks WHERE owner = $owner AND id = $id");
	if ($entry === null)
		return false;
	if (!executeQuery("DELETE FROM {$database['prefix']}Trackbacks WHERE owner = $owner AND id = $id"))
		return false;
	if (updateTrackbacksOfEntry($owner, $entry))
		return $entry;
	return false;
}

function sendTrackback($owner, $entryId, $url) {
	global $database;
	global $hostURL, $blogURL, $blog;
	requireComponent('Eolin.PHP.HTTPRequest');
	$entry = getEntry($owner, $entryId);
	if (!$entry)
		return false;
	$link = "$hostURL$blogURL/$entryId";
	$title = $entry['title'];
	$excerpt = utf8Lessen(removeAllTags(stripHTML($entry['content'])), 255);
	$blogTitle = $blog['title'];
	$blogURL = "$hostURL$blogURL/";
	$isNeedConvert = strpos($url, '/rserver.php?') !== false || strpos($url, 'blog.naver.com') !== false || strpos($url, '.egloos.com/tb/') !== false;
	if ($isNeedConvert) {
		$title = iconvWrapper('UTF-8', 'EUC-KR', $title);
		$excerpt = iconvWrapper('UTF-8', 'EUC-KR', $excerpt);
		$blogTitle = iconvWrapper('UTF-8', 'EUC-KR', $blogTitle);
		$content = "url=" . rawurlencode($link) . "&title=" . rawurlencode($title) . "&blog_name=" . rawurlencode($blogTitle) . "&excerpt=" . rawurlencode($excerpt);
		$request = new HTTPRequest('POST', $url);
		$request->contentType = 'application/x-www-form-urlencoded; charset=euc-kr';
		$isSuccess = $request->send($content);
	} else {
		$content = "url=" . rawurlencode($link) . "&title=" . rawurlencode($title) . "&blog_name=" . rawurlencode($blogTitle) . "&excerpt=" . rawurlencode($excerpt);
		$request = new HTTPRequest('POST', $url);
		$request->contentType = 'application/x-www-form-urlencoded; charset=utf-8';
		$isSuccess = $request->send($content);
	}
	if ($isSuccess && (checkResponseXML($request->responseText) === 0)) {
		$url = mysql_escape_string($url);
		mysql_query("insert into {$database['prefix']}TrackbackLogs values ($owner, '', $entryId, '$url', UNIX_TIMESTAMP())");
		return true;
	}
	return false;
}

function getTrackbackLog($owner, $entry) {
	global $database;
	$result = mysql_query("select * from {$database['prefix']}TrackbackLogs where owner = $owner and entry = $entry");
	$str = '';
	while ($row = mysql_fetch_array($result)) {
		$str .= $row['id'] . ',' . $row['url'] . ',' . Timestamp::format5($row['written']) . '*';
	}
	return $str;
}

function getTrackbackLogs($owner, $entryId) {
	global $database;
	$logs = array();
	$result = mysql_query("select * from {$database['prefix']}TrackbackLogs where owner = $owner and entry = $entryId");
	while ($log = mysql_fetch_array($result))
		array_push($logs, $log);
	return $logs;
}

function deleteTrackbackLog($owner, $id) {
	global $database;
	$result = mysql_query("delete from {$database['prefix']}TrackbackLogs where owner = $owner and id = $id");
	return ($result && (mysql_affected_rows() == 1)) ? true : false;
}

function lastIndexOf($string, $item) {
	$index = strpos(strrev($string), strrev($item));
	if ($index) {
		$index = strlen($string) - strlen($item) - $index;
		return $index;
	} else
		return - 1;
}

function getURLForFilter($value) {
	$value = mysql_escape_string($value);
	$value = str_replace('http://', '', $value);
	$lastSlashPos = lastIndexOf($value, '/');
	if ($lastSlashPos > - 1) {
		$value = substr($value, 0, $lastSlashPos);
	}
	return $value;
}
?>