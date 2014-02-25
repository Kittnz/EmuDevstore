<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/bbcode/BBCodeParser.class.php');
require_once(WCF_DIR.'lib/data/message/util/KeywordHighlighter.class.php');
require_once(WCF_DIR.'lib/data/message/smiley/Smiley.class.php');

/**
 * Parses bbcode tags, smilies etc. in messages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.bbcode
 * @subpackage	data.message.bbcode
 * @category 	Community Framework
 */
class MessageParser extends BBCodeParser {
	protected $smilies = array();
	protected static $instance = null;
	protected $cachedCodes = array();
	protected $sourceCodeRegEx = '';
	
	/**
	 * Creates a new MessageParser object.
	 */
	public function __construct() {
		parent::__construct();
		
		// add cache resources
		WCF::getCache()->addResource('bbcodes', WCF_DIR.'cache/cache.bbcodes.php', WCF_DIR.'lib/system/cache/CacheBuilderBBCodes.class.php');
		WCF::getCache()->addResource('smileys', WCF_DIR.'cache/cache.smileys.php', WCF_DIR.'lib/system/cache/CacheBuilderSmileys.class.php');
		
		// get smilies
		if (MODULE_SMILEY == 1) {
			$smilies = WCF::getCache()->get('smileys', 'smileys');
			$this->sourceCodeRegEx = implode('|', WCF::getCache()->get('bbcodes', 'sourceCodes'));
			foreach ($smilies as $categoryID => $categorySmileys) {
				foreach ($categorySmileys as $smiley) {
					$this->smilies[$smiley->smileyCode] = '<img src="'.$smiley->getURL().'" alt="'.StringUtil::encodeHTML($smiley->smileyCode).'" />';
				}
			}
		}
	}
	
