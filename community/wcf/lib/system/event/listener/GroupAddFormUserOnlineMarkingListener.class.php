<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/acp/form/GroupEditForm.class.php');

/**
 * Adds the online marking select to group add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.page.user.usersOnline
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class GroupAddFormUserOnlineMarkingListener implements EventListener {
	protected $userOnlineMarking = '%s';
	
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_USERS_ONLINE == 1) {
			if ($eventName == 'readFormParameters') {
				if (isset($_POST['userOnlineMarking'])) $this->userOnlineMarking = $_POST['userOnlineMarking'];
			}
			else if ($eventName == 'validate') {
				try {
					if (strpos($this->userOnlineMarking, '%s') === false || substr_count($this->userOnlineMarking, '%s') > 1) {
						throw new UserInputException('userOnlineMarking');
					}
				}
				catch (UserInputException $e) {
					$eventObj->errorType[$e->getField()] = $e->getType();
				}
			}
			else if ($eventName == 'save') {
				$eventObj->additionalFields['userOnlineMarking'] = $this->userOnlineMarking;
				if (!($eventObj instanceof GroupEditForm)) $this->userOnlineMarking = '%s';
			}
			else if ($eventName == 'assignVariables') {
				if (!count($_POST) && $eventObj instanceof GroupEditForm) $this->userOnlineMarking = $eventObj->group->userOnlineMarking;
				WCF::getTPL()->assign('userOnlineMarking', $this->userOnlineMarking);
				WCF::getTPL()->append('additionalFields', WCF::getTPL()->fetch('groupAddUserOnlineMarking'));
			}
		}
	}
}
?>