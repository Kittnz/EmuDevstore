{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/userRankL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.rank.view{/lang}</h2>
	</div>
</div>

{if $deletedRankID}
	<p class="success">{lang}wcf.acp.rank.delete.success{/lang}</p>	
{/if}

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=UserRankList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:PACKAGE_ID:SID_ARG_2ND_NOT_ENCODED}
	
	{if $this->user->getPermission('admin.user.rank.canAddRank')}
		<div class="largeButtons">
			<ul><li><a href="index.php?form=UserRankAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/userRankAddM.png" alt="" title="{lang}wcf.acp.rank.add{/lang}" /> <span>{lang}wcf.acp.rank.add{/lang}</span></a></li></ul>
		</div>
	{/if}
</div>

{if $ranks|count}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.rank.view.count{/lang} </h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnRankID{if $sortField == 'rankID'} active{/if}" colspan="2"><div><a href="index.php?page=UserRankList&amp;pageNo={@$pageNo}&amp;sortField=rankID&amp;sortOrder={if $sortField == 'rankID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.rank.rankID{/lang}{if $sortField == 'rankID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnRankTitle{if $sortField == 'rankTitle'} active{/if}"><div><a href="index.php?page=UserRankList&amp;pageNo={@$pageNo}&amp;sortField=rankTitle&amp;sortOrder={if $sortField == 'rankTitle' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.rank.title{/lang}{if $sortField == 'rankTitle'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnRankImage{if $sortField == 'rankImage'} active{/if}"><div><a href="index.php?page=UserRankList&amp;pageNo={@$pageNo}&amp;sortField=rankImage&amp;sortOrder={if $sortField == 'rankImage' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.rank.image{/lang}{if $sortField == 'rankImage'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnRankGroup{if $sortField == 'groupID'} active{/if}"><div><a href="index.php?page=UserRankList&amp;pageNo={@$pageNo}&amp;sortField=groupID&amp;sortOrder={if $sortField == 'groupID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.rank.group{/lang}{if $sortField == 'groupID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnRankPoints{if $sortField == 'neededPoints'} active{/if}"><div><a href="index.php?page=UserRankList&amp;pageNo={@$pageNo}&amp;sortField=neededPoints&amp;sortOrder={if $sortField == 'neededPoints' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.rank.neededPoints{/lang}{if $sortField == 'neededPoints'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnRankGender{if $sortField == 'gender'} active{/if}"><div><a href="index.php?page=UserRankList&amp;pageNo={@$pageNo}&amp;sortField=gender&amp;sortOrder={if $sortField == 'gender' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.rank.gender{/lang}{if $sortField == 'gender'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalColumns|isset}{@$additionalColumns}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$ranks item=rank}
				<tr class="{cycle values="container-1,container-2"}">
					<td class="columnIcon">
						{if $this->user->getPermission('admin.user.rank.canEditRank')}
							<a href="index.php?form=UserRankEdit&amp;rankID={@$rank.rankID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wcf.acp.rank.edit{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/editDisabledS.png" alt="" title="{lang}wcf.acp.rank.edit{/lang}" />
						{/if}
						{if $this->user->getPermission('admin.user.rank.canDeleteRank')}
							<a onclick="return confirm('{lang}wcf.acp.rank.delete.sure{/lang}')" href="index.php?action=UserRankDelete&amp;rankID={@$rank.rankID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.rank.delete{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.rank.delete{/lang}" />
						{/if}
						
						{if $rank.additionalButtons|isset}{@$rank.additionalButtons}{/if}
					</td>
					<td class="columnRankID columnID">{@$rank.rankID}</td>
					<td class="columnRankTitle columnText">
						{if $this->user->getPermission('admin.user.rank.canEditRank')}
							<a href="index.php?form=UserRankEdit&amp;rankID={@$rank.rankID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}{$rank.rankTitle}{/lang}</a>
						{else}
							{lang}{$rank.rankTitle}{/lang}
						{/if}
					</td>
					<td class="columnRankImage">{@$rank.object}</td>
					<td class="columnRankGroup columnText"><a href="index.php?form=GroupEdit&amp;groupID={@$rank.groupID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}{$rank.groupName}{/lang}</a></td>
					<td class="columnRankPoints columnNumbers">{#$rank.neededPoints}</td>
					<td class="columnRankGender">{if $rank.gender == 1}{lang}wcf.user.gender.male{/lang}{elseif $rank.gender == 2}{lang}wcf.user.gender.female{/lang}{/if}</td>
					
					{if $rank.additionalColumns|isset}{@$rank.additionalColumns}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>

	<div class="contentFooter">
		{@$pagesLinks}
		
		{if $this->user->getPermission('admin.user.rank.canAddRank')}
			<div class="largeButtons">
				<ul><li><a href="index.php?form=UserRankAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/userRankAddM.png" alt="" title="{lang}wcf.acp.rank.add{/lang}" /> <span>{lang}wcf.acp.rank.add{/lang}</span></a></li></ul>
			</div>
		{/if}
	</div>
{/if}

{include file='footer'}
