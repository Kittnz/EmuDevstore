<?php
require_once(WCF_DIR.'lib/form/UserGroupApplyForm.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
require_once(WCF_DIR.'lib/data/user/group/GroupApplicationEditor.class.php');

/**
 * Shows the user group application edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.group
 * @subpackage	form
 * @category 	Community Framework (commercial)
 */
class UserGroupApplicationEditForm extends UserGroupApplyForm {
	public $templateName = 'userGroupApplicationEdit';
	public $application;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		AbstractForm::readParameters();
		
		// get application
		if (isset($_REQUEST['applicationID'])) $this->applicationID = intval($_REQUEST['applicationID']);
		$this->application = new GroupApplicationEditor($this->applicationID);
		if (!$this->application->applicationID || !WCF::getUser()->userID || WCF::getUser()->userID != $this->application->userID) {
			throw new IllegalLinkException();
		}
		
		// get group
		$this->group = new Group($this->application->groupID);
		$this->groupID = $this->group->groupID;
		
		if ($this->application->applicationStatus > 0) {
			$this->reason = $this->application->reason;
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		// withdraw application
		if (isset($_POST['withdraw'])) {
			// check permission
			if ($this->application->applicationStatus > 0) {
				throw new PermissionDeniedException();
			}
			
			// withdraw
			$this->application->delete();
			
			HeaderUtil::redirect('index.php?page=UserGroups'.SID_ARG_2ND_NOT_ENCODED);
			exit;
		}
		
		parent::validate();
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// save
		$this->application->update(($this->application->applicationStatus == 0 ? $this->reason : null), $this->enableNotification);
		$this->saved();
		
		HeaderUtil::redirect('index.php?page=UserGroups'.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// default values
		if (!count($_POST)) {
			$this->reason = $this->application->reason;
			$this->enableNotification = $this->application->enableNotification;
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'application' => $this->application,
			'applicationID' => $this->applicationID,
		));
	}
}
?>