<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/PackageInstallation.class.php');
require_once(WCF_DIR.'lib/system/setup/FileHandler.class.php');

/**
 * PackageInstallationFileHandler is the abstract FileHandler implementation for all file installations during the package installation.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package
 * @category 	Community Framework
 */
abstract class PackageInstallationFileHandler implements FileHandler {
	protected $packageInstallation;
	
	/**
	 * Creates a new PackageInstallationFileHandler object.
	 * 
	 * @param	PackageInstallation	$packageInstallation
	 */
	public function __construct(PackageInstallation $packageInstallation) {
		$this->packageInstallation = $packageInstallation;
	}
}
?>