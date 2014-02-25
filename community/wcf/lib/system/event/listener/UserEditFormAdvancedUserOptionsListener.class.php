<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Adds advanced user options to user edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	system.event.listener
 * @category 	Community Framework
 */
class UserEditFormAdvancedUserOptionsListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if ($eventName == 'assignVariables') {
			WCF::getTPL()->assign(array(
				'user' => $eventObj->user,
				'url' => $eventObj->url
			));
			WCF::getTPL()->append('additionalUserOptions', WCF::getTPL()->fetch('userEditAdvancedUserOptions'));
		}
	}
}
?>