<?php
/**
 * @author	Marcel Werk
 * @copyright	2001-2007 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 */
$packageID = $this->installation->getPackageID();

// admin options
$sql = "UPDATE 	wcf".WCF_N."_group_option_value
	SET	optionValue = 1
	WHERE	groupID = 4
		AND optionID IN (
			SELECT	optionID
			FROM	wcf".WCF_N."_group_option
			WHERE	packageID IN (
					SELECT	dependency
					FROM	wcf".WCF_N."_package_dependency
					WHERE	packageID = ".$packageID."
				)
		)
		AND optionValue = '0'";
WCF::getDB()->sendQuery($sql);

// mod options
$sql = "UPDATE 	wcf".WCF_N."_group_option_value
	SET	optionValue = 1
	WHERE	groupID IN (5,6)
		AND optionID IN (
			SELECT	optionID
			FROM	wcf".WCF_N."_group_option
			WHERE	optionName LIKE 'mod.board.%'
				AND optionName <> 'mod.board.isSuperMod'
				AND packageID IN (
					SELECT	dependency
					FROM	wcf".WCF_N."_package_dependency
					WHERE	packageID = ".$packageID."
				)
		)
		AND optionValue = '0'";
WCF::getDB()->sendQuery($sql);

// super mod option
$sql = "UPDATE 	wcf".WCF_N."_group_option_value
	SET	optionValue = 1
	WHERE	groupID = 6
		AND optionID IN (
			SELECT	optionID
			FROM	wcf".WCF_N."_group_option
			WHERE	optionName = 'mod.board.isSuperMod'
				AND packageID IN (
					SELECT	dependency
					FROM	wcf".WCF_N."_package_dependency
					WHERE	packageID = ".$packageID."
				)
		)
		AND optionValue = '0'";
WCF::getDB()->sendQuery($sql);

// list admin & mod groups on team page by default
$sql = "UPDATE	wcf".WCF_N."_group
	SET	showOnTeamPage = 1
	WHERE	groupID IN (4,5,6)";
WCF::getDB()->sendQuery($sql);

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

// refresh style files
require_once(WCF_DIR.'lib/data/style/StyleEditor.class.php');
$sql = "SELECT * FROM wcf".WCF_N."_style";
$result = WCF::getDB()->sendQuery($sql);
while ($row = WCF::getDB()->fetchArray($result)) {
	$style = new StyleEditor(null, $row);
	$style->writeStyleFile();
}
?>