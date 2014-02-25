{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/infractionWarningL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.infraction.warning.view{/lang}</h2>
	</div>
</div>

{if $deletedWarningID}
	<p class="success">{lang}wcf.acp.infraction.warning.delete.success{/lang}</p>	
{/if}

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=WarningList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:PACKAGE_ID:SID_ARG_2ND_NOT_ENCODED}
	
	{if $this->user->getPermission('admin.user.infraction.canAddWarning')}
		<div class="largeButtons">
			<ul><li><a href="index.php?form=WarningAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/infractionWarningAddM.png" alt="" title="{lang}wcf.acp.infraction.warning.add{/lang}" /> <span>{lang}wcf.acp.infraction.warning.add{/lang}</span></a></li></ul>
		</div>
	{/if}
</div>

{if $warnings|count}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.infraction.warning.view.count{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnWarningID{if $sortField == 'warningID'} active{/if}" colspan="2"><div><a href="index.php?page=WarningList&amp;pageNo={@$pageNo}&amp;sortField=warningID&amp;sortOrder={if $sortField == 'warningID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.user.infraction.warning.warningID{/lang}{if $sortField == 'warningID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnWarningTitle{if $sortField == 'title'} active{/if}"><div><a href="index.php?page=WarningList&amp;pageNo={@$pageNo}&amp;sortField=title&amp;sortOrder={if $sortField == 'title' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.user.infraction.warning.title{/lang}{if $sortField == 'title'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnWarningPoints{if $sortField == 'points'} active{/if}"><div><a href="index.php?page=WarningList&amp;pageNo={@$pageNo}&amp;sortField=points&amp;sortOrder={if $sortField == 'points' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.user.infraction.warning.points{/lang}{if $sortField == 'points'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnWarningExpires{if $sortField == 'expires'} active{/if}"><div><a href="index.php?page=WarningList&amp;pageNo={@$pageNo}&amp;sortField=expires&amp;sortOrder={if $sortField == 'expires' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.user.infraction.warning.expires{/lang}{if $sortField == 'expires'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnWarnings{if $sortField == 'warnings'} active{/if}"><div><a href="index.php?page=WarningList&amp;pageNo={@$pageNo}&amp;sortField=warnings&amp;sortOrder={if $sortField == 'warnings' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.user.infraction.warning.warnings{/lang}{if $sortField == 'warnings'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalColumnHeads|isset}{@$additionalColumnHeads}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$warnings item=warning}
				<tr class="{cycle values="container-1,container-2"}">
					<td class="columnIcon">
						{if $this->user->getPermission('admin.user.infraction.canEditWarning')}
							<a href="index.php?form=WarningEdit&amp;warningID={@$warning->warningID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wcf.acp.infraction.warning.edit{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/editDisabledS.png" alt="" title="{lang}wcf.acp.infraction.warning.edit{/lang}" />
						{/if}
						{if $this->user->getPermission('admin.user.infraction.canDeleteWarning') && $warning->warnings == 0}
							<a onclick="return confirm('{lang}wcf.acp.infraction.warning.delete.sure{/lang}')" href="index.php?action=WarningDelete&amp;warningID={@$warning->warningID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.infraction.warning.delete{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.infraction.warning.delete{/lang}" />
						{/if}
						
						{if $additionalButtons.$warning->warningID|isset}{@$additionalButtons.$warning->warningID}{/if}
					</td>
					<td class="columnWarningID columnID">{@$warning->warningID}</td>
					<td class="columnWarningTitle columnText">
						{if $this->user->getPermission('admin.user.infraction.canEditWarning')}
							<a href="index.php?form=WarningEdit&amp;warningID={@$warning->warningID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.infraction.warning.edit{/lang}">{$warning->title}</a>
						{else}
							{$warning->title}
						{/if}
					</td>
					<td class="columnWarningPoints columnNumbers">{#$warning->points}</td>
					<td class="columnWarningExpires columnNumbers">{if $warning->expires > 0}{@$warning->expires+TIME_NOW|datediff}{else}{lang}wcf.user.infraction.warning.expires.never{/lang}{/if}</td>
					<td class="columnWarnings columnNumbers">{#$warning->warnings}</td>
					
					{if $additionalColumns.$warning->warningID|isset}{@$additionalColumns.$warning->warningID}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>

	<div class="contentFooter">
		{@$pagesLinks}
		
		{if $this->user->getPermission('admin.user.infraction.canAddWarning')}
			<div class="largeButtons">
				<ul><li><a href="index.php?form=WarningAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/infractionWarningAddM.png" alt="" title="{lang}wcf.acp.infraction.warning.add{/lang}" /> <span>{lang}wcf.acp.infraction.warning.add{/lang}</span></a></li></ul>
			</div>
		{/if}
	</div>
{else}
	<div class="border content">
		<div class="container-1">
			<p>{lang}wcf.acp.infraction.warning.view.count.noEntries{/lang}</p>
		</div>
	</div>
{/if}

{include file='footer'}
