<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'visibility' => array('int', 0, 3),
		'starred   ' => array('int', 0, 2),
		'category'   => array('int', 'default' => 0),
		'title'      => array('string'),
		'content'    => array('string'),
		'contentformatter' => array('string'),
		'contenteditor' => array('string'),
		'permalink'  => array('string', 'default' => ''),
		'location'   => array('string', 'default' => '/'),
		'latitude'   => array('number', 'default' => null, 'min' => -90.0, 'max' => 90.0, 'bypass'=>true),
		'longitude'   => array('number', 'default' => null, 'min' => -180.0, 'max' => 180.0, 'bypass'=>true),
		'tag'        => array('string', 'default' => ''),
		'acceptcomment'   => array(array('0', '1'), 'default' => '0'),
		'accepttrackback' => array(array('0', '1'), 'default' => '0'),
		'published'  => array('int', 0, 'default' => 1)
	)
);
require ROOT . '/library/preprocessor.php';
importlib('model.blog.entry');

requireStrictRoute();
$updateDraft = 0;
$entry = getEntry($blogid, $suri['id']);
if(is_null($entry)) {
	$entry = getEntry($blogid, $suri['id'],true);
	$updateDraft = 1;
}
if (!is_null($entry)) {
	$entry['visibility'] = $_POST['visibility'];
	$entry['starred'] = $_POST['starred'];
	$entry['category'] = $_POST['category'];
	$entry['location'] = empty($_POST['location']) ? '/' : $_POST['location'];
	$entry['latitude'] = (empty($_POST['latitude']) || $_POST['latitude'] == "null") ? null : $_POST['latitude'];
	$entry['longitude'] = (empty($_POST['longitude']) || $_POST['longitude'] == "null") ? null : $_POST['longitude'];
	$entry['tag'] = empty($_POST['tag']) ? '' : $_POST['tag'];
	$entry['title'] = $_POST['title'];
	$entry['content'] = $_POST['content'];
	$entry['contentformatter'] = $_POST['contentformatter'];
	$entry['contenteditor'] = $_POST['contenteditor'];
	$entry['slogan'] = $_POST['permalink'];
	$entry['acceptcomment'] = empty($_POST['acceptcomment']) ? 0 : 1;
	$entry['accepttrackback'] = empty($_POST['accepttrackback']) ? 0 : 1;
	$entry['published'] = empty($_POST['published']) ? 0 : $_POST['published'];
	if($id = updateEntry($blogid, $entry, $updateDraft)) {
		fireEvent('UpdatePost', $id, $entry);
		setBlogSetting('LatestEditedEntry_user'.getUserId(),$suri['id']);
		Respond::ResultPage(0);
	}
}
Respond::ResultPage(-1);
?>
