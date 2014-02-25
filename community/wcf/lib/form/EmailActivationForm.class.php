<?php
// wcf imports
require_once(WCF_DIR.'lib/form/RegisterActivationForm.class.php');
require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');

/**
 * Shows the email activation form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	form
 * @category 	Community Framework
 */
class EmailActivationForm extends RegisterActivationForm {
	public $templateName = 'emailActivation';
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		AbstractForm::validate();
		
		// check given user id
		$this->user = new UserEditor($this->userID);
		if (!$this->user->userID) {
			throw new UserInputException('u', 'notValid');
		}
		
		// user is already enabled
		if ($this->user->reactivationCode == 0) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.user.emailChange.error.emailAlreadyEnabled'));
		}
		
		// check whether the new email isn't unique anymore
		if (!UserUtil::isAvailableEmail($this->user->newEmail)) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.user.emailChange.error.email.notUnique'));
		}
		
		// check given activation code
		if ($this->user->reactivationCode != $this->activationCode) {
			throw new UserInputException('a', 'notValid');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();
		
		// enable new email
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	email = newEmail,
				newEmail = '',
				reactivationCode = 0
			WHERE	userID = ".$this->userID;
		WCF::getDB()->sendQuery($sql);
		
		// reset session
		WCF::getSession()->resetUserData();
		$this->saved();
		
		// forward to index page
		WCF::getTPL()->assign(array(
			'url' => 'index.php'.SID_ARG_1ST,
			'message' => WCF::getLanguage()->get('wcf.user.emailChange.reactivation.success')
		));
		WCF::getTPL()->display('redirect');
		exit;
	}
}
?>