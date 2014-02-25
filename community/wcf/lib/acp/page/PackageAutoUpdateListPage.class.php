<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/acp/package/update/PackageUpdate.class.php');

/**
 * Shows the list of available updates for installed packages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class PackageAutoUpdateListPage extends AbstractPage {
	public $templateName = 'packageAutoUpdateList';
	
	public $availableUpdates = array();
	
	/**
	 * @see	Page::assignVariables()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (!count($_POST)) {
			// refresh package database
			PackageUpdate::refreshPackageDatabase();
		}
		
		// get updatable packages
		$this->availableUpdates = PackageUpdate::getAvailableUpdates();
	}
	
	/**
	 * @see	Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'availableUpdates' => $this->availableUpdates
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// set active menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.autoupdate');
		
		// check permission
		WCF::getUser()->checkPermission('admin.system.package.canUpdatePackage');
		
		// check master password
		WCFACP::checkMasterPassword();
		
		parent::show();
	}
}
?>