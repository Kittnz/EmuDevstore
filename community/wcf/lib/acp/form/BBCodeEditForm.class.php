<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/form/BBCodeAddForm.class.php');
require_once(WCF_DIR.'lib/data/message/bbcode/BBCodeEditor.class.php');

/**
 * Shows the bbcode edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.bbcode
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class BBCodeEditForm extends BBCodeAddForm {
	// system
	public $activeMenuItem = 'wcf.acp.menu.link.bbcode';
	public $neededPermissions = 'admin.bbcode.canEditBBCode';
	
	/**
	 * bbcode id
	 * 
	 * @var	integer
	 */
	public $bbcodeID = 0;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['bbcodeID'])) $this->bbcodeID = intval($_REQUEST['bbcodeID']);
		$this->bbcode = new BBCodeEditor($this->bbcodeID);
		if (!$this->bbcode->bbcodeID) {
			throw new IllegalLinkException();
		}
		
		$this->isCoreBBCode = (preg_match("/^(align|b|code|color|font|i|img|list|quote|size|s|u|url)$/", $this->bbcode->bbcodeTag) ? 1 : 0);
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if ($this->isCoreBBCode) {
			$this->wysiwyg = $this->bbcode->wysiwyg;
			$this->wysiwygIcon = $this->bbcode->wysiwygIcon;
		}
	}
	
	/**
	 * @see BBCodeAddForm::validateBBCodeTag()
	 */
	protected function validateBBCodeTag() {
		if (empty($this->bbcodeTag)) {
			throw new UserInputException('bbcodeTag');
		}
		
		$sql = "SELECT	bbcodeID
			FROM	wcf".WCF_N."_bbcode
			WHERE	bbcodeID <> ".$this->bbcodeID."
				AND bbcodeTag = '".escapeString($this->bbcodeTag)."'";
		$row = WCF::getDB()->getFirstRow($sql);
		if (isset($row['bbcodeID'])) {
			throw new UserInputException('bbcodeTag', 'notUnique');
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function save() {
		AbstractForm::save();
		
		// update
		$this->bbcode->update($this->bbcodeTag, $this->htmlOpen, $this->htmlClose, $this->textOpen, $this->textClose, $this->allowedChildren, $this->className, $this->sourceCode, $this->attributes, $this->wysiwyg, $this->wysiwygIcon);
		
		// show success message
		WCF::getTPL()->assign('success', true);
		
		// delete cache
		WCF::getCache()->clear(WCF_DIR . 'cache', 'cache.bbcodes.php');
		
		$this->saved();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'bbcode' => $this->bbcode,
			'bbcodeID' => $this->bbcodeID,
			'action' => 'edit'
		));
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			// default value
			$this->bbcodeTag = $this->bbcode->bbcodeTag;
			$this->htmlOpen = $this->bbcode->htmlOpen;
			$this->htmlClose = $this->bbcode->htmlClose;
			$this->textOpen = $this->bbcode->textOpen;
			$this->textClose = $this->bbcode->textClose;
			$this->allowedChildren = $this->bbcode->allowedChildren;
			$this->className = $this->bbcode->className;
			$this->sourceCode = $this->bbcode->sourceCode;
			$this->attributes = $this->bbcode->getAttributes();
			$this->wysiwyg = $this->bbcode->wysiwyg;
			$this->wysiwygIcon = $this->bbcode->wysiwygIcon;
		}
	}
}
?>