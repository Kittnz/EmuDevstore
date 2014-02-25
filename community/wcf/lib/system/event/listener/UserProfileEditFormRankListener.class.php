<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Adds the rank select to user profile edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	system.event.listener
 * @category 	Community Framework
 */
class UserProfileEditFormRankListener implements EventListener {
	protected $userTitle = '';
	protected $rankID = 0;
	
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_USER_RANK == 1) {
			if ($eventObj->activeCategory == 'profile') {
				if ($eventName == 'validate') {
					if (WCF::getUser()->getPermission('user.profile.rank.canEditUserTitle')) {
						if (isset($_POST['userTitle'])) $this->userTitle = StringUtil::trim($_POST['userTitle']);
						
						// check user title
						if (!StringUtil::executeWordFilter($this->userTitle, USER_FORBIDDEN_TITLES)) {
							$eventObj->errorType['userTitle'] = 'forbidden';
						}
						else {
							// save user title
							$eventObj->additionalFields['userTitle'] = $this->userTitle;
						}
					}
					
					if (WCF::getUser()->getPermission('user.profile.rank.canSelectRank')) {
						if (isset($_POST['rankID'])) $this->rankID = intval($_POST['rankID']);
						
						// validate rank id
						if ($this->rankID) {
							try {
								$sql = "SELECT		rankID
									FROM		wcf".WCF_N."_user_rank
									WHERE		rankID = ".$this->rankID."
											AND (groupID = 0 OR groupID IN (".implode(',', WCF::getUser()->getGroupIDs())."))
											AND neededPoints <= ".intval(WCF::getUser()->activityPoints)."
											AND gender IN (0, ".intval(WCF::getUser()->gender).")";
								$row = WCF::getDB()->getFirstRow($sql);
								if (!isset($row['rankID'])) throw new UserInputException('rankID');
								
								// save rankid
								$eventObj->additionalFields['rankID'] = $this->rankID;
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
						$this->userTitle = WCF::getUser()->userTitle;
						$this->rankID = WCF::getUser()->rankID;
					}
					
					$fields = array();
					
					// get user title
					if (WCF::getUser()->getPermission('user.profile.rank.canEditUserTitle')) {
						$fields[] = array(
							'optionName' => 'userTitle',
		                       			'beforeLabel' => false,
		                        		'html' => '<input id="userTitle" type="text" class="inputText" name="userTitle" value="'.StringUtil::encodeHTML($this->userTitle).'" />'
		                        	);
					}
					
					// get ranks
					if (WCF::getUser()->getPermission('user.profile.rank.canSelectRank')) {
						require_once(WCF_DIR.'lib/data/user/rank/UserRank.class.php');
						$ranks = array();
						$sql = "SELECT		*
							FROM		wcf".WCF_N."_user_rank
							WHERE		(groupID = 0 OR groupID IN (".implode(',', WCF::getUser()->getGroupIDs())."))
									AND neededPoints <= ".intval(WCF::getUser()->activityPoints)."
									AND gender IN (0, ".intval(WCF::getUser()->gender).")
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
			                        		'html' => WCF::getTPL()->fetch('userProfileEditRankSelect')
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