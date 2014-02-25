<div class="border titleBarPanel">
	<div class="containerHead">
		<div class="containerIcon">
			<a onclick="openList('{$listName}', { save: true, openTitle: '{lang}wbb.board.threads.open{/lang}', closeTitle: '{lang}wbb.board.threads.close{/lang}' })"><img src="{icon}minusS.png{/icon}" id="{$listName}Image" alt="" title="{lang}wbb.board.threads.close{/lang}" /></a>
		</div>
		<div class="containerContent">
			<h3{if $listHasNewThreads && !$listStatus} class="new"{/if}>{@$title}{if $listHasNewThreads && !$listStatus} ({#$listHasNewThreads}){/if}</h3>
		</div>
	</div>
</div>
<div class="border borderMarginRemove" id="{$listName}">
	<table class="tableList">
		<thead>
			<tr class="tableHead">
				{if $permissions.canMarkThread}
					<th class="columnMark">
						<div>
							<label class="emptyHead">
								<input name="threadMarkAll" type="checkbox" />
							</label>
						</div>
					</th>
				{/if}
				<th colspan="2" class="columnTopic{if $sortField == 'topic'} active{/if}">
					<div><a href="index.php?page=Board&amp;boardID={@$board->boardID}&amp;pageNo={@$pageNo}&amp;sortField=topic&amp;sortOrder={if $sortField == 'topic' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;daysPrune={@$daysPrune}&amp;status={@$status}&amp;prefix={@$encodedPrefix}&amp;languageID={@$languageID}&amp;tagID={@$tagID}{@SID_ARG_2ND}">
						{lang}wbb.board.threads.topic{/lang}{if $sortField == 'topic'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
					</a></div>
				</th>
				{if $enableRating}
					<th class="columnRating{if $sortField == 'ratingResult'} active{/if}">
						<div><a href="index.php?page=Board&amp;boardID={@$board->boardID}&amp;pageNo={@$pageNo}&amp;sortField=ratingResult&amp;sortOrder={if $sortField == 'ratingResult' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;daysPrune={@$daysPrune}&amp;status={@$status}&amp;prefix={@$encodedPrefix}&amp;languageID={@$languageID}&amp;tagID={@$tagID}{@SID_ARG_2ND}">
							{lang}wbb.board.threads.rating{/lang}{if $sortField == 'ratingResult'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
						</a></div>
					</th>
				{/if}
				<th class="columnReplies{if $sortField == 'replies'} active{/if}">
					<div><a href="index.php?page=Board&amp;boardID={@$board->boardID}&amp;pageNo={@$pageNo}&amp;sortField=replies&amp;sortOrder={if $sortField == 'replies' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;daysPrune={@$daysPrune}&amp;status={@$status}&amp;prefix={@$encodedPrefix}&amp;languageID={@$languageID}&amp;tagID={@$tagID}{@SID_ARG_2ND}">
						{lang}wbb.board.threads.replies{/lang}{if $sortField == 'replies'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
					</a></div>
				</th>
				<th class="columnViews{if $sortField == 'views'} active{/if}">
					<div><a href="index.php?page=Board&amp;boardID={@$board->boardID}&amp;pageNo={@$pageNo}&amp;sortField=views&amp;sortOrder={if $sortField == 'views' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;daysPrune={@$daysPrune}&amp;status={@$status}&amp;prefix={@$encodedPrefix}&amp;languageID={@$languageID}&amp;tagID={@$tagID}{@SID_ARG_2ND}">
						{lang}wbb.board.threads.views{/lang}{if $sortField == 'views'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
					</a></div>
				</th>
				<th class="columnLastPost{if $sortField == 'lastPostTime'} active{/if}">
					<div><a href="index.php?page=Board&amp;boardID={@$board->boardID}&amp;pageNo={@$pageNo}&amp;sortField=lastPostTime&amp;sortOrder={if $sortField == 'lastPostTime' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;daysPrune={@$daysPrune}&amp;status={@$status}&amp;prefix={@$encodedPrefix}&amp;languageID={@$languageID}&amp;tagID={@$tagID}{@SID_ARG_2ND}">
						{lang}wbb.board.threads.lastPost{/lang}{if $sortField == 'lastPostTime'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
					</a></div>
				</th>
			</tr>
		</thead>
		<tbody>
			{cycle values='container-1,container-2' name='className' print=false advance=false}
			{foreach from=$threads item=thread}
				{assign var=threadID value=$thread->threadID}
				{if $thread->isDeleted && !$board->getModeratorPermission('canReadDeletedThread')}
					<tr class="{cycle name='className'}">
						{if $permissions.canMarkThread}
							<td class="columnMark">
								<label></label>
							</td>
						{/if}
						<td class="columnIcon">
							<img src="{icon}{@$thread->getIconName()}M.png{/icon}" alt="" />
						</td>
						<td class="columnTopic" colspan="{if $enableRating}5{else}4{/if}">
							<span>{lang}wbb.board.deletedThread{/lang}</span>
						</td>
					</tr>
				{else}
					<tr class="{cycle name='className'}" id="threadRow{@$thread->threadID}">
						{if $permissions.canMarkThread}
							<td class="columnMarkTopics">
								<label><input id="threadMark{@$thread->threadID}" type="checkbox" /></label>
							</td>
						{/if}
						<td class="columnIcon">
							{if $permissions.canHandleThread || $permissions.canHandlePost}
								{cycle name='className' print=false}
								<script type="text/javascript">
									//<![CDATA[
									threadData.set({@$thread->threadID}, {
										'isMarked': {@$thread->isMarked()},
										'isDeleted': {@$thread->isDeleted},
										'isDisabled': {@$thread->isDisabled},
										'isClosed': {@$thread->isClosed},
										'isMoved': {if $thread->movedThreadID}{@$thread->realThreadID}{else}0{/if},
										'isSticky': {@$thread->isSticky},
										'isAnnouncement': {@$thread->isAnnouncement},
										'className': '{cycle name="className"}',
										'prefix': '{$thread->prefix|encodeJS}',
										'isDone': {@$thread->isDone}
									});
									//]]>
								</script>
							{/if}
							<img id="threadEdit{@$thread->threadID}" src="{icon}{@$thread->getIconName()}M.png{/icon}" alt="" {if $thread->isNew()}title="{lang}wbb.thread.markAsReadByDoubleClick{/lang}" {/if}/>
							{if $thread->isNew()}
								<script type="text/javascript">
									//<![CDATA[
									threadMarkAsRead.init({@$thread->threadID});
									//]]>
								</script>
							{/if}
						</td>
						<td class="columnTopic"{if BOARD_THREADS_ENABLE_MESSAGE_PREVIEW && $board->getPermission('canReadThread')} title="{$thread->firstPostPreview}"{/if}>
							<div id="thread{@$thread->threadID}" class="topic{if $thread->isNew()} new{/if}{if $thread->ownPosts || $thread->subscribed} interesting{/if}">
								{if $thread->isNew()}
									<a id="gotoFirstNewPost{@$thread->threadID}" href="index.php?page=Thread&amp;threadID={@$thread->threadID}&amp;action=firstNew{@SID_ARG_2ND}"><img class="goToNewPost" src="{icon}goToFirstNewPostS.png{/icon}" alt="" title="{lang}wbb.index.gotoFirstNewPost{/lang}" /></a>
								{/if}
								
								<p id="threadTitle{@$thread->threadID}">
									<span{if $thread->boardID == $board->boardID} id="threadPrefix{@$thread->threadID}"{/if} class="prefix"><strong>{lang}{$thread->prefix}{/lang}</strong></span>
									<a href="index.php?page=Thread&amp;threadID={@$thread->threadID}{@SID_ARG_2ND}">{$thread->topic}</a>
								</p>
							</div>
							<div class="statusDisplay">
								{if BOARD_THREADS_ENABLE_SMALL_PAGES}{smallpages pages=$thread->getPages($board) link="index.php?page=Thread&threadID=$threadID&pageNo=%d"|concat:SID_ARG_2ND_NOT_ENCODED}{/if}
								<div class="statusDisplayIcons">
									{if $additionalSmallPages.$threadID|isset}{@$additionalSmallPages.$threadID}{/if}
									{if $thread->subscribed}<img src="{icon}subscribedS.png{/icon}" alt="" title="{lang}wbb.board.threads.subscribed{/lang}" />{/if}
									{if $thread->ownPosts}<img src="{icon}userS.png{/icon}" alt="" title="{lang}wbb.board.threads.ownPosts{/lang}" />{/if}
									{if $thread->polls}<img src="{icon}pollS.png{/icon}" alt="" title="{lang}wbb.board.threads.polls{/lang}" />{/if}
									{if MODULE_ATTACHMENT && $thread->attachments}<img src="{icon}attachmentS.png{/icon}" alt="" title="{lang}wbb.board.threads.attachments{/lang}" />{/if}
									{if BOARD_THREADS_ENABLE_LANGUAGE_FLAG && $thread->languageID}{@$thread->getLanguageIcon()}{/if}
									{if MODULE_THREAD_MARKING_AS_DONE == 1 && $board->enableMarkingAsDone == 1}
										{if $thread->isDone}<img id="threadMarking{@$thread->threadID}" src="{icon}doneS.png{/icon}" alt="" title="{lang}wbb.board.threads.done{/lang}" />{else}<img id="threadMarking{@$thread->threadID}" src="{icon}undoneS.png{/icon}" alt="" title="{lang}wbb.board.threads.undone{/lang}" />{/if}
									{/if}
								</div>
							</div>
							<p class="firstPost light">
								{lang}wbb.board.threads.postBy{/lang}
								{if $thread->userID}
									<a href="index.php?page=User&amp;userID={@$thread->userID}{@SID_ARG_2ND}">{$thread->username}</a>
								{else}
									{$thread->username}
								{/if}
								({@$thread->time|shorttime})
							</p>
														
							{if $thread->isDeleted}
								<p class="deleteNote smallFont">{lang}wbb.board.threads.deleteNote{/lang}</p>
							{/if}
						</td>
						{if $enableRating}
							<td class="columnRating">{@$thread->getRatingOutput()}</td>
						{/if}
						<td class="columnReplies{if $thread->replies >= BOARD_THREADS_REPLIES_HOT} hot{/if}">{#$thread->replies}</td>
						<td class="columnViews{if $thread->views > BOARD_THREADS_VIEWS_HOT} hot{/if}">{#$thread->views}</td>
						<td class="columnLastPost">
							{if $thread->replies != 0}
								<div class="containerIconSmall">
									<a href="index.php?page=Thread&amp;threadID={@$thread->threadID}&amp;action=lastPost{@SID_ARG_2ND}"><img src="{icon}goToLastPostS.png{/icon}" alt="" title="{lang}wbb.index.gotoLastPost{/lang}" /></a>
								</div>
								<div class="containerContentSmall">
									<p>{lang}wbb.board.threads.postBy{/lang} {if $thread->lastPosterID}<a href="index.php?page=User&amp;userID={@$thread->lastPosterID}{@SID_ARG_2ND}">{$thread->lastPoster}</a>{else}{$thread->lastPoster}{/if}</p>
									<p class="smallFont light">({@$thread->lastPostTime|shorttime})</p>
								</div>
							{else}
								<p class="smallFont light">{lang}wbb.board.threads.noReply{/lang}</p>
							{/if}
						</td>
					</tr>
				{/if}
			{/foreach}
	
		</tbody>
	</table>
</div>

<script type="text/javascript">
	//<![CDATA[
	initList('{$listName}', {@$listStatus});
	//]]>
</script>