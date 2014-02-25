<?php
/**
 * Contains message-related functions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.message
 * @subpackage	util
 * @category 	Community Framework
 */
class MessageUtil {
	/**
	 * Strips session links, html entities and \r\n from the given text.
	 * 
	 * @param	string		$text
	 * @return	string
	 */
	public static function stripCrap($text) {
		// strip session links
		$text = preg_replace('/(?<=\?|&)s=[a-z0-9]{40}/', '', $text);	
		
		// convert html entities (utf-8)
		$text = preg_replace_callback('/&#(3[2-9]|[4-9][0-9]|\d{3,5});/', array('MessageUtil', 'stripCrapCallback'), $text);
		
		// remove \r\n
		$text = StringUtil::unifyNewlines($text);
		
		return $text;
	}
	
	private static function stripCrapCallback($match) {
		return StringUtil::getCharacter(intval($match[1]));
	}
}
?>