	/**
	 * Returns an instance of MessageParser class.
	 * 
	 * @return	MessageParser
	 */
	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new MessageParser();
		}
		
		return self::$instance;
	}
	
	/**
	 * Parses a message.
	 * 
	 * @param	string		$message
	 * @param	boolean		$enableSmilies
	 * @param	boolean		$enableHtml
	 * @param	boolean		$enableBBCodes
	 * @return	string		parsed message
	 */
	public function parse($message, $enableSmilies = true, $enableHtml = false, $enableBBCodes = true, $doKeywordHighlighting = true) {
		$this->cachedCodes = array();
		
		if ($this->getOutputType() != 'text/plain') {
			if ($enableBBCodes) {
				// cache codes
				$message = $this->cacheCodes($message);
			}
			
			if (!$enableHtml) {
				// encode html
				$message = StringUtil::encodeHTML($message);
				
				// converts newlines to <br />'s
				$message = nl2br($message);
			}
		}
		
		// parse bbcodes
		if ($enableBBCodes) {
			$message = parent::parse($message);
		}
		
		if ($this->getOutputType() != 'text/plain') {
			// parse smilies
			if ($enableSmilies) {
				$message = $this->parseSmilies($message, $enableHtml);
			}
			
			if ($enableBBCodes && count($this->cachedCodes) > 0) {
				// insert cached codes
				$message = $this->insertCachedCodes($message);
			}
			
			// highlight search query
			if ($doKeywordHighlighting) {
				$message = KeywordHighlighter::doHighlight($message);
			}
			
			// replace bad html tags (script etc.)
			$badSearch = array('/javascript:/i', '/about:/i', '/vbscript:/i');
			$badReplace = array('javascript<b></b>:', 'about<b></b>:', 'vbscript<b></b>:');
			$message = preg_replace($badSearch, $badReplace, $message);
			
			// wrap text
			//$message = $this->wrap($message);
		}
		
		return $message;
	}
	
	/**
	 * Parses smiley codes.
	 * 
	 * @param	string		$text
	 * @return	string		text
	 */
	protected function parseSmilies($text, $enableHtml = false) {
		foreach ($this->smilies as $code => $html) {
			$text = preg_replace('~(?<!&\w{2}|&\w{3}|&\w{4}|&\w{5}|&\w{6}|&#\d{2}|&#\d{3}|&#\d{4}|&#\d{5})'.preg_quote((!$enableHtml ? StringUtil::encodeHTML($code) : $code), '~').'(?![^<]*>)~', $html, $text);
		}
		
		return $text;
	}
	
	/**
	 * Caches code bbcodes to avoid parsing of smileys and other bbcodes inside them.
	 * 
	 * @param	string		$text
	 * @return	string
	 */
	protected function cacheCodes($text) {
		if (!empty($this->sourceCodeRegEx)) {
			$text = preg_replace_callback("~(\[(".$this->sourceCodeRegEx.")
				(?:=
					(?:\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^,\]]*)
					(?:,(?:\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^,\]]*))*
				)?\])
				(.*?)
				(?:\[/\\2\])~six", array($this, 'cacheCodesCallback'), $text);
		}
		return $text;
	}
	
	protected function cacheCodesCallback($match) {
		return $this->cacheCode($match[1], $match[3]);
	}
	
	/**
	 * Caches a code bbcode.
	 * 
	 * @param	string		$codeTag
	 * @param	string		$content
	 * @return	string		$hash
	 */
	protected function cacheCode($codeTag, $content) {
		// strip slashes
		$codeTag = str_replace("\\\"", "\"", $codeTag);
		$content = str_replace("\\\"", "\"", $content);

		// create hash
		$hash = '@@'.StringUtil::getHash(uniqid(microtime()).$content).'@@';
		
		// build tag
		$tag = $this->buildTag($codeTag);
		$tag['content'] = $content;
		
		// save tag
		$this->cachedCodes[$hash] = $tag;
		
		return $hash;
	}
	
	/**
	 * Reinserts cached code bbcodes.
	 * 
	 * @param	string		$text
	 * @return	string
	 */
	protected function insertCachedCodes($text) {
		foreach ($this->cachedCodes as $hash => $tag) {
			// get object
			$bbcode = $this->getBBCodeObject($tag['name']);
			
			// build code and insert
			$text = str_replace($hash, $bbcode->getParsedTag($tag, $tag['content'], $tag, $this), $text);
		}
		
		return $text;
	}
	
	/**
	 * @see BBCodeParser::isValidTagAttribute()
	 */
	protected function isValidTagAttribute($tagAttributes, $definedTagAttribute) {
		if (!parent::isValidTagAttribute($tagAttributes, $definedTagAttribute)) {
			return false;
		}
		
		// check for cached codes
		if (isset($tagAttributes[$definedTagAttribute['attributeNo']]) && preg_match('/@@[a-f0-9]{40}@@/', $tagAttributes[$definedTagAttribute['attributeNo']])) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Wraps long text.
	 * WARNING: Does not work correctly with utf-8 encoded strings!
	 * 
	 * @param	string		$text
	 * @return	string		text
	 */
	public static function wrap($text, $width = 80, $separator = "\n") {
		if (StringUtil::length($text) <= 80) {
			return $text;
		}
	
		$htmlPattern = '~</?[a-z]+[1-6]?
			(?:\s*[a-z]+\s*=\s*(?:
			"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"|\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^\s>]
			))*\s*/?>~ix';
		
		// get all html tags	
		preg_match_all($htmlPattern, $text, $matches);
		$htmlTagArray = $matches[0];
		
		// get text
		$textArray = preg_split($htmlPattern, $text);
		
		$remain = $width;
		$newText = '';
		for ($i = 0, $j = count($textArray); $i < $j; $i++) {
			if (!empty($textArray[$i])) {
				$length = StringUtil::length($textArray[$i]);
								
				if ($length <= $width) {
					if (!preg_match('~[\s&]~', $textArray[$i])) {
						$remain -= $length;
					}
					else {
						$remain = $width;
					}
				}
				else {
					if ($remain < 0) $remain = 0;
					$textArray[$i] = preg_replace('~(?:^[^\s&]{'.$remain.'}|[^\s&]{'.$width.'})~', '\\0'.$separator, $textArray[$i]);
					//preg_match('~([^\s]*)$~', $textArray[$i], $match);
					//$remain = $width - StringUtil::length($match[1]);
					$remain = $width;
				}
			}
			
			$newText .= $textArray[$i] . (isset($htmlTagArray[$i]) ? $htmlTagArray[$i] : '');
		}
		
		return $newText;
	}
}
?>