<?php
// wcf imports
require_once(WCF_DIR.'lib/form/AbstractForm.class.php');

/**
 * Provides a default implementation for the show method in acp forms.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category 	Community Framework
 */
class ACPForm extends AbstractForm {
	/**
	 * Active acp menu item.
	 * 
	 * @var string
	 */
	public $activeMenuItem = '';
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		if (!empty($this->activeMenuItem)) WCFACP::getMenu()->setActiveMenuItem($this->activeMenuItem);
		
		parent::show();
	}
}
?>