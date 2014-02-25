<?php
// wcf imports
require_once(WCF_DIR.'lib/system/cache/CacheBuilder.class.php');

/**
 * Caches the paths of icons.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.system.style
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheBuilderIcon implements CacheBuilder {
	/**
	 * @see CacheBuilder::getData()
	 */
	public function getData($cacheResource) {
		list($cache, $packageID, $styleID) = explode('-', $cacheResource['cache']); 
		$data = array();

		// get active package
		require_once(WCF_DIR.'lib/acp/package/Package.class.php');
		$activePackage = new Package($packageID);
		$activePackageDir = FileUtil::getRealPath(WCF_DIR.$activePackage->getDir());
		
		// get package dirs
		$packageDirs = array();
		$sql = "SELECT		DISTINCT packageDir
			FROM		wcf".WCF_N."_package_dependency dependency
			LEFT JOIN	wcf".WCF_N."_package package
			ON		(package.packageID = dependency.dependency)
			WHERE		dependency.packageID = ".$packageID."
					AND packageDir <> ''
			ORDER BY	priority DESC";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			$packageDirs[] = FileUtil::getRealPath(WCF_DIR.$row['packageDir']);
		}
		$packageDirs[] = WCF_DIR;
		
		// get style icon path
		$iconDirs = array();
		$sql = "SELECT	variableValue
			FROM	wcf".WCF_N."_style_variable
			WHERE	styleID = ".$styleID."
				AND variableName = 'global.icons.location'";
		$row = WCF::getDB()->getFirstRow($sql);
		if (!empty($row['variableValue'])) $iconDirs[] = FileUtil::addTrailingSlash($row['variableValue']);
		if (!in_array('icon/', $iconDirs)) $iconDirs[] = 'icon/';
		
		// get icons
		foreach ($packageDirs as $packageDir) {
			$relativePackageDir = ($activePackageDir != $packageDir ? FileUtil::getRelativePath($activePackageDir, $packageDir) : '');
			
			foreach ($iconDirs as $iconDir) {
				$path = FileUtil::addTrailingSlash($packageDir.$iconDir);
				$icons = self::getIconFiles($path);
				foreach ($icons as $icon) {
					$icon = str_replace($path, '', $icon);
					if (!isset($data[$icon])) {
						$data[$icon] = $relativePackageDir.$iconDir.$icon;
					}
				}
			}
		}
		
		return $data;
	}
	
	protected static function getIconFiles($path) {
		$files = array();
		if (is_dir($path)) {
			if ($dh = opendir($path)) {
				while (($file = readdir($dh)) !== false) {
					if ($file == '.' || $file == '..') continue;
					if (is_dir($path.$file)) {
						$files = array_merge($files, self::getIconFiles(FileUtil::addTrailingSlash($path.$file)));
					}
					else if (preg_match('/\.png$/', $file)) {
						$files[] = $path.$file;
					}
				}
				closedir($dh);
			}
		}
		
		return $files;
	}
}
?>