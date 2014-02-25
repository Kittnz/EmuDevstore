<div class="contentBox">
	<h3 class="subHeadline">{lang}wbb.poll.threads{/lang}</h3>
	<ul class="itemList">
		{foreach from=$polls item=poll}
			<li>
				{if $poll->pollID == $pollID}
					<h4 class="itemListTitle">
						{if $poll->voted}<img src="{icon}pollVotedS.png{/icon}" title="{lang}wcf.poll.voted{/lang}" alt="" />{else}<img src="{icon}pollNotVotedS.png{/icon}" title="{lang}wcf.poll.notVoted{/lang}" alt="" />{/if}
						{$poll->question}
					</h4>
				{else}
					<h4 class="itemListTitle">
						{if $poll->voted}<img src="{icon}pollVotedS.png{/icon}" title="{lang}wcf.poll.voted{/lang}" alt="" />{else}<img src="{icon}pollNotVotedS.png{/icon}" title="{lang}wcf.poll.notVoted{/lang}" alt="" />{/if}
						<a href="index.php?page=PollOverview&amp;pollID={@$poll->pollID}{@SID_ARG_2ND}">{$poll->question}</a>
					</h4>
				{/if}
			</li>
		{/foreach}
	</ul>
</div>