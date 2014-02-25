<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/action/StyleAction.class.php');

/**
 * Deletes a style.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.style
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class StyleDeleteAction extends StyleAction {
	public $permissions = 'admin.style.canDeleteStyle';
	
	/**
	 * @see StyleAction::__execute()
	 */
	protected function __execute() {
		if ($this->style->isDefault) {
			throw new IllegalLinkException();
		}
		$this->style->delete();
	}
}
?>