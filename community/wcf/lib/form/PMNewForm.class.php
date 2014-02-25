<?php
require_once(WCF_DIR.'lib/form/MessageForm.class.php');
require_once(WCF_DIR.'lib/data/message/pm/PMEditor.class.php');
require_once(WCF_DIR.'lib/data/user/UserProfile.class.php');
require_once(WCF_DIR.'lib/system/event/EventHandler.class.php');

/**
 * Form for creating new private messages.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	form
 * @category 	Community Framework (commercial)
 */
class PMNewForm extends MessageForm {
	// system
	public $showPoll = false;
	public $templateName = 'pmNew';
	
	/**
	 * list of private messages.
	 * 
	 * @var	PMList 
	 */
	public $pmList = null;
	
	// request parameters
	public $pmID = 0;
	public $pm, $newPm;
	public $forwarding = 0;
	public $reply = 0;
	public $replyToAll = 0;
	public $userID = 0;
	public $blindCopyArray = array();
	public $recipientArray = array();	
	public $attachmentListEditor = null;
	public $notificationRecipients = array();
	
	// form parameters
	public $recipients = '';
	public $blindCopies = '';
	public $preview, $send, $draft;
	
	/**
	 * @see Page::readParameters()
	 */
	public function readParameters() {
		AbstractForm::readParameters();
		
		if (isset($_REQUEST['pmID'])) $this->pmID = intval($_REQUEST['pmID']);
		if (isset($_REQUEST['forwarding'])) $this->forwarding = intval($_REQUEST['forwarding']);
		if (isset($_REQUEST['reply'])) $this->reply = intval($_REQUEST['reply']);
		if (isset($_REQUEST['replyToAll'])) $this->replyToAll = intval($_REQUEST['replyToAll']);
		if ($this->replyToAll == 1) $this->reply = 1;
		if (isset($_REQUEST['userID'])) $this->userID = intval($_REQUEST['userID']);
		
		// change default values for pm form
		if (isset($_GET['recipients'])) $this->recipients = StringUtil::trim($_GET['recipients']);
		if (isset($_GET['blindCopies'])) $this->blindCopies = StringUtil::trim($_GET['blindCopies']);
		
		// get private message
		if ($this->pmID) {
			$this->pm = new PMEditor($this->pmID);
			
			// check permission
			if (!WCF::getUser()->userID || !$this->pm->hasAccess()) {
				throw new PermissionDeniedException();
			}
			$this->pm->markAsRead();
			
			if (!$this->reply && !$this->forwarding && !$this->pm->isDraft) {
				throw new IllegalLinkException();
			}
			
			if ($this->reply && !$this->pm->userID) {
				throw new IllegalLinkException();
			}
		}
	}
	
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['recipients'])) 	$this->recipients 	= StringUtil::trim($_POST['recipients']);
		if (isset($_POST['blindCopies'])) 	$this->blindCopies 	= StringUtil::trim($_POST['blindCopies']);
		if (isset($_POST['preview']))		$this->preview		= (boolean) $_POST['preview'];
		if (isset($_POST['send']))		$this->send		= (boolean) $_POST['send'];
		if (isset($_POST['draft']))		$this->draft		= (boolean) $_POST['draft'];
	}
	
	/**
	 * @see Form::submit()
	 */
	public function submit() {
		// call submit event
		EventHandler::fireAction($this, 'submit');
		
		$this->readFormParameters();
		
		try {
			// attachment handling
			if ($this->showAttachments) {
				$this->attachmentListEditor->handleRequest();
			}
			
			// preview
			if ($this->preview) {
				require_once(WCF_DIR.'lib/data/message/bbcode/AttachmentBBCode.class.php');
				AttachmentBBCode::setAttachments($this->attachmentListEditor->getSortedAttachments());
				WCF::getTPL()->assign('preview', PMEditor::createPreview($this->subject, $this->text, $this->enableSmilies, $this->enableHtml, $this->enableBBCodes));
			}
			// send message or save as draft
			if ($this->send || $this->draft) {
				$this->validate();
				// no errors
				$this->save();
			}
		}
		catch (UserInputException $e) {
			$this->errorField = $e->getField();
			$this->errorType = $e->getType();
		}
	}
	
	/**
	 * @see Form::validate()
	 */
	public function validate() {
		// recipients
		if (empty($this->recipients) && empty($this->blindCopies) && !$this->draft) {
			throw new UserInputException('recipients');
		}
		
		$this->recipientArray = $this->validateRecipients($this->recipients);
		$this->blindCopyArray = $this->validateRecipients($this->blindCopies, 'blindCopies');
		if (!count($this->recipientArray) && !count($this->blindCopyArray) && !$this->draft) {
			throw new UserInputException('recipients');
		}
		
		// check number of recipients
		if (count($this->recipientArray) + count($this->blindCopyArray) > WCF::getUser()->getPermission('user.pm.maxRecipients')) {
			throw new UserInputException('recipients', 'tooManyRecipients');
		}
		
		// validate subject and text
		parent::validate();
	}
	
	/**
	 * Checks the given recipients.
	 */
	protected function validateRecipients($recipients, $field = 'recipients') {
		// explode multiple recipients to an array
		$recipientArray = explode(',', $recipients);
		$result = array();
		$error = array();
		
		// loop through recipients and check their settings
		foreach ($recipientArray as $recipient) {
			$recipient = StringUtil::trim($recipient);
			if (empty($recipient)) continue;
			
			try {
				// get recipient's profile
				$user = new UserProfile(null, null, $recipient);
				if (!$user->userID) {
					throw new UserInputException('recipient', 'notFound');
				}
				
				$reply = ($this->pm && $this->reply && $this->pm->userID == $user->userID && !$this->pm->isReplied);
				
				// check recipient's settings and permissions
				// can use pm
				if (!$user->getPermission('user.pm.canUsePm')) {
					throw new UserInputException('recipient', 'canNotUsePm');
				}
				
				if (!$reply) {
					// accepts messages
					if (!$user->acceptPm) {
						throw new UserInputException('recipient', 'doesNotAcceptPm');
					}
					
					// recipient only accept private messages from buddies
					if ($user->onlyBuddyCanPm && !$user->buddy && $user->userID != WCF::getUser()->userID && !WCF::getUser()->getPermission('user.profile.blacklist.canNotBeIgnored')) {
						throw new UserInputException('recipient', 'onlyAcceptsPmFromBuddies');
					}
				}
				
				// active user is ignored by recipient
				if ($user->ignoredUser) {
					throw new UserInputException('recipient', 'ignoresYou');
				}
				
				// check recipient's mailbox quota
				if ($user->pmTotalCount >= $user->getPermission('user.pm.maxPm') && !WCF::getUser()->getPermission('user.profile.blacklist.canNotBeIgnored')) {
					throw new UserInputException('recipient', 'recipientsMailboxIsFull');
				}
				
				// no error
				$result[] = array('userID' => $user->userID, 'username' => $user->username);
				
				if ($user->emailOnPm) {
					$this->notificationRecipients[$user->userID] = $user;
				}
			}
			catch (UserInputException $e) {
				$error[] = array('type' => $e->getType(), 'username' => $recipient);
			}
		}
		
		if (count($error)) {
			throw new UserInputException($field, $error);
		}
		
		return $result;
	}
	
	/**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		
		// save message in database
		$update = ($this->pm && !$this->forwarding && !$this->reply);
		
		// search for double posts
		if (!$update && PMEditor::test($this->recipientArray, $this->blindCopyArray, $this->subject, $this->text, WCF::getUser()->userID, WCF::getUser()->username)) {
			HeaderUtil::redirect('index.php?page=PMList' . SID_ARG_2ND_NOT_ENCODED);
			exit;
		}
		
		if ($update) {
			$this->pm->update($this->draft, $this->recipientArray, $this->blindCopyArray, $this->subject, $this->text, $this->getOptions(), $this->attachmentListEditor);
			$this->newPm = new PMEditor($this->pm->pmID);
		}
		else {					
			$this->newPm = PMEditor::create($this->draft, $this->recipientArray, $this->blindCopyArray, $this->subject, $this->text, WCF::getUser()->userID, WCF::getUser()->username, $this->getOptions(), $this->attachmentListEditor, ($this->pm && $this->reply) ? $this->pm->parentPmID : 0);
		}
		
		// reply & forwarding
		if ($this->pmID) {
			if ($this->reply) {
				$this->pm->markAsReplied();
			}
			
			if ($this->forwarding) {
				$this->pm->markAsForwarded();
			}
		}
		
		// send e-mail notification
		if (!$this->draft) {
			$this->sendNotification();
		}
		// apply rules
		if (!$this->draft) {
			$this->newPm->applyRules();
		}
		
		$this->saved();
		
		// forward to pm index
		if ($this->draft) HeaderUtil::redirect('index.php?page=PMList&folderID=-2' . SID_ARG_2ND_NOT_ENCODED);
		else HeaderUtil::redirect('index.php?page=PMList' . SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
	
	/**
	 * @see Page::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (!count($_POST)) {
			// default values
			// user
			if ($this->userID) {
				$user = new User($this->userID);
				$this->recipients = $user->username;
			}
			
			// existing message
			if ($this->pm) {
				if ($this->forwarding) {
					$data = array(
						'$author' => ($this->pm->username ? $this->pm->username : WCF::getLanguage()->get('wcf.pm.author.system')),
						'$date' => DateUtil::formatTime(null, $this->pm->time),
						'$recipients' => implode(', ', $this->pm->getRecipients()),
						'$subject' => $this->pm->subject,
						'$text' => $this->pm->message
					);
					
					$this->subject = WCF::getLanguage()->get('wcf.pm.forward.subject', array('$subject' => $this->pm->subject));
					$this->text = WCF::getLanguage()->get('wcf.pm.forward.text', $data);
				}
				else if ($this->reply) {
					$this->subject = WCF::getLanguage()->get('wcf.pm.reply.subject', array('$subject' => $this->pm->subject));
					// replace RE: RE: RE: by RE[3]:
					$this->subject = preg_replace('/(^RE: RE\[)(\d+)(?=\]:)/ie', '"RE[".(\\2+1)', $this->subject);
					$this->subject = preg_replace('/^(RE: RE:(?: RE:)+)/ie', '"RE[".substr_count("\\1", "RE:")."]:"', $this->subject);
					
					if ($this->replyToAll == 1) {
						$recipients = array();
						foreach ($this->pm->getRecipients() as $recipient) {
							$recipients[] = $recipient->recipient;
						}
						$recipients[] = $this->pm->username;
						$recipients = array_unique($recipients);
						foreach ($recipients as $key => $value) {
							if (StringUtil::toLowerCase($value) == StringUtil::toLowerCase(WCF::getUser()->username)) {
								unset($recipients[$key]);
								break;
							}
						}
						
						$this->recipients = implode(', ', $recipients);
					}
					else {
						$this->recipients = $this->pm->username;
					}
				}
				else {
					// edit draft
					$sql = "SELECT		recipient, isBlindCopy
						FROM		wcf".WCF_N."_pm_to_user
						WHERE		pmID = ".$this->pm->pmID."
						ORDER BY	recipient";
					$result = WCF::getDB()->sendQuery($sql);
					while ($row = WCF::getDB()->fetchArray($result)) {
						if ($row['isBlindCopy']) {
							if (!empty($this->blindCopies)) $this->blindCopies .= ', ';
							$this->blindCopies .= $row['recipient'];
						}
						else {
							if (!empty($this->recipients)) $this->recipients .= ', ';
							$this->recipients .= $row['recipient'];
						}
					}
					
					$this->subject = $this->pm->subject;
					$this->text = $this->pm->message;
					
					// options
					$this->enableSmilies = $this->pm->enableSmilies;
					$this->enableHtml = $this->pm->enableHtml;
					$this->enableBBCodes = $this->pm->enableBBCodes;
					$this->showSignature = $this->pm->showSignature;
				}
			}
		}
		
		if ($this->reply) {
			require_once(WCF_DIR.'lib/data/message/pm/PMList.class.php');
			$this->pmList = new PMList($this->pm);
			$this->pmList->sqlLimit = 10;
			$this->pmList->readObjects();
		}
	}
	
	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'pmID' => $this->pmID,
			'parentPmID' => ($this->pm ? $this->pm->parentPmID : 0),
			'forwarding' => $this->forwarding,
			'reply' => $this->reply,
			'recipients' => $this->recipients,
			'blindCopies' => $this->blindCopies,
			'replyToAll' => $this->replyToAll,
			'insertQuotes' => (!count($_POST) && empty($this->text) ? 1 : 0)
		));
		if ($this->pmList !== null) {
			WCF::getTPL()->assign(array(
				'privateMessages' => $this->pmList->getObjects(),
				'pmAttachments' => $this->pmList->getAttachments()
			));
		}
	}
	
	/**
	 * @see Page::show()
	 */
	public function show() {
		if (!MODULE_PM) {
			throw new IllegalLinkException();
		}
	
		// check permission
		WCF::getUser()->checkPermission('user.pm.canUsePm');
		if (!WCF::getUser()->userID) {
			throw new PermissionDeniedException();
		}
		
		// check max pm permission
		if (WCF::getUser()->pmTotalCount >= WCF::getUser()->getPermission('user.pm.maxPm')) {
			throw new NamedUserException(WCF::getLanguage()->get('wcf.pm.error.mailboxIsFull'));
		}
		
		// get max text length
		$this->maxTextLength = WCF::getUser()->getPermission('user.pm.maxLength');
		
		// check upload permissin
		if (MODULE_ATTACHMENT != 1 || !WCF::getUser()->getPermission('user.pm.canUploadAttachment')) {
			$this->showAttachments = false;
		}
		
		// get attachments editor
		require_once(WCF_DIR.'lib/data/attachment/MessageAttachmentListEditor.class.php');
		$this->attachmentListEditor = new MessageAttachmentListEditor((($this->pmID && !$this->forwarding && !$this->reply) ? array($this->pmID) : array()), 'pm', WCF::getPackageID('com.woltlab.wcf.data.message.pm'), WCF::getUser()->getPermission('user.pm.maxAttachmentSize'), WCF::getUser()->getPermission('user.pm.allowedAttachmentExtensions'), WCF::getUser()->getPermission('user.pm.maxAttachmentCount'));
		
		// show form
		parent::show();
	}
	
	/**
	 * Sends the email notification for recipients.
	 */
	protected function sendNotification() {
		if (count($this->notificationRecipients) > 0) {
			$this->newPm->sendNotifications($this->notificationRecipients);
		}
	}
}
?>