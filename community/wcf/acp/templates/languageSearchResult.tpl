{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/languageSearchL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.language.search.result{/lang}</h2>
	</div>
</div>

<div class="border content">
	<div class="container-1">
		<fieldset>
			<legend>{lang}wcf.acp.language.search.success{/lang}</legend>
			
			{foreach from=$languageItems key=$languageID item=$items}
				<div class="formElement">
					<p class="formFieldLabel">
						<span>{lang}wcf.global.language.{@$languages[$languageID].languageCode}{/lang} ({@$languages[$languageID].languageCode})</span>
						<img src="{@RELATIVE_WCF_DIR}icon/language{@$languages[$languageID].languageCode|ucfirst}S.png" alt="" />
					</p>
					<div class="formField">
						<ul>
							{foreach from=$items item=$item}
								<li><a href="index.php?form=LanguageEdit&amp;languageID={@$languageID}&amp;languageItemID={@$item.languageItemID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}#languageItem{@$item.languageItemID}">{$item.languageItem}</a></li>
							{/foreach}
						</ul>
					</div>
				</div>
			{/foreach}
		</fieldset>
	</div>
	
	<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
	{@SID_INPUT_TAG}
</div>

{include file='footer'}