{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/groupL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.group.list{/lang}</h2>
	</div>
</div>

{if $deletedGroups}
	<p class="success">{lang}wcf.acp.group.delete.success{/lang}</p>	
{/if}

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=GroupList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:PACKAGE_ID:SID_ARG_2ND_NOT_ENCODED}
	<div class="largeButtons">
		<ul>
			<li><a href="index.php?form=GroupAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.group.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/groupAddM.png" alt="" /> <span>{lang}wcf.acp.group.add{/lang}</span></a></li>
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}			
		</ul>
	</div>
</div>

{if $groups|count}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.group.list.data{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnGroupID{if $sortField == 'groupID'} active{/if}" colspan="2"><div><a href="index.php?page=GroupList&amp;pageNo={@$pageNo}&amp;sortField=groupID&amp;sortOrder={if $sortField == 'groupID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.group.groupID{/lang}{if $sortField == 'groupID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnGroupName{if $sortField == 'groupName'} active{/if}"><div><a href="index.php?page=GroupList&amp;pageNo={@$pageNo}&amp;sortField=groupName&amp;sortOrder={if $sortField == 'groupName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.group.groupName{/lang}{if $sortField == 'groupName'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnMembers{if $sortField == 'members'} active{/if}"><div><a href="index.php?page=GroupList&amp;pageNo={@$pageNo}&amp;sortField=members&amp;sortOrder={if $sortField == 'members' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.group.members{/lang}{if $sortField == 'members'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalColumns|isset}{@$additionalColumns}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$groups item=group}
				<tr class="{cycle values="container-1,container-2"}">
					<td class="columnIcon">
						{if $group.editable}
							<a href="index.php?form=GroupEdit&amp;groupID={@$group.groupID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wcf.acp.group.edit{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/editDisabledS.png" alt="" title="{lang}wcf.acp.group.edit{/lang}" />
						{/if}
						{if $group.deletable}
							<a onclick="return confirm('{lang}wcf.acp.group.delete.sure{/lang}')" href="index.php?action=GroupDelete&amp;groupID={@$group.groupID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.group.delete{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.group.delete{/lang}" />
						{/if}
						
						{if $group.additionalButtons|isset}{@$group.additionalButtons}{/if}
					</td>
					<td class="columnGroupID columnID">{@$group.groupID}</td>
					<td class="columnGroupName columnText">{if $group.editable}<a title="{lang}wcf.acp.group.edit{/lang}" href="index.php?form=GroupEdit&amp;groupID={@$group.groupID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{$group.groupName}</a>{else}{$group.groupName}{/if}</td>
					<td class="columnMembers columnNumbers"><a title="{lang}wcf.acp.group.showMembers{/lang}" href="index.php?form=UserSearch&amp;groupID={@$group.groupID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{#$group.members}</a></td>
					
					{if $group.additionalColumns|isset}{@$group.additionalColumns}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
{/if}

<div class="contentFooter">
	{@$pagesLinks}
	<div class="largeButtons">
		<ul>
			<li><a href="index.php?form=GroupAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.group.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/groupAddM.png" alt="" /> <span>{lang}wcf.acp.group.add{/lang}</span></a></li>
			{if $additionalLargeButtons|isset}{@$additionalLargeButtons}{/if}
		</ul>
	</div>
</div>

{include file='footer'}
