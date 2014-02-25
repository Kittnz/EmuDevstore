{if $findThreads|isset}
	{if $i == 0}
		<script type="text/javascript" src="{@RELATIVE_WBB_DIR}js/ThreadMarkAsRead.class.js"></script>
		<div class="border">
			<table class="tableList">
				<thead>
					<tr class="tableHead">
						<th colspan="2" class="columnTopic"><div><span class="emptyHead">{lang}wbb.board.threads.topic{/lang}</span></div></th>
						{if THREAD_ENABLE_RATING}
							<th class="columnRating"><div><span class="emptyHead">{lang}wbb.board.threads.rating{/lang}</span></div></th>
						{/if}
						<th class="columnReplies"><div><span class="emptyHead">{lang}wbb.board.threads.replies{/lang}</span></div></th>
						<th class="columnViews"><div><span class="emptyHead">{lang}wbb.board.threads.views{/lang}</span></div></th>
						<th class="columnLastPost"><div><span class="emptyHead">{lang}wbb.board.threads.lastPost{/lang}</span></div></th>
					</tr>
				</thead>
				<tbody>
	{/if}

	{assign var=thread value=$item.message}
	{assign var=threadID value=$thread->threadID}
	{if SEARCH_RESULT_GROUP_BY_BOARD && $item.board|isset}
		<tr>
			<td colspan="{if THREAD_ENABLE_RATING}6{else}5{/if}" class="containerHead">
				{include file="navigation" showBoard=true hideRoot=true board=$item.board}
			</td>
		</tr>
	{/if}

	<tr class="container-{cycle name='results'}">
		<td class="columnIcon">
			<img id="threadEdit{@$thread->threadID}" src="{icon}{@$thread->getIconName()}M.png{/icon}" alt="" {if $thread->isNew()}title="{lang}wbb.thread.markAsReadByDoubleClick{/lang}" {/if}/>
			{if $thread->isNew()}
				<script type="text/javascript">
					//<![CDATA[
					threadMarkAsRead.init({@$thread->threadID});
					//]]>
				</script>
			{/if}
		</td>
		<td class="columnTopic"{if BOARD_THREADS_ENABLE_MESSAGE_PREVIEW} title="{$thread->firstPostPreview}"{/if}>
			<div id="thread{@$thread->threadID}" class="topic{if $thread->isNew()} new{/if}{if $thread->ownPosts || $thread->subscribed} interesting{/if}">
				{if $thread->isNew()}
					<a id="gotoFirstNewPost{@$thread->threadID}" href="index.php?page=Thread&amp;threadID={@$thread->threadID}&amp;action=firstNew{@SID_ARG_2ND}"><img class="goToNewPost" src="{icon}goToFirstNewPostS.png{/icon}" alt="" title="{lang}wbb.index.gotoFirstNewPost{/lang}" /></a>
				{/if}
				
				<div>
					{if $thread->prefix}
						<span class="prefix"><strong>{lang}{$thread->prefix}{/lang}</strong></span>
					{/if}
					<a href="index.php?page=Thread&amp;threadID={@$thread->threadID}&amp;highlight={$query|urlencode}{@SID_ARG_2ND}">{@$thread->getHighlightedTopic()}</a>
				</div>
			</div>
			
			<div class="statusDisplay">
				{if BOARD_THREADS_ENABLE_SMALL_PAGES}{smallpages pages=$thread->getPages() link="index.php?page=Thread&threadID=$threadID&pageNo=%d"|concat:SID_ARG_2ND_NOT_ENCODED}{/if}
				<div class="statusDisplayIcons">
					{if $additionalSmallPages.$threadID|isset}{@$additionalSmallPages.$threadID}{/if}
					{if $thread->subscribed}<img src="{icon}subscribedS.png{/icon}" alt="" title="{lang}wbb.board.threads.subscribed{/lang}" />{/if}
					{if $thread->polls}<img src="{icon}pollS.png{/icon}" alt="" title="{lang}wbb.board.threads.polls{/lang}" />{/if}
					{if MODULE_ATTACHMENT && $thread->attachments}<img src="{icon}attachmentS.png{/icon}" alt="" title="{lang}wbb.board.threads.attachments{/lang}" />{/if}
					{if $thread->ownPosts}<img src="{icon}userS.png{/icon}" alt="" title="{lang}wbb.board.threads.ownPosts{/lang}" />{/if}
					{if BOARD_THREADS_ENABLE_LANGUAGE_FLAG && $thread->languageID}{@$thread->getLanguageIcon()}{/if}
					{if MODULE_THREAD_MARKING_AS_DONE == 1 && $thread->enableMarkingAsDone == 1}
						{if $thread->isDone}<img src="{icon}doneS.png{/icon}" alt="" title="{lang}wbb.board.threads.done{/lang}" />{else}<img src="{icon}undoneS.png{/icon}" alt="" title="{lang}wbb.board.threads.undone{/lang}" />{/if}
					{/if}
				</div>
			</div>
			
			<p class="firstPost light">
				{lang}wbb.board.threads.postBy{/lang}
				{if $thread->userID}
					<a href="index.php?page=User&amp;userID={@$thread->userID}{@SID_ARG_2ND}">{$thread->username}</a>
				{else} {$thread->username} {/if} 
				({@$thread->time|shorttime}) 
				{if !SEARCH_RESULT_GROUP_BY_BOARD}- {lang}wcf.search.results.location{/lang} <a href="index.php?page=Board&amp;boardID={@$thread->boardID}{@SID_ARG_2ND}">{lang}{$thread->title}{/lang}</a>{/if}
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

	{if $i == $length - 1}
				</tbody>
			</table>
		</div>
	
	{/if}
	
