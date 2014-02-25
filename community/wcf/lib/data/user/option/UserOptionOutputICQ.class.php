<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/User.class.php');
require_once(WCF_DIR.'lib/data/user/option/UserOptionOutput.class.php');
require_once(WCF_DIR.'lib/data/user/option/UserOptionOutputContactInformation.class.php');

/**
 * UserOptionOutputICQ is an implementation of UserOptionOutput for the output of an icq uin.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.user.messenger
 * @subpackage	data.user.option
 * @category 	Community Framework (commercial)
 */
class UserOptionOutputICQ implements UserOptionOutput, UserOptionOutputContactInformation {
	protected $type = 'icq';
	
	// UserOptionOutput implementation
	/**
	 * @see UserOptionOutput::getShortOutput()
	 */
	public function getShortOutput(User $user, $optionData, $value) {
		if (empty($value)) return '';
		return $this->getLink($user, $this->getImage($user, 'S'));
	}

	/**
	 * @see UserOptionOutput::getMediumOutput()
	 */
	public function getMediumOutput(User $user, $optionData, $value) {
		if (empty($value)) return '';
		return $this->getLink($user, $this->getImage($user));
	}
	
	/**
	 * @see UserOptionOutput::getOutput()
	 */
	public function getOutput(User $user, $optionData, $value) {
		if (empty($value)) return '';
		return $this->getImage($user) . ' ' . $this->getLink($user, StringUtil::encodeHTML($value));
	}
	
	// UserOptionContactInformation implementation
	/**
	 * @see UserOptionContactInformation::getOutput()
	 */
	public function getOutputData(User $user, $optionData, $value) {
		if (empty($value)) return null;
		
		return array(
			'icon' => StyleManager::getStyle()->getIconPath($this->type.'M.png'),
			'title' => WCF::getLanguage()->get('wcf.user.option.'.$optionData['optionName']),
			'value' => StringUtil::encodeHTML($value),
			'url' => 'index.php?page=Messenger&amp;userID='.$user->userID.'&amp;action='.$this->type.SID_ARG_2ND.'" onclick="return !window.open(this.href, \'icq\', \'width=350,height=400,scrollbars=yes,resizable=yes\')'
		);
	}
	
	/**
	 * Returns the icon html code.
	 * 
	 * @return	string 
	 */
	protected function getImage(User $user, $imageSize = 'M') {
		$title = WCF::getLanguage()->get('wcf.user.profile.'.$this->type.'.title', array('$username' => StringUtil::encodeHTML($user->username)));
		if (class_exists('StyleManager')) return '<img src="'.StyleManager::getStyle()->getIconPath($this->type.$imageSize.'.png').'" alt="" title="'.$title.'" />';
		return '<img src="'.RELATIVE_WCF_DIR.'icon/'.$this->type.$imageSize.'.png'.'" alt="" title="'.$title.'" />';
	}
	
	/**
	 * Returns the link html code.
	 * 
	 * @return	string 
	 */
	protected function getLink(User $user, $title) {
		return '<a href="index.php?page=Messenger&amp;userID='.$user->userID.'&amp;action='.$this->type.SID_ARG_2ND.'" onclick="return !window.open(this.href, \'icq\', \'width=350,height=400,scrollbars=yes,resizable=yes\')">'.$title.'</a>';
	}
}
?>