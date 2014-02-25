<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractPackageInstallationPlugin.class.php');
require_once(WCF_DIR.'lib/acp/package/PackageInstallation.class.php');

/**
 * This PIP looks for template patches, reads them and calls the class(es) that apply them.
 * 
 * @author	Siegfried Schweizer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class TemplatePatchPackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	public $tagName = 'templatepatch';
	public $tableName = 'template_patch';
	protected $type = '';
	protected $templateTable = 'template';
	
	/**
	 * Read an additional parameter from the request.
	 */
	public function readParameters() {
		if (isset($_REQUEST['nextPatch'])) $nextPatch = $_REQUEST['nextPatch'];
		else $nextPatch = 0;
	}
	
	/** 
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		// extract the file containing the patches (the 'diff') directly to a string ...
		$patchFileName = $this->installation->getXMLTag($this->tagName);
		
		// get the attribute containing the fuzz factor value.
		$fuzzFactor = 2;
		$instructions = $this->installation->getArchive()->getInstructions('install');
		foreach ($instructions as $key => $instruction) {
			if (($key == $this->tagName) && isset($instruction['fuzzfactor']) && $instruction['fuzzfactor']) {
				$fuzzFactor = $instruction['fuzzfactor'];
				break;
			}
		}
		
		$patchFileString = $this->installation->getArchive()->getTar()->extractToString($patchFileName['cdata']);
		// ... and pass that string to a new object instance of TemplatePatch.class.
		
		require_once(WCF_DIR.'lib/system/template/patch/TemplatePatch.class.php');
		try {
			$patchObject = new TemplatePatch($this->installation->getPackageID(), $patchFileString, false, false, 0, $this->type, $fuzzFactor);
		}
		catch (TemplatePatchException $e) {
			// be a bit more friendly to the user.
			WCF::getTPL()->assign(array(
				'errorCode' => $e->getCode(),
				'errorMessage' => $e->getMessage(),
				'affectedTemplateName' => $e->getTemplateName()
			));
			WCF::getTPL()->display('packageInstallationPatchFailed');
			exit;
		}
	}
	
	/** 
	 * Looks if tpl files which have just been overwritten by a package update 
	 * have already been patched before. If this is true, it is attempted to 
	 * apply this patch once more.
	 * 
	 * @param	array			$files
	 */
	public function repatch($files = array()) {
		// order the names of the templates that were overwritten by the package update.
		$templateNames = array();
		foreach ($files as $file) {
			$templateNames[] = "'".substr($file['filename'], 0, -4)."'";
		}
		if (!count($templateNames)) return;
		
		// get the patches that have been applied to the these templates before.
		$patches = $this->getPatchesFromDB($this->installation->getPackageID(), $templateNames, true);
		
		// if there are patches, call a new TemplatePatch object for every patch that's been found.
		require_once(WCF_DIR.'lib/system/template/patch/TemplatePatch.class.php');
		$failures = array();
		if (count($patches)) {
			foreach ($patches as $key => $patch) {
				try {
					$patchObject = new TemplatePatch($this->installation->getPackageID(), $patch['patch'], false, true, $patch['templateID'], $this->type, $patch['fuzzFactor']);
				}
				catch (TemplatePatchException $e) {
					// if re-patching of an overwritten template failed, it is being remembered
					// and then noticed to the user that the package that patched the template 
					// most likely wont't work correctly no more.
					$failureIDs[] = $patch['patchID'];
					$failures[$key]['templateName'] = $patch['templateName'];
					$failures[$key]['packageName'] = $patch['packageName'];
					$failures[$key]['errorCode'] = $e->getCode();
					$failures[$key]['errorMessage'] = $e->getMessage();
					continue;
				}
			}
			if (count($failures)) {
				$this->updatePatchFailures($failureIDs);
				$nextStep = $this->installation->getNextPackageInstallationPlugin($this->installation->step);
				
				WCF::getTPL()->assign(array(
					'failures' => $failures,
					'nextStep' => $nextStep
				));
				WCF::getTPL()->display('packageInstallationRePatchFailed');
				exit;
			}
		}
	}
	
	/** 
	 * @see PackageInstallationPlugin::uninstall()
	 */
	public function uninstall() {
		// read the patch fields of the respective database entries into an array of strings.
		$result = $this->getPatchesFromDB($this->installation->getPackageID());
		// revert the order of the patches because it might be that successive patches have been applied to a template.
		$patches = array_reverse($result);
		
		// if there are patches, call a new TemplatePatch object for every patch that's been found.
		require_once(WCF_DIR.'lib/system/template/patch/TemplatePatch.class.php');
		if (count($patches)) {
			foreach ($patches as $patch) {
				if ($patch['success']) {
					try {
						// reverse apply the patch if has been successfully applied last time.
						$patchObject = new TemplatePatch($this->installation->getPackageID(), $patch['patch'], true, false, $patch['templateID'], $this->type, $patch['fuzzFactor']);
					}
					catch (TemplatePatchException $e) {
						// be a bit more friendly to the user.
						WCF::getTPL()->assign(array(
							'errorCode' => $e->getCode(),
							'errorMessage' => $e->getMessage(),
							'affectedTemplateName' => $e->getTemplateName()
						));
						WCF::getTPL()->display('packageInstallationPatchFailed');
						exit;
					}
				}
				else {
					// just delete the patch from the database.
					$this->deletePatch($patch['patchID']);
				}
			}
		}
	}
	
	/**
	 * Read from the database patch(es) that were applied by the package that's now being uninstalled.
	 * 
	 * @param	integer		$packageID
	 * @param	array		$templateNames
	 * @param 	boolean		$rePatch
	 * @return	array		$patches
	 */
	public function getPatchesFromDB($packageID = 0, $templateNames = array(), $rePatch = false) {
		if ($rePatch === true) {
			$sql = "SELECT		templatePatch.*,
						template.templateID, template.templateName, package.package AS packageName 
				FROM		wcf".WCF_N."_".$this->tableName." templatePatch 
				LEFT JOIN	wcf".WCF_N."_".$this->type."template template 
				ON		template.templateID = templatePatch.templateID 
				LEFT JOIN	wcf".WCF_N."_package package 
				ON		package.packageID = templatePatch.packageID 
				WHERE		template.packageID = ".$packageID." 
						AND template.templateName IN (".implode(',', $templateNames).") 
				ORDER BY	templatePatch.templateID DESC, templatePatch.patchID ASC";
		}
		else {
			$sql = "SELECT		templatePatch.* 
				FROM		wcf".WCF_N."_".$this->tableName." templatePatch 
				WHERE		templatePatch.packageID = ".$packageID." 
				ORDER BY	templatePatch.templateID DESC, templatePatch.patchID DESC";
		}
		$result = WCF::getDB()->sendQuery($sql);
		$patches = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!isset($row['fuzzFactor'])) $row['fuzzFactor'] = 0; // beta3 to beta4 update bugfix; remove this later!
			$patches[] = $row;
		}
		return $patches;
	}
	
	/**
	 * Update patch(es) where re-patching failed in order to skip and just delete them
	 * when the package that brought them is being uninstalled.
	 * 
	 * @return	array		$failureIDs
	 */
	public function updatePatchFailures($failureIDs = array()) {
		$idstring = implode(',', $failureIDs);
		$sql = "UPDATE		wcf".WCF_N."_".$this->tableName." 
			SET		success = 0 
			WHERE		patchID IN (".$idstring.")";
		$result = WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Remove a patch from database in case the package 
	 * that installs this patch is being deinstalled.
	 * 
	 * @param	integer		$patchID
	 */
	protected function deletePatch($patchID) {
		$sql = "DELETE FROM		wcf".WCF_N."_".$this->tableName." 
			WHERE			patchID = ".$patchID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>