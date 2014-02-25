{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.register.newActivationCode{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
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
			<h2>{lang}wcf.user.register.newActivationCode{/lang}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{if $errorField}
		<p class="error">{lang}wcf.global.form.error{/lang}</p>
	{/if}
	
	<form method="post" action="index.php?page=Register">
		<div class="border content">
			<div class="container-1">
				<div class="formElement{if $errorField == 'username'} formError{/if}">
					<div class="formFieldLabel">
						<label for="username">{lang}wcf.user.username{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="username" value="{$username}" id="username" />
						{if $errorField == 'username'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'notFound'}{lang}wcf.user.error.username.notFound{/lang}{/if}
								{if $errorType == 'alreadyEnabled'}{lang}wcf.user.emailChange.error.emailAlreadyEnabled{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
	
				<div class="formElement{if $errorField == 'password'} formError{/if}">
					<div class="formFieldLabel">
						<label for="password">{lang}wcf.user.password{/lang}</label>
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
				</div>
				
				<div class="formElement{if $errorField == 'email'} formError{/if}">
					<div class="formFieldLabel">
						<label for="email">{lang}wcf.user.email{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="email" value="{$email}" id="email" />
						{if $errorField == 'email'}
							<p class="innerError">
								{if $errorType == 'notValid'}{lang}wcf.user.error.email.notValid{/lang}{/if}
								{if $errorType == 'notUnique'}{lang}wcf.user.error.email.notUnique{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc">
						<p>{lang}wcf.user.register.newActivationCode.email.description{/lang}</p>
					</div>
				</div>
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
			</div>
		</div>
	
		<div class="formSubmit">
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
			<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
			{@SID_INPUT_TAG}
			<input type="hidden" name="action" value="newReactivationCode" />
		</div>
	</form>

</div>

{include file='footer' sandbox=false}
</body>
</html>