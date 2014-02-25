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
	
	<form method="post" action="index.php?form=UserGroupLeaderApplicationEdit">
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
							{lang}wcf.user.userGroups.application.time{/lang}
						</p>
						<p class="formField">
							{@$application->applicationTime|time}
						</p>
					</div>
					
					{if $application->groupLeaderID}
						<div class="formElement">
							<p class="formFieldLabel">
								{lang}wcf.user.userGroups.application.lastEditBy{/lang}
							</p>
							<p class="formField">
								<a href="index.php?page=User&amp;userID={@$application->groupLeaderID}{@SID_ARG_2ND}">{$application->groupLeader}</a>
							</p>
						</div>
					{/if}
	
					<div class="formElement">
						<p class="formFieldLabel">
							{lang}wcf.user.userGroups.application.reason{/lang}
						</p>
						<p class="formField">
							{$application->reason}
						</p>
					</div>
					
					<div class="formElement{if $errorField == 'applicationStatus'} formError{/if}">
						<div class="formFieldLabel">
							<label for="applicationStatus">{lang}wcf.user.userGroups.application.status{/lang}</label>
						</div>
						<div class="formField">
							<select name="applicationStatus" id="applicationStatus">
								<option value="0"{if $applicationStatus == 0} selected="selected"{/if}>{lang}wcf.user.userGroups.application.status.0{/lang}</option>
								<option value="1"{if $applicationStatus == 1} selected="selected"{/if}>{lang}wcf.user.userGroups.application.status.1{/lang}</option>
								<option value="2"{if $applicationStatus == 2} selected="selected"{/if}>{lang}wcf.user.userGroups.application.status.2{/lang}</option>
								<option value="3"{if $applicationStatus == 3} selected="selected"{/if}>{lang}wcf.user.userGroups.application.status.3{/lang}</option>
							</select>
							{if $errorField == 'applicationStatus'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
					
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="reply">{lang}wcf.user.userGroups.application.reply{/lang}</label>
						</div>
						<div class="formField">
							<textarea rows="10" cols="40" name="reply" id="reply">{$reply}</textarea>
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
			<input type="hidden" name="applicationID" value="{@$applicationID}" />
		</div>
	</form>

</div>

{include file='footer' sandbox=false}
</body>
</html>