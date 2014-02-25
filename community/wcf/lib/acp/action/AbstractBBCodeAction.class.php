<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/message/bbcode/BBCodeEditor.class.php');

/**
 * Abstract bbcode action.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.bbcode
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class AbstractBBCodeAction extends AbstractAction {
	/**
	 * bbcode id
	 * 
	 * @var	integer
	 */
	public $bbcodeID = 0;
	
	/**
	 * bbcode editor object
	 * 
	 * @var	BBCodeEditor
	 */
	public $bbcode = null;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['bbcodeID'])) $this->bbcodeID = intval($_REQUEST['bbcodeID']);
		$this->bbcode = new BBCodeEditor($this->bbcodeID);	
		if (!$this->bbcode->bbcodeID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see AbstractAction::executed()
	 */
	protected function executed() {
		parent::executed();
		
		// forward to list page
		HeaderUtil::redirect('index.php?page=BBCodeList&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>