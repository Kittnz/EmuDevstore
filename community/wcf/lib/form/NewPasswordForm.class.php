<?php
// wcf imports
require_once(WCF_DIR.'lib/form/AbstractForm.class.php');

/**
 * Shows the new password form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	form
 * @category 	Community Framework
 */
class NewPasswordForm extends AbstractForm {
	public $userID = 0;
	public $lostPasswordKey = '';
	public $user;
	public $newPassword = '';
	public $templateName = 'newPassword';
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['u'])) $this->userID = intval($_REQUEST['u']);
		if (isset($_REQUEST['k'])) $this->lostPasswordKey = StringUtil::trim($_REQUEST['k']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		// get user
		require_once(WCF_DIR.'lib/data/user/UserEditor.class.php');
		$this->user = new UserEditor($this->userID);
		
		if (!$this->user->userID) {
			throw new UserInputException('userID', 'invalid');
		}
		if (!$this->user->lostPasswordKey) {
			throw new UserInputException('lostPasswordKey');
		}
		
		if ($this->user->lostPasswordKey != $this->lostPasswordKey) {
			throw new UserInputException('lostPasswordKey', 'invalid');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// generate new password
		$this->newPassword = UserRegistrationUtil::getNewPassword((REGISTER_PASSWORD_MIN_LENGTH > 9 ? REGISTER_PASSWORD_MIN_LENGTH : 9));
		
		// update user
		$this->user->update('', '', $this->newPassword, null, null, array('lastLostPasswordRequest' => 0, 'lostPasswordKey' => ''));
		
		// send mail
		$subjectData = array('PAGE_TITLE' => WCF::getLanguage()->get(PAGE_TITLE));
		$messageData = array(
			'PAGE_TITLE' => WCF::getLanguage()->get(PAGE_TITLE),
			'$username' => $this->user->username,
			'$userID' => $this->user->userID,
			'$newPassword' => $this->newPassword,
			'PAGE_URL' => PAGE_URL,
			'MAIL_ADMIN_ADDRESS' => MAIL_ADMIN_ADDRESS
		);
		require_once(WCF_DIR.'lib/data/mail/Mail.class.php');
		$mail = new Mail(array($this->user->username => $this->user->email), WCF::getLanguage()->get('wcf.user.lostPassword.newPassword.mail.subject', $subjectData), WCF::getLanguage()->get('wcf.user.lostPassword.newPassword.mail', $messageData));
		$mail->send();
		$this->saved();
		
		// show result page
		WCF::getTPL()->assign(array(
			'url' => 'index.php'.SID_ARG_1ST,
			'message' => WCF::getLanguage()->get('wcf.user.lostPassword.success')
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
			'userID' => $this->userID,
			'lostPasswordKey' => $this->lostPasswordKey
		));
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		AbstractPage::readData();
		
		if (count($_POST) || (!empty($this->userID) && !empty($this->lostPasswordKey))) {
			$this->submit();
		}
	}
}
?>