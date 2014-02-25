<?php
/**
 * Contains users online-related functions.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.page.user.usersOnline
 * @subpackage	util
 * @category 	Community Framework (commercial)
 */
class UsersOnlineUtil {
	/**
	 * Returns the name of the user agent icon.
	 *
	 * @param	string		$userAgentString
	 * @return	string		icon name
	 */
	public static function getUserAgentIcon($userAgentString) {
		$userAgentString = StringUtil::toLowerCase($userAgentString);
		// ie
		if (strpos($userAgentString, 'msie') !== false) {
			return 'browserInternetExplorer';
		}
		// firefox
		else if (strpos($userAgentString, 'firefox') !== false) {
			return 'browserFirefox';
		}
		// chrome
		else if (strpos($userAgentString, 'chrome') !== false) {
			return 'browserChrome';
		}
		// safari
		else if (strpos($userAgentString, 'safari') !== false) {
			return 'browserSafari';
		}
		// opera
		else if (strpos($userAgentString, 'opera') !== false) {
			return 'browserOpera';
		}
		// konqueror
		else if (strpos($userAgentString, 'konqueror') !== false) {
			return 'browserKonqueror';
		}
		// netscape
		else if (strpos($userAgentString, 'netscape') !== false) {
			return 'browserNetscape';
		}
		// webkit
		else if (strpos($userAgentString, 'webkit') !== false) {
			return 'browserSafari';
		}
		// mozilla
		else if (strpos($userAgentString, 'gecko') !== false) {
			return 'browserMozilla';
		}
		
		return '';
	}
}
?>