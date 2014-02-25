{capture append='specialStyles'}
<style type="text/css">
	@import url("{@RELATIVE_WCF_DIR}acp/style/extra/styleEditor{if PAGE_DIRECTION == 'rtl'}-rtl{/if}.css");
</style>
{/capture}{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/styleL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.style.view{/lang}</h2>
	</div>
</div>

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=StyleList&pageNo=%d&packageID="|concat:PACKAGE_ID:SID_ARG_2ND_NOT_ENCODED}
	{if $this->user->getPermission('admin.style.canAddStyle')}
		<div class="largeButtons">
			<ul>
				<li><a href="index.php?form=StyleImport&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.style.import{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/styleImportM.png" alt="" /> <span>{lang}wcf.acp.menu.link.style.import{/lang}</span></a></li>
				<li><a href="index.php?form=StyleWriteFiles&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.style.writeFiles{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/styleRefreshM.png" alt="" /> <span>{lang}wcf.acp.menu.link.style.writeFiles{/lang}</span></a></li>
			</ul>
		</div>
	{/if}
</div>

{foreach from=$styles item=style}
	<div class="message content styleList">
		<div class="messageInner container-{cycle name='styles' values='1,2'}">
			
			<h3 class="subHeadline">
				{if $this->user->getPermission('admin.style.canEditStyle')}
					{if $style.disabled}
						<a href="index.php?action=StyleEnable&amp;styleID={@$style.styleID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/disabledS.png" alt="" title="{lang}wcf.acp.style.enable{/lang}" /></a>
					{elseif !$style.isDefault}
						<a href="index.php?action=StyleDisable&amp;styleID={@$style.styleID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/enabledS.png" alt="" title="{lang}wcf.acp.style.disable{/lang}" /></a>
					{else}
						<img src="{@RELATIVE_WCF_DIR}icon/defaultS.png" alt="" />
					{/if}
				{else}
					{if $style.disabled}
						<img src="{@RELATIVE_WCF_DIR}icon/disabledDisabledS.png" alt="" title="{lang}wcf.acp.style.enable{/lang}" />
					{elseif !$style.isDefault}
						<img src="{@RELATIVE_WCF_DIR}icon/enabledDisabledS.png" alt="" title="{lang}wcf.acp.style.disable{/lang}" />
					{else}
						<img src="{@RELATIVE_WCF_DIR}icon/defaultDisabledS.png" alt="" />
					{/if}
				{/if}
				<a href="index.php?form=StyleEdit&amp;styleID={@$style.styleID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{$style.styleName}</a>
			</h3>

			<div class="messageBody">
				<a href="index.php?form=StyleEdit&amp;styleID={@$style.styleID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" class="styleImage"><img src="{@RELATIVE_WCF_DIR}{if $style.image}{@$style.image}{else}images/styleNoPreview.jpg{/if}" alt="" /></a>
			
				{if $style.authorName != ''}
					<div class="formElement">
						<div class="formFieldLabel">
							<label>{lang}wcf.acp.style.authorName{/lang}</label>
						</div>
						<div class="formField">
							<span>{$style.authorName}</span>
						</div>
					</div>
				{/if}
				{if $style.copyright != ''}
					<div class="formElement">
						<div class="formFieldLabel">
							<label>{lang}wcf.acp.style.copyright{/lang}</label>
						</div>
						<div class="formField">
							<span>{$style.copyright}</span>
						</div>
					</div>
				{/if}
				{if $style.styleVersion != ''}
					<div class="formElement">
						<div class="formFieldLabel">
							<label>{lang}wcf.acp.style.version{/lang}</label>
						</div>
						<div class="formField">
							<span>{$style.styleVersion}</span>
						</div>
					</div>
				{/if}
				{if $style.styleDate != '0000-00-00'}
					<div class="formElement">
						<div class="formFieldLabel">
							<label>{lang}wcf.acp.style.date{/lang}</label>
						</div>
						<div class="formField">
							<span>{$style.styleDate}</span>
						</div>
					</div>
				{/if}
				{if $style.license != ''}
					<div class="formElement">
						<div class="formFieldLabel">
							<label>{lang}wcf.acp.style.license{/lang}</label>
						</div>
						<div class="formField">
							<span>{$style.license}</span>
						</div>
					</div>
				{/if}
				{if $style.authorURL != ''}
					<div class="formElement">
						<div class="formFieldLabel">
							<label>{lang}wcf.acp.style.authorURL{/lang}</label>
						</div>
						<div class="formField">
							<a href="{@RELATIVE_WCF_DIR}acp/dereferrer.php?url={$style.authorURL|rawurlencode}" class="externalURL">{$style.authorURL}</a>
						</div>
					</div>
				{/if}
				<div class="formElement">
					<div class="formFieldLabel">
						<label>{lang}wcf.acp.style.users{/lang}</label>
					</div>
					<div class="formField">
						{@$style.users}
					</div>
				</div>
				{if $style.styleDescription != ''}
					<div class="formElement">
						<div class="formFieldLabel">
							<label>{lang}wcf.acp.style.description{/lang}</label>
						</div>
						<div class="formField">
							<span>{$style.styleDescription}</span>
						</div>
					</div>
				{/if}
			</div>
			
			<div class="messageFooter">
				<div class="smallButtons">
					<ul>
						<li class="extraButton"><a href="#top" title="{lang}wcf.global.scrollUp{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/upS.png" alt="{lang}wcf.global.scrollUp{/lang}" /></a></li>
						{if $style.additionalButtons|isset}{@$style.additionalButtons}{/if}
						{if !$style.isDefault && $this->user->getPermission('admin.style.canEditStyle')}
							<li class="extraButton"><a href="index.php?action=StyleSetAsDefault&amp;styleID={@$style.styleID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/defaultS.png" alt="" title="" /> <span>{lang}wcf.acp.style.setAsDefault{/lang}</span></a></li>
						{/if}
						{if $this->user->getPermission('admin.style.canExportStyle')}
							<li><a href="index.php?form=StyleExport&amp;styleID={@$style.styleID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.style.export{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/exportS.png" alt="" /> <span>{lang}wcf.acp.style.export{/lang}</span></a></li>
						{/if}
						{if !$style.isDefault && $this->user->getPermission('admin.style.canDeleteStyle')}
							<li><a onclick="return confirm('{lang}wcf.acp.style.delete.sure{/lang}')" href="index.php?action=StyleDelete&amp;styleID={@$style.styleID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.style.delete{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" /> <span>{lang}wcf.acp.style.delete{/lang}</span></a></li>
						{/if}
						{if $this->user->getPermission('admin.style.canEditStyle')}
							<li><a href="index.php?form=StyleCopy&amp;styleID={@$style.styleID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.style.copyButton{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/copyS.png" alt="" /> <span>{lang}wcf.acp.style.copyButton{/lang}</span></a></li>
							<li><a href="index.php?form=StyleEdit&amp;styleID={@$style.styleID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.style.editButton{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" /> <span>{lang}wcf.acp.style.editButton{/lang}</span></a></li>
						{/if}
					</ul>
				</div>
			</div>
			<hr />
		</div>
	</div>
{/foreach}

<div class="contentFooter">
	{@$pagesLinks}
	{if $this->user->getPermission('admin.style.canAddStyle')}
		<div class="largeButtons">
			<ul>
				<li><a href="index.php?form=StyleImport&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.style.import{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/styleImportM.png" alt="" /> <span>{lang}wcf.acp.menu.link.style.import{/lang}</span></a></li>
				<li><a href="index.php?form=StyleWriteFiles&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.style.writeFiles{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/styleRefreshM.png" alt="" /> <span>{lang}wcf.acp.menu.link.style.writeFiles{/lang}</span></a></li>
			</ul>
		</div>
	{/if}
</div>

{include file='footer'}