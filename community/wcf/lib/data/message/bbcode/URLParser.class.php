<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * Parses URLs in message text.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.bbcode
 * @subpackage	data.message.bbcode
 * @category 	Community Framework
 */
class URLParser {
	private static $illegalChars = '[^\x0-\x2C\x2E\x2F\x3A-\x40\x5B-\x60\x7B-\x7F]+';
	private static $sourceCodeRegEx = null;
	private static $cachedCodes = array();
	
	public static $text = '';
	
	/**
	 * Adds the url and email bbcode tags in a text automatically.
	 */
	public static function parse($text) {
		self::$text = $text;
		
		// cache codes
		self::$text = self::cacheCodes(self::$text);
		
		// call event
		EventHandler::fireAction('URLParser', 'shouldParse');
		
		// define pattern
		$urlPattern = '~(?<!\B|"|\'|=|/|\]|,|\?)
			(?:						# hostname
				(?:ftp|https?)://'.self::$illegalChars.'(?:\.'.self::$illegalChars.')*
				|
				www\.(?:'.self::$illegalChars.'\.)+
				(?:[a-z]{2,4}(?=\b))
			)

			(?::\d+)?					# port

			(?:
				/
				[^!.,?;"\'<>()\[\]{}\s]*
				(?:
					[!.,?;(){}]+ [^!.,?;"\'<>()\[\]{}\s]+
				)*
			)?
			~ix';
			
		$emailPattern = '~(?<!\B|"|\'|=|/|\]|,|:)
			(?:)
			\w+(?:[\.\-]\w+)*
			@
			(?:'.self::$illegalChars.'\.)+		# hostname
			(?:[a-z]{2,4}(?=\b))
			(?!"|\'|\[|\-|\.[a-z])
			~ix';
		
		// add url tags
		self::$text = preg_replace($urlPattern, '[url]\\0[/url]', self::$text);
		if (StringUtil::indexOf(self::$text, '@') !== false) self::$text = preg_replace($emailPattern, '[email]\\0[/email]', self::$text);
	
		// call event
		EventHandler::fireAction('URLParser', 'didParse');
		
		if (count(self::$cachedCodes) > 0) {
			// insert cached codes
			self::$text = self::insertCachedCodes(self::$text);
		}
		
		return self::$text;
	}
	
	/**
	 * Caches code bbcodes to avoid parsing of urls inside them.
	 * 
	 * @param	string		$text
	 * @return	string
	 */
	private static function cacheCodes($text) {
		if (self::$sourceCodeRegEx === null) {
			self::$sourceCodeRegEx = implode('|', WCF::getCache()->get('bbcodes', 'sourceCodes'));
		}
		
		if (!empty(self::$sourceCodeRegEx)) {
			self::$cachedCodes = array();
			$text = preg_replace_callback("~(\[(".self::$sourceCodeRegEx.")
				(?:=
					(?:\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^,\]]*)
					(?:,(?:\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^,\]]*))*
				)?\])
				(.*?)
				(?:\[/\\2\])~six", array('URLParser', 'cacheCodesCallback'), $text);
		}
		return $text;
	}
	
	private static function cacheCodesCallback($match) {
		return self::cacheCode($match[0]);
	}
	
	/**
	 * Caches a code bbcode.
	 * 
	 * @param	string		$content
	 * @return	string		$hash
	 */
	private static function cacheCode($content) {
		// strip slashes
		$content = str_replace("\\\"", "\"", $content);

		// create hash
		$hash = '@@'.StringUtil::getHash(uniqid(microtime()).$content).'@@';
		
		// save tag
		self::$cachedCodes[$hash] = $content;
	
		return $hash;
	}
	
	/**
	 * Reinserts cached code bbcodes.
	 * 
	 * @param	string		$text
	 * @return	string
	 */
	private static function insertCachedCodes($text) {
		foreach (self::$cachedCodes as $hash => $content) {
			// build code and insert
			$text = str_replace($hash, $content, $text);
		}
		
		return $text;
	}
}
?>