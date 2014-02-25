<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/action/WorkerAction.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
require_once(WCF_DIR.'lib/data/mail/Mail.class.php');

/**
 * Sends e-mails.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class UserMailAction extends WorkerAction {
	public $limit = 50;
	public $mailID = 0;
	public $userMailData = array();
	public $action = 'UserMail';
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// parameters
		if (isset($_REQUEST['mailID'])) $this->mailID = intval($_REQUEST['mailID']);
		
		// get mail data
		$userMailData = WCF::getSession()->getVar('userMailData');
		if (!isset($userMailData[$this->mailID])) {
			throw new SystemException('could not find mail data');
		}
		
		$this->userMailData = $userMailData[$this->mailID];
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permission
		WCF::getUser()->checkPermission('admin.user.canMailUser');
		
		// sql condition
		$condition = '';
		if ($this->userMailData['action'] == '') {
			$condition = "WHERE user.userID IN (".$this->userMailData['userIDs'].")";
		}
		if ($this->userMailData['action'] == 'group') {
			$condition = "WHERE user.userID IN (SELECT userID FROM wcf".WCF_N."_user_to_groups WHERE groupID IN (".$this->userMailData['groupIDs']."))";
		}
		
		// count users
		$sql = "SELECT	COUNT(*) AS count
			FROM	wcf".WCF_N."_user user
			".$condition;
		$row = WCF::getDB()->getFirstRow($sql);
		$count = $row['count'];
		
		if ($count <= ($this->limit * $this->loop)) {
			// unmark users
			UserEditor::unmarkAll();
			
			// clear session
			$userMailData = WCF::getSession()->getVar('userMailData');
			unset($userMailData[$this->mailID]);
			WCF::getSession()->register('userMailData', $userMailData);
			
			$this->calcProgress();
			$this->finish();
		}
		
		// get users
		$sql = "SELECT		user_option.*, user.*
			FROM		wcf".WCF_N."_user user
			LEFT JOIN	wcf".WCF_N."_user_option_value user_option
			ON		(user_option.userID = user.userID)
			".$condition."
			ORDER BY	user.userID";
		$result = WCF::getDB()->sendQuery($sql, $this->limit, ($this->limit * $this->loop));
		while ($row = WCF::getDB()->fetchArray($result)) {
			$user = new User(null, $row);
			$adminCanMail = $user->adminCanMail;
			if ($adminCanMail === null || $adminCanMail == 1) {
				$this->sendMail($user);
			}
		}
		
		$this->executed();
		$this->calcProgress(($this->limit * $this->loop), $count);
		$this->nextLoop('wcf.acp.worker.progress.working', 'index.php?action='.$this->action.'&mailID='.$this->mailID.'&limit='.$this->limit.'&loop='.($this->loop + 1).'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
	}
	
	/**
	 * Sends the mail to given user.
	 * 
	 * @param	User		$user
	 */
	protected function sendMail(User $user) {
		// send mail
		try {
			$mail = new Mail(array($user->username => $user->email), $this->userMailData['subject'], StringUtil::replace('{$username}', $user->username, $this->userMailData['text']), $this->userMailData['from']);
			if ($this->userMailData['enableHTML']) $mail->setContentType('text/html');
			$mail->send();
		}
		catch (SystemException $e) {} // ignore errors
	}
}
?>