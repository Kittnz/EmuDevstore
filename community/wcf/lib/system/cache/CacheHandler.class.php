<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/cache/source/CacheSource.class.php');
}

/**
 * CacheHandler holds all registered cache resources.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache
 * @category 	Community Framework
 */
class CacheHandler {
	/**
	 * Registered cache resources.
	 * 
	 * @var array
	 */
	protected $cacheResources = array();
	
	/**
	 * cache source object
	 * 
	 * @var	CacheSource
	 */
	protected $cacheSource = null;
	
	/**
	 * Creates a new CacheHandler object.
	 */
	public function __construct() {
		// horizon update workaround
		if (!defined('CACHE_SOURCE_TYPE')) {
			define('CACHE_SOURCE_TYPE', 'disk');
		}

		// init cache source object
		try {
			$className = ucfirst(CACHE_SOURCE_TYPE).'CacheSource';
			if (!defined('NO_IMPORTS')) {
				require_once(WCF_DIR.'lib/system/cache/source/'.$className.'.class.php');
			}
			$this->cacheSource = new $className();
		}
		catch (SystemException $e) {
			if (CACHE_SOURCE_TYPE != 'disk') {
				// fallback to disk cache
				if (!defined('NO_IMPORTS')) {
					require_once(WCF_DIR.'lib/system/cache/source/DiskCacheSource.class.php');
					$this->cacheSource = new DiskCacheSource();
				}
			}
			else {
				throw $e;
			}
		}
	}
	
	/**
	 * Registers a new cache resource.
	 * 
	 * @param	string		$cache		name of this resource
	 * @param	string		$file		data file for this resource
	 * @param	string		$classFile
	 * @param	integer		$minLifetime
	 * @param	integer		$maxLifetime
	 */
	public function addResource($cache, $file, $classFile, $minLifetime = 0, $maxLifetime = 0) {
		$className = StringUtil::getClassName($classFile);
		
		$this->cacheResources[$cache] = array(
			'cache' => $cache,
			'file' => $file, 
			'className' => $className, 
			'classFile' => $classFile,
			'minLifetime' => $minLifetime,
			'maxLifetime' => $maxLifetime
		);
	}
	
	/**
	 * Deletes a registered cache resource.
	 * 
	 * @param 	string		$cache
	 * @param 	boolean		$ignoreLifetime
	 */
	public function clearResource($cache, $ignoreLifetime = false) {
		if (!isset($this->cacheResources[$cache])) {
			throw new SystemException("cache resource '".$cache."' does not exist", 11005);
		}
		
		$this->getCacheSource()->delete($this->cacheResources[$cache], $ignoreLifetime);
	}
	
	/**
	 * Marks cached files as obsolete.
	 *
	 * @param 	string 		$directory
	 * @param 	string 		$filepattern
	 * @param 	boolean		$forceDelete
	 */
	public function clear($directory, $filepattern, $forceDelete = false) {
		$this->getCacheSource()->clear($directory, $filepattern, $forceDelete);
	}
	
	/**
	 * Returns a cached variable.
	 *
	 * @param 	string 		$cache
	 * @param 	string 		$variable
	 * @return 	mixed 		$value
	 */
	public function get($cache, $variable = '') {
		if (!isset($this->cacheResources[$cache])) {
			throw new SystemException("unknown cache resource '".$cache."'", 11005);
		}
		
		// try to get value
		$value = $this->getCacheSource()->get($this->cacheResources[$cache]);
		if ($value === null) {
			// rebuild cache
			$this->rebuild($this->cacheResources[$cache]);
			
			// try to get value again
			$value = $this->getCacheSource()->get($this->cacheResources[$cache]);
			if ($value === null) {
				throw new SystemException("cache resource '".$cache."' does not exist", 11005);
			}
		}
		
		// return value
		if (!empty($variable)) {
			if (!isset($value[$variable])) {
				throw new SystemException("variable '".$variable."' does not exist in cache resource '".$cache."'", 11008);
			}
			
			return $value[$variable];
		}
		else {
			return $value;
		}
	}
	
	/**
	 * Rebuilds a cache resource.
	 * 
	 * @param 	array 		$cacheResource
	 * @return 	boolean 	result
	 */
	public function rebuild($cacheResource) {
		// include cache resource class
		if (!file_exists($cacheResource['classFile'])) {
			throw new SystemException("Unable to find class file '".$cacheResource['classFile']."'", 11000);
		}
		require_once($cacheResource['classFile']);

		// instance cache class
		if (!class_exists($cacheResource['className'])) {
			throw new SystemException("Unable to find class '".$cacheResource['className']."'", 11001);
		}
		
		// update file last modified time to avoid multiple users rebuilding cache at the same time
		if (get_class($this->getCacheSource()) == 'DiskCacheSource') {
			@touch($cacheResource['file']);
		}
		
		// build cache
		$cacheBuilder = new $cacheResource['className'];
		$value = $cacheBuilder->getData($cacheResource);

		// save cache
		$this->getCacheSource()->set($cacheResource, $value);
		
		return true;
	}
	
	/**
	 * Returns the cache source object.
	 *
	 * @return	CacheSource
	 */
	public function getCacheSource() {
		return $this->cacheSource;
	}
}
?>