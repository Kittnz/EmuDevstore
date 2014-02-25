<?php
/**
 * Manages the quotes.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.message.multiQuote
 * @subpackage	data.message.multiQuote
 * @category 	Community Framework (commercial)
 */
class MultiQuoteManager {
	/**
	 * quote storage
	 *
	 * @var	array
	 */
	protected static $quoteStorage = null;
	
	/**
	 * quote counts
	 *
	 * @var	array
	 */
	protected static $quoteCounts = null;
	
	/**
	 * Stores a given quote.
	 *
	 * @param	integer		$objectID
	 * @param	string		$objectType
	 * @param	string		$text
	 * @param	string		$author
	 * @param	string		$url
	 * @param	integer		$parentID
	 * @param	string		$quoteID
	 * @return	string		quote id
	 */
	public static function storeQuote($objectID, $objectType, $text, $author = '', $url = '', $parentID = 0, $quoteID = '') {
		self::loadStorage();
		
		if ($quoteID == '') $quoteID = StringUtil::getRandomID();
		self::$quoteStorage[$quoteID] = array(
			'quoteID' => $quoteID,
			'objectID' => $objectID,
			'objectType' => $objectType,
			'author' => $author,
			'url' => $url,
			'text' => StringUtil::unifyNewlines($text),
			'parentID' => $parentID
		);
		
		if (!isset(self::$quoteCounts[$objectType.'-'.$objectID])) self::$quoteCounts[$objectType.'-'.$objectID] = 0;
		self::$quoteCounts[$objectType.'-'.$objectID]++;
		
		return $quoteID;
	}
	
	/**
	 * Deletes a quote from storage.
	 *
	 * @param	string		$quoteID
	 * @return	boolean
	 */
	public static function removeQuote($quoteID) {
		self::loadStorage();
		
		if (isset(self::$quoteStorage[$quoteID])) {
			if (isset(self::$quoteCounts[self::$quoteStorage[$quoteID]['objectType'].'-'.self::$quoteStorage[$quoteID]['objectID']])) {
				self::$quoteCounts[self::$quoteStorage[$quoteID]['objectType'].'-'.self::$quoteStorage[$quoteID]['objectID']]--;
			}
			
			unset(self::$quoteStorage[$quoteID]);
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns quote count.
	 *
	 * @param	integer		$objectID
	 * @param	string		$objectType
	 * @return	integer
	 */
	public static function getQuoteCount($objectID, $objectType) {
		self::loadStorage();
		
		if (isset(self::$quoteCounts[$objectType.'-'.$objectID])) return self::$quoteCounts[$objectType.'-'.$objectID];
		return 0;
	}
	
	/**
	 * Deletes quotes from storage.
	 *
	 * @param	integer		$objectID
	 * @param	string		$objectType
	 */
	public static function removeQuotes($objectID, $objectType) {
		self::loadStorage();
		
		foreach (self::$quoteStorage as $quoteID => $quote) {
			if ($quote['objectID'] == $objectID && $quote['objectType'] == $objectType) {
				unset(self::$quoteStorage[$quoteID]);
			}
		}
		
		if (isset(self::$quoteCounts[$objectType.'-'.$objectID])) {
			unset(self::$quoteCounts[$objectType.'-'.$objectID]);
		}
	}
	
	/**
	 * Loads the quote storage.
	 */
	protected static function loadStorage() {
		if (self::$quoteStorage === null || self::$quoteCounts === null) {
			self::$quoteStorage = WCF::getSession()->getVar('quoteStorage');
			if (self::$quoteStorage === null) self::$quoteStorage = array();
			self::$quoteCounts = WCF::getSession()->getVar('quoteCounts');
			if (self::$quoteCounts === null) self::$quoteCounts = array();
		}
	}
	
	/**
	 * Saves the quote storage.
	 */
	public static function saveStorage() {
		WCF::getSession()->register('quoteStorage', self::$quoteStorage);
		WCF::getSession()->register('quoteCounts', self::$quoteCounts);
	}
	
	/**
	 * Returns the quote storage.
	 *
	 * @return	array
	 */
	public static function getStorage() {
		self::loadStorage();
		
		return self::$quoteStorage;
	}
	
	/**
	 * Gets quotes by given parent id.
	 *
	 * @param	string		$objectType
	 * @param	integer		$parentID
	 * @return	array
	 */
	public static function getQuotesByParentID($objectType, $parentID) {
		self::loadStorage();
		$quotes = array();
		
		foreach (self::$quoteStorage as $quoteID => $quote) {
			if ($quote['parentID'] == $parentID && $quote['objectType'] == $objectType) {
				$quotes[$quoteID] = $quote;
			}
		}
		
		return $quotes;
	}
}
?>