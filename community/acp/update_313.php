<?php
/**
 * @author	Marcel Werk
 * @copyright	2001-2010 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 */
// delete obsolet language items
$sql = "DELETE FROM	wcf".WCF_N."_language_item
	WHERE		languageItem IN (
				'wcf.help.item.board.index', 'wcf.help.item.board.index.description',
				'wcf.help.item.board.postadd', 'wcf.help.item.board.postadd.description',
				'wcf.help.item.board.moderation', 'wcf.help.item.board.board.moderation.description',
				'wcf.help.item.board.wiw', 'wcf.help.item.board.wiw.description',
				'wcf.help.item.usercp.moderation', 'wcf.help.item.usercp.moderation.description'
			)
			AND packageID = ".$this->installation->getPackageID();
WCF::getDB()->sendQuery($sql);
?>