{include file='header'}

<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Suggestion.class.js"></script>
<script src="{@RELATIVE_WCF_DIR}js/Calendar.class.js" type="text/javascript"></script>
<script type="text/javascript">
	//<![CDATA[
	var calendar = new Calendar('{$monthList}', '{$weekdayList}', {@$startOfWeek});
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/attachmentMassProcessingL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.attachment.massProcessing{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $affectedAttachments|isset}
	<p class="success">{lang}wcf.acp.attachment.massProcessing.success{/lang}</p>	
{/if}

<p class="warning">{lang}wcf.acp.attachment.massProcessing.warning{/lang}</p>

<form method="post" action="index.php?form=AttachmentsMassProcessing">
	<div class="border content">
		<div class="container-1">
	
			<h3 class="subHeadline">{lang}wcf.acp.attachment.massProcessing.conditions{/lang}</h3>
				
			<fieldset>
				<legend>{lang}wcf.acp.attachment.massProcessing.conditions.time{/lang}</legend>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="timeAfterDay">{lang}wcf.acp.attachment.massProcessing.conditions.time.after{/lang}</label>
					</div>
					<div class="formField">
						<div class="floatedElement">
							<label for="timeAfterDay">{lang}wcf.global.date.day{/lang}</label>
							{htmlOptions options=$dayOptions selected=$timeAfterDay id=timeAfterDay name=timeAfterDay}
						</div>
						
						<div class="floatedElement">
							<label for="timeAfterMonth">{lang}wcf.global.date.month{/lang}</label>
							{htmlOptions options=$monthOptions selected=$timeAfterMonth id=timeAfterMonth name=timeAfterMonth}
						</div>
						
						<div class="floatedElement">
							<label for="timeAfterYear">{lang}wcf.global.date.year{/lang}</label>
							<input id="timeAfterYear" class="inputText fourDigitInput" type="text" name="timeAfterYear" value="{@$timeAfterYear}" maxlength="4" />
						</div>
						
						<div class="floatedElement">
							<a id="timeAfterButton"><img src="{@RELATIVE_WCF_DIR}icon/datePickerOptionsM.png" alt="" /></a>
							<div id="timeAfterCalendar" class="inlineCalendar"></div>
							<script type="text/javascript">
								//<![CDATA[
								calendar.init('timeAfter');
								//]]>
							</script>
						</div>
					</div>
				</div>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="timeBeforeDay">{lang}wcf.acp.attachment.massProcessing.conditions.time.before{/lang}</label>
					</div>
					<div class="formField">
						<div class="floatedElement">
							<label for="timeBeforeDay">{lang}wcf.global.date.day{/lang}</label>
							{htmlOptions options=$dayOptions selected=$timeBeforeDay id=timeBeforeDay name=timeBeforeDay}
						</div>
						
						<div class="floatedElement">
							<label for="timeBeforeMonth">{lang}wcf.global.date.month{/lang}</label>
							{htmlOptions options=$monthOptions selected=$timeBeforeMonth id=timeBeforeMonth name=timeBeforeMonth}
						</div>
						
						<div class="floatedElement">
							<label for="timeBeforeYear">{lang}wcf.global.date.year{/lang}</label>
							<input id="timeBeforeYear" class="inputText fourDigitInput" type="text" name="timeBeforeYear" value="{@$timeBeforeYear}" maxlength="4" />
						</div>
						
						<div class="floatedElement">
							<a id="timeBeforeButton"><img src="{@RELATIVE_WCF_DIR}icon/datePickerOptionsM.png" alt="" /></a>
							<div id="timeBeforeCalendar" class="inlineCalendar"></div>
							<script type="text/javascript">
								//<![CDATA[
								calendar.init('timeBefore');
								//]]>
							</script>
						</div>
					</div>
				</div>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="lastDownloadTimeAfterDay">{lang}wcf.acp.attachment.massProcessing.conditions.lastDownloadTime.after{/lang}</label>
					</div>
					<div class="formField">
						<div class="floatedElement">
							<label for="lastDownloadTimeAfterDay">{lang}wcf.global.date.day{/lang}</label>
							{htmlOptions options=$dayOptions selected=$lastDownloadTimeAfterDay id=lastDownloadTimeAfterDay name=lastDownloadTimeAfterDay}
						</div>
						
						<div class="floatedElement">
							<label for="lastDownloadTimeAfterMonth">{lang}wcf.global.date.month{/lang}</label>
							{htmlOptions options=$monthOptions selected=$lastDownloadTimeAfterMonth id=lastDownloadTimeAfterMonth name=lastDownloadTimeAfterMonth}
						</div>
						
						<div class="floatedElement">
							<label for="lastDownloadTimeAfterYear">{lang}wcf.global.date.year{/lang}</label>
							<input id="lastDownloadTimeAfterYear" class="inputText fourDigitInput" type="text" name="lastDownloadTimeAfterYear" value="{@$lastDownloadTimeAfterYear}" maxlength="4" />
						</div>
						
						<div class="floatedElement">
							<a id="lastDownloadTimeAfterButton"><img src="{@RELATIVE_WCF_DIR}icon/datePickerOptionsM.png" alt="" /></a>
							<div id="lastDownloadTimeAfterCalendar" class="inlineCalendar"></div>
							<script type="text/javascript">
								//<![CDATA[
								calendar.init('lastDownloadTimeAfter');
								//]]>
							</script>
						</div>
					</div>
				</div>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="lastDownloadTimeBeforeDay">{lang}wcf.acp.attachment.massProcessing.conditions.lastDownloadTime.before{/lang}</label>
					</div>
					<div class="formField">
						<div class="floatedElement">
							<label for="lastDownloadTimeBeforeDay">{lang}wcf.global.date.day{/lang}</label>
							{htmlOptions options=$dayOptions selected=$lastDownloadTimeBeforeDay id=lastDownloadTimeBeforeDay name=lastDownloadTimeBeforeDay}
						</div>
						
						<div class="floatedElement">
							<label for="lastDownloadTimeBeforeMonth">{lang}wcf.global.date.month{/lang}</label>
							{htmlOptions options=$monthOptions selected=$lastDownloadTimeBeforeMonth id=lastDownloadTimeBeforeMonth name=lastDownloadTimeBeforeMonth}
						</div>
						
						<div class="floatedElement">
							<label for="lastDownloadTimeBeforeYear">{lang}wcf.global.date.year{/lang}</label>
							<input id="lastDownloadTimeBeforeYear" class="inputText fourDigitInput" type="text" name="lastDownloadTimeBeforeYear" value="{@$lastDownloadTimeBeforeYear}" maxlength="4" />
						</div>
						
						<div class="floatedElement">
							<a id="lastDownloadTimeBeforeButton"><img src="{@RELATIVE_WCF_DIR}icon/datePickerOptionsM.png" alt="" /></a>
							<div id="lastDownloadTimeBeforeCalendar" class="inlineCalendar"></div>
							<script type="text/javascript">
								//<![CDATA[
								calendar.init('lastDownloadTimeBefore');
								//]]>
							</script>
						</div>
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.attachment.massProcessing.conditions.filesize{/lang}</legend>
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="sizeMoreThan">{lang}wcf.acp.attachment.massProcessing.conditions.filesize.moreThan{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="sizeMoreThan" id="sizeMoreThan" value="{$sizeMoreThan}" />
					</div>
				</div>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="sizeLessThan">{lang}wcf.acp.attachment.massProcessing.conditions.filesize.lessThan{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="sizeLessThan" id="sizeLessThan" value="{$sizeLessThan}" />
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.attachment.massProcessing.conditions.downloads{/lang}</legend>
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="downloadsMoreThan">{lang}wcf.acp.attachment.massProcessing.conditions.downloads.moreThan{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="downloadsMoreThan" id="downloadsMoreThan" value="{$downloadsMoreThan}" />
					</div>
				</div>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="downloadsLessThan">{lang}wcf.acp.attachment.massProcessing.conditions.downloads.lessThan{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="downloadsLessThan" id="downloadsLessThan" value="{$downloadsLessThan}" />
					</div>
				</div>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.attachment.massProcessing.conditions.other{/lang}</legend>
				<div class="formElement{if $errorField == 'uploadedBy'} formError{/if}">
					<div class="formFieldLabel">
						<label for="uploadedBy">{lang}wcf.acp.attachment.massProcessing.conditions.uploadedBy{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="uploadedBy" id="uploadedBy" value="{$uploadedBy}" />
						<script type="text/javascript">
							//<![CDATA[
							suggestion.init('uploadedBy')
							//]]>
						</script>
						{if $errorField == 'uploadedBy'}
							<div class="innerError">
								{if $errorType|is_array}
									{foreach from=$errorType item=error}
										<p>
											{if $error.type == 'notFound'}{lang username=$error.username}wcf.user.error.username.notFound{/lang}{/if}
										</p>
									{/foreach}
								{/if}
							</div>
						{/if}
					</div>
				</div>
				
				{if $availableContainerTypes|count > 1}
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="containerType">{lang}wcf.attachment.containerType{/lang}</label>
						</div>
						<div class="formField">
							<select name="containerType" id="containerType">
								<option value=""></option>
								{foreach from=$availableContainerTypes item=availableContainerType}
									<option value="{$availableContainerType.containerType}"{if $availableContainerType.containerType == $containerType} selected="selected"{/if}>{lang}wcf.attachment.containerType.{$availableContainerType.containerType}{/lang}</option>
								{/foreach}
							</select>
						</div>
					</div>
				{/if}
				
				{if $availableFileTypes|count > 1}
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="fileType">{lang}wcf.attachment.fileType{/lang}</label>
						</div>
						<div class="formField">
							<select name="fileType" id="fileType">
								<option value=""></option>
								{foreach from=$availableFileTypes item=availableFileType}
									<option value="{$availableFileType.fileType}"{if $availableFileType.fileType == $fileType} selected="selected"{/if}>{$availableFileType.fileType}</option>
								{/foreach}
							</select>
						</div>
					</div>
				{/if}
			</fieldset>
			
			{if $additionalConditions|isset}{@$additionalConditions}{/if}
		</div>
	</div>
	<div class="border content">
		<div class="container-1">	
			<h3 class="subHeadline">{lang}wcf.acp.attachment.massProcessing.action{/lang}</h3>
				
			<div class="formGroup{if $errorField == 'action'} formError{/if}">
				<div class="formGroupLabel">
					<label>{lang}wcf.acp.attachment.massProcessing.action{/lang}</label>
				</div>
				<div class="formGroupField">
					<fieldset>
						<legend>{lang}wcf.acp.attachment.massProcessing.action{/lang}</legend>
						<div class="formField">
							<ul class="formOptionsLong">
								<li><label><input type="radio" name="action" value="delete" {if $action == 'delete'}checked="checked" {/if}/> {lang}wcf.acp.attachment.massProcessing.action.delete{/lang}</label></li>
								{if $additionalActions|isset}{@$additionalActions}{/if}
							</ul>
						</div>
						{if $errorField == 'action'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</fieldset>
				</div>
			</div>
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