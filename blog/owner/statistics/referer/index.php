<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header4.php';
require ROOT . '/lib/piece/owner/contentMenu41.php';
?>
						<form method="post" action="<?php echo $blogURL?>/owner/statistics">
							<div id="part-statistics-rank" class="part">
								<h2 class="caption"><span class="main-text"><?=_t('리퍼러 순위')?></span></h2>
								
								<table class="data-inbox" cellspacing="0" cellpadding="0">
									<thead>
										<tr>
											<th class="number"><span class="text"><?=_t('순위')?></span></th>
											<th class="site"><span class="text"><?=_t('리퍼러')?></span></th>
										</tr>
									</thead>
									<tbody>
<?
$temp = getRefererStatistics($owner);
for ($i=0; $i<sizeof($temp); $i++) {
	$record = $temp[$i];
	
	$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= ($i == sizeof($temp) - 1) ? ' last-line' : '';
?>
										<tr class="<?php echo $className?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
											<td class="rank"><?=$i + 1?>.</td>
											<td class="site"><a href="http://<?=escapeJSInAttribute($record['host'])?>" onclick="window.open(this.href); return false;"><?=htmlspecialchars($record['host'])?></a> <span class="count">(<?=$record['count']?>)</span></td>
										</tr>
<?
}
?>
									</tbody>
								</table>
							</div>
							
							<hr class="hidden" />
							
							<div id="part-statistics-log" class="part">
								<h2 class="caption"><span class="main-text"><?=_t('리퍼러 로그')?></span></h2>
								
								<table class="data-inbox" cellspacing="0" cellpadding="0">
									<thead>
										<tr>
											<th class="number"><span class="text"><?=_t('날짜')?></span></th>
											<th class="site"><span class="text"><?=_t('주소')?></span></th>
										</tr>
									</thead>
									<tbody>
<?
$temp = getRefererLogs();
for ($i=0; $i<sizeof($temp); $i++) {
	$record = $temp[$i];
	
	$className = ($i % 2) == 1 ? 'even-line' : 'odd-line';
	$className .= ($i == sizeof($temp) - 1) ? ' last-line' : '';
?>
										<tr class="<?php echo $className?> inactive-class" onmouseover="rolloverClass(this, 'over')" onmouseout="rolloverClass(this, 'out')">
											<td class="date"><?=Timestamp::formatDate($record['referred'])?></td>
											<td class="address"><a href="<?=escapeJSInAttribute($record['url'])?>" onclick="window.open(this.href); return false;" title="<?=htmlspecialchars($record['url'])?>"><?=fireEvent('ViewRefererURL', htmlspecialchars(UTF8::lessenAsEm($record['url'], 70)), $record)?></a></td>
										</tr>
<?
}
?>
									</tbody>
								</table>
							</div>
						</form>
<?
require ROOT . '/lib/piece/owner/footer1.php';
?>