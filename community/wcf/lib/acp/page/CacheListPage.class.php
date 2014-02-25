<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Shows a list of all cache resources.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class CacheListPage extends AbstractPage {
	// system
	public $templateName = 'cacheList';
	public $cleared = 0;
	
	/**
	 * contains a list of cache resources
	 *
	 * @var	array
	 */
	public $caches = array();
	
	/**
	 * contains general cache information
	 *
	 * @var array
	 */
	public $cacheData = array();
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['cleared'])) $this->cleared = intval($_REQUEST['cleared']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// init cache data
		$this->cacheData = array(
			'source' => get_class(WCF::getCache()->getCacheSource()),
			'version' => '',
			'size' => 0,
			'files' => 0
		);
		
		// filesystem cache
		if ($this->cacheData['source'] == 'DiskCacheSource') {
			// set version
			$this->cacheData['version'] = WCF_VERSION;

			// get package dirs
			$sql = "SELECT		package.packageDir
				FROM		wcf".WCF_N."_package_dependency package_dependency
				LEFT JOIN	wcf".WCF_N."_package package
				ON		(package.packageID = package_dependency.dependency)
				WHERE		package_dependency.packageID = ".PACKAGE_ID."
						AND standalone = 1";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$packageDir = FileUtil::getRealPath(WCF_DIR.$row['packageDir']);
				$cacheDir = $packageDir.'cache';
				if (file_exists($cacheDir)) {
					$this->caches[$cacheDir] = array();

					// get files in cache directory
					$files = glob($cacheDir.'/*.php');
					// get additional file information
					if (is_array($files)) {
						foreach ($files as $file) {
							$filesize = filesize($file);
							$this->caches[$cacheDir][] = array(
								'filename' => basename($file),
								'filesize' => $filesize,
								'mtime' => filemtime($file),
								'perm' => substr(sprintf('%o', fileperms($file)), -3),
								'writable' => is_writable($file)
							);
							
							$this->cacheData['files']++;
							$this->cacheData['size'] += $filesize;
						}
					}
				}
			}
		}
		// memcache
		else if ($this->cacheData['source'] == 'MemcacheCacheSource') {
			// get version
			require_once(WCF_DIR.'lib/system/cache/source/MemcacheAdapter.class.php');
			$this->cacheData['version'] = MemcacheAdapter::getInstance()->getMemcache()->getVersion();
			
			// get stats
			$stats = MemcacheAdapter::getInstance()->getMemcache()->getStats();
			$this->cacheData['files'] = $stats['curr_items'];
			$this->cacheData['size'] = $stats['bytes'];
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'caches' => $this->caches,
			'cacheData' => $this->cacheData,
			'cleared' => $this->cleared
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// enable menu item
		WCFACP::getMenu()->setActiveMenuItem('wcf.acp.menu.link.log.cache');
		
		// check permission
		WCF::getUser()->checkPermission('admin.system.canViewLog');
		
		parent::show();
	}
}
?>