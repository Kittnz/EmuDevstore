<?php
/**
 * Any PackageInstallationPlugin should implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
interface PackageInstallationPlugin {
	/**
	 * Returns true, if the installation of the given package should execute this plugin.
	 * 
	 * @return	boolean
	 */
	public function hasInstall();
	
	/**
	 * Executes the installation of this plugin.
	 */
	public function install();
	
	/**
	 * Returns true, if the update of the given package should execute this plugin.
	 * 
	 * @return	boolean
	 */
	public function hasUpdate();

	/**
	 * Executes the update of this plugin.
	 */
	public function update();
	
	/**
	 * Returns true, if the uninstallation of the given package should execute this plugin.
	 * 
	 * @return	boolean
	 */
	public function hasUninstall();
	
	/**
	 * Executes the uninstallation of this plugin.
	 */
	public function uninstall();
}
?>