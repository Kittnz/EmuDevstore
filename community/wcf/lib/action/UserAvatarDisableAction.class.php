<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractUserAvatarAction.class.php');

/**
 * Disables the user avatar. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	action
 * @category 	Community Framework
 */
class UserAvatarDisableAction extends AbstractUserAvatarAction {
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if ($this->user->disableAvatar == 1) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// disable avatar
		$this->user->updateFields(array('disableAvatar' => 1));
		$this->executed();
		
		// forward
		HeaderUtil::redirect('index.php?page=User&userID='.$this->userID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>