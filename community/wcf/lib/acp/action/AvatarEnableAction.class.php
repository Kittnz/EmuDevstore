<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Enables an avatar.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.avatar
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class AvatarEnableAction extends AbstractAction {
	public $userID = 0;
	public $pageNo = 1;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['userID'])) $this->userID = intval($_REQUEST['userID']);
		if (isset($_REQUEST['pageNo'])) $this->pageNo = intval($_REQUEST['pageNo']);
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
				
		// check permission
		WCF::getUser()->checkPermission('admin.avatar.canDisableAvatar');
		
		// enable avatar
		require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
		$user = new UserEditor($this->userID);	
		if (!$user->userID) {
			throw new IllegalLinkException();
		}
		
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	disableAvatar = 0
			WHERE	userID = ".$this->userID;
		WCF::getDB()->sendQuery($sql);
		
		// reset session
		Session::resetSessions($this->userID, true, false);
		$this->executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=AvatarList&type=1&pageNo='.$this->pageNo.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>