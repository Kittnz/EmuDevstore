<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/bbcode/BBCodeParser.class.php');
require_once(WCF_DIR.'lib/data/message/bbcode/BBCode.class.php');

/**
 * Parses the [quote] bbcode tag.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.bbcode
 * @subpackage	data.message.bbcode
 * @category 	Community Framework
 */
class QuoteBBCode implements BBCode {
	/**
	 * @see BBCode::getParsedTag()
	 */
	public function getParsedTag($openingTag, $content, $closingTag, BBCodeParser $parser) {
		if ($parser->getOutputType() == 'text/html') {
			// show template
			WCF::getTPL()->assign(array(
				'content' => $content,
				'quoteLink' => (!empty($openingTag['attributes'][1]) ? $openingTag['attributes'][1] : ''),
				'quoteAuthor' => (!empty($openingTag['attributes'][0]) ? $openingTag['attributes'][0] : '')
			));
			return WCF::getTPL()->fetch('quoteBBCodeTag');
		}
		else if ($parser->getOutputType() == 'text/plain') {
			$cite = '';
			if (!empty($openingTag['attributes'][0])) $cite = WCF::getLanguage()->get('wcf.bbcode.quote.cite.text', array('$name' => $openingTag['attributes'][0]));
			
			return WCF::getLanguage()->get('wcf.bbcode.quote.text', array('$content' => $content, '$cite' => $cite));
		}
	}
}
?>