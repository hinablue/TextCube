<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/preprocessor.php';
requireView('iphoneView');
list($entryId) = getCommentAttributes($blogid, $suri['id'], 'entry');
?>

<div id="comment_<?php echo $entryId."_".$suri['id'];?>" title="Delete <?php echo $suri['id'];?>" class="panel">
<?php
if (doesHaveOwnership()) {
?>
	<h2 class="title"><?php echo _t('Delete comment?');?></h2>
	<div class="content">
		<a class="whiteButton" href="<?php echo $blogURL;?>/comment/delete/action/<?php echo $suri['id'];?>"><?php echo _t('Yes');?></a>
		<a class="whiteButton margin-top10" href="<?php echo $blogURL;?>/comment/<?php echo $entryId;?>"><?php echo _t('No');?></a>
	</div>
	<?php
} else {
?>
	<h2 class="title"><?php echo _t('Please enter Password.');?></h2>
	<div class="content">
		<form method="get" action="<?php echo $blogURL;?>/comment/delete/action" class="dialog">
			<input type="hidden" name="replyId" value="<?php echo $suri['id'];?>" />
			<fieldset>
				<label for="password"><?php echo _t('Password:');?></label>
				<input type="password" name="password" id="password" />
				<a href="#" class="whiteButton margin-top10" type="submit"><?php echo _t('Delete Comment');?></a>
				<a href="<?php echo $blogURL;?>/comment/<?php echo $entryId;?>" class="whiteButton"><?php echo _t('Go to comments page');?></a>
			</fieldset>
		</form>
	</div>
<?php
}
?>
</div>
