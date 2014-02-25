<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Shows pm functions in the message sidebar.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class MessageSidebarFactoryPMListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		// pm link
		if (MODULE_PM) {
			foreach ($eventObj->messageSidebars as $id => $sidebar) {
				if ($sidebar->getUser()->userID) {
					$sidebar->addUserContact('<a href="index.php?form=PMNew&amp;userID='.$sidebar->getUser()->userID.SID_ARG_2ND.'"><img src="'.StyleManager::getStyle()->getIconPath('pmEmptyS.png').'" alt="'.WCF::getLanguage()->get('wcf.pm.profile.sendPM').'" title="'.WCF::getLanguage()->get('wcf.pm.profile.sendPM').'" /></a>');
				}
			}
		}
	}
}
?>