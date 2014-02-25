<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/UserAddForm.class.php');

/**
 * Shows the user edit form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class UserEditForm extends UserAddForm {
	public $menuItemName = 'wcf.acp.menu.link.user.management';
	public $permission = 'admin.user.canEditUser';
	
	public $userID = 0;
	public $url = '';
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['userID'])) {
			$this->userID = intval($_REQUEST['userID']);
			require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
			$this->user = new UserEditor($this->userID);
			if (!$this->user->userID) {
				throw new IllegalLinkException();
			}
			if (!Group::isAccessibleGroup($this->user->getGroupIDs())) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @see Page::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (!WCF::getUser()->getPermission('admin.user.canEditPassword')) $this->password = $this->confirmPassword = '';
		if (!WCF::getUser()->getPermission('admin.user.canEditMailAddress')) $this->email = $this->confirmEmail = $this->user->email;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		if (!count($_POST)) {
			// get visible languages
			$this->readVisibleLanguages();
			
			// default values
			$this->readDefaultValues();
		}
		
		parent::readData();
		
		$this->url = 'index.php?form=UserEdit&userID='.$this->user->userID.'&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED;
	}
	
	/**
	 * Gets the selected languages.
	 */
	protected function readVisibleLanguages() {
		$sql = "SELECT	languageID
			FROM	wcf".WCF_N."_user_to_languages
			WHERE	userID = ".$this->user->userID;
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->visibleLanguages[] = $row['languageID'];
		}
	}
	
	/**
	 * Gets the default values.
	 */
	protected function readDefaultValues() {
		$this->username = $this->user->username;
		$this->email = $this->confirmEmail = $this->user->email;
		$this->groupIDs = $this->user->getGroupIDs();
		$this->languageID = $this->user->languageID;
		
		foreach ($this->activeOptions as $key => $option) {
			$value = $this->user->{'userOption'.$option['optionID']};
			if ($value !== null) {
				$this->activeOptions[$key]['optionValue'] = $value;
			}
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'userID' => $this->user->userID,
			'action' => 'edit',
			'url' => $this->url,
			'markedUsers' => 0,
			'user' => $this->user
		));
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// add default groups
		$defaultGroups = Group::getAccessibleGroups(array(Group::GUESTS, Group::EVERYONE, Group::USERS));
		$oldGroupIDArray = $this->user->getGroupIDs();
		foreach ($oldGroupIDArray as $oldGroupID) {
			if (isset($defaultGroups[$oldGroupID])) {
				$this->groupIDs[] = $oldGroupID;
			}
		}
		$this->groupIDs = array_unique($this->groupIDs);
		
		// save user
		$this->additionalFields['languageID'] = $this->languageID;
		$this->user->update($this->username, $this->email, $this->password, null, $this->activeOptions, $this->additionalFields, $this->visibleLanguages);
		$this->user->addToGroups($this->groupIDs, true, false);
		$this->saved();
		
		// reset password
		$this->password = $this->confirmPassword = '';
	
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see UserAddForm::validateUsername()
	 */
	protected function validateUsername($username) {
		if (StringUtil::toLowerCase($this->user->username) != StringUtil::toLowerCase($username)) {
			parent::validateUsername($username);
		}
	}
	
	/**
	 * @see UserAddForm::validateEmail()
	 */
	protected function validateEmail($email, $confirmEmail) {
		if (StringUtil::toLowerCase($this->user->email) != StringUtil::toLowerCase($email)) {
			parent::validateEmail($email, $this->confirmEmail);	
		}
	}
	
	/**
	 * @see UserAddForm::validatePassword()
	 */
	protected function validatePassword($password, $confirmPassword) {
		if (!empty($password) || !empty($confirmPassword)) {
			parent::validatePassword($password, $confirmPassword);
		}
	}
}
?>