<?php
/**
 * Any object type that is taggable, can implement this interface.
 * 
 * @author 	Arian Glander
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.tagging
 * @subpackage	data.tag
 * @category 	Community Framework
 */
interface Taggable {
	/**
	 * Gets the id of this taggable type.
	 *
	 * @return 	integer 	the id to get
	 */
	public function getTaggableID();
	
	/**
	 * Gets the name of this taggable type.
	 *
	 * @return 	string 		the name to get
	 */
	public function getName();
	
	/**
	 * Gets tagged objects of this taggable type.
	 *
	 * @param 	array 		$objectIDs	array with ids of objects to get
	 * @param 	array 		$taggedObjects 	array with other tagged objects, this has to be added with own objects
	 * @return 	array 				array with tagged objects
	 */
	public function getObjectsByIDs($objectIDs, $taggedObjects);
	
	/**
	 * Returns the number of tagged objects.
	 *
	 * @param 	integer		$tagID
	 * @return	integer
	 */
	public function countObjectsByTagID($tagID);
	
	/**
	 * Gets a list of tagged objects by id.
	 *
	 * @param	integer		$tagID
	 * @param	integer		$limit
	 * @param 	integer		$offset
	 * @return	array<Tagged>
	 */
	public function getObjectsByTagID($tagID, $limit = 0, $offset = 0);
	
	/**
	 * Returns the database id field name of this specific object.
	 * 
	 * @return	string		id field name to get
	 */
	public function getIDFieldName();
	
	/**
	 * Gets the database table name of this specific object.
	 *
	 * @return 	string		table name to get
	 */
	public function getTableName();
	
	/**
	 * Gets the Templatename for a display of tagged Objects.
	 *
	 * @return 	string 		name of the template
	 */
	public function getResultTemplateName();
	
	/**
	 * Gets the name of the small symbol of the taggable.
	 *
	 * @return	string		the name of the small symbol to get
	 */
	public function getSmallSymbol();
	
	/**
	 * Gets the name of the medium symbol of the taggable.
	 * 
	 * @return	string 		the name of the large symbol to get
	 */
	public function getMediumSymbol();
	
	/**
	 * Gets the name of the large symbol of the taggable.
	 * 
	 * @return	string 		the name of the large symbol to get
	 */
	public function getLargeSymbol();
}
?>