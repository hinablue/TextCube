<?php

function showFlickrPhotos($target){
	global $blogURL, $pluginURL, $configVal;
	requireComponent('Textcube.Function.misc');
	require_once "lib/phpFlickr.php";
	
	$data = misc::fetchConfigVal($configVal);
	$flickruserid = isset($data['flickruserid']) ? $data['flickruserid'] : "";
	
	$flickr_api_key = "d1038f051000214af2bf694014ca8f98"; //It's key for plugin. It does not change.
	$f = new phpFlickr($flickr_api_key);	
	
	ob_start();
?>
	<script type="text/javascript">
	//<![CDDA[
		function actionFlickr(flag){
			var person = document.getElementById("person").value;
			var photoSets = (document.getElementById("photoSets")==undefined) ? "" : document.getElementById("photoSets").value;
			var length = 16;
			var searchKey = document.getElementById("searchKey").value;
			var srcTarget = document.getElementById("flickrview");

			switch(flag) {
				case "search":
					var flickrQueryString = "?person=" + person + "&photoSets=" + photoSets + "&length=" + length + "&search=" + encodeURIComponent(searchKey) + "&page=1";
					srcTarget.src = "<?php echo $blogURL;?>/plugin/flickrPhotos" + flickrQueryString;
				break;
				case "allview":
					var flickrQueryString = "?person=" + person + "&length=" + length + "&page=1";
					srcTarget.src = "<?php echo $blogURL;?>/plugin/flickrPhotos" + flickrQueryString;
				break;
				case "refresh":
				default:
					parent.frames['flickrview'].location.reload();
				break;
			}
		}
	//]]>
	</script>
	<div style="margin-top:10px;clear:both;"><strong>&bull; Flickr Photos</strong></div>
	<?php
	if(!empty($flickruserid)) {
		?>
		<div id="photosToyHeader" style="margin-top:15px;background-color:#eee;">
		<?php
		$userInfo = $f->people_getInfo($flickruserid);
		echo '&nbsp;Hello!&nbsp;<strong style="color: #36f;">',$userInfo['username'],'</strong>';
		$photosUrl = $userInfo['photosurl'];
		?>
		</div>
		<?php
	}
	?>	
	<div style="margin-top:5px;background-color:#fff;"><iframe name="flickrview" id="flickrview" src="<?php echo $blogURL;?>/plugin/flickrPhotos" scrolling="no" style="width:100%;height:200px;margin:0;padding:0;border:0;"></iframe></div>
	<div id="photosToolbar" style="background-color:#eee;white-space:nowrap;padding:4px;overflow:hidden;">
	<?php
	if(!empty($flickruserid)) {
		$mySets = $f->photosets_getList($flickruserid);
		echo 'Flickr Photosets&nbsp;<select id="photoSets" name="photoSets" size="1">';
		foreach((array)$mySets['photoset'] as $row) {
			echo '<option label="',$row['title'],'" value="',$row['id'],'">',$row['title'],'(',$row['photos'],')</option>';
		}
		echo '</select>&nbsp;&nbsp;';
	}
	?>
	Search&nbsp;<select name="person" id="person" value="1"><option label="My" value="my">My</option><option label="All" value="all">All</option></select>&nbsp;<input type="text" name="searchKey" id="searchKey" value="" maxlength="255" /><br /><br />
	<img src="<?php echo $pluginURL;?>/images/refresh.gif" width="82" height="16" border="0" alt="" style="cursor:pointer;" onclick="actionFlickr('refresh');return false;" />&nbsp;<img src="<?php echo $pluginURL;?>/images/search.gif" width="82" height="16" border="0" alt="" style="cursor:pointer;" onclick="actionFlickr('search');return false;" />&nbsp;<img src="<?php echo $pluginURL;?>/images/allview.gif" width="82" height="16" border="0" alt="" style="cursor:pointer;" onclick="actionFlickr('allview');return false;" />
	</div>
	<?php
	
	$script = ob_get_contents();
	ob_end_clean();
	return $target.$script;
}
function getFlickrPhotosInfo($photo = array(), $zoomstyle = 'flickr', $flickruserid = "") {
	$imgSrc = "http://farm{$photo['farm']}.static.flickr.com/{$photo['server']}/{$photo['id']}_{$photo['secret']}_s.jpg";
	$imgMedium = "http://farm{$photo['farm']}.static.flickr.com/{$photo['server']}/{$photo['id']}_{$photo['secret']}.jpg";
	$imgLightBoxLarge = $imgMedium;
    $owner = (isset($photo['owner']) && !empty($photo['owner'])) ? $photo['owner'] : $flickruserid;
	$imgLink = "http://www.flickr.com/photos/{$owner}/{$photo['id']}/";
	$_output = "<a href='javascript:%20void();' title='{$photo['title']}' onclick=\"insertPhoto('{$imgLink}', '" . $imgMedium . "', '" . $imgLightBoxLarge . "', '{$photo['title']}', '{$zoomstyle}'); return false;\" >";
	$_output .= "<img src='" . $imgSrc . "' width='75' height='75' border='0' alt='{$photo['title']}'>";
	$_output .= "</a>";

	return $_output;
}
function getFlickrPhotos($target) {
	global $blogURL, $pluginURL, $configVal;
	requireModel('reader.common');
	requireComponent('Textcube.Model.Paging');
	requireComponent('Textcube.Function.misc');

	$data = misc::fetchConfigVal($configVal);
	$flickruserid = isset($data['flickruserid']) ? $data['flickruserid'] : "";
	
	require_once "lib/phpFlickr.php";
	$flickr_api_key = "d1038f051000214af2bf694014ca8f98"; //It's key for plugin. It does not change.
	$f = new phpFlickr($flickr_api_key);
	
	$listLength = isset($_GET['length']) ? $_GET['length'] : 16;
	$searchKey	= (isset($_GET['search']) && !empty($_GET['search'])) ? urldecode($_GET['search']) : "";
	$photoSets	= (isset($_GET['photoSets']) && !empty($_GET['photoSets'])) ? $_GET['photoSets'] : "";
	$personMode = isset($_GET['person']) ? $_GET['person'] : "my";
	$page = isset($_GET['page']) ? $_GET['page'] : 1;
	$zoomStyle = "flickr";
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link type="text/css" rel="stylesheet" href="<?php echo $pluginURL;?>/style/default.css" />
	</head>
	<script type="text/javascript">
	//<![CDDA[
		function insertPhoto(_link, _src_m, _src_l, _title, _style){
			var linkTag_A = '', linkTag_B = '', imgTag = '';
			linkTag_A = '<a href="' + _link + '" title="' + _title + '" target="_blank">'; 
			linkTag_B = '</a>';
			imgTag	= '<img src="' + _src_m + '" border="0" />';

			var isWYSIWYG = false;
			try{
				if(parent.editor.editMode == 'WYSIWYG')
					isWYSIWYG = true;
			}
			catch(e){ }
			if(isWYSIWYG) {
				parent.editor.command('Raw', (linkTag_A + imgTag), linkTag_B);
			}else{
				parent.insertTag(parent.editor.textarea, (linkTag_A + imgTag), linkTag_B);
			}
		}
	//]]>
	</script>
	<body style="margin:0;padding:0;border:none;background-color:#ffffff;">
	<div id="photosBody" style="height:200px;width:100%;overflow-x:hidden;overflow-y:auto;background-color:#fff;">
	<?php	
	if ($personMode == "my" && !empty($flickruserid) && empty($searchKey)) {
		if(!empty($photoSets)) {
			$photoset = $f->photosets_getPhotos($photoSets, "owner_name", NULL, $listLength, $page);
			$photos['photos'] = $photoset['photoset'];
		} else {
			$photos = $f->people_getPublicPhotos($flickruserid, NULL, NULL, $listLength, $page);
		}
		foreach ($photos['photos']['photo'] as $photo) {
			$_output = getFlickrPhotosInfo($photo, $zoomStyle, $flickruserid);
			echo $_output;
		}
	} else if (($personMode == "my" || $personMode == "all") && !empty($searchKey)){
		if($personMode == "my" && !empty($flickruserid)){
			$searchArray = array("user_id"=>$flickruserid, "text"=>$searchKey, "sort"=>"date-posted-desc", "per_page"=>$listLength, "page"=>$page);
		} else {
			$searchArray = array("text"=>$searchKey, "sort"=>"date-posted-desc", "per_page"=>$listLength, "page"=>$page);
		}
		$photos['photos'] = $f->photos_search($searchArray);
		if ($photos['photos']['total'] > 0) {
			foreach ($photos['photos']['photo'] as $photo) {
				$_output = getFlickrPhotosInfo($photo, $zoomStyle);
				echo $_output;
			}	
		} else {
			echo '<p style="margin-top:30px; text-align:center; font-size:9pt;">Flickr did not find any photos. Please input title, description or tags.</p>';		
		}
	} else if ($personMode == "all" && empty($searchKey)){
		$photos['photos'] = $f->photos_getRecent(NULL, $listLength, $page);

		foreach ($photos['photos']['photo'] as $photo) {
			$_output = getFlickrPhotosInfo($photo, $zoomStyle);
			echo $_output;
		}		
	} else {
		echo '<p style="margin-top:30px; text-align:center; font-size:9pt;">Flickr did not find any photos.' , (!$nickName ? '(Input title, description or tags.)' : '') , '</p>';
	}
	
	$pageLink = '?person='.$personMode.'&photoSets='.$photoSets.'&length='.$listLength.'&search='.rawurlencode($searchKey);
	//$photos['total'] = $photos['total'] > 996 ? 996 : $photos['total'];
	$paging = HC_getFetchWithPaging($photos['photos']['total'], $page, $listLength, "$blogURL/plugin/flickrPhotos", $pageLink . "&page=");
	$pagingTemplate = '[##_paging_rep_##]';
	$pagingItemTemplate = '<a [##_paging_rep_link_##] class="num">[##_paging_rep_link_num_##]</a>';
	
	$prev_page = isset($paging['prev']) ? " href='{$pageLink}page={$paging['prev']}' " : '';
	$next_page = isset($paging['next']) ? " href='{$pageLink}page={$paging['next']}' " : '';
	$no_more_prev = isset($paging['prev']) ? '' : 'no-more-prev';
	$no_more_next = isset($paging['next']) ? '' : 'no-more-next';

	$target = '<div class="paging-list"><a '.$prev_page.' class="prev '.$no_more_prev.'">prev </a>';
	$target .= '<span class="numbox">'.str_repeat("\t", 12).Paging::getPagingView($paging, $pagingTemplate, $pagingItemTemplate).'</span>';
	$target .= '<a '.$next_page.' class="next '.$no_more_next.'"> next</a>';
	$target .= '<div class="totalResults">';
	$target .= 'Total '.$photos['photos']['total'].' photos.';
	$target .= '</div></div>';
	
	echo $target;
	?>
	</div>
	</body>
	</html>
	<?php		
}

function HC_getFetchWithPaging($totalCount, $page, $count, $url = null, $prefix = '?page=', $countItem = null) {
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

function getFlickrPhotosDataSet($DATA) {
	requireComponent('Textcube.Function.misc');
	$cfg = misc::fetchConfigVal($DATA);
	if(!$cfg['flickruserid'] || empty($cfg['flickruserid'])) return "::Input error::\n\n Flickr's NSID must input certainly.";
	return true;
}
?>
