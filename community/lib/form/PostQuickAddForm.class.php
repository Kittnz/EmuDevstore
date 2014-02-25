<?php
// wbb imports
require_once(WBB_DIR.'lib/form/PostAddForm.class.php');

/**
 * Shows the thread quick reply form.
 *
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	form
 * @category 	Burning Board
 */
class PostQuickAddForm extends PostAddForm {
	/**
	 * @see Form::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		// default settings
		$this->closeThread = $this->thread->isClosed;
		$this->subscription = $this->thread->subscribed;
		if (WCF::getUser()->userID) {
			// options
			$this->parseURL = WCF::getUser()->{$this->permissionType.'ParseURL'};
			$this->enableSmilies = WCF::getUser()->{$this->permissionType.'EnableSmilies'};
			$this->enableHtml = WCF::getUser()->{$this->permissionType.'EnableHtml'};
			$this->enableBBCodes = WCF::getUser()->{$this->permissionType.'EnableBBCodes'};
			if ($this->showSignatureSetting) {
				$this->showSignature = WCF::getUser()->{$this->permissionType.'ShowSignature'};
			}
			
			if (!$this->subscription && WCF::getUser()->enableSubscription) {
				$this->subscription = 1;
			}
		}
		else {
			// options
			$this->parseURL = MESSAGE_FORM_DEFAULT_PARSE_URL;
			$this->enableSmilies = MESSAGE_FORM_DEFAULT_ENABLE_SMILIES;
			$this->enableHtml = MESSAGE_FORM_DEFAULT_ENABLE_HTML;
			$this->enableBBCodes = MESSAGE_FORM_DEFAULT_ENABLE_BBCODES;
		}
		
		
		$this->enableSmilies = intval($this->enableSmilies && WCF::getUser()->getPermission('user.'.$this->permissionType.'.canUseSmilies'));
		$this->enableHtml = intval($this->enableHtml && WCF::getUser()->getPermission('user.'.$this->permissionType.'.canUseHtml'));
		$this->enableBBCodes = intval($this->enableBBCodes && WCF::getUser()->getPermission('user.'.$this->permissionType.'.canUseBBCodes'));
	}
	
	/**
	 * Does nothing.
	 */
	protected function saveOptions() {}
}
?>