<?php
// wcf imports
require_once(WCF_DIR.'lib/system/event/EventListener.class.php');

/**
 * Shows the amout of posts on profile page.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	WoltLab Burning Board License <http://www.woltlab.com/products/burning_board/license.php>
 * @package	com.woltlab.wbb
 * @subpackage	system.event.listener
 * @category 	Burning Board
 */
class UserPagePostsListener implements EventListener {
	/**
	 * @see EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if ($eventName == 'init') {
			$eventObj->sqlSelects .= 'wbb_user.posts,';
			$eventObj->sqlJoins .= ' LEFT JOIN wbb'.WBB_N.'_user wbb_user
						ON (wbb_user.userID = user.userID) ';
		}
		else if ($eventName == 'assignVariables') {
			$user = $eventObj->frame->getUser();
			$eventObj->generalInformation[] = array(
				'icon' => StyleManager::getStyle()->getIconPath('postM.png'),
				'title' => WCF::getLanguage()->get('wcf.user.posts'),
				'value' => '<a href="index.php?form=Search&amp;types[]=post&amp;userID='.$user->userID.SID_ARG_2ND.'" title="'.WCF::getLanguage()->get('wcf.user.profile.search', array('$username' => StringUtil::encodeHTML($user->username))).'">'.StringUtil::formatInteger(intval($user->posts)) . 
					($user->getProfileAge() > 1 ? ' ' . WCF::getLanguage()->get('wcf.user.postsPerDay', array('$posts' => StringUtil::formatDouble($user->posts / $user->getProfileAge()))) : '') . '</a>');

			// show last 5 posts
			if (PROFILE_SHOW_LAST_POSTS) {
				require_once(WBB_DIR.'lib/data/post/ViewablePost.class.php');
				require_once(WBB_DIR.'lib/data/board/Board.class.php');
				$boardIDArray = Board::getAccessibleBoardIDArray(array('canViewBoard', 'canEnterBoard', 'canReadThread'));
				
				if (count($boardIDArray)) {
					$posts = array();
					$sql = "SELECT		post.postID, post.time,
								CASE WHEN post.subject <> '' THEN post.subject ELSE thread.topic END AS subject
						FROM		wbb".WBB_N."_user_last_post user_last_post
						LEFT JOIN	wbb".WBB_N."_post post
						ON		(post.postID = user_last_post.postID)
						LEFT JOIN	wbb".WBB_N."_thread thread
						ON		(thread.threadID = post.threadID)
						WHERE		user_last_post.userID = ".$user->userID."
								AND post.isDeleted = 0
								AND post.isDisabled = 0
								AND thread.boardID IN (".implode(',', $boardIDArray).")
								".(count(WCF::getSession()->getVisibleLanguageIDArray()) ? "AND thread.languageID IN (".implode(',', WCF::getSession()->getVisibleLanguageIDArray()).")" : "")."
						ORDER BY	user_last_post.time DESC";
					$result = WCF::getDB()->sendQuery($sql, 5);
					while ($row = WCF::getDB()->fetchArray($result)) {
						$posts[] = new ViewablePost(null, $row);
					}
					
					if (count($posts)) {
						WCF::getTPL()->assign(array(
							'posts' => $posts,
							'user' => $user
						));
						WCF::getTPL()->append('additionalContent2', WCF::getTPL()->fetch('userProfileLastPosts'));
					}
				}
			}
		}
	}
}
?>