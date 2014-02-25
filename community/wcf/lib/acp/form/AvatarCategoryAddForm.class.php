<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/category/AvatarCategoryEditor.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');

/**
 * Shows the form for adding new avatar categories.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.avatar
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class AvatarCategoryAddForm extends ACPForm {
	// system
	public $templateName = 'avatarCategoryAdd';
	public $activeMenuItem = 'wcf.acp.menu.link.avatar.category.add';
	public $neededPermissions = 'admin.avatar.canAddAvatarCategory';
	
	// parameters
	public $title = '';
	public $showOrder = 0;
	public $groupID = 0;
	public $neededPoints = 0;
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['title'])) $this->title = StringUtil::trim($_POST['title']);
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);
		if (isset($_POST['groupID'])) $this->groupID = intval($_POST['groupID']);
		if (isset($_POST['neededPoints'])) $this->neededPoints = intval($_POST['neededPoints']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		// validate title
		if (empty($this->title)) {
			throw new UserInputException('title');
		}
		
		// validate group id
		$group = new Group($this->groupID);
		if (!$group->groupID) {
			throw new UserInputException('groupID');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save
		AvatarCategoryEditor::create($this->title, $this->showOrder, $this->groupID, $this->neededPoints);
		$this->saved();
		
		// reset values
		$this->title = '';
		$this->showOrder = $this->neededPoints = $this->groupID = 0;

		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get available groups
		$this->groups = Group::getAccessibleGroups(array(), array(Group::GUESTS, Group::EVERYONE));
		
		if (!count($_POST)) {
			// default value
			$this->groupID = Group::getGroupIdByType(Group::USERS);
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'action' => 'add',
			'title' => $this->title,
			'showOrder' => $this->showOrder,
			'groups' => $this->groups,	
			'groupID' => $this->groupID,
			'neededPoints' => $this->neededPoints
		));
	}
}
?>