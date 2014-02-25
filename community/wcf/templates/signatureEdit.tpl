{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.signature.title{/lang} - {lang}wcf.user.usercp{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	
	{include file='headInclude' sandbox=false}
	<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/TabbedPane.class.js"></script>
	{if $canUseBBCodes}{include file="wysiwyg"}{/if}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	{capture append=userMessages}
		{if $errorField}
			<p class="error">{lang}wcf.global.form.error{/lang}</p>
		{/if}
		
		{if $success|isset}
			<p class="success">{lang}wcf.user.signature.success{/lang}</p>
		{/if}
		
		{if $this->user->disableSignature}
			<p class="error">{lang}wcf.user.signature.error.disabled{/lang}</p>
		{/if}
	{/capture}
	
	{include file="userCPHeader"}
	
	<form method="post" action="index.php?form=SignatureEdit">
		<div class="border tabMenuContent">
			<div class="container-1">
				<h3 class="subHeadline">{lang}wcf.user.signature.title{/lang}</h3>
					
				{if $signatureCache}
					<fieldset>
						<legend>{lang}wcf.user.signature.current{/lang}</legend>
						<div>
							{@$signatureCache}
						</div>
					</fieldset>
				{/if}
				
				{if $signaturePreview}
					<fieldset>
						<legend>{lang}wcf.user.signature.preview{/lang}</legend>
						<div>
							{@$signaturePreview}
						</div>
					</fieldset>
				{/if}
				
				{if !$this->user->disableSignature}
					<fieldset>
						<legend>{lang}wcf.user.signature{/lang}</legend>
						<div class="editorFrame formElement{if $errorField == 'text'} formError{/if}" id="textDiv">
							<div class="formFieldLabel">
								<label for="text">{lang}wcf.user.signature{/lang}</label>
							</div>
							<div class="formField">
								<textarea name="text" id="text" cols="40" rows="15" tabindex="{counter name='tabindex'}">{$text}</textarea>
								{if $errorField == 'text'}
									<p class="innerError">
										{if $errorType == 'tooLong'}{lang}wcf.user.signature.error.tooLong{/lang}{/if}
										{if $errorType == 'tooManyImages'}{lang}wcf.user.signature.error.tooManyImages{/lang}{/if}
										{if $errorType == 'censoredWordsFound'}{lang}wcf.message.error.censoredWordsFound{/lang}{/if}
										{if $errorType|is_array}
											{foreach from=$errorType item=error}
												{if $error.errorType == 'tooLarge'}{lang}wcf.user.signature.error.imageTooLarge{/lang}{/if}
											{/foreach}
										{/if}
									</p>
								{/if}
							</div>
						</div>
						
						{include file="messageFormTabs"}
					</fieldset>
				{/if}
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
			</div>
		</div>
		{if !$this->user->disableSignature}
			<div class="formSubmit">
				{@SID_INPUT_TAG}
				<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" tabindex="{counter name='tabindex'}" />
				<input type="submit" name="preview" accesskey="p" value="{lang}wcf.global.button.preview{/lang}" tabindex="{counter name='tabindex'}" />
				<input type="reset" name="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" tabindex="{counter name='tabindex'}" />
			</div>
		{/if}
	</form>

</div>

{include file='footer' sandbox=false}
</body>
</html>