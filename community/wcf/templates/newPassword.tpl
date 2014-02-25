{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.lostPassword.newPassword.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a></li>
	</ul>
	
	<div class="mainHeadline">
		<img src="{icon}lostPasswordL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2>{lang}wcf.user.lostPassword.newPassword.title{/lang}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{if $errorField}
		<p class="error">{lang}wcf.global.form.error{/lang}</p>
	{/if}
	
	<form method="post" action="index.php?form=NewPassword">
		<div class="border content">
			<div class="container-1">
				<div class="formElement{if $errorField == 'userID'} formError{/if}">
					<div class="formFieldLabel">
						<label for="userID">{lang}wcf.user.userID{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="u" value="{@$userID}" id="userID" />
						{if $errorField == 'userID'}
							<p class="innerError">
								{if $errorType == 'invalid'}{lang}wcf.user.lostPassword.error.userID.invalid{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
				
				<div class="formElement{if $errorField == 'lostPasswordKey'} formError{/if}">
					<div class="formFieldLabel">
						<label for="lostPasswordKey">{lang}wcf.user.lostPassword.lostPasswordKey{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="k" value="{$lostPasswordKey}" id="lostPasswordKey" />
						{if $errorField == 'lostPasswordKey'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'invalid'}{lang}wcf.user.lostPassword.error.lostPasswordKey.invalid{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
				
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
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