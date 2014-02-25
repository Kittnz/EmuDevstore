<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/acp/form/GroupEditForm.class.php');

/**
 * Adds the options for automatic joins to group add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.group
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class GroupAddFormAutoJoinListener implements EventListener {
	public $neededAge = 0;
	public $neededPoints = 0;
	
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if ($eventObj instanceof GroupEditForm && $eventObj->group->groupType < 4) {
			// a default group doesn't support automatic joins
			return;	
		}
		
		if ($eventName == 'readFormParameters') {
			if (isset($_POST['neededAge'])) $this->neededAge = intval($_POST['neededAge']);
			if (isset($_POST['neededPoints'])) $this->neededPoints = intval($_POST['neededPoints']);
		}
		else if ($eventName == 'save') {
			// save
			$eventObj->additionalFields['neededAge'] = $this->neededAge;
			$eventObj->additionalFields['neededPoints'] = $this->neededPoints;
			
			// reset values
			if (!($eventObj instanceof GroupEditForm)) {
				$this->neededAge = 0;
				$this->neededPoints = 0;
			}
		}
		else if ($eventName == 'assignVariables') {
			if (!count($_POST) && $eventObj instanceof GroupEditForm) {
				// get default values
				$this->neededAge = $eventObj->group->neededAge;
				$this->neededPoints = $eventObj->group->neededPoints;
			}
			
			// assign variables	
			WCF::getTPL()->assign(array(
				'neededAge' => $this->neededAge,
				'neededPoints' => $this->neededPoints
			));
			WCF::getTPL()->append('additionalFieldSets', WCF::getTPL()->fetch('groupAddAutoJoin'));
		}
	}
}
?>