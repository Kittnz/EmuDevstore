{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.userGroups.title{/lang} - {lang}wcf.user.usercp{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	{include file="userCPHeader"}
	
	<div class="border tabMenuContent">
		<div class="container-1">
			<h3 class="subHeadline">{lang}wcf.user.userGroups.title{/lang}</h3>
			
			{if $memberships|count > 0}
				<fieldset>
					<legend>{lang}wcf.user.userGroups.memberships{/lang}</legend>
					
					<ul class="userGroupsList">
						{foreach from=$memberships item=membership}
							<li>
								<h4>{lang}{$membership.groupName}{/lang}</h4>
								
								<div class="smallFont">
									<p>{lang}{$membership.groupDescription}{/lang}</p>
									<p>{lang}wcf.user.userGroups.groupType.{@$membership.groupType}{/lang}</p>
									{if $groupLeaders[$membership.groupID]|isset}
										<p>
											{lang}wcf.user.userGroups.groupLeaders{/lang}:
											{implode from=$groupLeaders[$membership.groupID] item=groupLeader}{if $groupLeader->leaderUserID}<a href="index.php?page=User&amp;userID={@$groupLeader->leaderUserID}{@SID_ARG_2ND}">{$groupLeader->username}</a>{else}{$groupLeader->groupName}{/if}{/implode}
										</p>
									{/if}
								</div>
								
								{if $membership.groupType == 5 || $membership.groupType == 6}<div class="smallButtons"><ul><li><a href="index.php?action=UserGroupLeave&amp;groupID={@$membership.groupID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" title="{lang}wcf.user.userGroups.leave{/lang}"><img src="{icon}deleteS.png{/icon}" alt="" /> <span>{lang}wcf.user.userGroups.leave{/lang}</span></a></li></ul></div>{/if}
							</li>
						{/foreach}
					</ul>
				</fieldset>
			{/if}
			
			{if $applications|count > 0}
				<fieldset>
					<legend>{lang}wcf.user.userGroups.applications{/lang}</legend>
					
					<ul class="userGroupsList">
						{foreach from=$applications item=application}
							<li>
								<h4>{lang}{$application.groupName}{/lang}</h4>
								
								<div class="smallFont">
									<p>{lang}{$application.groupDescription}{/lang}</p>
									<p>{lang}wcf.user.userGroups.groupType.{@$application.groupType}{/lang}</p>
									<p>{lang}wcf.user.userGroups.application.time{/lang}: {@$application.applicationTime|time}</p>
									<p>{lang}wcf.user.userGroups.application.status{/lang}: {lang}wcf.user.userGroups.application.status.{@$application.applicationStatus}{/lang}</p>
									
									{if $groupLeaders[$application.groupID]|isset}
										<p>
											{lang}wcf.user.userGroups.groupLeaders{/lang}:
											{implode from=$groupLeaders[$application.groupID] item=groupLeader}{if $groupLeader->leaderUserID}<a href="index.php?page=User&amp;userID={@$groupLeader->leaderUserID}{@SID_ARG_2ND}">{$groupLeader->username}</a>{else}{$groupLeader->groupName}{/if}{/implode}
										</p>
									{/if}
								</div>
								
								<div class="smallButtons"><ul><li><a href="index.php?form=UserGroupApplicationEdit&amp;applicationID={@$application.applicationID}{@SID_ARG_2ND}" title="{lang}wcf.user.userGroups.application.edit{/lang}"><img src="{icon}editS.png{/icon}" alt="" /> <span>{lang}wcf.user.userGroups.application.edit{/lang}</span></a></li></ul></div>
							</li>
						{/foreach}
					</ul>
				</fieldset>
			{/if}
			
			{if $openGroups|count > 0}
				<fieldset>
					<legend>{lang}wcf.user.userGroups.openGroups{/lang}</legend>
					
					<ul class="userGroupsList">
						{foreach from=$openGroups item=openGroup}
							<li>
								<h4>{lang}{$openGroup.groupName}{/lang}</h4>
								
								<div class="smallFont">
									<p>{lang}{$openGroup.groupDescription}{/lang}</p>
									<p>{lang}wcf.user.userGroups.groupType.{@$openGroup.groupType}{/lang}</p>
									{if $groupLeaders[$openGroup.groupID]|isset}
										<p>
											{lang}wcf.user.userGroups.groupLeaders{/lang}:
											{implode from=$groupLeaders[$openGroup.groupID] item=groupLeader}{if $groupLeader->leaderUserID}<a href="index.php?page=User&amp;userID={@$groupLeader->leaderUserID}{@SID_ARG_2ND}">{$groupLeader->username}</a>{else}{$groupLeader->groupName}{/if}{/implode}
										</p>
									{/if}
								</div>
								
								{if $openGroup.groupType == 5}<div class="smallButtons"><ul><li><a href="index.php?action=UserGroupJoin&amp;groupID={@$openGroup.groupID}&amp;t={@SECURITY_TOKEN}{@SID_ARG_2ND}" title="{lang}wcf.user.userGroups.join{/lang}"><img src="{icon}addS.png{/icon}" alt="" /> <span>{lang}wcf.user.userGroups.join{/lang}</span></a></li></ul></div>
								{else}<div class="smallButtons"><ul><li><a href="index.php?form=UserGroupApply&amp;groupID={@$openGroup.groupID}{@SID_ARG_2ND}" title="{lang}wcf.user.userGroups.apply{/lang}"><img src="{icon}addS.png{/icon}" alt="" /> <span>{lang}wcf.user.userGroups.apply{/lang}</span></a></li></ul></div>{/if}
							</li>
						{/foreach}
					</ul>
				</fieldset>
			{/if}
			
			{if $additionalFields|isset}{@$additionalFields}{/if}
		</div>
	</div>

</div>

{include file='footer' sandbox=false}
</body>
</html>