<?php
/**
 * Provides functions to do benchmarks.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.benchmark
 * @category 	Community Framework
 */
class Benchmark {
	protected $startTime;
	protected $items 	= array();
	protected $queryCount 	= 0;
	protected $queryTime	= 0;

	/**
	 * Creates a new Benchmark object.
	 */
	public function __construct() {
		$this->startTime = self::getMicrotime();
	}

	/**
	 * Starts a benchmark.
	 * 
	 * @param	string		$text
	 * @param	integer		$type
	 * @return	integer		index
	 */
	public function start($text, $type = 0) {
		$newIndex = count($this->items);
		$this->items[$newIndex]['text']	= $text;
		$this->items[$newIndex]['type']	= $type;
		$this->items[$newIndex]['before'] = self::getMicrotime();
		$this->items[$newIndex]['start'] = $this->compareMicrotimes($this->startTime, $this->items[$newIndex]['before']);
		return $newIndex;
	}

	/**
	 * Stops an active benchmark.
	 * 
	 * @param	integer		$index
	 */
	public function stop($index = null) {
		if ($index === null) {
			$index = count($this->items) - 1;
		}
	
		$this->items[$index]['after'] = self::getMicrotime();
		$this->items[$index]['use']  = $this->compareMicrotimes($this->items[$index]['before'], $this->items[$index]['after']);
		$this->items[$index]['end'] = $this->compareMicrotimes($this->startTime, $this->items[$index]['after']);
		if ($this->items[$index]['type'] == 1) {
			$this->queryCount++;
			$this->queryTime += $this->items[$index]['use'];
		}
	}

	/**
	 * Returns the current unix timestamp as a float.
	 * 
	 * @return	float		unix timestamp
	 */
	protected static function getMicrotime() {
		return microtime(true);
	}

	/**
	 * Calculates the difference of two unix timestamps.
	 * 
	 * @param	float		$startTime
	 * @param	float		$endTime
	 * @return	float		difference
	 */
	protected static function compareMicrotimes($startTime, $endTime) {
		return round($endTime - $startTime, 4);
	}
 	
 	/**
 	 * Returns the output of all benchmarks.
 	 * 
 	 * @return	string		output
 	 */
	public function getResult() {
		$output = "<ul>";

		for ($i = 0; $i < count($this->items); $i++) {
			if (!isset($this->items[$i]['after'])) continue;
			$output .= "<li><b>".$this->items[$i]['text']."</b><br />start: ".$this->items[$i]['start']."; end: ".$this->items[$i]['end']."; exec time: ".$this->items[$i]['use']."</li>";	
		}

		$output .= "<li><b>".$this->queryCount." sql queries.</b><br />total exec time: ".$this->compareMicrotimes($this->startTime, self::getMicrotime())."; for sql queries: ".$this->queryTime.";</li>";
		$output .= "</ul>";
		return $output;
	}
}
?>