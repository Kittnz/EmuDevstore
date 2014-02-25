<?php
// wcf imports
require_once(WCF_DIR.'lib/data/tag/Taggable.class.php');

/**
 * Convenient abstract class that already implements certain functions of Taggable.
 * 
 * @author 	Arian Glander
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.tagging
 * @subpackage	data.tag
 * @category 	Community Framework
 */
abstract class AbstractTaggableObject implements Taggable {
	/**
	 * taggable id
	 *
	 * @var integer
	 */
	protected $taggableID = 0;
	
	/**
	 * tag name
	 *
	 * @var	string
	 */
	protected $name = '';
	
	/**
	 * Will be called when sub class is instantiated
	 * Initializes taggableID and name
	 *
	 * @param	integer 	$taggableID	id of taggable type
	 * @param 	string		$name		name of taggable type
	 */
	public function __construct($taggableID, $name) {
		$this->taggableID = $taggableID;
		$this->name = $name;
	}
	
	/**
	 * @see Taggable::getTaggableID()
	 */
	public function getTaggableID() {
		return $this->taggableID;
	}
	
	/**
	 * @see Taggable::getName()
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * @see Taggable::getTableName()
	 */
	public function getTableName() {
		return '';
	}
}
?>