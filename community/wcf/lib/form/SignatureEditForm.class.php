<?php
require_once(WCF_DIR.'lib/form/MessageForm.class.php');
require_once(WCF_DIR.'lib/data/message/bbcode/MessageParser.class.php');
require_once(WCF_DIR.'lib/page/util/menu/UserCPMenu.class.php');

/**
 * Shows the signature edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.signature
 * @subpackage	form
 * @category 	Community Framework (commercial)
 */
class SignatureEditForm extends MessageForm {
	public $preview = false;
	public $currentSignature;
	public $signaturePreview = '';
	public $signatureCache = null;
	public $showAttachments = false;
	public $showPoll = false;
	public $showSignatureSetting = false;
	public $permissionType = 'profile.signature';
	public $maxLength = null;
	public $templateName = 'signatureEdit';
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['preview'])) $this->preview = $_POST['preview'];
	}
	
	/**
	 * @see Form::submit()
	 */
	public function submit() {
		// call submit event
		EventHandler::fireAction($this, 'submit');
		
		$this->readFormParameters();
		
		if ($this->preview) {
			$this->signaturePreview = MessageParser::getInstance()->parse($this->text, $this->enableSmilies, $this->enableHtml, $this->enableBBCodes, false);
		}
		else {
			try {
				$this->validate();
				// no errors
				$this->save();
			}
			catch (UserInputException $e) {
				$this->errorField = $e->getField();
				$this->errorType = $e->getType();
			}
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		AbstractForm::validate();
		
		$this->validateText();
	}
	
	/**
	 * @see MessageForm::validateText()
	 */
	protected function validateText() {
		if (empty($this->text)) return;
		
		parent::validateText();
		
		// check image count
		$imageCount = preg_match_all('!\[img=.+?\]!i', $this->text, $m) + preg_match_all('!\[img\].+?(\[/img\]|$)!is', $this->text, $m);
		if ($imageCount > WCF::getUser()->getPermission('user.profile.signature.maxImages')) {
			throw new UserInputException('text', 'tooManyImages');
		}
		
		if (WCF::getUser()->getPermission('user.profile.signature.maxImageSize') > 0 || WCF::getUser()->getPermission('user.profile.signature.maxImageWidth') > 0 || WCF::getUser()->getPermission('user.profile.signature.maxImageHeight') > 0) {
			// get images
			$images = array();
			// [img=path][/img] syntax
			preg_match_all("!\[img=(?:'([^'\\\\]+|\\\\.)*'|(.+?))(?:,(?:'(?:left|right)'|(?:left|right)))?\]!i", $this->text, $matches);
			$images = array_merge($images, ArrayUtil::trim($matches[1]), ArrayUtil::trim($matches[2]));
			// [img]path[/img] syntax
			preg_match_all("!\[img\](.+?)(\[/img\]|$)!is", $this->text, $matches);
			$images = array_merge($images, ArrayUtil::trim($matches[1]));
			
			$errors = array();
			foreach ($images as $image) {
				// download file
				try {
					if (@$tmpFile = FileUtil::downloadFileFromHttp($image, 'image_')) {
						if (WCF::getUser()->getPermission('user.profile.signature.maxImageSize') > 0) {
							// get remote image size (byte)
							if (filesize($tmpFile) > WCF::getUser()->getPermission('user.profile.signature.maxImageSize')) {
				           			$errors[] = array('errorType' => 'tooLarge', 'image' => $image);
				           			continue;
				           		}
						}
						
			           		// get remote image size (pixel)
			           		if (WCF::getUser()->getPermission('user.profile.signature.maxImageWidth') > 0 || WCF::getUser()->getPermission('user.profile.signature.maxImageHeight') > 0) {
							if ($size = @getImageSize($tmpFile)) {
								$width = $size[0]; $height = $size[1];
								if ($width > WCF::getUser()->getPermission('user.profile.signature.maxImageWidth') || $height > WCF::getUser()->getPermission('user.profile.signature.maxImageHeight')) {
									$errors[] = array('errorType' => 'tooLarge', 'image' => $image);
								}
							}
			           		}
					}
				}
				catch (SystemException $e) {}
			}
			
			if (count($errors) > 0) {
				throw new UserInputException('text', $errors);
			}
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save changes
		$this->signatureCache = MessageParser::getInstance()->parse($this->text, $this->enableSmilies, $this->enableHtml, $this->enableBBCodes, false);
		$fields = array(
			'signature' => $this->text,
			'signatureCache' => $this->signatureCache,
			'enableSignatureSmilies' => $this->enableSmilies,
			'enableSignatureHtml' => $this->enableHtml,
			'enableSignatureBBCodes' => $this->enableBBCodes
		);
		$editor = WCF::getUser()->getEditor();
		$editor->updateFields($fields);
		$editor->updateOptions(array('wysiwygEditorMode' => $this->wysiwygEditorMode, 'wysiwygEditorHeight' => $this->wysiwygEditorHeight));
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * Does nothing.
	 */
	protected function saveOptions() {}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			// default settings
			$this->parseURL = 1;
			$this->enableSmilies = WCF::getUser()->enableSignatureSmilies;
			$this->enableHtml = WCF::getUser()->enableSignatureHtml;
			$this->enableBBCodes = WCF::getUser()->enableSignatureBBCodes;
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'signaturePreview' => $this->signaturePreview,
			'signatureCache' => $this->signatureCache,
			'maxImages' => WCF::getUser()->getPermission('user.profile.signature.maxImages')
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if (!WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		if (MODULE_USER_SIGNATURE != 1) {
			throw new IllegalLinkException();
		}
		
		// get max text length
		$this->maxTextLength = WCF::getUser()->getPermission('user.profile.signature.maxLength');
		
		// set active tab
		UserCPMenu::getInstance()->setActiveMenuItem('wcf.user.usercp.menu.link.profile.signature');
		
		// get signature
		if ($this->signatureCache == null) $this->signatureCache = WCF::getUser()->signatureCache;
		$this->text = WCF::getUser()->signature;
		
		// show form
		AbstractForm::show();
	}
}
?>