<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();

if (!array_key_exists('viewMode', $_REQUEST)) $_REQUEST['viewMode'] = '';
else $_REQUEST['viewMode'] = '?' . $_REQUEST['viewMode'];

POD::execute("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `blogid` = {$blogid} AND `name` = 'sidebarOrder'");
header('Location: '. $blogURL . '/owner/skin/sidebar' . $_REQUEST['viewMode']);
?>