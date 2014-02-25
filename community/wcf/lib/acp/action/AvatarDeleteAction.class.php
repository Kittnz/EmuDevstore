<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Deletes an avatar.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.avatar
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class AvatarDeleteAction extends AbstractAction {
	public $avatarID = 0;
	public $pageNo = 1;
	public $type = 0;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['avatarID'])) $this->avatarID = intval($_REQUEST['avatarID']);
		if (isset($_REQUEST['pageNo'])) $this->pageNo = intval($_REQUEST['pageNo']);
		if (isset($_REQUEST['type'])) $this->type = intval($_REQUEST['type']);
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.avatar.canDeleteAvatar');
		
		// delete avatar
		require_once(WCF_DIR.'lib/data/user/avatar/AvatarEditor.class.php');
		$avatar = new AvatarEditor($this->avatarID);	
		if (!$avatar->avatarID) {
			throw new IllegalLinkException();
		}
		$avatar->delete();
		if (!$avatar->userID) {
			$sql = "UPDATE	wcf".WCF_N."_user
				SET	avatarID = 0
				WHERE	avatarID = ".$this->avatarID;
			WCF::getDB()->sendQuery($sql);
		}
		$this->executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=AvatarList&deletedAvatarID='.$this->avatarID.'&type='.$this->type.'&pageNo='.$this->pageNo.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>