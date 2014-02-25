<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Shows the a button for the deactivation of a user signature. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.signature
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class UserProfileFrameSignatureListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_USER_SIGNATURE == 1 && WCF::getUser()->getPermission('admin.user.canEditUser')) {
			// add button
			if ($eventObj->getUser()->disableSignature == 1) {
				WCF::getTPL()->append('additionalAdminOptions', '<li><a href="index.php?action=UserSignatureEnable&amp;userID='.$eventObj->getUserID().'&amp;t='.SECURITY_TOKEN.SID_ARG_2ND.'">'.WCF::getLanguage()->get('wcf.user.profile.signature.enable').'</a></li>');
			}
			else {
				WCF::getTPL()->append('additionalAdminOptions', '<li><a href="index.php?action=UserSignatureDisable&amp;userID='.$eventObj->getUserID().'&amp;t='.SECURITY_TOKEN.SID_ARG_2ND.'">'.WCF::getLanguage()->get('wcf.user.profile.signature.disable').'</a></li>');
			}
		}
	}
}
?>