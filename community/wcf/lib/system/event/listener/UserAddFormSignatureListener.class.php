<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');
require_once(WCF_DIR.'lib/acp/form/UserEditForm.class.php');

/**
 * Adds the signature textarea to user edit form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.signature
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class UserAddFormSignatureListener implements EventListener {
	public $signature = '';
	public $enableSmilies = 1;
	public $enableHtml = 0;
	public $enableBBCodes = 1;
	public $disableSignature = 0;
	public $disableSignatureReason = '';
	
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_USER_SIGNATURE == 1) {
			if ($eventName == 'readFormParameters') {
				$this->enableSmilies = $this->enableBBCodes = 0;
				if (isset($_POST['signature'])) $this->signature = $_POST['signature'];
				if (isset($_POST['enableSmilies'])) $this->enableSmilies = intval($_POST['enableSmilies']);
				if (isset($_POST['enableHtml'])) $this->enableHtml = intval($_POST['enableHtml']);
				if (isset($_POST['enableBBCodes'])) $this->enableBBCodes = intval($_POST['enableBBCodes']);
				if (isset($_POST['disableSignature'])) $this->disableSignature = intval($_POST['disableSignature']);
				if (isset($_POST['disableSignatureReason'])) $this->disableSignatureReason = $_POST['disableSignatureReason'];
			}
			else if ($eventName == 'save') {
				// update user
				$eventObj->additionalFields['signature'] = $this->signature;
				$eventObj->additionalFields['enableSignatureSmilies'] = $this->enableSmilies;
				$eventObj->additionalFields['enableSignatureHtml'] = $this->enableHtml;
				$eventObj->additionalFields['enableSignatureBBCodes'] = $this->enableBBCodes;
				$eventObj->additionalFields['disableSignature'] = $this->disableSignature;
				$eventObj->additionalFields['disableSignatureReason'] = $this->disableSignatureReason;
				$eventObj->additionalFields['signatureCache'] = '';
			}
			else if ($eventName == 'assignVariables') {
				// get default values
				if (!count($_POST) && $eventObj instanceof UserEditForm) {
					$this->signature = $eventObj->user->signature;
					$this->enableSmilies = $eventObj->user->enableSignatureSmilies;
					$this->enableHtml = $eventObj->user->enableSignatureHtml;
					$this->enableBBCodes = $eventObj->user->enableSignatureBBCodes;
					$this->disableSignature = $eventObj->user->disableSignature;
					$this->disableSignatureReason = $eventObj->user->disableSignatureReason;
				}
				
				WCF::getTPL()->assign(array(
					'signature' => $this->signature,
					'enableSmilies' => $this->enableSmilies,
					'enableHtml' => $this->enableHtml,
					'enableBBCodes' => $this->enableBBCodes,
					'disableSignature' => $this->disableSignature,
					'disableSignatureReason' => $this->disableSignatureReason
				));
				
				WCF::getTPL()->append(array(
					'additionalTabs' => '<li id="signature"><a onclick="tabMenu.showSubTabMenu(\'signature\');"><span>'.WCF::getLanguage()->get('wcf.user.signature').'</span></a></li>',
					'additionalTabContents' => WCF::getTPL()->fetch('userAddSignature')
				));
			}
		}
	}
}
?>