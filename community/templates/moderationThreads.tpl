{include file="documentHeader"}
<head>
	<title>{lang}wbb.moderation.{@$action}.title{/lang} {if $pageNo > 1}- {lang}wcf.page.pageNo{/lang} {/if}- {lang}wcf.user.usercp{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	{include file="userCPHeader"}
	
	{if $threads|count == 0}
		<div class="border tabMenuContent">
			<div class="container-1">
				<h3 class="subHeadline">{lang}wbb.moderation.{@$action}.title{/lang}</h3>
				<p>{lang}wbb.moderation.{@$action}.noPosts{/lang}</p>
			</div>
		</div>
	{else}
		<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
		
		{pages print=false assign=pagesOutput link="index.php?page=$page&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"|concat:SID_ARG_2ND_NOT_ENCODED}
		
		<script type="text/javascript">
			//<![CDATA[
			var language = new Object();
			var postData = new Hash();
			var url = 'index.php?page={@$page}&pageNo={@$pageNo}&sortField={@$sortField}&sortOrder={@$sortOrder}{@SID_ARG_2ND_NOT_ENCODED}';
			//]]>
		</script>
		{include file='threadInlineEdit' pageType=$action}
		
		<div class="border tabMenuContent">
			<div class="container-1">
				<h3 class="subHeadline">{lang}wbb.moderation.{@$action}.title{/lang}</h3>
				<div class="border">
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
									<div><a href="index.php?page={@$page}&amp;sortField=topic&amp;sortOrder={if $sortField == 'topic' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{@SID_ARG_2ND}">
										{lang}wbb.board.threads.topic{/lang}{if $sortField == 'topic'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
									</a></div>
								</th>
								{if THREAD_ENABLE_RATING}
									<th class="columnRating{if $sortField == 'ratingResult'} active{/if}">
										<div><a href="index.php?page={@$page}&amp;sortField=ratingResult&amp;sortOrder={if $sortField == 'ratingResult' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{@SID_ARG_2ND}">
											{lang}wbb.board.threads.rating{/lang}{if $sortField == 'ratingResult'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
										</a></div>
									</th>
								{/if}
								<th class="columnReplies{if $sortField == 'replies'} active{/if}">
									<div><a href="index.php?page={@$page}&amp;sortField=replies&amp;sortOrder={if $sortField == 'replies' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{@SID_ARG_2ND}">
										{lang}wbb.board.threads.replies{/lang}{if $sortField == 'replies'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
									</a></div>
								</th>
								<th class="columnViews{if $sortField == 'views'} active{/if}">
									<div><a href="index.php?page={@$page}&amp;sortField=views&amp;sortOrder={if $sortField == 'views' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{@SID_ARG_2ND}">
										{lang}wbb.board.threads.views{/lang}{if $sortField == 'views'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
									</a></div>
								</th>
								<th class="columnLastPost{if $sortField == 'lastPostTime'} active{/if}">
									<div><a href="index.php?page={@$page}&amp;sortField=lastPostTime&amp;sortOrder={if $sortField == 'lastPostTime' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{@SID_ARG_2ND}">
										{lang}wbb.board.threads.lastPost{/lang}{if $sortField == 'lastPostTime'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
									</a></div>
								</th>
							</tr>
						</thead>
						<tbody>
							{cycle values='container-1,container-2' name='className' print=false advance=false}
							{foreach from=$threads item=thread}
								{assign var=threadID value=$thread->threadID}
								<tr class="{cycle name='className'}" id="threadRow{@$thread->threadID}">
									<td class="columnMark">
										<label><input id="threadMark{@$thread->threadID}" type="checkbox" /></label>
									</td>
									<td class="columnIcon">
										<img id="threadEdit{@$thread->threadID}" src="{icon}{@$thread->getIconName()}M.png{/icon}" alt="" />
										
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
												'className': '{cycle name="className" advance=false}',
												'prefix': '{$thread->prefix|encodeJS}'
											});
											//]]>
										</script>
									</td>
									<td class="columnTopic"{if BOARD_THREADS_ENABLE_MESSAGE_PREVIEW} title="{$thread->firstPostPreview}"{/if}>
										<div id="thread{@$thread->threadID}" class="topic{if $thread->isNew()} new{/if}">
											{if $thread->isNew()}
												<a id="gotoFirstNewPost{@$thread->threadID}" href="index.php?page=Thread&amp;threadID={@$thread->threadID}&amp;action=firstNew{@SID_ARG_2ND}"><img style="float: left;" src="{icon}goToFirstNewPostS.png{/icon}" alt="" title="{lang}wbb.index.gotoFirstNewPost{/lang}" /></a>
											{/if}
											
											<h4 id="threadTitle{@$thread->threadID}" class="messageHeadingTable">
												{if $thread->prefix}<span class="prefix"><strong>{lang}{$thread->prefix}{/lang}</strong></span>{/if}
												<a href="index.php?page=Thread&amp;threadID={@$thread->threadID}{@SID_ARG_2ND}">{$thread->topic}</a>
											</h4>
										</div>
										
										<div class="statusDisplay">
											{if BOARD_THREADS_ENABLE_SMALL_PAGES}{smallpages pages=$thread->getPages() link="index.php?page=Thread&threadID=$threadID&pageNo=%d"|concat:SID_ARG_2ND_NOT_ENCODED}{/if}
											<div class="statusDisplayIcons">
												{if $thread->polls}<img src="{icon}pollS.png{/icon}" alt="" title="{lang}wbb.board.threads.polls{/lang}" />{/if}
												{if MODULE_ATTACHMENT && $thread->attachments}<img src="{icon}attachmentS.png{/icon}" alt="" title="{lang}wbb.board.threads.attachments{/lang}" />{/if}
												{if $thread->ownPosts}<img src="{icon}userS.png{/icon}" alt="" title="{lang}wbb.board.threads.ownPosts{/lang}" />{/if}
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
					
					{if $action == 'deletedThreads'}
						<div class="largeButtons">
							<ul>
								<li><a href="index.php?action=EmptyThreadRecycleBin&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" onclick="return confirm('{lang}wbb.moderation.emptyRecycleBin.sure{/lang}')" title="{lang}wbb.moderation.emptyRecycleBin{/lang}"><img src="{icon}emptyRecycleBinM.png{/icon}" alt="" /> <span>{lang}wbb.moderation.emptyRecycleBin{/lang}</span></a></li>
							</ul>
						</div>
					{/if}
				</div>
			</div>
		</div>
		
	{/if}
	
</div>

{include file='footer' sandbox=false}
</body>
</html>