<?php
/*
	Plugin Name: Defensio Anti-Spam
    Plugin URI: http://defensio.com/
	Description: Defensio is an advanced spam filtering web service that learns and adapts to your behaviors and those of your readers.

	Author: JongHyuk Park (bliss@hanirc.org) - http://bliss.hanirc.org
	Version: 0.2 beta (20080827)

	LICENSE : GPL

	Based on -

	[Defensio anti-spam for wordpress]
	Version: 1.1
	Author: Karabunga, Inc
	Author URI: http://karabunga.com/

	[EAS-Eolin Antispam Service]
	Version: 0.95beta
	Author: Tatter & Company
 
 */
include_once("lib/spyc.php");

requireModel("blog.trackback");
requireModel("blog.comment");

define('DF_SUCCESS', 'success');
define('DF_FAIL', 'fail');


function defensio_create_table() {
	global $database, $entry;

	$name = "defensio" ;
	$plugin = "Defensio";
	$version = "0.1 beta";

	$table_name = $database['prefix'] . $name;
	$row = POD::queryRow("SHOW TABLES LIKE '$table_name'") ;
	if ( !$row ) {
		$sql = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
			blog_ID int(11) NOT NULL, 
			comment_ID int(11) NOT NULL, 
			comment_TYPE char(1) NOT NULL DEFAULT 'C', 
			spaminess FLOAT NOT NULL, 
			signature VARCHAR(55) NOT NULL, 
			PRIMARY KEY (blog_ID,comment_ID, comment_TYPE)
		);";
		if ( POD::execute($sql) ) {
			$keyname = POD::escapeString(UTF8::lessenAsEncoding('Database_' . $name, 32));
			$value = POD::escapeString(UTF8::lessenAsEncoding($plugin . '/' . $version , 255));
			POD::execute("INSERT INTO {$database['prefix']}ServiceSettings SET name='$keyname', value ='$value'");
		}
	}
}

// Post an action to Defesio and use args as POST data, returns false on error 
function defensio_post($action, $args = null) {
	global $defensio_conf;

	// Use snoopy to post
	require_once ('lib/class-snoopy.php');

	$snoopy = new Snoopy();
	$snoopy->read_timeout = $defensio_conf['post_timeout'];

	// Supress the possible fsock warning 
	@$snoopy->submit(defensio_url_for($action, $defensio_conf['key']), $args, array ());

	// Defensio will return 200 nomally, 401 on authentication failure, anything else is unexpected behaivour
	if ($snoopy->status == 200 or $snoopy->status == 401) {
		return $snoopy->results; 
	} else {
		return false;
	}
}

// Returns the URL for possible actions
function defensio_url_for($action, $key = null) {
	global $defensio_conf;

	if ($key == null) {
		return null;
	} else {
		if ($action == 'validate-key')	   { return 'http://' . $defensio_conf['server'] . '/' . $defensio_conf['path'] . '/' . $defensio_conf['api-version'] . '/' . $action . '/' . $key . '.' . $defensio_conf['format']; }
		if ($action == 'audit-comment')	   { return 'http://' . $defensio_conf['server'] . '/' . $defensio_conf['path'] . '/' . $defensio_conf['api-version'] . '/' . $action . '/' . $key . '.' . $defensio_conf['format']; }
		if ($action == 'report-false-negatives') { return 'http://' . $defensio_conf['server'] . '/' . $defensio_conf['path'] . '/' . $defensio_conf['api-version'] . '/' . $action . '/' . $key . '.' . $defensio_conf['format']; }
		if ($action == 'report-false-positives') { return 'http://' . $defensio_conf['server'] . '/' . $defensio_conf['path'] . '/' . $defensio_conf['api-version'] . '/' . $action . '/' . $key . '.' . $defensio_conf['format']; }
		if ($action == 'get-stats')		   { return 'http://' . $defensio_conf['server'] . '/' . $defensio_conf['path'] . '/' . $defensio_conf['api-version'] . '/' . $action . '/' . $key . '.' . $defensio_conf['format']; }
		if ($action == 'announce-article') { return 'http://' . $defensio_conf['server'] . '/' . $defensio_conf['path'] . '/' . $defensio_conf['api-version'] . '/' . $action . '/' . $key .  '.' . $defensio_conf['format']; }
	}

	return null;
}

