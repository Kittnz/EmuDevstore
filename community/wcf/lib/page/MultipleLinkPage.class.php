<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * Provides default implementations for a multiple link page.
 * Handles the page number parameter automatically.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category 	Community Framework
 */
abstract class MultipleLinkPage extends AbstractPage {
	/**
	 * The current page number.
	 * 
	 * @var integer
	 */
	public $pageNo = 0;
	
	/**
	 * The number of all pages.
	 * 
	 * @var integer
	 */
	public $pages = 0;
	
	/**
	 * The number of items shown per page.
	 * 
	 * @var integer
	 */
	public $itemsPerPage = 20;
	
	/**
	 * The number of all items.
	 * 
	 * @var integer
	 */
	public $items = 0;
	
	/**
	 * Indicates the range of the listed items.
	 * 
	 * @var integer
	 */
	public $startIndex, $endIndex;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// read page number parameter
		if (isset($_REQUEST['pageNo'])) $this->pageNo = intval($_REQUEST['pageNo']);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// calculates page number
		$this->calculateNumberOfPages();
	}
	
	/**
	 * Calculates the number of pages and
	 * handles the given page number parameter.
	 */
	public function calculateNumberOfPages() {
		// call calculateNumberOfPages event
		EventHandler::fireAction($this, 'calculateNumberOfPages');
		
		// calculate number of pages
		$this->items = $this->countItems();
		$this->pages = intval(ceil($this->items / $this->itemsPerPage));
		
		// correct active page number
		if ($this->pageNo > $this->pages) $this->pageNo = $this->pages;
		if ($this->pageNo < 1) $this->pageNo = 1;
		
		// calculate start and end index
		$this->startIndex = ($this->pageNo - 1) * $this->itemsPerPage;
		$this->endIndex = $this->startIndex + $this->itemsPerPage;
		$this->startIndex++;
		if ($this->endIndex > $this->items) $this->endIndex = $this->items;
	}
	
	/**
	 * Counts the displayed items.
	 * 
	 * @return	integer
	 */
	public function countItems() {
		// call countItems event
		EventHandler::fireAction($this, 'countItems');
		
		return 0;
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		// assign page parameters
		WCF::getTPL()->assign(array(
			'pageNo' => $this->pageNo,
			'pages' => $this->pages,
			'items' => $this->items,
			'itemsPerPage' => $this->itemsPerPage,
			'startIndex' => $this->startIndex,
			'endIndex' => $this->endIndex
		));
	}
}
?>