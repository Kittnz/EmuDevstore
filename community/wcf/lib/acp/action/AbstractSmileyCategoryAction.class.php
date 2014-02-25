<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/data/message/smiley/SmileyEditor.class.php');
require_once(WCF_DIR.'lib/data/message/smiley/category/SmileyCategoryEditor.class.php');

/**
 * Provides default implementations for smiley category actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.smiley
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class AbstractSmileyCategoryAction extends AbstractAction {
	/**
	 * smiley category id
	 *
	 * @var	integer
	 */
	public $smileyCategoryID = 0;
	
	/**
	 * smiley category editor object
	 *
	 * @var	SmileyCategoryEditor
	 */
	public $smileyCategory = null;
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['smileyCategoryID'])) $this->smileyCategoryID = intval($_REQUEST['smileyCategoryID']);
		$this->smileyCategory = new SmileyCategoryEditor($this->smileyCategoryID);
		if (!$this->smileyCategory->smileyCategoryID) {
			throw new IllegalLinkException();
		}
	}
}
?>