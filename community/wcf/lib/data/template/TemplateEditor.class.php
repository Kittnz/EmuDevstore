<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * TemplateEditor provides functions to create, edit or delete templates. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.template
 * @category 	Community Framework
 */
class TemplateEditor extends DatabaseObject {
	/**
	 * Create a new TemplateEditor object.
	 * 
	 * @param	string		$templateID
	 * @param	array		$row
	 */
	public function __construct($templateID, $row = null) {
		if ($row === null) {
			$sql = "SELECT		template.*, pack.templatePackFolderName, package.packageDir
				FROM		wcf".WCF_N."_template template
				LEFT JOIN	wcf".WCF_N."_template_pack pack
				ON		(pack.templatePackID = template.templatePackID)
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = template.packageID)
				WHERE		template.templateID = ".$templateID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		parent::__construct($row);
	}
	
	/**
	 * Creates a new template.
	 * 
	 * @param	string			$name
	 * @param	string			$source
	 * @param	integer			$templatePackID
	 * @param	integer			$packageID
	 * @return	TemplateEditor	new template
	 */
	public static function create($name, $source = '', $templatePackID = 0, $packageID = PACKAGE_ID) {
		$sql = "INSERT INTO	wcf".WCF_N."_template
					(packageID, templateName, templatePackID)
			VALUES		(".$packageID.", '".escapeString($name)."', ".$templatePackID.")";
		WCF::getDB()->sendQuery($sql);
		$templateID = WCF::getDB()->getInsertID();
		$template = new TemplateEditor($templateID);
		$template->setSource($source);
		
		return $template;
	}
	
	/**
	 * Returns the path to this template.
	 * 
	 * @return	string
	 */
	public function getPath() {
		$path = FileUtil::getRealPath(WCF_DIR . $this->packageDir) . 'templates/' . $this->templatePackFolderName . $this->templateName . '.tpl';
		return $path;
	}
	
	/**
	 * Saves the source of this template.
	 * 
	 * @param	string		$source 
	 */
	public function setSource($source) {
		$path = $this->getPath();
		// create dir
		$folder = dirname($path);
		if (!file_exists($folder) && !FileUtil::getSafeMode()) {
			mkdir($folder, 0777);
		}
		
		// set source		
		require_once(WCF_DIR.'lib/system/io/File.class.php');
		$file = new File($path);
		$file->write($source);
		$file->close();
		@$file->chmod(0777);
	}
	
	/**
	 * Returns the source of this template.
	 * 
	 * @return	string
	 */
	public function getSource() {
		return @file_get_contents($this->getPath());
	}
	
	/**
	 * Renames the file of this template.
	 * 
	 * @param	string		$name
	 * @param	integer		$templatePackID
	 */
	protected function rename($name, $templatePackID = 0) {
		// get current path
		$currentPath = $this->getPath();

		// get new path		
		$this->data['templatePackFolderName'] = '';
		if ($templatePackID != 0) {
			// get folder name
			$sql = "SELECT	templatePackFolderName
				FROM	wcf".WCF_N."_template_pack
				WHERE	templatePackID = ".$templatePackID;
			$row = WCF::getDB()->getFirstRow($sql);
			$this->data['templatePackFolderName'] = $row['templatePackFolderName'];
		}
		
		// delete compiled templates
		$this->deleteCompiledFiles();
		
		// rename
		$this->data['templateName'] = $name;
		$newPath = $this->getPath();
		
		// move file
		@rename($currentPath, $newPath);
	}
	
	/**
	 * Updates the data of this template.
	 * 
	 * @param	string		$name
	 * @param	string		$source
	 * @param	integer		$templatePackID
	 */
	public function update($name, $source = '', $templatePackID = 0) {
		// save new values
		$sql = "UPDATE	wcf".WCF_N."_template
			SET	templateName = '".escapeString($name)."',
				templatePackID = ".$templatePackID."
			WHERE	templateID = ".$this->templateID;
		WCF::getDB()->sendQuery($sql);
		
		// update name or path
		if ($this->templateName != $name || $this->templatePackID != $templatePackID) {
			$this->rename($name, $templatePackID);
		}
		
		$this->setSource($source);
	}
	
