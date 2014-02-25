{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.team.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
	</ul>
	
	<div class="mainHeadline">
		<img src="{icon}membersL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2>{lang}wcf.user.membersList.title{/lang}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	<div class="tabMenu">
		<ul>
			<li><a href="index.php?page=MembersList{@SID_ARG_2ND}"><img src="{icon}membersM.png{/icon}" alt="" /> <span>{lang}wcf.user.membersList.allMembers{/lang}</span></a></li>
			<li><a href="index.php?form=MembersSearch{@SID_ARG_2ND}"><img src="{icon}membersSearchM.png{/icon}" alt="" /> <span>{lang}wcf.user.membersList.membersSearch{/lang}</span></a></li>
			{if $hasFriends}<li><a href="index.php?page=MyFriendsList{@SID_ARG_2ND}"><img src="{icon}friendsM.png{/icon}" alt="" /> <span>{lang}wcf.user.membersList.myFriends{/lang}</span></a></li>{/if}
			<li class="activeTabMenu"><a href="index.php?page=Team{@SID_ARG_2ND}"><img src="{icon}teamM.png{/icon}" alt="" /> <span>{lang}wcf.user.team.title{/lang}</span></a></li>
			{if $additionalTabs|isset}{@$additionalTabs}{/if}
		</ul>
	</div>
	
	<div class="subTabMenu">
		<div class="containerHead"></div>
	</div>
	<div class="border tabMenuContent">
		<div class="container-1">
			{if $members|count > 0}
				{assign var='counter' value=0}
				{foreach from=$members item=group}
					<div class="contentBox">
						<h3 class="subHeadline">{lang}{$group.groupName}{/lang}</h3>
						<div class="border">
							<table class="tableList membersList">
								<thead>
									<tr class="tableHead">
										{foreach from=$header item=field}
											{if $field.sortable}
												<th class="column{$field.field|ucfirst}{if $sortField == $field.field} active{/if}"><p><a href="index.php?page=Team&amp;sortField={$field.field}&amp;sortOrder={if $sortField == $field.field && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}{@$field.name}{/lang}{if $sortField == $field.field} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}</a></p></th>
											{else}
												<th class="column{$field.field|ucfirst}"><p><span class="emptyHead">{lang}{@$field.name}{/lang}</span></p></th>
											{/if}
										{/foreach}
									</tr>
								</thead>
								<tbody>
								{if $group.leaders|count > 0}
									<tr>
										<td  class="containerHead" colspan="{@$header|count}"><h4>{lang}wcf.user.team.leaders{/lang}</h4></td>
									</tr>
								
									{foreach from=$group.leaders item=member}
										<tr class="container-{cycle values='1,2'}">
											{foreach from=$fields item=field}
												<td class="column{$field|ucfirst}"><p>{@$member.$field}</p></td>
											{/foreach}
										</tr>
									{/foreach}
									
									{if $group.members|count > 0}
										<tr class="containerHead">
											<td colspan="{@$header|count}"><h4>{lang}wcf.user.team.members{/lang}</h4></td>
										</tr>
									{/if}
								{/if}
								
								{foreach from=$group.members item=member}
									<tr class="container-{cycle values='1,2'}">
										{foreach from=$fields item=field}
											<td class="column{$field|ucfirst}"><p>{@$member.$field}</p></td>
										{/foreach}
									</tr>
								{/foreach}
								</tbody>
							</table>
						</div>
					</div>	
				{/foreach}
			{else}
				{lang}wcf.user.team.error.noMembers{/lang}	
			{/if}
		</div>
	</div>
</div>

{include file='footer' sandbox=false}
</body>
</html>