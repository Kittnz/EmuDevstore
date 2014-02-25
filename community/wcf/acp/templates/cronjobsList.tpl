{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/cronjobsL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.cronjobs.list{/lang}</h2>
		<p>{lang}wcf.acp.cronjobs.subtitle{/lang}</p>
	</div>
</div>

{if $deleteJob}
	<p class="success">{lang}wcf.acp.cronjobs.delete.success{/lang}</p>	
{/if}

{if $successfulExecuted}
	<p class="success">{lang}wcf.acp.cronjobs.execute.success{/lang}</p>	
{/if}

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=CronjobsList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:PACKAGE_ID:SID_ARG_2ND_NOT_ENCODED}
	
	{if $this->user->getPermission('admin.system.cronjobs.canAddCronjob')}
		<div class="largeButtons">
			<ul><li><a href="index.php?form=CronjobsAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.cronjobs.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/cronjobsAddM.png" alt="" /> <span>{lang}wcf.acp.cronjobs.add{/lang}</span></a></li></ul>
		</div>
	{/if}
</div>

{if !$items}
	<div class="border content">
		<div class="container-1">
			<p>{lang}wcf.acp.cronjobs.noneAvailable{/lang}</p>
		</div>
	</div>
{else}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.cronjobs.list.count{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnCronjobID{if $sortField == 'cronjobID'} active{/if}" colspan="2"><div><a href="index.php?page=CronjobsList&amp;pageNo={@$pageNo}&amp;sortField=cronjobID&amp;sortOrder={if $sortField == 'cronjobID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.cronjobs.cronjobID{/lang}{if $sortField == 'cronjobID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnStartMinute{if $sortField == 'startMinute'} active{/if}" title="{lang}wcf.acp.cronjobs.startMinute{/lang}"><div><a href="index.php?page=CronjobsList&amp;pageNo={@$pageNo}&amp;sortField=startMinute&amp;sortOrder={if $sortField == 'startMinute' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.cronjobs.startMinuteShort{/lang}{if $sortField == 'startMinute'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnStartHour{if $sortField == 'startHour'} active{/if}" title="{lang}wcf.acp.cronjobs.startHour{/lang}"><div><a href="index.php?page=CronjobsList&amp;pageNo={@$pageNo}&amp;sortField=startHour&amp;sortOrder={if $sortField == 'startHour' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.cronjobs.startHourShort{/lang}{if $sortField == 'startHour'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnStartDom{if $sortField == 'startDom'} active{/if}" title="{lang}wcf.acp.cronjobs.startDom{/lang}"><div><a href="index.php?page=CronjobsList&amp;pageNo={@$pageNo}&amp;sortField=startDom&amp;sortOrder={if $sortField == 'startDom' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.cronjobs.startDomShort{/lang}{if $sortField == 'startDom'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnStartMonth{if $sortField == 'startMonth'} active{/if}" title="{lang}wcf.acp.cronjobs.startMonth{/lang}"><div><a href="index.php?page=CronjobsList&amp;pageNo={@$pageNo}&amp;sortField=startMonth&amp;sortOrder={if $sortField == 'startMonth' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.cronjobs.startMonthShort{/lang}{if $sortField == 'startMonth'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnStartDow{if $sortField == 'startDow'} active{/if}" title="{lang}wcf.acp.cronjobs.startDow{/lang}"><div><a href="index.php?page=CronjobsList&amp;pageNo={@$pageNo}&amp;sortField=startDow&amp;sortOrder={if $sortField == 'startDow' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.cronjobs.startDowShort{/lang}{if $sortField == 'startDow'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnDescription{if $sortField == 'description'} active{/if}"><div><a href="index.php?page=CronjobsList&amp;pageNo={@$pageNo}&amp;sortField=description&amp;sortOrder={if $sortField == 'description' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.cronjobs.description{/lang}{if $sortField == 'description'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnNextExec{if $sortField == 'nextExec'} active{/if}"><div><a href="index.php?page=CronjobsList&amp;pageNo={@$pageNo}&amp;sortField=nextExec&amp;sortOrder={if $sortField == 'nextExec' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.cronjobs.nextExec{/lang}{if $sortField == 'nextExec'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalColumns|isset}{@$additionalColumns}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$cronjobs item=cronjob}
				<tr class="{cycle values="container-1,container-2"}">
					<td class="columnIcon">
						{if $this->user->getPermission('admin.system.cronjobs.canEditCronjob')}
							<a href="index.php?action=CronjobExecute&amp;cronjobID={@$cronjob.cronjobID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/cronjobExecuteS.png" alt="" title="{lang}wcf.acp.cronjobs.execute{/lang}" /></a>
						{/if}
						
						{if $cronjob.enableDisable}
							{if $cronjob.active}
								<a href="index.php?action=CronjobsDisable&amp;cronjobID={@$cronjob.cronjobID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/enabledS.png" alt="" title="{lang}wcf.acp.cronjobs.disable{/lang}" /></a>
							{else}
								<a href="index.php?action=CronjobsEnable&amp;cronjobID={@$cronjob.cronjobID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/disabledS.png" alt="" title="{lang}wcf.acp.cronjobs.enable{/lang}" /></a>
							{/if}
						{else}
							{if $cronjob.active}
								<img src="{@RELATIVE_WCF_DIR}icon/enabledDisabledS.png" alt="" title="{lang}wcf.acp.cronjobs.disable{/lang}" />
							{else}
								<img src="{@RELATIVE_WCF_DIR}icon/disabledDisabledS.png" alt="" title="{lang}wcf.acp.cronjobs.enable{/lang}" />
							{/if}
						{/if}
						
						{if $cronjob.editable}
							<a href="index.php?form=CronjobsEdit&amp;cronjobID={@$cronjob.cronjobID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wcf.acp.cronjobs.edit{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/editDisabledS.png" alt="" title="{lang}wcf.acp.cronjobs.edit.disabled{/lang}" />
						{/if}
						{if $cronjob.deletable}
							<a onclick="return confirm('{lang}wcf.acp.cronjobs.delete.sure{/lang}')" href="index.php?action=CronjobsDelete&amp;cronjobID={@$cronjob.cronjobID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.cronjobs.delete{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.cronjobs.delete.disabled{/lang}" />
						{/if}
						{if $cronjob.additionalButtons|isset}{@$cronjob.additionalButtons}{/if}
					</td>
					<td class="columnID">{@$cronjob.cronjobID}</td>
					<td class="columnStartMinute">{$cronjob.startMinute|truncate:30:' ...'}</td>
					<td class="columnStartHour">{$cronjob.startHour|truncate:30:' ...'}</td>
					<td class="columnStartDom">{$cronjob.startDom|truncate:30:' ...'}</td>
					<td class="columnStartMonth">{$cronjob.startMonth|truncate:30:' ...'}</td>
					<td class="columnStartDow">{$cronjob.startDow|truncate:30:' ...'}</td>
					<td class="columnDescription columnText" title="{$cronjob.description}">
						{if $cronjob.editable}
							<a title="{lang}wcf.acp.cronjobs.edit{/lang}" href="index.php?form=CronjobsEdit&amp;cronjobID={@$cronjob.cronjobID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{$cronjob.description|truncate:50:" ..."}</a>
						{else}
							{$cronjob.description|truncate:50:' ...'}
						{/if}
					</td>
					<td class="columnNextExec columnDate">
						{if $cronjob.active && $cronjob.nextExec != 1}
							{@$cronjob.nextExec|shorttime}
						{/if}
					</td>
					
					{if $cronjob.additionalColumns|isset}{@$cronjob.additionalColumns}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
	
	<div class="contentFooter">
		{@$pagesLinks}
		
		{if $this->user->getPermission('admin.system.cronjobs.canAddCronjob')}
			<div class="largeButtons">
				<ul><li><a href="index.php?form=CronjobsAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.cronjobs.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/cronjobsAddM.png" alt="" /> <span>{lang}wcf.acp.cronjobs.add{/lang}</span></a></li></ul>
			</div>
		{/if}	
	</div>
{/if}

{include file='footer'}