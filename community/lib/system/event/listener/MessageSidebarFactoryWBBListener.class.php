<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Shows board functions in the message sidebar.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.event.listener
 * @category 	Burning Board
 */
class MessageSidebarFactoryWBBListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		// ip address
		if (WCF::getUser()->getPermission('admin.general.canViewIpAddress') && $eventObj->container instanceof ThreadPage) {
			foreach ($eventObj->messageSidebars as $id => $sidebar) {
				if ($sidebar->getSidebarObject()->ipAddress) {
					$title = WCF::getLanguage()->getDynamicVariable('wbb.thread.ipAddress', array(
						'username' => $sidebar->getUser()->username,
						'ipAddress' => $sidebar->getSidebarObject()->ipAddress
					));
					$sidebar->addUserContact('<a href="index.php?page=IpAddress&amp;postID='.$sidebar->getSidebarObject()->postID.SID_ARG_2ND.'"><img src="'.StyleManager::getStyle()->getIconPath('ipAddressS.png').'" alt="'.$title.'" title="'.$title.'" /></a>');
				}
			}
		}
		
		// thread starter icon
		if (MESSAGE_SIDEBAR_ENABLE_THREAD_STARTER_ICON == 1 && $eventObj->container instanceof ThreadPage && $eventObj->container->thread->userID != 0) {
			foreach ($eventObj->messageSidebars as $id => $sidebar) {
				if ($eventObj->container->thread->userID == $sidebar->getUser()->userID) {
					$title = WCF::getLanguage()->getDynamicVariable('wbb.thread.starter', array(
						'username' => $sidebar->getUser()->username
					));
					$sidebar->addUserSymbol('<img src="'.StyleManager::getStyle()->getIconPath('threadStarterS.png').'" alt="'.$title.'" title="'.$title.'" />');
				}
			}
		}
		
		// post count
		if (MESSAGE_SIDEBAR_ENABLE_USER_POSTS == 1) {
			foreach ($eventObj->messageSidebars as $id => $sidebar) {
				if ($sidebar->getUser()->userID != 0 && $sidebar->getSidebarObject()->posts !== null) {
					$sidebar->userCredits = array_merge(array(array('name' => WCF::getLanguage()->get('wcf.user.posts'), 'value' => StringUtil::formatInteger($sidebar->getSidebarObject()->posts), 'url' => 'index.php?form=Search&amp;types[]=post&amp;userID='.$sidebar->getUser()->userID.SID_ARG_2ND)), $sidebar->userCredits);
				}
			}
		}
	}
}
?>