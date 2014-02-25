<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/action/UserActivationMailAction.class.php');

/**
 * Sends new password mail.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class UserNewPasswordMailAction extends UserActivationMailAction {
	public $action = 'UserNewPasswordMail';
	
	/**
	 * Sends the mail to given user.
	 * 
	 * @param	User		$user
	 */
	protected function sendMail(User $user) {
		// generate new password
		$password = substr(StringUtil::getRandomID(), 0, 10);
		
		// save
		$editor = $user->getEditor();
		$editor->update('', '', $password);
		
		// send mail
		if (!isset($this->languages[$user->languageID])) {
			$this->languages[$user->languageID] = new Language($user->languageID);
		}
		
		$mail = new Mail(array($user->username => $user->email),
					$this->languages[$user->languageID]->get('wcf.acp.user.newPassword.mail.subject', array('PAGE_TITLE' => $this->languages[$user->languageID]->get(PAGE_TITLE))),
					$this->languages[$user->languageID]->get('wcf.acp.user.newPassword.mail',
						array('PAGE_TITLE' => $this->languages[$user->languageID]->get(PAGE_TITLE), '$username' => $user->username, '$password' => $password, 'PAGE_URL' => PAGE_URL, 'MAIL_ADMIN_ADDRESS' => MAIL_ADMIN_ADDRESS)));
		$mail->send();
	}
}
?>