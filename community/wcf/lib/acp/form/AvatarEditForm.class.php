<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/AvatarEditor.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/category/AvatarCategory.class.php');

/**
 * Shows the avatar edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.avatar
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class AvatarEditForm extends ACPForm {
	public $templateName = 'avatarEdit';
	public $activeMenuItem = 'wcf.acp.menu.link.avatar';
	public $neededPermissions = 'admin.avatar.canEditAvatar';
		
	public $avatar;
	public $avatarID = 0;
	public $groupID = 0;
	public $neededPoints = 0;
	public $groups = array();
	public $avatarCategoryID = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['avatarID'])) $this->avatarID = intval($_REQUEST['avatarID']);
		$this->avatar = new AvatarEditor($this->avatarID);
		if (!$this->avatar->avatarID || $this->avatar->userID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['groupID'])) $this->groupID = intval($_POST['groupID']);
		if (isset($_POST['neededPoints'])) $this->neededPoints = intval($_POST['neededPoints']);
		if (isset($_POST['avatarCategoryID'])) $this->avatarCategoryID = intval($_POST['avatarCategoryID']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		// validate group id
		$group = new Group($this->groupID);
		if (!$group->groupID) {
			throw new UserInputException('groupID');
		}
		
		// category
		if ($this->avatarCategoryID != 0) {
			$avatarCategory = new AvatarCategory($this->avatarCategoryID);
			if (!$avatarCategory->avatarCategoryID) {
				throw new UserInputException('avatarCategoryID');
			}
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function save() {
		parent::save();

		// update
		$this->avatar->update(array('groupID' => $this->groupID, 'neededPoints' => $this->neededPoints, 'avatarCategoryID' => $this->avatarCategoryID));
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'groups' => $this->groups,	
			'groupID' => $this->groupID,
			'neededPoints' => $this->neededPoints,
			'avatar' => $this->avatar,
			'avatarID' => $this->avatarID,
			'avatarCategoryID' => $this->avatarCategoryID,
			'availableAvatarCategories' => AvatarCategory::getAvatarCategories()
		));
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
			$this->groupID = $this->avatar->groupID;
			$this->neededPoints = $this->avatar->neededPoints;
			$this->avatarCategoryID = $this->avatar->avatarCategoryID;
		}
	}
}
?>