<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');

/**
 * Provides default implementations for avatar actions. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	action
 * @category 	Community Framework
 */
class AbstractUserAvatarAction extends AbstractSecureAction {
	/**
	 * user id
	 *
	 * @var	integer
	 */
	public $userID = 0;
	
	/**
	 * user editor object
	 *
	 * @var	UserEditor
	 */
	public $user = null;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// check module
		if (MODULE_AVATAR != 1) {
			throw new IllegalLinkException();
		}
		
		// check permission
		WCF::getUser()->checkPermission('admin.user.canEditUser');
		
		// get user
		if (isset($_REQUEST['userID'])) $this->userID = intval($_REQUEST['userID']);
		$this->user = new UserEditor($this->userID);
		if (!$this->user->userID) {
			throw new IllegalLinkException();
		}
	}
}
?>