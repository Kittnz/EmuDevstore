<?php
// wcf imports
if (!defined('NO_IMPORTS')) {
	require_once(WCF_DIR.'lib/system/cache/source/CacheSource.class.php');
	require_once(WCF_DIR.'lib/system/cache/source/MemcacheAdapter.class.php');
}

/**
 * MemcacheCacheSource is an implementation of CacheSource that uses a Memcache server to store cached variables.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.source
 * @category 	Community Framework
 */
class MemcacheCacheSource implements CacheSource {
	/**
	 * MemcacheAdapter object
	 *
	 * @var MemcacheAdapter
	 */
	protected $adapter = null;
	
	/**
	 * list of cache resources
	 *
	 * @var array<string>
	 */
	protected $cacheResources = null;
	
	/**
	 * list of new cache resources
	 * 
	 * @var	array<string>
	 */
	protected $newLogEntries = array();
	
	/**
	 * list of obsolete resources
	 * 
	 * @var	array<string>
	 */
	protected $obsoleteLogEntries = array();
	
	/**
	 * Creates a new MemcacheCacheSource object.
	 */
	public function __construct() {
		$this->adapter = MemcacheAdapter::getInstance();
	}
	
	/**
	 * Returns the memcache adapter.
	 *
	 * @return	MemcacheAdapter
	 */
	public function getAdapter() {
		return $this->adapter;
	}
	
	// internal log functions
	/**
	 * Loads the cache log.
	 */
	protected function loadLog() {
		if ($this->cacheResources === null) {
			$this->cacheResources = array();
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_cache_resource";
			$result = WCF::getDB()->sendQuery($sql);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->cacheResources[] = $row['cacheResource'];
			}
		}
	}
	
	/**
	 * Saves modifications of the cache log.
	 */
	protected function updateLog() {
		if (count($this->newLogEntries)) {
			$sql = "INSERT IGNORE INTO	wcf".WCF_N."_cache_resource
							(cacheResource)
				VALUES			('".implode("'),('", array_map('escapeString', $this->newLogEntries))."')";
			WCF::getDB()->registerShutdownUpdate($sql);
		}
		if (count($this->obsoleteLogEntries)) {
			$sql = "DELETE FROM	wcf".WCF_N."_cache_resource
				WHERE		cacheResource IN ('".implode("','", array_map('escapeString', $this->obsoleteLogEntries))."')";
			WCF::getDB()->registerShutdownUpdate($sql);
		}
	}
	
	/**
	 * Adds a cache resource to cache log.
	 *
	 * @param	string		$cacheResource
	 */
	protected function addToLog($cacheResource) {
		$this->newLogEntries[] = $cacheResource;
	}
	
	/**
	 * Removes an obsolete cache resource from cache log.
	 *
	 * @param	string		$cacheResource
	 */
	protected function removeFromLog($cacheResource) {
		$this->obsoleteLogEntries[] = $cacheResource;
	}
	
	// CacheSource implementations
	/**
	 * @see CacheSource::get()
	 */
	public function get($cacheResource) {
		$value = $this->getAdapter()->getMemcache()->get($cacheResource['file']);
		if ($value === false) return null;
		return $value;
	}
	
	/**
	 * @see CacheSource::set()
	 */
	public function set($cacheResource, $value) {
		$this->getAdapter()->getMemcache()->set($cacheResource['file'], $value, MEMCACHE_COMPRESSED, $cacheResource['maxLifetime']);
		$this->addToLog($cacheResource['file']);
	}
	
	/**
	 * @see CacheSource::delete()
	 */
	public function delete($cacheResource, $ignoreLifetime = false) {
		$this->getAdapter()->getMemcache()->delete($cacheResource['file']);
		$this->removeFromLog($cacheResource['file']);
	}
	
	/**
	 * @see CacheSource::clear()
	 */
	public function clear($directory, $filepattern, $forceDelete = false) {
		$this->loadLog();
		$pattern = preg_quote(FileUtil::addTrailingSlash($directory), '%').str_replace('*', '.*', str_replace('.', '\.', $filepattern));
		foreach ($this->cacheResources as $cacheResource) {
			if (preg_match('%^'.$pattern.'$%i', $cacheResource)) {
				$this->getAdapter()->getMemcache()->delete($cacheResource);
				$this->removeFromLog($cacheResource);
			}
		}
	}
	
	/**
	 * @see CacheSource::flush()
	 */
	public function flush() {
		// clear cache
		$this->getAdapter()->getMemcache()->flush();
		
		// clear log
		$this->newLogEntries = $this->obsoleteLogEntries = array();
		WCF::getDB()->sendQuery("DELETE FROM wcf".WCF_N."_cache_resource");
	}
	
	/**
	 * @see CacheSource::close()
	 */
	public function close() {
		// update log
		$this->updateLog();
		// close connection
		// if ($this->getAdapter() !== null && $this->getAdapter()->getMemcache() !== null) $this->getAdapter()->getMemcache()->close();
	}
}
?>