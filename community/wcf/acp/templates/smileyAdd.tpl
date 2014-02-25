{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/smileyAddL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.smiley.add{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $savedSmilies|isset && $savedSmilies > 0}
	<p class="success">{lang}wcf.acp.smiley.add.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=SmileyList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/smileyM.png" alt="" title="{lang}wcf.acp.menu.link.smiley.view{/lang}" /> <span>{lang}wcf.acp.menu.link.smiley.view{/lang}</span></a></li></ul>
	</div>
</div>
<form enctype="multipart/form-data" method="post" action="index.php?form=SmileyAdd">
	
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.smiley.source{/lang}</legend>
				<div class="formElement{if $errorField == 'upload'} formError{/if}" id="uploadDiv">
					<div class="formFieldLabel">
						<label for="upload">{lang}wcf.acp.smiley.add.upload{/lang}</label>
					</div>
					<div class="formField">
						<input type="file" name="upload" id="upload" />
						{if $errorField == 'upload'}
							<div class="innerError">
								{if $errorType|is_array}
									{foreach from=$errorType item=error}
										<p>
											{$error.filename}:
											{if $error.errorType == 'noValidImage'}{lang}wcf.acp.smiley.error.noValidImage{/lang}{/if}
											{if $error.errorType == 'copyFailed'}{lang}wcf.acp.smiley.error.copyFailed{/lang}{/if}
										</p>
									{/foreach}
								{else}
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
									{if $errorType == 'uploadFailed'}{lang}wcf.acp.smiley.error.uploadFailed{/lang}{/if}
									{if $errorType == 'emptyArchive'}{lang}wcf.acp.smiley.error.emptyArchive{/lang}{/if}
									{if $errorType == 'noValidImage'}{lang}wcf.acp.smiley.error.noValidImage{/lang}{/if}
									{if $errorType == 'copyFailed'}{lang}wcf.acp.smiley.error.copyFailed{/lang}{/if}
								{/if}
							</div>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="uploadHelpMessage">
						{lang}wcf.acp.smiley.add.upload.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('upload');
				//]]></script>
				
				<div class="formElement{if $errorField == 'filename'} formError{/if}" id="filenameDiv">
					<div class="formFieldLabel">
						<label for="filename">{lang}wcf.acp.smiley.add.filename{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="filename" id="filename" value="{$filename}" />
						{if $errorField == 'filename'}
							<div class="innerError">
								{if $errorType|is_array}
									{foreach from=$errorType item=error}
										<p>
											{$error.filename}:
											{if $error.errorType == 'noValidImage'}{lang}wcf.acp.smiley.error.noValidImage{/lang}{/if}
											{if $error.errorType == 'copyFailed'}{lang}wcf.acp.smiley.error.copyFailed{/lang}{/if}
										</p>
									{/foreach}
								{else}
									{if $errorType == 'notFound'}{lang}wcf.global.error.file.notFound{/lang}{/if}
									{if $errorType == 'emptyFolder'}{lang}wcf.acp.smiley.error.emptyFolder{/lang}{/if}
									{if $errorType == 'noValidImage'}{lang}wcf.acp.smiley.error.noValidImage{/lang}{/if}
									{if $errorType == 'copyFailed'}{lang}wcf.acp.smiley.error.copyFailed{/lang}{/if}
								{/if}
							</div>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="filenameHelpMessage">
						{lang}wcf.acp.smiley.add.filename.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('filename');
				//]]></script>
				
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.smiley.data{/lang}</legend>
				
				{if $availableSmileyCategories|count}
					<div class="formElement{if $errorField == 'smileyCategoryID'} formError{/if}" id="smileyCategoryIDDiv">
						<div class="formFieldLabel">
							<label for="smileyCategoryID">{lang}wcf.acp.smiley.category{/lang}</label>
						</div>
						<div class="formField">
							<select name="smileyCategoryID" id="smileyCategoryID">
								<option value="0"></option>
								{htmlOptions options=$availableSmileyCategories selected=$smileyCategoryID}
							</select>
							{if $errorField == 'smileyCategoryID'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
						<div class="formFieldDesc hidden" id="smileyCategoryIDHelpMessage">
							{lang}wcf.acp.smiley.category.description{/lang}
						</div>
					</div>
					<script type="text/javascript">//<![CDATA[
						inlineHelp.register('smileyCategoryID');
					//]]></script>
				{/if}
				
				<div class="formElement{if $errorField == 'title'} formError{/if}" id="titleDiv">
					<div class="formFieldLabel">
						<label for="title">{lang}wcf.acp.smiley.title{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="title" id="title" value="{$title}" />
						{if $errorField == 'title'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="titleHelpMessage">
						{lang}wcf.acp.smiley.title.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('title');
				//]]></script>
				
				<div class="formElement{if $errorField == 'code'} formError{/if}" id="codeDiv">
					<div class="formFieldLabel">
						<label for="code">{lang}wcf.acp.smiley.code{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="code" id="code" value="{$code}" />
						{if $errorField == 'code'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'notUnique'}{lang}wcf.acp.smiley.error.code.notUnique{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="codeHelpMessage">
						{lang}wcf.acp.smiley.code.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('code');
				//]]></script>
				
				<div class="formElement" id="showOrderDiv">
					<div class="formFieldLabel">
						<label for="showOrder">{lang}wcf.acp.smiley.showOrder{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="showOrder" id="showOrder" value="{@$showOrder}" />
					</div>
					<div class="formFieldDesc hidden" id="showOrderHelpMessage">
						{lang}wcf.acp.smiley.showOrder.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('showOrder');
				//]]></script>
				
			</fieldset>
			
			{if $additionalFields|isset}{@$additionalFields}{/if}
		</div>
	</div>
		
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
 		{@SID_INPUT_TAG}
 	</div>
</form>

{include file='footer'}