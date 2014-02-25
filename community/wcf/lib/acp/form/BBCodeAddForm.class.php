<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/data/message/bbcode/BBCodeEditor.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * Shows the bbcode add form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.bbcode
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class BBCodeAddForm extends ACPForm {
	// system
	public $templateName = 'bbcodeAdd';
	public $activeMenuItem = 'wcf.acp.menu.link.bbcode.add';
	public $neededPermissions = 'admin.bbcode.canAddBBCode';
	
	// parameters
	public $bbcodeTag = '';
	public $htmlOpen = '';
	public $htmlClose = '';
	public $textOpen = '';
	public $textClose = '';
	public $allowedChildren = '';
	public $className = '';
	public $sourceCode = 0;
	public $attributes = array();
	public $addAttribute = false;
	public $removeAttribute = null;
	public $wysiwyg = 0;
	public $wysiwygIcon = '';
	public $isCoreBBCode = false;
	
	/**
	 * bbcode editor object
	 * 
	 * @var	BBCodeEditor
	 */
	public $bbcode = null;
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['bbcodeTag'])) $this->bbcodeTag = StringUtil::trim($_POST['bbcodeTag']);
		if (isset($_POST['htmlOpen'])) $this->htmlOpen = $_POST['htmlOpen'];
		if (isset($_POST['htmlClose'])) $this->htmlClose = $_POST['htmlClose'];
		if (isset($_POST['textOpen'])) $this->textOpen = $_POST['textOpen'];
		if (isset($_POST['textClose'])) $this->textClose = $_POST['textClose'];
		if (isset($_POST['allowedChildren'])) $this->allowedChildren = StringUtil::trim($_POST['allowedChildren']);
		if (isset($_POST['className'])) $this->className = StringUtil::trim($_POST['className']);
		if (isset($_POST['sourceCode'])) $this->sourceCode = intval($_POST['sourceCode']);
		if (isset($_POST['attributes']) && is_array($_POST['attributes'])) $this->attributes = $_POST['attributes'];
		if (isset($_POST['addAttribute'])) $this->addAttribute = true;
		if (isset($_POST['removeAttribute']) && is_array($_POST['removeAttribute']) && count($_POST['removeAttribute'])) {
			$keys = array_keys($_POST['removeAttribute']);
			$this->removeAttribute = $keys[0];
		}
		if (isset($_POST['wysiwyg']) && !empty($this->htmlOpen) && empty($this->className)) $this->wysiwyg = intval($_POST['wysiwyg']);
		if (isset($_POST['wysiwygIcon'])) $this->wysiwygIcon = $_POST['wysiwygIcon'];
		
		// check attributes
		foreach ($this->attributes as $key => $attribute) {
			if (!isset($attribute['attributeHtml'])) $attribute['attributeHtml'] = '';
			if (!isset($attribute['attributeText'])) $attribute['attributeText'] = '';
			if (!isset($attribute['validationPattern'])) $attribute['validationPattern'] = '';
			if (isset($attribute['required'])) $attribute['required'] = intval($attribute['required']);
			else $attribute['required'] = 0;
			if (isset($attribute['useText'])) $attribute['useText'] = intval($attribute['useText']); 
			else $attribute['useText'] = 0;
			$this->attributes[$key] = $attribute;
		}
	}
	
	/**
	 * Validates the bbcode tag.
	 */
	protected function validateBBCodeTag() {
		if (empty($this->bbcodeTag)) {
			throw new UserInputException('bbcodeTag');
		}
		
		$sql = "SELECT	bbcodeID
			FROM	wcf".WCF_N."_bbcode
			WHERE	bbcodeTag = '".escapeString($this->bbcodeTag)."'";
		$row = WCF::getDB()->getFirstRow($sql);
		if (isset($row['bbcodeID'])) {
			throw new UserInputException('bbcodeTag', 'notUnique');
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		$this->validateBBCodeTag();
	}
	
	/**
	 * @see Form::submit()
	 */
	public function submit() {
		// call submit event
		EventHandler::fireAction($this, 'submit');
		
		$this->readFormParameters();
		
		// add attribute
		if ($this->addAttribute) {
			$this->attributes[] = array('attributeHtml' => '', 'attributeText' => '', 'validationPattern' => '', 'required' => 0, 'useText' => 0);
		}
		// remove attribute
		else if ($this->removeAttribute !== null) {
			unset($this->attributes[$this->removeAttribute]);
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
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		$this->bbcode = BBCodeEditor::create($this->bbcodeTag, $this->htmlOpen, $this->htmlClose, $this->textOpen, $this->textClose, $this->allowedChildren, $this->className, $this->sourceCode, $this->attributes, $this->wysiwyg, $this->wysiwygIcon);
		
		// reset values
		$this->wysiwyg = 0;
		$this->bbcodeTag = $this->htmlOpen = $this->htmlClose = $this->textOpen = $this->textClose = $this->allowedChildren = $this->className = $this->wysiwygIcon = '';
		$this->attributes = array();
		
		// delete cache
		WCF::getCache()->clear(WCF_DIR . 'cache', 'cache.bbcodes.php');
		$this->saved();
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'bbcodeTag' => $this->bbcodeTag,
			'htmlOpen' => $this->htmlOpen,
			'htmlClose' => $this->htmlClose,
			'textOpen' => $this->textOpen,
			'textClose' => $this->textClose,
			'allowedChildren' => $this->allowedChildren,
			'className' => $this->className,
			'sourceCode' => $this->sourceCode,
			'attributes' => $this->attributes,
			'wysiwyg' => $this->wysiwyg,
			'wysiwygIcon' => $this->wysiwygIcon,
			'action' => 'add',
			'isCoreBBCode' => $this->isCoreBBCode
		));
	}
}
?>