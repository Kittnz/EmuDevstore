<?php
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Adds the signature to the user page.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wcf.form.user.signature
 * @subpackage	system.event.listener
 * @category 	Community Framework (commercial)
 */
class UserPageSignatureListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (MODULE_USER_SIGNATURE == 1) {
			if ((!WCF::getUser()->userID || WCF::getUser()->showSignature) && !$eventObj->frame->getUser()->disableSignature) {
				$signature = '';
				if ($eventObj->frame->getUser()->signatureCache) {
					$signature = $eventObj->frame->getUser()->signatureCache;
				}
				else if ($eventObj->frame->getUser()->signature) {
					require_once(WCF_DIR.'lib/data/message/bbcode/MessageParser.class.php');
					$parser = MessageParser::getInstance();
					$parser->setOutputType('text/html');
					$signature = $parser->parse($eventObj->frame->getUser()->signature, $eventObj->frame->getUser()->enableSignatureSmilies, $eventObj->frame->getUser()->enableSignatureHtml, $eventObj->frame->getUser()->enableSignatureBBCodes, false);
				}
				
				if (!empty($signature)) {
					WCF::getTPL()->append('additionalAboutMeContent', '<div class="signature">'.$signature.'</div>');
				}
			}
		}
	}
}
?>