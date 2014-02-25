{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.membersList.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{* --- quick search controls --- *}
{assign var='searchScript' value='index.php?form=MembersSearch'}
{assign var='searchFieldName' value='staticParameters[username]'}
{if $staticParameters.username|isset}{assign var='searchFieldValue' value=$staticParameters.username}{/if}
{assign var='searchFieldTitle' value='{lang}wcf.user.membersList.search.query{/lang}'}
{assign var='searchExtendedLink' value='index.php?form=MembersSearch'|concat:SID_ARG_2ND}
{* --- end --- *}
{include file='header' sandbox=false}

<div id="main">
	
	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
	</ul>
	
	<div class="mainHeadline">
		<img src="{icon}membersL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2>{lang}wcf.user.membersList.title{/lang}</h2>
			<p>{lang}wcf.user.membersList.description{/lang}</p>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	<div class="tabMenu">
		<ul>
			<li{if !$searchID} class="activeTabMenu"{/if}><a href="index.php?page=MembersList{@SID_ARG_2ND}"><img src="{icon}membersM.png{/icon}" alt="" /> <span>{lang}wcf.user.membersList.allMembers{/lang}</span></a></li>
			<li{if $searchID} class="activeTabMenu"{/if}><a href="index.php?form=MembersSearch{@SID_ARG_2ND}"><img src="{icon}membersSearchM.png{/icon}" alt="" /> <span>{lang}wcf.user.membersList.membersSearch{/lang}</span></a></li>
			{if $hasFriends}<li><a href="index.php?page=MyFriendsList{@SID_ARG_2ND}"><img src="{icon}friendsM.png{/icon}" alt="" /> <span>{lang}wcf.user.membersList.myFriends{/lang}</span></a></li>{/if}
			{if $additionalTabs|isset}{@$additionalTabs}{/if}
		</ul>
	</div>
	<div class="subTabMenu">
		<div class="containerHead">
			<ul>
				<li{if $letter|empty} class="activeSubTabMenu"{/if}><a href="index.php?page=MembersList&amp;searchID={@$searchID}{@SID_ARG_2ND}"><span>{lang}wcf.user.membersList.all{/lang}</span></a></li>
				{foreach from=$letters item=letterItem}
					<li{if $letterItem|rawurlencode == $letter} class="activeSubTabMenu"{/if}><a href="index.php?page=MembersList&amp;letter={@$letterItem|rawurlencode}{if $searchID}&amp;searchID={@$searchID}{/if}{@SID_ARG_2ND}"><span>{$letterItem}</span></a></li>
				{/foreach}
			</ul>
		</div>
	</div>
	
	<div class="border tabMenuContent">
		{if $members|count > 0}
			<table class="tableList membersList">
				<thead>
					<tr class="tableHead">
						{foreach from=$header item=field}
							{if $field.sortable}
								<th class="column{$field.field|ucfirst}{if $sortField == $field.field} active{/if}"><div><a href="index.php?page=MembersList&amp;letter={@$letter}&amp;searchID={@$searchID}&amp;pageNo={@$pageNo}&amp;sortField={$field.field}&amp;sortOrder={if $sortField == $field.field && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}{@$field.name}{/lang}{if $sortField == $field.field} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}</a></div></th>
							{else}
								<th class="column{$field.field|ucfirst}"><div><span class="emptyHead">{lang}{@$field.name}{/lang}</span></div></th>
							{/if}
						{/foreach}
					</tr>
				</thead>
				<tbody>
					{foreach from=$members item=member}
						<tr class="container-{cycle values='1,2'}">
							{foreach from=$fields item=field}
								<td class="column{$field|ucfirst}">{@$member.$field}</td>
							{/foreach}
						</tr>
					{/foreach}
				</tbody>
			</table>
		{else}
			<div class="container-1">
				{lang}wcf.user.membersList.error.noMembers{/lang}
			</div>
		{/if}
	</div>
	
	{pages link="index.php?page=MembersList&pageNo=%d&searchID=$searchID&sortField=$sortField&sortOrder=$sortOrder&letter=$letter"|concat:SID_ARG_2ND_NOT_ENCODED}

</div>

{include file='footer' sandbox=false}
</body>
</html>