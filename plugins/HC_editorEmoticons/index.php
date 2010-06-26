<?php
/* 強制 UTF-8 */
/* 不使用 preg_replace
function showEmoticons_ContentReplacer($matches)
{
	global $suri;
	require_once ('emoticons.inc.php');
	$emoIcons = (isset($__HCEMO)) ? $__HCEMO : array();
	
	if (isset($matches[1]) && $suri['directive']!="/rss" && $suri['directive']!="/m" && $suri['directive']!="/i/entry") {
		return "<img src=\"\" border=\"0\" alt=\"{$matches[1]}\" />";
	}
	return "";
}

function showEmoticons_DressContent($mother, $id)
{
	return preg_replace_callback('@\\[##_HCEMO_([^\\]]*)_##\\]@Usi', 'showEmoticons_ContentReplacer', $mother);
}
*/

function showEmoticons_Toolbox($target){
	global $blogURL, $pluginURL;
	ob_start();
?>
	<div style="margin-top:10px;clear:both;"><strong>&bull; Editor Emoticons</strong></div>
	<div style="margin-top:5px;background-color:#fff;"><iframe name="Emoticons" id="Emoticons" src="<?php echo $blogURL;?>/plugin/getEmoticons" scrolling="no" style="width:100%;height:200px;margin:0;padding:0;border:0;"></iframe></div>
	<?php
	$script = ob_get_contents();
	ob_end_clean();
	return $target.$script;
}

function showEmoticons_getEmoticons($target) {
	global $blogURL, $pluginURL, $__HCEMO;
	requireModel('reader.common');
	requireComponent('Textcube.Model.Paging');
	requireComponent('Textcube.Function.misc');
	require_once ('emoticons.inc.php');
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link type="text/css" rel="stylesheet" href="<?php echo $pluginURL;?>/styles/default.css" />
	</head>
	<script type="text/javascript">
	//<![CDDA[
		function insertHCEMO(_emoticons, _imagessrc){
			var emoTag = '<img src="'+_imagessrc+'" border="0" alt="'+_emoticons+'" longdesc="[##_HCEMO_'+_emoticons+'_##]" />';

			var isWYSIWYG = false;
			try{
				if(parent.editor.editMode == 'WYSIWYG')
					isWYSIWYG = true;
			}
			catch(e){ }
			if(isWYSIWYG) {
				parent.editor.command('Raw', emoTag);
			}else{
				parent.insertTag(parent.editor.textarea, emoTag);
			}
		}
	//]]>
	</script>
	<body style="margin:0;padding:0;border:none;background-color:#ffffff;">
	<div id="emoticonsBody" style="height:200px;width:100%;overflow-x:hidden;overflow-y:auto;background-color:#fff;">
	<?php
	$page = isset($_GET['page']) ? $_GET['page'] : 1;
	$items = (isset($__HCEMO)) ? count($__HCEMO) : 0;
	$listLength = 33;
	
	$paging = HCEMO_getFetchWithPaging($items, $page, $listLength, "$blogURL/plugin/getEmoticons");
	$pagingTemplate = '[##_paging_rep_##]';
	$pagingItemTemplate = '<a [##_paging_rep_link_##] class="num">[##_paging_rep_link_num_##]</a>';

	if ($items>0) {
		$counter['start'] = ($page-1)*$listLength;
		$counter['end'] = $page*$listLength-1;
		$counter['step'] = 0;
		foreach($__HCEMO as $key => $images) {
			if ($counter['step']>=$counter['start'] && $counter['step']<=$counter['end']) {
				echo "<img class=\"emoticons\" src=\"{$pluginURL}/emoticons/{$images}\" border=\"0\" alt=\"{$key}\" onclick=\"insertHCEMO('{$key}','{$pluginURL}/emoticons/{$images}'); return false\" />";
			}
			$counter['step']++;
		}
	} else {
		echo '<p style="margin-top:30px; text-align:center; font-size:9pt;">No emoticons installed.</p>';
	}

	
	$prev_page = isset($paging['prev']) ? " href='?page={$paging['prev']}' " : '';
	$next_page = isset($paging['next']) ? " href='?page={$paging['next']}' " : '';
	$no_more_prev = isset($paging['prev']) ? '' : 'no-more-prev';
	$no_more_next = isset($paging['next']) ? '' : 'no-more-next';

	$target = '<div class="paging-list"><a '.$prev_page.' class="prev '.$no_more_prev.'">prev </a>';
	$target .= '<span class="numbox">'.str_repeat("\t", 12).Paging::getPagingView($paging, $pagingTemplate, $pagingItemTemplate).'</span>';
	$target .= '<a '.$next_page.' class="next '.$no_more_next.'"> next</a>';
	$target .= '<div class="totalResults">';
	$target .= 'Total '.$items.' icons.';
	$target .= '</div></div>';
	
	echo $target;
	?>
	</div>
	</body>
	</html>
	<?php		
}

function HCEMO_getFetchWithPaging($totalCount, $page, $count, $url = null, $prefix = '?page=', $countItem = null) {
	global $folderURL, $service;
	if ($url === null)
		$url = $folderURL;
	$paging = array('url' => $url, 'prefix' => $prefix, 'postfix' => '');
	$paging['total'] = $totalCount;
	if (empty($count)) $count = 1;
	$paging['pages'] = intval(ceil($paging['total'] / $count));
	$paging['page'] = is_numeric($page) ? $page : 1;
	if ($paging['page'] > $paging['pages']) {
		$paging['page'] = $paging['pages'];
		if ($paging['pages'] > 0)
			$paging['prev'] = $paging['pages'] - 1;
		//return array(array(), $paging);
	}
	if ($paging['page'] > 1)
		$paging['prev'] = $paging['page'] - 1;
	if ($paging['page'] < $paging['pages'])
		$paging['next'] = $paging['page'] + 1;
	$offset = ($paging['page'] - 1) * $count;
	if ($offset < 0) $offset = 0;
	if ($countItem !== null) $count = $countItem;
	return $paging;
}
?>