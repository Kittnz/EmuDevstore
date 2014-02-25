<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Adds the online marking select to user profile edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.page.user.usersOnline
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class UserProfileEditFormOnlineMarkingListener implements EventListener {
	protected $userOnlineGroupID = 0;
	
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_USERS_ONLINE == 1) {
			if ($eventObj->activeCategory == 'profile') {
				if ($eventName == 'validate') {
					if (WCF::getUser()->getPermission('user.profile.rank.canSelectOnlineMarking')) {
						if (isset($_POST['userOnlineGroupID'])) $this->userOnlineGroupID = intval($_POST['userOnlineGroupID']);
						
						// validate user online group id
						if ($this->userOnlineGroupID) {
							try {
								$sql = "SELECT		groupID
									FROM		wcf".WCF_N."_group
									WHERE		groupID = ".$this->userOnlineGroupID."
											AND groupID IN (".implode(',', WCF::getUser()->getGroupIDs()).")";
								$row = WCF::getDB()->getFirstRow($sql);
								if (!isset($row['groupID'])) throw new UserInputException('userOnlineGroupID');
								
								// save rankid
								$eventObj->additionalFields['userOnlineGroupID'] = $this->userOnlineGroupID;
							}
							catch (UserInputException $e) {
								$eventObj->errorType[$e->getField()] = $e->getType();
							}
						}
					}
				}
				else if ($eventName == 'assignVariables') {
					if (!count($_POST)) {
						// get current values
						$this->userOnlineGroupID = WCF::getUser()->userOnlineGroupID;
					}
					
					$fields = array();
					
					// get user online markings
					if (WCF::getUser()->getPermission('user.profile.rank.canSelectOnlineMarking')) {
						$markings = array();
						$sql = "SELECT		groupID, groupName, userOnlineMarking
							FROM		wcf".WCF_N."_group
							WHERE		groupID IN (".implode(',', WCF::getUser()->getGroupIDs()).")
							ORDER BY	groupID ASC";
						$result = WCF::getDB()->sendQuery($sql);
						while ($row = WCF::getDB()->fetchArray($result)) {
							$row['userOnlineMarking'] = sprintf($row['userOnlineMarking'], StringUtil::encodeHTML(WCF::getUser()->username));
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
			                        		'html' => WCF::getTPL()->fetch('userProfileEditOnlineMarkingSelect')
			                        	);
						}
					}
				
					// add fields
					if (count($fields) > 0) {
						foreach ($eventObj->options as $key => $category) {
							if ($category['categoryName'] == 'profile.rank') {
								$eventObj->options[$key]['options'] = array_merge($category['options'], $fields);
								return;
							}
						}
						
						$eventObj->options[] = array(
							'categoryName' => 'profile.rank',
							'categoryIconM' => '',
							'options' => $fields
						);
					}
				}
			}
		}
	}
}
?>