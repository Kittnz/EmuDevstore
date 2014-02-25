<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractPackageInstallationPlugin.class.php');

/**
 * This PIP installs, updates or deletes styles.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.system.style
 * @subpackage	acp.package.plugin
 * @category 	Community Framework
 */
class StylePackageInstallationPlugin extends AbstractPackageInstallationPlugin {
	public $tagName = 'style';
	public $tableName = 'style';
	
	/** 
	 * @see PackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();
		
		require_once(WCF_DIR.'lib/data/style/StyleEditor.class.php');
		$instructions = $this->installation->getInstructions();
		$styles = $instructions['style'];
		if (count($styles) && isset($styles['cdata'])) $styles = array($styles);
		
		// Install each <style>-tag from package.xml
		foreach ($styles as $styleData) {
			// extract style tar from package archive
			// No <style>-tag in the instructions in package.xml
			if (!isset($styleData['cdata']) || !$styleData['cdata']) {
				return false;
			}
			
			// extract style tar
			$filename = $this->installation->getArchive()->extractTar($styleData['cdata'], 'style_');
			
			// import style
			$style = StyleEditor::import($filename, $this->installation->getPackageID());
			
			// set wcf basic style as default
			if (isset($styleData['default'])) {
				$style->setAsDefault();
			}
			
			// delete tmp file
			@unlink($filename);
		}
	}
	
	/** 
	 * @see PackageInstallationPlugin::uninstall()
	 */
	public function uninstall() {
		// call uninstall event
		EventHandler::fireAction($this, 'uninstall');
		
		// get all style of this package
		$isDefault = false;
		require_once(WCF_DIR.'lib/data/style/StyleEditor.class.php');
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_style
			WHERE	packageID = ".$this->installation->getPackageID();
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			// delete style
			$style = new StyleEditor(null, $row);
			$style->delete();
			
			$isDefault = $isDefault || $style->isDefault;
		}
		
		// default style deleted
		if ($isDefault) {
			$sql = "SELECT		*
				FROM		wcf".WCF_N."_style
				ORDER BY	styleID";
			$row = WCF::getDB()->getFirstRow($sql);
			if (!empty($row['styleID'])) {
				$style = new StyleEditor(null, $row);
				$style->setAsDefault();
			}
		}
	}
}
?>