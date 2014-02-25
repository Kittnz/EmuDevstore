<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/data/help/HelpItem.class.php');

/**
 * Shows the end-user help.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.help
 * @subpackage	page
 * @category 	Community Framework
 */
class HelpPage extends AbstractPage {
	// system
	public $templateName = 'help';
	
	/**
	 * parent help item
	 * 
	 * @var	string
	 */
	public $parentHelpItem = '';
	
	/**
	 * top help item
	 * 
	 * @var	string
	 */
	public $topHelpItem = '';
	
	/**
	 * list of help items
	 * 
	 * @var	array<HelpItem>
	 */
	public $items = array();
	
	/**
	 * the structure of all help items
	 * 
	 * @var	array
	 */
	public $helpStructure = array();
	
	/**
	 * table of contents
	 * 
	 * @var	array
	 */
	public $toc = array();
	
	/**
	 * list of help items
	 * 
	 * @var	array<HelpItem>
	 */
	public $itemList = array();
	
	/**
	 * list of all help item names
	 * 
	 * @var	array
	 */
	public $allItemNames = array();
	
	/**
	 * list of parent items
	 * 
	 * @var	array<HelpItem>
	 */
	public $parentItems = array();
	
	/**
	 * next help item
	 * 
	 * @var	HelpItem
	 */
	public $nextItem = null;
	
	/**
	 * previous help item
	 * 
	 * @var	HelpItem
	 */
	public $previousItem = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// load cache
		$cache = WCF::getCache()->get('help-'.PACKAGE_ID);
		$this->items = $cache['items'];
		$this->helpStructure = $cache['structure'];
		unset($cache);

		// check item options and permissions
		$this->checkOptions();
		$this->checkPermissions();
		
