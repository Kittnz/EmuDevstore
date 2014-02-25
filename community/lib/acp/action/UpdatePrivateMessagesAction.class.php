<?php
// wbb imports
require_once(WBB_DIR.'lib/acp/action/UpdateCounterAction.class.php');

/**
 * Updates the private messages.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	acp.action
 * @category 	Burning Board
 */
class UpdatePrivateMessagesAction extends UpdateCounterAction {
	public $action = 'UpdatePrivateMessages';
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// count message
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_pm";
		$row = WCF::getDB()->getFirstRow($sql);
		$count = $row['count'];
		
		// get message ids
		$pmIDArray = array();
		$sql = "SELECT		pmID
			FROM		wcf".WCF_N."_pm
			ORDER BY	pmID";
		$result = WCF::getDB()->sendQuery($sql, $this->limit, ($this->limit * $this->loop));
		while ($row = WCF::getDB()->fetchArray($result)) {
			$pmIDArray[] = $row['pmID'];
		}
		
		if (!count($pmIDArray)) {
			$this->calcProgress();
			$this->finish();
		}
		
		// reset attachment status
		$sql = "UPDATE	wcf".WCF_N."_pm
			SET	attachments = 0
			WHERE	pmID IN (".implode(',', $pmIDArray).")";
		WCF::getDB()->sendQuery($sql);
		
		// update attachment status
		$sql = "SELECT		COUNT(*) AS count, containerID
			FROM		wcf".WCF_N."_attachment
			WHERE		packageID IN (
						SELECT	dependency
						FROM	wcf".WCF_N."_package_dependency
						WHERE	packageID = ".PACKAGE_ID."
					)
					AND containerID IN (".implode(',', $pmIDArray).")
					AND containerType = 'pm'
			GROUP BY	containerID";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$sql = "UPDATE	wcf".WCF_N."_pm
				SET	attachments = ".$row['count']."
				WHERE	pmID = ".$row['containerID'];
			WCF::getDB()->sendQuery($sql);
		}
		
		// update viewed status
		$sql = "UPDATE	wcf".WCF_N."_pm pm
			SET	isViewedByAll = if((
					SELECT	COUNT(*)
					FROM 	wcf".WCF_N."_pm_to_user
					WHERE 	pmID = pm.pmID
						AND isDeleted < 2
						AND isViewed = 0) > 0
				, 0, 1)
			WHERE	pm.pmID IN (".implode(',', $pmIDArray).")";
		WCF::getDB()->sendQuery($sql);
		$this->executed();
		
		$this->calcProgress(($this->limit * $this->loop), $count);
		$this->nextLoop();
	}
}
?>