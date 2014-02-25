{include file="documentHeader"}
<head>
	<title>{lang}wcf.global.error.permissionDenied.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	<p class="error">{lang}wcf.global.error.permissionDenied{/lang}</p>

	{if !$this->user->userID && !LOGIN_USE_CAPTCHA}
		<form method="post" action="index.php?form=UserLogin">
			<div class="border content">
				<div class="container-1">
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="loginUsername">{lang}wcf.user.username{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" name="loginUsername" value="" id="loginUsername" />
						</div>
					</div>
					
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="loginPassword">{lang}wcf.user.password{/lang}</label>
						</div>
						<div class="formField">
							<input type="password" class="inputText" name="loginPassword" value="" id="loginPassword" />
						</div>
					</div>
					
					<div class="formElement">
						<div class="formField">
							<label><input type="checkbox" name="useCookies" value="1" /> {lang}wcf.user.login.useCookies{/lang}</label>
						</div>
					</div>
					
					{if $additionalFields|isset}{@$additionalFields}{/if}
					
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
			
			{if $this->session->requestMethod == 'GET'}<input type="hidden" name="url" value="{$this->session->requestURI}" />{/if}
			{@SID_INPUT_TAG}
		</form>
	{/if}

</div>

{include file='footer' sandbox=false}
</body>
</html>