{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/smileyCategoryL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.smiley.category.view{/lang}</h2>
	</div>
</div>

{if $deletedSmileyCategoryID}
	<p class="success">{lang}wcf.acp.smiley.category.delete.success{/lang}</p>	
{/if}

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=SmileyCategoryList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:PACKAGE_ID:SID_ARG_2ND_NOT_ENCODED}
	
	{if $this->user->getPermission('admin.smiley.canAddSmileyCategory')}
		<div class="largeButtons">
			<ul><li><a href="index.php?form=SmileyCategoryAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/smileyCategoryAddM.png" alt="" title="{lang}wcf.acp.smiley.category.add{/lang}" /> <span>{lang}wcf.acp.smiley.category.add{/lang}</span></a></li></ul>
		</div>
	{/if}
</div>

{if $smileyCategories|count}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.smiley.category.view.count{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnSmileyCategoryID{if $sortField == 'smileyCategoryID'} active{/if}" colspan="2"><div><a href="index.php?page=SmileyCategoryList&amp;pageNo={@$pageNo}&amp;sortField=smileyCategoryID&amp;sortOrder={if $sortField == 'smileyCategoryID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.smiley.category.smileyCategoryID{/lang}{if $sortField == 'smileyCategoryID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnSmileyCategoryTitle{if $sortField == 'title'} active{/if}"><div><a href="index.php?page=SmileyCategoryList&amp;pageNo={@$pageNo}&amp;sortField=title&amp;sortOrder={if $sortField == 'title' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.smiley.category.title{/lang}{if $sortField == 'title'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnSmileys{if $sortField == 'smileys'} active{/if}"><div><a href="index.php?page=SmileyCategoryList&amp;pageNo={@$pageNo}&amp;sortField=smileys&amp;sortOrder={if $sortField == 'smileys' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.smiley.category.smileys{/lang}{if $sortField == 'smileys'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnShowOrder{if $sortField == 'showOrder'} active{/if}"><div><a href="index.php?page=SmileyCategoryList&amp;pageNo={@$pageNo}&amp;sortField=showOrder&amp;sortOrder={if $sortField == 'showOrder' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.smiley.category.showOrder{/lang}{if $sortField == 'showOrder'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalColumnHeads|isset}{@$additionalColumnHeads}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$smileyCategories item=smileyCategory}
				<tr class="{cycle values="container-1,container-2"}">
					<td class="columnIcon">
						{if $this->user->getPermission('admin.smiley.canEditSmileyCategory')}
							{if $smileyCategory->disabled}
								<a href="index.php?action=SmileyCategoryEnable&amp;smileyCategoryID={@$smileyCategory->smileyCategoryID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/disabledS.png" alt="" title="{lang}wcf.acp.smiley.category.enable{/lang}" /></a>
							{else}
								<a href="index.php?action=SmileyCategoryDisable&amp;smileyCategoryID={@$smileyCategory->smileyCategoryID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/enabledS.png" alt="" title="{lang}wcf.acp.smiley.category.disable{/lang}" /></a>
							{/if}
						{else}
							{if $smileyCategory->disabled}
								<img src="{@RELATIVE_WCF_DIR}icon/disabledDisabledS.png" alt="" title="{lang}wcf.acp.smiley.category.enable{/lang}" />
							{else}
								<img src="{@RELATIVE_WCF_DIR}icon/enabledDisabledS.png" alt="" title="{lang}wcf.acp.smiley.category.disable{/lang}" />
							{/if}
						{/if}
						
						{if $this->user->getPermission('admin.smiley.canEditSmileyCategory')}
							<a href="index.php?form=SmileyCategoryEdit&amp;smileyCategoryID={@$smileyCategory->smileyCategoryID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wcf.acp.smiley.category.edit{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/editDisabledS.png" alt="" title="{lang}wcf.acp.smiley.category.edit{/lang}" />
						{/if}
						
						{if $this->user->getPermission('admin.smiley.canDeleteSmileyCategory')}
							<a onclick="return confirm('{lang}wcf.acp.smiley.category.delete.sure{/lang}')" href="index.php?action=SmileyCategoryDelete&amp;smileyCategoryID={@$smileyCategory->smileyCategoryID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.smiley.category.delete{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.smiley.category.delete{/lang}" />
						{/if}
						
						{if $additionalButtons.$smileyCategory->smileyCategoryID|isset}{@$additionalButtons.$smileyCategory->smileyCategoryID}{/if}
					</td>
					<td class="columnSmileyCategoryID columnID">{@$smileyCategory->smileyCategoryID}</td>
					<td class="columnSmileyCategoryTitle columnText">
						{if $this->user->getPermission('admin.smiley.canEditSmileyCategory')}
							<a href="index.php?form=SmileyCategoryEdit&amp;smileyCategoryID={@$smileyCategory->smileyCategoryID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.smiley.category.edit{/lang}">{lang}{$smileyCategory->title}{/lang}</a>
						{else}
							{lang}{$smileyCategory->title}{/lang}
						{/if}
					</td>
					<td class="columnSmileys columnNumbers">{#$smileyCategory->smileys}</td>
					<td class="columnShowOrder columnNumbers">{#$smileyCategory->showOrder}</td>
					
					{if $additionalColumns.$smileyCategory->smileyCategoryID|isset}{@$additionalColumns.$smileyCategory->smileyCategoryID}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>

	<div class="contentFooter">
		{@$pagesLinks}
		
		{if $this->user->getPermission('admin.smiley.canAddSmileyCategory')}
			<div class="largeButtons">
				<ul><li><a href="index.php?form=SmileyCategoryAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/smileyCategoryAddM.png" alt="" title="{lang}wcf.acp.smiley.category.add{/lang}" /> <span>{lang}wcf.acp.smiley.category.add{/lang}</span></a></li></ul>
			</div>
		{/if}
	</div>
{else}
	<div class="border content">
		<div class="container-1">
			<p>{lang}wcf.acp.smiley.category.view.count.noEntries{/lang}</p>
		</div>
	</div>
{/if}

{include file='footer'}
