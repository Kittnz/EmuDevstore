<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Adds advanced user options to user search result page.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class UserListPagePMOptionListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_PM == 1) {
			if ($eventName == 'assignVariables') {
				WCF::getTPL()->assign(array(
					'url' => $eventObj->url
				));
				WCF::getTPL()->append('additionalMarkedOptions', WCF::getTPL()->fetch('userListPMOption'));
			}
		}
	}
}
?>