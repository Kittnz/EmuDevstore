<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/action/UserMailAction.class.php');

/**
 * Sends activation mail.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.user
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class UserActivationMailAction extends UserMailAction {
	public $action = 'UserActivationMail';
	public $languages = array();
	
	/**
	 * Creates a new UserActivationMailAction object.
	 */
	public function __construct() {
		$this->languages[WCF::getLanguage()->getLanguageID()] = WCF::getLanguage();
		$this->languages[0] = WCF::getLanguage();
		parent::__construct();
	}
	
	/**
	 * Sends the mail to given user.
	 * 
	 * @param	User		$user
	 */
	protected function sendMail(User $user) {
		if (!$user->activationCode) return;
		
		if (!isset($this->languages[$user->languageID])) {
			$this->languages[$user->languageID] = new Language($user->languageID);
		}
		
		$mail = new Mail(array($user->username => $user->email),
					$this->languages[$user->languageID]->get('wcf.user.register.needActivation.mail.subject', array('PAGE_TITLE' => $this->languages[$user->languageID]->get(PAGE_TITLE))),
					$this->languages[$user->languageID]->get('wcf.user.register.needActivation.mail',
						array('PAGE_TITLE' => $this->languages[$user->languageID]->get(PAGE_TITLE), '$username' => $user->username, '$userID' => $user->userID, '$activationCode' => $user->activationCode, 'PAGE_URL' => PAGE_URL, 'MAIL_ADMIN_ADDRESS' => MAIL_ADMIN_ADDRESS)));
		$mail->send();
	}
}
?>