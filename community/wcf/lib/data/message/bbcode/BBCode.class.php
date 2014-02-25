<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/bbcode/BBCodeParser.class.php');

/**
 * Any special bbcode class should implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.bbcode
 * @subpackage	data.message.bbcode
 * @category 	Community Framework
 */
interface BBCode {
	/**
	 * Parses a bbcode tag.
	 * 
	 * @param	array			$openingTag
	 * @param	string			$content
	 * @param	array			$closingTag
	 * @param	BBCodeParser	$parser
	 * @return	string			parsed tag
	 */
	public function getParsedTag($openingTag, $content, $closingTag, BBCodeParser $parser);
}
?>