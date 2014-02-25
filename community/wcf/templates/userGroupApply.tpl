{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.userGroups.apply{/lang} - {lang}wcf.user.usercp{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
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
	
	<form method="post" action="index.php?form=UserGroupApply">
		<div class="border tabMenuContent">
			<div class="container-1">
				<h3 class="subHeadline">{lang}wcf.user.userGroups.apply{/lang}</h3>
				
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
	
					<div class="formElement{if $errorField == 'reason'} formError{/if}">
						<div class="formFieldLabel">
							<label for="reason">{lang}wcf.user.userGroups.application.reason{/lang}</label>
						</div>
						<div class="formField">
							<textarea rows="10" cols="40" name="reason" id="reason">{$reason}</textarea>
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
					
					{if $additionalFields|isset}{@$additionalFields}{/if}
				</fieldset>
			</div>
		</div>
		
		<div class="formSubmit">
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
			<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
			{@SID_INPUT_TAG}
			<input type="hidden" name="groupID" value="{@$groupID}" />
		</div>
	</form>

</div>

{include file='footer' sandbox=false}
</body>
</html>