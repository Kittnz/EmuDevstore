<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/AvatarEditor.class.php');
require_once(WCF_DIR.'lib/data/user/avatar/category/AvatarCategory.class.php');
require_once(WCF_DIR.'lib/data/user/group/Group.class.php');

/**
 * Adds the avatar select to user edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.user.avatar
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class UserEditFormAvatarListener implements EventListener {
	public $avatarID = 0;
	public $disableAvatar = 0;
	public $disableAvatarReason = '';
	public $avatarUpload;
	public $avatarURL = 'http://';
	public $useAvatar = 0;
	public $gravatar = '';
	
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_AVATAR == 1) {
			if ($eventName == 'readFormParameters') {
				if (isset($_POST['avatarID'])) $this->avatarID = intval($_POST['avatarID']);
				if (isset($_POST['disableAvatar'])) $this->disableAvatar = intval($_POST['disableAvatar']);
				if (isset($_POST['disableAvatarReason'])) $this->disableAvatarReason = $_POST['disableAvatarReason'];
				if (isset($_POST['useAvatar'])) $this->useAvatar = intval($_POST['useAvatar']);
				if (isset($_POST['avatarURL'])) $this->avatarURL = StringUtil::trim($_POST['avatarURL']);
				if (isset($_FILES['avatarUpload'])) $this->avatarUpload = $_FILES['avatarUpload'];
				if (MODULE_GRAVATAR == 1 && isset($_POST['gravatar'])) $this->gravatar = StringUtil::trim($_POST['gravatar']);
			}
			else if ($eventName == 'validate') {
				try {
					if ($this->useAvatar == 1) {
						if (empty($this->gravatar)) {
							// upload or download avatar
							if ($this->avatarUpload && $this->avatarUpload['error'] != 4) {
								if ($this->avatarUpload['error'] != 0) {
									throw new UserInputException('avatarUpload', 'uploadFailed');
								}
							
								$this->avatarID = AvatarEditor::create($this->avatarUpload['tmp_name'], $this->avatarUpload['name'], 'avatarUpload', $eventObj->userID);
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
								
								$this->avatarID = AvatarEditor::create($tmpName, $this->avatarURL, 'avatarURL', $eventObj->userID);
							}
							else {
								$this->avatarID = $eventObj->user->avatarID;
							}
						}
					}
					else if ($this->useAvatar == 2) {
						// use a default avatar
						$avatar = new AvatarEditor($this->avatarID);
						
						if (!$avatar->avatarID || $avatar->userID || ($avatar->groupID && !in_array($avatar->groupID, $eventObj->user->getGroupIDs())) || $avatar->neededPoints > $eventObj->user->activityPoints) {
							throw new UserInputException('availableAvatars', 'invalid');
						}
					}
					else {
						$this->avatarID = 0;
					}
				}
				catch (UserInputException $e) {
					$eventObj->errorType[$e->getField()] = $e->getType();
				}
			}
			else if ($eventName == 'save') {
				// delete old avatar if necessary
				if ($eventObj->user->avatarID) {
					$currentAvatar = new AvatarEditor($eventObj->user->avatarID);
					if ($currentAvatar->userID && $this->avatarID != $currentAvatar->avatarID) $currentAvatar->delete();
				}
				
				// update user
				$eventObj->additionalFields['avatarID'] = $this->avatarID;
				$eventObj->additionalFields['disableAvatar'] = $this->disableAvatar;
				$eventObj->additionalFields['disableAvatarReason'] = $this->disableAvatarReason;
				$eventObj->additionalFields['gravatar'] = $this->gravatar;
			}
			else if ($eventName == 'show') {
				// get default values
				if (!count($_POST)) {
					$this->avatarID = $eventObj->user->avatarID;
					$this->disableAvatar = $eventObj->user->disableAvatar;
					$this->disableAvatarReason = $eventObj->user->disableAvatarReason;
					$this->gravatar = $eventObj->user->gravatar;
				}
					
				$currentAvatar = null;
				if ($this->avatarID) {
					$currentAvatar = new AvatarEditor($this->avatarID);
					$this->useAvatar = ($currentAvatar->userID ? 1 : 2);
				}
				else if ($this->gravatar) {
					require_once(WCF_DIR.'lib/data/user/avatar/Gravatar.class.php');
					$currentAvatar = new Gravatar($this->gravatar);
					$this->useAvatar = 1;
				}
					
				$availableAvatarCategories = $this->getAvailableAvatars(implode(',', $eventObj->user->getGroupIDs()), intval($eventObj->user->activityPoints));
				$avatarCount = 0;
				foreach ($availableAvatarCategories as $availableAvatarCategory) $avatarCount += count($availableAvatarCategory['avatars']);
				WCF::getTPL()->assign(array(
					'avatarID' => $this->avatarID,
					'disableAvatar' => $this->disableAvatar,
					'disableAvatarReason' => $this->disableAvatarReason,
					'avatarURL' => $this->avatarURL,
					'currentAvatar' => $currentAvatar,
					'avatarCategories' => $availableAvatarCategories,
					'items' => $avatarCount,
					'useAvatar' => $this->useAvatar,
					'gravatar' => $this->gravatar
				));
				WCF::getTPL()->append(array(
					'additionalTabs' => '<li id="avatar"><a onclick="tabMenu.showSubTabMenu(\'avatar\');"><span>'.WCF::getLanguage()->get('wcf.user.avatar').'</span></a></li>',
					'additionalTabContents' => WCF::getTPL()->fetch('userEditAvatar')
				));
			}
		}
	}
	
	/**
	 * Returns a list of available avatars.
	 * 
	 * @return	array
	 */
	protected function getAvailableAvatars($groupIDs, $activityPoints) {
		// get avatar categories
		$avatarCategories = array(0 => array('category' => null, 'avatars' => array()));
		foreach (AvatarCategory::getAvatarCategories() as $key => $object) {
			$avatarCategories[$key] = array('category' => $object, 'avatars' => array());
		}
		
		// get avatars
		$i = 0;
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_avatar
			WHERE		userID = 0
					AND groupID IN (0,".$groupIDs.") AND neededPoints <= ".$activityPoints."
			ORDER BY	avatarName, avatarID";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (isset($avatarCategories[$row['avatarCategoryID']])) {
				$avatarCategories[$row['avatarCategoryID']]['avatars'][$row['avatarID']] = new Avatar(null, $row);
				$i++;
			}
		}
		
		if ($i == 0) return array();
		return $avatarCategories;
	}
}
?>