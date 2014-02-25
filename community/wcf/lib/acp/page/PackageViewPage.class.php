<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/acp/package/Package.class.php');

/**
 * Shows all information about an installed package.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class PackageViewPage extends AbstractPage {
	public $package;
	public $packageID = 0;
	public $templateName = 'packageView';
	public $requiredPackages = array();
	public $dependentPackages = array();
	public $dependencies = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['activePackageID'])) $this->packageID = intval($_REQUEST['activePackageID']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get package data
		try {
			$this->package = new Package($this->packageID);
			$this->requiredPackages = $this->package->getRequiredPackages();
			$this->dependentPackages = $this->package->getDependentPackages();
			$this->dependencies = $this->package->getDependencies();
		}
		catch (SystemException $e) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'requiredPackages' => $this->requiredPackages,
			'dependentPackages' => $this->dependentPackages,
			'dependencies' => $this->dependencies,
			'package' => $this->package
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.package');
		
		// check permission
		WCF::getUser()->checkPermission(array('admin.system.package.canUpdatePackage', 'admin.system.package.canUninstallPackage'));
		
		parent::show();
	}
}
?>