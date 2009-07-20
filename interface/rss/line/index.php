<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('NO_SESSION', true);
define('__TEXTCUBE_LOGIN__',true);

require ROOT . '/library/preprocessor.php';
requireModel("blog.feed");

requireStrictBlogURL();

$children = array();
$cache = new pageCache;

$cache->name = 'linesRSS';
if(!$cache->load()) {
	$result = getLinesFeed(getBlogId(),'public','rss');
	if($result !== false) {
		$cache->contents = $result;
		$cache->update();
	}
}
header('Content-Type: application/rss+xml; charset=utf-8');
fireEvent('FeedOBStart');
echo fireEvent('ViewLinesRSS', $cache->contents);
fireEvent('FeedOBEnd');
?>