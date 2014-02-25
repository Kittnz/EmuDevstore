<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractPackageInstallationPlugin.class.php');

/**
 * Default implementation of some functions for a PackageInstallationPlugin using xml definitions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
abstract class AbstractXMLPackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	/**
	 * Loads the xml file into a string and returns this string.
	 *  
	 * @return 	XML		$xml
	 */
	protected function getXML() {
		$instructions = $this->installation->getInstructions();
		
		if (!isset($instructions[$this->tagName]['cdata'])) {
			return false; 
		}

		// Search the acpmenu xml-file in the package archive.
		// Abort installation in case no file was found.
		if (($fileIndex = $this->installation->getArchive()->getTar()->getIndexByFilename($instructions[$this->tagName]['cdata'])) === false) {
			throw new SystemException("xml file '".$instructions[$this->tagName]['cdata']."' not found in '".$this->installation->getArchive()->getArchive()."'", 13008);
		}

		// Extract acpmenu file and parse with SimpleXML
		$xml = new XML();
		$tmpFile = FileUtil::getTemporaryFilename('xml_');
		try {
			$this->installation->getArchive()->getTar()->extract($fileIndex, $tmpFile);
			$xml->loadFile($tmpFile);
		}
		catch (Exception $e) { // bugfix to avoid file caching problems
			try {
				$this->installation->getArchive()->getTar()->extract($fileIndex, $tmpFile);
				$xml->loadFile($tmpFile);
			}
			catch (Exception $e) {
				$this->installation->getArchive()->getTar()->extract($fileIndex, $tmpFile);
				$xml->loadFile($tmpFile);
			}
		}
		
		@unlink($tmpFile);
		return $xml;
	}
	
	/**
	 * @see		AbstractXMLPackageInstallationPlugin::getXML()
	 * @deprecated
	 */
	protected function readXML() {
		return $this->getXML();
	}
	
	/**
	 * Returns the show order value.
	 * 
	 * @param	integer		$showOrder
	 * @param	string		$parentName
	 * @param	string		$columnName
	 * @param	string		$tableNameExtension
	 * @return	integer 	new show order
	 */
	protected function getShowOrder($showOrder, $parentName = null, $columnName = null, $tableNameExtension = '') {
		if ($showOrder === null) {
	        	 // get greatest showOrder value
	          	$sql = "SELECT	MAX(showOrder) AS showOrder
			  	FROM	wcf".WCF_N."_".$this->tableName.$tableNameExtension." 
				".($columnName !== null ? "WHERE ".$columnName." = '".escapeString($parentName)."'" : "");
			$maxShowOrder = WCF::getDB()->getFirstRow($sql);
			if (is_array($maxShowOrder) && isset($maxShowOrder['showOrder'])) {
				return $maxShowOrder['showOrder'] + 1;
			}
			else {
				return 1;
			}
	       	}
	       	else {
			// increase all showOrder values which are >= $showOrder
			$sql = "UPDATE	wcf".WCF_N."_".$this->tableName.$tableNameExtension."
				SET	showOrder = showOrder+1
				WHERE	showOrder >= ".$showOrder." 
				".($columnName !== null ? "AND ".$columnName." = '".escapeString($parentName)."'" : "");
			WCF::getDB()->sendQuery($sql);
			// return the wanted showOrder level
			return $showOrder;     
       		}
	}
}
?>