{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/avatarCategoryL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.avatar.category.view{/lang}</h2>
	</div>
</div>

{if $deletedAvatarCategoryID}
	<p class="success">{lang}wcf.acp.avatar.category.delete.success{/lang}</p>	
{/if}

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=AvatarCategoryList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:PACKAGE_ID:SID_ARG_2ND_NOT_ENCODED}
	
	{if $this->user->getPermission('admin.avatar.canAddAvatarCategory')}
		<div class="largeButtons">
			<ul><li><a href="index.php?form=AvatarCategoryAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.avatar.category.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/avatarCategoryAddM.png" alt="" /> <span>{lang}wcf.acp.avatar.category.add{/lang}</span></a></li></ul>
		</div>
	{/if}
</div>

{if $avatarCategories|count}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.avatar.category.view.count{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnAvatarCategoryID{if $sortField == 'avatarCategoryID'} active{/if}" colspan="2"><div><a href="index.php?page=AvatarCategoryList&amp;pageNo={@$pageNo}&amp;sortField=avatarCategoryID&amp;sortOrder={if $sortField == 'avatarCategoryID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.avatar.category.avatarCategoryID{/lang}{if $sortField == 'avatarCategoryID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnAvatarCategoryTitle{if $sortField == 'title'} active{/if}"><div><a href="index.php?page=AvatarCategoryList&amp;pageNo={@$pageNo}&amp;sortField=title&amp;sortOrder={if $sortField == 'title' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.avatar.category.title{/lang}{if $sortField == 'title'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnAvatars{if $sortField == 'avatars'} active{/if}"><div><a href="index.php?page=AvatarCategoryList&amp;pageNo={@$pageNo}&amp;sortField=avatars&amp;sortOrder={if $sortField == 'avatars' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.avatar.category.avatars{/lang}{if $sortField == 'avatars'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnShowOrder{if $sortField == 'showOrder'} active{/if}"><div><a href="index.php?page=AvatarCategoryList&amp;pageNo={@$pageNo}&amp;sortField=showOrder&amp;sortOrder={if $sortField == 'showOrder' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.avatar.category.showOrder{/lang}{if $sortField == 'showOrder'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalColumnHeads|isset}{@$additionalColumnHeads}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$avatarCategories item=avatarCategory}
				<tr class="{cycle values="container-1,container-2"}">
					<td class="columnIcon">
						{if $this->user->getPermission('admin.avatar.canEditAvatarCategory')}
							<a href="index.php?form=AvatarCategoryEdit&amp;avatarCategoryID={@$avatarCategory->avatarCategoryID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wcf.acp.avatar.category.edit{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/editDisabledS.png" alt="" title="{lang}wcf.acp.avatar.category.edit{/lang}" />
						{/if}
						{if $this->user->getPermission('admin.avatar.canDeleteAvatarCategory')}
							<a onclick="return confirm('{lang}wcf.acp.avatar.category.delete.sure{/lang}')" href="index.php?action=AvatarCategoryDelete&amp;avatarCategoryID={@$avatarCategory->avatarCategoryID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.avatar.category.delete{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.avatar.category.delete{/lang}" />
						{/if}
						
						{if $additionalButtons.$avatarCategory->avatarCategoryID|isset}{@$additionalButtons.$avatarCategory->avatarCategoryID}{/if}
					</td>
					<td class="columnAvatarCategoryID columnID">{@$avatarCategory->avatarCategoryID}</td>
					<td class="columnAvatarCategoryTitle columnText">
						{if $this->user->getPermission('admin.avatar.canEditAvatarCategory')}
							<a href="index.php?form=AvatarCategoryEdit&amp;avatarCategoryID={@$avatarCategory->avatarCategoryID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.avatar.category.edit{/lang}">{lang}{$avatarCategory->title}{/lang}</a>
						{else}
							{lang}{$avatarCategory->title}{/lang}
						{/if}
					</td>
					<td class="columnAvatars columnNumbers">{#$avatarCategory->avatars}</td>
					<td class="columnShowOrder columnNumbers">{#$avatarCategory->showOrder}</td>
					
					{if $additionalColumns.$avatarCategory->avatarCategoryID|isset}{@$additionalColumns.$avatarCategory->avatarCategoryID}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>

	<div class="contentFooter">
		{@$pagesLinks}
		
		{if $this->user->getPermission('admin.avatar.canAddAvatarCategory')}
			<div class="largeButtons">
				<ul><li><a href="index.php?form=AvatarCategoryAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.avatar.category.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/avatarCategoryAddM.png" alt="" /> <span>{lang}wcf.acp.avatar.category.add{/lang}</span></a></li></ul>
			</div>
		{/if}
	</div>
{else}
	<div class="border content">
		<div class="container-1">
			<p>{lang}wcf.acp.avatar.category.view.count.noEntries{/lang}</p>
		</div>
	</div>
{/if}

{include file='footer'}
