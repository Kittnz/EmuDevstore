<?php
// wcf imports
require_once(WCF_DIR.'lib/page/MultipleLinkPage.class.php');
require_once(WCF_DIR.'lib/data/message/util/SearchResultTextParser.class.php');
require_once(WCF_DIR.'lib/data/message/util/KeywordHighlighter.class.php');

/**
 * Shows the help search result page.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.help
 * @subpackage	page
 * @category 	Community Framework
 */
class HelpSearchResultPage extends MultipleLinkPage {
	// system
	public $templateName = 'helpSearchResult';
	
	/**
	 * highlight string
	 * 
	 * @var	string
	 */
	public $highlight = '';
	
	/**
	 * search id
	 * 
	 * @var	integer
	 */
	public $searchID = 0;
	
	/**
	 * search query
	 * 
	 * @var	string
	 */
	public $query = null;
	
	/**
	 * search results
	 * 
	 * @var	array
	 */
	public $result = null;
	
	/**
	 * list of help items
	 * 
	 * @var	array
	 */
	public $helpItems = array();

	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['searchID'])) $this->searchID = intval($_REQUEST['searchID']);
		// get search
		$sql = "SELECT 	*
			FROM	wcf".WCF_N."_search
			WHERE	searchID = ".$this->searchID."
				AND userID = ".WCF::getUser()->userID;
		$search = WCF::getDB()->getFirstRow($sql);
		if (empty($search['searchID']) || ($search['userID'] && $search['userID'] != WCF::getUser()->userID)) {
			throw new IllegalLinkException();
		}
		
		// get search data
		$search = unserialize($search['searchData']);
		$this->query = $search['query'];
		$this->result = $search['result'];
		
		// get highlight string
		if (isset($_REQUEST['highlight'])) $this->highlight = $_REQUEST['highlight'];
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		return count($this->result);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$this->readItems();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'query' => $this->query,
			'helpItems' => $this->helpItems,
			'searchID' => $this->searchID,
			'highlight' => $this->highlight
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		require_once(WCF_DIR.'lib/page/util/menu/PageMenu.class.php');
		PageMenu::setActiveMenuItem('wcf.header.menu.help');
		
		parent::show();
	}
	
	/**
	 * Gets the items for the current page.
	 */
	protected function readItems() {
		for ($i = $this->startIndex - 1; $i < $this->endIndex; $i++) {
			// get item name
			$itemName = $this->result[$i];
			
			// get description
			$description = WCF::getLanguage()->getDynamicVariable('wcf.help.item.'.$itemName.'.description');
			// remove headlines
			$description = preg_replace('~<h4>.*?</h4>~', '', $description);
			// remove help images
			$description = preg_replace('~<p class="helpImage.*?</p>~s', '', $description);
			
			// add item
			$this->helpItems[] = array(
				'item' => $itemName,
				'title' => KeywordHighlighter::doHighlight(WCF::getLanguage()->get('wcf.help.item.'.$itemName)),
				'description' => SearchResultTextParser::parse($description)
			);
		}
	}
}
?>