<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
requireStrictRoute();

if (!array_key_exists('viewMode', $_REQUEST)) $_REQUEST['viewMode'] = '';
else $_REQUEST['viewMode'] = '?' . $_REQUEST['viewMode'];

POD::execute("DELETE FROM `{$database['prefix']}BlogSettings` WHERE `blogid` = {$blogid} AND `name` = 'coverpageOrder'");
header('Location: '. $context->getProperty('uri.blog') . '/owner/skin/coverpage' . $_REQUEST['viewMode']);
?>
