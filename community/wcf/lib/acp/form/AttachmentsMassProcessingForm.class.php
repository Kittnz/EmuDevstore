<?php	
// wcf imports
require_once(WCF_DIR.'lib/acp/form/ACPForm.class.php');
require_once(WCF_DIR.'lib/system/database/ConditionBuilder.class.php');
require_once(WCF_DIR.'lib/page/util/InlineCalendar.class.php');
require_once(WCF_DIR.'lib/data/attachment/AttachmentList.class.php');

/**
 * Shows the attachments mass processing form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.acp.content.attachment
 * @subpackage	acp.form
 * @category 	Community Framework (commercial)
 */
class AttachmentsMassProcessingForm extends ACPForm {
	public $templateName = 'attachmentsMassProcessing';
	public $activeMenuItem = 'wcf.acp.menu.link.attachment.massProcessing';
	public $neededPermissions = 'admin.attachment.canDeleteAttachment';
	public $availableActions = array('delete');
	public $affectedAttachments = 0;
	public $conditions;
	public $availableContainerTypes = array();
	public $availableFileTypes = array();
	
	// form parameters
	public $timeAfterDay = 0;
	public $timeAfterMonth = 0;
	public $timeAfterYear = '';
	public $timeBeforeDay = 0;
	public $timeBeforeMonth = 0;
	public $timeBeforeYear = '';
	public $lastDownloadTimeAfterDay = 0;
	public $lastDownloadTimeAfterMonth = 0;
	public $lastDownloadTimeAfterYear = '';
	public $lastDownloadTimeBeforeDay = 0;
	public $lastDownloadTimeBeforeMonth = 0;
	public $lastDownloadTimeBeforeYear = '';
	public $uploadedBy = '';
	public $containerType = '';
	public $sizeMoreThan = '', $sizeLessThan = '';
	public $downloadsMoreThan = '', $downloadsLessThan = '';
	public $fileType = '';
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		// time
		if (isset($_POST['timeAfterDay'])) $this->timeAfterDay = intval($_POST['timeAfterDay']);
		if (isset($_POST['timeAfterMonth'])) $this->timeAfterMonth = intval($_POST['timeAfterMonth']);
		if (!empty($_POST['timeAfterYear'])) $this->timeAfterYear = intval($_POST['timeAfterYear']);
		if (isset($_POST['timeBeforeDay'])) $this->timeBeforeDay = intval($_POST['timeBeforeDay']);
		if (isset($_POST['timeBeforeMonth'])) $this->timeBeforeMonth = intval($_POST['timeBeforeMonth']);
		if (!empty($_POST['timeBeforeYear'])) $this->timeBeforeYear = intval($_POST['timeBeforeYear']);
		if (isset($_POST['lastDownloadTimeAfterDay'])) $this->lastDownloadTimeAfterDay = intval($_POST['lastDownloadTimeAfterDay']);
		if (isset($_POST['lastDownloadTimeAfterMonth'])) $this->lastDownloadTimeAfterMonth = intval($_POST['lastDownloadTimeAfterMonth']);
		if (!empty($_POST['lastDownloadTimeAfterYear'])) $this->lastDownloadTimeAfterYear = intval($_POST['lastDownloadTimeAfterYear']);
		if (isset($_POST['lastDownloadTimeBeforeDay'])) $this->lastDownloadTimeBeforeDay = intval($_POST['lastDownloadTimeBeforeDay']);
		if (isset($_POST['lastDownloadTimeBeforeMonth'])) $this->lastDownloadTimeBeforeMonth = intval($_POST['lastDownloadTimeBeforeMonth']);
		if (!empty($_POST['lastDownloadTimeBeforeYear'])) $this->lastDownloadTimeBeforeYear = intval($_POST['lastDownloadTimeBeforeYear']);
		
		if (isset($_POST['uploadedBy'])) $this->uploadedBy = StringUtil::trim($_POST['uploadedBy']);
		if (isset($_POST['containerType'])) $this->containerType = $_POST['containerType'];
		if (!empty($_POST['sizeMoreThan'])) $this->sizeMoreThan = intval($_POST['sizeMoreThan']);
		if (!empty($_POST['sizeLessThan'])) $this->sizeLessThan = intval($_POST['sizeLessThan']);
		if (!empty($_POST['downloadsMoreThan'])) $this->downloadsMoreThan = intval($_POST['downloadsMoreThan']);
		if (!empty($_POST['downloadsLessThan'])) $this->downloadsLessThan = intval($_POST['downloadsLessThan']);
		if (isset($_POST['fileType'])) $this->fileType = $_POST['fileType'];
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();

		// action
		if (!in_array($this->action, $this->availableActions)) {
			throw new UserInputException('action');
		}
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		// build conditions
		$this->conditions = new ConditionBuilder();
		
		parent::save();
		
