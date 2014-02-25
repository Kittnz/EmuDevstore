{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.register.activation{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
	</ul>
	
	<div class="mainHeadline">
		<img src="{icon}registerL.png{/icon}" alt="" /> 
		<div class="headlineContainer">
			<h2>{lang}wcf.user.register.activation{/lang}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{if $errorField}
		<p class="error">{lang}wcf.global.form.error{/lang}</p>
	{/if}
	
	<form method="post" action="index.php?page=Register">
		<div class="border content">
			<div class="container-1">
				<div class="formElement{if $errorField == 'u'} formError{/if}">
					<div class="formFieldLabel">
						<label for="userID">{lang}wcf.user.register.activation.userID{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="u" value="{@$u}" id="userID" />
						{if $errorField == 'u'}
							<p class="innerError">
								{if $errorType == 'notValid'}{lang}wcf.user.register.activation.error.userID.notValid{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
	
				<div class="formElement{if $errorField == 'a'} formError{/if}">
					<div class="formFieldLabel">
						<label for="activationCode">{lang}wcf.user.register.activation.code{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" maxlength="9" name="a" value="{@$a}" id="activationCode" />
						{if $errorField == 'a'}
							<p class="innerError">
								{if $errorType == 'notValid'}{lang}wcf.user.register.activation.error.code.notValid{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
				
				<div class="formElement">
					<div class="formField">
						<ul class="formOptionsLong">
							<li><img src="{icon}registerS.png{/icon}" alt="" /> <a href="index.php?page=Register&amp;action=newActivationCode{@SID_ARG_2ND}">{lang}wcf.user.register.newActivationCode{/lang}</a></li>
						</ul>
					</div>
				</div>
			</div>
		</div>	
	
		<div class="formSubmit">
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
			<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
			{@SID_INPUT_TAG}
			<input type="hidden" name="action" value="enable" />
		</div>
	</form>

</div>

{include file='footer' sandbox=false}
</body>
</html>