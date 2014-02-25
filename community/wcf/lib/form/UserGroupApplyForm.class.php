<?php
require_once(WCF_DIR.'lib/form/AbstractForm.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
require_once(WCF_DIR.'lib/data/user/group/GroupApplicationEditor.class.php');

/**
 * Shows the user group application form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.group
 * @subpackage	form
 * @category 	Community Framework (commercial)
 */
class UserGroupApplyForm extends AbstractForm {
	public $groupID = 0;
	public $group;
	public $templateName = 'userGroupApply';
	public $reason = '';
	public $enableNotification = 0;
	public $applicationID;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['groupID'])) $this->groupID = intval($_REQUEST['groupID']);
		$this->group = new Group($this->groupID);
		if (!$this->group->groupID || ($this->group->groupType != 6 && $this->group->groupType != 7)) {
			throw new IllegalLinkException();
		}
		
		// check if an application for this group already exist
		if (GroupApplicationEditor::getApplication(WCF::getUser()->userID, $this->group->groupID)) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['reason'])) $this->reason = StringUtil::trim($_POST['reason']);
		if (isset($_POST['enableNotification'])) $this->enableNotification = intval($_POST['enableNotification']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if (empty($this->reason)) {
			throw new UserInputException('reason');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save
		require_once(WCF_DIR.'lib/data/user/group/GroupApplicationEditor.class.php');
		$this->applicationID = GroupApplicationEditor::create(WCF::getUser()->userID, $this->groupID, $this->reason, $this->enableNotification);
		$application = new GroupApplicationEditor($this->applicationID);
		$application->sendLeaderNotification();
		$this->saved();
		
		HeaderUtil::redirect('index.php?page=UserGroups'.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'group' => $this->group,
			'groupID' => $this->groupID,
			'reason' => $this->reason,
			'enableNotification' => $this->enableNotification
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// check permission
		if (!WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		if (MODULE_MODERATED_USER_GROUP != 1) {
			throw new IllegalLinkException();
		}
		
		// set active tab
		UserCPMenu::getInstance()->setActiveMenuItem('wcf.user.usercp.menu.link.userGroups');
		
		parent::show();
	}
}
?>