		// check active item
		if (isset($_REQUEST['item'])) {
			$this->parentHelpItem = $_REQUEST['item'];
			if (!isset($this->items[$this->parentHelpItem])) {
				throw new IllegalLinkException();
			}
		}
		else {
			// get parent by referer
			if (WCF::getSession()->lastRequestURI) {
				foreach ($this->items as $item) {
					if ($item->refererPattern) {
						if (preg_match('~'.$item->refererPattern.'~i', WCF::getSession()->lastRequestURI)) {
							$this->parentHelpItem = $item->helpItem;
						}
					}
				}
			}
		}
	}

	/**
	 * Checks the options of the help items.
	 * Removes items of disabled options.
	 * 
	 * @param	string		$parentItem
	 */
	protected function checkOptions($parentItem = '') {
		if (!isset($this->helpStructure[$parentItem])) return;
		
		foreach ($this->helpStructure[$parentItem] as $key => $itemName) {
			$item = $this->items[$itemName];
			$hasEnabledOption = true;
			// check the options of this item
			if ($item->options) {
				$hasEnabledOption = false;
				$options = explode(',', strtoupper($item->options));
				foreach ($options as $option) {
					if (defined($option) && constant($option)) {
						$hasEnabledOption = true;
						break;
					}
				}
			}
			
			if ($hasEnabledOption) {
				// check option of the children
				$this->checkOptions($itemName);
			}
			else {
				// remove this item
				unset($this->helpStructure[$parentItem][$key], $this->items[$itemName]);
			}
		}
	}
	
	/**
	 * Checks the permissions of the help items.
	 * Removes items without permission.
	 */
	protected function checkPermissions($parentItem = '') {
		if (!isset($this->helpStructure[$parentItem])) return;
		
		foreach ($this->helpStructure[$parentItem] as $key => $itemName) {
			$item = $this->items[$itemName];
			$hasPermission = true;
			// check the permissions of this item
			if ($item->permissions) {
				$hasPermission = false;
				$permissions = explode(',', $item->permissions);
				foreach ($permissions as $permission) {
					if (WCF::getUser()->getPermission($permission)) {
						$hasPermission = true;
						break;
					}
				}
			}
			
			if ($hasPermission) {
				// check permissions of the children
				$this->checkPermissions($itemName);
			}
			else {
				// remove this item
				unset($this->helpStructure[$parentItem][$key], $this->items[$itemName]);
			}
		}
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		// get top item
		$this->loadTopItem();
		$this->loadParentItems();
		
		// render table of contents
		$this->renderToc();
		
		// get items
		$this->loadItemList();
		$this->loadNextItem();
		$this->loadPreviousItem();
		
		// format parent items
		array_shift($this->parentItems);
		$this->parentItems = array_reverse($this->parentItems);
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'toc' => $this->toc,
			'parentHelpItem' => ($this->parentHelpItem ? $this->items[$this->parentHelpItem] : null),
			'items' => $this->itemList,
			'allItemNames' => $this->allItemNames,
			'nextItem' => $this->nextItem,
			'previousItem' => $this->previousItem,
			'parentItems' => $this->parentItems
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if (MODULE_HELP != 1) {
			throw new IllegalLinkException();
		}
		
		require_once(WCF_DIR.'lib/page/util/menu/PageMenu.class.php');
		PageMenu::setActiveMenuItem('wcf.header.menu.help');
		
		parent::show();
	}
	
	/**
	 * Gets the current top item.
	 */
	protected function loadTopItem() {
		if ($this->parentHelpItem) {
			$parentItem = $this->items[$this->parentHelpItem];
			while ($parentItem->parentHelpItem) {
				$parentItem = $this->items[$parentItem->parentHelpItem];
			}
			
			$this->topHelpItem = $parentItem->helpItem;
		}
	}
	
	/**
	 * Renders the table of contents.
	 */
	protected function renderToc($parent = '', $index = '', $openParents = 0, $depth = 0) {
		if (!isset($this->helpStructure[$parent])) {
			return;
		}
		
		$i = 1;
		$count = count($this->helpStructure[$parent]);
		foreach ($this->helpStructure[$parent] as $helpItem) {
			$itemIndex = $index . $i . '.';
			$this->items[$helpItem]->setIndex($itemIndex);
			//if (isset($this->parentItems[$helpItem])) $this->parentItems[$helpItem]['index'] = $itemIndex;
			
			$childrenOpenParents = $openParents + 1;
			$item = array('item' => $this->items[$helpItem]);
			$item['hasChildren'] = (!empty($this->helpStructure[$helpItem]) && !empty($this->parentItems[$helpItem]));
			$last = ($i == $count);
			if ($item['hasChildren'] && !$last) $childrenOpenParents = 1;
			$item['openParents'] = ((!$item['hasChildren'] && $last) ? $openParents : 0);
			$this->allItemNames[$helpItem] = $helpItem;
			if ($depth < 1 || (!empty($this->parentItems[$parent]) || ($this->parentHelpItem && $helpItem == $this->parentHelpItem))) $this->toc[] = $item;
			
			// children
			if ($item['hasChildren']) $this->renderToc($helpItem, $itemIndex, $childrenOpenParents, $depth + 1);
			$i++;
		}
	}
	
	/**
	 * Gets a list of children of the active help item.
	 */
	protected function loadItemList() {
		if (isset($this->helpStructure[$this->parentHelpItem])) {
			foreach ($this->helpStructure[$this->parentHelpItem] as $item) {
				$this->itemList[] = $this->items[$item];
			}
		}
	}
	
	/**
	 * Gets the next item.
	 */
	protected function loadNextItem() {
		if ($this->parentHelpItem) {
			for ($i = 0, $j = count($this->toc); $i < $j; $i++) {
				if ($this->toc[$i]['item']->helpItem == $this->parentHelpItem) {
					if ($i + 1 < $j) $this->nextItem = $this->toc[($i + 1)]['item'];
				}
			}
		}
	}
	
	/**
	 * Gets the previous item.
	 */
	protected function loadPreviousItem() {
		if ($this->parentHelpItem) {
			for ($i = 0, $j = count($this->toc); $i < $j; $i++) {
				if ($this->toc[$i]['item']->helpItem == $this->parentHelpItem) {
					if ($i > 0) $this->previousItem = $this->toc[($i - 1)]['item'];
				}
			}
		}
	}
	
	/**
	 * Gets a list of all parent help items.
	 */
	protected function loadParentItems() {
		if ($this->parentHelpItem) {
			$this->parentItems[$this->parentHelpItem] = $this->items[$this->parentHelpItem];
			$parentItem = $this->items[$this->parentHelpItem];
			while ($parentItem->parentHelpItem) {
				$parentItem = $this->items[$parentItem->parentHelpItem];
				$this->parentItems[$parentItem->helpItem] = $parentItem;
			}
		}
	}
}
?>