<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/help/HelpItemEditor.class.php');

/**
 * Renames a help item.
 *
 * @author	Arian Glander
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.help
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class HelpItemRenameAction extends AbstractAction {
	/**
	 * help item id
	 * 
	 * @var	integer
	 */
	public $helpItemID = 0;
	
	/**
	 * new name
	 * 
	 * @var	string
	 */
	public $title = '';

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if (isset($_REQUEST['helpItemID'])) $this->helpItemID = intval($_REQUEST['helpItemID']);
		if (isset($_POST['title'])) {
			$this->title = $_POST['title'];
			if (CHARSET != 'UTF-8') $this->title = StringUtil::convertEncoding('UTF-8', CHARSET, $this->title);
		}
	}

	/**
	 * @see Action::execute();
	 */
	public function execute() {
		parent::execute();

		// check permission
		WCF::getUser()->checkPermission('admin.help.canEditHelpItem');

		// get help item
		$helpItem = new HelpItemEditor($this->helpItemID);
		if (!$helpItem->helpItemID) {
			throw new IllegalLinkException();
		}

		// change language variable
		require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');
		$language = new LanguageEditor(WCF::getLanguage()->getLanguageID());
		$language->updateItems(array(('wcf.help.item.' . $helpItem->helpItem) => $this->title), 0, PACKAGE_ID, array('wcf.help.item.' . $helpItem->helpItem => 1));

		// reset cache
		WCF::getCache()->clearResource('help-' . PACKAGE_ID);
		$this->executed();
	}
}
?>