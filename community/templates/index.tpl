{include file="documentHeader"}
<head>
	<title>{lang}wbb.index.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	
	{include file='headInclude' sandbox=false}
	<link rel="alternate" type="application/rss+xml" href="index.php?page=ThreadsFeed&amp;format=rss2" title="{lang}wbb.index.feed{/lang} (RSS2)" />
	<link rel="alternate" type="application/atom+xml" href="index.php?page=ThreadsFeed&amp;format=atom" title="{lang}wbb.index.feed{/lang} (Atom)" />
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	<div class="mainHeadline">
		<img src="{icon}indexL.png{/icon}" alt="" ondblclick="document.location.href=fixURL('index.php?action=BoardMarkAllAsRead&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}')" title="{lang}wbb.index.markAsRead{/lang}" />
		<div class="headlineContainer">
			<h2>{lang}{PAGE_TITLE}{/lang}</h2>
			<p>{lang}{PAGE_DESCRIPTION}{/lang}</p>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{if $additionalTopContents|isset}{@$additionalTopContents}{/if}
	
	{include file="boardList"}
	
	{if $usersOnlineTotal|isset || INDEX_ENABLE_STATS || $additionalBoxes|isset || $tags|count}
		{cycle values='container-1,container-2' print=false advance=false}
		<div class="border infoBox">
			{if $usersOnlineTotal|isset}
				<div class="{cycle} infoBoxUsersOnline">
					<div class="containerIcon"> <img src="{icon}membersM.png{/icon}" alt="" /></div>
					<div class="containerContent">
						<h3>{if $this->user->getPermission('user.usersOnline.canView')}<a href="index.php?page=UsersOnline{@SID_ARG_2ND}">{lang}wbb.index.usersOnline{/lang}</a>{else}{lang}wbb.index.usersOnline{/lang}{/if}</h3> 
						<p class="smallFont">{lang}wbb.index.usersOnline.detail{/lang} {lang}wbb.index.usersOnline.record{/lang}</p>
						{if $usersOnline|count}
							<p class="smallFont">
							{implode from=$usersOnline item=userOnline}<a href="index.php?page=User&amp;userID={@$userOnline.userID}{@SID_ARG_2ND}">{@$userOnline.username}</a>{/implode}
							</p>
							{if INDEX_ENABLE_USERS_ONLINE_LEGEND && $usersOnlineMarkings|count}
								<p class="smallFont">
								{lang}wcf.usersOnline.marking.legend{/lang} {implode from=$usersOnlineMarkings item=usersOnlineMarking}{@$usersOnlineMarking}{/implode}
								</p>
							{/if}
						{/if}
					</div>
				</div>
			{/if}
			
			{if INDEX_ENABLE_STATS}
				<div class="{cycle} infoBoxStatistics">
					<div class="containerIcon"><img src="{icon}statisticsM.png{/icon}" alt="" /></div>
					<div class="containerContent">
						<h3>{lang}wbb.index.stats{/lang}</h3> 
						<p class="smallFont">{lang}wbb.index.stats.detail{/lang}</p>
					</div>
				</div>
			{/if}
			
			{if $additionalBoxes|isset}{@$additionalBoxes}{/if}
			
			{if $tags|count}
				<div class="{cycle} infoBoxTags">
					<div class="containerIcon"><img src="{icon}tagM.png{/icon}" alt="" /></div>
					<div class="containerContent">
						<h3><a href="index.php?page=TaggedObjects{@SID_ARG_2ND}">{lang}wcf.tagging.mostPopular{/lang}</a></h3>
						{include file='tagCloud'}
					</div>
				</div>
			{/if}
		</div>
	{/if}
	<div class="pageOptions">
		{if $additionalPageOptions|isset}{@$additionalPageOptions}{/if}
		<a href="index.php?action=BoardMarkAllAsRead&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}"><img src="{icon}boardMarkAsReadS.png{/icon}" alt="" /> <span>{lang}wbb.index.markAsRead{/lang}</span></a>
	</div>
</div>

{include file='footer' sandbox=false}

</body>
</html>