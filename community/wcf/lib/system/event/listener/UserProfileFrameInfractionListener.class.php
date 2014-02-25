<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Removes the warning tab.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.user.infraction
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class UserProfileFrameInfractionListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_USER_INFRACTION == 1) {
			if ($eventName == 'init') {
				if (WCF::getUser()->getPermission('admin.user.infraction.canWarnUser') || (USER_CAN_SEE_HIS_WARNINGS && WCF::getUser()->userID == $eventObj->userID)) {
					$eventObj->sqlSelects .= "(SELECT COUNT(*) FROM wcf".WCF_N."_user_infraction_warning_to_user WHERE userID = ".$eventObj->userID.") AS warnings,";
				}
			}
			else if ($eventName == 'assignVariables') {
				if (!$eventObj->getUser()->warnings) {
					// remove warning overview tab
					foreach (UserProfileMenu::getInstance()->menuItems as $parentMenuItem => $items) {
						foreach ($items as $key => $item) {
							if ($item['menuItem'] == 'wcf.user.profile.menu.link.infraction') {
								unset(UserProfileMenu::getInstance()->menuItems[$parentMenuItem][$key]);
							}
						}
					}
				}
				
				// add warn button
				if (WCF::getUser()->getPermission('admin.user.infraction.canWarnUser')) {
					WCF::getTPL()->append('additionalAdminOptions', '<li><a href="index.php?form=UserWarn&amp;userID='.$eventObj->userID.SID_ARG_2ND.'">'.WCF::getLanguage()->get('wcf.user.infraction.button.warn').'</a></li>');
				}
			}
		}
	}
}
?>