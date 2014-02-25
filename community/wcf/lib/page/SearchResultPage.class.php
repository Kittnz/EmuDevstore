<?php
// wcf imports
require_once(WCF_DIR.'lib/page/MultipleLinkPage.class.php');
require_once(WCF_DIR.'lib/data/message/search/SearchEngine.class.php');

/**
 * Shows the result of a search request.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.search
 * @subpackage	page
 * @category 	Community Framework
 */
class SearchResultPage extends MultipleLinkPage {
	public $itemsPerPage = SEARCH_RESULTS_PER_PAGE;
	public $templateName = 'searchResult';
	public $searchID = 0;
	public $query = '';
	public $result = array();
	public $search = null;
	public $additionalData = array();
	public $searchTypes = array();
	public $highlight = '';
	public $messages = array();
	public $sortField = '';
	public $sortOrder = '';
	public $alterable = 0;
	
	/**
	 * Creates a new SearchResultPage object.
	 * 
	 * @param	integer		$searchID
	 */
	public function __construct($searchID) {
		$this->searchID = $searchID;
		parent::__construct();
	}
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['highlight'])) $this->highlight = $_REQUEST['highlight'];
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// cache message data
		$this->cacheMessageData();
		
		// get messages
		$this->messages = $this->readMessages();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'query' => $this->query,
			'messages' => $this->messages,
			'searchID' => $this->searchID,
			'highlight' => $this->highlight,
			'types' => SearchEngine::$searchTypeObjects,
			'sortField' => $this->sortField,
			'sortOrder' => $this->sortOrder,
			'alterable' => $this->alterable
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		// get the data of the selected search
		$this->readSearch();
		
		parent::show();
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		return count($this->result);
	}
	
	/**
	 * Gets the data of the selected search from database.
	 */
	protected function readSearch() {
		$sql = "SELECT 	*
			FROM	wcf".WCF_N."_search
			WHERE	searchID = ".$this->searchID."
				AND searchType = 'messages'
				AND userID = ".WCF::getUser()->userID;
		$search = WCF::getDB()->getFirstRow($sql);
		if (!isset($search['searchID']) || ($search['userID'] && $search['userID'] != WCF::getUser()->userID)) {
			throw new IllegalLinkException();
		}
		
		$this->search = unserialize($search['searchData']);
		$this->query = $this->search['query'];
		$this->result = $this->search['result'];
		$this->additionalData = $this->search['additionalData'];
		$this->sortOrder = $this->search['sortOrder'];
		$this->sortField = $this->search['sortField'];
		if (isset($this->search['alterable'])) $this->alterable = $this->search['alterable'];
		
		// check package id of this search
		if (!empty($this->search['packageID']) && $this->search['packageID'] != PACKAGE_ID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * Caches the message data.
	 */
	protected function cacheMessageData() {
		$types = array();
		
		for ($i = $this->startIndex - 1; $i < $this->endIndex; $i++) {
			$type = $this->result[$i]['messageType'];
			$messageID = $this->result[$i]['messageID'];
			
			if (isset($types[$type])) $types[$type] .= ','.$messageID;
			else $types[$type] = $messageID;
		}
		
		foreach ($types as $type => $messageIDs) {
			$object = SearchEngine::getSearchTypeObject($type);
			$object->cacheMessageData($messageIDs, (isset($this->additionalData[$type]) ? $this->additionalData[$type] : null));
		}
	}
	
	/**
	 * Gets the data of the messages.
	 */
	protected function readMessages() {
		$messages = array();
		for ($i = $this->startIndex - 1; $i < $this->endIndex; $i++) {
			$type = $this->result[$i]['messageType'];
			$messageID = $this->result[$i]['messageID'];
			
			$object = SearchEngine::getSearchTypeObject($type);
			if (($message = $object->getMessageData($messageID, (isset($this->additionalData[$type]) ? $this->additionalData[$type] : null))) !== null) {
				$messages[] = $message;
			}
		}
		
		return $messages;
	}
}
?>