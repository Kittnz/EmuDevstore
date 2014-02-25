{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.login{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	<script type="text/javascript">
		//<![CDATA[
		onloadEvents.push(function() { if (!'{$username|encodeJS}' || '{$errorField}' == 'username') document.getElementById('loginUsername').focus(); else document.getElementById('loginPassword').focus(); });
		//]]>
	</script>
	
	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
	</ul>
	
	<div class="mainHeadline">
		<img src="{icon}loginL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2> {lang}wcf.user.login{/lang}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{if $errorField}
		<p class="error">{lang}wcf.global.form.error{/lang}</p>
	{/if}
	
	<form method="post" action="index.php?form=UserLogin">
		<div class="border content">
			<div class="container-1">
				<div class="formElement{if $errorField == 'username'} formError{/if}">
					<div class="formFieldLabel">
						<label for="loginUsername">{lang}wcf.user.username{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="loginUsername" value="{$username}" id="loginUsername" />
						{if $errorField == 'username'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'notFound'}{lang}wcf.user.error.username.notFound{/lang}{/if}
								{if $errorType == 'notEnabled'}{lang}wcf.user.login.error.username.notEnabled{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
				
				<div class="formElement{if $errorField == 'password'} formError{/if}">
					<div class="formFieldLabel">
						<label for="loginPassword">{lang}wcf.user.password{/lang}</label>
					</div>
					<div class="formField">
						<input type="password" class="inputText" name="loginPassword" value="{$password}" id="loginPassword" />
						{if $errorField == 'password'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'false'}{lang}wcf.user.login.error.password.false{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
				
				{if $supportsPersistentLogins}
					<div class="formElement">
						<div class="formField">
							<label><input type="checkbox" name="useCookies" value="1" {if $useCookies == 1}checked="checked" {/if}/> {lang}wcf.user.login.useCookies{/lang}</label>
						</div>
					</div>
				{/if}
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
				{include file='captcha'}
				
				<div class="formElement">
					<div class="formField">
						<ul class="formOptionsLong">
							<li><img src="{icon}lostPasswordS.png{/icon}" alt="" /> <a href="index.php?form=LostPassword{@SID_ARG_2ND}">{lang}wcf.user.lostPassword.title{/lang}</a></li>
							{if !REGISTER_DISABLED && REGISTER_ACTIVATION_METHOD == 1}<li><img src="{icon}registerS.png{/icon}" alt="" /> <a href="index.php?page=Register&amp;action=enable{@SID_ARG_2ND}">{lang}wcf.user.register.activation{/lang}</a></li>{/if}
						</ul>
					</div>
				</div>
				
			</div>
		</div>
			
		<div class="formSubmit">
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
			<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		</div>
		
		<input type="hidden" name="url" value="{$url}" />
		{@SID_INPUT_TAG}
	</form>

</div>

{include file='footer' sandbox=false}
</body>
</html>