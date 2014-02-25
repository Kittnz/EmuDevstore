<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractPackageInstallationPlugin.class.php');
require_once(WCF_DIR.'lib/acp/package/TemplatesFileHandler.class.php');

/**
 * This PIP installs, updates or deletes by a package delivered templates.
 * 
 * @author 	Benjamin Kunz
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class TemplatesPackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	public $tagName = 'templates';
	public $tableName = 'template';
	
	/**
	 * Installs the templates of this package.
	 */
	public function install() {
		parent::install();

		// extract files.tar to temp folder
		$tag = $this->installation->getXMLTag('templates');
		$sourceFile = $this->installation->getArchive()->extractTar($tag['cdata'], 'templates_');
		
		// create file handler
		$fileHandler = new TemplatesFileHandler($this->installation);
		
		// extract content of files.tar
		$packageDir = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR.$this->installation->getPackage()->getDir()));
		
		try {
			$fileInstaller = $this->installation->extractFiles($packageDir.'templates/', $sourceFile, $fileHandler);
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
		require_once(WCF_DIR.'lib/acp/package/plugin/TemplatePatchPackageInstallationPlugin.class.php');
		$tar = new Tar($sourceFile);
		$files = $tar->getContentList();
		$templatePatch = new TemplatePatchPackageInstallationPlugin($this->installation);
		$templatePatch->repatch($files);
		
		// delete temporary sourceArchive
		@unlink($sourceFile);
	}
	
	/**
	 * Uninstalls the templates of this package.
	 */
	public function uninstall() {
		// create templates list
		$templates = array();
		
		// get templates from log
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_template
			WHERE 	packageID = ".$this->installation->getPackageID();
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$files[] = 'templates/'.$row['templateName'].'.tpl';
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