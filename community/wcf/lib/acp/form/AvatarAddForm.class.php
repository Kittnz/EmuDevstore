<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/AvatarEditor.class.php');
require_once(WCF_DIR.'lib/system/io/Tar.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/category/AvatarCategory.class.php');

/**
 * Shows the avatar add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.avatar
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class AvatarAddForm extends ACPForm {
	public $templateName = 'avatarAdd';
	public $activeMenuItem = 'wcf.acp.menu.link.avatar.add';
	public $neededPermissions = 'admin.avatar.canAddAvatar';
		
	public $groupID = 0;
	public $neededPoints = 0;
	public $upload;
	public $filename = '';
	public $groups = array();
	public $avatarIDs = array();
	public $avatarCategoryID = 0;
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['groupID'])) $this->groupID = intval($_POST['groupID']);
		if (isset($_POST['neededPoints'])) $this->neededPoints = intval($_POST['neededPoints']);
		if (isset($_FILES['upload'])) $this->upload = $_FILES['upload'];
		if (isset($_POST['filename'])) $this->filename = StringUtil::trim($_POST['filename']);
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
		
		$savedAvatars = 0;
		WCF::getTPL()->assignByRef('savedAvatars', $savedAvatars);
		
		// upload avatar(s)
		if ($this->upload && $this->upload['error'] != 4) {
			if ($this->upload['error'] != 0) {
				throw new UserInputException('upload', 'uploadFailed');
			}
			
			// try to open file as an archive
			if (preg_match('/(?:tar\.gz|tgz|tar)$/i', $this->upload['name'])) {
				$errors = array();
				$tar = new Tar($this->upload['tmp_name']);
				foreach ($tar->getContentList() as $file) {
					if ($file['type'] != 'folder') {
						// extract to tmp dir
						$tmpname = FileUtil::getTemporaryFilename('avatar_');
						$tar->extract($file['index'], $tmpname);
						
						try {
							$this->avatarIDs[] = AvatarEditor::create($tmpname, $file['filename'], 'upload', 0, $this->groupID, $this->neededPoints, $this->avatarCategoryID);
							$savedAvatars++;
						}
						catch (UserInputException $e) {
							$errors[] = array('filename' => $file['filename'], 'errorType' => $e->getType());
						}
					}
				}
				$tar->close();
				@unlink($this->upload['tmp_name']);
				
				if (count($errors)) {
					throw new UserInputException('upload', $errors);
				}
				else if ($savedAvatars == 0) {
					throw new UserInputException('upload', 'emptyArchive');
				}
			}
			else {
				// import as image file
				$this->avatarIDs[] = AvatarEditor::create($this->upload['tmp_name'], $this->upload['name'], 'upload', 0, $this->groupID, $this->neededPoints, $this->avatarCategoryID);
				$savedAvatars++;
			}
		}
		// copy avatars
		else if (!empty($this->filename)) {
			if (!file_exists($this->filename)) {
				throw new UserInputException('filename', 'notFound');
			}
			
			// copy avatars from a dir
			if (is_dir($this->filename)) {
				$errors = array();
				$this->filename = FileUtil::addTrailingSlash($this->filename);
				$handle = opendir($this->filename);
				while (($file = readdir($handle)) !== false) {
					if ($file != '.' && $file != '..' && is_file($this->filename . $file)) {
						try {
							$this->avatarIDs[] = AvatarEditor::create($this->filename . $file, $this->filename . $file, 'filename', 0, $this->groupID, $this->neededPoints, $this->avatarCategoryID);
							$savedAvatars++;
						}
						catch (UserInputException $e) {
							$errors[] = array('filename' => $this->filename . $file, 'errorType' => $e->getType());
						}
					}
				}
				
				if (count($errors)) {
					throw new UserInputException('filename', $errors);
				}
				else if ($savedAvatars == 0) {
					throw new UserInputException('filename', 'emptyFolder');
				}
			}
			// simple file name
			else {
				$this->avatarIDs[] = AvatarEditor::create($this->filename, $this->filename, 'filename', 0, $this->groupID, $this->neededPoints, $this->avatarCategoryID);
				$savedAvatars++;
			}
		}
		else {
			throw new UserInputException('upload');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();

		// reset values
		$this->groupID = Group::getGroupIdByType(Group::USERS);
		$this->neededPoints = $this->avatarCategoryID = 0;
		$this->filename = WCF_DIR;
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
			$this->filename = WCF_DIR;
		}
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
			'filename' => $this->filename,
			'avatarCategoryID' => $this->avatarCategoryID,
			'availableAvatarCategories' => AvatarCategory::getAvatarCategories()
		));
	}
}
?>