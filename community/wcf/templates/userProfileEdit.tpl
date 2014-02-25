{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.option.category.{$category}{/lang} - {lang}wcf.user.usercp{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	{capture append=userMessages}
		{if $errorField}
			<p class="error">{lang}wcf.global.form.error{/lang}</p>
		{/if}
		
		{if $success|isset}
			<p class="success">{lang}wcf.user.edit.success{/lang}</p>
		{/if}
	{/capture}
	
	{include file="userCPHeader"}
	
	<form method="post" action="index.php?form=UserProfileEdit">
		<div class="border tabMenuContent">
			<div class="container-1">
				<h3 class="subHeadline">{lang}wcf.user.option.category.{$category}{/lang}</h3>
				
				{@SID_INPUT_TAG}
				<input type="hidden" name="category" value="{$category}" />
				
				{if $category == 'settings.general' && $availableLanguages|count > 1}
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
				
				{if $category == 'settings.display' && $availableStyles|count > 1}
					<fieldset>
						<legend><label for="styleID">{lang}wcf.user.style{/lang}</label></legend>
						<div class="formElement">
							<div class="formFieldLabel">
								<label for="styleID">{lang}wcf.user.style{/lang}</label>
							</div>
							<div class="formField">
								<select name="styleID" id="styleID" onchange="showStylePreviewImage(this)" onkeyup="showStylePreviewImage(this)">
									<option value="0"></option>
									{htmlOptions options=$availableStyles selected=$styleID}
								</select>
							</div>
							<div class="formFieldDesc">
								<p>{lang}wcf.user.style.description{/lang}</p>
								<div id="stylePreviewImageContainer" class="stylePreviewImageContainer"></div>
							</div>
							
							<script type="text/javascript">
								//<![CDATA[
								var stylePreviewImages = new Array();
								{foreach from=$availableStyles item=availableStyle}{if $availableStyle->image}stylePreviewImages[{@$availableStyle->styleID}] = '{@$availableStyle->image|encodeJS}';{/if}{/foreach}
								function showStylePreviewImage(styleSelect) {
									// remove image
									var stylePreviewImageContainer = document.getElementById('stylePreviewImageContainer');
									for (var i = stylePreviewImageContainer.childNodes.length - 1; i >= 0; i--) {
										stylePreviewImageContainer.removeChild(stylePreviewImageContainer.childNodes[i]);
									}
									
									if (styleSelect.selectedIndex != -1) {
										var styleID = styleSelect.options[styleSelect.selectedIndex].value
										if (stylePreviewImages[styleID]) {
											var image = document.createElement('img');
											stylePreviewImageContainer.appendChild(image);
											image.src = RELATIVE_WCF_DIR + stylePreviewImages[styleID];
										}
									}
								}
								
								onloadEvents.push(function() { showStylePreviewImage(document.getElementById('styleID')) });
								//]]>
							</script>
						</div>
					</fieldset>
				{/if}
				
				{foreach from=$optionCategories item=category}
					<fieldset>
						<legend>{if $category.categoryIconM}<img src="{icon}{$category.categoryIconM}{/icon}" alt="" /> {/if}{lang}wcf.user.option.category.{$category.categoryName}{/lang}</legend>
						
						{include file='userOptionFieldList' options=$category.options}
						
					</fieldset>
				{/foreach}
				
				{if $options|count > 0}
					{include file='userOptionFieldList'}
				{/if}
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
			</div>
		</div>
		<div class="formSubmit">
			<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
			<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		</div>
	</form>

</div>

{include file='footer' sandbox=false}
</body>
</html>