function defensio_verify_key($key) {
	global $defensio_conf;

	DEFENSIO_Init();
	$defensio_conf['key'] = $key;

	$result = false;
	$params = array('key'		=> $key, 'owner-url' => $defensio_conf['blog']);

	if ($r = defensio_post('validate-key', $params)) {
		// Parse result
		$ar = Spyc :: YAMLLoad($r);
		// Spyc will return an empty array in case the result is not a well-formed YAML string.
		// Verify that the array is a valid Defensio result before continuing
		if (isset ($ar['defensio-result'])) {
			if ($ar['defensio-result']['status'] == DF_SUCCESS) {
				$result = true; 
				return $result;
			}
		} else {
			return $result;
		}
	} else {
	  return $result;
	}

	return $result;
}

function defensio_submit_ham($signatures) {
	global $defensio_conf;
	DEFENSIO_Init();
	$params = array(
		'signatures' => $signatures,
		'owner-url'	 => $defensio_conf['blog']
	);

	$r = defensio_post('report-false-positives', $params);
}


function defensio_submit_spam($signatures){
	global $defensio_conf;
	DEFENSIO_Init();
	$params = array(
		'signatures' => $signatures,
		'owner-url'	 => $defensio_conf['blog']
	);

	$r = defensio_post('report-false-negatives', $params);
}

function defensio_get_stats() {
	global $defensio_conf;

	DEFENSIO_Init();
	$r = defensio_post('get-stats', array('owner-url' => $defensio_conf['blog'])); 

	$ar = Spyc::YAMLLoad($r); 
	if (isset($ar['defensio-result'])) {
		//defensio_update_stats_cache($ar['defensio-result']);
		return $ar['defensio-result'];
	} else {
		return false;
	}
}

function defensio_unescape_string($str) {
	return stripslashes($str);
}

function DEFENSIO_Init() {
	global $configVal, $hostURL, $defensio_conf;
	requireComponent('Textcube.Function.misc');

	$blogHome = preg_replace("'^http://'", "", $hostURL);
	$data = misc::fetchConfigVal( $configVal );
	$defensio_conf = array(
		'server'			=> 'api.defensio.com',
		'path'				=> 'blog',
		'api-version'		=> '1.2',
		'format'			=> 'yaml',
		'blog'				=> $blogHome,
		'key'				=> $data['apikey'],
		'force_with_tca'	=> isset($data['force_with_tca'])? $data['force_with_tca'] : 0,
		'post_timeout'		=> 10
	);
}

