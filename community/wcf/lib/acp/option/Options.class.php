<?php
/**
 * Generates the options.inc.php file.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.option
 * @category 	Community Framework
 */
class Options {
	const FILENAME = 'options.inc.php';
	
	/**
	 * Saves options.
	 * 
	 * @param	array		$options
	 */
	public static function save($options) {
		foreach ($options as $optionID => $optionValue) {
			$sql = "UPDATE	wcf".WCF_N."_option
				SET	optionValue = '".escapeString($optionValue)."'
				WHERE	optionID = ".$optionID;
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * Resets the options cache resource.
	 */
	public static function resetCache() {
		WCF::getCache()->clear(WCF_DIR.'cache', 'cache.option-*.php', true);
	}
	
	/**
	 * Deletes relevant options.inc.php's
	 * 
	 * @param	array<integer>	$packageIDArray
	 */
	public static function resetFile($packageIDArray = PACKAGE_ID) {
		if (!is_array($packageIDArray)) {
                    	$packageIDArray = array($packageIDArray);
            	}
		$sql = "SELECT		package.packageID, package.packageDir
			FROM		wcf".WCF_N."_package_dependency package_dependency
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = package_dependency.packageID)
			WHERE		package_dependency.dependency IN (".implode(',', $packageIDArray).")
					AND package.standalone = 1
					AND package.package <> 'com.woltlab.wcf'
			GROUP BY    	package.packageID";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$filename = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR.$row['packageDir'])).self::FILENAME;
			if (file_exists($filename)) {
				if (!@touch($filename, 1)) {
					if (!@unlink($filename)) {
						self::rebuildFile($filename, $row['packageID']);
					}
				}
			}
		}
	}
	
	/**
	 * Rebuilds cached options
	 *
	 * @param 	string 		filename
	 * @param	integer		$packageID
	 */
	public static function rebuildFile($filename, $packageID = PACKAGE_ID) {
		$buffer = '';
		
		// file header
		$buffer .= "<?php\n/**\n* generated at ".gmdate('r')."\n*/\n";
		
		// get all options
		$options = self::getOptions($packageID);
		foreach ($options as $optionName => $option) {
			$buffer .= "define('".$optionName."', ".(($option['optionType'] == 'boolean' || $option['optionType'] == 'integer') ? intval($option['optionValue']) : "'".addcslashes($option['optionValue'], "'\\")."'").");\n";
		}
		unset($options);
		
		// file footer
		$buffer .= "?>";
		
		// open file
		require_once(WCF_DIR.'lib/system/io/File.class.php');
		$file = new File($filename);
		
		// write buffer
		$file->write($buffer);
		unset($buffer);
		
		// close file
		$file->close();
		@$file->chmod(0777);
	}
	
	/**
	 * Returns a list of options.
	 *
	 * @param	integer		$packageID
	 * @return	array
	 */
	public static function getOptions($packageID = PACKAGE_ID) {
		$sql = "SELECT		optionName, optionID 
			FROM		wcf".WCF_N."_option acp_option,
					wcf".WCF_N."_package_dependency package_dependency
			WHERE 		acp_option.packageID = package_dependency.dependency
					AND package_dependency.packageID = ".$packageID."
			ORDER BY	package_dependency.priority";
		$result = WCF::getDB()->sendQuery($sql);
		$optionIDs = array();
		while ($row = WCF::getDB()->fetchArray($result)) {
			$optionIDs[$row['optionName']] = $row['optionID'];
		}
		
		$options = array();
		if (count($optionIDs) > 0) {
			// get needed options
			$sql = "SELECT		optionName, optionValue, optionType
				FROM		wcf".WCF_N."_option
				WHERE		optionID IN (".implode(',', $optionIDs).")
				ORDER BY	optionName";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$options[strtoupper($row['optionName'])] = $row;
			}
		}
		
		return $options;
	}
	
	/**
	 * Returns a list of option values.
	 *
	 * @param	integer		$packageID
	 * @return	array
	 */
	public static function getOptionValues($packageID = PACKAGE_ID) {
		$options = self::getOptions($packageID);
		foreach ($options as $optionName => $option) {
			$options[$optionName] = $option['optionValue'];
		}
		
		return $options;
	}
}
?>