<?php
// wcf imports
require_once(WCF_DIR.'lib/data/message/bbcode/BBCodeParser.class.php');
require_once(WCF_DIR.'lib/data/message/bbcode/BBCode.class.php');

/**
 * Parses the [list] bbcode tag.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.bbcode
 * @subpackage	data.message.bbcode
 * @category 	Community Framework
 */
class ListBBCode implements BBCode {
	/**
	 * @see BBCode::getParsedTag()
	 */
	public function getParsedTag($openingTag, $content, $closingTag, BBCodeParser $parser) {
		if (StringUtil::indexOf($content, '[*]') !== false) {
			// get list elements
			$listElements = preg_split('/\[\*\]/', StringUtil::trim($content), -1, PREG_SPLIT_NO_EMPTY);
			
			// remove empty elements
			foreach ($listElements as $key => $val) {
				$listElements[$key] = StringUtil::trim($val);
				if (empty($listElements[$key]) || $listElements[$key] == '<br />') {
					unset($listElements[$key]);
				}
			}
			
			if (count($listElements) > 0) {
				// get list style type
				$listType = 'disc';
				if (isset($openingTag['attributes'][0])) $listType = $openingTag['attributes'][0];
				$listType = strtolower($listType);
				
				// replace old types
				if ($listType == '1') $listType = 'decimal';
				if ($listType == 'a') $listType = 'lower-latin';
				
				if ($parser->getOutputType() == 'text/html') {
					// build list html
					$listHTML = 'ol';
					if ($listType == 'none' || $listType == 'circle' || $listType == 'square' || $listType == 'disc') {
						$listHTML = 'ul';
					}
					
					return '<'.$listHTML.' style="list-style-type: '.$listType.'"><li>'.implode('</li><li>', $listElements).'</li></'.$listHTML.'>';
				}
				else if ($parser->getOutputType() == 'text/plain') {
					$result = '';
					
					$i = 1;
					foreach ($listElements as $listElement) {
						switch ($listType) {
							case 'decimal':
								$result .= $i.'. ';
								break;
							default:
								$result .= '- ';
						}
						
						$result .= $listElement."\n";
						$i++;
					}
					
					return $result;
				}
			}
		}
		
		// no valid list
		// return bbcode as text
		return $openingTag['source'].$content.$closingTag['source'];
	}
}
?>