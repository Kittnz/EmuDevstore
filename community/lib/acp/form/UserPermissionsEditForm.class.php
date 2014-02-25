<?php
// wbb imports
require_once(WBB_DIR.'lib/data/board/Board.class.php');
require_once(WBB_DIR.'lib/acp/form/GroupPermissionsEditForm.class.php');

/**
 * Shows the user to boards permissions list.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.form
 * @category 	Burning Board
 */
class UserPermissionsEditForm extends GroupPermissionsEditForm {
	// system
	public $activeMenuItem = 'wcf.acp.menu.link.user.management';
	public $neededPermissions = 'admin.board.canEditPermissions';
	
	/**
	 * user object
	 * 
	 * @var	User
	 */
	public $user = null;
	
	/**
	 * user id
	 * 
	 * @var	integer
	 */
	public $userID = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		AbstractForm::readParameters();
		
		// get user
		if (isset($_REQUEST['userID'])) {
			$this->userID = intval($_REQUEST['userID']);
			require_once(WBB_DIR.'lib/data/user/AbstractWBBUserSession.class.php');
			$this->user = new AbstractWBBUserSession($this->userID);
			if (!$this->user->userID) {
				throw new IllegalLinkException();
			}
			require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
			if (!Group::isAccessibleGroup($this->user->getGroupIDs())) {
				throw new PermissionDeniedException();
			}
		}
		
		// active permission
		if (isset($_REQUEST['permissionName'])) $this->permissionName = $_REQUEST['permissionName'];
		
		$this->readPermissionSettings();
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		$inserts = $fields = '';
		foreach ($this->permissionSettings as $name) {
			$fields .= ', '.$name;
		}
		
		foreach ($this->boardPermissions as $boardID => $permissions) {
			$noDefaultValue = false;
			foreach ($permissions as $value) {
				if ($value != -1) $noDefaultValue = true;
			}
			
			if (!$noDefaultValue) continue;
			
			if (!empty($inserts)) $inserts .= ',';
			$inserts .= '('.intval($boardID).', '.$this->userID;
			foreach ($this->permissionSettings as $name) {
				$inserts .= ', '.(isset($permissions[$name]) ? $permissions[$name] : -1);
			}
			$inserts .= ')';
		}
		
		// delete old entries
		$sql = "DELETE FROM	wbb".WBB_N."_board_to_user
			WHERE		userID = ".$this->userID;
		WCF::getDB()->sendQuery($sql);
			
		if (!empty($inserts)) {
			$sql = "INSERT IGNORE INTO	wbb".WBB_N."_board_to_user
							(boardID, userID".$fields.")
				VALUES			".$inserts;
			WCF::getDB()->sendQuery($sql);
		}
		
		// reset sessions
		Session::resetSessions(array(), true, false);
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'userID' => $this->userID,
			'user' => $this->user,
			'type' => 'user'
		));
	}
	
	/**
	 * Gets a list of board permissions.
	 */
	protected function readBoardPermissions() {
		$sql = "SELECT		*
			FROM		wbb".WBB_N."_board_to_user
			WHERE		userID = ".$this->userID."
			ORDER BY	boardID";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$boardID = $row['boardID'];
			unset($row['boardID'], $row['userID']);
			$this->activeBoardPermissions[$boardID] = $row;
		}
	}
	
	/**
	 * Gets available permission settings.
	 */
	protected function readPermissionSettings() {
		$sql = "SHOW COLUMNS FROM wbb".WBB_N."_board_to_user";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if ($row['Field'] != 'boardID' && $row['Field'] != 'userID') {
				// check modules
				switch ($row['Field']) {
					case 'canMarkAsDoneOwnThread': 
						if (!MODULE_THREAD_MARKING_AS_DONE) continue 2;
						break;
					
					case 'canSetTags':
						if (!MODULE_TAGGING) continue 2;
						break;
					
					case 'canUploadAttachment':
					case 'canDownloadAttachment':
					case 'canViewAttachmentPreview': 
						if (!MODULE_ATTACHMENT) continue 2;
						break;
					
					case 'canStartPoll':
					case 'canVotePoll': 
						if (!MODULE_POLL) continue 2;
						break;
				}
				
				$this->permissionSettings[] = $row['Field'];
			}
		}
	}
	
	/**
	 * @see GroupPermissionsEditForm::loadGlobalPermissions()
	 */
	protected function loadGlobalPermissions() {
		$boards = WCF::getCache()->get('board', 'boards');
		foreach ($boards as $board) {
			$this->globalPermissions[$board->boardID] = array(
				'canViewBoard' => intval($this->user->getBoardPermission('canViewBoard', $board->boardID)),
				'canEnterBoard' => intval($this->user->getBoardPermission('canEnterBoard', $board->boardID)),
				'canReadThread' => intval($this->user->getBoardPermission('canReadThread', $board->boardID)),
				'canReadOwnThread' => intval($this->user->getBoardPermission('canReadOwnThread', $board->boardID)),
				'canStartThread' => intval($this->user->getBoardPermission('canStartThread', $board->boardID)),
				'canReplyThread' => intval($this->user->getBoardPermission('canReplyThread', $board->boardID)),
				'canReplyOwnThread' => intval($this->user->getBoardPermission('canReplyOwnThread', $board->boardID)),
				'canStartThreadWithoutModeration' => intval($this->user->getBoardPermission('canStartThreadWithoutModeration', $board->boardID)),
				'canReplyThreadWithoutModeration' => intval($this->user->getBoardPermission('canReplyThreadWithoutModeration', $board->boardID)),
				'canRateThread' => intval($this->user->getBoardPermission('canRateThread', $board->boardID)),
				'canUsePrefix' => intval($this->user->getBoardPermission('canUsePrefix', $board->boardID)),
				'canDeleteOwnPost' => intval($this->user->getBoardPermission('canDeleteOwnPost', $board->boardID)),
				'canEditOwnPost' => intval($this->user->getBoardPermission('canEditOwnPost', $board->boardID))
			);
			
			if (MODULE_THREAD_MARKING_AS_DONE) {
				$this->globalPermissions[$board->boardID]['canMarkAsDoneOwnThread'] = intval($this->user->getBoardPermission('canMarkAsDoneOwnThread', $board->boardID));
			}
			
			if (MODULE_TAGGING) {
				$this->globalPermissions[$board->boardID]['canSetTags'] = intval($this->user->getBoardPermission('canSetTags', $board->boardID));
			}
			
			if (MODULE_ATTACHMENT) {
				$this->globalPermissions[$board->boardID]['canUploadAttachment'] = intval($this->user->getBoardPermission('canUploadAttachment', $board->boardID));
				$this->globalPermissions[$board->boardID]['canDownloadAttachment'] = intval($this->user->getBoardPermission('canDownloadAttachment', $board->boardID));
				$this->globalPermissions[$board->boardID]['canViewAttachmentPreview'] = intval($this->user->getBoardPermission('canViewAttachmentPreview', $board->boardID));
			}
			
			if (MODULE_POLL) {
				$this->globalPermissions[$board->boardID]['canStartPoll'] = intval($this->user->getBoardPermission('canStartPoll', $board->boardID));
				$this->globalPermissions[$board->boardID]['canVotePoll'] = intval($this->user->getBoardPermission('canVotePoll', $board->boardID));
			}
		}
	}
}
?>