	/**
	 * Deletes this template.
	 */
	public function delete() {
		$this->deleteFile();
		
		self::deleteAll($this->templateID);
	}
	
	/**
	 * Deletes the file of this template.
	 */
	public function deleteFile() {
		// delete source
		@unlink($this->getPath());
		
		// delete compiled templates
		$this->deleteCompiledFiles();
	}
	
	/**
	 * Deletes the compiled files of this template.
	 */
	public function deleteCompiledFiles() {
		$matches = glob(WCF_DIR . 'templates/compiled/' . $this->packageID . '_*_' . $this->templateName . '.php');
		if (is_array($matches)) {
			foreach ($matches as $match) @unlink($match);
		}
	}
	
	/**
	 * Deletes the database entries of this given template ids.
	 */
	public static function deleteAll($templateIDs) {
		$sql = "DELETE FROM	wcf".WCF_N."_template
			WHERE		templateID IN (".$templateIDs.")";
		WCF::getDB()->sendQuery($sql);
	}
	
	/**
	 * Searches in templates.
	 * 
	 * @param	string		$search		search query
	 * @param	string		$replace
	 * @param	array		$templateIDs
	 * @param	boolean		$invertTemplates
	 * @param	boolean		$useRegex
	 * @param	boolean		$caseSensitive
	 * @param	boolean		$invertSearch
	 * @return	array		results 
	 */
	public static function search($search, $replace = null, $templateIDs = null, $invertTemplates = 0, $useRegex = 0, $caseSensitive = 0, $invertSearch = 0) {
		// get available template ids
		$results = array();
		$availableTemplateIDs = array();
		$sql = "SELECT		template.templateName, template.templateID, template.templatePackID, template.packageID
			FROM		wcf".WCF_N."_template template,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		template.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".PACKAGE_ID."
					".($replace !== null ? "AND template.templatePackID <> 0" : "")."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!isset($availableTemplateIDs[$row['templateName'].'-'.$row['templatePackID']]) || PACKAGE_ID == $row['packageID']) {
				$availableTemplateIDs[$row['templateName'].'-'.$row['templatePackID']] = $row['templateID'];
			}
		}
		
		// get templates
		if (!count($availableTemplateIDs)) return $results;
		$sql = "SELECT		template.*, pack.templatePackFolderName, package.packageDir
			FROM		wcf".WCF_N."_template template
			LEFT JOIN	wcf".WCF_N."_template_pack pack
			ON		(pack.templatePackID = template.templatePackID)
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = template.packageID)
			WHERE		template.templateID IN (".implode(',', $availableTemplateIDs).")
					".($templateIDs != null ? "AND template.templateID ".($invertTemplates ? "NOT " : "")."IN (".implode(',', $templateIDs).")" : "")."
			ORDER BY	templateName";
		$result = WCF::getDB()->sendQuery($sql);
		unset($availableTemplateIDs);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$template = new TemplateEditor(null, $row);
			if ($replace === null) {
				// search
				if ($useRegex) $matches = (intval(preg_match('/'.$search.'/s'.(!$caseSensitive ? 'i' : ''), $template->getSource())) !== 0);
				else {
					if ($caseSensitive) $matches = (StringUtil::indexOf($template->getSource(), $search) !== false);
					else $matches = (StringUtil::indexOfIgnoreCase($template->getSource(), $search) !== false);
				}
				
				if (($matches && !$invertSearch) || (!$matches && $invertSearch)) {
					$results[] = $row;
				}
			}
			else {
				// search and replace
				$matches = 0;
				if ($useRegex) {
					$newSource = preg_replace('/'.$search.'/s'.(!$caseSensitive ? 'i' : ''), $replace, $template->getSource(), -1, $matches);
				}
				else {
					if ($caseSensitive) $newSource = StringUtil::replace($search, $replace, $template->getSource(), $matches);
					else $newSource = StringUtil::replaceIgnoreCase($search, $replace, $template->getSource(), $matches);
				}
				
				if ($matches > 0) {
					$template->setSource($newSource);
					$row['matches'] = $matches;
					$results[] = $row;
				}
			}
		}
		
		return $results;
	}
}
?>