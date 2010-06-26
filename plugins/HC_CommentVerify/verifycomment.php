<?php
include_once("./recaptcha.php");

if (!function_exists("curl_init")) die("Need cURL support.");

if (isset($_POST['recaptcha_response_field']) && !empty($_POST['recaptcha_response_field'])) {
	$answer = trim(stripslashes($_POST['recaptcha_response_field']));
	$postData['challenge'] = $_POST['recaptcha_challenge_field'];	
	$postData['response'] = urlencode($answer);
	$ch = curl_init("http://api-verify.recaptcha.net/verify");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($ch);
	curl_close($ch);
	$response = explode(PHP_EOL, $data);
	if ($response[0] == 'true') {
		echo $_POST['entryId'];
	} else {
		echo 'Error, please try again.';
	}
} else {
	echo 'Error, please try again.';
}
?>
