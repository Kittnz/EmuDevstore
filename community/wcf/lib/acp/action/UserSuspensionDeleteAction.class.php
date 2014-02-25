<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/suspension/UserSuspensionEditor.class.php');
require_once(WCF_DIR.'lib/data/user/infraction/suspension/Suspension.class.php');

/**
 * Deletes a user suspension.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class UserSuspensionDeleteAction extends AbstractAction {
	/**
	 * user suspension id
	 *
	 * @var	integer
	 */
	public $userSuspensionID = 0;
	
	/**
	 * user suspension editor object
	 *
	 * @var	UserSuspensionEditor
	 */
	public $userSuspension;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['userSuspensionID'])) $this->userSuspensionID = intval($_REQUEST['userSuspensionID']);
		$this->userSuspension = new UserSuspensionEditor($this->userSuspensionID);
		if (!$this->userSuspension->userSuspensionID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.user.infraction.canDeleteSuspension');
		
		// revoke suspension
		if (!$this->userSuspension->revoked) {
			$object = Suspension::getSuspensionTypeObject($this->userSuspension->suspensionType);
			$object->revoke(new User($this->userSuspension->userID), $this->userSuspension, new Suspension($this->userSuspension->suspensionID));
			Session::resetSessions($this->userSuspension->userID);
		}
		
		// delete suspension
		$this->userSuspension->delete();
		$this->executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=UserSuspensionList&deletedUserSuspensionID='.$this->userSuspensionID.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>