<?php

function mailNotifyComment($target, $mother)
{
	global $blogid, $hostURL, $blogURL, $database, $service, $configVal, $serviceURL, $pluginURL;
	requireComponent('TextCube.Function.misc');

	include_once( ROOT."/library/contrib/phpmailer/class.phpmailer.php" );
	$mail = new PHPMailer();
	$data = misc::fetchConfigVal($configVal);
	$type = 1; // comment
	if ($mother['entry'] == 0) $type = 3; // guestbook

	$notifyType = (isset($data['notifysetting']) && (int)$data['notifysetting']===1) ? true : false;
	$nofityGuestbook = (isset($data['notifyguestbook']) && (int)$data['notifyguestbook']===1) ? true : false;

	$userid = getUserId();
	$mailselfcheck = ($mother['replier']===$userid) ? true : false;

	$mailercheck = false;
	if((1 === $type) || (3 === $type && $nofityGuestbook))
	{
		if ($notifyType)
		{
			if ((1 === $type && !$mailselfcheck) || (3 === $type && $nofityGuestbook))
			{
				$email = (isset($data['mail']) && !empty($data['mail'])) ? $data['mail'] : POD::queryCell("SELECT `loginid` FROM `{$database['prefix']}Users` WHERE `userid`=$userid LIMIT 1");
				$name = POD::queryCell("SELECT `name` FROM `{$database['prefix']}Users` WHERE `userid`=$userid LIMIT 1");
				if($mail->ValidateAddress($email)) {
					$mail->AddAddress( $email, $name );
					$mailercheck = true;
				}
			}
		} else {
			$result = POD::query("SELECT `u`.`userid`, `u`.`loginid` AS email, `u`.`name` FROM `{$database['prefix']}TeamUserSettings` AS t
					LEFT JOIN `{$database['prefix']}Users` AS u ON `u`.`userid`=`t`.`userid` WHERE `t`.`blogid`={$blogid}");
			if(POD::num_rows($result)>0) {
				while($row = POD::fetch($result, 'array'))
				{
					if($mail->ValidateAddress($row['email'])) {
						if ($row['userid']===$userid || $row['userid']===$mother['replier'])
						{
							$mail->AddAddress( $row['email'], $row['name'] );
						} else {
							$mail->AddCC( $row['email'], $row['name'] );
						}
						$mailercheck = true;
					}
				}
			}
		}
	}
	if($mailercheck) {
		$link = ($type===1) ? "$hostURL$blogURL/{$mother['entry']}" : ((!is_null($mother['parent']) && $mother['parent']>0) ? "$hostURL$blogURL/guestbook/".$mother['parent']."#guestbook".$mother['entry'] : "$hostURL$blogURL/guestbook/".$mother['entry']);

		$message = "<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
</head>
<body>
<div style=\"text-align:left;\">
<table style=\"width:100%;border:1px solid #000;\">
<tr><th colspan=\"2\"> "._f('You have a new %1 %2.', ($mother['secret'] === true ? _t('secret') : ''), ($type===1 ? _t('comment.') : _t('message.')))." </th></tr>
<tr><td style=\"width:150px;\">"._t('link')."</td><td><a href=\"".$link."\">"._t('link')."</a></td></tr>
<tr><td style=\"width:150px;\">"._t('nickname')."</td><td>".$mother['name']."</td></tr>
<tr><td colspan=\"2\">"._t('content')."</td></tr>
<tr><td colspan=\"2\">".nl2br($mother['comment'])."<br /><br />via IP:".$mother['ip']."</td></tr>
</table>
</div>
<p style=\"text-align:center;color:#999999;font-size: 0.75em;\">MailNotification By TextCube.</p>
</body>
</html>";
		ob_start();
		$mail->SetLanguage( 'en', ROOT."/library/contrib/phpmailer/language/" );
		$mail->IsHTML(true);
		$mail->CharSet  = 'utf-8';
		$mail->From     = "noreply@".$service['domain'];
		$mail->FromName = "TextCube Mail Notification";
		$mail->Subject  = (!isset($data['emailsubject']) || empty($data['emailsubject'])) ? _t("[TextCube] New message notification.") : $data['emailsubject'];
		$mail->Body     = $message;
		$mail->AltBody  = 'To view this email message, open the email with html enabled mailer.';

		if( !getServiceSetting( 'useCustomSMTP', 0 ) ) {
			$mail->IsMail();
		} else {
			$mail->IsSMTP();
			$mail->Host = getServiceSetting( 'smtpHost', '127.0.0.1' );
			$mail->Port = getServiceSetting( 'smtpPort', 25 );
		}
		$mail->Send();
		$mail->ClearAddresses();
		$mail->ClearCCs();
		ob_end_clean();
	}

	return $target && true;
}
function MailNotificationDataSet($DATA){
	requireComponent('Textcube.Function.misc');
	$cfg = misc::fetchConfigVal( $DATA );
	return true;
}
?>
