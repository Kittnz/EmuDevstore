<?php
require_once(WCF_DIR.'lib/form/UserGroupApplicationEditForm.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
require_once(WCF_DIR.'lib/data/user/group/GroupApplicationEditor.class.php');

/**
 * Shows the user group leader application edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.group
 * @subpackage	form
 * @category 	Community Framework (commercial)
 */
class UserGroupLeaderApplicationEditForm extends UserGroupApplicationEditForm {
	public $templateName = 'userGroupLeaderApplicationEdit';
	public $reply = '';
	public $applicationStatus = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		AbstractForm::readParameters();
		
		// get application
		if (isset($_REQUEST['applicationID'])) $this->applicationID = intval($_REQUEST['applicationID']);
		$this->application = new GroupApplicationEditor($this->applicationID);
		if (!$this->application->applicationID) {
			throw new IllegalLinkException();
		}
		
		// get group
		$this->group = new Group($this->application->groupID);
		$this->groupID = $this->group->groupID;
		
		if ($this->application->applicationStatus > 0) {
			$this->reason = $this->application->reason;
		}
		
		// check permission
		if (!GroupApplicationEditor::isGroupLeader(WCF::getUser(), $this->application->groupID)) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		AbstractForm::readFormParameters();
		
		if (isset($_POST['reply'])) $this->reply = StringUtil::trim($_POST['reply']);
		if (isset($_POST['applicationStatus'])) $this->applicationStatus = intval($_POST['applicationStatus']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		AbstractForm::validate();
		
		if ($this->applicationStatus < 0 || $this->applicationStatus > 3) {
			throw new UserInputException('applicationStatus');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// save
		$this->application->updateByLeader($this->applicationStatus, $this->reply, WCF::getUser()->userID);
		
		// reset session
		Session::resetSessions($this->application->userID);
		$this->saved();
		
		HeaderUtil::redirect('index.php?page=UserGroupLeader'.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// default values
		if (!count($_POST)) {
			$this->reply = $this->application->reply;
			$this->applicationStatus = $this->application->applicationStatus;
			if ($this->applicationStatus == 0) $this->applicationStatus = 1;
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'reply' => $this->reply,
			'applicationStatus' => $this->applicationStatus
		));
	}
}
?>