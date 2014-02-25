<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Shows the permissions edit button in user search result list.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.event.listener
 * @category 	Burning Board
 */
class UserListPagePermissionsButtonListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		$additionalButtons = array();
		
		foreach ($eventObj->users as $key => $user) {
			$additionalButtons[$user->userID] = ''; 
			
			// user permissions
			if ($user->editable && WCF::getUser()->getPermission('admin.board.canEditPermissions')) {
				$additionalButtons[$user->userID] .= ' <a href="index.php?form=UserPermissionsEdit&amp;userID='.$user->userID.'&amp;packageID='.PACKAGE_ID.SID_ARG_2ND.'"><img src="'.RELATIVE_WBB_DIR.'icon/permissionsS.png" alt="" title="'.WCF::getLanguage()->get('wbb.acp.board.user.permissions.edit').'" /></a>';
			}
			else {
				$additionalButtons[$user->userID] .= ' <img src="'.RELATIVE_WBB_DIR.'icon/permissionsDisabledS.png" alt="" title="'.WCF::getLanguage()->get('wbb.acp.board.user.permissions.edit').'" />';
			}
			
			// moderator permissions
			if ($user->editable && WCF::getUser()->getPermission('admin.board.canEditModerators')) {
				$additionalButtons[$user->userID] .= ' <a href="index.php?form=ModeratorPermissionsEdit&amp;userID='.$user->userID.'&amp;packageID='.PACKAGE_ID.SID_ARG_2ND.'"><img src="'.RELATIVE_WBB_DIR.'icon/moderatorPermissionsS.png" alt="" title="'.WCF::getLanguage()->get('wbb.acp.board.moderator.permissions.edit').'" /></a>';
			}
			else {
				$additionalButtons[$user->userID] .= ' <img src="'.RELATIVE_WBB_DIR.'icon/moderatorPermissionsDisabledS.png" alt="" title="'.WCF::getLanguage()->get('wbb.acp.board.moderator.permissions.edit').'" />';
			}
		}
		
		WCF::getTPL()->append('additionalButtons', $additionalButtons);
	}
}
?>