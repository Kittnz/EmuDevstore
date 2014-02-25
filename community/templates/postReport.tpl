{include file="documentHeader"}
<head>
	<title>{lang}wbb.report.title{/lang} - {if $thread->prefix}{lang}{$thread->prefix}{/lang} {/if}{$thread->topic} - {lang}{$board->title}{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>

{include file='header' sandbox=false}

<div id="main">
	
	{include file="navigation" showThread=true}
	
	<div class="mainHeadline">
		<img src="{icon}postReportL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2>{lang}wbb.report.title{/lang}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	{if $errorField}
		<p class="error">{lang}wcf.global.form.error{/lang}</p>
	{/if}
	
	<form method="post" action="index.php?form=PostReport&amp;postID={@$postID}">
		<div class="border content">
			<div class="container-1">
				<h3 class="subHeadline">{lang}wbb.report.title{/lang}</h3>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="text">{lang}wbb.report.label{/lang}</label>
					</div>	
						
					<div class="formField{if $errorField == 'text'} formError{/if}">
						<textarea id="text" name="text" rows="20" cols="40"></textarea>
						{if $errorField == 'text'}
							<p class="innerError">{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}</p>
						{/if}
					</div>
					<div class="formFieldDesc">
						<p>{lang}wbb.report.description{/lang}</p>
					</div>
				</div>
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
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