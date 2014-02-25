<?php
/**
 * Any tagged object has to implement this interface.
 * 
 * @author 	Arian Glander
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.tagging
 * @subpackage	data.tag
 * @category 	Community Framework
 */
interface Tagged {
	/**
	 * Gets the id of the tagged object.
	 *
	 * @return	integer		the id to get
	 */
	public function getObjectID();
	
	/**
	 * Gets the title of the tagged object.
	 *
	 * @return 	string 		the title to get
	 */
	public function getTitle();
	
	/**
	 * Gets the description of the tagged object.
	 *
	 * @return	string		the description to get
	 */
	public function getDescription();
	
	/**
	 * Gets the name of the small symbol of the tagged object.
	 *
	 * @return	string		the name of the small symbol to get
	 */
	public function getSmallSymbol();
	
	/**
	 * Gets the name of the medium symbol of the tagged object.
	 * 
	 * @return	string 		the name of the large symbol to get
	 */
	public function getMediumSymbol();
	
	/**
	 * Gets the name of the large symbol of the tagged object.
	 * 
	 * @return	string 		the name of the large symbol to get
	 */
	public function getLargeSymbol();

	/**
	 * Gets the user who is bound to the tagged object.
	 *
	 * @return 	User 		the user to get
	 */
	public function getUser();
	
	/**
	 * Gets the creation date of this object.
	 * 
	 * @return	integer		unix timestamp
	 */
	public function getDate();
	
	/**
	 * Gets the taggable type of this tagged object.
	 *
	 * @return	Taggable 	the taggable to get
	 */
	public function getTaggable();
	
	/**
	 * Returns the URL of this object.
	 *
	 * @return	string
	 */
	public function getURL();
}
?>