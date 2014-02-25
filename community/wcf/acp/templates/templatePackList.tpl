{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/templatePackL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.template.pack.view{/lang}</h2>
	</div>
</div>

{if $deletedTemplatePackID}
	<p class="success">{lang}wcf.acp.template.pack.delete.success{/lang}</p>	
{/if}

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=TemplatePackList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:PACKAGE_ID:SID_ARG_2ND_NOT_ENCODED}
	<div class="largeButtons">
		<ul><li><a href="index.php?form=TemplatePackAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/templatePackAddM.png" alt="" title="{lang}wcf.acp.template.pack.add{/lang}" /> <span>{lang}wcf.acp.template.pack.add{/lang}</span></a></li></ul>
	</div>
</div>
	
{if $templatePacks|count}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.template.pack.view.count{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnTemplatePackID{if $sortField == 'templatePackID'} active{/if}" colspan="2"><div><a href="index.php?page=TemplatePackList&amp;pageNo={@$pageNo}&amp;sortField=templatePackID&amp;sortOrder={if $sortField == 'templatePackID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.template.pack.templatePackID{/lang}{if $sortField == 'templatePackID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnTemplatePackName{if $sortField == 'templatePackName'} active{/if}"><div><a href="index.php?page=TemplatePackList&amp;pageNo={@$pageNo}&amp;sortField=templatePackName&amp;sortOrder={if $sortField == 'templatePackName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.template.pack.name{/lang}{if $sortField == 'templatePackName'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnTemplatePackFolderName{if $sortField == 'templatePackFolderName'} active{/if}"><div><a href="index.php?page=TemplatePackList&amp;pageNo={@$pageNo}&amp;sortField=templatePackFolderName&amp;sortOrder={if $sortField == 'templatePackFolderName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.template.pack.folderName{/lang}{if $sortField == 'templatePackFolderName'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnTemplates{if $sortField == 'templates'} active{/if}"><div><a href="index.php?page=TemplatePackList&amp;pageNo={@$pageNo}&amp;sortField=templates&amp;sortOrder={if $sortField == 'templates' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.template.pack.templates{/lang}{if $sortField == 'templates'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalColumns|isset}{@$additionalColumns}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$templatePacks item=templatePack}
				<tr class="{cycle values="container-1,container-2"}">
					<td class="columnIcon">
						{if $this->user->getPermission('admin.template.canEditTemplatePack')}
							<a href="index.php?form=TemplatePackEdit&amp;templatePackID={@$templatePack.templatePackID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wcf.acp.template.pack.edit{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/editDisabledS.png" alt="" title="{lang}wcf.acp.template.pack.edit{/lang}" />
						{/if}
						{if $this->user->getPermission('admin.template.canDeleteTemplatePack')}
							<a onclick="return confirm('{lang}wcf.acp.template.pack.delete.sure{/lang}')" href="index.php?action=TemplatePackDelete&amp;templatePackID={@$templatePack.templatePackID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.template.pack.delete{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" title="{lang}wcf.acp.template.pack.delete{/lang}" />
						{/if}
						
						{if $templatePack.additionalButtons|isset}{@$templatePack.additionalButtons}{/if}
					</td>
					<td class="columnTemplatePackID columnID">{@$templatePack.templatePackID}</td>
					<td class="columnTemplatePackName columnText">{$templatePack.templatePackName}</td>
					<td class="columnTemplatePackFolderName columnText">{$templatePack.templatePackFolderName}</td>
					<td class="columnTemplates columnNumbers"><a href="index.php?page=TemplateList&amp;templatePackID={@$templatePack.templatePackID}&packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.template.view{/lang}">{#$templatePack.templates}</a></td>
					
					{if $templatePack.additionalColumns|isset}{@$templatePack.additionalColumns}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
	
	<div class="contentFooter">
		{@$pagesLinks}
		<div class="largeButtons">
			<ul><li><a href="index.php?form=TemplatePackAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/templatePackAddM.png" alt="" title="{lang}wcf.acp.template.pack.add{/lang}" /> <span>{lang}wcf.acp.template.pack.add{/lang}</span></a></li></ul>
		</div>
	</div>
{else}
	<div class="border content">
		<div class="container-1">
			<p>{lang}wcf.acp.template.pack.view.count.noEntries{/lang}</p>
		</div>
	</div>
{/if}

{include file='footer'}
