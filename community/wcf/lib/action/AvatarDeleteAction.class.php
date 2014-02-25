<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/AvatarEditor.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/Gravatar.class.php');

/**
 * Deletes an avatar of a user
 * 
 * @author	Arian Glander
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	action
 * @category 	Community Framework
 */
class AvatarDeleteAction extends AbstractSecureAction {
	/**
	 * Avatar
	 *
	 * @var AvatarEditor
	 */
	public $avatar;
	/**
	 * Type of avatar
	 *
	 * @var string
	 */
	public $avatarType = 'none';
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (WCF::getUser()->avatarID) {
			$this->avatar = new AvatarEditor(WCF::getUser()->avatarID);
			$this->avatarType = ($this->avatar->userID ? 'user' : 'selected');
		}
		else if (MODULE_GRAVATAR == 1 && WCF::getUser()->gravatar) {
			$this->avatar = new Gravatar(WCF::getUser()->gravatar);
			$this->avatarType = 'gravatar';
		}
		
		if (!WCF::getUser()->userID || WCF::getUser()->disableAvatar) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// delete old user avatar if necessary
		if ($this->avatarType == 'user') {
			$this->avatar->delete();
			$this->avatar = null;
		}
		
		// update user
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	avatarID = 0,
				gravatar = ''
			WHERE	userID = ".WCF::getUser()->userID;
		WCF::getDB()->sendQuery($sql);
		
		// reset session
		WCF::getSession()->resetUserData();
		
		// forward
		if (empty($_REQUEST['ajax'])) {
			HeaderUtil::redirect('index.php?form=AvatarEdit' . SID_ARG_2ND_NOT_ENCODED);
			exit;
		}
	}
}
?>