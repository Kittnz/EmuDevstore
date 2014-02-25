<?php
// wcf imports
require_once(WCF_DIR.'lib/form/AbstractForm.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');

/**
 * Shows the user activation form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	form
 * @category 	Community Framework
 */
class RegisterActivationForm extends AbstractForm {
	
	/**
	 * user id
	 *
	 * @var integer
	 */
	public $userID = null;
	
	/**
	 * activation code
	 *
	 * @var integer
	 */
	public $activationCode = '';
	
	/**
	 * user object
	 * 
	 *  @var UserEditor
	 */
	public $user;
	
	/**
	 * @see AbstractPage::$templateName
	 */
	public $templateName = 'registerActivation';
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_GET['u']) && !empty($_GET['u'])) $this->userID = intval($_GET['u']);
		if (isset($_GET['a']) && !empty($_GET['a'])) $this->activationCode = intval($_GET['a']);
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['u']) && !empty($_POST['u'])) $this->userID = intval($_POST['u']);
		if (isset($_POST['a']) && !empty($_POST['a'])) $this->activationCode = intval($_POST['a']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		// check given user id
		require_once(WCF_DIR.'lib/system/session/UserSession.class.php');
		$this->user = new UserEditor($this->userID);
		if (!$this->user->userID) {
			throw new UserInputException('u', 'notValid');
		}
		
		// user is already enabled
		if ($this->user->activationCode == 0) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.user.register.error.userAlreadyEnabled'));
		}
		
		// check given activation code
		if ($this->user->activationCode != $this->activationCode) {
			throw new UserInputException('a', 'notValid');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();

		// enable user
		// update activation code
		$this->additionalFields['activationCode'] = 0;
		$this->user->update('', '', '', null, null, $this->additionalFields);
		
		// remove user from guest group
		$this->user->removeFromGroup(Group::getGroupIdByType(Group::GUESTS));
		
		// add user to default users group
		$this->user->addToGroup(Group::getGroupIdByType(Group::USERS));
		
		// reset session
		Session::resetSessions($this->user->userID, true, false);
		$this->saved();
		
		// forward to login page
		WCF::getTPL()->assign(array(
			'url' => 'index.php'.SID_ARG_1ST,
			'message' => WCF::getLanguage()->get('wcf.user.register.activation.redirect')
		));
		WCF::getTPL()->display('redirect');
		exit;
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'u' => $this->userID,
			'a' => $this->activationCode
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if (!count($_POST) && $this->userID !== null && $this->activationCode != 0) {
			$this->submit();
		}
		
		parent::show();
	}
}
?>