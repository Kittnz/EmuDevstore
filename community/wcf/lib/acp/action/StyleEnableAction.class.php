<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/action/StyleAction.class.php');

/**
 * Enables a style.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.style
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class StyleEnableAction extends StyleAction {
	public $permissions = 'admin.style.canEditStyle';
	
	/**
	 * @see StyleAction::__execute()
	 */
	protected function __execute() {
		if (!$this->style->disabled) {
			throw new IllegalLinkException();
		}
		$this->style->enable();
	}
}
?>