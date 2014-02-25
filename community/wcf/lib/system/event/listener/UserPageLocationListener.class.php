<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/data/user/usersOnline/UsersOnlineLocation.class.php');

/**
 * Shows the current location of a user on profile page.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.page.user.usersOnline
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class UserPageLocationListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_USERS_ONLINE == 1) {
			if ($eventObj->frame->getUser()->isOnline()) {
				$data = array('userID' => $eventObj->frame->getUser()->userID, 'requestURI' => $eventObj->frame->getUser()->requestURI, 'requestMethod' => $eventObj->frame->getUser()->requestMethod);
				$location = new UsersOnlineLocation();
				$location->cacheLocation($data);
				$userLocation = $location->getLocation($data);
				
				if (!empty($userLocation)) {
					$eventObj->generalInformation[] = array('icon' => StyleManager::getStyle()->getIconPath('onlineM.png'), 'title' => WCF::getLanguage()->get('wcf.user.profile.currentLocation'), 'value' => $userLocation);
				}
				
				// show ip address and user agent
				if (WCF::getUser()->getPermission('admin.general.canViewIpAddress')) {
					if ($eventObj->frame->getUser()->ipAddress) $eventObj->generalInformation[] = array('icon' => StyleManager::getStyle()->getIconPath('ipAddressM.png'), 'title' => WCF::getLanguage()->get('wcf.usersOnline.ipAddress'), 'value' => StringUtil::encodeHTML($eventObj->frame->getUser()->ipAddress));
					if ($eventObj->frame->getUser()->userAgent) {
						$icon = UsersOnlineUtil::getUserAgentIcon($eventObj->frame->getUser()->userAgent);
						$eventObj->generalInformation[] = array('icon' => ($icon ? StyleManager::getStyle()->getIconPath('browsers/'.$icon.'M.png') : ''), 'title' => WCF::getLanguage()->get('wcf.usersOnline.userAgent'), 'value' => StringUtil::encodeHTML($eventObj->frame->getUser()->userAgent));
					}
				}
			}
		}
	}
}
?>