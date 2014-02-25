<?php
// wcf imports
require_once(WCF_DIR.'lib/data/template/TemplatePack.class.php');

/**
 * TemplateEditor provides functions to create, edit or delete template packs. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.template
 * @category 	Community Framework
 */
class TemplatePackEditor extends TemplatePack {
	/**
	 * Updates the data of this template pack.
	 * 
	 * @param	string		$name
	 * @param	string		$folderName
	 * @param	integer		$parentTemplatePackID
	 */
	public function update($name, $folderName, $parentTemplatePackID = 0) {
		$sql = "UPDATE	wcf".WCF_N."_template_pack
			SET	templatePackName = '".escapeString($name)."',
				templatePackFolderName = '".escapeString($folderName)."',
				parentTemplatePackID = ".$parentTemplatePackID."
			WHERE	templatePackID = ".$this->templatePackID;
		WCF::getDB()->sendQuery($sql);
		
		if ($folderName != $this->templatePackFolderName) {
			$this->renameFolders($folderName);
		}
	}
	
	/**
	 * Renames the folders of this template pack.
	 * 
	 * @param	string		$newFolderName
	 */
	public function renameFolders($newFolderName) {
		// default template dir
		$folders = array(WCF_DIR . 'templates/' . $this->templatePackFolderName => WCF_DIR . 'templates/' . $newFolderName);
		
		// get package dirs
		$sql = "SELECT	packageDir
			FROM	wcf".WCF_N."_package
			WHERE	packageDir <> ''";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$packageDir = FileUtil::getRealPath(WCF_DIR . $row['packageDir']);
			$folders[$packageDir . 'templates/' . $this->templatePackFolderName] = $packageDir . 'templates/' . $newFolderName;
		}
		
		// rename folders
		foreach ($folders as $oldName => $newName) {
			if (file_exists($oldName)) {
				@rename($oldName, $newName);
			}
		}
	}
	
	/**
	 * Deletes this template pack.
	 */
	public function delete() {
		// update children
		$sql = "UPDATE	wcf".WCF_N."_template_pack
			SET	parentTemplatePackID = ".$this->parentTemplatePackID."
			WHERE	parentTemplatePackID = ".$this->templatePackID;
		WCF::getDB()->sendQuery($sql);
		
		// delete template pack
		$sql = "DELETE FROM	wcf".WCF_N."_template_pack
			WHERE		templatePackID = ".$this->templatePackID;
		WCF::getDB()->sendQuery($sql);
		
		// delete templates
		$sql = "DELETE FROM	wcf".WCF_N."_template
			WHERE		templatePackID = ".$this->templatePackID;
		WCF::getDB()->sendQuery($sql);
		
		$this->deleteFolders();
	}

	/**
	 * Deletes the folders of this template pack.
	 */
	public function deleteFolders() {
		// default template dir
		$folders = array(WCF_DIR . 'templates/' . $this->templatePackFolderName);
		
		// get package dirs
		$sql = "SELECT	packageDir
			FROM	wcf".WCF_N."_package
			WHERE	packageDir <> ''";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$packageDir = FileUtil::getRealPath(WCF_DIR . $row['packageDir']);
			$folders[] = $packageDir . 'templates/' . $this->templatePackFolderName;
		}
		
		// rename folders
		foreach ($folders as $folder) {
			if (file_exists($folder)) {
				// empty folder
				$files = glob(FileUtil::addTrailingSlash($folder).'*');
				if (is_array($files)) {
					foreach ($files as $file) @unlink($file);
				}
				
				// delete foler
				@rmdir($folder);
			}
		}
	}
	
	/**
	 * Creates a new template pack.
	 * 
	 * @param	string		$name
	 * @param	string		$folderName
	 * @return	integer		template pack id
	 */
	public static function create($name, $folderName, $parentTemplatePackID = 0) {
		$sql = "INSERT INTO	wcf".WCF_N."_template_pack
					(templatePackName, templatePackFolderName, parentTemplatePackID)
			VALUES		('".escapeString($name)."', '".escapeString($folderName)."', ".$parentTemplatePackID.")";
		WCF::getDB()->sendQuery($sql);
		return WCF::getDB()->getInsertID();
	}
}
?>