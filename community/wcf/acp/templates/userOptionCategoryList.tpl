{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/userOptionCategoryL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.user.option.category.view{/lang}</h2>
	</div>
</div>

{if $deletedCategoryID}
	<p class="success">{lang}wcf.acp.user.option.category.delete.success{/lang}</p>	
{/if}

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=UserOptionCategoryList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:PACKAGE_ID:SID_ARG_2ND_NOT_ENCODED}
	
	{if $this->user->getPermission('admin.user.option.canAddOptionCategory')}
		<div class="largeButtons">
			<ul><li><a href="index.php?form=UserOptionCategoryAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/userOptionCategoryAddM.png" alt="" title="{lang}wcf.acp.user.option.category.add{/lang}" /> <span>{lang}wcf.acp.user.option.category.add{/lang}</span></a></li></ul>
		</div>
	{/if}
</div>

{if $userOptionCategories|count}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.user.option.category.view.count{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnUserOptionCategoryID{if $sortField == 'categoryID'} active{/if}" colspan="2"><div><a href="index.php?page=UserOptionCategoryList&amp;pageNo={@$pageNo}&amp;sortField=categoryID&amp;sortOrder={if $sortField == 'categoryID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.user.option.category.categoryID{/lang}{if $sortField == 'categoryID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnUserOptionCategoryName{if $sortField == 'categoryName'} active{/if}"><div><a href="index.php?page=UserOptionCategoryList&amp;pageNo={@$pageNo}&amp;sortField=categoryName&amp;sortOrder={if $sortField == 'categoryName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.user.option.category.name{/lang}{if $sortField == 'categoryName'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnUserOptionCategoryOptions{if $sortField == 'options'} active{/if}"><div><a href="index.php?page=UserOptionCategoryList&amp;pageNo={@$pageNo}&amp;sortField=options&amp;sortOrder={if $sortField == 'options' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.user.option.category.options{/lang}{if $sortField == 'options'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnUserOptionCategoryShowOrder{if $sortField == 'showOrder'} active{/if}"><div><a href="index.php?page=UserOptionCategoryList&amp;pageNo={@$pageNo}&amp;sortField=showOrder&amp;sortOrder={if $sortField == 'showOrder' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.user.option.category.showOrder{/lang}{if $sortField == 'showOrder'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalColumnHeads|isset}{@$additionalColumnHeads}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$userOptionCategories item=userOptionCategory}
				<tr class="{cycle values="container-1,container-2"}">
					<td class="columnIcon">
						{if $this->user->getPermission('admin.user.option.canEditOptionCategory')}
							<a href="index.php?form=UserOptionCategoryEdit&amp;categoryID={@$userOptionCategory->categoryID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wcf.acp.user.option.category.edit{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/editDisabledS.png" alt="" title="{lang}wcf.acp.user.option.category.edit{/lang}" />
						{/if}
						{if $this->user->getPermission('admin.user.option.canDeleteOptionCategory') && $userOptionCategory->options == 0}
							<a onclick="return confirm('{lang}wcf.acp.user.option.category.delete.sure{/lang}')" href="index.php?action=UserOptionCategoryDelete&amp;categoryID={@$userOptionCategory->categoryID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.user.option.category.delete{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.user.option.category.delete{/lang}" />
						{/if}
						
						{if $additionalButtons.$userOptionCategory->categoryID|isset}{@$additionalButtons.$userOptionCategory->categoryID}{/if}
					</td>
					<td class="columnUserOptionCategoryID columnID">{@$userOptionCategory->categoryID}</td>
					<td class="columnUserOptionCategoryName columnText">
						{if $this->user->getPermission('admin.user.option.canEditOptionCategory')}
							<a href="index.php?form=UserOptionCategoryEdit&amp;categoryID={@$userOptionCategory->categoryID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.user.option.category.edit{/lang}">{lang}wcf.user.option.category.{$userOptionCategory->categoryName}{/lang}</a>
						{else}
							{lang}wcf.user.option.category.{$userOptionCategory->categoryName}{/lang}
						{/if}
					</td>
					<td class="columnUserOptionCategoryOptions columnNumbers">{#$userOptionCategory->options}</td>
					<td class="columnUserOptionCategoryShowOrder columnNumbers">{#$userOptionCategory->showOrder}</td>
					
					{if $additionalColumns.$userOptionCategory->categoryID|isset}{@$additionalColumns.$userOptionCategory->categoryID}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>

	<div class="contentFooter">
		{@$pagesLinks}
		
		{if $this->user->getPermission('admin.user.option.canAddOptionCategory')}
			<div class="largeButtons">
				<ul><li><a href="index.php?form=UserOptionCategoryAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/userOptionCategoryAddM.png" alt="" title="{lang}wcf.acp.user.option.category.add{/lang}" /> <span>{lang}wcf.acp.user.option.category.add{/lang}</span></a></li></ul>
			</div>
		{/if}
	</div>
{/if}

{include file='footer'}
