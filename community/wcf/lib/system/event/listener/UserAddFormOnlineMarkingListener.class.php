<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/acp/form/UserEditForm.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');

/**
 * Adds the user online marking select to user add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.page.user.usersOnline
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class UserAddFormOnlineMarkingListener implements EventListener {
	protected $userOnlineGroupID = 0;
	
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_USERS_ONLINE == 1) {
			if ($eventObj instanceof UserEditForm) {
				$groupIDs = $eventObj->user->getGroupIDs();
				$username = $eventObj->user->username;
			}
			else {
				$groupIDs = Group::getGroupIdsByType(array(GROUP::EVERYONE, GROUP::USERS));
				$username = WCF::getLanguage()->get('wcf.user.username');
			}
			
			if ($eventName == 'readFormParameters') {
				if (isset($_POST['userOnlineGroupID'])) $this->userOnlineGroupID = intval($_POST['userOnlineGroupID']);
			}
			else if ($eventName == 'validate') {
				$groupIDs = array_unique(array_merge(Group::getGroupIdsByType(array(GROUP::EVERYONE, GROUP::USERS)), $eventObj->groupIDs));
				
				// get rank id
				$sql = "SELECT		groupID
					FROM		wcf".WCF_N."_group
					WHERE		groupID = ".$this->userOnlineGroupID."
							AND groupID IN (".implode(',', $groupIDs).")";
				$row = WCF::getDB()->getFirstRow($sql);
				if (!isset($row['groupID'])) $this->userOnlineGroupID = Group::getGroupIdByType(Group::USERS);
				
				// save rankid
				$eventObj->additionalFields['userOnlineGroupID'] = $this->userOnlineGroupID;
			}
			else if ($eventName == 'assignVariables') {
				if (!count($_POST) && $eventObj instanceof UserEditForm) {
					// get current values
					$this->userOnlineGroupID = $eventObj->user->userOnlineGroupID;
				}
				
				$fields = array();
				
				$markings = array();
				$sql = "SELECT		groupID, groupName, userOnlineMarking
					FROM		wcf".WCF_N."_group
					WHERE		groupID IN (".implode(',', $groupIDs).")
					ORDER BY	groupID ASC";
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					$row['userOnlineMarking'] = sprintf($row['userOnlineMarking'], StringUtil::encodeHTML($username));
					$markings[] = $row;
				}
				
				if (count($markings) > 1) {
					WCF::getTPL()->assign(array(
						'markings' => $markings,
						'userOnlineGroupID' => $this->userOnlineGroupID
					));
					$fields[] = array(
						'optionName' => 'userOnlineGroupID',
						'divClass' => 'formRadio',
	                       			'beforeLabel' => false,
	                       			'isOptionGroup' => true,
	                        		'html' => WCF::getTPL()->fetch('userAddOnlineMarkingSelect')
	                        	);
				}
							
				// add fields
				if (count($fields) > 0) {
					foreach ($eventObj->options as $key1 => $category1) {
						if ($category1['categoryName'] == 'profile') {
							foreach ($category1['categories'] as $key2 => $category2) {
								if ($category2['categoryName'] == 'profile.rank') {
									$eventObj->options[$key1]['categories'][$key2]['options'] = array_merge($category2['options'], $fields);
									return;
								}
							}
							
							$eventObj->options[$key1]['categories'][] = array(
								'categoryName' => 'profile.rank',
								'categoryIconM' => RELATIVE_WCF_DIR . 'icon/userProfileRankM.png',
								'options' => $fields
							);
						}
					}
				}
			}
		}
	}
}
?>