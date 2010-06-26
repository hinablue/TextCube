<?php
include_once '../../../config.php';

$count = $_POST['count'];
$blogid = $_POST['id'];

$dbConn = mysql_connect($database['server'], $database['username'], $database['password']); 
mysql_select_db($database['database'], $dbConn); 
$result = mysql_query("SELECT datemark, visits FROM {$database['prefix']}DailyStatistics WHERE blogid={$blogid} ORDER BY datemark DESC LIMIT ".$count, $dbConn) or die(mysql_error()); 
$i = $count;
while($data = mysql_fetch_array($result)) {
	echo "&count$i=".$data[visits];
	echo "&date$i=".$data[datemark];
	$i--;
}
?>