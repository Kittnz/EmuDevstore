<?php
// wcf imports
require_once(WCF_DIR.'lib/page/MultipleLinkPage.class.php');
require_once(WCF_DIR.'lib/data/tag/TagEngine.class.php');
require_once(WCF_DIR.'lib/data/tag/TagCloud.class.php');

/**
 * Shows a list of tagged objects.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.search.tagging
 * @subpackage	page
 * @category 	Community Framework
 */
class TaggedObjectsPage extends MultipleLinkPage {
	// system
	public $templateName = 'taggedObjects';
	
	/**
	 * tag id
	 * 
	 * @var	integer
	 */
	public $tagID = 0;
	
	/**
	 * tag name
	 * 
	 * @var	string
	 */
	public $tag = '';
	
	/**
	 * tag object
	 * 
	 * @var	Tag
	 */
	public $tagObj = null;
	
	/**
	 * taggable id
	 * 
	 * @var	integer
	 */
	public $taggableID = 0;
	
	/**
	 * taggable object
	 * 
	 * @var	Taggable
	 */
	public $taggable = null;
	
	/**
	 * list of available taggables.
	 * 
	 * @var	array<Taggable>
	 */
	public $availableTaggables = array();
	
	/**
	 * list of tagged objects
	 * 
	 * @var	array
	 */
	public $taggedObjects = array();
	
	/**
	 * list of tags for tag cloud
	 * 
	 * @var	array<Tag>
	 */
	public $tags = array();
	
	/**
	 * @see Page::readParameters();
	 */
	public function readParameters() {
		parent::readParameters();

		if (isset($_REQUEST['tagID'])) $this->tagID = intval($_REQUEST['tagID']);
		if (isset($_REQUEST['tag'])) $this->tag = StringUtil::trim($_REQUEST['tag']);
		if (isset($_REQUEST['taggableID'])) $this->taggableID = intval($_REQUEST['taggableID']);
		
		// get tag object
		if ($this->tagID != 0) {
			$this->tagObj = TagEngine::getInstance()->getTagByID($this->tagID);
			if ($this->tagObj === null) {
				throw new IllegalLinkException();
			}
			
			$this->tagID = $this->tagObj->getID();
		}
		else if (!empty($this->tag)) {
			$this->tagObj = TagEngine::getInstance()->getTagByName($this->tag, WCF::getSession()->getVisibleLanguageIDArray());
			if ($this->tagObj === null) {
				throw new IllegalLinkException();
			}
			
			$this->tagID = $this->tagObj->getID();
		}

		// get taggable
		if ($this->taggableID != 0) {
			$this->taggable = TagEngine::getInstance()->getTaggableByID($this->taggableID);
			if ($this->taggable === null) {
				throw new IllegalLinkException();
			}
		}
		
		// get available taggables
		if ($this->tagObj) {
			$this->availableTaggables = TagEngine::getInstance()->getTaggablesByTagID($this->tagObj->getID());
			if (count($this->availableTaggables) == 1) {
				$this->taggableID = $this->availableTaggables[0]->getTaggableID();
				$this->taggable = $this->availableTaggables[0];
			}
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		if ($this->tagObj) {
			if ($this->taggableID != 0) {
				return $this->taggable->countObjectsByTagID($this->tagObj->getID());
			}
		}
		return 0;
	}
	
	/**
	 * @see Page::readData();
	 */
	public function readData() {
		parent::readData();
		
		if ($this->tagObj) {
			// get tagged objects
			if ($this->taggableID == 0) {
				// get objects for overview
				$this->taggedObjects = TagEngine::getInstance()->getGroupedTaggedObjectsByTagID($this->tagObj->getID());
			}
			else {
				$this->taggedObjects = $this->taggable->getObjectsByTagID($this->tagObj->getID(), $this->itemsPerPage, ($this->pageNo - 1) * $this->itemsPerPage);
			}
		}
			
		// get tags
		$tagCloud = new TagCloud(WCF::getSession()->getVisibleLanguageIDArray());
		$this->tags = $tagCloud->getTags(($this->tagObj !== null ? 50 : 500));
	}
	
	/**
	 * @see Page::assignVariables();
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
			'tags' => $this->tags,
			'tagObj' => $this->tagObj,
			'tagID' => $this->tagID,
			'taggedObjects' => $this->taggedObjects,
			'taggable' => $this->taggable,
			'taggableID' => $this->taggableID,
			'availableTaggables' => $this->availableTaggables,
			'allowSpidersToIndexThisPage' => true
		));
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if (!MODULE_TAGGING) {
			throw new IllegalLinkException();
		}
		
		parent::show();
	}
}
?>