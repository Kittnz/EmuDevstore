<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a help item.
 *
 * @author	Michael Schaefer
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.help
 * @subpackage	data.help
 * @category 	Community Framework
 */
class HelpItem extends DatabaseObject {
	/**
	 * Creates a new HelpItem object.
	 * 
	 * @param 	integer		$helpItemID
	 * @param 	array		$row
	 */
	public function __construct($helpItemID, $row = null) {
		if ($helpItemID !== null) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_help_item
				WHERE	helpItemID = ".$helpItemID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
	
	/**
	 * Returns an excerpt of the help item.
	 * 
	 * @return	string
	 */
	public function getExcerpt() {
		// get text
		$description = WCF::getLanguage()->getDynamicVariable('wcf.help.item.'.$this->helpItem.'.description');
		
		// remove headlines
		$description = preg_replace('~<h4>.*?</h4>~', '', $description);
		// remove help images
		$description = preg_replace('~<p class="helpImage.*?</p>~s', '', $description);
		
		// strip html tags
		$description = strip_tags($description);
		
		// truncate text
		if (StringUtil::length($description) > 250) {
			$description = preg_replace('/\s+?(\S+)?$/', '', StringUtil::substring($description, 0, 251));
			$description = StringUtil::substring($description, 0, 250).'...';
		}
		
		return $description;
	}
	
	/**
	 * Sets the index of this help item.
	 * 
	 * @param	integer		$index
	 */
	public function setIndex($index) {
		$this->data['index'] = $index;
	}
}
?>