{else}	
	{* show results as posts *}
	<div class="message content">
		<div class="messageInner container-{cycle name='results'}">
			<div class="messageHeader">
				<div class="containerIcon">
					<a href="index.php?page=Thread&amp;postID={@$item.message->postID}&amp;highlight={$query|urlencode}{@SID_ARG_2ND}#post{@$item.message->postID}"><img src="{icon}postM.png{/icon}" alt="" /></a>
				</div>
				<div class="containerContent">
					<p class="light smallFont">{@$item.message->time|time}</p>
					<p class="light smallFont">{lang}wbb.search.result.author{/lang}</p>
				</div>
			</div>
			<h3><a href="index.php?page=Thread&amp;postID={@$item.message->postID}&amp;highlight={$query|urlencode}{@SID_ARG_2ND}#post{@$item.message->postID}">{$item.message->subject}</a></h3>
			<div class="messageBody">
				{@$item.message->getFormattedMessage()}
			</div>
			<div class="messageFooter">
				<ul class="breadCrumbs light">
					<li><img src="{icon}boardS.png{/icon}" alt="" /> <a href="index.php?page=Board&amp;boardID={@$item.message->boardID}{@SID_ARG_2ND}">{lang}{$item.message->title}{/lang}</a> &raquo; </li>
					<li><img src="{icon}threadS.png{/icon}" alt="" /> {if $item.message->prefix}<span class="prefix"><strong>{lang}{$item.message->prefix}{/lang}</strong></span> {/if}<a href="index.php?page=Thread&amp;threadID={@$item.message->threadID}&amp;highlight={$query|urlencode}{@SID_ARG_2ND}">{$item.message->topic}</a></li>
				</ul>
				<div class="smallButtons">
					<ul>
						<li class="extraButton"><a href="#top" title="{lang}wcf.global.scrollUp{/lang}"><img src="{icon}upS.png{/icon}" alt="" /> <span class="hidden">{lang}wcf.global.scrollUp{/lang}</span></a></li>
					</ul>
				</div>
				
			</div>
			<hr />
		</div>
	</div>
{/if}