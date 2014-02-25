<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObjectList.class.php');
require_once(WCF_DIR.'lib/data/message/pm/ViewablePM.class.php');

/**
 * Represents a list of private messages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm
 * @category 	Community Framework (commercial)
 */
class PMList extends DatabaseObjectList {
	/**
	 * sql order by statement
	 *
	 * @var	string
	 */
	public $sqlOrderBy = 'pm.time DESC';

	/**
	 * pm object
	 *
	 * @var PM
	 */
	public $pm = nulL;
	
	/**
	 * list of messages
	 * 
	 * @var array<ViewablePM>
	 */
	public $messages = array();
	
	/**
	 * list of pm ids
	 * 
	 * @var	array<integer>
	 */
	public $pmIDArray = array();
	
	/**
	 * list of pm ids
	 * 
	 * @var	array<integer>
	 */
	public $attachmentPMIDArray = array();
	
	/**
	 * attachment list object
	 * 
	 * @var	MessageAttachmentList
	 */
	public $attachmentList = null;
	
	/**
	 * list of attachments
	 * 
	 * @var	array
	 */
	public $attachments = array();
	
	/**
	 * list of recipients
	 * 
	 * @var	array
	 */
	public $recipients = array();
	
	/**
	 * Creates a new PMList object.
	 *
	 * @param	PM	$pm
	 */
	public function __construct(PM $pm) {
		$this->pm = $pm;
	}

	/**
	 * @see DatabaseObjectList::countObjects()
	 */
	public function countObjects() {
		if ($this->pm->parentPmID) {
			$sql = "SELECT		COUNT(*) AS count
				FROM		wcf".WCF_N."_pm pm
				".$this->sqlJoins."
				WHERE		pm.parentPmID = ".$this->pm->parentPmID."
						AND (
							(pm.userID = ".WCF::getUser()->userID." AND (pm.saveInOutbox = 1 OR pm.isDraft = 1))
							OR (pm.pmID IN (
								SELECT	pmID
								FROM	wcf".WCF_N."_pm_to_user
								WHERE	recipientID = ".WCF::getUser()->userID."
									AND isDeleted < 2
							) AND pm.isDraft = 0)
						)
						".(!empty($this->sqlConditions) ? "AND ".$this->sqlConditions : '');
			$row = WCF::getDB()->getFirstRow($sql);
			return $row['count'];
		}
		return 1;
	}
	
	/**
	 * Gets pm ids.
	 */
	protected function readObjectIDArray() {
		if ($this->pm->parentPmID) {
			$sql = "SELECT		pm.pmID, pm.attachments
				FROM		wcf".WCF_N."_pm pm
				".$this->sqlJoins."
				WHERE		pm.parentPmID = ".$this->pm->parentPmID."
						AND (
							(pm.userID = ".WCF::getUser()->userID." AND (pm.saveInOutbox = 1 OR pm.isDraft = 1))
							OR (pm.pmID IN (
								SELECT	pmID
								FROM	wcf".WCF_N."_pm_to_user
								WHERE	recipientID = ".WCF::getUser()->userID."
									AND isDeleted < 2
							) AND pm.isDraft = 0)
						)
						".(!empty($this->sqlConditions) ? "AND ".$this->sqlConditions : '')."
				".(!empty($this->sqlOrderBy) ? "ORDER BY ".$this->sqlOrderBy : '');
			$result = WCF::getDB()->sendQuery($sql, $this->sqlLimit, $this->sqlOffset);
			while ($row = WCF::getDB()->fetchArray($result)) {
				$this->pmIDArray[] = $row['pmID'];
				// attachments
				if ($row['attachments'] != 0) {
					$this->attachmentPMIDArray[] = $row['pmID'];
				}
			}
		}
		else {
			$this->pmIDArray[] = $this->pm->pmID;
			if ($this->pm->attachments) {
				$this->attachmentPMIDArray[] = $this->pm->pmID;
			}
		}
		
		if (count($this->pmIDArray)) {
			$this->readRecipients();
			$this->readAttachments();
		}
	}
	
