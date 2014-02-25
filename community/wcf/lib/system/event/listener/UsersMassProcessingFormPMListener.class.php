<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class UsersMassProcessingFormPMListener implements EventListener {
	public $pmSubject = '';
	public $pmText = '';
	
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_PM == 1 && WCF::getUser()->getPermission('admin.user.canPMUser')) {
			if ($eventName == 'readParameters') {
				$eventObj->availableActions[] = 'sendPM';
			}
			else if ($eventName == 'readFormParameters') {
				if (isset($_POST['pmSubject'])) $this->pmSubject = StringUtil::trim($_POST['pmSubject']);
				if (isset($_POST['pmText'])) $this->pmText = StringUtil::trim($_POST['pmText']);
			}
			else if ($eventName == 'validate') {
				if ($eventObj->action == 'sendPM') {
					if (empty($this->pmSubject)) {
						throw new UserInputException('pmSubject');
					}
					
					if (empty($this->pmText)) {
						throw new UserInputException('pmText');
					}
				}
			}
			else if ($eventName == 'buildConditions') {
				if ($eventObj->action == 'sendPM') {
					// get user ids
					$userIDArray = array();
					$sql = "SELECT		user.userID
						FROM		wcf".WCF_N."_user user
						".$eventObj->conditions->get();
					$result = WCF::getDB()->sendQuery($sql);
					while ($row = WCF::getDB()->fetchArray($result)) {
						$userIDArray[] = $row['userID'];
					}
					
					if (count($userIDArray)) {
						// save pm
						$sql = "INSERT INTO	wcf".WCF_N."_pm
									(userID, username, subject, message, time)
							VALUES		(".WCF::getUser()->userID.", '".escapeString(WCF::getUser()->username)."', '".escapeString($this->pmSubject)."', '".escapeString($this->pmText)."', ".TIME_NOW.")";
						WCF::getDB()->sendQuery($sql);
						// get id
						$pmID = WCF::getDB()->getInsertID("wcf".WCF_N."_pm", 'pmID');
						
						// add recipients
						$sql = "INSERT INTO	wcf".WCF_N."_pm_to_user
									(pmID, recipientID, recipient, isBlindCopy)
							SELECT		".$pmID.", userID, username, 1
							FROM		wcf".WCF_N."_user
							WHERE		userID IN (".implode(',', $userIDArray).")";
						WCF::getDB()->sendQuery($sql);
						
						// update counters
						$sql = "UPDATE	wcf".WCF_N."_user
							SET	pmUnreadCount = pmUnreadCount + 1,
								pmOutstandingNotifications = pmOutstandingNotifications + 1
							WHERE	userID IN (".implode(',', $userIDArray).")";
						WCF::getDB()->sendQuery($sql);
						
						// reset sessions
						Session::resetSessions($userIDArray, true, false);
						
						// set affected users
						$eventObj->affectedUsers = count($userIDArray);
					}
				}
			}
			else if ($eventName == 'assignVariables') {
				WCF::getTPL()->append('additionalActions', '<li><label><input onclick="if (IS_SAFARI) enableSendPM()" onfocus="enableSendPM()" type="radio" name="action" value="sendPM" '.($eventObj->action == 'sendPM' ? 'checked="checked" ' : '').'/> '.WCF::getLanguage()->get('wcf.acp.user.sendPM').'</label></li>');
				WCF::getTPL()->assign(array(
					'pmSubject' => $this->pmSubject,
					'pmText' => $this->pmText,
					'errorField' => $eventObj->errorField,
					'errorType' => $eventObj->errorType
				));
				WCF::getTPL()->append('additionalActionSettings', WCF::getTPL()->fetch('usersMassProcessingPM'));
			}
		}
	}
}
?>