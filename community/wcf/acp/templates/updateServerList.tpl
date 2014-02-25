{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/updateServerL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.updateServer.view{/lang}</h2>
	</div>
</div>

{if $deletedPackageUpdateServerID}
	<p class="success">{lang}wcf.acp.updateServer.delete.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?form=UpdateServerAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.updateServer.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/updateServerAddM.png" alt="" /> <span>{lang}wcf.acp.updateServer.add{/lang}</span></a></li></ul>
	</div>
</div>

{if !$updateServers|count}
	<div class="border content">
		<div class="container-1">
			<p>{lang}wcf.acp.updateServer.view.noneAvailable{/lang}</p>
		</div>
	</div>
{else}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.updateServer.list.available{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnPackageUpdateServerID{if $sortField == 'packageUpdateServerID'} active{/if}" colspan="2"><div><a href="index.php?page=UpdateServerList&amp;pageNo={@$pageNo}&amp;sortField=packageUpdateServerID&amp;sortOrder={if $sortField == 'packageUpdateServerID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.updateServer.packageUpdateServerID{/lang}{if $sortField == 'packageUpdateServerID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnServer{if $sortField == 'server'} active{/if}"><div><a href="index.php?page=UpdateServerList&amp;pageNo={@$pageNo}&amp;sortField=server&amp;sortOrder={if $sortField == 'server' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.updateServer.server{/lang}{if $sortField == 'server'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnPackages{if $sortField == 'packages'} active{/if}"><div><a href="index.php?page=UpdateServerList&amp;pageNo={@$pageNo}&amp;sortField=packages&amp;sortOrder={if $sortField == 'packages' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.updateServer.packages{/lang}{if $sortField == 'packages'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnStatus{if $sortField == 'status'} active{/if}"><div><a href="index.php?page=UpdateServerList&amp;pageNo={@$pageNo}&amp;sortField=status&amp;sortOrder={if $sortField == 'status' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.updateServer.status{/lang}{if $sortField == 'status'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnErrorText{if $sortField == 'errorText'} active{/if}"><div><a href="index.php?page=UpdateServerList&amp;pageNo={@$pageNo}&amp;sortField=errorText&amp;sortOrder={if $sortField == 'errorText' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.updateServer.errorText{/lang}{if $sortField == 'errorText'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnTimestamp{if $sortField == 'timestamp'} active{/if}"><div><a href="index.php?page=UpdateServerList&amp;pageNo={@$pageNo}&amp;sortField=timestamp&amp;sortOrder={if $sortField == 'timestamp' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.updateServer.timestamp{/lang}{if $sortField == 'timestamp'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalColumns|isset}{@$additionalColumns}{/if}
				</tr>
			</thead>
			<tbody>
				{foreach from=$updateServers item=updateServer}
					<tr class="{cycle values="container-1,container-2"}">
						<td class="columnIcon">
							{if $updateServer.statusUpdate == 1}
								<a href="index.php?action=UpdateServerChangeStatus&amp;packageUpdateServerID={@$updateServer.packageUpdateServerID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/enabledS.png" alt="" title="{lang}wcf.acp.updateServer.disable{/lang}" /></a>
							{else}
								<a href="index.php?action=UpdateServerChangeStatus&amp;packageUpdateServerID={@$updateServer.packageUpdateServerID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/disabledS.png" alt="" title="{lang}wcf.acp.updateServer.enable{/lang}" /></a>
							{/if}
							
							<a href="index.php?form=UpdateServerEdit&amp;packageUpdateServerID={@$updateServer.packageUpdateServerID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wcf.acp.updateServer.edit{/lang}" /></a>
							<a onclick="return confirm('{lang}wcf.acp.updateServer.delete.sure{/lang}')" href="index.php?action=UpdateServerDelete&amp;packageUpdateServerID={@$updateServer.packageUpdateServerID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.updateServer.delete{/lang}" /></a>
							
							{if $updateServer.additionalButtons|isset}{@$updateServer.additionalButtons}{/if}						
						</td>
						<td class="columnID">{@$updateServer.packageUpdateServerID}</td>
						<td class="columnText">
							<a href="index.php?form=UpdateServerEdit&amp;packageUpdateServerID={@$updateServer.packageUpdateServerID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">
								{@$updateServer.server}
							</a>
						</td>
						<td class="columnText smallFont">
							{#$updateServer.packages}
						</td>
						<td class="columnText smallFont" style="color: {if $updateServer.status == 'online'}green{else}red{/if}">
							{@$updateServer.status}
						</td>
						<td class="columnText smallFont">
							<div title="{@$updateServer.errorText}">
								{@$updateServer.errorText|truncate:"30"}
							</div>
						</td>
						<td class="columnDate smallFont">
							{if $updateServer.timestamp}{@$updateServer.timestamp|shorttime}{/if}
						</td>
						
						{if $updateServer.additionalColumns|isset}{@$updateServer.additionalColumns}{/if}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	<div class="contentHeader">
		<div class="largeButtons">
			<ul><li><a href="index.php?form=UpdateServerAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.updateServer.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/updateServerAddM.png" alt="" /> <span>{lang}wcf.acp.updateServer.add{/lang}</span></a></li></ul>
		</div>
	</div>
{/if}

{include file='footer'}