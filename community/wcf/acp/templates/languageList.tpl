{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/languageL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.language.view{/lang}</h2>
	</div>
</div>

{if $deletedLanguageID}
	<p class="success">{lang}wcf.acp.language.delete.success{/lang}</p>	
{/if}

{if $deletedVariable}
	<p class="success">{lang}wcf.acp.language.variable.delete.success{/lang}</p>	
{/if}

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=LanguageList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:PACKAGE_ID:SID_ARG_2ND_NOT_ENCODED}
	
	{if $this->user->getPermission('admin.language.canAddLanguage')}
		<div class="largeButtons">
			<ul><li><a href="index.php?form=LanguageAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/languageAddM.png" alt="" title="{lang}wcf.acp.language.add{/lang}" /> <span>{lang}wcf.acp.language.add{/lang}</span></a></li></ul>
		</div>
	{/if}
</div>

{if $languages|count}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.language.view.count{/lang} </h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th colspan="2"{if $sortField == 'languageID'} class="active"{/if}><div><a href="index.php?page=LanguageList&amp;pageNo={@$pageNo}&amp;sortField=languageID&amp;sortOrder={if $sortField == 'languageID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.language.languageID{/lang}{if $sortField == 'languageID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th{if $sortField == 'languageCode'} class="active"{/if}><div><a href="index.php?page=LanguageList&amp;pageNo={@$pageNo}&amp;sortField=languageCode&amp;sortOrder={if $sortField == 'languageCode' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.language.name{/lang}{if $sortField == 'languageCode'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th{if $sortField == 'languageEncoding'} class="active"{/if}><div><a href="index.php?page=LanguageList&amp;pageNo={@$pageNo}&amp;sortField=languageEncoding&amp;sortOrder={if $sortField == 'languageEncoding' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.language.encoding{/lang}{if $sortField == 'languageEncoding'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th{if $sortField == 'users'} class="active"{/if}><div><a href="index.php?page=LanguageList&amp;pageNo={@$pageNo}&amp;sortField=users&amp;sortOrder={if $sortField == 'users' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.language.users{/lang}{if $sortField == 'users'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th{if $sortField == 'variables'} class="active"{/if}><div><a href="index.php?page=LanguageList&amp;pageNo={@$pageNo}&amp;sortField=variables&amp;sortOrder={if $sortField == 'variables' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.language.variables{/lang}{if $sortField == 'variables'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th{if $sortField == 'customVariables'} class="active"{/if}><div><a href="index.php?page=LanguageList&amp;pageNo={@$pageNo}&amp;sortField=customVariables&amp;sortOrder={if $sortField == 'customVariables' && $sortOrder == 'ASC'}DESC{else}ASC{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.acp.language.customVariables{/lang}{if $sortField == 'customVariables'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalColumns|isset}{@$additionalColumns}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$languages item=language}
				<tr class="{cycle values="container-1,container-2"}">
					<td class="columnIcon">
						{if $this->user->getPermission('admin.language.canEditLanguage')}
							<a href="index.php?form=LanguageEdit&amp;languageID={@$language.languageID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wcf.acp.language.edit{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/editDisabledS.png" alt="" />
						{/if}
						{if $this->user->getPermission('admin.language.canDeleteLanguage') && !$language.isDefault}
							<a onclick="return confirm('{lang}wcf.acp.language.delete.sure{/lang}')" href="index.php?action=LanguageDelete&amp;languageID={@$language.languageID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.language.delete{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png" alt="" />
						{/if}
						
						{if $this->user->getPermission('admin.language.canEditLanguage')}
							<a href="index.php?form=LanguageExport&amp;languageID={@$language.languageID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/exportS.png" alt="" title="{lang}wcf.acp.language.export{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/exportDisabledS.png" alt="" />
						{/if}
						
						{if $this->user->getPermission('admin.language.canEditLanguage') && !$language.isDefault}
							<a href="index.php?action=LanguageSetAsDefault&amp;languageID={@$language.languageID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/defaultS.png" alt="" title="{lang}wcf.acp.language.setAsDefault{/lang}" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/defaultDisabledS.png" alt="" />
						{/if}
						
						{if $language.additionalButtons|isset}{@$language.additionalButtons}{/if}
					</td>
					<td class="columnID">{@$language.languageID}</td>
					<td class="columnText">
						<img src="{@RELATIVE_WCF_DIR}icon/language{@$language.languageCode|ucfirst}S.png" alt="" />
						{if $this->user->getPermission('admin.language.canEditLanguage')}
							<a href="index.php?form=LanguageEdit&amp;languageID={@$language.languageID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{lang}wcf.global.language.{@$language.languageCode}{/lang} ({@$language.languageCode})</a>
						{else}
							{lang}wcf.global.language.{@$language.languageCode}{/lang} ({@$language.languageCode})
						{/if}
					</td>
					<td class="columnText">{$language.languageEncoding}</td>
					<td class="columnNumbers">{#$language.users}</td>
					<td class="columnNumbers">{#$language.variables}</td>
					<td class="columnNumbers">{if $language.customVariables > 0 && $this->user->getPermission('admin.language.canEditLanguage')}<a href="index.php?form=LanguageEdit&amp;languageID={@$language.languageID}&amp;customVariables=1&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{#$language.customVariables}</a>{else}{#$language.customVariables}{/if}</td>
					
					{if $language.additionalColumns|isset}{@$language.additionalColumns}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>

	<div class="contentFooter">
		{@$pagesLinks}
		
		{if $this->user->getPermission('admin.language.canAddLanguage')}
			<div class="largeButtons">
				<ul><li><a href="index.php?form=LanguageAdd&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/languageAddM.png" alt="" title="{lang}wcf.acp.language.add{/lang}" /> <span>{lang}wcf.acp.language.add{/lang}</span></a></li></ul>
			</div>
		{/if}
	</div>
{/if}

{include file='footer'}
