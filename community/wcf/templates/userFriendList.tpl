{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.profile.title{/lang} - {lang}wcf.user.profile.members{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{* --- quick search controls --- *}
{assign var='searchFieldTitle' value='{lang}wcf.user.profile.search.query{/lang}'}
{capture assign=searchHiddenFields}
	<input type="hidden" name="userID" value="{@$user->userID}" />
{/capture}
{* --- end --- *}
{include file='header' sandbox=false}

<div id="main">
	{include file="userProfileHeader"}
	
	<div class="border {if $this|method_exists:'getUserProfileMenu' && $this->getUserProfileMenu()->getMenuItems('')|count > 1}tabMenuContent{else}content{/if}">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.user.profile.friends{/lang}</h3>
	
			<div class="contentHeader">
				{pages print=true assign=pagesOutput link="index.php?page=UserFriendList&userID=$userID&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"|concat:SID_ARG_2ND_NOT_ENCODED}
			</div>
	
			<div class="border">
				<table class="tableList membersList">
					<thead>
						<tr class="tableHead">
							{foreach from=$header item=field}
								{if $field.sortable}
									<th class="column{$field.field|ucfirst}{if $sortField == $field.field} active{/if}"><div><a href="index.php?page=UserFriendList&amp;userID={@$userID}&amp;pageNo={@$pageNo}&amp;sortField={$field.field}&amp;sortOrder={if $sortField == $field.field && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}{@$field.name}{/lang}{if $sortField == $field.field} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}</a></div></th>
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
			</div>
			
			<div class="contentFooter">
				{@$pagesOutput}
			</div>
		</div>
	</div>
</div>

{include file='footer' sandbox=false}
</body>
</html>