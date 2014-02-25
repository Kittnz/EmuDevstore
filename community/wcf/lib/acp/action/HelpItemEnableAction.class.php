<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/help/HelpItemEditor.class.php');

/**
 * Enables / disables a help item.
 * 
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.help
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class HelpItemEnableAction extends AbstractAction {
	/**
	 * 1 to enable the item, 0 to disable
	 * 
	 * @var integer
	 */
	public $enable = 0;
	
	/**
	 * help item id
	 *
	 * @var integer
	 */
	public $helpItemID = 0;
	
	/**
	 * help item object
	 *
	 * @var HelpItemEditor
	 */
	public $helpItem = null;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['helpItemID'])) $this->helpItemID = intval($_REQUEST['helpItemID']);
		$this->helpItem = new HelpItemEditor($this->helpItemID);
		if (!$this->helpItem->helpItemID) {
			throw new IllegalLinkException();
		}
		if (isset($_REQUEST['enable'])) $this->enable = intval($_REQUEST['enable']);
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permissions
		WCF::getUser()->checkPermission('admin.help.canEditHelpItem');
		
		// update
		$this->helpItem->enable($this->enable);
		
		// delete cache
		HelpItemEditor::clearCache();
		$this->executed();

		// forward to list page
		header('Location: index.php?page=HelpItemList&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);	
		exit;
	}
}
?>