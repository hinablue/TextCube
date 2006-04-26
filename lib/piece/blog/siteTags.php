<?
$tagView = $skin->siteTag;
list($maxTagFreq, $minTagFreq) = getTagFrequencyRange();
$itemsView = '';
foreach ($siteTags as $siteTag) {
	$itemView = $skin->siteTagItem;
	dress('tag_name', htmlspecialchars($siteTag), $itemView);
	dress('tag_link', "$blogURL/tag/" . urlencoder(htmlspecialchars(escapeURL($siteTag))), $itemView);
	dress('tag_class', "cloud" . getTagFrequency($siteTag, $maxTagFreq, $minTagFreq), $itemView);
	$itemsView .= $itemView;
}
dress('tag_rep', $itemsView, $tagView);
dress('tag', $tagView, $view);
?>
