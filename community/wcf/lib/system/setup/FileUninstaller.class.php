<?php
// wcf imports
require_once(WCF_DIR.'lib/system/setup/Uninstaller.class.php');

/**
 * FileUninstaller is an implementation of Uninstaller.
 * It uses simple file operations to delete files and directories.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.setup
 * @category 	Community Framework
 */
class FileUninstaller extends Uninstaller {
	/**
	 * @see Uninstaller::deleteFile()
	 */
	protected function deleteFile($file) {
		@unlink($file);
	}
	
	/**
	 * @see Uninstaller::deleteDir()
	 */
	protected function deleteDir($dir) {
		@rmdir($dir);
	}
}
?>