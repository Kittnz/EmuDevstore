<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Shows the private message link in user profiles.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class UserPagePMLinkListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_PM == 1) {
			if (WCF::getUser()->userID) {
				$user = $eventObj->getUser();
				// check user otpions and permissions
				if (	WCF::getUser()->userID != $user->userID &&
					$user->getPermission('user.pm.canUsePm') &&
					$user->acceptPm &&
					!$user->ignoredUser &&
					(!$user->onlyBuddyCanPm || $user->buddy) &&
					$user->pmTotalCount < $user->getPermission('user.pm.maxPm')) {
					WCF::getTPL()->append('additionalUserCardOptions', '<li><a href="index.php?form=PMNew&amp;userID='.$user->userID.SID_ARG_2ND.'"><img src="'.StyleManager::getStyle()->getIconPath('pmM.png').'" alt="" /> <span>'.WCF::getLanguage()->get('wcf.pm.profile.sendPM').'</span></a></li>');
				}
			}
		}
	}
}
?>