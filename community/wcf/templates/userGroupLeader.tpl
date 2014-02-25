{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.userGroups.leader.title{/lang} - {lang}wcf.user.usercp{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	{include file="userCPHeader"}
	
	<div class="border tabMenuContent">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.user.userGroups.leader.title{/lang}</h3>
			
			<fieldset>
				<legend>{lang}wcf.user.userGroups.leader.groups{/lang}</legend>
				
				<ul class="userGroupsList">
					{foreach from=$groups item=group}
						<li>
							<h4><img src="{icon}editS.png{/icon}" alt="" /> <a href="index.php?form=UserGroupAdministrate&amp;groupID={@$group.groupID}{@SID_ARG_2ND}"> <span>{lang}{$group.groupName}{/lang} ({#$group.members})</span></a></h4>
							<div class="smallFont">
								<p>{lang}{$group.groupDescription}{/lang}</p>
								<p>{lang}wcf.user.userGroups.groupType.{@$group.groupType}{/lang}</p>
							</div>
						</li>
					{/foreach}
				</ul>
			</fieldset>
			
			{if $applications|count > 0}
				<form method="post" action="index.php?action=UserGroupApplicationDelete">
					<div class="contentHeader">
						{pages print=true assign=pagesOutput link="index.php?page=UserGroupLeader&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"|concat:SID_ARG_2ND_NOT_ENCODED}
					</div>
					
					<div class="border titleBarPanel">
						<div class="containerHead"><h4>{lang}wcf.user.userGroups.leader.applications{/lang}</h4></div>
					</div>
					<div class="border borderMarginRemove">
						<table class="tableList">
							<thead>
								<tr class="tableHead">
									<th>
										<div>
											<label class="emptyHead">&nbsp;</label>
										</div>
									</th>
									<th colspan="2"{if $sortField == 'username'} class="active"{/if}>
										<div><a href="index.php?page=UserGroupLeader&amp;sortField=username&amp;sortOrder={if $sortField == 'username' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{@SID_ARG_2ND}">
											{lang}wcf.user.username{/lang}{if $sortField == 'username'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
										</a></div>
									</th>
									<th{if $sortField == 'groupName'} class="active"{/if}>
										<div><a href="index.php?page=UserGroupLeader&amp;sortField=groupName&amp;sortOrder={if $sortField == 'groupName' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{@SID_ARG_2ND}">
											{lang}wcf.user.userGroups.application.for{/lang}{if $sortField == 'groupName'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
										</a></div>
									</th>
									<th{if $sortField == 'applicationTime'} class="active"{/if}>
										<div><a href="index.php?page=UserGroupLeader&amp;sortField=applicationTime&amp;sortOrder={if $sortField == 'applicationTime' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{@SID_ARG_2ND}">
											{lang}wcf.user.userGroups.application.time{/lang}{if $sortField == 'applicationTime'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
										</a></div>
									</th>
									<th{if $sortField == 'applicationStatus'} class="active"{/if}>
										<div><a href="index.php?page=UserGroupLeader&amp;sortField=applicationStatus&amp;sortOrder={if $sortField == 'applicationStatus' && $sortOrder == 'DESC'}ASC{else}DESC{/if}&amp;pageNo={@$pageNo}{@SID_ARG_2ND}">
											{lang}wcf.user.userGroups.application.status{/lang}{if $sortField == 'applicationStatus'} <img src="{icon}sort{@$sortOrder}S.png{/icon}" alt="" />{/if}
										</a></div>
									</th>
									
									{if $additionalColumns|isset}{@$additionalColumns}{/if}
								</tr>
							</thead>
							<tbody>
								{foreach from=$applications item=application}
									<tr class="{cycle values='container-1,container-2'}">
										<td class="columnMark"><label><input name="applicationIDs[]" value="{@$application.applicationID}" type="checkbox" /></label></td>
										<td class="columnIcon"><a href="index.php?form=UserGroupLeaderApplicationEdit&amp;applicationID={@$application.applicationID}{@SID_ARG_2ND}"><img src="{icon}editS.png{/icon}" alt="" title="{lang}wcf.user.userGroups.application.edit{/lang}" /></a></td>
										<td class="columnText"><a href="index.php?page=User&amp;userID={@$application.userID}{@SID_ARG_2ND}">{$application.username}</a></td>
										<td class="columnText">{lang}{$application.groupName}{/lang}</td>
										<td class="columnDate">{@$application.applicationTime|time}</td>
										<td class="columnText">{lang}wcf.user.userGroups.application.status.{@$application.applicationStatus}{/lang}</td>
										
										{if $application.additionalColumns|isset}{@$application.additionalColumns}{/if}
									</tr>
								{/foreach}
							</tbody>
						</table>
					</div>
					
					<div class="contentFooter">
						{@$pagesOutput}
					</div>
					
					<div class="formSubmit">
						<input type="submit" accesskey="d" value="{lang}wcf.user.userGroups.application.delete{/lang}" />
						{@SID_INPUT_TAG}
						{@SECURITY_TOKEN_INPUT_TAG}
					</div>
				</form>
			{/if}
		</div>
	</div>

</div>

{include file='footer' sandbox=false}
</body>
</html>