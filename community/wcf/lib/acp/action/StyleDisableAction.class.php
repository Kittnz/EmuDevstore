<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/action/StyleEnableAction.class.php');

/**
 * Disables a style.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.display.style
 * @subpackage	acp.action
 * @category 	Community Framework (commercial)
 */
class StyleDisableAction extends StyleEnableAction {
	/**
	 * @see StyleAction::__execute()
	 */
	protected function __execute() {
		if ($this->style->disabled || $this->style->isDefault) {
			throw new IllegalLinkException();
		}
		$this->style->disable();
	}
}
?>