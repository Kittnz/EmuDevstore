<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Renames a package.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class PackageRenameAction extends AbstractAction {
	public $packageID = 0;
	public $name = '';
	
	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['packageID'])) $this->packageID = intval($_REQUEST['packageID']);
		if (isset($_POST['name'])) {
			$this->name = $_POST['name'];
			if (CHARSET != 'UTF-8') $this->name = StringUtil::convertEncoding('UTF-8', CHARSET, $this->name);
		}
	}
	
	/**
	 * @see Action::execute();
	 */
	public function execute() {
		parent::execute();
		
		// check permissions
		WCF::getUser()->checkPermission(array('admin.system.package.canUpdatePackage', 'admin.system.package.canUninstallPackage'));
				
		// update name
		$sql = "UPDATE	wcf".WCF_N."_package
			SET	instanceName = '".escapeString($this->name)."'
			WHERE	packageID = ".$this->packageID;
		WCF::getDB()->sendQuery($sql);
		
		// reset cache
		WCF::getCache()->clearResource('packages');
		$this->executed();
	}
}
?>