function defensio_save_meta_data($comment_TYPE, $comment_ID, $defensio_meta) {
	global $database;
	$meta = $defensio_meta;
	//Create Defensio record
	if (isset($meta['spaminess']) and isset($meta['signature'])) {
		POD::execute("INSERT INTO {$database['prefix']}defensio{$postfix} (blog_ID, comment_ID, comment_TYPE, spaminess, signature) 
			VALUES	( " . getBlogId() . ", {$comment_ID} , '{$comment_TYPE}', {$meta['spaminess']}, '{$meta['signature']}')");
	} else {
		POD::execute("INSERT INTO {$database['prefix']}defensio{$postfix} (blog_ID, comment_ID, comment_TYPE, spaminess, signature) 
			VALUES	(" . getBlogId() . " {$comment_ID}, '{$comment_TYPE}',  -1 , '' )");
	}
	return $comment_ID;
}
function defensio_make_to_spam( $comment_TYPE, $comment_ID = 0 ) {
	global $database;

	$table = $comment_TYPE == 'C' ? "Comments" : "Trackbacks";
	$blogid = getBlogId() ;

	$sql  = "SELECT c.*, d.spaminess, d.signature FROM {$database['prefix']}{$table} c LEFT JOIN  {$database['prefix']}defensio d
		ON c.blogid = d.blog_ID and c.id = d.comment_ID 
		WHERE d.blog_ID = '$blogid' and d.comment_TYPE = '$comment_TYPE' and c.isFiltered > 0 and d.spaminess <= 0";
	if ( $comment_ID ) $sql .= " and c.id in ($comment_ID)";

	$r = POD::queryAll($sql);

	if ( !$r || !count($r) ) return;

	$spams = array();
	$comment_IDs = array();
	foreach($r as $c) {
		if ( $c['spaminess'] < 0 ) {
			// check again ?? 
		}
		else {
			array_push($spams, $c['signature']);
			array_push($comment_IDs, $c['id']);
		}
	}

	// make ham to spam
	if ( count($spams) > 0 ) {
		defensio_submit_spam( implode(',',$spams) );
		$string_id = implode(',', $comment_IDs);
		$sql = "UPDATE {$database['prefix']}defensio SET spaminess = 1 WHERE blog_ID = '$blogid' and comment_TYPE = '$comment_TYPE' and comment_ID in ($string_id)";
		POD::execute($sql);
	}
}

function defensio_make_to_ham( $comment_TYPE, $comment_ID = 0 ) 
{
	global $database;

	$table = $comment_TYPE == 'C' ? "Comments" : "Trackbacks";
	$blogid = getBlogId() ;

	$sql  = "SELECT c.*, d.spaminess, d.signature FROM {$database['prefix']}{$table} c LEFT JOIN  {$database['prefix']}defensio d
		ON c.blogid = d.blog_ID and c.id = d.comment_ID 
		WHERE d.blog_ID = '$blogid' and d.comment_TYPE = '$comment_TYPE' and c.isFiltered = 0 and d.spaminess > 0";
	if ( $comment_ID ) $sql .= " and c.id in ($comment_ID)";

	$r = POD::queryAll($sql);

	if ( !$r || !count($r) ) return;

	$hams = array();
	$comment_IDs = array();
	foreach($r as $c) {
		array_push($hams , $c['signature']);
		array_push($comment_IDs, $c['id']);
	}

	// make spam to ham 
	if ( count($hams) > 0 ) {
		defensio_submit_ham( implode(',',$hams) );
		$string_id = implode(',', $comment_IDs);
		$sql = "UPDATE {$database['prefix']}defensio SET spaminess = 0 WHERE blog_ID = '$blogid' and comment_TYPE = '$comment_TYPE' and comment_ID in ($string_id)";
		POD::execute($sql);

		//
		//$sql = "UPDATE {$database['prefix']}{$table} SET isFiltered = 0 WHERE blogid = '$blogid' and id in ($string_id)";
		//POD::execute($sql);
	}
}

function defensio_clear_comments($comment_TYPE, $IDs = array()) 
{
	global $database;

	$table = $comment_TYPE == 'C' ? "Comments" : "Trackbacks";
	$blogid = getBlogId() ;

	$sql = "SELECT D.* from {$database['prefix']}{$table} C RIGHT OUTER JOIN {$database['prefix']}defensio D
		ON C.blogid = D.blog_ID and C.id = D.comment_ID WHERE D.blog_ID = '$blogid' and D.comment_TYPE = '$comment_TYPE' and C.id IS NULL";
	$r = POD::queryAll($sql);

	if ( is_array($r) && count($r) > 0 ) {
		foreach($r as $c) {
			$a[] = $c['comment_ID'];
		}
		$string_id = implode(',', $a);
		$sql = "DELETE FROM {$database['prefix']}defensio WHERE blog_ID = '$blogid' and comment_TYPE = '$comment_TYPE' and comment_ID in ($string_id)";
		POD::execute($sql);
	}

	/*
	// remove old comments from defensio table for better performance.
	$olds = defensio_get_all_comments_id( $comment_TYPE, strtotime('-3 month'));
	if ( count($olds) > 0 ) $IDs = array_unique(array_merge($IDs, $olds));
	 */

	if ( is_array($IDs) && count($IDs) > 0 ){
		$string_id = implode(',', $IDs);
		$sql = "DELETE FROM {$database['prefix']}defensio WHERE blog_ID = '$blogid' and comment_TYPE = '$comment_TYPE' and comment_ID in ($string_id)";
		POD::execute($sql);
	}
}

function defensio_get_all_comments_id( $comment_TYPE , $ts = 0) {
	global $database;
	$table = $comment_TYPE == 'C' ? "Comments" : "Trackbacks";
	$blogid = getBlogId() ;
	$sql  = "SELECT c.id  FROM {$database['prefix']}{$table} c LEFT JOIN  {$database['prefix']}defensio d
		ON c.blogid = d.blog_ID and c.id = d.comment_ID WHERE d.blog_ID = '$blogid' and  d.comment_TYPE = '{$comment_TYPE}' and c.isFiltered > 0";
	if ( $ts ) $sql .= " c.written < $ts";

	$r = POD::queryAll($sql);
	$id = array();
	foreach($r as $i) { $id[] = $i['id']; }

	return $id;
}

function defensio_get_data( $comment_TYPE , $page, $count) {
	global $database;
	$table = $comment_TYPE == 'C' ? "Comments" : "Trackbacks";
	$blogid = getBlogId() ;
	$sql  = "SELECT c.*, d.spaminess, d.signature FROM {$database['prefix']}{$table} c LEFT JOIN  {$database['prefix']}defensio d
		ON c.blogid = d.blog_ID and c.id = d.comment_ID WHERE d.blog_ID = '$blogid' and  d.comment_TYPE = '{$comment_TYPE}' and c.isFiltered > 0";
	$sql .= " ORDER BY c.written DESC";
	
	return fetchWithPaging($sql, $page, $count);
	//return POD::queryAll($qry);
}

function defensio_get_all_spams_count($comment_TYPE = '') {
	global $database;
	$blogid = getBlogId() ;
	$sql = "SELECT count(*) FROM {$database['prefix']}defensio WHERE blog_ID = '$blogid' and spaminess > 0";
	if ( $comment_TYPE != '' ) $sql .= " comment_TYPE = '$comment_TYPE'";
	$r = POD::queryCell($sql);
	return $r ? $r : 0;
}

function getCommentsNextId() {
	global $database;
	$maxId = POD::queryCell("SELECT max(id) FROM {$database['prefix']}Comments WHERE blogid = ".getBlogId());
	return empty($maxId) ? 1 : $maxId + 1;
}

function getTrackBacksNextId() {
	global $database;
	$maxId = POD::queryCell("SELECT max(id) FROM {$database['prefix']}Trackbacks WHERE blogid = ".getBlogId());
	return empty($maxId) ? 1 : $maxId + 1;
}



function DEFNENSIO_FILTER($type, $name, $title, $url, $content, $openid= false)
{
	global $hostURL, $blogURL, $database, $configVal, $defensio_conf;

	//if ( doesHaveOwnership() ) return true; // owner

	DEFENSIO_Init();
	$defensio_meta = array();
	$comment = array();

	$comment['referrer'] = $_SERVER['HTTP_REFERER'];
	$comment['user-ip'] = preg_replace('/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR']);
	$comment['user-ip'] = '168.126.63.1';

	$comment['owner-url'] = $defensio_conf['blog'];

	$comment['comment_type'] = ( $type == 2 ) ? 'trackback' : 'comment';
	$comment['comment-author'] = $name;
	$comment['article-date'] = strftime("%Y/%m/%d", time());

	// $comment['permalink'] = $comment_perma_link;

	// Make sure it we don't send an SQL escaped string to the server
	$comment['comment-content'] = defensio_unescape_string($content);
	$comment['comment-author-url'] = $url;
	//$comment['comment-author-email'] = $email; // optional field
	
	$next_id = ( $type == 2 ) ? getTrackBacksNextId() : getCommentsNextId();
	$comment_TYPE = ( $type == 2 ) ? 'T' : 'C';

	// to using openid
	if ( $openid ) {
		$comment['openid'] = Acl::getIdentity('openid');
		$comment['user-logged-in'] = 'true';
	}
	
	// to testing
	// $comment['test-force'] = 'spam,x.xxxx'; // | 'ham,x.xxxx' ( 0 ~ 1)
	
	if ($r = defensio_post('audit-comment', $comment)) {
		$ar = Spyc :: YAMLLoad($r);
		if (isset($ar['defensio-result'])) {
			if ($ar['defensio-result']['status'] == DF_SUCCESS ) {
				// Set metadata about the comment
				$defensio_meta['spaminess'] = $ar['defensio-result']['spaminess'];
				$defensio_meta['signature'] = $ar['defensio-result']['signature'];

				error_log(print_r($ar,true));
			
				if ($ar['defensio-result']['spam']) {
					$defensio_meta['spam'] = true;
					defensio_save_meta_data($comment_TYPE, $next_id, $defensio_meta);
					return false;
				} else {
					// not spam
					$defensio_meta['spaminess'] = 0; 

					// if do you want check with Thief-cat algorithm, comment out the following two lines.
					if ( !$defensio_conf['force_with_tca'] ) {
						defensio_save_meta_data($comment_TYPE, $next_id, $defensio_meta);
						return true;
					}
				} 
			}
		} /* else {
			// Succesful http request, but Defensio failed.
		} */
	} /* else {
		// Unsuccesful POST to the server. Defensio might be down.
	} */
	//defensio_save_meta_data($comment_TYPE, $next_id, $defensio_meta); // there is problem in defensio.

	///////////////////////
	// call fail
	// Do Local spam check with "Thief-cat algorithm"
	$count = 0;
	$tableName = $database['prefix'] . 'Trackbacks';
		
	if ($type == 2) // Trackback Case
	{
		$sql = 'SELECT COUNT(id) as cc FROM ' . $database['prefix'] . 'Trackbacks WHERE';
		$sql .= ' url = \'' . POD::escapeString($url) . '\'';
		$sql .= ' AND isFiltered > 0';
		
		if ($row = POD::queryRow($sql)) {
			$count += @$row[0];
		}
		
	} else { // Comment Case
		$tableName = $database['prefix'] . 'Comments';	

		$sql = 'SELECT COUNT(id) as cc FROM ' . $database['prefix'] . 'Comments WHERE';
		$sql .= ' comment = \'' . POD::escapeString($content) . '\'';
		$sql .= ' AND homepage = \'' . POD::escapeString($url) . '\'';
		$sql .= ' AND name = \'' . POD::escapeString($name) . '\'';
		$sql .= ' AND isFiltered > 0';
		
		if ($row = POD::queryRow($sql)) {
			$count += @$row[0];
		}
	}

	// Check IP
	$sql = 'SELECT COUNT(id) as cc FROM ' . $tableName . ' WHERE';
	$sql .= ' ip = \'' . POD::escapeString($_SERVER['REMOTE_ADDR']) . '\'';
	$sql .= ' AND isFiltered > 0';

	if ($row = POD::queryRow($sql)) {
		$count += @$row[0];
	}

	$is_spam = ($count >= 10) ? 1 : 0;

	if (isset($defensio_meta['spaminess']) and isset($defensio_meta['signature']) && $is_spam ) 
		defensio_submit_spam($defensio_meta['signature']);

	$defensio_meta['spam'] = $defensio_meta['spaminess'] = $is_spam;
	defensio_save_meta_data($comment_TYPE, $next_id, $defensio_meta); 
	return !($is_spam);
}

function DEFENSIO_AddingTrackback($target, $mother)
{
	return DEFNENSIO_FILTER(2, $mother['site'], $mother['title'], $mother['url'], $mother['excerpt']);
}

function DEFENSIO_AddingComment($target, $mother)
{
	global $user;
	if ($mother['secret'] ==  true) // it's secret(only owner can see it)
	{
		// Don't touch
		return $target;
	}
	
	$type = 1; // comment
	if ($mother['entry'] == 0) $type = 3; // guestbook

	return $target && DEFNENSIO_FILTER($type, $mother['name'], '', $mother['homepage'], $mother['comment'], $mother['password'] == '-OPENID-' ? true : false);
}

function DEFENSIO_dataValHandler($DATA) {
	global $hostURL;
	requireComponent('Textcube.Function.misc');
	$cfg = misc::fetchConfigVal($DATA);

	// check apikey
	if ( isset($cfg['apikey']) ) {
		$r = defensio_verify_key($cfg['apikey']);

		if ( $r ) {
			// check and create table;
			defensio_create_table();
			return true;
		}
		else 
			return "API-Key is invalid";
	}
	else {
		return "Please, Input your defensio api-key.";
	}
}

// Defensio UI handler
function DEFENSIO_PostAction()
{
	global $blogid, $pluginMenuURL, $pluginURL, $pluginSelfParam,$blog, $user,$blogURL,$defaultURL, $hostURL,$service,$skinSetting, $configVal;

	if (isset($_POST['perPage']) && is_numeric($_POST['perPage'])) {
		$perPage = $_POST['perPage'];
		setBlogSetting('rowsPerPage', $_POST['perPage']);
	}

	$l = $blogURL . "/owner/plugin/adminMenu?name=Defensio/DEFENSIO";	
	$l .= "&t=" . $_REQUEST['t'];
	if ( isset($_REQUEST['page']) && $_REQUEST['page'] != '' && $_REQUEST['page'] != 1 ) $l .= "&page=" . $_REQUEST['page'];
	//print_r($_REQUEST);
	header("Location: $l");
	exit;
}

function DEFENSIO_DeleteItems()
{
	global $blogid, $pluginMenuURL, $pluginURL, $pluginSelfParam,$blog, $user,$blogURL,$defaultURL, $hostURL,$service,$skinSetting, $configVal, $suri;

	$id = !empty($_GET['id']) ? $_GET['id'] : '';
	$comment_TYPE = !empty($_GET['t']) ? $_GET['t'] : '';
	if ( $comment_TYPE != "T" ) $comment_TYPE = "C";

	if ( $id ) {
		defensio_make_to_spam($comment_TYPE, $id);
		$isAjaxRequest = checkAjaxRequest();
		//$isAjaxRequest ? respond::ResultPage(0) : header("Location: ".$_SERVER['HTTP_REFERER']);

		defensio_clear_comments($comment_TYPE, array($id));
		$r = -1;
		if ( $comment_TYPE == 'C' ){
			if (deleteCommentInOwner($blogid, $id) === true) $r = 0;
		}
		else {
			if (deleteTrackback($blogid, $id) !== true) $r = 0;
		}
		$isAjaxRequest ? respond::ResultPage($r) : header("Location: ".$_SERVER['HTTP_REFERER']);
	}
	else {
		$targets = explode('~*_)', $_POST['targets']);

		$id = array();
		foreach($targets as $t) {
			if ( $t == '' ) continue;
			array_push($id, $t);
		}
		if ( count($id) > 0 ) defensio_make_to_spam($comment_TYPE, implode(',',$id));
		foreach($id as $t) {
			( $comment_TYPE == 'C' ) ? deleteCommentInOwner($blogid, $t,  false) : deleteTrackback($blogid, $t);
		}
		defensio_clear_comments($comment_TYPE);
		respond::ResultPage(0);
	}
	exit;
}

function DEFENSIO_RevertItems()
{
	global $blogid, $pluginMenuURL, $pluginURL, $pluginSelfParam,$blog, $user,$blogURL,$defaultURL, $hostURL,$service,$skinSetting, $configVal, $suri;

	$id = !empty($_GET['id']) ? $_GET['id'] : '';
	$comment_TYPE = !empty($_GET['t']) ? $_GET['t'] : '';
	if ( $comment_TYPE != "T" ) $comment_TYPE = "C";

	if ( $id ) {
		$isAjaxRequest = checkAjaxRequest();

		$r = -1;
		if ( $comment_TYPE == 'C' ){
			if (revertCommentInOwner($blogid, $id) === true) $r = 0;
		}
		else {
			if (revertTrackback($blogid, $id) !== true) $r = 0;
		}
		defensio_make_to_ham($comment_TYPE, $id);
		defensio_clear_comments($comment_TYPE);
		$isAjaxRequest ? respond::ResultPage($r) : header("Location: ".$_SERVER['HTTP_REFERER']);
	}
	else {
		$targets = explode('~*_)', $_POST['targets']);

		$id = array();
		foreach($targets as $t) {
			if ( $t == '' ) continue;
			array_push($id, $t);
			( $comment_TYPE == 'C' ) ? revertCommentInOwner($blogid, $t,  false) : revertTrackback($blogid, $t);
		}
		if ( count($id) > 0 ) defensio_make_to_ham($comment_TYPE, implode(',',$id));
		defensio_clear_comments($comment_TYPE);
		respond::ResultPage(0);
	}
	exit;
}

function DEFENSIO_Quarantine()
{
	// remove all
	global $blogid, $pluginMenuURL, $pluginURL, $pluginSelfParam,$blog, $user,$blogURL,$defaultURL, $hostURL,$service,$skinSetting, $configVal, $suri;

	$comment_TYPE = !empty($_GET['t']) ? $_GET['t'] : '';
	if ( $comment_TYPE != "T" ) $comment_TYPE = "C";

	$id = defensio_get_all_comments_id($comment_TYPE);
		
	//if ( count($id) > 0 ) defensio_make_to_spam($comment_TYPE, implode(',',$id));
	
	if ( count($id) > 0 ) defensio_make_to_spam($comment_TYPE);
	foreach($id as $t) {
		( $comment_TYPE == 'C' ) ? deleteCommentInOwner($blogid, $t,  false) : deleteTrackback($blogid, $t);
	}
	defensio_clear_comments($comment_TYPE);

	if (array_key_exists('ajaxcall', $_GET)) respond::ResultPage(0);
	else header("Location: ".$_SERVER['HTTP_REFERER']);
}

function DEFENSIO_Notice()
{
	global $blogURL;

	// clear garbages
	defensio_clear_comments('C');
	defensio_clear_comments('T');

	$s = defensio_get_all_spams_count();
	print "<div style='margin:5px;'><ul>";
	print "<li>You have <strong style='color:#ff9601;'>$s</strong> spam in your <a href='$blogURL/owner/plugin/adminMenu?name=Defensio/DEFENSIO'>Defensio quarantine</a>.</li>";
	print "</ul></div>";
}
function DEFENSIO()
{
	global $blogid, $pluginMenuURL, $pluginURL, $pluginSelfParam,$blog, $user,$blogURL,$defaultURL, $hostURL,$service,$skinSetting, $configVal;
	requireComponent('Textcube.Function.misc');

	$comment_TYPE = !empty($_GET['t']) ? $_GET['t'] : '';
	$page = !empty($_GET['page']) ? $_GET['page'] : 1;
	$perPage = getBlogSetting('rowsPerPage', 10); 

	if ( $comment_TYPE != "T" ) $comment_TYPE = "C";



	$data = misc::fetchConfigVal( $configVal );
	$blogHome = preg_replace("'^http://'", "", $hostURL);
	print '<div><h2 class="caption"><span class="main-text">Defensio Anti Spam Filter</span></h2></div>';

	if ( !isset($data['apikey']) || $data['apikey'] == '' ) {
		print <<<ENDL
		<div>
			<p><a href="http://www.defensio.com">Defensio</a>'s blog spam web service aggressively and intelligently prevents comment and trackback spam from hitting your blog. You should quickly notice a dramatic reduction in the amount of spam you have to worry about.</p><br />
			<p>When the filter does rarely make a mistake (say, the odd spam message gets through or a rare good comment is marked as spam) we've made it a joy to sort through your comments and set things straight. Not only will the filter learn and improve over time, but it will do so in a personalized way!</p><br />
			<p>In order to use our service, you will need a free Defensio API key. Get yours now at <a href="http://www.defensio.com/signup">Defensio.com</a>.</p>
		</div>
ENDL;
		return;
	}

	print "<div>";
	
	// TODO: Cache?
	// get stats
	$stats = defensio_get_stats();
	if ( $stats !== false ) {
		$accuracy = number_format( $stats['accuracy'] * 100 , 2 , '.', '');
		$spam = $stats['spam'];
		$ham = $stats['ham'];
		$fn = $stats['false-negatives'];
		$fp = $stats['false-positives'];
		$apikey = $data['apikey'];
		print <<<ENDL
		<div class="defensio_stats" style="margin:10px;padding:5px;">
		<div style="float:right;"><a href="http://defensio.com/manage/stats/$apikey">Detail Stats</a></div>
		<ul>
			<li><strong>Recent accuracy: $accuracy%</strong><hr></li>
			<li><strong>$spam</strong> spams, <strong>$ham</strong> legitimate comments</li>
			<li><strong>$fn</strong> false negatives (undetected spam)</li>
			<li><strong>$fp</strong> false positives (legitimate comments identified as spam)</li>
		</ul>
		</div>
ENDL;
	}

	// get quarntines list
	list($comments, $paging) = defensio_get_data($comment_TYPE , $page, $perPage);
	include('template/'. ($comment_TYPE == 'C' ? 'comment' : 'trackback') . '.php');

	print "</div>";
}
?>
