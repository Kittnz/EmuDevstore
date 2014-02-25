<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractPackageInstallationPlugin.class.php');
require_once(WCF_DIR.'lib/acp/package/ACPTemplatesFileHandler.class.php');

/**
 * This PIP installs, updates or deletes by a package delivered ACP-templates.
 * 
 * @author 	Benjamin Kunz
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class ACPTemplatesPackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	public $tagName = 'acptemplates';
	public $tableName = 'acp_template';
	
	/**
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();

		// extract files.tar to temp folder
		$tag = $this->installation->getXMLTag('acptemplates');
		$sourceFile = $this->installation->getArchive()->extractTar($tag['cdata'], 'acptemplates_');
		
		// create file handler
		$fileHandler = new ACPTemplatesFileHandler($this->installation);
		
		// extract content of files.tar
		$packageDir = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR.$this->installation->getPackage()->getDir()));
		
		try {
			$fileInstaller = $this->installation->extractFiles($packageDir.'acp/templates/', $sourceFile, $fileHandler);
		}
		catch (SystemException $e) {
			if (!@file_exists(WCF_DIR.'acp/templates/packageInstallationFileInstallationFailed.tpl')) {
				// workaround for wcf 1.0 to 1.1 update
				throw $e;
			}
			else {
				WCF::getTPL()->assign(array(
					'exception' => $e
				));
				WCF::getTPL()->display('packageInstallationFileInstallationFailed');
				exit;
			}
		}
		
		// look if tpl files are to be overwritten by this update, 
		// and if so, check if these files have been patched before ... 
		require_once(WCF_DIR.'lib/acp/package/plugin/ACPTemplatePatchPackageInstallationPlugin.class.php');
		$tar = new Tar($sourceFile);
		$files = $tar->getContentList();
		$templatePatch = new ACPTemplatePatchPackageInstallationPlugin($this->installation);
		$templatePatch->repatch($files);
		
		// delete temporary sourceArchive
		@unlink($sourceFile);
	}
	
	/**
	 * @see PackageInstallationPlugin::uninstall()
	 */
	public function uninstall() {
		// create ACP-templates list
		$templates = array();
		
		// get ACP-templates from log
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_acp_template
			WHERE 	packageID = ".$this->installation->getPackageID();
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			// store acp template with suffix (_$packageID)
			$files[] = 'acp/templates/'.$row['templateName'].'.tpl';
		}
		
		if (count($files) > 0) {
			// delete template files
			$packageDir = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR.$this->installation->getPackage()->getDir()));
			$deleteEmptyDirectories = $this->installation->getPackage()->isStandalone();
			$this->installation->deleteFiles($packageDir, $files, false, $deleteEmptyDirectories);
			
			// delete log entries
			parent::uninstall();
		}
	}
}
?>