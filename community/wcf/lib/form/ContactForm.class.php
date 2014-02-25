<?php
// wcf imports
require_once(WCF_DIR.'lib/form/CaptchaForm.class.php');

/**
 * Shows the user mail form.
 *
 * @author		Jean-Marc Licht
 * @copyright	2011 web-produktion
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.web-produktion.contact
 * @subpackage	form
 * @category 	Community Framework
 */
class ContactForm extends CaptchaForm {
	public $useCaptcha = PROFILE_MAIL_USE_CAPTCHA;
	public $userID = MESSAGE_CONTACT_ADMIN_ID;
	public $subject = '';
	public $username = '';
	public $message = '';
	public $email = '';
	public $templateName = 'contact';
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['username'])) $this->username = StringUtil::trim($_POST['username']);
		if (isset($_POST['message'])) $this->message = StringUtil::trim($_POST['message']);
		if (isset($_POST['subject'])) $this->subject = StringUtil::trim($_POST['subject']);
		if (isset($_POST['email'])) $this->email = StringUtil::trim($_POST['email']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		
		// check valide email from guest
		if (!WCF::getUser()->userID) {
			if (empty($this->email)) {
				throw new UserInputException('email');
			}
			
			if (!UserUtil::isValidEmail($this->email)) {
				throw new UserInputException('email', 'notValid');
			}
			
			// check empty username
			if (empty($this->username)) {
				throw new UserInputException('username');
			}
		}
		
		// check empty subject
		if (empty($this->subject)) {
			throw new UserInputException('subject');
		}
		
		// check empty message
		if (empty($this->message)) {
			throw new UserInputException('message');
		}
		
		parent::validate();
	}
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		// get user
		require_once(WCF_DIR.'lib/data/user/UserProfile.class.php');
		$this->user = new UserProfile($this->userID);
		if (!$this->user->userID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// enable recipient language
		$languages = array(0 => WCF::getLanguage(), WCF::getLanguage()->getLanguageID() => WCF::getLanguage());
		if (!isset($languages[$this->user->languageID])) $languages[$this->user->languageID] = new Language($this->user->languageID);	
		$languages[$this->user->languageID]->setLocale();
		
		// build subject data
		$subjectData = array(
			'$username' => WCF::getUser()->userID ? WCF::getUser()->username : $this->username,
			'$subject' => $this->subject,
			'PAGE_TITLE' => $languages[$this->user->languageID]->get(PAGE_TITLE),
		);
		
		// build message data
		$messageData = array(
			'$name' => WCF::getUser()->userID ? WCF::getUser()->username : $this->username,
			'$subject' => $this->subject,
			'$message' => $this->message,
			'PAGE_TITLE' => $languages[$this->user->languageID]->get(PAGE_TITLE),
			'$recipient' => $this->user->username,
			'$username' => WCF::getUser()->userID ? WCF::getUser()->username : $this->email,
			'PAGE_URL' => PAGE_URL,
			'$email' => WCF::getUser()->userID ? WCF::getUser()->email : $this->email
		);
		
		// build mail
		require_once(WCF_DIR.'lib/data/mail/Mail.class.php');
		$mail = new Mail(array($this->user->username => $this->user->email), 
							$languages[$this->user->languageID]->get('wcf.contact.mail.subject', $subjectData), 
							$languages[$this->user->languageID]->get('wcf.contact.mail.message', $messageData));
	
		// add reply-to tag
		if (WCF::getUser()->userID) {
			$mail->setHeader('Reply-To: '.Mail::buildAddress(WCF::getUser()->username, WCF::getUser()->email));
		}
		else $mail->setHeader('Reply-To: '.$this->email);
		
		// send mail
		$mail->send();
		$this->saved();
		
		// enable user language
		WCF::getLanguage()->setLocale();
		
		// redirect
		WCF::getTPL()->assign(array(
			'url' => 'index.php'.SID_ARG_2ND_NOT_ENCODED,
			'message' => WCF::getLanguage()->get('wcf.contact.mailvers', array('$username' => StringUtil::encodeHTML($this->user->username))),
			'wait' => 5
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
			'username' => $this->username,
			'message' => $this->message,
			'subject' => $this->subject,
			'allowSpidersToIndexThisPage' => true,
			'email' => $this->email
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// check aktiv modul
		if (!MODULE_CONTACT) {
			throw new IllegalLinkException();
		}
		// set active header menu item
		require_once(WCF_DIR.'lib/page/util/menu/PageMenu.class.php');
		PageMenu::setActiveMenuItem('wcf.header.menu.contact');
		
		parent::show();
	}
}
?>