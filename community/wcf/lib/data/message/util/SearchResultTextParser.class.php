<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/util/KeywordHighlighter.class.php');

/**
 * Formats messages for search result output.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message
 * @subpackage	data.message.util
 * @category 	Community Framework
 */
class SearchResultTextParser {
	const MAX_LENGTH = 500;
	protected static $searchQuery = null;
	
	/**
	 * Gets the search query keywords.
	 */
	protected static function getSearchQuery() {
		self::$searchQuery = false;
		
		if (isset($_GET['highlight'])) {
			$keywordString = $_GET['highlight'];
			
			// remove search operators
			$keywordString = preg_replace('/[\+\-><()~\*]+/', '', $keywordString);
			
			if (StringUtil::substring($keywordString, 0, 1) == '"' && StringUtil::substring($keywordString, -1) == '"') {
				// phrases search
				$keywordString = StringUtil::trim(StringUtil::substring($keywordString, 1, -1));
				
				if (!empty($keywordString)) {
					self::$searchQuery = $keywordString;
				}
			}
			else {
				self::$searchQuery = ArrayUtil::trim(explode(' ', $keywordString));
				if (count(self::$searchQuery) == 0) self::$searchQuery = false;
				else if (count(self::$searchQuery) == 1) self::$searchQuery = reset(self::$searchQuery);
			}
		}
	}
	
	/**
	 * Returns an abstract of the given message. 
	 * Uses search keywords to shift the start and end position of the abstract (like Google).
	 * 
	 * @param	string		$text
	 * @return	string
	 */
	protected static function getMessageAbstract($text) {
		// replace newlines with spaces
		$text = preg_replace("/\s+/", ' ', $text);
	
		if (StringUtil::length($text) > self::MAX_LENGTH) {
			// get search query
			if (self::$searchQuery === null) self::getSearchQuery();
			
			if (self::$searchQuery) {
				// phrase search
				if (!is_array(self::$searchQuery)) {
					$start = StringUtil::indexOfIgnoreCase($text, self::$searchQuery);
					if ($start !== false) {
						$end = $start + StringUtil::length(self::$searchQuery);
						$shiftStartBy = $shiftEndBy = round((self::MAX_LENGTH - StringUtil::length(self::$searchQuery)) / 2);
						
						// shiftStartBy is negative when search query length is over max length
						if ($shiftStartBy < 0) {
							$shiftEndBy += $shiftStartBy;
							$shiftStartBy = 0;
						}
							
						// shift abstract start
						if ($start - $shiftStartBy < 0) {
							$shiftEndBy += $shiftStartBy - $start;
							$start = 0;
						}
						else {
							$start -= $shiftStartBy;
						}
						
						// shift abstract end
						if ($end + $shiftEndBy > StringUtil::length($text) - 1) {
							$shiftStartBy = $end + $shiftEndBy - StringUtil::length($text) - 1;
							$shiftEndBy = 0;
							if ($shiftStartBy > $start) {
								$start = 0;
							}
							else {
								$start -= $shiftStartBy;
							}
						}
						else {
							$end += $shiftEndBy;
						}
						
						$newText = '';
						if ($start > 0) $newText .= '...';
						$newText .= StringUtil::substring($text, $start, $end - $start);
						if ($end < StringUtil::length($text) - 1) $newText .= '...';
						return $newText;
					}
				}
				else {
					$matches = array();
					$shiftLength = self::MAX_LENGTH;
					// find first match of each keyword
					foreach (self::$searchQuery as $keyword) {
						$start = StringUtil::indexOfIgnoreCase($text, $keyword);
						if ($start !== false) {
							$shiftLength -= StringUtil::length($keyword);
							$matches[$keyword] = array('start' => $start, 'end' => $start + StringUtil::length($keyword));
						}
					}
					
					// shift match position
					$shiftBy = round(($shiftLength / count(self::$searchQuery)) / 2);
					foreach ($matches as $keyword => $position) {
						$position['start'] -= $shiftBy;
						$position['end'] += $shiftBy;
						$matches[$keyword] = $position;
					}
					
					$start = 0;
					$end = StringUtil::length($text) - 1;
					$newText = '';
					$i = 0; $length = count($matches);
					foreach ($matches as $keyword => $position) {
						if ($position['start'] < $start) {
							$position['end'] += $start - $position['start'];
							$position['start'] = $start;
						}
						
						if ($position['end'] > $end) {
							if ($position['start'] > $start) {
								$shiftStartBy = $position['end'] - $end;
								if ($position['start'] - $shiftStartBy < $start) {
									$shiftStartBy = $position['start'] - $start;
								}
								
								$position['start'] -= $shiftStartBy;
							}
							
							$position['end'] = $end;
						}
						
						if ($position['start'] > $start) $newText .= '...';
						$newText .= StringUtil::substring($text, $position['start'], $position['end'] - $position['start']);
						if ($i == $length - 1 && $position['end'] < $end) $newText .= '...';
						
						$start = $position['end'];
						$i++;
					}
					
					if (!empty($newText)) return $newText;
				}
			}
		
			// no search query or no matches
			return StringUtil::substring($text, 0, self::MAX_LENGTH) . '...';
		}
		
		return $text;
	}
	
	/**
	 * Formats a message for search result output.
	 * 
	 * @param	string		$text
	 * @return	string
	 */
	public static function parse($text) {
		// remove html codes
		$text = StringUtil::stripHTML($text);
		
		// decode html
		$text = StringUtil::decodeHTML($text);
		
		// get abstract
		$text = self::getMessageAbstract($text);
		
		// encode html
		$text = StringUtil::encodeHTML($text);
		
		// do highlighting
		return KeywordHighlighter::doHighlight($text);
	}
}
?>