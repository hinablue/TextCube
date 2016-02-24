<?php
function InsertWebImageToEditor($target) {
	global $service, $blog;
	# Locale language file support, defualt is en-us.php
	if (!isset($blog['blogLanguage'])) {
		$blog['blogLanguage'] = $service['language'];
	}
	// Locale language file support.
	$gtext = array();
	switch($blog['blogLanguage']) {
		case "zh-TW":
			$gtext[0] = "插入網路圖片";
			$gtext[1] = "預覽";
			$gtext[2] = "插入";
			$gtext[3] = "請檢查圖片位址是否有誤。";
			$gtext[4] = "圖片位址";
		break;
		case "zh-CN":
			$gtext[0] = "插入网络图片";
			$gtext[1] = "预览";
			$gtext[2] = "插入";
			$gtext[3] = "请检查图片位址是否有误。";
			$gtext[4] = "图片位址";
		break;
		case "en":
		default:
			$gtext[0] = "Insert Web Image";
			$gtext[1] = "Preview";
			$gtext[2] = "Insert";
			$gtext[3] = "Please check your image url.";
			$gtext[4] = "Image URL";
	}
	
	ob_start();
?>
	<script type="text/javascript">
	//<![CDDA[
	function insertWebImageIntoEditor() {
		try {
			var isWYSIWYG = false;
			var webimageurl = document.getElementById('webimageurl').value;
			if (webimageurl=='' || webimageurl=='http://') {
				alert('<?php echo _t('Please check your image url.');?>');
			} else {
				var imgTag	= '<img src="' + webimageurl + '" border="0" />';
			}
			if(editor.editMode == 'WYSIWYG') isWYSIWYG = true;
			if(isWYSIWYG) {
				editor.command('Raw', imgTag);
			}else{
				insertTag(editor.textarea, imgTag);
			}
		} catch(e) {}
	}
	function previewWebImage() {
		try {
			var webimageurl = document.getElementById('webimageurl').value;
			var previewWebImageBox = document.getElementById('previewWebImageBox');
			if (webimageurl!='' && webimageurl!='http://') {
				var imgTag	= '<img src="' + webimageurl + '" width="120" border="0" />';
				previewWebImageBox.innerHTML = imgTag;
			} else {
				previewWebImageBox.innerHTML = '';
			}
		} catch(e) {}
	}
	//]]>
	</script>
	<div style="margin-top:10px;clear:both;"><strong>&bull;&nbsp;<?php echo _t('Insert Web Image');?></strong></div>
	<div style="margin:5px 0px 10px 0px;background-color:#eee;">
		<div id="previewWebImageBox"></div>
		<?php echo _t('Image URL');?>&nbsp;<input type="text" name="webimageurl" id="webimageurl" value="http://" size="50" maxlength="255" onclick="this.select();" />&nbsp;<input type="button" value="<?php echo _t('Preview');?>" onclick="previewWebImage(); return false;" />&nbsp;<input type="button" value="<?php echo _t('Insert');?>" onclick="insertWebImageIntoEditor(); return false;" />
	</div>
<?php	
	$target .= ob_get_contents();
	ob_end_clean();
	return $target;
}
?>
