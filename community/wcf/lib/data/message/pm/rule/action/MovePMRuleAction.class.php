<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/pm/rule/action/AbstractPMRuleAction.class.php');

/**
 * Moves incoming private message to a specific folder.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm.rule.action
 * @category 	Community Framework (commercial)
 */
class MovePMRuleAction extends AbstractPMRuleAction {
	/**
	 * caches the folders of a user
	 *
	 * @var array
	 */
	protected static $userFolders = null;

	/**
	 * @see PMRuleAction::execute()
	 */
	public function execute(PMEditor $pm, PMRule $rule, UserProfile $recipient) {
		$sql = "UPDATE	wcf".WCF_N."_pm_to_user
			SET	folderID = ".intval($rule->ruleDestination)."
			WHERE	pmID = ".$pm->pmID."
				AND recipientID = ".$recipient->userID;
		WCF::getDB()->sendQuery($sql);
		return true;
	}

	/**
	 * @see PMRuleAction::getDestinationType()
	 */
	public function getDestinationType() {
		return 'options';
	}
	
	/**
	 * @see PMRuleAction::getAvailableDestinations()
	 */
	public function getAvailableDestinations() {
		if (self::$userFolders === null) {
			self::$userFolders = array();
			require_once(WCF_DIR.'lib/data/message/pm/PMFolderList.class.php');
			$folders = PMFolderList::getUserFolders();
			foreach ($folders as $folder) {
				self::$userFolders[$folder['folderID']] = $folder['folderName'];
			}
		}
		
		return self::$userFolders;
	}
}
?>