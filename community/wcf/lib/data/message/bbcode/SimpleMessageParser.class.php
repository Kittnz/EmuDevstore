<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/smiley/Smiley.class.php');

/**
 * Parses urls and smileys in simple messages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.bbcode
 * @subpackage	data.message.bbcode
 * @category 	Community Framework
 */
class SimpleMessageParser {
	private static $illegalChars = '[^\x0-\x2C\x2E\x2F\x3A-\x40\x5B-\x60\x7B-\x7F]+';
	protected static $pageURLs = null;
	
	/**
	 * list of smileys
	 * 
	 * @var	array
	 */
	protected $smileys = array();
	
	/**
	 * instance of SimpleMessageParser
	 * 
	 * @var	SimpleMessageParser
	 */
	protected static $instance = null;
	
	/**
	 * Creates a new SimpleMessageParser object.
	 */
	private function __construct() {
		// get smilies
		if (MODULE_SMILEY == 1) {
			$smilies = WCF::getCache()->get('smileys', 'smileys');
			foreach ($smilies as $categoryID => $categorySmileys) {
				foreach ($categorySmileys as $smiley) {
					$this->smileys[$smiley->smileyCode] = '<img src="'.$smiley->getURL().'" alt="'.StringUtil::encodeHTML($smiley->smileyCode).'" />';
				}
			}
		}
	}
	
	/**
	 * Returns an instance of SimpleMessageParser class.
	 * 
	 * @return	SimpleMessageParser
	 */
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new SimpleMessageParser();
		}
		
		return self::$instance;
	}
	
	/**
	 * Parses a message.
	 * 
	 * @param	string		$message
	 * @param	boolean		$parseURLs
	 * @param	boolean		$parseSmileys
	 * @return	string		parsed message
	 */
	public function parse($message, $parseURLs = true, $parseSmileys = true) {
		// encode html
		$message = StringUtil::encodeHTML($message);
		
		// converts newlines to <br />'s
		$message = nl2br($message);
		
		// parse urls
		if ($parseURLs) {
			$message = $this->parseURLs($message);
		}
		
		// parse smilies
		if ($parseSmileys) {
			$message = $this->parseSmileys($message);
		}
		
		// replace bad html tags (script etc.)
		$badSearch = array('/javascript:/i', '/about:/i', '/vbscript:/i');
		$badReplace = array('javascript<b></b>:', 'about<b></b>:', 'vbscript<b></b>:');
		$message = preg_replace($badSearch, $badReplace, $message);
		
		return $message;
	}
	
	/**
	 * Parses urls.
	 * 
	 * @param	string		$text
	 * @return	string		text
	 */
	public static function parseURLs($text) {
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
			(?!"|\'|\[|\-)
			~ix';
		
		// parse urls
		$text = preg_replace_callback($urlPattern, array('self', 'parseURLsCallback'), $text);
		
		// parse emails
		if (StringUtil::indexOf($text, '@') !== false) {
			$text = preg_replace($emailPattern, '<a href="mailto:\\0">\\0</a>', $text);
		}
	
		return $text;
	}
	
	/**
	 * Parses smiley codes.
	 * 
	 * @param	string		$text
	 * @return	string		text
	 */
	protected function parseSmileys($text) {
		foreach ($this->smileys as $code => $html) {
			$text = preg_replace('~(?<!&\w{2}|&\w{3}|&\w{4}|&\w{5}|&\w{6}|&#\d{2}|&#\d{3}|&#\d{4}|&#\d{5})'.preg_quote(StringUtil::encodeHTML($code), '~').'(?![^<]*>)~', $html, $text);
		}
		
		return $text;
	}
	
	private static function parseURLsCallback($matches) {
		$url = $title = $matches[0];
		$decodedTitle = StringUtil::decodeHTML($title);
		if (StringUtil::length($decodedTitle) > 60) {
			$title = StringUtil::encodeHTML(StringUtil::substring($decodedTitle, 0, 40)) . '&hellip;' . StringUtil::encodeHTML(StringUtil::substring($decodedTitle, -15));
		}
		// add protocol if necessary
		if (!preg_match("/[a-z]:\/\//si", $url)) $url = 'http://'.$url;
		
		$external = true;
		if (($newURL = self::isInternalURL($url)) !== false) {
			$url = $newURL;
			$external = false;
		}
		
		return '<a href="'.$url.'"'.($external ? ' class="externalURL"' : '').'>'.$title.'</a>';
	}
	
	/**
	 * Checks whether a URL is an internal URL.
	 * 
	 * @param	string		$url
	 * @return	mixed	
	 */
	protected static function isInternalURL($url) {
		if (self::$pageURLs === null) {
			self::getPageURLs();
		}
		
		foreach (self::$pageURLs as $pageURL) {
			if (stripos($url, $pageURL) === 0) {
				return str_ireplace($pageURL.'/', '', $url);
			}
		}
		
		return false;
	}
	
	/**
	 * Gets the page URLs.
	 * 
	 * @return	array
	 */
	protected static function getPageURLs() {
		$urlString = '';
		if (defined('PAGE_URL')) $urlString .= PAGE_URL;
		if (defined('PAGE_URLS')) $urlString .= "\n".PAGE_URLS;
		
		$urlString = StringUtil::unifyNewlines($urlString);
		self::$pageURLs = ArrayUtil::trim(explode("\n", $urlString));
	}
}
?>