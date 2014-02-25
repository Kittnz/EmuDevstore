{include file="documentHeader"}
<head>
	<title>{lang}wbb.user.subscriptions.title{/lang} {if $pageNo > 1}- {lang}wcf.page.pageNo{/lang} {/if}- {lang}wcf.user.usercp{/lang} - {PAGE_TITLE}</title>
	
	{include file='headInclude' sandbox=false}
	
	{if $threads|count > 0}
		<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
		<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/InlineListEdit.class.js"></script>
		<script type="text/javascript" src="{@RELATIVE_WBB_DIR}js/SubscribedThreadListEdit.class.js"></script>
		<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/StringUtil.class.js"></script>
		<script type="text/javascript" src="{@RELATIVE_WBB_DIR}js/ThreadMarkAsRead.class.js"></script>
		<script type="text/javascript">
			//<![CDATA[
			// data array
			var threadData = new Hash();
			var url = 'index.php?page=SubscriptionsList&pageNo={@$pageNo}&sortField={@$sortField}&sortOrder={@$sortOrder}&daysPrune={@$daysPrune}{@SID_ARG_2ND_NOT_ENCODED}';
				
			// language
			var language = new Object();
			language['wcf.global.button.mark']			= '{lang}wcf.global.button.mark{/lang}';
			language['wcf.global.button.unmark'] 			= '{lang}wcf.global.button.unmark{/lang}';
			language['wbb.user.subscriptions.unsubscribe']		= '{lang}wbb.user.subscriptions.unsubscribe{/lang}';
			language['wbb.user.subscriptions.markedThreads'] 	= '{lang}wbb.user.subscriptions.markedThreads{/lang}';
			
			// init
			onloadEvents.push(function() { threadListEdit = new SubscribedThreadListEdit(threadData, {@$markedThreads}); });
			//]]>
		</script>
	{/if}
	
	<link rel="alternate" type="application/rss+xml" href="index.php?page=SubscribedThreadsFeed&amp;format=rss2" title="{lang}wbb.user.subscriptions.feed{/lang} (RSS2)" />
	<link rel="alternate" type="application/atom+xml" href="index.php?page=SubscribedThreadsFeed&amp;format=atom" title="{lang}wbb.user.subscriptions.feed{/lang} (Atom)" />
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	{include file="userCPHeader"}
	
	<div class="border tabMenuContent">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wbb.user.subscriptions.title{/lang}</h3>
			
			{if $boards|count == 0 && $allItems == 0}
				<p>{lang}wbb.user.subscriptions.noSubscriptions{/lang}</p>
			{/if}
			
			{if $boards|count > 0}
				<div class="border titleBarPanel">
					<div class="containerHead">
						<span>{lang}wbb.user.subscriptions.boards{/lang}</span>
					</div>
					<ul id="boardlist" style="margin-bottom: 0">
						{foreach from=$boards item=board}
							{assign var="boardID" value=$board.boardID}
							<li>
								<div class="boardlistInner container-{cycle values='1,2'}"{if $board.imageShowAsBackground}{if $board.image || $unreadThreadsCount.$boardID|isset && $board.imageNew} style="background-image: url({if $unreadThreadsCount.$boardID|isset && $board.imageNew}{$board.imageNew}{else}{$board.image}{/if}); background-repeat: {$board.imageBackgroundRepeat}"{/if}{/if}>
									<div class="boardlistTitle{if BOARD_LIST_ENABLE_LAST_POST && BOARD_LIST_ENABLE_STATS} boardlistCols-3{else}{if BOARD_LIST_ENABLE_LAST_POST || BOARD_LIST_ENABLE_STATS} boardlistCols-2{/if}{/if}">
										<div class="containerIcon">
											<img src="{if $unreadThreadsCount.$boardID|isset && $board.imageNew && !$board.imageShowAsBackground}{$board.imageNew}{elseif $board.image && !$board.imageShowAsBackground}{$board.image}{else}{icon}board{if $unreadThreadsCount.$boardID|isset}New{/if}M.png{/icon}{/if}" alt="" />
										</div>
										
										<div class="containerContent">
											<h4>
												<a href="index.php?page=Board&amp;boardID={@$board.boardID}{@SID_ARG_2ND}"{if $unreadThreadsCount.$boardID|isset} class="new"{/if}>{lang}{$board.title}{/lang}{if $unreadThreadsCount.$boardID|isset}<span> ({#$unreadThreadsCount.$boardID})</span>{/if}</a>
											</h4>
											
											{if $board.description}
												<p class="boardlistDescription">
													{lang}{if $board.allowDescriptionHtml}{@$board.description}{else}{$board.description}{/if}{/lang}
												</p>
											{/if}
										</div>
									</div>
										
									{if $boardStats.$boardID|isset}
										<div class="boardlistStats">
											<dl>
												<dt>{lang}wbb.board.stats.threads{/lang}</dt>
												<dd>{#$boardStats[$boardID]['threads']}</dd>
												<dt>{lang}wbb.board.stats.posts{/lang}</dt>
												<dd>{#$boardStats[$boardID]['posts']}</dd>
											</dl>
										</div>
									{/if}
									
									{if $lastPosts.$boardID|isset}
										<div class="boardlistLastPost">								
											<div class="containerIconSmall"><a href="index.php?page=Thread&amp;threadID={@$lastPosts.$boardID->threadID}&amp;action=firstNew{@SID_ARG_2ND}"><img src="{icon}goToFirstNewPostS.png{/icon}" alt="" title="{lang}wbb.index.gotoFirstNewPost{/lang}" /></a></div>
											<div class="containerContentSmall">
												<p>
													<span class="prefix"><strong>{lang}{$lastPosts.$boardID->prefix}{/lang}</strong></span>
													<a href="index.php?page=Thread&amp;threadID={@$lastPosts.$boardID->threadID}&amp;action=firstNew{@SID_ARG_2ND}">{$lastPosts.$boardID->topic}</a>
												</p>
												<p>{lang}wbb.board.threads.postBy{/lang}
													{if $lastPosts.$boardID->lastPosterID != 0}
														<a href="index.php?page=User&amp;userID={@$lastPosts.$boardID->lastPosterID}{@SID_ARG_2ND}">{$lastPosts.$boardID->lastPoster}</a>
													{else}
														{$lastPosts.$boardID->lastPoster}
													{/if}
													<span class="light">({@$lastPosts.$boardID->lastPostTime|shorttime})</span>
												</p>
											</div>
										</div>
									{/if}
									
									<div class="smallButtons">
										<ul>
											<li>
												<a href="index.php?page=Subscriptions&amp;action=unsubscribeBoard&amp;boardID={@$board.boardID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" title="{lang}wbb.user.subscriptions.unsubscribe{/lang}"><img src="{icon}unsubscribeS.png{/icon}" alt="" /> <span>{lang}wbb.user.subscriptions.unsubscribe{/lang}</span></a>
											</li>
										</ul>
									</div>
								</div>
							</li>
						{/foreach}
					</ul>
				</div>
				
				{if $boards|count > 1}
					<div class="contentFooter">
						<div class="largeButtons">
							<ul>
								<li><a href="index.php?page=Subscriptions&amp;action=unsubscribeBoards&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" onclick="return confirm('{lang}wbb.user.subscriptions.boards.unsubscribe.sure{/lang}')" title="{lang}wbb.user.subscriptions.boards.unsubscribe{/lang}"><img src="{icon}unsubscribeM.png{/icon}" alt="" /> <span>{lang}wbb.user.subscriptions.boards.unsubscribe{/lang}</span></a></li>
							</ul>
						</div>
					</div>
				{/if}
			{/if}
			
			{assign var=__pageNo value=$pageNo}
			{if $allItems > 0}
				{if $threads|count > 0}
					{pages assign=pagesOutput link="index.php?page=SubscriptionsList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&daysPrune=$daysPrune"|concat:SID_ARG_2ND_NOT_ENCODED}
					
					<div class="border titleBarPanel">
						<div class="containerHead">
							<h4>{lang}wbb.user.subscriptions.threads{/lang}</h4>
						</div>
					</div>
					<div class="border borderMarginRemove">
						<table class="tableList">
							<thead>
								<tr class="tableHead">
									<th class="columnMark">
										<div>
											<label class="emptyHead">
												<input name="threadMarkAll" type="checkbox" />
											</label>
										</div>
									</th>
									
									<th colspan="2" class="columnTopic{if $sortField == 'topic'} active{/if}">
										<div><a href="index.php?page=SubscriptionsList&amp;sortField=topic&amp;sortOrder={if $sortField == 'topic' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}&amp;daysPrune={@$daysPrune}{@SID_ARG_2ND}">
											{lang}wbb.board.threads.topic{/lang}{if $sortField == 'topic'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
										</a></div>
									</th>
									{if THREAD_ENABLE_RATING}
										<th class="columnRating{if $sortField == 'ratingResult'} active{/if}">
											<div><a href="index.php?page=SubscriptionsList&amp;sortField=ratingResult&amp;sortOrder={if $sortField == 'ratingResult' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}&amp;daysPrune={@$daysPrune}{@SID_ARG_2ND}">
												{lang}wbb.board.threads.rating{/lang}{if $sortField == 'ratingResult'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
											</a></div>
										</th>
									{/if}
									<th class="columnReplies{if $sortField == 'replies'} active{/if}">
										<div><a href="index.php?page=SubscriptionsList&amp;sortField=replies&amp;sortOrder={if $sortField == 'replies' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}&amp;daysPrune={@$daysPrune}{@SID_ARG_2ND}">
											{lang}wbb.board.threads.replies{/lang}{if $sortField == 'replies'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
										</a></div>
									</th>
									<th class="columnViews{if $sortField == 'views'} active{/if}">
										<div><a href="index.php?page=SubscriptionsList&amp;sortField=views&amp;sortOrder={if $sortField == 'views' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}&amp;daysPrune={@$daysPrune}{@SID_ARG_2ND}">
											{lang}wbb.board.threads.views{/lang}{if $sortField == 'views'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
										</a></div>
									</th>
									<th class="columnLastPost{if $sortField == 'lastPostTime'} active{/if}">
										<div><a href="index.php?page=SubscriptionsList&amp;sortField=lastPostTime&amp;sortOrder={if $sortField == 'lastPostTime' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}&amp;daysPrune={@$daysPrune}{@SID_ARG_2ND}">
											{lang}wbb.board.threads.lastPost{/lang}{if $sortField == 'lastPostTime'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
										</a></div>
									</th>
								</tr>
							</thead>
							<tbody>
								{foreach from=$threads item=thread}
									{assign var=threadID value=$thread->threadID}
									<tr class="{cycle values='container-1,container-2'}" id="threadRow{@$thread->threadID}">
										<td class="columnMark">
											<label><input id="threadMark{@$thread->threadID}" type="checkbox" /></label>
										</td>
										<td class="columnIcon">
											<img id="threadEdit{@$thread->threadID}" src="{icon}{@$thread->getIconName()}M.png{/icon}" alt="" {if $thread->isNew()}title="{lang}wbb.thread.markAsReadByDoubleClick{/lang}" {/if}/>
											
											<script type="text/javascript">
												//<![CDATA[
												threadData.set({@$thread->threadID}, {
													'isMarked': {@$thread->isMarked()}
												});
												{if $thread->isNew()}
													threadMarkAsRead.init({@$thread->threadID});
												{/if}
												//]]>
											</script>
										</td>
										<td class="columnTopic"{if BOARD_THREADS_ENABLE_MESSAGE_PREVIEW} title="{$thread->firstPostPreview}"{/if}>
											<div id="thread{@$thread->threadID}" class="topic{if $thread->isNew()} new{/if}">
												{if $thread->isNew()}
													<a id="gotoFirstNewPost{@$thread->threadID}" href="index.php?page=Thread&amp;threadID={@$thread->threadID}&amp;action=firstNew{@SID_ARG_2ND}"><img style="float: left;" src="{icon}goToFirstNewPostS.png{/icon}" alt="" title="{lang}wbb.index.gotoFirstNewPost{/lang}" /></a>
												{/if}
												
												<p>
													{if $thread->prefix}
														<span class="prefix"><strong>{lang}{$thread->prefix}{/lang}</strong></span>
													{/if}
													<a href="index.php?page=Thread&amp;threadID={@$thread->threadID}{@SID_ARG_2ND}">{$thread->topic}</a>
												</p>
												
												
											</div>
								
											<div class="statusDisplay">
												{if BOARD_THREADS_ENABLE_SMALL_PAGES}{smallpages pages=$thread->getPages() link="index.php?page=Thread&threadID=$threadID&pageNo=%d"|concat:SID_ARG_2ND_NOT_ENCODED}{/if}
												<div class="statusDisplayIcons">
													{if $additionalSmallPages.$threadID|isset}{@$additionalSmallPages.$threadID}{/if}
													{if $thread->ownPosts}<img src="{icon}userS.png{/icon}" alt="" title="{lang}wbb.board.threads.ownPosts{/lang}" />{/if}
													{if $thread->polls}<img src="{icon}pollS.png{/icon}" alt="" title="{lang}wbb.board.threads.polls{/lang}" />{/if}
													{if MODULE_ATTACHMENT && $thread->attachments}<img src="{icon}attachmentS.png{/icon}" alt="" title="{lang}wbb.board.threads.attachments{/lang}" />{/if}
													{if BOARD_THREADS_ENABLE_LANGUAGE_FLAG && $thread->languageID}{@$thread->getLanguageIcon()}{/if}
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
										</td>
										{if THREAD_ENABLE_RATING}
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
								{/foreach}
							</tbody>
						</table>
					</div>
					
					<div class="contentFooter">
						{@$pagesOutput}
						
						<div id="threadEditMarked" class="optionButtons"></div>
						
						<div class="largeButtons">
							<ul>
								<li><a href="index.php?page=Subscriptions&amp;action=unsubscribeThreads&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" onclick="return confirm('{lang}wbb.user.subscriptions.threads.unsubscribe.sure{/lang}')" title="{lang}wbb.user.subscriptions.threads.unsubscribe{/lang}"><img src="{icon}unsubscribeM.png{/icon}" alt="" /> <span>{lang}wbb.user.subscriptions.threads.unsubscribe{/lang}</span></a></li>
							</ul>
						</div>
					</div>
				{/if}
				
				<div class="border infoBox">
					<div class="container-1 infoBoxSorting">
						<div class="containerIcon"><img src="{icon}sortM.png{/icon}" alt="" /> </div>
						<div class="containerContent">
							<h3>{lang}wbb.board.sorting{/lang}</h3>
							<form method="get" action="index.php">
								<div class="threadSort">
									<input type="hidden" name="page" value="SubscriptionsList" />
									<input type="hidden" name="pageNo" value="{@$__pageNo}" />
									
									<div class="floatedElement">
										<label for="sortField">{lang}wbb.board.sortBy{/lang}</label>
										<select name="sortField" id="sortField">
											<option value="prefix"{if $sortField == 'prefix'} selected="selected"{/if}>{lang}wbb.board.sortBy.prefix{/lang}</option>
											<option value="topic"{if $sortField == 'topic'} selected="selected"{/if}>{lang}wbb.board.sortBy.topic{/lang}</option>
											{if MODULE_ATTACHMENT}<option value="attachments"{if $sortField == 'attachments'} selected="selected"{/if}>{lang}wbb.board.sortBy.attachments{/lang}</option>{/if}
											{if MODULE_POLL}<option value="polls"{if $sortField == 'polls'} selected="selected"{/if}>{lang}wbb.board.sortBy.polls{/lang}</option>{/if}
											<option value="username"{if $sortField == 'username'} selected="selected"{/if}>{lang}wbb.board.sortBy.starter{/lang}</option>
											<option value="time"{if $sortField == 'time'} selected="selected"{/if}>{lang}wbb.board.sortBy.startTime{/lang}</option>
											{if THREAD_ENABLE_RATING}<option value="ratingResult"{if $sortField == 'ratingResult'} selected="selected"{/if}>{lang}wbb.board.sortBy.rating{/lang}</option>{/if}
											<option value="replies"{if $sortField == 'replies'} selected="selected"{/if}>{lang}wbb.board.sortBy.replies{/lang}</option>
											<option value="views"{if $sortField == 'views'} selected="selected"{/if}>{lang}wbb.board.sortBy.views{/lang}</option>
											<option value="lastPostTime"{if $sortField == 'lastPostTime'} selected="selected"{/if}>{lang}wbb.board.sortBy.lastPostTime{/lang}</option>
										</select>
										<select name="sortOrder">
											<option value="ASC"{if $sortOrder == 'ASC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.ascending{/lang}</option>
											<option value="DESC"{if $sortOrder == 'DESC'} selected="selected"{/if}>{lang}wcf.global.sortOrder.descending{/lang}</option>
										</select>
									</div>
									
									<div class="floatedElement">
										<label for="filterDate">{lang}wbb.board.filterByDate{/lang}</label>
										<select name="daysPrune" id="filterDate">
											<option value="1"{if $daysPrune == 1} selected="selected"{/if}>{lang}wbb.board.filterByDate.1{/lang}</option>
											<option value="3"{if $daysPrune == 3} selected="selected"{/if}>{lang}wbb.board.filterByDate.3{/lang}</option>
											<option value="7"{if $daysPrune == 7} selected="selected"{/if}>{lang}wbb.board.filterByDate.7{/lang}</option>
											<option value="14"{if $daysPrune == 14} selected="selected"{/if}>{lang}wbb.board.filterByDate.14{/lang}</option>
											<option value="30"{if $daysPrune == 30} selected="selected"{/if}>{lang}wbb.board.filterByDate.30{/lang}</option>
											<option value="60"{if $daysPrune == 60} selected="selected"{/if}>{lang}wbb.board.filterByDate.60{/lang}</option>
											<option value="100"{if $daysPrune == 100} selected="selected"{/if}>{lang}wbb.board.filterByDate.100{/lang}</option>
											<option value="365"{if $daysPrune == 365} selected="selected"{/if}>{lang}wbb.board.filterByDate.365{/lang}</option>
											<option value="1000"{if $daysPrune == 1000} selected="selected"{/if}>{lang}wbb.board.filterByDate.1000{/lang}</option>
										</select>
									</div>
									
									<div class="floatedElement">
										<input type="image" class="inputImage" src="{icon}submitS.png{/icon}" alt="{lang}wcf.global.button.submit{/lang}" />
									</div>
		
									{@SID_INPUT_TAG}
								</div>
							</form>
						</div>
					</div>
					
					{if $additionalBoxes|isset}{@$additionalBoxes}{/if}
				</div>
			{/if}
		</div>
	</div>

</div>

{include file='footer' sandbox=false}
</body>
</html>