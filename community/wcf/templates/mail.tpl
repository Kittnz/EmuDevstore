{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.mail.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
	</ul>
	<div class="mainHeadline">
		<img src="{icon}emailL.png{/icon}" alt="" /> 
		<div class="headlineContainer">
			<h2>{lang}wcf.user.mail.title{/lang}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{if $errorField}
		<p class="error">{lang}wcf.global.form.error{/lang}</p>
	{/if}
	
	<form method="post" action="index.php?form=Mail&amp;userID={@$user->userID}">
		<div class="border content">
			<div class="container-1">
				{if $this->user->userID}
					<div>
						<div class="formField">
							<label><input type="checkbox" name="showAddress" value="1" {if $showAddress == 1}checked="checked"{/if} /> {lang}wcf.user.mail.showAddress{/lang}</label>
						</div>
					</div>
				{else}
					<div class="formElement{if $errorField == 'email'} formError{/if}">
						<div class="formFieldLabel">
							<label for="email">{lang}wcf.user.mail.senderEmail{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" name="email" value="{$email}" id="email" />
							{if $errorField == 'email'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
									{if $errorType == 'notValid'}{lang}wcf.user.error.email.notValid{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>	
				{/if}
						
				<fieldset>
					<legend>{lang}wcf.user.mail.message{/lang}</legend>
					<div class="formElement{if $errorField == 'subject'} formError{/if}">
						<div class="formFieldLabel">
							<label for="subject">{lang}wcf.user.mail.subject{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" name="subject" value="{$subject}" id="subject" />
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
							<textarea rows="15" cols="40" name="message" id="message">{$message}</textarea>
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
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
			<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		</div>
	
		{@SID_INPUT_TAG}
	</form>

</div>

{include file='footer' sandbox=false}
</body>
</html>