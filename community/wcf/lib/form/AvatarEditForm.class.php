<?php
// wcf imports
require_once(WCF_DIR.'lib/form/AbstractForm.class.php');
require_once(WCF_DIR.'lib/page/AvatarListPage.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/Gravatar.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/AvatarEditor.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/category/AvatarCategory.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');

/**
 * Shows the avatar edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	form
 * @category 	Community Framework
 */
class AvatarEditForm extends AbstractForm {
	// system
	public $templateName = 'avatarEdit';
	
	/**
	 * selected avatar id
	 * 
	 * @var	integer
	 */
	public $avatarID = 0;
	
	/**
	 * avatar upload
	 * 
	 * @var	array
	 */
	public $avatarUpload = null;
	
	/**
	 * avatar download url
	 * 
	 * @var	string
	 */
	public $avatarURL = 'http://';
	
	/**
	 * gravatar
	 * 
	 * @var	string
	 */
	public $gravatar = '';
	
	/**
	 * avatar object
	 * 
	 * @var	DisplayableAvatar
	 */
	public $avatar = null;
	
	/**
	 * avatar type
	 * 
	 * @var	string
	 */
	public $avatarType = 'none';
	
	/**
	 * new avatar id
	 * 
	 * @var	integer
	 */
	public $newAvatarID = 0;
	
	/**
	 * Type of page (can be 'user' or 'selected')
	 *
	 * @var string
	 */
	public $userAvatar = true;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (WCF::getUser()->avatarID) {
			$this->avatar = new AvatarEditor(WCF::getUser()->avatarID);
			$this->avatarType = ($this->avatar->userID ? 'user' : 'selected');
		}
		else if (MODULE_GRAVATAR == 1 && WCF::getUser()->gravatar) {
			$this->avatar = new Gravatar(WCF::getUser()->gravatar);
			$this->avatarType = 'gravatar';
		}
		
		if (!isset($_REQUEST['userAvatar']) && $this->avatarType == 'selected' || isset($_REQUEST['userAvatar']) && !$_REQUEST['userAvatar']) $this->userAvatar = false;
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		// check permissions
		if (WCF::getUser()->disableAvatar) {
			throw new PermissionDeniedException();
		}
		
		if (isset($_POST['avatarID'])) $this->avatarID = intval($_POST['avatarID']);
		else  $this->avatarID = -1;
		if (isset($_POST['avatarURL'])) $this->avatarURL = StringUtil::trim($_POST['avatarURL']);
		if (MODULE_GRAVATAR == 1 && $this->avatarID == -1 && isset($_POST['gravatar'])) $this->gravatar = StringUtil::trim($_POST['gravatar']);
		if (isset($_FILES['avatarUpload'])) $this->avatarUpload = $_FILES['avatarUpload'];
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// delete old user avatar if necessary
		if ($this->avatarType == 'user' && $this->avatarID != $this->avatar->avatarID) {
			$this->avatar->delete();
			$this->avatar = null;
		}
			
		// update user
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	avatarID = ".$this->avatarID.",
				gravatar = '".escapeString($this->gravatar)."'
			WHERE	userID = ".WCF::getUser()->userID;
		WCF::getDB()->registerShutdownUpdate($sql);
		
		// reset session
		WCF::getSession()->resetUserData();
		
		// show success message
		WCF::getTPL()->assign('success', true);
		
		// reset avatar url
		$this->avatarURL = 'http://';
		
		// get object of new avatar
		if ($this->avatarID) {
			$this->avatar = new AvatarEditor($this->avatarID);
			$this->avatarType = ($this->avatar->userID ? 'user' : 'selected');
			if ($this->avatarType != 'selected') $this->avatarID = 0;
		}
		else if (MODULE_GRAVATAR == 1 && $this->gravatar) {
			$this->avatar = new Gravatar($this->gravatar);
			$this->avatarType = 'gravatar';
		}
		else {
			$this->avatar = null;
			$this->avatarType = 'none';
		}
		
		$this->saved();
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		if ($this->avatarID) {
			if ($this->avatarID == -1) {
				if (empty($this->gravatar)) {
					// check permission
					WCF::getUser()->checkPermission('user.profile.avatar.canUploadAvatar');
					
					// upload or download avatar
					if ($this->avatarUpload && $this->avatarUpload['error'] != 4) {
						if ($this->avatarUpload['error'] != 0) {
							throw new UserInputException('avatarUpload', 'uploadFailed');
						}
					
						$this->avatarID = AvatarEditor::create($this->avatarUpload['tmp_name'], $this->avatarUpload['name'], 'avatarUpload', WCF::getUser()->userID);
					}
					else if ($this->avatarURL != 'http://') {
						if (StringUtil::indexOf($this->avatarURL, 'http://') !== 0) {
							throw new UserInputException('avatarURL', 'downloadFailed');
						}
						
						try {
							$tmpName = FileUtil::downloadFileFromHttp($this->avatarURL, 'avatar');
						}
						catch (SystemException $e) {
							throw new UserInputException('avatarURL', 'downloadFailed');
						}
						
						$this->avatarID = AvatarEditor::create($tmpName, $this->avatarURL, 'avatarURL', WCF::getUser()->userID);
					}
					else {
						throw new UserInputException('avatarUpload');
					}
				}
				else {
					$this->avatarID = 0;
				}
			}
			else {
				// check permission
				WCF::getUser()->checkPermission('user.profile.avatar.canUseDefaultAvatar');
				
				// use a default avatar
				$avatar = new AvatarEditor($this->avatarID);
				
				if (!$avatar->avatarID || $avatar->userID || ($avatar->groupID && !in_array($avatar->groupID, WCF::getUser()->getGroupIDs())) || $avatar->neededPoints > WCF::getUser()->activityPoints) {
					throw new UserInputException('availableAvatars', 'invalid');
				}
				
				// check category permissions
				if ($avatar->avatarCategoryID) {
					$category = new AvatarCategory($avatar->avatarCategoryID);
					if (($category->groupID && !in_array($category->groupID, WCF::getUser()->getGroupIDs())) || $category->neededPoints > WCF::getUser()->activityPoints) {
						throw new UserInputException('availableAvatars', 'invalid');
					}
				}
			}
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// default values
		if (!count($_POST)) {
			if ($this->avatarType == 'selected') {
				$this->avatarID = $this->avatar->avatarID;
			}
			if (MODULE_GRAVATAR == 1) {
				$this->gravatar = WCF::getUser()->gravatar;
			}
		}
		else {
			if ($this->avatarType == 'selected') {
				$this->userAvatar = false;		
			}
			else {
				$this->userAvatar = true;
			}
		}
		
		// init avatar list
		new AvatarListPage($this->avatarID);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'avatarURL' => $this->avatarURL,
			'currentAvatar' => $this->avatar,
			'avatarID' => $this->avatarID,
			'disableAvatar' => WCF::getUser()->disableAvatar,
			'gravatar' => $this->gravatar,
			'avatarType' => $this->avatarType,
			'userAvatar' => $this->userAvatar
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if (!WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		if (!MODULE_AVATAR) {
			throw new IllegalLinkException();
		}
		
		// check permission
		WCF::getUser()->checkPermission(array('user.profile.avatar.canUseDefaultAvatar', 'user.profile.avatar.canUploadAvatar'));
		
		// set active tab
		UserCPMenu::getInstance()->setActiveMenuItem('wcf.user.usercp.menu.link.profile.avatar');
		
		// show form
		parent::show();
	}
}
?>