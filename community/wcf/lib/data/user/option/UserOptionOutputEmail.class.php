<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/User.class.php');
require_once(WCF_DIR.'lib/data/user/option/UserOptionOutput.class.php');
require_once(WCF_DIR.'lib/data/user/option/UserOptionOutputContactInformation.class.php');

/**
 * UserOptionOutputEmail is an implementation of UserOptionOutput for the output of a user email.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	data.user.option
 * @category 	Community Framework
 */
class UserOptionOutputEmail implements UserOptionOutput, UserOptionOutputContactInformation {
	// UserOptionOutput implementation
	/**
	 * @see UserOptionOutput::getShortOutput()
	 */
	public function getShortOutput(User $user, $optionData, $value) {
		return $this->getImage($user, 'S');
	}
	
	/**
	 * @see UserOptionOutput::getMediumOutput()
	 */
	public function getMediumOutput(User $user, $optionData, $value) {
		return $this->getImage($user);
	}
	
	/**
	 * @see UserOptionOutput::getOutput()
	 */
	public function getOutput(User $user, $optionData, $value) {
		if (!$user->email) return '';
		if ($user->hideEmailAddress && !WCF::getUser()->getPermission('admin.user.canMailUser')) return '';
		if (!WCF::getUser()->getPermission('user.mail.canMail')) return '';
		$email = StringUtil::encodeAllChars($user->email);
		return '<a href="mailto:'.$email.'">'.$email.'</a>';
	}
	
	// UserOptionOutputContactInformation implementation
	/**
	 * @see UserOptionOutputContactInformation::getOutput()
	 */
	public function getOutputData(User $user, $optionData, $value) {
		if (!$user->email) return null;
		if (!$user->hideEmailAddress || WCF::getUser()->getPermission('admin.user.canMailUser')) {
			$email = StringUtil::encodeAllChars($user->email);
			return array(
				'icon' => StyleManager::getStyle()->getIconPath('emailM.png'),
				'title' => WCF::getLanguage()->get('wcf.user.option.'.$optionData['optionName']),
				'value' => $email,
				'url' => 'mailto:'.$email
			);
		}
		else if ($user->userCanMail && WCF::getUser()->getPermission('user.mail.canMail')) {
			return array(
				'icon' => StyleManager::getStyle()->getIconPath('emailM.png'),
				'title' => WCF::getLanguage()->get('wcf.user.option.'.$optionData['optionName']),
				'value' => WCF::getLanguage()->get('wcf.user.profile.email.title', array('$username' => StringUtil::encodeHTML($user->username))),
				'url' => 'index.php?form=Mail&amp;userID='.$user->userID.SID_ARG_2ND
			);
		}
		else {
			return null;
		}
	}
	
	/**
	 * Generates an image button.
	 * 
	 * @see UserOptionOutput::getShortOutput()
	 */
	protected function getImage(User $user, $imageSize = 'M') {
		if (!$user->email) return '';
		if (!$user->hideEmailAddress || WCF::getUser()->getPermission('admin.user.canMailUser')) {
			$url = 'mailto:'.StringUtil::encodeAllChars($user->email);
		}
		else if ($user->userCanMail && WCF::getUser()->getPermission('user.mail.canMail')) {
			$url = 'index.php?form=Mail&amp;userID='.$user->userID.SID_ARG_2ND;
		}
		else {
			return '';
		}
		
		$title = WCF::getLanguage()->get('wcf.user.profile.email.title', array('$username' => StringUtil::encodeHTML($user->username)));
		return '<a href="'.$url.'"><img src="'.StyleManager::getStyle()->getIconPath('email'.$imageSize.'.png').'" alt="" title="'.$title.'" /></a>';
	}
}
?>