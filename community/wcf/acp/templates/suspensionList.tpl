{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/infractionSuspensionL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.infraction.suspension.view{/lang}</h2>
	</div>
</div>

{if $deletedSuspensionID}
	<p class="success">{lang}wcf.acp.infraction.suspension.delete.success{/lang}</p>	
{/if}

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=SuspensionList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:PACKAGE_ID:SID_ARG_2ND_NOT_ENCODED}
	
	{if $this->user->getPermission('admin.user.infraction.canAddSuspension')}
		<div class="largeButtons">
			<ul><li><a href="index.php?form=SuspensionAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/infractionSuspensionAddM.png" alt="" title="{lang}wcf.acp.infraction.suspension.add{/lang}" /> <span>{lang}wcf.acp.infraction.suspension.add{/lang}</span></a></li></ul>
		</div>
	{/if}
</div>

{if $suspensions|count}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.infraction.suspension.view.count{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnSuspensionID{if $sortField == 'suspensionID'} active{/if}" colspan="2"><div><a href="index.php?page=SuspensionList&amp;pageNo={@$pageNo}&amp;sortField=suspensionID&amp;sortOrder={if $sortField == 'suspensionID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.infraction.suspension.suspensionID{/lang}{if $sortField == 'suspensionID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnSuspensionTitle{if $sortField == 'title'} active{/if}"><div><a href="index.php?page=SuspensionList&amp;pageNo={@$pageNo}&amp;sortField=title&amp;sortOrder={if $sortField == 'title' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.user.infraction.warning.title{/lang}{if $sortField == 'title'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnSuspensionPoints{if $sortField == 'points'} active{/if}"><div><a href="index.php?page=SuspensionList&amp;pageNo={@$pageNo}&amp;sortField=points&amp;sortOrder={if $sortField == 'points' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.user.infraction.warning.points{/lang}{if $sortField == 'points'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnSuspensionExpires{if $sortField == 'expires'} active{/if}"><div><a href="index.php?page=SuspensionList&amp;pageNo={@$pageNo}&amp;sortField=expires&amp;sortOrder={if $sortField == 'expires' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.user.infraction.warning.expires{/lang}{if $sortField == 'expires'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnSuspensions{if $sortField == 'suspensions'} active{/if}"><div><a href="index.php?page=SuspensionList&amp;pageNo={@$pageNo}&amp;sortField=suspensions&amp;sortOrder={if $sortField == 'suspensions' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.infraction.suspension.suspensions{/lang}{if $sortField == 'suspensions'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalColumnHeads|isset}{@$additionalColumnHeads}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$suspensions item=suspension}
				<tr class="{cycle values="container-1,container-2"}">
					<td class="columnIcon">
						{if $this->user->getPermission('admin.user.infraction.canEditSuspension')}
							<a href="index.php?form=SuspensionEdit&amp;suspensionID={@$suspension->suspensionID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wcf.acp.infraction.suspension.edit{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/editDisabledS.png" alt="" title="{lang}wcf.acp.infraction.suspension.edit{/lang}" />
						{/if}
						{if $this->user->getPermission('admin.user.infraction.canDeleteSuspension') && $suspension->suspensions == 0}
							<a onclick="return confirm('{lang}wcf.acp.infraction.suspension.delete.sure{/lang}')" href="index.php?action=SuspensionDelete&amp;suspensionID={@$suspension->suspensionID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.infraction.suspension.delete{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.infraction.suspension.delete{/lang}" />
						{/if}
						
						{if $additionalButtons.$suspension->suspensionID|isset}{@$additionalButtons.$suspension->suspensionID}{/if}
					</td>
					<td class="columnSuspensionID columnID">{@$suspension->suspensionID}</td>
					<td class="columnSuspensionTitle columnText">
						{if $this->user->getPermission('admin.user.infraction.canEditSuspension')}
							<a href="index.php?form=SuspensionEdit&amp;suspensionID={@$suspension->suspensionID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.infraction.suspension.edit{/lang}">{$suspension->title}</a>
						{else}
							{$suspension->title}
						{/if}
					</td>
					<td class="columnSuspensionPoints columnNumbers">{#$suspension->points}</td>
					<td class="columnSuspensionExpires columnNumbers">{if $suspension->expires > 0}{@$suspension->expires+TIME_NOW|datediff}{else}{lang}wcf.user.infraction.warning.expires.never{/lang}{/if}</td>
					<td class="columnSuspensions columnNumbers">{#$suspension->suspensions}</td>
					
					{if $additionalColumns.$suspension->suspensionID|isset}{@$additionalColumns.$suspension->suspensionID}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>

	<div class="contentFooter">
		{@$pagesLinks}
		
		{if $this->user->getPermission('admin.user.infraction.canAddSuspension')}
			<div class="largeButtons">
				<ul><li><a href="index.php?form=SuspensionAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/infractionSuspensionAddM.png" alt="" title="{lang}wcf.acp.infraction.suspension.add{/lang}" /> <span>{lang}wcf.acp.infraction.suspension.add{/lang}</span></a></li></ul>
			</div>
		{/if}
	</div>
{else}
	<div class="border content">
		<div class="container-1">
			<p>{lang}wcf.acp.infraction.suspension.view.count.noEntries{/lang}</p>
		</div>
	</div>
{/if}

{include file='footer'}
