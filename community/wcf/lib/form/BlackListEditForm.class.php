<?php
// wcf imports
require_once(WCF_DIR.'lib/form/AbstractUserListEditForm.class.php');

/**
 * Shows the black list edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	form
 * @category 	Community Framework
 */
class BlackListEditForm extends AbstractUserListEditForm {
	public $listType = 'black';
	public $templateName = 'blackListEdit';
	
	/**
	 * @see WhiteListEditForm::validateUser()
	 */
	protected function validateUser(UserSession $user) {
		parent::validateUser($user);
		
		if ($user->getPermission('user.profile.blacklist.canNotBeIgnored')) {
			throw new UserInputException('username', 'canNotIgnore');
		}
		
		// friends cannot be ignored 
		$sql = "SELECT	whiteUserID
			FROM	wcf".WCF_N."_user_whitelist
			WHERE	userID = ".WCF::getUser()->userID."
				AND whiteUserID = ".$user->userID;
		$row = WCF::getDB()->getFirstRow($sql);
		if (!empty($row['whiteUserID'])) {
			throw new UserInputException('username', 'canNotIgnore');
		}
	}
}
?>