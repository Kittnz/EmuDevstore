<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/action/StyleAction.class.php');

/**
 * Sets a style as default style.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.style
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class StyleSetAsDefaultAction extends StyleAction {
	public $permissions = 'admin.style.canEditStyle';
	
	/**
	 * @see StyleAction::__execute()
	 */
	protected function __execute() {
		$this->style->setAsDefault();
	}
}
?>