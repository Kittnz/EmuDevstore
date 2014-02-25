<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/bbcode/BBCodeParser.class.php');
require_once(WCF_DIR.'lib/data/message/bbcode/BBCode.class.php');

/**
 * Parses the [code] bbcode tag.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.bbcode
 * @subpackage	data.message.bbcode
 * @category 	Community Framework
 */
class CodeBBCode implements BBCode {
	/**
	 * @see BBCode::getParsedTag()
	 */
	public function getParsedTag($openingTag, $content, $closingTag, BBCodeParser $parser) {
		if ($parser->getOutputType() == 'text/html') {	
			// encode html
			$content = self::trim($content);
			$content = StringUtil::encodeHTML($content);
			
			// show template
			WCF::getTPL()->assign(array(
				'lineNumbers' => $this->makeLineNumbers($content, $this->getLineNumbersStart($openingTag)),
				'content' => $content,
				'codeBoxName' => WCF::getLanguage()->get('wcf.bbcode.code.title')
			));
			return WCF::getTPL()->fetch('codeBBCodeTag');
		}
		else if ($parser->getOutputType() == 'text/plain') {
			return WCF::getLanguage()->get('wcf.bbcode.code.text', array('$content' => $content));
		}
	}
	
	/**
	 * Returns the preferred start of the line numbers.
	 * 
	 * @param	array		$openingTag
	 * @return	integer
	 */
	protected static function getLineNumbersStart($openingTag) {
		$start = 1;
		if (!empty($openingTag['attributes'][0])) {
			$start = intval($openingTag['attributes'][0]);
			if ($start < 1) $start = 1;
		}
		
		return $start;
	}
	
	/**
	 * Returns a string with all line numbers
	 * 
	 * @return	string
	 */
	protected static function makeLineNumbers($code, $start, $split = "\n") {
		//$code = StringUtil::trim($code);
		$lines = explode($split, $code);	
		
		$lineNumbers = '';
		for ($i = 0, $j = count($lines); $i < $j; $i++) {
			$lineNumbers .= ($i + $start) . "\n";
		}
		return $lineNumbers;	
	}
	
	/**
	 * Removes empty lines from the beginning and end of a string.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	protected static function trim($string) {
		$string = preg_replace('/^(\s*\n)+/', '', $string);
		$string = preg_replace('/(\s*\n)+$/', '', $string);
		return $string;
	}
}
?>