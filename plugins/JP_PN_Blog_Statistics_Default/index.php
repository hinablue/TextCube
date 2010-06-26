<?php
include_once ('lib/statistics_inc.php');
include_once ('lib/open_flash_chart_object.php');

function CT_BlogVisitStatistics($target) {
	global $database, $blogid, $defaultURL, $blogURL, $pluginURL, $pluginMenuURL, $configVal;
	requireComponent('Textcube.Model.Statistics');

	$weekly = Statistics::getWeeklyStatistics();
	asort($weekly);

	$i = 0;
	$getData = array();
	$getTags = array();
	foreach($weekly as $day) {
		if($i > 0) {
			array_push($getData, $day['visits']);
			$labelFormat =  sprintf("%s.%s", substr($day['date'], 4, 2), substr($day['date'], 6, 8));
			array_push($getTags, $labelFormat);
		}
		$i++;
	}

	$getData  = implode("|", $getData);
	$getTags  = implode("|", $getTags);
	$getTotal = getStatisticsTotalDB(date("Ym"), "visit", 0);
	unset($weekly);
	ob_start();
	?>
		<div class="flash-Line">
			<div id="getBlogVisitLine">
				<?php echo open_flash_chart_object(299, 130, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=line&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
			</div>
		</div>
	<?php
	$target = ob_get_contents();
	ob_end_clean();
	return $target;
}

function PN_Blog_Statistics_Default() {
	global $database, $blogid, $defaultURL, $blogURL, $pluginURL, $pluginMenuURL, $configVal;
	requireComponent('Textcube.Model.Statistics');
	requireComponent('Textcube.Function.misc');
	
	
	$data = misc::fetchConfigVal($configVal);
	if(is_null($data)){
		$data['privateChk'] = 2;
	}
	$getVisibility =($data['privateChk'] == 2)?" AND visibility > 0 ":"";

	$stats = Statistics::getStatistics($blogid);
	$getDate  = isset($_GET['date']) ? $_GET['date'] : date('Y', strtotime("now"));
	$getMenu  = isset($_GET['menu']) ? $_GET['menu'] : "entry";
	$getYear  = substr($getDate,0,4);
	$getMonth = substr($getDate,4);

	$yearRow  = POD::queryAll("SELECT EXTRACT(YEAR FROM FROM_UNIXTIME(published)) period, COUNT(*) count FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft = 0 {$getVisibility} AND category >= 0 GROUP BY period ORDER BY period DESC"); 
	$yearCell = POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft = 0 {$getVisibility} AND category >= 0 AND EXTRACT(YEAR FROM FROM_UNIXTIME(published)) = '".date('Y', strtotime("now"))."'"); 
	$yearAll  = POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft = 0 {$getVisibility} AND category >= 0"); 
	$yearSelect  = "<select id=\"yearSelect\" onchange=\"execDate(this);\">\n";
	$yearSelect .= "<option value=\"\">"._t('&nbsp;- 년 도 -')."</option>\n";	
	$selected = ($getYear == 9999)?" selected":"";
	$yearSelect .= "<option value=\"9999\" style=\"font-weight:bold;\" {$selected}>"._t('#전체')."({$yearAll})</option>\n";	
	if(!$yearCell){
		$selected = ($getYear == date('Y', strtotime("now")))?" selected":"";
		$yearSelect .= "<option value=\"".date('Y', strtotime("now"))."\" {$selected}>".date('Y', strtotime("now"))._t('년')."(0)</option>\n";		
	}
	foreach($yearRow as $items){
		$selected = ($getYear == $items['period'])?" selected":"";
		$yearSelect .= "<option value=\"{$items['period']}\" {$selected}>{$items['period']}"._t('년')."({$items['count']})</option>\n";
	}
	$yearSelect .= "</select>\n";
	$monthSelect  = "<select id=\"monthSelect\" onchange=\"execDate(this);\">\n";
	$monthSelect .= "<option value=\"\">- "._t('월')." -</option>\n";
	$monthSelect .= "<option value=\"{$getYear}\" ".(strlen($getDate)==4 ? " selected":"").">"._t('#전체')."</option>\n";
	for ($i=1; $i<=12; $i++) {
		$gMonth = (strlen($i) == 1)?"0".$i:$i;
		$gValue = $getYear.$gMonth;
		$selected = ($getMonth == $gMonth)?" selected":"";
		$monthSelect .= "<option value=\"{$gValue}\" {$selected}>".$gMonth._t('월')."</option>\n";
	}
	$monthSelect .= "</select>\n";

	$noticeRow = POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft = 0 {$getVisibility} AND category = -2"); //공지
	$keywordRow = POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Entries WHERE blogid = $blogid AND draft = 0 {$getVisibility} AND category = -1"); //키워드
?>
<script type="text/javascript">
//<![CDATA[
	function execLoadFunction() {
		tempDiv = document.createElement("DIV");
		tempDiv.style.clear = "both";
		document.getElementById("part-statistics-blog").appendChild(tempDiv);
	}

	function execDate(selectObject){
		if(selectObject.options[selectObject.selectedIndex].value){
			location.href="<?php echo $pluginMenuURL;?>&date="+selectObject.options[selectObject.selectedIndex].value+"&menu=<?php echo $getMenu;?>";
		}
	}

	window.addEventListener("load", execLoadFunction, false);
//]]>
</script>
					 		
<div id="part-statistics-blog" class="part">
	<h2 class="caption"><span class="main-text"><?php echo _t('블로그 통계정보를 보여줍니다');?></span></h2>
	<div id="statistics-main">
		<div id="statistics-counter-inbox">
			<div class="title"><h3><?php echo _t('종 합 정 보');?></h3></div>
			<table width="100%">
				<tbody>
					<tr class="tr">
						<td colspan="2"><?php echo _t('년/월별 선택');?><br />
						<?php echo $yearSelect;?> <?php echo $monthSelect;?></td>
					</tr>
					<tr height="5"><td colspan="2"></td></tr>
					<tr height="1" bgcolor="#dddddd"><td colspan="2"></td></tr>
					<tr class="tr">
						<td><?php echo _t('오늘 방문자');?></td>
						<th><?php echo number_format($stats['today']);?></th>
					</tr>
					<tr class="tr">
						<td><?php echo _t('어제 방문자');?></td>
						<th><?php echo number_format($stats['yesterday']);?></th>
					</tr>
					<tr class="tr">
						<td><?php echo _t('총 방문자');?></td>
						<th><?php echo number_format($stats['total']);?></th>
					</tr>
					<tr height="1" bgcolor="#dddddd"><td colspan="2"></td></tr>
					<tr class="tr">
						<td><?php echo _t('글 개수');?></td>
						<th><?php echo number_format(getEntriesTotalCountDB($blogid));?></th>
					</tr>
					<tr class="tr">
						<td><?php echo _t('공지 개수');?></td>
						<th><?php echo number_format($noticeRow);?></th>
					</tr>
					<tr class="tr">
						<td><?php echo _t('키워드 개수');?></td>
						<th><?php echo number_format($keywordRow);?></th>
					</tr>
					<tr class="tr">
						<td><?php echo _t('댓글 개수');?></td>
						<th><?php echo number_format(getCommentCountDB($blogid));?></th>
					</tr>
					<tr class="tr">
						<td><?php echo _t('방명록 개수');?></td>
						<th><?php echo number_format(getGuestbookCountDB($blogid));?></th>
					</tr>
					<tr class="tr">
						<td><?php echo _t('트랙백 개수');?></td>
						<th><?php echo number_format(getTrackbackCountDB($blogid));?></th>
					</tr>
					<tr height="10"><td colspan="2"></td></tr>
				</tbody>
			</table>
			<div class="title"><h3><?php echo _t('세 부 메 뉴');?></h3></div>
			<table width="100%">
				<tbody>
					<tr class="tr">
						<td>&nbsp;&raquo; <a href="<?php echo $pluginMenuURL;?>&amp;date=<?php echo $getDate;?>&amp;menu=entry"><?php echo _get_t(_t('글(포스트) 통계'), 'entry');?></a></td>
					</tr>
					<tr class="tr">
						<td>&nbsp;&raquo; <a href="<?php echo $pluginMenuURL;?>&amp;date=<?php echo $getDate;?>&amp;menu=comment"><?php echo _get_t(_t('댓글 통계'), 'comment');?></a></td>
					</tr>
					<tr class="tr">
						<td>&nbsp;&nbsp;&nbsp;&nbsp; - <a href="<?php echo $pluginMenuURL;?>&amp;date=<?php echo $getDate;?>&amp;menu=commenter"><?php echo _get_t(_t('작성자 목록'), 'commenter');?></a></td>
					</tr>
					<tr class="tr">
						<td>&nbsp;&raquo; <a href="<?php echo $pluginMenuURL;?>&amp;date=<?php echo $getDate;?>&amp;menu=trackback"><?php echo _get_t(_t('트랙백 통계'), 'trackback');?></a></td>
					</tr>
					<tr class="tr">
						<td>&nbsp;&raquo; <a href="<?php echo $pluginMenuURL;?>&amp;date=<?php echo $getDate;?>&amp;menu=guestbook"><?php echo _get_t(_t('방명록 통계'), 'guestbook');?></a></td>
					</tr>
					<tr class="tr">
						<td>&nbsp;&nbsp;&nbsp;&nbsp; - <a href="<?php echo $pluginMenuURL;?>&amp;date=<?php echo $getDate;?>&amp;menu=guestbookcommenter"><?php echo _get_t(_t('작성자 목록'), 'guestbookcommenter');?></a></td>
					</tr>
					<tr class="tr">
						<td>&nbsp;&raquo; <a href="<?php echo $pluginMenuURL;?>&amp;date=<?php echo $getDate;?>&amp;menu=tag"><?php echo _get_t(_t('태그 통계'), 'tag');?></a></td>
					</tr>
					<tr class="tr">
						<td>&nbsp;&raquo; <a href="<?php echo $pluginMenuURL;?>&amp;date=<?php echo $getDate;?>&amp;menu=visit"><?php echo _get_t(_t('방문자 통계'), 'visit');?></a></td>
					</tr>
					<tr class="tr">
						<td>&nbsp;&raquo; <a href="<?php echo $pluginMenuURL;?>&amp;date=<?php echo $getDate;?>&amp;menu=referer"><?php echo _get_t(_t('리퍼러 통계'), 'referer');?></a></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="statistics-inbox">
<?php
//##############################
// 글(포스트) 통계 #########
//##############################
if($getMenu == "entry"){
	if($getYear == 9999 && !$getMonth)
	{
		$getData = array();
		$getTags = array();
		$getDateTitle = _t('전체 년도별');

		$tempData = getStatisticsDB('years', '', $getMenu);
		foreach ($tempData as $item){
			array_push($getData, $item['count']);
			array_push($getTags, rawurlencode($item['period']._t('년')));
		}
		$getData  = implode("|", $getData);
		$getTags  = implode("|", $getTags);
		$getTotal = getStatisticsTotalDB($getDate, $getMenu, 1);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('글(포스트) 통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>YearStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>YearPizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=archiveYear&grpYear=".$getYear."&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?
	}
	$getData = array();
	$getTags = array();
	if($getYear == 9999){
		$getDateTitle = (!$getMonth)? _t('전체'):_t('전체').'&nbsp'.$getMonth._t('월');
	}else{
		$getDateTitle = (!$getMonth)?$getYear._t('년도'):$getYear._t('년도').$getMonth._t('월');
	}
	if(!$getMonth){
		$lastCount = 12;
		$textValue = _t('월');
	}else{
		$lastCount = ($getYear == 9999)?31:date('t',mktime(0,0,0,$getMonth,1,$getYear));	
		$textValue = "";
	}

	for ($i=1; $i<=$lastCount; $i++) {
		$tempData = getStatisticsDB($getDate, $i, $getMenu);
		if($tempData){
			array_push($getData, $tempData['count']);
			array_push($getTags,  rawurlencode($i.$textValue));
		}
	}
	$getData  = implode("|", $getData);
	$getTags  = implode("|", $getTags);
	$getTotal = getStatisticsTotalDB($getDate, $getMenu, 1);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('글(포스트) 통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>Stic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td align="right">
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>Pizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=archiveMonth&grpYear=".$getYear."&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?php
	if(!$getMonth){
		$getData = array();
		$getTags = array();
		for ($i=1; $i<=4; $i++) {
			$tempData = getQuartersStatistics($getDate, $i, $getMenu);
			if($tempData){
				array_push($getData, $tempData);
				array_push($getTags, rawurlencode($i."/4 "._t('분기')));
			}
		}
		$getData  = implode("|", $getData);
		$getTags  = implode("|", $getTags);
		$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('분기별')._t('글(포스트) 통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>QuartersStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>QuartersPizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=&grpYear=".$getYear."&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?php
	}
	$getData = array();
	$getTags = array();
	$tempData = getCategoryStatistics();

	$itemCnt = getCategoryStatisticsTotal(0, $getDate);
	if($itemCnt){
		array_push($getData, $itemCnt);
		array_push($getTags, $item['id']);
	}
	foreach ($tempData as $item){
		$itemCnt = getCategoryStatisticsTotal($item['id'], $getDate);
		if($itemCnt){
			array_push($getData, $itemCnt);
			array_push($getTags, $item['id']);
		}
	}
	$getData  = implode("|", $getData);
	$getTags  = implode("|", $getTags);
	$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('카테고리별')._t('글(포스트) 통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>CategoryStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=category&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>CategoryPizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=category&grpYear=".$getYear."&grpTypeName=category&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?php
	$query = "SELECT * FROM {$database['prefix']}Entries_hits";
	if (POD::queryRow($query)) {
		$getData = array();
		$getTags = array();
		$tempData = getEntryHitsStatistics($getDate);
		foreach ($tempData as $item){
			array_push($getData, $item['hits']);
			array_push($getTags, $item['id']);
		}
		$getData  = implode("|", $getData);
		$getTags  = implode("|", $getTags);
		$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('조회수 TOP 10')._t('글(포스트) 통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>HitsStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=hits&grpXLabelType=2&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>HitsPizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=entry&grpYear=".$getYear."&grpTypeName=hits&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?php
	}
	$getData = array();
	$getTags = array();
	for ($i=0; $i<=23; $i++) {
		$tempData = getTimeStatistics($getDate, $i, $getMenu);
		if($tempData){
			if($tempData['period'] == "00") {
				$tempData['period'] = str_replace("00","0",$tempData['period']);
			}else if(substr($tempData['period'], 0, 1) == "0"){
				$tempData['period'] = str_replace("0","",$tempData['period']);
			}
			array_push($getData, $tempData['count']);
			array_push($getTags, $tempData['period']);
		}
	}
	$getData  = implode("|", $getData);
	$getTags  = implode("|", $getTags);
	$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('시간대별')._t('글(포스트) 통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>TimeStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=time&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>TimePizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=&grpYear=".$getYear."&grpTypeName=time&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<hr class="hidden" />
<?php
}else if($getMenu == "comment"){
//##############################
// 댓글 통계 ###################
//##############################
	if($getYear == 9999 && !$getMonth)
	{
		$getData = array();
		$getTags = array();
		$getDateTitle = _t('전체 년도별');

		$tempData = getStatisticsDB('years', '', $getMenu);
		foreach ($tempData as $item){
			array_push($getData, $item['count']);
			array_push($getTags, rawurlencode($item['period']._t('년')));
		}
		$getData  = implode("|", $getData);
		$getTags  = implode("|", $getTags);
		$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('댓글 통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>YearStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>YearPizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=&grpYear=".$getYear."&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?
	}
	$getData = array();
	$getTags = array();
	if($getYear == 9999){
		$getDateTitle = (!$getMonth)? _t('전체'):_t('전체').'&nbsp'.$getMonth._t('월');
	}else{
		$getDateTitle = (!$getMonth)?$getYear._t('년도'):$getYear._t('년도').$getMonth._t('월');
	}
	if(!$getMonth){
		$lastCount = 12;
		$textValue = _t('월');
	}else{
		$lastCount = ($getYear == 9999)?31:date('t',mktime(0,0,0,$getMonth,1,$getYear));	
		$textValue = "";
	}

	for ($i=1; $i<=$lastCount; $i++) {
		$tempData = getStatisticsDB($getDate, $i, $getMenu);
		if($tempData){
			array_push($getData, $tempData['count']);
			array_push($getTags, rawurlencode($i.$textValue));
		}
	}
	$getData  = implode("|", $getData);
	$getTags  = implode("|", $getTags);
	$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('댓글 통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>Stic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>Pizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=&grpYear=".$getYear."&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?php
	if(!$getMonth){
		$getData = array();
		$getTags = array();
		for ($i=1; $i<=4; $i++) {
			$tempData = getQuartersStatistics($getYear, $i, $getMenu);
			if($tempData){
				array_push($getData, $tempData);
				array_push($getTags, rawurlencode($i."/4 "._t('분기')));
			}
		}
		$getData  = implode("|", $getData);
		$getTags  = implode("|", $getTags);
		$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('분기별')._t('댓글 통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>QuartersStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>QuartersPizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=&grpYear=".$getYear."&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?php
	}
	$getData = array();
	$getTags = array();
	$tempData = getCommentEntryMaxCount($getDate);
	foreach ($tempData as $item){
		array_push($getData, $item['comments']);
		array_push($getTags, $item['id']);
	}
	$getData  = implode("|", $getData);
	$getTags  = implode("|", $getTags);
	$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('댓글 TOP 10')._t('글 통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>EntryMaxStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=cmmax&grpXLabelType=2&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>EntryMaxPizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=entry&grpYear=".$getYear."&grpTypeName=cmmax&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?php
	$getData = array();
	$getTags = array();
	$tempData = getCommenterMaxCount($getDate, $getMenu);
	foreach ($tempData as $item){
		array_push($getData, $item['namecnt']);
		array_push($getTags, rawurlencode($item['name']));
	}
	$getData  = implode("|", $getData);
	$getTags  = implode("|", $getTags);
	$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('댓글자 TOP 10')._t('통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>erMaxStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=commenter&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>erMaxPizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=&grpYear=".$getYear."&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?php
	$getData = array();
	$getTags = array();
	for ($i=0; $i<=23; $i++) {
		$tempData = getTimeStatistics($getDate, $i, $getMenu);
		if($tempData){
			if($tempData['period'] == "00") {
				$tempData['period'] = str_replace("00","0",$tempData['period']);
			}else if(substr($tempData['period'], 0, 1) == "0"){
				$tempData['period'] = str_replace("0","",$tempData['period']);
			}
			array_push($getData, $tempData['count']);
			array_push($getTags, $tempData['period']);
		}
	}
	$getData  = implode("|", $getData);
	$getTags  = implode("|", $getTags);
	$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('시간대별')._t('댓글 통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>TimeStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=time&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>TimePizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=&grpYear=".$getYear."&grpTypeName=time&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<hr class="hidden" />
<?php
}else if($getMenu == "commenter"){
//##############################
// 댓글 작성자 목록 ############
//##############################
	if($getYear == 9999){
		$getDateTitle = (!$getMonth)? _t('전체'):_t('전체').'&nbsp'.$getMonth._t('월');;
	}else{
		$getDateTitle = (!$getMonth)?$getYear._t('년도'):$getYear._t('년도').$getMonth._t('월');	
	}
	$tempData = getCommenterMaxCount($getDate, $getMenu);
	$tempAllCount = getCommenterMaxCount(9999, $getMenu);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('댓글')._t('작성자')._t('목록');?>(<?php echo number_format(count($tempData))."/".number_format(count($tempAllCount));?><?php echo _t('명');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
						<div id="commenterList">
<?php
	$i = 0;
	foreach ($tempData as $item){
		$guestname = htmlspecialchars(UTF8::lessenAsEm($item['name'],20));
		if(!empty($item['home'])){
			$homepage = ereg('^[[:alnum:]]+:', $item['home'])?htmlspecialchars($item['home']):"http://".htmlspecialchars($item['home']);
			$guestname = "<a href=\"{$homepage}\" onclick=\"window.open(this.href); return false;\">{$guestname}</a>";
		}
		$count = "<span class=\"count\">(".$item['namecnt'].")</span>";
?>
							<div class="userlist"><?php echo $guestname?> <?php echo $count?></div><?php if(($i%4) == 3) echo "<div class=\"clear\"></div>\n";?>
<?
	$i++;
	}
?>
						</div>
						</td>
					</tr>
				</tbody>
			</table>
			<hr class="hidden" />
<?php
}else if($getMenu == "trackback"){
//##############################
// 역인글 통계 #################
//##############################
	if($getYear == 9999 && !$getMonth)
	{
		$getData = array();
		$getTags = array();
		$getDateTitle = _t('전체 년도별');

		$tempData = getStatisticsDB('years', '', $getMenu);
		foreach ($tempData as $item){
			array_push($getData, $item['count']);
			array_push($getTags, rawurlencode($item['period']._t('년')));
		}
		$getData  = implode("|", $getData);
		$getTags  = implode("|", $getTags);
		$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> 트랙백 통계(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>YearStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>YearPizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=&grpYear=".$getYear."&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?
	}
	$getData = array();
	$getTags = array();
	if($getYear == 9999){
		$getDateTitle = (!$getMonth)? _t('전체'):_t('전체').'&nbsp'.$getMonth._t('월');;
	}else{
		$getDateTitle = (!$getMonth)?$getYear._t('년도'):$getYear._t('년도').$getMonth._t('월');
	}
	if(!$getMonth){
		$lastCount = 12;
		$textValue = _t('월');
	}else{
		$lastCount = ($getYear == 9999)?31:date('t',mktime(0,0,0,$getMonth,1,$getYear));	
		$textValue = "";
	}

	for ($i=1; $i<=$lastCount; $i++) {
		$tempData = getStatisticsDB($getDate, $i, $getMenu);
		if($tempData){
			array_push($getData, $tempData['count']);
			array_push($getTags, rawurlencode($i.$textValue));
		}
	}
	$getData  = implode("|", $getData);
	$getTags  = implode("|", $getTags);
	$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('트랙백')._t('통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>Stic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>Pizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=&grpYear=".$getYear."&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?php
	if(!$getMonth){
		$getData = array();
		$getTags = array();
		for ($i=1; $i<=4; $i++) {
			$tempData = getQuartersStatistics($getYear, $i, $getMenu);
			if($tempData){
				array_push($getData, $tempData);
				array_push($getTags, rawurlencode($i."/4 "._t('분기')));
			}
		}
		$getData  = implode("|", $getData);
		$getTags  = implode("|", $getTags);
		$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('분기별')._t('트랙백')._t('통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>QuartersStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>QuartersPizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=&grpYear=".$getYear."&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?php
	}
	$getData = array();
	$getTags = array();
	$getLink = array();
	$tempData = getTrackbackEntryMaxCount($getDate);
	foreach ($tempData as $item){
		array_push($getData, $item['trackbacks']);
		array_push($getTags, $item['id']);
	}
	$getData  = implode("|", $getData);
	$getTags  = implode("|", $getTags);
	$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('트랙백')._t('받은 TOP 10')._t('글 통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>EntryMaxStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=tbmax&grpXLabelType=2&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>EntryMaxPizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=entry&grpYear=".$getYear."&grpTypeName=tbmax&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?php
	$getData = array();
	$getTags = array();
	$tempData = getTrackbackCallEntryMaxCount($getDate);
	foreach ($tempData as $item){
		array_push($getData, $item['trackbacklogs']);
		array_push($getTags, $item['id']);
	}
	$getData  = implode("|", $getData);
	$getTags  = implode("|", $getTags);
	$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('트랙백')._t('보낸 TOP 10')._t('글 통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>CallEntryMaxStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=cmmax&grpXLabelType=2&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>CallEntryMaxPizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=entry&grpYear=".$getYear."&grpTypeName=cmmax&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?php
	$getData = array();
	$getTags = array();
	for ($i=0; $i<=23; $i++) {
		$tempData = getTimeStatistics($getDate, $i, $getMenu);
		if($tempData){
			if($tempData['period'] == "00") {
				$tempData['period'] = str_replace("00","0",$tempData['period']);
			}else if(substr($tempData['period'], 0, 1) == "0"){
				$tempData['period'] = str_replace("0","",$tempData['period']);
			}
			array_push($getData, $tempData['count']);
			array_push($getTags, $tempData['period']);
		}
	}
	$getData  = implode("|", $getData);
	$getTags  = implode("|", $getTags);
	$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('시간대별')._t('트랙백')._t('통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>TimeStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=time&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>TimePizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=&grpYear=".$getYear."&grpTypeName=time&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<hr class="hidden" />
<?php
}else if($getMenu == "guestbook"){
//##############################
// 방명록 통계 #################
//##############################
	if($getYear == 9999 && !$getMonth)
	{
		$getData = array();
		$getTags = array();
		$getDateTitle = _t('전체 년도별');

		$tempData = getStatisticsDB('years', '', $getMenu);
		foreach ($tempData as $item){
			array_push($getData, $item['count']);
			array_push($getTags, rawurlencode($item['period']._t('년')));
		}
		$getData  = implode("|", $getData);
		$getTags  = implode("|", $getTags);
		$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('방명록')._t('통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>YearStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>YearPizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=&grpYear=".$getYear."&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?
	}
	$getData = array();
	$getTags = array();
	if($getYear == 9999){
		$getDateTitle = (!$getMonth)? _t('전체'):_t('전체').'&nbsp'.$getMonth._t('월');;
	}else{
		$getDateTitle = (!$getMonth)?$getYear._t('년도'):$getYear._t('년도').$getMonth._t('월');	
	}
	if(!$getMonth){
		$lastCount = 12;
		$textValue = _t('월');
	}else{
		$lastCount = ($getYear == 9999)?31:date('t',mktime(0,0,0,$getMonth,1,$getYear));	
		$textValue = "";
	}

	for ($i=1; $i<=$lastCount; $i++) {
		$tempData = getStatisticsDB($getDate, $i, $getMenu);
		if($tempData){
			array_push($getData, $tempData['count']);
			array_push($getTags, rawurlencode($i.$textValue));
		}
	}
	$getData  = implode("|", $getData);
	$getTags  = implode("|", $getTags);
	$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('방명록')._t('통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>Stic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>Pizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=&grpYear=".$getYear."&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?php
	if(!$getMonth){
		$getData = array();
		$getTags = array();
		for ($i=1; $i<=4; $i++) {
			$tempData = getQuartersStatistics($getYear, $i, $getMenu);
			if($tempData){
				array_push($getData, $tempData);
				array_push($getTags, rawurlencode($i."/4 "._t('분기')));
			}
		}
		$getData  = implode("|", $getData);
		$getTags  = implode("|", $getTags);
		$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('분기별')._t('방명록')._t('통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>QuartersStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>QuartersPizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=&grpYear=".$getYear."&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?php
	}
	$getData = array();
	$getTags = array();
	$tempData = getCommenterMaxCount($getDate, $getMenu);
	foreach ($tempData as $item){
		array_push($getData, $item['namecnt']);
		array_push($getTags, rawurlencode($item['name']));
	}
	$getData  = implode("|", $getData);
	$getTags  = implode("|", $getTags);
	$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('방명록')._t('댓글자 TOP 10')._t('통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>CommenterMaxStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=commenter&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>CommenterMaxPizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=&grpYear=".$getYear."&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?php
	$getData = array();
	$getTags = array();
	for ($i=0; $i<=23; $i++) {
		$tempData = getTimeStatistics($getDate, $i, $getMenu);
		if($tempData){
			if($tempData['period'] == "00") {
				$tempData['period'] = str_replace("00","0",$tempData['period']);
			}else if(substr($tempData['period'], 0, 1) == "0"){
				$tempData['period'] = str_replace("0","",$tempData['period']);
			}
			array_push($getData, $tempData['count']);
			array_push($getTags, $tempData['period']);
		}
	}
	$getData  = implode("|", $getData);
	$getTags  = implode("|", $getTags);
	$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('시간대별')._t('방명록')._t('통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>TimeStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=time&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>TimePizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=&grpYear=".$getYear."&grpTypeName=time&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<hr class="hidden" />
<?php
}else if($getMenu == "guestbookcommenter"){
//##############################
// 방명록 작성자 목록 ############
//##############################
	if($getYear == 9999){
		$getDateTitle = (!$getMonth)? _t('전체'):_t('전체').'&nbsp'.$getMonth._t('월');;
	}else{
		$getDateTitle = (!$getMonth)?$getYear._t('년도'):$getYear._t('년도').$getMonth._t('월');	
	}
	$tempData = getCommenterMaxCount($getDate, $getMenu);
	$tempAllCount = getCommenterMaxCount(9999, $getMenu);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('방명록')._t('작성자')._t('목록');?>(<?php echo number_format(count($tempData))."/".number_format(count($tempAllCount));?>명)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
						<div id="commenterList">
<?php
	$i = 0;
	foreach ($tempData as $item){
		$guestname = htmlspecialchars(UTF8::lessenAsEm($item['name'],20));
		if(!empty($item['home'])){
			$homepage = ereg('^[[:alnum:]]+:', $item['home'])?htmlspecialchars($item['home']):"http://".htmlspecialchars($item['home']);
			$guestname = "<a href=\"{$homepage}\" onclick=\"window.open(this.href); return false;\">{$guestname}</a>";
		}
		$count = "<span class=\"count\">(".$item['namecnt'].")</span>";
?>
							<div class="userlist"><?php echo $guestname?> <?php echo $count?></div><?php if(($i%4) == 3) echo "<div class=\"clear\"></div>\n";?>
<?
	$i++;
	}
?>
						</div>
						</td>
					</tr>
				</tbody>
			</table>
			<hr class="hidden" />
<?php
}else if($getMenu == "tag"){
//##############################
// 태그 통계 ###################
//##############################
	$getDateTitle = (!$getMonth)?$getYear._t('년도'):$getYear._t('년도').$getMonth._t('월');
	$getData = array();
	$getTags = array();
	$tempData = getTagMaxCount();
	foreach ($tempData as $item){
		array_push($getData, $item['count']);
		array_push($getTags, rawurlencode($item['name']));
	}
	$getData  = implode("|", $getData);
	$getTags  = implode("|", $getTags);
	$getTotal = getStatisticsTotalDB($getDate, $getMenu, 1);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo _t('인기')._t('태그 TOP 10')._t('통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>MaxStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=tag&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>MaxPizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=tag&grpYear=".$getYear."&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?php
	$getData = array();
	$getTags = array();
	if($getYear == 9999){
		$getDateTitle = (!$getMonth)? _t('전체'):_t('전체').'&nbsp'.$getMonth._t('월');;
	}else{
		$getDateTitle = (!$getMonth)?$getYear._t('년도'):$getYear._t('년도').$getMonth._t('월');	
	}
	$tempData = getTagEntryMaxCount($getDate, 1);
	foreach ($tempData as $item){
		array_push($getData, $item['count']);
		array_push($getTags, $item['id']);
	}
	$getData  = implode("|", $getData);
	$getTags  = implode("|", $getTags);
	$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('태그')._t('걸린 TOP 10')._t('글 통계');?>(<?php echo number_format(getTagEntryMaxCount($getDate, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>EntryMaxStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=tagmax&grpXLabelType=2&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>EntryMaxPizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=entry&grpYear=".$getYear."&grpTypeName=tagmax&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<hr class="hidden" />
<?php
}else if($getMenu == "visit"){
//##############################
// 방문자 통계 #################
//##############################
	if($getYear == 9999 && !$getMonth)
	{
		$getData = array();
		$getTags = array();
		$getDateTitle = _t('전체 년도별');

		$tempData = getStatisticsDB('years', '', $getMenu);
		foreach ($tempData as $item){
			array_push($getData, $item['count']);
			array_push($getTags, rawurlencode($item['period']._t('년')));
		}
		$getData  = implode("|", $getData);
		$getTags  = implode("|", $getTags);
		$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('방문자')._t('통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>YearStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>YearPizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=&grpYear=".$getYear."&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?
	}
	$getData = array();
	$getTags = array();
	if($getYear == 9999){
		$getDateTitle = (!$getMonth)? _t('전체'):_t('전체').'&nbsp'.$getMonth._t('월');;
	}else{
		$getDateTitle = (!$getMonth)?$getYear._t('년도'):$getYear._t('년도').$getMonth._t('월');	
	}
	if(!$getMonth){
		$lastCount = 12;
		$textValue = _t('월');
	}else{
		$lastCount = ($getYear == 9999)?31:date('t',mktime(0,0,0,$getMonth,1,$getYear));	
		$textValue = "";
	}

	for ($i=1; $i<=$lastCount; $i++) {
		$tempData = getStatisticsDB($getDate, $i, $getMenu);
		if($tempData){
			array_push($getData, $tempData['count']);
			array_push($getTags, rawurlencode($i.$textValue));
		}
	}
	$getData  = implode("|", $getData);
	$getTags  = implode("|", $getTags);
	$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('방문자')._t('통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>Stic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>Pizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=&grpYear=".$getYear."&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?php
	if(!$getMonth){
		$getData = array();
		$getTags = array();
		for ($i=1; $i<=4; $i++) {
			$tempData = getQuartersStatistics($getYear, $i, $getMenu);
			if($tempData){
				array_push($getData, $tempData);
				array_push($getTags, rawurlencode($i."/4 "._t('분기')));
			}
		}
		$getData  = implode("|", $getData);
		$getTags  = implode("|", $getTags);
		$getTotal = getStatisticsTotalDB($getDate, $getMenu, 0);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo $getDateTitle;?> <?php echo _t('분기별')._t('방문자')._t('통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 0))."/".number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>QuartersStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>QuartersPizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=&grpYear=".$getYear."&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<hr class="hidden" />
			<hr class="hidden" />
<?php
	}
}else if($getMenu == "referer"){
//##############################
// 리퍼러 통계 #################
//##############################
	$getData = array();
	$getTags = array();
	$tempData = getRefererMaxCount();
	foreach ($tempData as $item){
		array_push($getData, $item['count']);
		array_push($getTags, rawurlencode($item['host']));
	}
	$getData  = implode("|", $getData);
	$getTags  = implode("|", $getTags);
	$getTotal = getStatisticsTotalDB($getDate, $getMenu, 1);
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo _t('리퍼러')._t('TOP 10')._t('통계');?>(<?php echo number_format(getStatisticsTotalDB($getDate, $getMenu, 1));?><?php echo _t('개');?>)</h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>MaxStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=refer&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>MaxPizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=&grpYear=".$getYear."&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?php
	$getData = array();
	$getTags = array();
	$getDuplicate = array();
	$beforCount = "";
	$tempData = getRefererKeywordStatistics();
	foreach ($tempData as $item) if($item['count'] != $beforCount){ $beforCount = $item['count']; }else{	array_push($getDuplicate, $item['count']); } 
	$getDuplicate = array_count_values($getDuplicate);

	$beforCount = "";
	foreach ($tempData as $item){
		if($item['count'] != $beforCount && $item['rank'] < 11){
			array_push($getData, $item['count']);
			$duplicateCount = ($getDuplicate[$item['count']])? _f("(외 %1 개)", $getDuplicate[$item['count']]) : _f("(외 %1 개)", 0);
			array_push($getTags, rawurlencode(htmlspecialchars(UTF8::lessenAsEm($item['keyword'],15)).$duplicateCount));
			$beforCount = $item['count'];
		}
	}
	$getData  = implode("|", $getData);
	$getTags  = implode("|", $getTags);
	$getTotal = $tempData[0]['total'];
?>
			<table class="data-inbox" cellspacing="0" cellpadding="0">
				<thead>
					<tr>
						<th colspan="2"><h3><?php echo _t('리퍼러')._t('키워드 TOP 10')._t('통계');?>(<?php echo number_format($tempData[0]['total']);?><?php echo _t('개');?>) : <?php echo $tempData[0]['dateStart'];?> ~ <?php echo $tempData[0]['dateEnd'];?></h3></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							<div class="flash-stic">
								<div id="get<?php echo $getMenu;?>KeywordMaxStic">
									<?php echo open_flash_chart_object(470, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=bar&grpTypeName=referkey&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_bar" : "")); ?>
								</div>
							</div>
						</td>
						<td>
							<div class="flash-pizza">
								<div id="get<?php echo $getMenu;?>KeywordMaxPizza">
									<?php echo open_flash_chart_object(265, 250, $blogURL . "/plugin/BlogStatisticsProcess/?grpStyle=pie&grpLinkType=&grpYear=".$getYear."&grpTypeName=&grpData=" . $getData . "&grpTotal=" . $getTotal . "&grpLabel=" . $getTags . (empty($getData) ? "&grpNoData_pie" : "")); ?>
								</div>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
<?php
}
?>
		</div>
	</div>

</div>
						
<?php 
}

function PN_BlogStatisticsProcess($target){
	global $blogid, $pluginURL, $defaultURL, $blog;
	
	
	
	if (doesHaveOwnership()){
		include_once ('lib/open-flash-chart.php');

		$grpStyle = $_GET['grpStyle'];
		$grpData = explode("|", $_GET['grpData']);
		$grpTotal = $_GET['grpTotal'];
		$grpYear = $_GET['grpYear'];
		$grpTypeName = $_GET['grpTypeName'];
		$grpLinkType = $_GET['grpLinkType'];
		$grpXLabelType = isset($_GET['grpXLabelType']) ? $_GET['grpXLabelType'] : 0 ;
		$grpLabel = explode("|", $_GET['grpLabel']);
		$grpSubTitle = array();
		$grpLabelTemp = array();

		if(!empty($_GET['grpData'])){
			if($grpTypeName == "category"){
				for( $i=0; $i<count($grpLabel); $i++ )
				{
					$grpSubTitle[] = rawurlencode(($grpLabel[$i] == 0) ?  _t('분류없음') : htmlspecialchars(UTF8::lessenAsEm(getCategoryNameById($blogid, $grpLabel[$i]),15)));
				}
			}else if($grpTypeName == "hits" || $grpTypeName == "cmmax" || $grpTypeName == "tbmax" || $grpTypeName == "tagmax"){
				for( $i=0; $i<count($grpLabel); $i++ )
				{
					$grpSubTitle[] = rawurlencode(htmlspecialchars(UTF8::lessenAsEm(str_replace(",","-",getEntryTitleById($blogid, $grpLabel[$i])),25)));
				}
			}else if($grpTypeName == "commenter" || $grpTypeName == "tag"){
				for( $i=0; $i<count($grpLabel); $i++ )
				{
					$grpSubTitle[] = htmlspecialchars(UTF8::lessenAsEm($grpLabel[$i],15));
					$grpLabelTemp[] = htmlspecialchars(UTF8::lessenAsEm($grpLabel[$i],6,''));
				}
				$grpLabel = $grpLabelTemp;
			}else if($grpTypeName == "time"){
				for( $i=0; $i<count($grpLabel); $i++ )
				{
					$grpSubTitle[] = rawurlencode($grpLabel[$i]."시");
				}
			}else if($grpTypeName == "refer"){
				for( $i=0; $i<count($grpLabel); $i++ )
				{
					$tmpHost = explode('.', $grpLabel[$i]);
					$tmpHostCnt = count($tmpHost) - 1;
					$tmpDomain = ( strlen($tmpHost[$tmpHostCnt]) < 3 ? $tmpHost[$tmpHostCnt-2] . '.' : '' ) . $tmpHost[$tmpHostCnt-1] . '.' . $tmpHost[$tmpHostCnt];
					$cutDomain = explode('.', $tmpDomain);
					$grpSubTitle[] = htmlspecialchars($grpLabel[$i]);
					$grpLabelTemp[] = htmlspecialchars(UTF8::lessenAsEm($cutDomain[0],6,''));
				}
				$grpLabel = $grpLabelTemp;
			}else if($grpTypeName == "referkey"){
				for( $i=0; $i<count($grpLabel); $i++ )
				{
					$tmpLabel = explode('(', $grpLabel[$i]);
					$grpSubTitle[] = htmlspecialchars(UTF8::lessenAsEm($grpLabel[$i],15));
					$grpLabelTemp[] = htmlspecialchars(UTF8::lessenAsEm($tmpLabel[0],6,''));
				}
				$grpLabel = $grpLabelTemp;
			}

			$g = new graph();
			$g->bg_colour = '#FFFFFF';
			if($grpStyle == "pie"){
				$grpClickLink = "";
				$grpPercent = array();
				$grpLink = array();	
				for( $i=0; $i<count($grpData); $i++ )
				{
					$grpPercent[] = round((($grpData[$i] / $grpTotal) * 100), 0);
				}

				if($grpLinkType == "archiveYear"){
					for( $i=0; $i<count($grpLabel); $i++ )
					{
						$permalink = $defaultURL."/archive/".substr($grpLabel[$i],0,4);
						$grpLink[] = "javascript:window.open('{$permalink}');void(0)";
					}
					$grpClickLink = "<br>click on the pie.";
				}else if($grpLinkType == "archiveMonth" && $grpYear != "9999"){
					for( $i=0; $i<count($grpLabel); $i++ )
					{
						$cutMonth = str_replace("월","",$grpLabel[$i]);
						$tmpMonth =  strlen($cutMonth)==1 ? "0".$cutMonth : $cutMonth;
						$permalink = $defaultURL."/archive/".$grpYear.$tmpMonth;
						$grpLink[] = "javascript:window.open('{$permalink}');void(0)";
					}
					$grpClickLink = "<br>click on the pie.";
				}else if($grpLinkType == "category"){
					for( $i=0; $i<count($grpLabel); $i++ )
					{
						$permalink = $defaultURL."/category/".getCategoryLabelById($blogid, $grpLabel[$i]);
						$grpLink[] = "javascript:window.open('{$permalink}');void(0)";
					}
					$grpClickLink = "<br>click on the pie.";
				}else if($grpLinkType == "entry"){
					for( $i=0; $i<count($grpLabel); $i++ )
					{
						$permalink = $defaultURL.($blog['useSlogan']?"/entry/".getEntrySloganById($blogid, $grpLabel[$i]):"/".$grpLabel[$i]);
						$grpLink[] = "javascript:window.open('{$permalink}');void(0)";
					}
					$grpClickLink = "<br>click on the pie.";
				}else if($grpLinkType == "tag"){
					for( $i=0; $i<count($grpLabel); $i++ )
					{
						$permalink = $defaultURL."/tag/".$grpLabel[$i];
						$grpLink[] = "javascript:window.open('{$permalink}');void(0)";
					}
					$grpClickLink = "<br>click on the pie.";
				}

				$g->pie(75,'#ffffff','#000000',false,1);
				$g->pie_values($grpData, $grpLabel, $grpLink, $grpSubTitle);
				$g->pie_slice_colours( array('#B9D2E6','#E2B11C','#A3CF22','#EC7122','#4FC0C0','#D45E5E','#A275A2','#52A7D2','#9F373B','#B4ADA5','#5FC97E','#CFB85D','#9DC64E','#FFAB29','#E23838','#43CEA9','#4CA9D9','#BA4ECA','#6C79DA','#CCCCCC','#AB5C06','#C06868','#5FC97E','CFB85D') );
				$g->set_tool_tip((count($grpSubTitle)?'#x_title#<br>':'#x_label#<br>').'#val#(#percent#%25)'.$grpClickLink);
			} else if($grpStyle == "bar") {
				$g->title('&nbsp;', '{font-size:12px; color:#000000;margin-top:0px;padding:3px;}');
				$g->set_data($grpData);
				$g->set_bar_titles($grpSubTitle);
				$g->bar_glass(70, '#68B1D9', '#62A0C1', '', 12) ;
				$g->bar_colours( array('#B9D2E6','#E2B11C','#A3CF22','#EC7122','#4FC0C0','#D45E5E','#A275A2','#52A7D2','#9F373B','#B4ADA5','#5FC97E','#CFB85D','#9DC64E','#FFAB29','#E23838','#43CEA9','#4CA9D9','#BA4ECA','#6C79DA','#CCCCCC','#AB5C06','#C06868','#5FC97E','CFB85D') );
				$g->x_axis_colour('#909090', '#D2D2FB');
				$g->y_axis_colour('#909090', '#D2D2FB');
				$g->set_x_labels($grpLabel);
				$g->set_x_label_style(10, '#000000', $grpXLabelType, -1);
				$g->set_y_label_style(9, '#888888');

				$tmp_data_max = floor(Max($grpData) * 1.2);
				if($tmp_y_max = $tmp_data_max % 10) $tmp_data_max = $tmp_data_max + (10 - $tmp_y_max);

				$g->set_y_max($tmp_data_max);
				$g->set_y_legend('', 11, '#736AFF' );
				$g->set_tool_tip((count($grpSubTitle)?'#x_title#<br>':'#x_label#<br>').'#val#');
			} else if($grpStyle == "line") {
				$g->title('', '{font-size:1px; color:#000000;}' );
				$g->set_data($grpData);
				$g->line_dot( 2, 4, '#6FBBC6', _t('최근 7일간 방문자 수'), 11);    // <-- 3px thick + dots
				$g->set_x_labels($grpLabel);
				$g->set_x_label_style(8, '#333333', $grpXLabelType, -1);
				$g->x_axis_colour('#909090', '#e7e7e7');
				$g->y_axis_colour('#909090', '#e7e7e7');

				$tmp_data_max = floor(Max($grpData) * 1.2);
				if($tmp_y_max = $tmp_data_max % 10) $tmp_data_max = $tmp_data_max + (10 - $tmp_y_max);

				$g->set_y_max($tmp_data_max);
				$g->set_y_legend('', 1, '#736AFF' );
				$g->y_label_steps(4);
				$g->set_y_label_style(8, '#333333', $grpXLabelType, -1);
				$g->set_tool_tip((count($grpSubTitle)?'#x_title#<br>':'#x_label#<br>').'#val#');
			}
			echo $g->render();
			flush();
		}
	}
}

function PN_Blog_Statistics_DataSet($DATA){
	requireComponent('Textcube.Function.misc');
	$cfg = misc::fetchConfigVal($DATA);
	return true;
}
?>
