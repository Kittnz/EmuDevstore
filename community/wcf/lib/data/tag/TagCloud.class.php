<?php
require_once(WCF_DIR.'lib/data/tag/Tag.class.php');

/**
 * This class holds a list of tags that can be used for creating a tag cloud.
 * 
 * @author 	Arian Glander
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.tagging
 * @subpackage	data.tag
 * @category 	Community Framework
 */
class TagCloud {
	/**
	 * List of Tags
	 *
	 * @var array<Tag>
	 */
	protected $tags = null;

	/**
	 * max value of tag counter
	 *
	 * @var integer
	 */
	protected $maxCounter = 0;

	/**
	 * min value of tag counter
	 *
	 * @var integer
	 */
	protected $minCounter = 4294967295;
	
	/**
	 * active language ids
	 * 
	 * @var	array<integer>
	 */
	protected $languageIDArray = array();
	
	/**
	 * cache name
	 *
	 * @var	string
	 */
	protected $cacheName = '';

	/**
	 * Contructs a new TagCloud.
	 *
	 * @param	array<integer>	$languageIDArray
	 */
	public function __construct($languageIDArray = array()) {
		$this->languageIDArray = $languageIDArray;
		if (!count($this->languageIDArray)) $this->languageIDArray = array(0);
		
		// init cache
		$this->cacheName = 'tagCloud-'.PACKAGE_ID.'-'.implode(',', $this->languageIDArray);
		$this->loadCache();
	}

	/**
	 * Loads the tag cloud cache.
	 */
	public function loadCache() {
		if ($this->tags !== null) return;

		// get cache
		WCF::getCache()->addResource($this->cacheName, WCF_DIR.'cache/cache.tagCloud-'.PACKAGE_ID.'-'.StringUtil::getHash(implode(',', $this->languageIDArray)).'.php', WCF_DIR.'lib/system/cache/CacheBuilderTagCloud.class.php', 0, 86400);
		$this->tags = WCF::getCache()->get($this->cacheName);
	}
	
	/**
	 * Clears the tag cloud cache.
	 */
	public function clearCache() {
		WCF::getCache()->clearResource($this->cacheName);
	}

	/**
	 * Gets a list of weighted tags.
	 *
	 * @param	integer		$slice
	 * @return	array<Tag>	the tags to get
	 */
	public function getTags($slice = 50) {
		// slice list
		$tags = array_slice($this->tags, 0, min($slice, count($this->tags)));
		
		// get min / max counter
		foreach ($tags as $tag) {
			if ($tag->getCounter() > $this->maxCounter) $this->maxCounter = $tag->getCounter();
			if ($tag->getCounter() < $this->minCounter) $this->minCounter = $tag->getCounter();
		}
		
		// assign sizes
		foreach ($tags as $tag) {
			$tag->setSize($this->calculateSize($tag->getCounter()));
		}
		
		// sort alphabetically
		ksort($tags);
		
		// return tags
		return $tags;
	}
	
	/**
	 * Calculate the size of the tag in a weighted list
	 *
	 * @param	integer 	$counter 	the number of times a tag has been used
	 * @return	double 				the size to calculate
	 */
	private function calculateSize($counter) {
		$maxSize = 250;
		$minSize = 80;

		if ($this->maxCounter == $this->minCounter) {
			return 100;
		}
		else {
			return ($maxSize - $minSize) / ($this->maxCounter - $this->minCounter) * $counter + $minSize - (($maxSize - $minSize) / ($this->maxCounter - $this->minCounter)) * $this->minCounter;
		}
	}
}
?>