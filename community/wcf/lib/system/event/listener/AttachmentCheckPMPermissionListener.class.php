<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Checks the download permission for private message attachments.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.data.message.pm
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class AttachmentCheckPMPermissionListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		$attachment = $eventObj->attachment;
		
		if ($attachment['containerID'] && $attachment['containerType'] == 'pm') {
			if (!WCF::getUser()->userID) {
				throw new PermissionDeniedException();
			}
			require_once(WCF_DIR.'lib/data/message/pm/PM.class.php');
			$pm = new PM($attachment['containerID']);
			if (!$pm->hasAccess()) {
				throw new PermissionDeniedException();
			}
		}
	}
}
?>