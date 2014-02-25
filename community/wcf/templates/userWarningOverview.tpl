{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.profile.title{/lang} - {lang}wcf.user.profile.members{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
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
			{if $userWarnings|count > 0}
				<div class="contentBox">
					<h3 class="subHeadline">{lang}wcf.user.infraction.userWarning.warnings{/lang} <span>({#$userWarnings|count})</span></h3>
					
					<div class="border">
						<table class="tableList">
							<thead>
								<tr class="tableHead">
									{if $this->user->getPermission('admin.user.infraction.canWarnUser')}
										<th class="columnUserWarningID" colspan="2"><div><span class="emptyHead">{lang}wcf.user.infraction.userWarning.userWarningID{/lang}</span></div></th>
									{/if}
									<th class="columnUserWarningTitle"><div><span class="emptyHead">{lang}wcf.user.infraction.warning.title{/lang}</span></div></th>
									<th class="columnUserWarningObject"><div><span class="emptyHead">{lang}wcf.user.infraction.userWarning.object{/lang}</span></div></th>
									<th class="columnUserWarningJudge"><div><span class="emptyHead">{lang}wcf.user.infraction.userWarning.jugde{/lang}</span></div></th>
									<th class="columnUserWarningPoints"><div><span class="emptyHead">{lang}wcf.user.infraction.warning.points{/lang}</span></div></th>
									<th class="columnUserWarningReason"><div><span class="emptyHead">{lang}wcf.user.infraction.userWarning.reason{/lang}</span></div></th>
									<th class="columnUserWarningTime"><div><span class="emptyHead">{lang}wcf.user.infraction.userWarning.time{/lang}</span></div></th>
									<th class="columnUserWarningExpires"><div><span class="emptyHead">{lang}wcf.user.infraction.warning.expires{/lang}</span></div></th>
									
									{if $additionalColumnHeads|isset}{@$additionalColumnHeads}{/if}
								</tr>
							</thead>
							<tbody>
								{foreach from=$userWarnings item=userWarning}
									<tr class="{cycle values="container-1,container-2"}">
										{if $this->user->getPermission('admin.user.infraction.canWarnUser')}
											<td class="columnIcon">
												{if $this->user->getPermission('admin.user.infraction.canEditWarning')}
													<a href="acp/index.php?form=Login&amp;url=index.php%3Fform=UserWarningEdit%26userWarningID%3D{@$userWarning->userWarningID}%26packageID%3D{@PACKAGE_ID}" title="{lang}wcf.acp.infraction.userWarning.edit{/lang}"><img src="{icon}editS.png{/icon}" alt="" /></a>
												{else}
													<img src="{icon}editDisabledS.png{/icon}" alt="" title="{lang}wcf.acp.infraction.userWarning.edit{/lang}" />
												{/if}
												{if $this->user->getPermission('admin.user.infraction.canDeleteWarning')}
													<a onclick="return confirm('{lang}wcf.acp.infraction.userWarning.delete.sure{/lang}')" href="acp/index.php?form=Login&amp;url=index.php%3Faction=UserWarningDelete%26userWarningID%3D{@$userWarning->userWarningID}%26packageID%3D{@PACKAGE_ID}" title="{lang}wcf.acp.infraction.userWarning.delete{/lang}"><img src="{icon}deleteS.png{/icon}" alt="" /></a>
												{else}
													<img src="{icon}deleteDisabledS.png{/icon}" alt="" title="{lang}wcf.acp.infraction.userWarning.delete{/lang}" />
												{/if}
												
												{if $additionalButtons.$userWarning->userWarningID|isset}{@$additionalButtons.$userWarning->userWarningID}{/if}
											</td>
											<td class="columnUserWarningID columnID">{@$userWarning->userWarningID}</td>
										{/if}
										<td class="columnUserWarningTitle columnText">
											{if $this->user->getPermission('admin.user.infraction.canEditWarning')}
												<a href="acp/index.php?form=Login&amp;url=index.php%3Fform=UserWarningEdit%26userWarningID%3D{@$userWarning->userWarningID}%26packageID%3D{@PACKAGE_ID}" title="{lang}wcf.acp.infraction.userWarning.edit{/lang}">{$userWarning->title}</a>
											{else}
												{$userWarning->title}
											{/if}
										</td>
										<td class="columnUserWarningObject columnText">
											{if $userWarning->object}
												<a href="{$userWarning->object->getURL()}">{$userWarning->object->getTitle()}</a>
											{/if}
										</td>
										<td class="columnUserWarningJudge columnText">
											{if $userWarning->judgeID}
												<a href="index.php?page=User&amp;userID={@$userWarning->judgeID}{@SID_ARG_2ND}" title="{lang username=$userWarning->judgeUsername}wcf.user.viewProfile{/lang}">{$userWarning->judgeUsername}</a>
											{else}
												{$userWarning->judgeUsername}
											{/if} 
										</td>
										<td class="columnUserWarningPoints columnNumbers">{#$userWarning->points}</td>
										<td class="columnUserWarningReason columnText smallFont">
											{@$userWarning->reason|truncate:500|newlineToBreak}
										</td>
										<td class="columnUserWarningTime columnDate smallFont">{@$userWarning->time|shorttime}</td>
										<td class="columnUserWarningExpires columnDate smallFont">{if $userWarning->expires > 0}{@$userWarning->expires|shorttime}{else}{lang}wcf.user.infraction.warning.expires.never{/lang}{/if}</td>
										
										{if $additionalColumns.$userWarning->userWarningID|isset}{@$additionalColumns.$userWarning->userWarningID}{/if}
									</tr>
								{/foreach}
							</tbody>
						</table>
					</div>
				</div>
			{/if}
			
			{if $userSuspensions|count > 0}
				<div class="contentBox">
					<h3 class="subHeadline">{lang}wcf.user.infraction.userSuspension.suspensions{/lang} <span>({#$userSuspensions|count})</span></h3>
					
					<div class="border">
						<table class="tableList">
							<thead>
								<tr class="tableHead">
									{if $this->user->getPermission('admin.user.infraction.canWarnUser')}
										<th class="columnUserSuspensionID" colspan="2"><div><span class="emptyHead">{lang}wcf.acp.infraction.userSuspension.userSuspensionID{/lang}</span></div></th>
									{/if}
									<th class="columnUserSuspensionTitle"><div><span class="emptyHead">{lang}wcf.user.infraction.warning.title{/lang}</span></div></th>
									<th class="columnUserSuspensionTime"><div><span class="emptyHead">{lang}wcf.user.infraction.userWarning.time{/lang}</span></div></th>
									<th class="columnUserSuspensionExpires"><div><span class="emptyHead">{lang}wcf.user.infraction.warning.expires{/lang}</span></div></th>
									
									{if $additionalColumnHeads|isset}{@$additionalColumnHeads}{/if}
								</tr>
							</thead>
							<tbody>
								{foreach from=$userSuspensions item=userSuspension}
									<tr class="{cycle values="container-1,container-2"}">
										{if $this->user->getPermission('admin.user.infraction.canWarnUser')}
											<td class="columnIcon">
												{if $this->user->getPermission('admin.user.infraction.canEditSuspension')}
													<a href="acp/index.php?form=Login&amp;url=index.php%3Fform=UserSuspensionEdit%26userSuspensionID%3D{@$userSuspension->userSuspensionID}%26packageID%3D{@PACKAGE_ID}"><img src="{icon}editS.png{/icon}" alt="" title="{lang}wcf.acp.infraction.userSuspension.edit{/lang}" /></a>
												{else}
													<img src="{icon}editDisabledS.png{/icon}" alt="" title="{lang}wcf.acp.infraction.userSuspension.edit{/lang}" />
												{/if}
												{if $this->user->getPermission('admin.user.infraction.canDeleteSuspension')}
													<a onclick="return confirm('{lang}wcf.acp.infraction.userSuspension.delete.sure{/lang}')" href="acp/index.php?form=Login&amp;url=index.php%3Faction=UserSuspensionDelete%26userSuspensionID%3D{@$userSuspension->userSuspensionID}%26packageID%3D{@PACKAGE_ID}"><img src="{icon}deleteS.png{/icon}" alt="" title="{lang}wcf.acp.infraction.userSuspension.delete{/lang}" /></a>
												{else}
													<img src="{icon}deleteDisabledS.png{/icon}" alt="" title="{lang}wcf.acp.infraction.userSuspension.delete{/lang}" />
												{/if}
												
												{if $additionalButtons.$userSuspension->userSuspensionID|isset}{@$additionalButtons.$userSuspension->userSuspensionID}{/if}
											</td>
											<td class="columnUserSuspensionID columnID">{@$userSuspension->userSuspensionID}</td>
										{/if}
										<td class="columnUserSuspensionTitle columnText">
											{if $this->user->getPermission('admin.user.infraction.canEditSuspension')}
												<a href="acp/index.php?form=Login&amp;url=index.php%3Fform=UserSuspensionEdit%26userSuspensionID%3D{@$userSuspension->userSuspensionID}%26packageID%3D{@PACKAGE_ID}" title="{lang}wcf.acp.infraction.userSuspension.edit{/lang}">{$userSuspension->title}</a>
											{else}
												{$userSuspension->title}
											{/if}
										</td>
										<td class="columnUserSuspensionTime columnDate">{@$userSuspension->time|shorttime}</td>
										<td class="columnUserSuspensionExpires columnText">{if $userSuspension->expires > 0}{@$userSuspension->expires|shorttime}{else}{lang}wcf.user.infraction.warning.expires.never{/lang}{/if}</td>
										
										{if $additionalColumns.$userSuspension->userSuspensionID|isset}{@$additionalColumns.$userSuspension->userSuspensionID}{/if}
									</tr>
								{/foreach}
							</tbody>
						</table>
					</div>
				</div>
			{/if}
		</div>
	</div>

</div>

{include file='footer' sandbox=false}
</body>
</html>