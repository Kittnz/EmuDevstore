<?php
/**
 * Highlights keywords in text messages.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message
 * @subpackage	data.message.util
 * @category 	Community Framework
 */
class KeywordHighlighter {
	protected static $keywords = null;
	protected static $searchQueryKeys = array(
		'q', 		// google, msn, altavista
		'p', 		// yahoo
		'query', 	// lycos, fireball
		'eingabe',	// metager
		'begriff',	// acoon.de
		'keyword',	// fixx.de
		'search',	// excite.co.jp
		'highlight',	// burning board and other bulletin board systems ;)
		
		// ???:
		'ask',
		'searchfor',
		'key',
		'keywords',
		'qry',
		'searchitem',
		'kwd',
		'recherche',
		'search_text',
		'search_term',
		'term',
		'terms',
		'qq',
		'qry_str',
		'qu',
		//'s',
		//'k',
		//'t',
		'va'
	);
	
	/**
	 * Gets the search keywords.
	 */
	protected static function getSearchKeywords() {
		self::$keywords = array();
		
		// take keywords from request
		if (isset($_GET['highlight'])) {
			self::parseKeywords($_GET['highlight']);
		}
		// take keywords from referer
		else if (!empty($_SERVER['HTTP_REFERER'])) {
			$url = parse_url($_SERVER['HTTP_REFERER']);
			if (!empty($url['query'])) {
				$query = explode('&', $url['query']);
				foreach ($query as $element) {
					if (strpos($element, '=') === false) continue;
					list($varname, $value) = explode('=', $element);
					
					if (in_array($varname, self::$searchQueryKeys)) {
						self::parseKeywords(urldecode($value));
						break;
					}
				}
			}
		}
		
		if (count(self::$keywords) > 0) {
			self::$keywords = array_unique(self::$keywords);
			self::$keywords = array_map('preg_quote', self::$keywords);
		}
	}
	
	/**
	 * Parses search keywords.
	 * 
	 * @param	string		$keywordString
	 */
	protected static function parseKeywords($keywordString) {
		// convert encoding if necessary
		if (CHARSET == 'UTF-8' && !StringUtil::isASCII($keywordString) && !StringUtil::isUTF8($keywordString)) {
			$keywordString = StringUtil::convertEncoding('ISO-8859-1', 'UTF-8', $keywordString);
		}
		
		// remove bad wildcards
		$keywordString = preg_replace('/(?<!\w)\*/', '', $keywordString);
		
		// remove search operators
		$keywordString = preg_replace('/[\+\-><()~]+/', '', $keywordString);
		
		if (StringUtil::substring($keywordString, 0, 1) == '"' && StringUtil::substring($keywordString, -1) == '"') {
			// phrases search
			$keywordString = StringUtil::trim(StringUtil::substring($keywordString, 1, -1));
			
			if (!empty($keywordString)) {
				self::$keywords = array_merge(self::$keywords, array(StringUtil::encodeHTML($keywordString)));
			}
		}
		else {	
			// replace word delimiters by space
			$keywordString = preg_replace('/[.,]/', ' ', $keywordString);
			
			$keywords = ArrayUtil::encodeHTML(ArrayUtil::trim(explode(' ', $keywordString)));
			if (count($keywords) > 0) {
				self::$keywords = array_merge(self::$keywords, $keywords);
			}
		}
	}
	
	/**
	 * Highlights search keywords.
	 * 
	 * @param	string		$text
	 * @return	string		text
	 */
	public static function doHighlight($text) {
		if (self::$keywords == null) self::getSearchKeywords();
		if (count(self::$keywords) == 0) return $text;
		
		$keywordPattern = '('.implode('|', self::$keywords).')';
		$keywordPattern = StringUtil::replace('\*', '\w*', $keywordPattern);
		return preg_replace('+(?<!&|&\w{1}|&\w{2}|&\w{3}|&\w{4}|&\w{5}|&\w{6})'.$keywordPattern.'(?![^<]*>)+i', '<span class="highlight">\\1</span>', $text);
	}
}
?>