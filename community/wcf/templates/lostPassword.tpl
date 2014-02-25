{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.lostPassword.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
	<script type="text/javascript">
		//<![CDATA[
		function checkLostPasswordInput() {
			if (document.getElementById('username').value == '') {
				enableOptions('email');
			}
			if (document.getElementById('email').value == '') {
				enableOptions('username');
			}
			
			if (document.getElementById('username').value != '' && document.getElementById('email').value == '') {
				disableOptions('email');
			}
			if (document.getElementById('username').value == '' && document.getElementById('email').value != '') {
				disableOptions('username');
			}
		}
		
		onloadEvents.push(function() { checkLostPasswordInput(); });
		//]]>
	</script>
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
	</ul>
	
	<div class="mainHeadline">
		<img src="{icon}lostPasswordL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2>{lang}wcf.user.lostPassword.title{/lang}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	<p class="info">{lang}wcf.user.lostPassword.description{/lang}</p>
	
	{if $errorField}
		<p class="error">{lang}wcf.global.form.error{/lang}</p>
	{/if}
	
	<form method="post" action="index.php?form=LostPassword">
		<div class="border content">
			<div class="container-1">
				<div id="usernameDiv" class="formElement{if $errorField == 'username'} formError{/if}">
					<div class="formFieldLabel">
						<label for="username">{lang}wcf.user.username{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="username" value="{$username}" id="username" onkeyup="checkLostPasswordInput();" />
						{if $errorField == 'username'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'notFound'}{lang}wcf.user.error.username.notFound{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
			
				<div id="emailDiv" class="formElement{if $errorField == 'email'} formError{/if}">
					<div class="formFieldLabel">
						<label for="email">{lang}wcf.user.email{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="email" value="{$email}" id="email" onkeyup="checkLostPasswordInput();" />
						{if $errorField == 'email'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'notFound'}{lang}wcf.user.lostPassword.error.email.notFound{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
				{include file='captcha'}
			</div>
		</div>
		
		<div class="formSubmit">
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
			<input type="reset" accesskey="r" onclick="window.setTimeout('checkLostPasswordInput()', 100)" value="{lang}wcf.global.button.reset{/lang}" />
		</div>
	
		{@SID_INPUT_TAG}
	</form>

</div>

{include file='footer' sandbox=false}
</body>
</html>