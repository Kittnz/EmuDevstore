<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Filters the user list page and adds additional list options
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	system.event.listener
 * @category 	Community Framework
 */
class UserListPageUserListOptionsListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if ($eventName == 'readData') {
			if ($eventObj->action == 'notActivated') {
				if (!empty($eventObj->sqlConditions)) $this->sqlConditions .= ' AND ';
				$eventObj->sqlConditions .= "user_table.activationCode <> 0";
			}
			else if ($eventObj->action == 'new') {
				if (!empty($eventObj->sqlConditions)) $this->sqlConditions .= ' AND ';
				$eventObj->sqlConditions .= "user_table.registrationDate > ".(TIME_NOW - 3600 * 24);
			}
			else if ($eventObj->action == 'banned') {
				if (!empty($eventObj->sqlConditions)) $this->sqlConditions .= ' AND ';
				$eventObj->sqlConditions .= "user_table.banned = 1";
			}
		}
		if ($eventName == 'assignVariables') {
			WCF::getTPL()->assign('action', $eventObj->action);
			WCF::getTPL()->append('additionalUserListOptions', WCF::getTPL()->fetch('userSearchUserListOptions'));
		}
	}
}
?>