<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
require_once(WCF_DIR.'lib/system/session/Session.class.php');
require_once(WCF_DIR.'lib/data/mail/Mail.class.php');

/**
 * Enables users.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class UserEnableAction extends AbstractAction {
	public $userIDs = array();
	public $url = '';
		
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['userID'])) $this->userIDs[] = intval($_REQUEST['userID']);
		else {
			$this->userIDs = WCF::getSession()->getVar('markedUsers');	
			if (!is_array($this->userIDs)) $this->userIDs = array();
		}
		if (isset($_REQUEST['url'])) $this->url = $_REQUEST['url'];
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.user.canEnableUser');
		
		if (count($this->userIDs) > 0) {
			// check permission
			$sql = "SELECT	DISTINCT groupID
				FROM	wcf".WCF_N."_user_to_groups
				WHERE	userID IN (".implode(',', $this->userIDs).")";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (!Group::isAccessibleGroup($row['groupID'])) {
					throw new PermissionDeniedException();
				}
			}
			
			// send notification
			$languages = array(0 => WCF::getLanguage(), WCF::getLanguage()->getLanguageID() => WCF::getLanguage());
			$sql = "SELECT	userID, username, email, languageID
				FROM	wcf".WCF_N."_user
				WHERE	userID IN (".implode(',', $this->userIDs).")
					AND activationCode <> 0";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				if (!isset($languages[$row['languageID']])) {
					$languages[$row['languageID']] = new Language($row['languageID']);
				}
				
				$mail = new Mail(array($row['username'] => $row['email']),
					$languages[$row['languageID']]->get('wcf.acp.user.activation.mail.subject', array('PAGE_TITLE' => $languages[$row['languageID']]->get(PAGE_TITLE))),
					$languages[$row['languageID']]->get('wcf.acp.user.activation.mail',
						array('PAGE_TITLE' => $languages[$row['languageID']]->get(PAGE_TITLE), '$username' => $row['username'], 'PAGE_URL' => PAGE_URL, 'MAIL_ADMIN_ADDRESS' => MAIL_ADMIN_ADDRESS)));
				$mail->send();
			}
				
			// update groups
			$sql = "DELETE FROM	wcf".WCF_N."_user_to_groups
				WHERE		userID IN (".implode(',', $this->userIDs).")
						AND groupID = ".Group::getGroupIdByType(Group::GUESTS);
			WCF::getDB()->sendQuery($sql);
			$sql = "INSERT IGNORE INTO	wcf".WCF_N."_user_to_groups
							(userID, groupID)
				VALUES			(".implode(', '.Group::getGroupIdByType(Group::USERS).'),(', $this->userIDs).", '".Group::getGroupIdByType(Group::USERS)."')";
			WCF::getDB()->sendQuery($sql);
			
			// update user
			$sql = "UPDATE	wcf".WCF_N."_user
				SET	activationCode = 0
				WHERE	userID IN (".implode(',', $this->userIDs).")";
			WCF::getDB()->sendQuery($sql);
			
			// unmark users
			UserEditor::unmarkAll();
		
			// reset sessions
			Session::resetSessions($this->userIDs);
		}
		$this->executed();
		
		if (!empty($this->url)) HeaderUtil::redirect($this->url);
		else {
			// set active menu item
			WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.user.management');
			
			// show succes message
			WCF::getTPL()->assign('message', 'wcf.acp.user.enable.success');
			WCF::getTPL()->display('success');
		}
		exit;
	}
}
?>