	/**
	 * Gets a list of recipients.
	 */
	protected function readRecipients() {
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_pm_to_user
			WHERE		pmID IN (".implode(',', $this->pmIDArray).")
					AND isBlindCopy = 0
			ORDER BY	recipient";
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (!isset($this->recipients[$row['pmID']])) $this->recipients[$row['pmID']] = array();
			$this->recipients[$row['pmID']][] = new PMRecipient(null, null, $row);
		}
	}
	
	/**
	 * Gets a list of attachments.
	 */
	protected function readAttachments() {
		// read attachments
		if (MODULE_ATTACHMENT == 1 && count($this->attachmentPMIDArray)) {
			require_once(WCF_DIR.'lib/data/attachment/MessageAttachmentList.class.php');
			$this->attachmentList = new MessageAttachmentList($this->attachmentPMIDArray, 'pm', '', WCF::getPackageID('com.woltlab.wcf.data.message.pm'));
			$this->attachmentList->readObjects();
			$this->attachments = $this->attachmentList->getSortedAttachments();
			require_once(WCF_DIR.'lib/data/message/bbcode/AttachmentBBCode.class.php');
			AttachmentBBCode::setAttachments($this->attachments);
			
			if (count($this->attachments) > 0) {
				MessageAttachmentList::removeEmbeddedAttachments($this->attachments);
			}
		}
	}
	
	/**
	 * @see DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		// get ids
		$this->readObjectIDArray();
		
		// get objects
		$sql = "SELECT		".(!empty($this->sqlSelects) ? $this->sqlSelects.',' : '')."
					pm_to_user.*, user_option.*, avatar.*, user.*, rank.*,
					pm.*, user.userID,
					(SELECT	MAX(isViewed) FROM wcf".WCF_N."_pm_to_user WHERE pmID = pm.pmID) AS isViewedByOne
			FROM		wcf".WCF_N."_pm pm
			LEFT JOIN 	wcf".WCF_N."_pm_to_user pm_to_user
			ON 		(pm_to_user.pmID = pm.pmID AND pm_to_user.recipientID = ".WCF::getUser()->userID.")
			LEFT JOIN 	wcf".WCF_N."_user user
			ON 		(user.userID = pm.userID)
			LEFT JOIN 	wcf".WCF_N."_user_option_value user_option
			ON 		(user_option.userID = pm.userID)
			LEFT JOIN	wcf".WCF_N."_avatar avatar
			ON		(avatar.avatarID = user.avatarID)
			LEFT JOIN 	wcf".WCF_N."_user_rank rank
			ON		(rank.rankID = user.rankID)			
			".$this->sqlJoins."
			WHERE		pm.pmID IN (".implode(',', $this->pmIDArray).")
			".(!empty($this->sqlOrderBy) ? "ORDER BY ".$this->sqlOrderBy : '');
		$result = WCF::getDB()->sendQuery($sql);
		while ($row = WCF::getDB()->fetchArray($result)) {
			if (isset($this->recipients[$row['pmID']])) {
				if ($row['userID'] != WCF::getUser()->userID) {
					// remove blind copies
					foreach ($this->recipients[$row['pmID']] as $key => $recipient) {
						if ($recipient->isBlindCopy) {
							unset($this->recipients[$row['pmID']][$key]);
						}
					}
				}
				$row['recipients'] = $this->recipients[$row['pmID']];
			}
			else {
				$row['recipients'] = array();
			}
			
			$this->messages[] = new ViewablePM(null, $row);
		}
	}
	
	/**
	 * @see DatabaseObjectList::getObjects()
	 */
	public function getObjects() {
		return $this->messages;
	}
	
	/**
	 * Returns the list of attachments.
	 * 
	 * @return	array
	 */
	public function getAttachments() {
		return $this->attachments;
	}
}
?>