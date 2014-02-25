{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.accountManagement.title{/lang} - {lang}wcf.user.usercp{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	{capture append=userMessages}
		{if $errorField}
			<p class="error">{lang}wcf.global.form.error{/lang}</p>
		{/if}
		
		{if $success|isset && $success|count > 0}
			<div class="success">
				{foreach from=$success item=successMessage}
					<p>{lang}{@$successMessage}{/lang}</p>
				{/foreach}
			</div>
		{/if}
	{/capture}
	
	{include file="userCPHeader"}
	
	<form method="post" action="index.php?form=AccountManagement">
		<div class="border tabMenuContent">
			<div class="container-1">
				<h3 class="subHeadline">{lang}wcf.user.accountManagement.title{/lang}</h3>
				
				<p class="warning">{lang}wcf.user.accountManagement.edit.warning{/lang}</p>
				
				<div class="formElement{if $errorField == 'password'} formError{/if}">
					<div class="formFieldLabel">
						<label for="password">{lang}wcf.user.accountManagement.password{/lang}</label>
					</div>
					<div class="formField">
						<input type="password" class="inputText" name="password" value="{$password}" id="password" />
						{if $errorField == 'password'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'false'}{lang}wcf.user.login.error.password.false{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc">
						<p>{lang}wcf.user.accountManagement.password.description{/lang}</p>
					</div>
				</div>
				
				{if $canChangeUsername}
					<fieldset>
						<legend><label for="username">{lang}wcf.user.rename.title{/lang}</label></legend>
						
						<div class="formElement{if $errorField == 'username'} formError{/if}">
							<div class="formFieldLabel">
								<label for="username">{lang}wcf.user.username{/lang}</label>
							</div>
							
							<div class="formField">
								<input type="text" class="inputText" name="username" value="{$username}" id="username" />
								
								{if $errorField == 'username'}
									<p class="innerError">
										{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
										{if $errorType == 'notValid'}{lang}wcf.user.error.username.notValid{/lang}{/if}
										{if $errorType == 'notUnique'}{lang}wcf.user.error.username.notUnique{/lang}{/if}
										{if $errorType == 'notChanged'}{lang}wcf.user.rename.error.username.notChanged{/lang}{/if}
									</p>
								{/if}
							</div>
							{if $renamePeriod > 0}
								<div class="formFieldDesc">
									<p>{lang}wcf.user.rename.description{/lang}</p>
								</div>
							{/if}
						</div>
					</fieldset>
				{/if}
				
				<fieldset>
					<legend><label for="newPassword">{lang}wcf.user.passwordChange.title{/lang}</label></legend>
					
					<div class="formElement{if $errorField == 'newPassword'} formError{/if}">
						<div class="formFieldLabel">
							<label for="newPassword">{lang}wcf.user.passwordChange.newPassword{/lang}</label>
						</div>
						<div class="formField">
							<input type="password" class="inputText" name="newPassword" value="{$newPassword}" id="newPassword" />
							
							{if $errorField == 'newPassword'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
									{if $errorType == 'notSecure'}{lang}wcf.user.error.password.notSecure{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
					
					<div class="formElement{if $errorField == 'confirmNewPassword'} formError{/if}">
						<div class="formFieldLabel">
							<label for="confirmNewPassword">{lang}wcf.user.passwordChange.confirmNewPassword{/lang}</label>
						</div>
						<div class="formField">
							<input type="password" class="inputText" name="confirmNewPassword" value="{$confirmNewPassword}" id="confirmNewPassword" />
							
							{if $errorField == 'confirmNewPassword'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
									{if $errorType == 'notEqual'}{lang}wcf.user.error.confirmPassword.notEqual{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
				</fieldset>
				
				{if $this->user->getPermission('user.profile.canChangeEmail')}
					<fieldset>
						<legend><label for="email">{lang}wcf.user.emailChange.title{/lang}</label></legend>
						
						<div class="formElement{if $errorField == 'email'} formError{/if}">
							<div class="formFieldLabel">
								<label for="email">{lang}wcf.user.email{/lang}</label>
							</div>
							<div class="formField">
								<input type="text" class="inputText" name="email" value="{$email}" id="email" />
								
								{if $errorField == 'email'}
									<p class="innerError">
										{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
										{if $errorType == 'notValid'}{lang}wcf.user.error.email.notValid{/lang}{/if}
										{if $errorType == 'notUnique'}{lang}wcf.user.error.email.notUnique{/lang}{/if}
										{if $errorType == 'notChanged'}{lang}wcf.user.emailChange.error.email.notChanged{/lang}{/if}
									</p>
								{/if}
							</div>
						</div>
						
						<div class="formElement{if $errorField == 'confirmEmail'} formError{/if}">
							<div class="formFieldLabel">
								<label for="confirmEmail">{lang}wcf.user.confirmEmail{/lang}</label>
							</div>
							<div class="formField">
								<input type="text" class="inputText" name="confirmEmail" value="{$confirmEmail}" id="confirmEmail" />
								
								{if $errorField == 'confirmEmail'}
									<p class="innerError">
										{if $errorType == 'notEqual'}{lang}wcf.user.error.confirmEmail.notEqual{/lang}{/if}
									</p>
								{/if}
							</div>
						</div>
						
						{if REGISTER_ACTIVATION_METHOD == 1 && $this->user->reactivationCode != 0}
							<div class="formElement">
								<div class="formField">
									<ul class="formOptionsLong">
										<li><img src="{icon}emailS.png{/icon}" alt="" /> <a href="index.php?page=Register&amp;action=reenable{@SID_ARG_2ND}">{lang}wcf.user.emailChange.reactivation.title{/lang}</a></li>
									</ul>
								</div>
							</div>
						{/if}
					</fieldset>
				{/if}
				
				{if $this->user->getPermission('user.profile.canQuit')}
					<fieldset>
						<legend>{lang}wcf.user.quit.title{/lang}</legend>
					
						{if $quitStarted}
							<div class="formElement">
								<div class="formField">
									<label><input type="checkbox" name="cancelQuit" value="1" {if $cancelQuit == 1}checked="checked" {/if}/> {lang}wcf.user.quit.cancel{/lang}</label>
								</div>
							</div>
						{else}
							<div class="formElement">
								<div class="formField">
									<label><input type="checkbox" name="quit" value="1" {if $quit == 1}checked="checked" {/if}/> {lang}wcf.user.quit{/lang}</label>
								</div>
								<div class="formFieldDesc">
									<p>{lang}wcf.user.quit.description{/lang}</p>
								</div>
							</div>
						{/if}
					</fieldset>
				{/if}
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
			</div>
		</div>
		
		<div class="formSubmit">
			{@SID_INPUT_TAG}
			{@SECURITY_TOKEN_INPUT_TAG}
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
			<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		</div>
	</form>
	
</div>

{include file='footer' sandbox=false}
</body>
</html>