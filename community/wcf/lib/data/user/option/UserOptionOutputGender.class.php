<?php
// wcf imports
require_once(WCF_DIR.'lib/data/user/option/UserOptionOutputSelectOptions.class.php');

/**
 * UserOptionOutputGender is an implementation of UserOptionOutput for the output the gender option.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	data.user.option
 * @category 	Community Framework
 */
class UserOptionOutputGender extends UserOptionOutputSelectOptions {
	/**
	 * @see UserOptionOutput::getShortOutput()
	 */
	public function getShortOutput(User $user, $optionData, $value) {
		if ($value == 1) {
			$title = WCF::getLanguage()->getDynamicVariable('wcf.user.profile.gender.male', array('username' => $user->username));
			return '<img src="'.StyleManager::getStyle()->getIconPath('genderMaleS.png').'" alt="'.$title.'" title="'.$title.'" />';
		}
		else if ($value == 2) {
			$title = WCF::getLanguage()->getDynamicVariable('wcf.user.profile.gender.female', array('username' => $user->username));
			return '<img src="'.StyleManager::getStyle()->getIconPath('genderFemaleS.png').'" alt="'.$title.'" title="'.$title.'" />';
		}
		else {
			return '';
		}
	}
}
?>