		// time
		if ($this->timeAfterDay && $this->timeAfterMonth && $this->timeAfterYear) {
			$time = @gmmktime(0, 0, 0, $this->timeAfterMonth, $this->timeAfterDay, $this->timeAfterYear);
			if ($time !== false && $time !== -1) $this->conditions->add("uploadTime > ".$time);
		}
		if ($this->timeBeforeDay && $this->timeBeforeMonth && $this->timeBeforeYear) {
			$time = @gmmktime(0, 0, 0, $this->timeBeforeMonth, $this->timeBeforeDay, $this->timeBeforeYear);
			if ($time !== false && $time !== -1) $this->conditions->add("uploadTime < ".$time);
		}
		// last download time
		if ($this->lastDownloadTimeAfterDay && $this->lastDownloadTimeAfterMonth && $this->lastDownloadTimeAfterYear) {
			$time = @gmmktime(0, 0, 0, $this->lastDownloadTimeAfterMonth, $this->lastDownloadTimeAfterDay, $this->lastDownloadTimeAfterYear);
			if ($time !== false && $time !== -1) $this->conditions->add("lastDownloadTime > ".$time);
		}
		if ($this->lastDownloadTimeBeforeDay && $this->lastDownloadTimeBeforeMonth && $this->lastDownloadTimeBeforeYear) {
			$time = @gmmktime(0, 0, 0, $this->lastDownloadTimeBeforeMonth, $this->lastDownloadTimeBeforeDay, $this->lastDownloadTimeBeforeYear);
			if ($time !== false && $time !== -1) $this->conditions->add("lastDownloadTime < ".$time);
		}
		
		// username
		if ($this->uploadedBy != '') {
			$users = preg_split('/\s*,\s*/', $this->uploadedBy, -1, PREG_SPLIT_NO_EMPTY);
			$users = array_map('escapeString', $users);
			$this->conditions->add("userID IN (SELECT userID FROM wcf".WCF_N."_user WHERE username IN ('".implode("','", $users)."'))");
		}
		
		// container type
		if ($this->containerType != '') {
			$this->conditions->add("containerType = '".escapeString($this->containerType)."'");
		}
		// file type
		if ($this->fileType != '') {
			$this->conditions->add("fileType = '".escapeString($this->fileType)."'");
		}
		
		// filesize
		if ($this->sizeMoreThan !== '') $this->conditions->add('attachmentSize > '.$this->sizeMoreThan);
		if ($this->sizeLessThan !== '') $this->conditions->add('attachmentSize < '.$this->sizeLessThan);
		// downloads
		if ($this->downloadsMoreThan !== '') $this->conditions->add('downloads > '.$this->downloadsMoreThan);
		if ($this->downloadsLessThan !== '') $this->conditions->add('downloads < '.$this->downloadsLessThan);
		
		// execute action
		$conditions = $this->conditions->get();
		switch ($this->action) {
			case 'delete':
				// delete files
				$sql = "SELECT	*
					FROM	wcf".WCF_N."_attachment
					".$conditions;
				$result = WCF::getDB()->sendQuery($sql);
				while ($row = WCF::getDB()->fetchArray($result)) {
					// delete attachment file
					if (file_exists(WCF_DIR.'attachments/attachment-'.$row['attachmentID'])) @unlink(WCF_DIR.'attachments/attachment-'.$row['attachmentID']);
					
					// delete thumbnail, if exists
					if (file_exists(WCF_DIR.'attachments/thumbnail-'.$row['attachmentID'])) @unlink(WCF_DIR.'attachments/thumbnail-'.$row['attachmentID']);
					
					$this->affectedAttachments++;
				}
				
				// delete database entries
				$sql = "DELETE FROM	wcf".WCF_N."_attachment
					".$conditions;
				WCF::getDB()->sendQuery($sql);
				break;
		}
		$this->saved();
		
		WCF::getTPL()->assign('affectedAttachments', $this->affectedAttachments);
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		$attachmentList = new AttachmentList();
		// get available container types
		$this->availableContainerTypes = $attachmentList->getAvailableContainerTypes();
		
		// get available file types
		$this->availableFileTypes = $attachmentList->getAvailableFileTypes();
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		InlineCalendar::assignVariables();
		WCF::getTPL()->assign(array(
			'timeAfterDay' => $this->timeAfterDay,
			'timeAfterMonth' => $this->timeAfterMonth,
			'timeAfterYear' => $this->timeAfterYear,
			'timeBeforeDay' => $this->timeBeforeDay,
			'timeBeforeMonth' => $this->timeBeforeMonth,
			'timeBeforeYear' => $this->timeBeforeYear,
			'lastDownloadTimeAfterDay' => $this->lastDownloadTimeAfterDay,
			'lastDownloadTimeAfterMonth' => $this->lastDownloadTimeAfterMonth,
			'lastDownloadTimeAfterYear' => $this->lastDownloadTimeAfterYear,
			'lastDownloadTimeBeforeDay' => $this->lastDownloadTimeBeforeDay,
			'lastDownloadTimeBeforeMonth' => $this->lastDownloadTimeBeforeMonth,
			'lastDownloadTimeBeforeYear' => $this->lastDownloadTimeBeforeYear,
			'uploadedBy' => $this->uploadedBy,
			'containerType' => $this->containerType,
			'sizeMoreThan' => $this->sizeMoreThan,
			'sizeLessThan' => $this->sizeLessThan,
			'downloadsMoreThan' => $this->downloadsMoreThan,
			'downloadsLessThan' => $this->downloadsLessThan,
			'fileType' => $this->fileType,
			'availableContainerTypes' => $this->availableContainerTypes,
			'availableFileTypes' => $this->availableFileTypes
		));
	}
}
?>