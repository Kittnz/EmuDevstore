<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/help/HelpItemEditor.class.php');

/**
 * Sorts the structure of help items.
 * 
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.help
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class HelpItemSortAction extends AbstractAction {
	/**
	 * positions
	 * 
	 * @var	array
	 */
	public $positions = array();
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get positions
		if (isset($_POST['helpListPositions']) && is_array($_POST['helpListPositions'])) $this->positions = ArrayUtil::toIntegerArray($_POST['helpListPositions']);
	}
		
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// check permissions
		WCF::getUser()->checkPermission('admin.help.canEditHelpItem');
		
		// update postions
		foreach ($this->positions as $helpItemID => $data) {
			foreach ($data as $parentHelpItemID => $position) {
				$parentHelpItem = '';
				if ($parentHelpItemID != 0) {
					$parentHelpObject = new HelpItem($parentHelpItemID);
					$parentHelpItem = $parentHelpObject->helpItem;
				}
				
				HelpItemEditor::updateShowOrder(intval($helpItemID), $parentHelpItem, $position);
			}
		}
		
		// delete cache
		HelpItemEditor::clearCache();
		$this->executed();
		
		// forward to list page
		header('Location: index.php?page=HelpItemList&successfullSorting=1&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);	
		exit;
	}
}
?>