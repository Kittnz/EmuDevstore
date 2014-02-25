<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/acp/form/GroupEditForm.class.php');

/**
 * Shows the team tab on members list page.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.page.user.membersList.team
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class GroupAddFormShowOnTeamPageListener implements EventListener {
	protected $showOnTeamPage = 0;
	protected $teamPagePosition = 0;
	
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_TEAM_LIST) {
			if ($eventName == 'readFormParameters') {
				if (isset($_POST['showOnTeamPage'])) $this->showOnTeamPage = intval($_POST['showOnTeamPage']);
				if (isset($_POST['teamPagePosition'])) $this->teamPagePosition = intval($_POST['teamPagePosition']);
			}
			else if ($eventName == 'save') {
				$eventObj->additionalFields['showOnTeamPage'] = $this->showOnTeamPage;
				$eventObj->additionalFields['teamPagePosition'] = $this->teamPagePosition;
				if (!($eventObj instanceof GroupEditForm)) {
					$this->showOnTeamPage = $this->teamPagePosition = 0;
				}
				
				// clear cache
				WCF::getCache()->clear(WCF_DIR.'cache', 'cache.teamCount.php');
			}
			else if ($eventName == 'assignVariables') {
				if (!count($_POST) && $eventObj instanceof GroupEditForm) {
					$this->showOnTeamPage = $eventObj->group->showOnTeamPage;
					$this->teamPagePosition = $eventObj->group->teamPagePosition;
				}
				WCF::getTPL()->assign(array(
					'showOnTeamPage' => $this->showOnTeamPage,
					'teamPagePosition' => $this->teamPagePosition
				));
				WCF::getTPL()->append('additionalFields', WCF::getTPL()->fetch('groupAddShowOnTeamPage'));
			}
		}
	}
}
?>