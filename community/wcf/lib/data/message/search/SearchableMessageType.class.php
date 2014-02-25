<?php
/**
 * All searchable message types should implement this interface. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.search
 * @subpackage	data.message.search
 * @category 	Community Framework
 */
interface SearchableMessageType {
	/**
	 * Caches the data for the given message ids.
	 * 
	 * @param	string		$messageIDs
	 * @param	array		$additionalData
	 */
	public function cacheMessageData($messageIDs, $additionalData = null);
	
	/**
	 * Returns the data for the given message ids.
	 * 
	 * @param	integer		$messageID
	 * @param	array		$additionalData
	 * @return	array
	 */
	public function getMessageData($messageID, $additionalData = null);
	
	/**
	 * Shows the form part of this message type.
	 * 
	 * @param	Form		$form	instance of the form class where the search has taken place
	 */
	public function show($form = null);
	
	/**
	 * Returns the search conditions of this message type.
	 * 
	 * @param	Form		$form	instance of the form class where the search has taken place
	 * @return	string
	 */
	public function getConditions($form = null);
	
	/**
	 * Provides the ability to add additional joins to sql search query. 
	 * 
	 * @return	string
	 */
	public function getJoins();
	
	/**
	 * Returns the database table name of this message.
	 * 
	 * @return	string
	 */
	public function getTableName();
	
	/**
	 * Returns the database field name of the message id.
	 * 
	 * @return	string
	 */
	public function getIDFieldName();
	
	/**
	 * Returns additional search information.
	 * 
	 * @return	mixed
	 */
	public function getAdditionalData();
	
	/**
	 * Returns true, if the current user can use this searchable message type.
	 * 
	 * @return	boolean
	 */
	public function isAccessible();
	
	/**
	 * Returns the name of the form template for this message type.
	 * 
	 * @return	string
	 */
	public function getFormTemplateName();
	
	/**
	 * Returns the name of the result page template for this message type.
	 * 
	 * @return	string
	 */
	public function getResultTemplateName();
	
	/**
	 * Returns database field names of subject fields.
	 * 
	 * @return	array
	 */
	public function getSubjectFieldNames();
	
	/**
	 * Returns database field names of message fields.
	 * 
	 * @return	array
	 */
	public function getMessageFieldNames();
	
	/**
	 * Returns the database field name of the user id.
	 * 
	 * @return	string
	 */
	public function getUserIDFieldName();
	
	/**
	 * Returns the database field name of the username.
	 * 
	 * @return	string
	 */
	public function getUsernameFieldName();
	
	/**
	 * Returns the database field name of the time.
	 * 
	 * @return	string
	 */
	public function getTimeFieldName();
}
?>