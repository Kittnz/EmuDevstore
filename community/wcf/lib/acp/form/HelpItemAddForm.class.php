<?php
// wcf imports
require_once(WCF_DIR.'lib/data/help/HelpItemEditor.class.php');
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');

/**
 * Shows the help item add form.
 *
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.help
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class HelpItemAddForm extends ACPForm {
	// system	
	public $templateName = 'helpItemAdd';
	public $activeMenuItem = 'wcf.acp.menu.link.helpItem.add';
	public $neededPermissions = 'admin.help.canAddHelpItem';
	
	// item
	public $helpItem = null;
	
	// parameters
	public $topic = '';
	public $parentItem = '';
	public $text = '';
	public $refererPattern = '';
	public $showOrder = 0;
	public $isDisabled = 0;	
	public $languageID = 0;
	
	// items
	/**
	 * help item list
	 * 
	 * @var array<array>
	 */
	public $helpItemList = array();
	
	/**
	 * structured help item list
	 * 
	 * @var array<array>
	 */
	public $helpItems = array();

	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['parentItem'])) $this->parentItem = $_POST['parentItem'];
		if (isset($_POST['refererPattern'])) $this->refererPattern = StringUtil::trim($_POST['refererPattern']);
		if (isset($_POST['showOrder'])) $this->showOrder = intval($_POST['showOrder']);	
		if (isset($_POST['isDisabled'])) $this->isDisabled = intval($_POST['isDisabled']);
		if (isset($_POST['topic'])) $this->topic = StringUtil::trim($_POST['topic']);
		if (isset($_POST['text'])) $this->text = StringUtil::trim($_POST['text']);		
		if (isset($_POST['languageID'])) $this->languageID = intval($_POST['languageID']);
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();
		
		//  validate topic
		$this->validateTopic();
		// validate text
		$this->validateText();
	}
	
	/**
	 * Validates the given topic.
	 */
	public function validateTopic() {
		if (empty($this->topic)) {
			throw new UserInputException('topic');
		}
	}
	
	/**
	 * Validates the given text.
	 */
	public function validateText() {
		if (empty($this->text)) {
			throw new UserInputException('text');
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readHelpItems();
		$this->makeHelpItemList();
	}
	
	/**
	 * Gets a list of all help items.
	 */
	protected function readHelpItems() {
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_help_item
			WHERE		packageID IN (
						SELECT	dependency
						FROM	wcf".WCF_N."_package_dependency
						WHERE	packageID = ".PACKAGE_ID."
					)
			ORDER BY	parentHelpItem, showOrder";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->helpItems[$row['parentHelpItem']][] = new HelpItem(null, $row);
		}
	}

	/**
	 * Renders one level of the help item structure.
	 *
	 * @param	integer		$parentHelpItem
	 * @param	integer		$depth
	 */
	protected function makeHelpItemList($parentHelpItem = '', $depth = 1) {
		if (!isset($this->helpItems[$parentHelpItem])) return;
		
		foreach ($this->helpItems[$parentHelpItem] as $helpItem) {
			// we must encode html here because the htmloptions plugin doesn't do it
			$title = WCF::getLanguage()->get('wcf.help.item.'.StringUtil::encodeHTML($helpItem->helpItem));
			if ($depth > 0) $title = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $depth). ' ' . $title;
			$this->helpItemList[$helpItem->helpItem] = $title;
			
			// make next level of the list
			$this->makeHelpItemList($helpItem->helpItem, $depth + 1);
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save
		$this->helpItem = HelpItemEditor::create($this->topic, $this->text, $this->parentItem, $this->refererPattern, $this->showOrder, $this->isDisabled, WCF::getLanguage()->getLanguageID());
		HelpItemEditor::clearCache();
		$this->saved();
		
		// reset values
		$this->topic = $this->text = $this->parentItem = $this->refererPattern = '';
		$this->languageID = $this->showOrder = $this->isDisabled = 0;
		
		// show success message
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'items' => $this->helpItemList,
			'topic' => $this->topic,
			'parentItem' => $this->parentItem,
			'text' => $this->text,
			'refererPattern' => $this->refererPattern,
			'showOrder' => $this->showOrder,
			'isDisabled' => $this->isDisabled,
			'helpItem' => $this->helpItem,
			'languageID' => $this->languageID,
			'action' => 'add'
		));
	}
}
?>