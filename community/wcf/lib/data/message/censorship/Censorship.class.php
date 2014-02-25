<?php
/**
 * Finds censored words.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.censorship
 * @subpackage	data.message.censorship
 * @category 	Community Framework
 */
class Censorship {
	/**
	 * list of words
	 * 
	 * @var	array<string>
	 */
	protected static $words = array();
	
	/**
	 * list of matches
	 * 
	 * @var	array
	 */
	protected static $matches = array();
	
	/**
	 * word delimiters
	 * 
	 * @var	string
	 */
	protected static $delimiters = '[\s\x21-\x2F\x3A-\x40\x5B-\x60\x7B-\x7E]';
						
	/**
	 * Returns censored words from a text. 
	 * 
	 * @param	string		$text
	 * @return	mixed		$matches / false
	 */
	public static function test($text) {
		// reset matches
		self::$matches = array();
		
		// get words which should be censored
    		$censoredWords = explode("\n", StringUtil::unifyNewlines(StringUtil::toLowerCase(CENSORED_WORDS)));

		// format censored words
		$censoredWords = ArrayUtil::trim($censoredWords);
		
		// string to lower case
		$text = StringUtil::toLowerCase($text);
		
		// ignore bbcode tags
		$text = preg_replace('~\[/?[a-z]+[^\]]*\]~i', '', $text);
		
		// split the text in single words
		self::$words = preg_split("!".self::$delimiters."+!", $text, -1, PREG_SPLIT_NO_EMPTY);
		
		// check each word if it censored.
		for ($i = 0, $count = count(self::$words); $i < $count; $i++) {
			$word = self::$words[$i];
			foreach ($censoredWords as $censoredWord) {
				// check for direct matches ("badword" == "badword")
				if ($censoredWord == $word) {
					// store censored word
					if (isset(self::$matches[$word])) {
						self::$matches[$word]++;
					}
					else {
						self::$matches[$word] = 1;
					}
					
					continue 2;	
				}
				// check for asterisk matches ("*badword*" == "FooBadwordBar")
				else if (StringUtil::indexOf($censoredWord, '*') !== false) {
					$censoredWord = StringUtil::replace('\*', '.*', preg_quote($censoredWord));
					if (preg_match('!^'.$censoredWord.'$!', $word)) {
						// store censored word
						if (isset(self::$matches[$word])) {
							self::$matches[$word]++;
						}
						else {
							self::$matches[$word] = 1;
						}
						
						continue 2;	
					}
				}
				// check for partial matches ("~badword~" == "bad-word")
				else if (StringUtil::indexOf($censoredWord, '~') !== false) {
					$censoredWord = StringUtil::replace('~', '', $censoredWord);
					if (($position = StringUtil::indexOf($censoredWord, $word)) !== false) {
						if ($position > 0) {
							// look behind
							if (!self::lookBehind($i - 1, StringUtil::substring($censoredWord, 0, $position))) {
								continue;
							}
						}
						
						if ($position + StringUtil::length($word) < StringUtil::length($censoredWord)) {
							// look ahead
							if (($newIndex = self::lookAhead($i + 1, StringUtil::substring($censoredWord, $position + StringUtil::length($word))))) {
								$i = $newIndex;
							}
							else {
								continue;
							}
						}
						
						// store censored word
						if (isset(self::$matches[$censoredWord])) {
							self::$matches[$censoredWord]++;
						}
						else {
							self::$matches[$censoredWord] = 1;
						}
						
						continue 2;
					}
				}
			}
		}
		
		// at least one censored word was found 
		if (count(self::$matches) > 0) {
			return self::$matches;
		}
		// text is clean
		else {
			return false;
		}
	}
	
	/**
	 * Looks behind in the word list.
	 * 
	 * @param	integer		$index
	 * @param	string		$search
	 * @return	boolean
	 */
	protected static function lookBehind($index, $search) {
		if (isset(self::$words[$index])) {
			if (StringUtil::indexOf(self::$words[$index], $search) === (StringUtil::length(self::$words[$index]) - StringUtil::length($search))) {
				return true;
			}
			else if (StringUtil::indexOf($search, self::$words[$index]) === (StringUtil::length($search) - StringUtil::length(self::$words[$index]))) {
				return self::lookBehind($index - 1, 0, (StringUtil::length($search) - StringUtil::length(self::$words[$index])));
			}
		}
		
		return false;
	}
	
	/**
	 * Looks ahead in the word list.
	 * 
	 * @param	integer		$index
	 * @param	string		$search
	 * @return	mixed
	 */
	protected static function lookAhead($index, $search) {
		if (isset(self::$words[$index])) {
			if (StringUtil::indexOf(self::$words[$index], $search) === 0) {
				return $index;
			}
			else if (StringUtil::indexOf($search, self::$words[$index]) === 0) {
				return self::lookAhead($index + 1, StringUtil::substring($search, StringUtil::length(self::$words[$index])));
			}
		}
		
		return false;
	}
}
?>