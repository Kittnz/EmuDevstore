<?php
require_once(WCF_DIR.'lib/data/message/pm/PM.class.php');
require_once(WCF_DIR.'lib/data/user/UserProfile.class.php');
require_once(WCF_DIR.'lib/data/message/bbcode/MessageParser.class.php');
require_once(WCF_DIR.'lib/data/message/bbcode/AttachmentBBCode.class.php');
require_once(WCF_DIR.'lib/data/message/sidebar/MessageSidebarObject.class.php');

/**
 * This class extends PM class with functions to display the private message.
 *
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	data.message.pm
 * @category 	Community Framework (commercial)
 */
class ViewablePM extends PM implements MessageSidebarObject {
	protected $user;
	protected $signature = null;
	
	/**
	 * @see Message::getText()
	 */
	public function getFormattedMessage() {
		AttachmentBBCode::setMessageID($this->pmID);
		$parser = MessageParser::getInstance();
		$parser->setOutputType('text/html');
		return $parser->parse($this->message, $this->enableSmilies, $this->enableHtml, $this->enableBBCodes, !$this->messagePreview);
	}
	
	/**
	 * Returns an excerpt of this private message.
	 * 
	 * @return	string
	 */
	public function getMessagePreview() {
		AttachmentBBCode::setMessageID($this->pmID);
		$parser = MessageParser::getInstance();
		$parser->setOutputType('text/plain');
		$message = $parser->parse($this->message, $this->enableSmilies, $this->enableHtml, $this->enableBBCodes, false);
		if (StringUtil::length($message) > 500) {
			$message = StringUtil::substring($message, 0, 497) . '...';
		}		
		
		return $message;
	}
	
	/**
	 * @see PM::handleData()
	 */
	protected function handleData($data) {
		if (!$data['username']) $data['username'] = WCF::getLanguage()->get('wcf.pm.author.system');
		parent::handleData($data);
		$this->user = new UserProfile(null, $data);
	}
	
	/**
	 * Returns true, if this post is marked in the active session.
	 */
	public function isMarked() {
		$sessionVars = WCF::getSession()->getVars();
		if (isset($sessionVars['markedPMs'])) {
			if (in_array($this->pmID, $sessionVars['markedPMs'])) return 1;
		}
		
		return 0;
	}
	
	/**
	 * Returns the signature of the message author.
	 * 
	 * @return	string
	 */
	public function getSignature() {
		if ($this->signature === null) {
			$this->signature = '';
			
			if ($this->showSignature && (!WCF::getUser()->userID || WCF::getUser()->showSignature) && !$this->user->disableSignature) {
				if ($this->user->signatureCache) $this->signature = $this->user->signatureCache;
				else if ($this->user->signature) {
					$parser = MessageParser::getInstance();
					$parser->setOutputType('text/html');
					$this->signature = $parser->parse($this->user->signature, $this->user->enableSignatureSmilies, $this->user->enableSignatureHtml, $this->user->enableSignatureBBCodes, false);
				}
			}
		}
		
		return $this->signature;
	}
	
	// MessageSidebarObject implementation
	/**
	 * @see MessageSidebarObject::getUser()
	 */
	public function getUser() {
		return $this->user;
	}
	
	/**
	 * @see MessageSidebarObject::getMessageID()
	 */
	public function getMessageID() {
		return $this->pmID;
	}
	
	/**
	 * @see MessageSidebarObject::getMessageType()
	 */
	public function getMessageType() {
		return 'pm';
	}
}
?>