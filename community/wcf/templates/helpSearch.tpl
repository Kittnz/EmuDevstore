{include file="documentHeader"}
<head>
	<title>{lang}wcf.help.search{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{* --- quick search controls --- *}
{assign var='searchScript' value='index.php?form=HelpSearch'}
{assign var='searchFieldTitle' value='{lang}wcf.help.search{/lang}'}
{assign var='searchShowExtendedLink' value=false}
{assign var='searchFieldOptions' value=false}
{* --- end --- *}
{include file='header' sandbox=false}

<div id="main">

	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
	</ul>
	<div class="mainHeadline">
		<img src="{icon}helpL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2>{lang}wcf.help.search{/lang}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{if $errorField}
		<p class="error">{lang}wcf.global.form.error{/lang}</p>
	{/if}
	
	{if $errorMessage|isset}
		<p class="error">{@$errorMessage}</p>
	{/if}
	
	<form method="post" action="index.php?form=HelpSearch">
		<div class="border content">
			<div class="container-1">
				<fieldset>
					<legend>{lang}wcf.help.search{/lang}</legend>
					
					<div class="formElement{if $errorField == 'query'} formError{/if}">
						<div class="formFieldLabel">
							<label for="searchTerm">{lang}wcf.help.search.query{/lang}</label>
						</div>
						<div class="formField">
							<input type="text" class="inputText" id="searchTerm" name="q" value="{$query}" maxlength="255" />
							{if $errorField == 'query'}
								<p class="innerError">
									{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
									{if $errorType == 'invalid'}{lang}wcf.help.search.query.error.invalid{/lang}{/if}
								</p>
							{/if}
						</div>
					</div>
					
					{if $additionalFields|isset}{@$additionalFields}{/if}
				</fieldset>
			</div>
		</div>
		
		<div class="formSubmit">
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
			<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
			{@SID_INPUT_TAG}
		</div>
	</form>
</div>
{include file='footer' sandbox=false}
</body>
</html>