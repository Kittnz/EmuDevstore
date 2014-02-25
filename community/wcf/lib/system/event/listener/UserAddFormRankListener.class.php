<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/acp/form/UserEditForm.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
require_once(WCF_DIR.'lib/data/user/rank/UserRank.class.php');

/**
 * Adds the rank select to user profile add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	system.event.listener
 * @category 	Community Framework
 */
class UserAddFormRankListener implements EventListener {
	protected $userTitle = '';
	protected $rankID = 0;
	
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_USER_RANK == 1) {
			if ($eventObj instanceof UserEditForm) {
				$groupIDs = $eventObj->user->getGroupIDs();
				$activityPoints = $eventObj->user->activityPoints;
				$gender = $eventObj->user->gender;
			}
			else {
				$activityPoints = $gender = 0;
				$groupIDs = Group::getGroupIdsByType(array(GROUP::EVERYONE, GROUP::USERS));
			}
			
			if ($eventName == 'readFormParameters') {
				if (isset($_POST['userTitle'])) $this->userTitle = StringUtil::trim($_POST['userTitle']);
				if (isset($_POST['rankID'])) $this->rankID = intval($_POST['rankID']);
			}
			else if ($eventName == 'validate') {
				// save user title
				$eventObj->additionalFields['userTitle'] = $this->userTitle;
				$groupIDs = array_unique(array_merge(Group::getGroupIdsByType(array(GROUP::EVERYONE, GROUP::USERS)), $eventObj->groupIDs));
				
				if (isset($_POST['rankID'])) {
					// get rank id
					$sql = "SELECT		rankID
						FROM		wcf".WCF_N."_user_rank
						WHERE		".($this->rankID ? "rankID = ".$this->rankID." AND" : "")."
								groupID IN (0,".implode(',', $groupIDs).")
								AND neededPoints <= ".intval($activityPoints)."
								AND gender IN (0, ".intval($gender).")";
					$row = WCF::getDB()->getFirstRow($sql);
					if (!isset($row['rankID'])) $this->rankID = 0;
					
					// save rankid
					$eventObj->additionalFields['rankID'] = $this->rankID;
				}
			}
			else if ($eventName == 'assignVariables') {
				if (!count($_POST) && $eventObj instanceof UserEditForm) {
					// get current values
					$this->userTitle = $eventObj->user->userTitle;
					$this->rankID = $eventObj->user->rankID;
				}
				
				$fields = array();
				
				// get user title
				$fields[] = array(
					'optionName' => 'userTitle',
	                   		'beforeLabel' => false,
	                    		'html' => '<input id="userTitle" type="text" class="inputText" name="userTitle" value="'.StringUtil::encodeHTML($this->userTitle).'" />'
	                    	);
				
				// get ranks
				require_once(WCF_DIR.'lib/data/user/rank/UserRank.class.php');
				$ranks = array();
				$sql = "SELECT		*
					FROM		wcf".WCF_N."_user_rank
					WHERE		groupID IN (0,".implode(',', $groupIDs).")
							AND neededPoints <= ".intval($activityPoints)."
							AND gender IN (0, ".intval($gender).")
					ORDER BY	neededPoints DESC, gender DESC";
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					if (isset($ranks[$row['groupID']])) continue;
					$ranks[$row['groupID']] = new UserRank(null, $row);
				}
				
				if (count($ranks) > 1) {
					WCF::getTPL()->assign(array(
						'ranks' => $ranks,
						'rankID' => $this->rankID
					));
					$fields[] = array(
						'optionName' => 'rankID',
						'divClass' => 'formRadio',
	                       			'beforeLabel' => false,
	                       			'isOptionGroup' => true,
	                        		'html' => WCF::getTPL()->fetch('userAddRankSelect')
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