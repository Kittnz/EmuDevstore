{include file='header'}
<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/dbImportL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.db.import.pageHeadline{/lang}</h2>
	</div>
</div>
{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<p class="info">{lang}wcf.acp.db.import.description{/lang}</p>

<form enctype="multipart/form-data" method="post" action="index.php?form=DatabaseImport">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.db.import.settings{/lang}</legend>
				
				<div class="formElement{if $errorField == 'upload'} formError{/if}" id="uploadDiv">
					<div class="formFieldLabel">
						<label for="upload">{lang}wcf.acp.db.import.file.upload{/lang}</label>
					</div>
					<div class="formField">
						<input type="file" id="upload" name="upload" value="{if $upload|isset && $upload != ''}{$upload}{/if}" />
						{if $errorField == 'upload'}
							<p class="innerError">
								{lang}wcf.acp.db.import.error.notValid{/lang}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="uploadHelpMessage">
						{lang}wcf.acp.db.import.file.upload.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('upload');
				//]]></script>
				
				<div class="formElement{if $errorField == 'importFile'} formError{/if}" id="importFileDiv">
					<div class="formFieldLabel">
						<label for="importFile">{lang}wcf.acp.db.import.file.path{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="importFile" name="importFile" value="{if $importFile|isset && $importFile != ''}{$importFile}{/if}" />
						{if $errorField == 'importFile'}
							<p class="innerError">
								{lang}wcf.acp.db.import.error.notValid{/lang}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="importFileHelpMessage">
						{lang}wcf.acp.db.import.file.path.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('importFile');
				//]]></script>
				
				<div class="formCheckBox formElement">
					<div class="formField">
						<label><input type="checkbox" id="ignoreErrors" name="ignoreErrors" /> {lang}wcf.acp.db.import.error.ignore{/lang}</label>
					</div>
				</div>
			</fieldset>
			
			{if $additionalFields|isset}{@$additionalFields}{/if}
		</div>
	</div>
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
 		{@SID_INPUT_TAG}
 		<input type="hidden" name="action" value="import" />
 	</div>
</form>


{include file='footer'}