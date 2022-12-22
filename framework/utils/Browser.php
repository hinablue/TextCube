<?php
/// Copyright (c) 2004-2014, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

final class Utils_Browser extends Singleton
{
	private static $browserName;
	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}

	function __construct() {
		self::$browserName = null;
		$this->machineName = null;
	}

	public function getBrowserName() {
		/// Blocking (is in development)
		$ctx = Model_Context::getInstance();
//		if($ctx->getProperty('service.usemobileadmin',true) === false) {
//			return 'unknown';
//		}
		if(!is_null(self::$browserName)) return self::$browserName;
		if(isset($_SERVER['HTTP_USER_AGENT'])) {
			if(strpos($_SERVER['HTTP_USER_AGENT'],'iPhone') ||
				strpos($_SERVER['HTTP_USER_AGENT'],'iPod') ||
				strpos($_SERVER['HTTP_USER_AGENT'],'Mobile Safari') ||
				(strpos($_SERVER['HTTP_USER_AGENT'],'AppleWebkit')!== false &&
					(strpos($_SERVER['HTTP_USER_AGENT'],'SymbianOS')!== false ||	// Nokia
					strpos($_SERVER['HTTP_USER_AGENT'],'Pre')!== false))){ 	// Palm pre
				self::$browserName = 'MobileSafari';
			} else if(strpos($_SERVER['HTTP_USER_AGENT'],'Android')) {
				self::$browserName = 'Android';
			} else if(strpos($_SERVER['HTTP_USER_AGENT'],'Firefox') ||
				strpos($_SERVER['HTTP_USER_AGENT'],'iceweasel') ||
				strpos($_SERVER['HTTP_USER_AGENT'],'Minefield')) {
				self::$browserName = 'firefox';
			} else if(strpos($_SERVER['HTTP_USER_AGENT'],'Safari')) {
				self::$browserName = 'Safari';
			} else if(strpos($_SERVER['HTTP_USER_AGENT'],'Chrome')) {
				self::$browserName = 'Chrome';
			} else if (strpos($_SERVER['HTTP_USER_AGENT'],'Webkit')) {
				self::$browserName = 'Webkit';
			} else if (strpos($_SERVER['HTTP_USER_AGENT'],'IEMobile')) {
				self::$browserName = 'IEMobile';
			} else if (strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')) {
				self::$browserName = 'IE';
			} else if (strpos($_SERVER['HTTP_USER_AGENT'],'Opera Mini')) {
				self::$browserName = 'OperaMini';
			} else if (strpos($_SERVER['HTTP_USER_AGENT'],'Opera')) {
				self::$browserName = 'Opera';
			} else if (strpos($_SERVER['HTTP_USER_AGENT'],'AvantGo')) {	// Avantgo (palm)
				self::$browserName = 'AvantGo';
			} else if (strpos($_SERVER['HTTP_USER_AGENT'],'DoCoMo')) {	// DoCoMo Phones
				self::$browserName = 'DoCoMo';
			} else if (strpos($_SERVER['HTTP_USER_AGENT'],'Minimo')) {	// Firefox mini
				self::$browserName = 'Minimo';
			} else if (strpos($_SERVER['HTTP_USER_AGENT'],'Maemo')) {	// Firefox mini
				self::$browserName = 'Maemo';
			} else if (strpos($_SERVER['HTTP_USER_AGENT'],'BlackBerry')!== false) {	// Blackberry
				self::$browserName = 'BlackBerry';
			} else if (strpos($_SERVER['HTTP_USER_AGENT'],'POLARIS')!== false) {	// LGE Phone
				self::$browserName = 'Polaris';
			} else {
				self::$browserName = 'unknown';
			}
		}
		return self::$browserName;
	}
	public function getVersion() {
	}

	public function isMobile() {
		return (in_array($this->getBrowserName(),array('MobileSafari','Android','Maemo','OperaMini','Minimo','DoCoMo','AvantGo','BlockBerry')));
	}
	public function isSafari() {
		return (in_array($this->getBrowserName(),array('Safari','MobileSafari')) ? true : false);
	}
	public function isIE() {
		return ($this->getBrowserName() == 'IE' ? true : false);
	}
	public function isChrome() {
		return ($this->getBrowserName() == 'Chrome' ? true : false);
	}

	public function isOpera() {
		return ($this->getBrowserName() == 'Opera' ? true : false);
	}

	function __destruct() {
		// Nothing to do: destruction of this class means the end of execution
	}
}
?>
