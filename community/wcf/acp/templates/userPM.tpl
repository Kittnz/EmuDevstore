{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/pmL.png" alt="" />
	<div class="headlineContainer">
		<h2>
			{if $action == 'all'}
				{lang}wcf.acp.user.sendPM.all{/lang}
			{elseif $action == 'group'}
				{lang}wcf.acp.user.sendPM.group{/lang}
			{else}
				{lang}wcf.acp.user.sendPM{/lang}
			{/if}
		</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.user.sendPM.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul>
			<li><a href="index.php?page=UserList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/usersM.png" alt="" title="{lang}wcf.acp.menu.link.user.list{/lang}" /> <span>{lang}wcf.acp.menu.link.user.list{/lang}</span></a></li>
			<li><a href="index.php?form=UserSearch&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/searchM.png" alt="" title="{lang}wcf.acp.user.search{/lang}" /> <span>{lang}wcf.acp.user.search{/lang}</span></a></li>
		</ul>
	</div>
</div>
<form method="post" action="index.php?form=UserPM">
	<div class="border content">
		<div class="container-1">
	
			{if $action == ''}
				<fieldset>
					<legend>{lang}wcf.acp.user.sendPM.markedUsers{/lang}</legend>
					
					<div>
						{implode from=$users item=$user}<a href="index.php?form=UserEdit&amp;userID={@$user->userID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{$user}</a>{/implode}
					</div>
				</fieldset>	
			{/if}
			{if $action == 'group'}
				<fieldset>
					<legend>{lang}wcf.acp.user.sendPM.groups{/lang}</legend>
					
					<div class="formGroup{if $errorField == 'groupIDs'} formError{/if}">
						<div class="formGroupLabel">
							<label>{lang}wcf.acp.user.groups{/lang}</label>
						</div>
						<div class="formGroupField">
							<fieldset>
								<legend>{lang}wcf.acp.user.groups{/lang}</legend>
								
								<div class="formField">
									{htmlCheckboxes options=$groups name=groupIDs selected=$groupIDs}
								</div>
							</fieldset>
							{if $errorField == 'groupIDs'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
				</fieldset>	
			{/if}
			<fieldset>
				<legend>{lang}wcf.acp.user.sendPM.message{/lang}</legend>
				
				<div>
					<div class="formElement{if $errorField == 'subject'} formError{/if}" id="subjectDiv">
						<div class="formFieldLabel">
							<label for="subject">{lang}wcf.pm.subject{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="subject" name="subject" value="{$subject}" />
							{if $errorField == 'subject'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
						<div class="formFieldDesc hidden" id="subjectHelpMessage">
							<p>{lang}wcf.pm.subject.description{/lang}</p>
						</div>
					</div>
					<script type="text/javascript">//<![CDATA[
						inlineHelp.register('subject');
					//]]></script>
					
					<div class="formElement{if $errorField == 'text'} formError{/if}" id="textDiv">
						<div class="formFieldLabel">
							<label for="text">{lang}wcf.pm.text{/lang}</label>
						</div>
						<div class="formField">
							<textarea id="text" name="text" rows="15" cols="40">{$text}</textarea>
							{if $errorField == 'text'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
						<div class="formFieldDesc hidden" id="textHelpMessage">
							<p>{lang}wcf.pm.text.description{/lang}</p>
						</div>
					</div>
					<script type="text/javascript">//<![CDATA[
						inlineHelp.register('text');
					//]]></script>
				</div>
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
			</fieldset>
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
		<input type="hidden" name="action" value="{@$action}" />
 		{@SID_INPUT_TAG}
 		<input type="hidden" name="userIDs" value="{@$userIDs}" />
 	</div>
</form>

{include file='footer'}