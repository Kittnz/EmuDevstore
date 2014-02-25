<?php
// wcf imports
require_once(WCF_DIR.'lib/form/CaptchaForm.class.php');

/**
 * Shows the user mail form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.page.user.profile
 * @subpackage	form
 * @category 	Community Framework
 */
class MailForm extends CaptchaForm {
	public $useCaptcha = PROFILE_MAIL_USE_CAPTCHA;
	public $userID;
	public $user;
	public $showAddress = 1;
	public $subject = '';
	public $message = '';
	public $email = '';
	public $templateName = 'mail';

	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['message'])) $this->message = StringUtil::trim($_POST['message']);
		if (isset($_POST['subject'])) $this->subject = StringUtil::trim($_POST['subject']);
		if (isset($_POST['email'])) $this->email = StringUtil::trim($_POST['email']);
		if (isset($_POST['showAddress'])) $this->showAddress = intval($_POST['showAddress']);
		else $this->showAddress = 0;
	}

	/**
	 * @see Form::validate()
	 */
	public function validate() {
		if (!WCF::getUser()->userID) {
			if (empty($this->email)) {
				throw new UserInputException('email');
			}
			
			if (!UserUtil::isValidEmail($this->email)) {
				throw new UserInputException('email', 'notValid');
			}
		}
		
		if (empty($this->subject)) {
			throw new UserInputException('subject');
		}
		
		if (empty($this->message)) {
			throw new UserInputException('message');
		}
		
		parent::validate();
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
		
		// build message data
		$subjectData = array(
			'$username' => WCF::getUser()->userID ? WCF::getUser()->username : $this->email,
			'$subject' => $this->subject
		);
		$messageData = array(
			'$message' => $this->message,
			'PAGE_TITLE' => $languages[$this->user->languageID]->get(PAGE_TITLE),
			'$recipient' => $this->user->username,
			'$username' => WCF::getUser()->userID ? WCF::getUser()->username : $this->email,
			'PAGE_URL' => PAGE_URL
		);
		
		// build mail
		require_once(WCF_DIR.'lib/data/mail/Mail.class.php');
		$mail = new Mail(array($this->user->username => $this->user->email), $languages[$this->user->languageID]->get('wcf.user.mail.mail.subject', $subjectData), $languages[$this->user->languageID]->get('wcf.user.mail.mail', $messageData));
		
		// add reply-to tag
		if (WCF::getUser()->userID) {
			if ($this->showAddress) $mail->setHeader('Reply-To: '.Mail::buildAddress(WCF::getUser()->username, WCF::getUser()->email));
		}
		else $mail->setHeader('Reply-To: '.$this->email);
		
		// send mail
		$mail->send();
		$this->saved();
		
		// enable user language
		WCF::getLanguage()->setLocale();
				
		// forward to profile page
		WCF::getTPL()->assign(array(
			'url' => 'index.php?page=User&userID='.$this->userID.SID_ARG_2ND_NOT_ENCODED,
			'message' => WCF::getLanguage()->get('wcf.user.mail.sent', array('$username' => StringUtil::encodeHTML($this->user->username)))
		));
		WCF::getTPL()->display('redirect');
		exit;
	}

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get user
		if (isset($_REQUEST['userID'])) $this->userID = intval($_REQUEST['userID']);
		require_once(WCF_DIR.'lib/data/user/UserProfile.class.php');
		$this->user = new UserProfile($this->userID);
		if (!$this->user->userID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'user' => $this->user,
			'showAddress' => $this->showAddress,
			'message' => $this->message,
			'subject' => $this->subject,
			'email' => $this->email
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		WCF::getUser()->checkPermission('user.mail.canMail');
		
		// can mail permission
		if (!$this->user->canMail()) {
			throw new PermissionDeniedException();
		}
		
		parent::show();
	}
}
?>