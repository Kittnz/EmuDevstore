<?php
/**
 * Contains Array-related functions.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.tagging
 * @subpackage	util
 * @category 	Community Framework
 */
class TaggingUtil {
	/**
	 * Takes a string of comma separated tags and splits it into an array.
	 *
	 * @param	string		$tags
	 * @param	string		$separators
	 * @return	array
	 */
	public static function splitString($tags, $separators = ',;') {
		return array_unique(ArrayUtil::trim(preg_split('/['.preg_quote($separators).']/', $tags)));
	}
	
	/**
	 * Takes a list of tags and builds a comma separated string from it.
	 *
	 * @param	array<mixed>	$tagArray
	 * @param	string		$separator
	 * @return	string
	 */
	public static function buildString($tagArray, $separator = ', ') {
		$string = '';
		foreach ($tagArray as $tag) {
			if (!empty($string)) $string .= $separator;
			$string .= (is_object($tag) ? $tag->getName() : $tag);
		}
		
		return $string;
	}
}
?>