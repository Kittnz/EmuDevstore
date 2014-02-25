{capture append='specialStyles'}
<style type="text/css">
	@import url("{@RELATIVE_WCF_DIR}acp/style/extra/styleEditor{if PAGE_DIRECTION == 'rtl'}-rtl{/if}.css");
</style>
{/capture}{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/styleImportL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.style.import{/lang}</h2>
	</div>
</div>

<div class="message content styleList">
	<div class="messageInner container-{cycle name='styles' values='1,2'}">
		
		<h3 class="subHeadline">
			{$style.name}
		</h3>
		
		<div class="messageBody">
			<span class="styleImage"><img src="{@RELATIVE_WCF_DIR}{if $style.image}tmp/{@$style.image}{else}images/styleNoPreview.jpg{/if}" alt="" /></span>
		
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
			{if $style.version != ''}
				<div class="formElement">
					<div class="formFieldLabel">
						<label>{lang}wcf.acp.style.version{/lang}</label>
					</div>
					<div class="formField">
						<span>{$style.version}</span>
					</div>
				</div>
			{/if}
			{if $style.date != '0000-00-00'}
				<div class="formElement">
					<div class="formFieldLabel">
						<label>{lang}wcf.acp.style.date{/lang}</label>
					</div>
					<div class="formField">
						<span>{$style.date}</span>
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
			{if $style.description != ''}
				<div class="formElement">
					<div class="formFieldLabel">
						<label>{lang}wcf.acp.style.description{/lang}</label>
					</div>
					<div class="formField">
						<span>{$style.description}</span>
					</div>
				</div>
			{/if}
		</div>
		
		<hr />
	</div>
</div>
	
<form method="post" action="index.php?form=StyleImport">	
	<div class="formSubmit">
		<input type="button" accesskey="c" value="{lang}wcf.global.button.back{/lang}" onclick="document.location.href=fixURL('index.php?form=StyleImport&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}')" />
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.next{/lang}" />
		
		<input type="hidden" name="filename" value="{$filename}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
		<input type="hidden" name="destinationStyleID" value="{@$destinationStyleID}" />
	 	{@SID_INPUT_TAG}
	</div>
</form>

{include file='footer'}