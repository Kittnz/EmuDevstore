{include file='header'}
<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/dbExportL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.db.export.pageHeadline{/lang}</h2>
	</div>
</div>
{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}
<form method="post" action="index.php?form=DatabaseExport">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.db.export.settings{/lang}</legend>
				<div class="formElement{if $errorField == 'backupFileName'} formError{/if}" id="backupFileNameDiv">
					<div class="formFieldLabel">
						<label for="backupFileName">{lang}wcf.acp.db.export.backupFileName{/lang}</label>
					</div>
					<div class="formField">
						<input class="inputText" type="text" id="backupFileName" name="backupFileName" value="{$backupFile}" />
						{if $errorField == 'backupFileName'}
							<p class="innerError">
								{lang}wcf.acp.db.export.error.notValid{/lang}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="backupFileNameHelpMessage">
						<p>{lang}wcf.acp.db.export.backupFileName.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('backupFileName');
				//]]></script>
				
				<div class="formElement">
					<div class="formField">
						<label><input type="radio" id="exportAll" name="exportAll" onclick="if (IS_SAFARI) hideOptions('selectTables')" onfocus="hideOptions('selectTables')" value="1" {if $exportAll == 1}checked="checked" {/if}/> {lang}wcf.acp.db.export.all{/lang}</label>
					</div>
				</div>
				
				<div class="formElement">
					<div class="formField">
						<label><input type="radio" id="exportSelected" name="exportAll" onclick="if (IS_SAFARI) showOptions('selectTables')" onfocus="showOptions('selectTables')" value="0" {if $exportAll == 0}checked="checked" {/if}/> {lang}wcf.acp.db.export.selected{/lang}</label>
					</div>
				</div>
				
				<div class="formElement{if $errorField == 'exportTables'} formError{/if}" id="selectTables"{if $exportAll == 1} style="display: none"{/if}>
					<div class="formFieldLabel">
						<label for="exportTables">{lang}wcf.acp.db.export.tables{/lang}</label>
					</div>
					<div class="formField">
						<select name="exportTables[]" id="exportTables" multiple="multiple" size="10">
							{htmlOptions values=$loggedTables output=$loggedTables selected=$exportTables}
						</select>
						{if $errorField == 'exportTables'}
							<p class="innerError">
								{lang}wcf.global.error.empty{/lang}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc">
						<p>{lang}wcf.global.multiSelect{/lang}<p>
					</div>
				</div>
				
				<div class="formCheckBox formElement" id="isGzipDiv">
					<div class="formField">
						<label><input type="checkbox" id="isGzip" name="isGzip" value="1" {if $isGzip == 1}checked="checked" {/if}/> {lang}wcf.acp.db.gzipOn{/lang}</label>
					</div>
					<div class="formFieldDesc hidden" id="isGzipHelpMessage">
						<p>{lang}wcf.acp.db.gzipOn.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('isGzip');
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
 		<input type="hidden" name="action" value="export" />
 	</div>
</form>

{include file='footer'}