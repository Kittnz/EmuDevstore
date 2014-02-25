<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/User.class.php');
require_once(WCF_DIR.'lib/data/user/option/UserOptionOutput.class.php');
require_once(WCF_DIR.'lib/data/user/option/UserOptionOutputContactInformation.class.php');

/**
 * UserOptionOutputURL is an implementation of UserOptionOutput for the output of an url.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	data.user.option
 * @category 	Community Framework
 */
class UserOptionOutputURL implements UserOptionOutput, UserOptionOutputContactInformation {
	// UserOptionOutput implementation
	/**
	 * @see UserOptionOutput::getShortOutput()
	 */
	public function getShortOutput(User $user, $optionData, $value) {
		return $this->getImage($user, $value, 'S');
	}
	
	/**
	 * @see UserOptionOutput::getMediumOutput()
	 */
	public function getMediumOutput(User $user, $optionData, $value) {
		return $this->getImage($user, $value);
	}
	
	/**
	 * @see UserOptionOutput::getOutput()
	 */
	public function getOutput(User $user, $optionData, $value) {
		if (empty($value) || $value == 'http://') return '';
		
		$value = self::getURL($value);
		$value = StringUtil::encodeHTML($value);
		return '<a href="'.$value.'">'.$value.'</a>';
	}
	
	// UserOptionOutputContactInformation implementation
	/**
	 * @see UserOptionOutputContactInformation::getOutput()
	 */
	public function getOutputData(User $user, $optionData, $value) {
		if (empty($value) || $value == 'http://') return null;
		
		$value = self::getURL($value);
		$value = StringUtil::encodeHTML($value);
		
		return array(
			'icon' => StyleManager::getStyle()->getIconPath('websiteM.png'),
			'title' => WCF::getLanguage()->get('wcf.user.option.'.$optionData['optionName']),
			'value' => $value,
			'url' => $value
		);
	}
	
	/**
	 * Generates an image button.
	 * 
	 * @see UserOptionOutput::getShortOutput()
	 */
	protected function getImage(User $user, $value, $imageSize = 'M') {
		if (empty($value) || $value == 'http://') return '';
		
		$value = self::getURL($value);
		$title = WCF::getLanguage()->get('wcf.user.profile.homepage.title', array('$username' => StringUtil::encodeHTML($user->username)));
		return '<a href="'.StringUtil::encodeHTML($value).'"><img src="'.StyleManager::getStyle()->getIconPath('website'.$imageSize.'.png').'" alt="" title="'.$title.'" /></a>';
	}
	
	/**
	 * Formats the URL.
	 * 
	 * @param	string		$url
	 * @return	string
	 */
	private static function getURL($url) {
		if (!preg_match('~^https?://~i', $url)) {
			$url = 'http://'.$url;
		}
		
		return $url;
	}
}
?>