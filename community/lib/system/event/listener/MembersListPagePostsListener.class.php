<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Shows the amout of posts in members list page.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.event.listener
 * @category 	Burning Board
 */
class MembersListPagePostsListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		/* OptionTypeMemberslistcolumns */
		if ($eventName == 'construct') {
			$eventObj->staticColumns['posts'] = 'wcf.user.posts';
		}
		/* MembersListPage */
		else if ($eventName == 'readParameters') {
			$eventObj->specialSortFields[] = 'posts';
		}
		else if ($eventName == 'readData') {
			if ($eventObj->sortField == 'posts') {
				$eventObj->userTable = 'wbb'.WBB_N.'_user';
			}
			else {
				$eventObj->sqlSelects .= 'wbb_user.posts,';
				$eventObj->sqlJoins .= ' LEFT JOIN wbb'.WBB_N.'_user wbb_user
							ON (wbb_user.userID = user.userID) ';
			}
		}
		else if ($eventName == 'assignVariables') {
			if (in_array('posts', $eventObj->activeFields)) {
				foreach ($eventObj->members as $key => $memberData) {
					$user = $memberData['user'];
					$username = $memberData['encodedUsername'];
					$eventObj->members[$key]['posts'] = '<a href="index.php?form=Search&amp;types[]=post&amp;userID='.$user->userID.SID_ARG_2ND.'" title="'.WCF::getLanguage()->get('wcf.user.profile.search', array('$username' => $username)).'">'.StringUtil::formatInteger(intval($user->posts)).'</a>';
				}
			}
		}
	}
}
?>