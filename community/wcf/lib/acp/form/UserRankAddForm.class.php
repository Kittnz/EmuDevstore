<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/user/rank/UserRankEditor.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');

/**
 * Shows the user rank add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.rank
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class UserRankAddForm extends ACPForm {
	public $templateName = 'userRankAdd';
	public $activeMenuItem = 'wcf.acp.menu.link.user.rank.add';
	public $neededPermissions = 'admin.user.rank.canAddRank';
	
	public $groupID = 0;
	public $neededPoints = 0;
	public $title = '';
	public $image = '';
	public $gender = 0;
	public $repeatImage = 1;
	public $groups = array();
	public $rankID = 0;
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['groupID'])) $this->groupID = intval($_POST['groupID']);
		if (isset($_POST['neededPoints'])) $this->neededPoints = intval($_POST['neededPoints']);
		if (isset($_POST['title'])) $this->title = StringUtil::trim($_POST['title']);
		if (isset($_POST['image'])) $this->image = StringUtil::trim($_POST['image']);
		if (isset($_POST['gender'])) $this->gender = intval($_POST['gender']);
		if (isset($_POST['repeatImage'])) $this->repeatImage = intval($_POST['repeatImage']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		// title
		if (empty($this->title)) {
			throw new UserInputException('title');
		}
		
		// validate group id
		$group = new Group($this->groupID);
		if (!$group->groupID) {
			throw new UserInputException('groupID');
		}
		
		// gender
		if ($this->gender < 0 || $this->gender > 2) {
			throw new UserInputException('gender');
		}
		
		// repeat
		if (!empty($this->image) && $this->repeatImage < 1) {
			throw new UserInputException('repeatImage');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		$this->rankID = UserRankEditor::create($this->title, $this->image, $this->groupID, $this->neededPoints, $this->gender, $this->repeatImage);
		$this->saved();
		
		// reset values
		$this->groupID = Group::getGroupIdByType(Group::USERS);
		$this->neededPoints = $this->gender = 0;
		$this->repeatImage = 1;
		$this->title = $this->image = '';
		
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
			'gender' => $this->gender,
			'title' => $this->title,
			'image' => $this->image,
			'repeatImage' => $this->repeatImage,
			'action' => 'add'
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
			$this->groupID = Group::getGroupIdByType(Group::USERS);
		}
	}
}
?>