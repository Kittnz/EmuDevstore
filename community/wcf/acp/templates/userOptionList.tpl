{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/userOptionL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.user.option.view{/lang}</h2>
	</div>
</div>

{if $deletedOptionID}
	<p class="success">{lang}wcf.acp.user.option.delete.success{/lang}</p>	
{/if}


<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=UserOptionList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:PACKAGE_ID:SID_ARG_2ND_NOT_ENCODED}
	
	{if $this->user->getPermission('admin.user.option.canAddOption')}
		<div class="largeButtons">
			<ul><li><a href="index.php?form=UserOptionAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/userOptionAddM.png" alt="" title="{lang}wcf.acp.user.option.add{/lang}" /> <span>{lang}wcf.acp.user.option.add{/lang}</span></a></li></ul>
		</div>
	{/if}
</div>
{if $options|count}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.user.option.view.count{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnUserOptionID{if $sortField == 'optionID'} active{/if}" colspan="2"><div><a href="index.php?page=UserOptionList&amp;pageNo={@$pageNo}&amp;sortField=optionID&amp;sortOrder={if $sortField == 'optionID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.user.option.optionID{/lang}{if $sortField == 'optionID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnUserOptionName{if $sortField == 'optionName'} active{/if}"><div><a href="index.php?page=UserOptionList&amp;pageNo={@$pageNo}&amp;sortField=optionName&amp;sortOrder={if $sortField == 'optionName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.user.option.optionName{/lang}{if $sortField == 'optionName'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnUserOptionCategoryName{if $sortField == 'categoryName'} active{/if}"><div><a href="index.php?page=UserOptionList&amp;pageNo={@$pageNo}&amp;sortField=categoryName&amp;sortOrder={if $sortField == 'categoryName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.user.option.categoryName{/lang}{if $sortField == 'categoryName'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnUserOptionType{if $sortField == 'optionType'} active{/if}"><div><a href="index.php?page=UserOptionList&amp;pageNo={@$pageNo}&amp;sortField=optionType&amp;sortOrder={if $sortField == 'optionType' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.user.option.optionType{/lang}{if $sortField == 'optionType'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnUserOptionShowOrder{if $sortField == 'showOrder'} active{/if}"><div><a href="index.php?page=UserOptionList&amp;pageNo={@$pageNo}&amp;sortField=showOrder&amp;sortOrder={if $sortField == 'showOrder' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.user.option.showOrder{/lang}{if $sortField == 'showOrder'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalColumns|isset}{@$additionalColumns}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$options item=option}
				<tr class="{cycle values="container-1,container-2"}">
					<td class="columnIcon">
						{if $this->user->getPermission('admin.user.option.canEditOption')}
							{if !$option.disabled}
								<a href="index.php?action=UserOptionDisable&amp;optionID={@$option.optionID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/enabledS.png" alt="" title="{lang}wcf.acp.user.option.disable{/lang}" /></a>
							{else}
								<a href="index.php?action=UserOptionEnable&amp;optionID={@$option.optionID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/disabledS.png" alt="" title="{lang}wcf.acp.user.option.enable{/lang}" /></a>
							{/if}
							
							<a href="index.php?form=UserOptionEdit&amp;optionID={@$option.optionID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wcf.acp.user.option.edit{/lang}" /></a>
						{else}
							{if !$option.disabled}
								<img src="{@RELATIVE_WCF_DIR}icon/enabledDisabledS.png" alt="" title="{lang}wcf.acp.user.option.disable{/lang}" />
							{else}
								<img src="{@RELATIVE_WCF_DIR}icon/disabledDisabledS.png" alt="" title="{lang}wcf.acp.user.option.enable{/lang}" />
							{/if}
							
							<img src="{@RELATIVE_WCF_DIR}icon/editDisabledS.png" alt="" title="{lang}wcf.acp.user.option.edit{/lang}" />
						{/if}
						{if $this->user->getPermission('admin.user.option.canDeleteOption')}
							<a onclick="return confirm('{lang}wcf.acp.user.option.delete.sure{/lang}')" href="index.php?action=UserOptionDelete&amp;optionID={@$option.optionID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.user.option.delete{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.user.option.delete{/lang}" />
						{/if}
						
						{if $option.additionalButtons|isset}{@$option.additionalButtons}{/if}
					</td>
					<td class="columnUserOptionID columnID">{@$option.optionID}</td>
					<td class="columnUserOptionName columnText">
						{if $this->user->getPermission('admin.user.option.canEditOption')}
							<a href="index.php?form=UserOptionEdit&amp;optionID={@$option.optionID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.user.option.{$option.optionName}{/lang}</a>
						{else}
							{lang}wcf.user.option.{$option.optionName}{/lang}
						{/if}
					</td>
					<td class="columnUserOptionCategoryName columnText">{lang}wcf.user.option.category.{$option.categoryName}{/lang}</td>
					<td class="columnUserOptionType">{$option.optionType}</td>
					<td class="columnUserOptionShowOrder columnNumbers">{#$option.showOrder}</td>
					
					{if $option.additionalColumns|isset}{@$option.additionalColumns}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>

	<div class="contentFooter">
		{@$pagesLinks}
		
		{if $this->user->getPermission('admin.user.option.canAddOption')}
			<div class="largeButtons">
				<ul><li><a href="index.php?form=UserOptionAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/userOptionAddM.png" alt="" title="{lang}wcf.acp.user.option.add{/lang}" /> <span>{lang}wcf.acp.user.option.add{/lang}</span></a></li></ul>
			</div>
		{/if}
	</div>
{/if}

{include file='footer'}
