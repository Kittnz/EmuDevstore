<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Adds advanced user options to user search result page.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	system.event.listener
 * @category 	Community Framework
 */
class UserListPageAdvancedUserOptionsListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if ($eventName == 'assignVariables') {
			WCF::getTPL()->assign(array(
				'url' => $eventObj->url
			));
			WCF::getTPL()->append('additionalMarkedOptions', WCF::getTPL()->fetch('userListAdvancedUserOptions'));
		}
	}
}
?>