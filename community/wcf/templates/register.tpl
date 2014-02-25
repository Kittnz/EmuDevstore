{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.register.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
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
			<h2> {lang}wcf.user.register.title{/lang}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{if $errorField}
		<p class="error">{lang}wcf.global.form.error{/lang}</p>
	{/if}
	
	<form method="post" action="index.php?page=Register">
		<div class="border content">
			<div class="container-1">
				<div class="formElement{if $errorType.username|isset} formError{/if}">
					<div class="formFieldLabel">
						<label for="username">{lang}wcf.user.username{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="username" value="{$username}" id="username" />
						{if $errorType.username|isset}
							<p class="innerError">
								{if $errorType.username == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType.username == 'notValid'}{lang}wcf.user.error.username.notValid{/lang}{/if}
								{if $errorType.username == 'notUnique'}{lang}wcf.user.error.username.notUnique{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc">
						<p>{lang}wcf.user.register.username.description{/lang}</p>
					</div>
				</div>
			
			<fieldset>
				<legend><label for="email">{lang}wcf.user.email{/lang}</label></legend>
				<div class="formElement{if $errorType.email|isset} formError{/if}">
					<div class="formFieldLabel">
						<label for="email">{lang}wcf.user.email{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="email" value="{$email}" id="email" />
						{if $errorType.email|isset}
							<p class="innerError">
								{if $errorType.email == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType.email == 'notValid'}{lang}wcf.user.error.email.notValid{/lang}{/if}
								{if $errorType.email == 'notUnique'}{lang}wcf.user.error.email.notUnique{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc">
						<p>{lang}wcf.user.register.email.description{/lang}</p>
					</div>
				</div>
					
				<div class="formElement{if $errorType.confirmEmail|isset} formError{/if}">
					<div class="formFieldLabel">
						<label for="confirmEmail">{lang}wcf.user.confirmEmail{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="confirmEmail" value="{$confirmEmail}" id="confirmEmail" />
					
						{if $errorType.confirmEmail|isset}
							<p class="innerError">
								{if $errorType.confirmEmail == 'notEqual'}{lang}wcf.user.error.confirmEmail.notEqual{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc">
						<p>{lang}wcf.user.register.confirmEmail.description{/lang}</p>
					</div>
				</div>
			</fieldset>
	
			<fieldset>
				<legend><label for="password">{lang}wcf.user.password{/lang}</label></legend>
				<div class="formElement{if $errorType.password|isset} formError{/if}">
					<div class="formFieldLabel">
						<label for="password">{lang}wcf.user.password{/lang}</label>
					</div>
					<div class="formField">
						<input type="password" class="inputText" name="password" value="{$password}" id="password" />
						
						{if $errorType.password|isset}
							<p class="innerError">
								{if $errorType.password == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType.password == 'notSecure'}{lang}wcf.user.error.password.notSecure{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc">
						<p>{lang}wcf.user.register.password.description{/lang}</p>
					</div>
				</div>
					
				<div class="formElement{if $errorType.confirmPassword|isset} formError{/if}">
					<div class="formFieldLabel">
						<label for="confirmPassword">{lang}wcf.user.confirmPassword{/lang}</label>
					</div>
					<div class="formField">
						<input type="password" class="inputText" name="confirmPassword" value="{$confirmPassword}" id="confirmPassword" />
						{if $errorType.confirmPassword|isset}
							<p class="innerError">
								{if $errorType.confirmPassword == 'notEqual'}{lang}wcf.user.error.confirmPassword.notEqual{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc">
						<p>{lang}wcf.user.register.confirmPassword.description{/lang}</p>
					</div>
				</div>
			</fieldset>
			
			{if $availableLanguages|count > 1}
				<fieldset>
					<legend><label for="languageID">{lang}wcf.user.language{/lang}</label></legend>
					<div class="formElement">
						<div class="formFieldLabel">
							<label for="languageID">{lang}wcf.user.language{/lang}</label>
						</div>
						<div class="formField">
							{htmlOptions options=$availableLanguages selected=$languageID name=languageID id=languageID disableEncoding=true}
						</div>
						<div class="formFieldDesc">
							<p>{lang}wcf.user.language.description{/lang}</p>
						</div>
					</div>
					
					{if $availableContentLanguages|count > 1}
						<div class="formGroup">
							<div class="formGroupLabel">
								<label>{lang}wcf.user.visibleLanguages{/lang}</label>
							</div>
							<div class="formGroupField">
								<fieldset>
									<legend class="formFieldLabel">
										<label>{lang}wcf.user.visibleLanguages{/lang}</label>
									</legend>
									<div class="formField">
										<ul class="formOptions">
											{foreach from=$availableContentLanguages key=availableLanguageID item=availableLanguage}
												<li><label><input type="checkbox" name="visibleLanguages[]" value="{@$availableLanguageID}"{if $availableLanguageID|in_array:$visibleLanguages} checked="checked"{/if} /> {@$availableLanguage}</label></li>
											{/foreach}
										</ul>
									</div>
									<div class="formFieldDesc">
										<p>{lang}wcf.user.visibleLanguages.description{/lang}</p>
									</div>
								</fieldset>
							</div>
						</div>
					{/if}
				</fieldset>
			{/if}
	
			{if $additionalFields|isset}{@$additionalFields}{/if}
			
			{foreach from=$optionCategories item=category}
				<fieldset>
					<legend>{if $category.categoryIconM}<img src="{icon}{$category.categoryIconM}{/icon}" alt="" /> {/if}{lang}wcf.user.option.category.{$category.categoryName}{/lang}</legend>
					
					{include file='userOptionFieldList' options=$category.options}
					
				</fieldset>
			{/foreach}
			
			{if $errorType.captchaString|isset}
				{assign var='captchaErrorField' value='captchaString'}
				{assign var='captchaErrorType' value=$errorType.captchaString}
			{else}
				{assign var='captchaErrorField' value=''}
				{assign var='captchaErrorType' value=''}
			{/if}
			
			{include file='captcha' errorField=$captchaErrorField errorType=$captchaErrorType}
			</div>
		</div>	
	
		<div class="formSubmit">
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
			<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		</div>
	
		{@SID_INPUT_TAG}
		<input type="hidden" name="action" value="register" />
	</form>

</div>

{include file='footer' sandbox=false}
</body>
</html>