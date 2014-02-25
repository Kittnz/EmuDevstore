<div class="contentBox">
	<h3 class="subHeadline">{lang}wcf.tagging.taggable.com.woltlab.wbb.thread{/lang} <span>({#$items})</span></h3>
	
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
		
			{foreach from=$taggedObjects item=thread}
				{assign var=threadID value=$thread->threadID}
			
				<tr class="{cycle values='container-1,container-2'}">
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
								<a href="index.php?page=Thread&amp;threadID={@$thread->threadID}{@SID_ARG_2ND}">{@$thread->topic}</a>
							</div>
						</div>
						
						<div class="statusDisplay">
							{if BOARD_THREADS_ENABLE_SMALL_PAGES}{smallpages pages=$thread->getPages() link="index.php?page=Thread&pageNo=%d&threadID="|concat:$thread->threadID:SID_ARG_2ND_NOT_ENCODED}{/if}
							<div class="statusDisplayIcons">
								{if $additionalSmallPages.$threadID|isset}{@$additionalSmallPages.$threadID}{/if}
								{if $thread->subscribed}<img src="{icon}subscribedS.png{/icon}" alt="" title="{lang}wbb.board.threads.subscribed{/lang}" />{/if}
								{if $thread->polls}<img src="{icon}pollS.png{/icon}" alt="" title="{lang}wbb.board.threads.polls{/lang}" />{/if}
								{if MODULE_ATTACHMENT && $thread->attachments}<img src="{icon}attachmentS.png{/icon}" alt="" title="{lang}wbb.board.threads.attachments{/lang}" />{/if}
								{if $thread->ownPosts}<img src="{icon}userS.png{/icon}" alt="" title="{lang}wbb.board.threads.ownPosts{/lang}" />{/if}
								{if BOARD_THREADS_ENABLE_LANGUAGE_FLAG && $thread->languageID}{@$thread->getLanguageIcon()}{/if}
							</div>
						</div>
			
						<p class="firstPost light">
							{lang}wbb.board.threads.postBy{/lang}
							{if $thread->userID}
								<a href="index.php?page=User&amp;userID={@$thread->userID}{@SID_ARG_2ND}">{$thread->username}</a>
							{else} {$thread->username} {/if} 
							({@$thread->time|shorttime}) 
							{lang}wcf.search.results.location{/lang} <a href="index.php?page=Board&amp;boardID={@$thread->boardID}{@SID_ARG_2ND}">{lang}{$thread->title}{/lang}</a>
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
</div>