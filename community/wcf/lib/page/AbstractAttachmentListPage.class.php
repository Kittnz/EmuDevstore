<?php
// wcf imports
require_once(WCF_DIR.'lib/page/SortablePage.class.php');
require_once(WCF_DIR.'lib/page/util/InlineCalendar.class.php');

/**
 * Shows a list of attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.data.message.attachment
 * @subpackage	page
 * @category 	Community Framework
 */
abstract class AbstractAttachmentListPage extends SortablePage {
	// system
	public $defaultSortField = 'attachmentName';
	
	// parameters
	public $containerType = '';
	public $fileType = '';
	public $isImage = 0;
	public $showThumbnail = 0;
	public $filename = '';
	public $greaterThan = 0;
	public $fromDay = 0;
	public $fromMonth = 0;
	public $fromYear = '';
	public $untilDay = 0;
	public $untilMonth = 0;
	public $untilYear = '';
	
	/**
	 * filtered attachment statistics
	 * 
	 * @var	array
	 */
	public $stats = array();
	
	/**
	 * total attachment statistics
	 * 
	 * @var	array
	 */
	public $statsTotal = array();
	
	/**
	 * list of available container types
	 *
	 * @var array<string>
	 */
	public $availableContainerTypes = array();
	
	/**
	 * list of available file types
	 *
	 * @var array<string>
	 */
	public $availableFileTypes = array();
	
	/**
	 * attachment list object
	 * 
	 * @var	UserAttachmentList
	 */
	public $attachmentList = null;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['containerType'])) $this->containerType = $_REQUEST['containerType'];
		if (isset($_REQUEST['fileType'])) $this->fileType = $_REQUEST['fileType'];
		if (isset($_REQUEST['isImage'])) $this->isImage = intval($_REQUEST['isImage']);
		if (isset($_REQUEST['showThumbnail'])) $this->showThumbnail = intval($_REQUEST['showThumbnail']);
		if (isset($_REQUEST['filename'])) $this->filename = $_REQUEST['filename'];
		if (isset($_REQUEST['greaterThan'])) $this->greaterThan = intval($_REQUEST['greaterThan']);
		
		// period
		if (isset($_REQUEST['fromDay'])) $this->fromDay = intval($_REQUEST['fromDay']);
		if (isset($_REQUEST['fromMonth'])) $this->fromMonth = intval($_REQUEST['fromMonth']);
		if (isset($_REQUEST['fromYear'])) {
			$this->fromYear = intval($_REQUEST['fromYear']);
			if (empty($this->fromYear)) $this->fromYear = '';
		}
		if (isset($_REQUEST['untilDay'])) $this->untilDay = intval($_REQUEST['untilDay']);
		if (isset($_REQUEST['untilMonth'])) $this->untilMonth = intval($_REQUEST['untilMonth']);
		if (isset($_REQUEST['untilYear'])) {
			$this->untilYear = intval($_REQUEST['untilYear']);
			if (empty($this->untilYear)) $this->untilYear = '';
		}
		
		// get select options
		$this->availableContainerTypes = $this->attachmentList->getAvailableContainerTypes();
		$this->availableFileTypes = $this->attachmentList->getAvailableFileTypes();
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		// get total stats
		$this->statsTotal = $this->attachmentList->getStats();
		
		// set sql conditions
		$this->setSQLConditions();
		
		parent::readData();
		
		// get stats
		if (!empty($this->containerType) || !empty($this->fileType) || $this->isImage == 1 || !empty($this->filename) || $this->greaterThan > 0) {
			$this->stats = $this->attachmentList->getStats();
		}
		else {
			$this->stats = $this->statsTotal;
		}
		
		// read objects
		$this->attachmentList->sqlOffset = ($this->pageNo - 1) * $this->itemsPerPage;
		$this->attachmentList->sqlLimit = $this->itemsPerPage;
		$this->attachmentList->sqlOrderBy = $this->sortField." ".$this->sortOrder;
		$this->attachmentList->readObjects();
	}
	
	/**
	 * Sets the sql conditions.
	 */
	protected function setSQLConditions() {
		if (!empty($this->containerType)) $this->attachmentList->sqlConditions .= " AND attachment.containerType = '".escapeString($this->containerType)."'";
		if (!empty($this->fileType)) $this->attachmentList->sqlConditions .= " AND attachment.fileType = '".escapeString($this->fileType)."'";
		if ($this->isImage == 1) $this->attachmentList->sqlConditions .= " AND attachment.isImage = 1";
		if (!empty($this->filename)) $this->attachmentList->sqlConditions .= " AND attachment.attachmentName LIKE '%".escapeString($this->filename)."%'";
		if ($this->greaterThan > 0) $this->attachmentList->sqlConditions .= " AND attachment.attachmentSize > ".$this->greaterThan;
		
		// period
		if (!empty($this->fromYear)) {
			$fromYear = $this->fromYear;
			if ($fromYear < 100) $fromYear += 1900;
			if (checkdate($this->fromMonth, $this->fromDay, $fromYear)) {
				$this->attachmentList->sqlConditions .= " AND attachment.uploadTime > ".intval(gmmktime(0, 0, 0, $this->fromMonth, $this->fromDay, $fromYear));
			}
		}
		if (!empty($this->untilYear)) {
			$untilYear = $this->untilYear;
			if ($untilYear < 100) $untilYear += 1900;
			if (checkdate($this->untilMonth, $this->untilDay, $untilYear)) {
				$this->attachmentList->sqlConditions .= " AND attachment.uploadTime < ".intval(gmmktime(23, 59, 59, $this->untilMonth, $this->untilDay, $untilYear));
			}
		}
	}
	
	/**
	 * @see SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'attachmentID':
			case 'userID':
			case 'containerID':
			case 'containerType':
			case 'attachmentName':
			case 'attachmentSize': 
			case 'fileType':
			case 'downloads':
			case 'lastDownloadTime':
			case 'uploadTime': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
	
	/**
	 * @see MultipleLinkPage::countItems()
	 */
	public function countItems() {
		parent::countItems();
		
		return $this->attachmentList->countObjects();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		InlineCalendar::assignVariables();
		WCF::getTPL()->assign(array(
			'attachments' => $this->attachmentList->getObjects(),
			'containerType' => $this->containerType,
			'fileType' => $this->fileType,
			'isImage' => $this->isImage,
			'availableContainerTypes' => $this->availableContainerTypes,
			'availableFileTypes' => $this->availableFileTypes,
			'showThumbnail' => $this->showThumbnail,
			'stats' => $this->stats,
			'statsTotal' => $this->statsTotal,
			'filename' => $this->filename,
			'greaterThan' => $this->greaterThan,
			'fromDay' => $this->fromDay,
			'fromMonth' => $this->fromMonth,
			'fromYear' => $this->fromYear,
			'untilDay' => $this->untilDay,
			'untilMonth' => $this->untilMonth,
			'untilYear' => $this->untilYear
		));
	}
}
?>