<ul class="breadCrumbs">
	{if !$hideRoot|isset}
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
	{/if}
	
	{foreach from=$board->getParentBoards() item=parentBoard}
		<li><a href="index.php?page=Board&amp;boardID={@$parentBoard->boardID}{@SID_ARG_2ND}"><img src="{icon}{@$parentBoard->getIconName()}S.png{/icon}" alt="" /> <span>{lang}{$parentBoard->title}{/lang}</span></a> &raquo;</li>
	{/foreach}
	
	{if $showBoard|isset || $showThread|isset}
		<li><a href="index.php?page=Board&amp;boardID={@$board->boardID}{@SID_ARG_2ND}"><img src="{icon}{@$board->getIconName()}S.png{/icon}" alt="" /> <span>{lang}{$board->title}{/lang}</span></a> &raquo;</li>
	{/if}
	
	{if $showThread|isset}
		<li><a href="index.php?page=Thread&amp;threadID={@$thread->threadID}{@SID_ARG_2ND}"><img src="{icon}threadS.png{/icon}" alt="" /> {if $thread->prefix}<span class="prefix"><strong>{lang}{$thread->prefix}{/lang}</strong></span> {/if}<span>{$thread->topic}</span></a> &raquo;</li>
	{/if}
</ul>