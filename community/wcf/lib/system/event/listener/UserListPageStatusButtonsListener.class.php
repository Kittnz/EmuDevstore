<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Shows the permissions edit button in user search result list.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	system.event.listener
 * @category 	Community Framework
 */
class UserListPageStatusButtonsListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		$canBanUser = WCF::getUser()->getPermission('admin.user.canBanUser');
		$canEnableUser = WCF::getUser()->getPermission('admin.user.canEnableUser');
		$url = rawurlencode($eventObj->url);
		$additionalButtons = array();
		
		foreach ($eventObj->users as $key => $user) {
			$additionalButtons[$user->userID] = '';
			
			if ($canEnableUser && $user->accessible && $user->userID != WCF::getUser()->userID) {
				if ($user->activationCode == 0) {
					$additionalButtons[$user->userID] .= ' <a href="index.php?action=UserDisable&amp;userID='.$user->userID.'&amp;url='.$url.'&amp;packageID='.PACKAGE_ID.SID_ARG_2ND.'"><img src="'.RELATIVE_WCF_DIR.'icon/enabledS.png" alt="" title="'.WCF::getLanguage()->get('wcf.acp.user.button.disable').'" /></a>';
				}
				else {
					$additionalButtons[$user->userID] .= ' <a href="index.php?action=UserEnable&amp;userID='.$user->userID.'&amp;url='.$url.'&amp;packageID='.PACKAGE_ID.SID_ARG_2ND.'"><img src="'.RELATIVE_WCF_DIR.'icon/disabledS.png" alt="" title="'.WCF::getLanguage()->get('wcf.acp.user.button.enable').'" /></a>';
				}
			}
			else {
				if ($user->activationCode == 0) {
					$additionalButtons[$user->userID] .= ' <img src="'.RELATIVE_WCF_DIR.'icon/enabledDisabledS.png" alt="" title="'.WCF::getLanguage()->get('wcf.acp.user.button.disable').'" />';
				}
				else {
					$additionalButtons[$user->userID] .= ' <img src="'.RELATIVE_WCF_DIR.'icon/disabledDisabledS.png" alt="" title="'.WCF::getLanguage()->get('wcf.acp.user.button.enable').'" />';
				}
			}
			
			if ($canBanUser && $user->accessible && $user->userID != WCF::getUser()->userID) {
				if ($user->banned == 0) {
					$additionalButtons[$user->userID] .= ' <a href="index.php?form=UserBan&amp;userID='.$user->userID.'&amp;url='.$url.'&amp;packageID='.PACKAGE_ID.SID_ARG_2ND.'"><img src="'.RELATIVE_WCF_DIR.'icon/userBanS.png" alt="" title="'.WCF::getLanguage()->get('wcf.acp.user.button.ban').'" /></a>';
				}
				else {
					$additionalButtons[$user->userID] .= ' <a href="index.php?action=UserUnban&amp;userID='.$user->userID.'&amp;url='.$url.'&amp;packageID='.PACKAGE_ID.SID_ARG_2ND.'"><img src="'.RELATIVE_WCF_DIR.'icon/userUnbanS.png" alt="" title="'.WCF::getLanguage()->get('wcf.acp.user.button.unban').'" /></a>';
				}
			}
			else {
				$additionalButtons[$user->userID] .= ' <img src="'.RELATIVE_WCF_DIR.'icon/userBanDisabledS.png" alt="" title="'.WCF::getLanguage()->get('wcf.acp.user.button.ban').'" />';
			}
		}
		
		WCF::getTPL()->append('additionalButtons', $additionalButtons);
	}
}
?>