{include file="documentHeader"}
<head>
	<title>{lang}wcf.contact.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
	
{include file='header' sandbox=false}
	
<div id="main">
	
	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
	</ul>
	
	<div class="mainHeadline">
		<img src="{icon}contactL.png{/icon}" alt="" /> 
		<div class="headlineContainer">
			<h2>{lang}wcf.contact.title{/lang}</h2>
			<p>{lang}wcf.contact.description{/lang}</p>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{if $errorField}
		<p class="error">{lang}wcf.global.form.error{/lang}</p>
	{/if}
	
	<form method="post" action="index.php?form=Contact">
		<div class="border content">
			<div class="container-1">
				
				{if !$this->user->userID}
					<fieldset>
						<legend>{lang}wcf.contact.daten{/lang}</legend>

						<div class="formElement{if $errorField == 'username'} formError{/if}">
							<div class="formFieldLabel">
								<label for="username">{lang}wcf.contact.username{/lang}</label>
							</div>
							<div class="formField">
								<input type="text" class="inputText" name="username" value="{$username}" id="username" tabindex="{counter name='tabindex'}" />
								{if $errorField == 'username'}
									<p class="innerError">
										{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
									</p>
								{/if}
							</div>
						</div>

						<div class="formElement{if $errorField == 'email'} formError{/if}">
							<div class="formFieldLabel">
								<label for="email">{lang}wcf.user.mail.senderEmail{/lang}</label>
							</div>
							<div class="formField">
								<input type="text" class="inputText" name="email" value="{$email}" id="email" tabindex="{counter name='tabindex'}" />
								{if $errorField == 'email'}
									<p class="innerError">
										{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
										{if $errorType == 'notValid'}{lang}wcf.user.error.email.notValid{/lang}{/if}
									</p>
								{/if}
							</div>
						</div>
					</fieldset>
				{/if}
				
				<fieldset>
					<legend>{lang}wcf.user.mail.message{/lang}</legend>
					
					<div class="formElement{if $errorField == 'subject'} formError{/if}">
						<div class="formFieldLabel">
							<label for="subject">{lang}wcf.user.mail.subject{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" name="subject" value="{$subject}" id="subject" tabindex="{counter name='tabindex'}" />
							{if $errorField == 'subject'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>

					<div class="formElement{if $errorField == 'message'} formError{/if}">
						<div class="formFieldLabel">
							<label for="message">{lang}wcf.user.mail.message{/lang}</label>
						</div>
						<div class="formField">
							<textarea rows="15" cols="40" name="message" id="message" tabindex="{counter name='tabindex'}">{$message}</textarea>
							{if $errorField == 'message'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
				</fieldset>
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
				
				{include file='captcha'}
			</div>
		</div>
		
		<div class="formSubmit">
			<input type="submit" name="send" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" tabindex="{counter name='tabindex'}" />
			<input type="reset" name="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" tabindex="{counter name='tabindex'}" />
			{@SID_INPUT_TAG}
		</div>
	</form>
</div>
	
{include file='footer' sandbox=false}
</body>
</html>