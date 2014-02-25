<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/acp/form/GroupEditForm.class.php');

/**
 * Adds the options for moderated groups to group add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.group
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class GroupAddFormModeratedGroupsListener implements EventListener {
	public $groupDescription = '';
	public $groupType = 4;
	public $groupLeaders = '';
	public $leaders = array();
	public $saveLeaders = false;
	
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_MODERATED_USER_GROUP == 1) {
			if ($eventObj instanceof GroupEditForm && $eventObj->group->groupType < 4) {
				// a default group can't be a moderated group
				return;	
			}
			
			if ($eventName == 'readFormParameters') {
				if (isset($_POST['groupDescription'])) $this->groupDescription = StringUtil::trim($_POST['groupDescription']);
				if (isset($_POST['groupType'])) $this->groupType = intval($_POST['groupType']);
				if (isset($_POST['groupLeaders'])) $this->groupLeaders = StringUtil::trim($_POST['groupLeaders']);
			}
			else if ($eventName == 'validate') {
				try {
					// group type
					if ($this->groupType < 4 || $this->groupType > 7) {
						throw new UserInputException('groupType');
					}
					
					// group leaders
					// explode multiple names to an array
					$nameArray = ArrayUtil::trim(explode(',', $this->groupLeaders));
					$error = array();
		
					// loop through names
					foreach ($nameArray as $name) {
						try {
							// get user group
							$sql = "SELECT	groupID, groupName
								FROM	wcf".WCF_N."_group
								WHERE	groupName = '".escapeString($name)."'";
							$row = WCF::getDB()->getFirstRow($sql);
							if (!empty($row['groupID']) && (!($eventObj instanceof GroupEditForm) || $row['groupID'] != $eventObj->groupID)) {
								$this->leaders[] = new Group($row['groupID']);
							}
							else {
								// get user
								$user = new User(null, null, $name);
								if (!$user->userID) {
									throw new UserInputException('username', 'notFound');
								}
								
								$this->leaders[] = $user;
							}
						}
						catch (UserInputException $e) {
							$error[] = array('type' => $e->getType(), 'username' => $name);
						}
					}
					
					if (count($error)) {
						throw new UserInputException('groupLeaders', $error);
					}
				}
				catch (UserInputException $e) {
					$eventObj->errorType[$e->getField()] = $e->getType();
				}
			}
			else if ($eventName == 'save') {
				// save
				$eventObj->additionalFields['groupDescription'] = $this->groupDescription;
				$eventObj->additionalFields['groupType'] = $this->groupType;
				
				// reset values
				if (!($eventObj instanceof GroupEditForm)) {
					$this->groupDescription = '';
					$this->groupType = 4;
				}
			}
			else if ($eventName == 'saved') {
				if ($eventObj instanceof GroupEditForm) {
					// delete old group leaders
					$sql = "DELETE FROM	wcf".WCF_N."_group_leader
						WHERE		groupID = ".$eventObj->group->groupID;
					WCF::getDB()->sendQuery($sql);
					
					// deleted old applications
					if ($this->groupType != 6 && $this->groupType != 7) {
						$sql = "DELETE FROM	wcf".WCF_N."_group_application
							WHERE		groupID = ".$eventObj->group->groupID;
						WCF::getDB()->sendQuery($sql);
					}
				}
				
				// save group leaders
				$inserts = '';
				foreach ($this->leaders as $leader) {
					if (!empty($inserts)) $inserts .= ',';
					$inserts .= '('.$eventObj->group->groupID.', '.(($leader instanceof User) ? $leader->userID : 0).', '.(($leader instanceof Group) ? $leader->groupID : 0).')';
				}
				
				if (!empty($inserts)) {
					$sql = "INSERT IGNORE INTO	wcf".WCF_N."_group_leader
									(groupID, leaderUserID, leaderGroupID)
						VALUES			".$inserts;
					WCF::getDB()->sendQuery($sql);
				}
				
				// reset values
				if (!($eventObj instanceof GroupEditForm)) {
					$this->groupLeaders = '';
				}
			}
			else if ($eventName == 'assignVariables') {
				if (!count($_POST) && $eventObj instanceof GroupEditForm) {
					// get default values
					$this->groupDescription = $eventObj->group->groupDescription;
					$this->groupType = $eventObj->group->groupType;
					
					// get group leaders
					$this->groupLeaders = '';
					$sql = "SELECT		CASE WHEN user_table.username IS NOT NULL THEN user_table.username ELSE usergroup.groupName END AS name
						FROM		wcf".WCF_N."_group_leader leader
						LEFT JOIN	wcf".WCF_N."_user user_table
						ON		(user_table.userID = leader.leaderUserID)
						LEFT JOIN	wcf".WCF_N."_group usergroup
						ON		(usergroup.groupID = leader.leaderGroupID)
						WHERE		leader.groupID = ".$eventObj->group->groupID."
						ORDER BY	name";
					$result = WCF::getDB()->sendQuery($sql);
					while ($row = WCF::getDB()->fetchArray($result)) {
						if (!empty($this->groupLeaders)) $this->groupLeaders .= ', ';
						$this->groupLeaders .= $row['name'];
					}
				}
				
				// assign variables	
				WCF::getTPL()->assign(array(
					'groupDescription' => $this->groupDescription,
					'groupType' => $this->groupType,
					'groupLeaders' => $this->groupLeaders,
					'errorField' => $eventObj->errorField,
					'errorType' => $eventObj->errorType
				));
				WCF::getTPL()->append('additionalFields', WCF::getTPL()->fetch('groupAddModeratedGroups'));
			}
		}
	}
}
?>