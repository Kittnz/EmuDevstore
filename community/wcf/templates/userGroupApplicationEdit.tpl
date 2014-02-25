{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.userGroups.application.edit{/lang} - {lang}wcf.user.usercp{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	{capture append=userMessages}
		{if $errorField}
			<p class="error">{lang}wcf.global.form.error{/lang}</p>
		{/if}
	{/capture}
	
	{include file="userCPHeader"}
	
	<form method="post" action="index.php?form=UserGroupApplicationEdit">
		<div class="border tabMenuContent">
			<div class="container-1">
				<h3 class="subHeadline">{lang}wcf.user.userGroups.application.edit{/lang}</h3>
				
				<fieldset>
					<legend>{lang}wcf.user.userGroups.application{/lang}</legend>
					
					<div class="formElement">
						<p class="formFieldLabel">
							{lang}wcf.user.userGroups.application.for{/lang}
						</p>
						<p class="formField">
							{lang}{$group->groupName}{/lang}
						</p>
					</div>
					
					<div class="formElement">
						<p class="formFieldLabel">
							{lang}wcf.user.userGroups.application.status{/lang}
						</p>
						<p class="formField">
							{lang}wcf.user.userGroups.application.status.{@$application->applicationStatus}{/lang}
						</p>
					</div>
					
					<div class="formElement">
						<p class="formFieldLabel">
							{lang}wcf.user.userGroups.application.time{/lang}
						</p>
						<p class="formField">
							{@$application->applicationTime|time}
						</p>
					</div>
	
					<div class="formElement{if $errorField == 'reason'} formError{/if}">
						<div class="formFieldLabel">
							<label for="reason">{lang}wcf.user.userGroups.application.reason{/lang}</label>
						</div>
						<div class="formField">
							<textarea rows="10" cols="40" name="reason" id="reason"{if $application->applicationStatus > 0} disabled="disabled"{/if}>{$reason}</textarea>
							{if $errorField == 'reason'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
					
					<div class="formElement">
						<div class="formField">
							<label><input type="checkbox" name="enableNotification" value="1" {if $enableNotification == 1}checked="checked"{/if} /> {lang}wcf.user.userGroups.application.enableNotification{/lang}</label>
						</div>
					</div>
					
					{if $application->applicationStatus == 2 && $application->reply}
						<div class="formElement">
							<p class="formFieldLabel">
								{lang}wcf.user.userGroups.application.reply{/lang}
							</p>
							<p class="formField">
								{$application->reply}
							</p>
						</div>
					{/if}
					
					{if $additionalFields|isset}{@$additionalFields}{/if}
				</fieldset>
			</div>
		</div>
		
		<div class="formSubmit">
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
			{if $application->applicationStatus == 0}
				<input onclick="return confirm('{lang}wcf.user.userGroups.application.withdraw.sure{/lang}')" type="submit" name="withdraw" accesskey="w" value="{lang}wcf.user.userGroups.application.withdraw{/lang}" />
			{/if}
			<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
			{@SID_INPUT_TAG}
			<input type="hidden" name="applicationID" value="{@$applicationID}" />
		</div>
	</form>

</div>

{include file='footer' sandbox=false}
</body>
</html>