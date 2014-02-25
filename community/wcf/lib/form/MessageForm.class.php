<?php
// wcf imports
require_once(WCF_DIR.'lib/form/CaptchaForm.class.php');
require_once(WCF_DIR.'lib/data/message/smiley/Smiley.class.php');
require_once(WCF_DIR.'lib/data/message/smiley/category/SmileyCategory.class.php');

/**
 * MessageForm is an abstract form implementation for a message with optional captcha suppport.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.form.message
 * @subpackage	form
 * @category 	Community Framework
 */
abstract class MessageForm extends CaptchaForm {
	public $useCaptcha = 0;
	public $showSmilies = true;
	public $showSettings = true;
	public $showAttachments = true;
	public $showPoll = true;
	public $showSignatureSetting = true;
	public $permissionType = 'message';
	public $maxTextLength = null;
	public $defaultSmileys = array();
	public $smileyCategories = array();
	public $wysiwygBBCodes = array();
	
	// form parameters
	public $subject = '';
	public $text = '';
	public $parseURL = 1;
	public $enableSmilies = 1;
	public $enableHtml = 0;
	public $enableBBCodes = 1;
	public $showSignature = 0;
	public $activeTab = '';
	public $messageTable = '';
	public $wysiwygEditorMode = 0;
	public $wysiwygEditorHeight = 200;
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['subject'])) 		$this->subject 		= StringUtil::trim($_POST['subject']);
		if (isset($_POST['text'])) 		$this->text 		= MessageUtil::stripCrap(StringUtil::trim($_POST['text']));
		if (isset($_POST['activeTab'])) 	$this->activeTab 	= $_POST['activeTab'];
		
		// wysiwyg
		if (isset($_POST['wysiwygEditorMode'])) $this->wysiwygEditorMode 	= intval($_POST['wysiwygEditorMode']);
		if (isset($_POST['wysiwygEditorHeight'])) $this->wysiwygEditorHeight 	= intval($_POST['wysiwygEditorHeight']);
		// settings
		$this->enableSmilies = $this->enableHtml = $this->enableBBCodes = $this->parseURL = $this->showSignature = 0;
		if (isset($_POST['parseURL'])) 		$this->parseURL 	= intval($_POST['parseURL']);
		if (isset($_POST['enableSmilies']))	$this->enableSmilies 	= intval($_POST['enableSmilies']);
		$this->enableSmilies = intval($this->enableSmilies && WCF::getUser()->getPermission('user.'.$this->permissionType.'.canUseSmilies'));
		if (isset($_POST['enableHtml'])) 	$this->enableHtml 	= intval($_POST['enableHtml']);
		$this->enableHtml = intval($this->enableHtml && WCF::getUser()->getPermission('user.'.$this->permissionType.'.canUseHtml'));
		if (isset($_POST['enableBBCodes'])) 	$this->enableBBCodes 	= intval($_POST['enableBBCodes']);
		$this->enableBBCodes = intval($this->enableBBCodes && WCF::getUser()->getPermission('user.'.$this->permissionType.'.canUseBBCodes'));
		if (isset($_POST['showSignature'])) 	$this->showSignature 	= intval($_POST['showSignature']);
		
		// stop shouting
		if (StringUtil::length($this->subject) >= MESSAGE_SUBJECT_STOP_SHOUTING && StringUtil::toUpperCase($this->subject) == $this->subject) {
			$this->subject = StringUtil::wordsToUpperCase(StringUtil::toLowerCase($this->subject));
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		// subject
		$this->validateSubject();
		
		// text
		$this->validateText();
		
		parent::validate();
	}
	
	/**
	 * Validates message subject.
	 */
	protected function validateSubject() {
		if (empty($this->subject)) {
			throw new UserInputException('subject');
		}
	}
	
	/**
	 * Validates message text.
	 */
	protected function validateText() {
		if (empty($this->text)) {
			throw new UserInputException('text');
		}
		
		// check text length
		if ($this->maxTextLength !== null && StringUtil::length($this->text) > $this->maxTextLength) {
			throw new UserInputException('text', 'tooLong');
		}
		
		// search for censored words
		if (ENABLE_CENSORSHIP) {
			require_once(WCF_DIR.'lib/data/message/censorship/Censorship.class.php');
			$result = Censorship::test($this->text);
			if ($result) {
				WCF::getTPL()->assign('censoredWords', $result);
				throw new UserInputException('text', 'censoredWordsFound');
			}
		}
	}
	
	/**
	 * Does the flood control.
	 * Searches for messages of the active user in the last seconds.
	 */
	protected function doFloodControl() {
		if (empty($this->messageTable) || !WCF::getUser()->getPermission('user.message.floodControlTime')) {
			return;
		}
		
		$sql = "SELECT		time
			FROM		".$this->messageTable."
			WHERE		".(WCF::getUser()->userID ? "userID = ".WCF::getUser()->userID : "ipAddress = '".escapeString(WCF::getSession()->ipAddress)."'")."
					AND time > ".(TIME_NOW - WCF::getUser()->getPermission('user.message.floodControlTime'))."
			ORDER BY	time DESC";
		$row = WCF::getDB()->getFirstRow($sql);
		if (isset($row['time'])) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.message.error.floodControl', array(
				'$waitingTime' => $row['time'] - (TIME_NOW - WCF::getUser()->getPermission('user.message.floodControlTime')),
				'$floodControlTime' => WCF::getUser()->getPermission('user.message.floodControlTime'))));
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// parse URLs
		if ($this->parseURL == 1) {
			require_once(WCF_DIR.'lib/data/message/bbcode/URLParser.class.php');
			$this->text = URLParser::parse($this->text);
		}
		
		$this->saveOptions();
	}
	
	/**
	 * Saves the message form options for the active user.
	 */
	protected function saveOptions() {
		if (WCF::getUser()->userID) {
			$options = array();
			
			// wysiwyg
			$options['wysiwygEditorMode'] = $this->wysiwygEditorMode;
			$options['wysiwygEditorHeight'] = $this->wysiwygEditorHeight;
			
			// options
			if ($this->permissionType !== null) {
				if (WCF::getUser()->getPermission('user.'.$this->permissionType.'.canUseSmilies')) {
					$options[$this->permissionType.'EnableSmilies'] = $this->enableSmilies;
				}
				if (WCF::getUser()->getPermission('user.'.$this->permissionType.'.canUseHtml')) {
					$options[$this->permissionType.'EnableHtml'] = $this->enableHtml;
				}
				if (WCF::getUser()->getPermission('user.'.$this->permissionType.'.canUseBBCodes')) {
					$options[$this->permissionType.'ParseURL'] = $this->parseURL;
					$options[$this->permissionType.'EnableBBCodes'] = $this->enableBBCodes;
				}
				if ($this->showSignatureSetting) {
					$options[$this->permissionType.'ShowSignature'] = $this->showSignature;
				}
			}
			
			$editor = WCF::getUser()->getEditor();
			$editor->updateOptions($options);
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			// default settings
			if (WCF::getUser()->userID) {
				// wysiwyg
				$this->wysiwygEditorMode = WCF::getUser()->wysiwygEditorMode;
				$this->wysiwygEditorHeight = WCF::getUser()->wysiwygEditorHeight;
				// options
				if ($this->permissionType !== null) {
					$this->parseURL = WCF::getUser()->{$this->permissionType.'ParseURL'};
					$this->enableSmilies = WCF::getUser()->{$this->permissionType.'EnableSmilies'};
					$this->enableHtml = WCF::getUser()->{$this->permissionType.'EnableHtml'};
					$this->enableBBCodes = WCF::getUser()->{$this->permissionType.'EnableBBCodes'};
					if ($this->showSignatureSetting) {
						$this->showSignature = WCF::getUser()->{$this->permissionType.'ShowSignature'};
					}
				}
			}
			else {
				// wysiwyg
				$this->wysiwygEditorMode = WYSIWYG_EDITOR_MODE;
				$this->wysiwygEditorHeight = WYSIWYG_EDITOR_HEIGHT;
				// options
				$this->parseURL = MESSAGE_FORM_DEFAULT_PARSE_URL;
				$this->enableSmilies = MESSAGE_FORM_DEFAULT_ENABLE_SMILIES;
				$this->enableHtml = MESSAGE_FORM_DEFAULT_ENABLE_HTML;
				$this->enableBBCodes = MESSAGE_FORM_DEFAULT_ENABLE_BBCODES;
			}
		}
		
		$this->smileyCategories = WCF::getCache()->get('smileys', 'categories');
		$smileys = WCF::getCache()->get('smileys', 'smileys');
		$this->defaultSmileys = (isset($smileys[0]) ? $smileys[0] : array());
		$this->wysiwygBBCodes = WCF::getCache()->get('bbcodes', 'all');
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// flood control
		$this->doFloodControl();
		
		parent::show();
	}
	
	/**
	 * Returns the selected message options.
	 * 
	 * @return	array
	 */
	protected function getOptions() {
		return array(
			'enableSmilies' => $this->enableSmilies,
			'enableHtml' => $this->enableHtml,
			'enableBBCodes' => $this->enableBBCodes,
			'showSignature' => $this->showSignature,
		);
	}
	
	/**
	 * @see AbstractForm::assignVariables();
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'activeTab' => $this->activeTab,
			'subject' => $this->subject,
			'text' => $this->text,
			'parseURL' => $this->parseURL,
			'enableSmilies' => $this->enableSmilies,
			'enableHtml' => $this->enableHtml,
			'enableBBCodes' => $this->enableBBCodes,
			'showSignature' => $this->showSignature,
			'showSmilies' => $this->showSmilies,
			'showSettings' => $this->showSettings,
			'showAttachments' => $this->showAttachments,
			'showPoll' => $this->showPoll,
			'showSignatureSetting' => $this->showSignatureSetting,
			'canUseBBCodes' => WCF::getUser()->getPermission('user.'.$this->permissionType.'.canUseBBCodes'),
			'canUseSmilies' => WCF::getUser()->getPermission('user.'.$this->permissionType.'.canUseSmilies'),
			'canUseHtml' => WCF::getUser()->getPermission('user.'.$this->permissionType.'.canUseHtml'),
			'defaultSmileys' => $this->defaultSmileys,
			'smileyCategories' => $this->smileyCategories,
			'wysiwygBBCodes' => $this->wysiwygBBCodes,
			'maxTextLength' => $this->maxTextLength,
			'wysiwygEditorMode' => $this->wysiwygEditorMode,
			'wysiwygEditorHeight' => $this->wysiwygEditorHeight
		));
	}
}
?>