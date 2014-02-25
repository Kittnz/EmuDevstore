<div class="message content">
	<div class="messageInner container-{cycle name='results' values='1,2'}">
		<div class="messageHeader">
			<div class="containerIcon">
				<a href="index.php?page=PMView&amp;pmID={@$item.message->pmID}&amp;folderID={@$item.message->folderID}&amp;highlight={$query|urlencode}{@SID_ARG_2ND}"><img src="{icon}pmUnreadM.png{/icon}" alt="" /></a>
			</div>
			<div class="containerContent">
				<p class="light smallFont">{@$item.message->time|time}</p>
				<p class="light smallFont">{lang}wcf.pm.search.result.sender{/lang}</p>
				{if $item.message->getRecipients()|count > 0}
					<p class="light smallFont">{lang}wcf.pm.recipientList{/lang}
					{implode from=$item.message->getRecipients() item=recipient}<a href="index.php?page=User&amp;userID={@$recipient->recipientID}{@SID_ARG_2ND}">{$recipient->recipient}</a>{/implode}
					</p>
				{/if}
			</div>
		</div>
		
		<h3><a href="index.php?page=PMView&amp;pmID={@$item.message->pmID}&amp;folderID={@$item.message->folderID}&amp;highlight={$query|urlencode}{@SID_ARG_2ND}#pm{@$item.message->pmID}">{$item.message->subject}</a></h3>
		
		<div class="messageBody">
			{@$item.message->getFormattedMessage()}
		</div>
		
		<div class="messageFooter">
			<ul class="breadCrumbs light">
				<li><img src="{icon}pmFolderS.png{/icon}" alt="" /> <a href="index.php?page=PMList&amp;folderID={@$item.message->folderID}{@SID_ARG_2ND}">{$item.message->folderName}</a> </li>
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

