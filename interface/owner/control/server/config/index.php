<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$IV = array(
	'POST' => array(
		'allowBlogVisibility' => array('int'),
		'disableEolinSuggestion' => array('int',0,1),
		'encoding'               => array('string'),
		'faviconDailyTraffic'    => array('int'),
		'flashClipboardPoter'    => array('int',0,1),
		'flashUploader'          => array('int',0,1),
		'language'               => array('string'),
		'serviceurl'             => array('string'),
		'skin'                   => array('string'),
		'timeout'                => array('int'),
		'timezone'               => array('string'),
		'useDebugMode'           => array('int',0,1),
		'useEncodedURL'          => array('int',0,1),
		'useNumericRSS'          => array('int',0,1),
		'usePageCache'           => array('int',0,1),
		'useReader'              => array('int',0,1),
		'useRewriteDebugMode'    => array('int',0,1),
		'useSessionDebugMode'    => array('int',0,1),
		'useSkinCache'           => array('int',0,1)
		)
);

require ROOT . '/lib/includeForBlogOwner.php';

requireModel('blog.service');
requireStrictRoute();
$matchTable = array(
	'timeout'=> 'timeout',
	'skin'=>'skin',
	'language'=>'language',
	'timezone'=>'timezone',
	'encoding'=>'encoding',
	'serviceurl' => 'serviceURL',
	'usePageCache'=>'pagecache',
	'useSkinCache'=>'skincache',
	'useReader'=>'reader',
	'useNumericRSS'=>'useNumericRSS',
	'useEncodedURL'=>'useEncodedURL',
	'disableEolinSuggestion'=>'disableEolinSuggestion',
	'allowBlogVisibility'=> 'allowBlogVisibilitySetting',
	'flashClipboardPoter' => 'flashClipboardPoter',
	'flashUploader' => 'flashUploader',
	'useDebugMode'=>'debugmode',
	'useSessionDebugMode' => 'debug_session_dump',
	'useRewriteDebugMode' => 'debug_rewrite_module',
	'faviconDailyTraffic' =>'favicon_daily_traffic'
	);

$config = array();
foreach($matchTable as $abs => $real) {
	if($_POST[$abs] === 1) $config[$real] = true;
	else if($_POST[$abs] === 0) $config[$real] = false;
	else $config[$real] = $_POST[$abs];
}

$result = writeConfigFile($config);
if ($result === true) {
	respond::PrintResult(array('error' => 0));
} else {
	respond::PrintResult(array('error' => 1, 'msg' => $result));
}
?>
