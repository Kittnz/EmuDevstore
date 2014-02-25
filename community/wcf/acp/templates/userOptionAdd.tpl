{include file='header'}
<script type="text/javascript">//<![CDATA[
	var optionTypesUsingSelectOptions = new Array('{implode from=$optionTypesUsingSelectOptions item=optionTypesUsingSelectOption glue="', '"}{@$optionTypesUsingSelectOption}{/implode}');
	function isUsingSelectOptions(optionType) {
		for (var i = 0; i < optionTypesUsingSelectOptions.length; i++) {
			if (optionTypesUsingSelectOptions[i] == optionType) return true;
		}
		return false;
	}
	
	function selectOptionType(newOptionType) {
		if (isUsingSelectOptions(newOptionType)) showOptions('selectOptionsDiv');
		else hideOptions('selectOptionsDiv');
		
		if (newOptionType == 'text') showOptions('textFormatDiv');
		else hideOptions('textFormatDiv');
		
		if (newOptionType == 'textarea') showOptions('showLineBreaksDiv');
		else hideOptions('showLineBreaksDiv');
	}
	
	onloadEvents.push(function() { selectOptionType('{@$optionType|encodeJS}'); });
//]]></script>


<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/userOption{@$action|ucfirst}L.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.user.option.{@$action}{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.user.option.{@$action}.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=UserOptionList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/userOptionM.png" alt="" title="{lang}wcf.acp.menu.link.user.option.view{/lang}" /> <span>{lang}wcf.acp.menu.link.user.option.view{/lang}</span></a></li></ul>
	</div>
</div>
<form method="post" action="index.php?form=UserOption{@$action|ucfirst}">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.user.option.general{/lang}</legend>
				<div class="formElement{if $errorField == 'optionName'} formError{/if}" id="optionNameDiv">
					<div class="formFieldLabel">
						<label for="optionName">{lang}wcf.acp.user.option.optionName{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="optionName" id="optionName" value="{$optionName}" />
						{if $errorField == 'optionName'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="optionNameHelpMessage">
						{lang}wcf.acp.user.option.optionName.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('optionName');
				//]]></script>
				
				<div class="formElement" id="optionDescriptionDiv">
					<div class="formFieldLabel">
						<label for="optionDescription">{lang}wcf.acp.user.option.description{/lang}</label>
					</div>
					<div class="formField">
						<textarea rows="10" cols="40" name="optionDescription" id="optionDescription">{$optionDescription}</textarea>
					</div>
					<div class="formFieldDesc hidden" id="optionDescriptionHelpMessage">
						{lang}wcf.acp.user.option.description.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('optionDescription');
				//]]></script>
				
				<div class="formElement{if $errorField == 'categoryName'} formError{/if}" id="categoryNameDiv">
					<div class="formFieldLabel">
						<label for="categoryName">{lang}wcf.acp.user.option.categoryName{/lang}</label>
					</div>
					<div class="formField">
						<select name="categoryName" id="categoryName">
							<option value=""></option>
							{htmlOptions options=$categories selected=$categoryName}
						</select>
						{if $errorField == 'categoryName'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="categoryNameHelpMessage">
						{lang}wcf.acp.user.option.categoryName.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('categoryName');
				//]]></script>
				
				<div class="formElement{if $errorField == 'optionType'} formError{/if}" id="optionTypeDiv">
					<div class="formFieldLabel">
						<label for="optionType">{lang}wcf.acp.user.option.optionType{/lang}</label>
					</div>
					<div class="formField">
						<select name="optionType" id="optionType" onchange="selectOptionType(this.options[this.selectedIndex].value)">
							<option value=""></option>
							{htmlOptions output=$optionTypes values=$optionTypes selected=$optionType}
						</select>
						{if $errorField == 'optionType'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="optionTypeHelpMessage">
						{lang}wcf.acp.user.option.optionType.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('optionType');
				//]]></script>
				
				<div class="formElement" id="defaultValueDiv">
					<div class="formFieldLabel">
						<label for="defaultValue">{lang}wcf.acp.user.option.defaultValue{/lang}</label>
					</div>
					<div class="formField">
						<textarea rows="3" cols="40" name="defaultValue" id="defaultValue">{$defaultValue}</textarea>
					</div>
					<div class="formFieldDesc hidden" id="defaultValueHelpMessage">
						{lang}wcf.acp.user.option.defaultValue.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('defaultValue');
				//]]></script>
				
				<div class="formElement{if $errorField == 'selectOptions'} formError{/if}" id="selectOptionsDiv">
					<div class="formFieldLabel">
						<label for="selectOptions">{lang}wcf.acp.user.option.selectOptions{/lang}</label>
					</div>
					<div class="formField">
						<textarea rows="10" cols="40" name="selectOptions" id="selectOptions">{$selectOptions}</textarea>
						{if $errorField == 'selectOptions'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="selectOptionsHelpMessage">
						{lang}wcf.acp.user.option.selectOptions.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('selectOptions');
				//]]></script>
				
				<div class="formElement" id="showOrderDiv">
					<div class="formFieldLabel">
						<label for="showOrder">{lang}wcf.acp.user.option.showOrder{/lang}</label>
					</div>
					<div class="formField">	
						<input type="text" class="inputText" name="showOrder" id="showOrder" value="{$showOrder}" />
					</div>
					<div class="formFieldDesc hidden" id="showOrderHelpMessage">
						{lang}wcf.acp.user.option.showOrder.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('showOrder');
				//]]></script>
				
				{if $outputClassSelectable}
					<div class="formElement" id="showLineBreaksDiv">
						<div class="formField">
							<label><input type="checkbox" name="showLineBreaks" id="showLineBreaks" value="1" {if $showLineBreaks == 1}checked="checked" {/if}/> {lang}wcf.acp.user.option.showLineBreaks{/lang}</label>
						</div>
					</div>
					
					<div class="formElement" id="textFormatDiv">
						<div class="formFieldLabel">
							<label for="textFormat">{lang}wcf.acp.user.option.textFormat{/lang}</label>
						</div>
						<div class="formField">	
							<select name="textFormat" id="textFormat">
								<option value=""{if $textFormat == ''} selected="selected"{/if}></option>
								<option value="link"{if $textFormat == 'link'} selected="selected"{/if}>{lang}wcf.acp.user.option.textFormat.link{/lang}</option>
								<option value="image"{if $textFormat == 'image'} selected="selected"{/if}>{lang}wcf.acp.user.option.textFormat.image{/lang}</option>
							</select>
						</div>
						<div class="formFieldDesc hidden" id="textFormatHelpMessage">
							{lang}wcf.acp.user.option.textFormat.description{/lang}
						</div>
					</div>
					<script type="text/javascript">//<![CDATA[
						inlineHelp.register('textFormat');
					//]]></script>
				{/if}
			</fieldset>
			<fieldset>
				<legend>{lang}wcf.acp.user.option.access{/lang}</legend>	
				<div class="formElement" id="editableDiv">
					<div class="formFieldLabel">
						<label for="editable">{lang}wcf.acp.user.option.editable{/lang}</label>
					</div>
					<div class="formField">	
						<select name="editable" id="editable">
							<option value="0"{if $editable == 0} selected="selected"{/if}>{lang}wcf.acp.user.option.editable.0{/lang}</option>
							<option value="1"{if $editable == 1} selected="selected"{/if}>{lang}wcf.acp.user.option.editable.1{/lang}</option>
							<option value="2"{if $editable == 2} selected="selected"{/if}>{lang}wcf.acp.user.option.editable.2{/lang}</option>
							<option value="3"{if $editable == 3} selected="selected"{/if}>{lang}wcf.acp.user.option.editable.3{/lang}</option>
						</select>
					</div>
					<div class="formFieldDesc hidden" id="editableHelpMessage">
						{lang}wcf.acp.user.option.editable.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('editable');
				//]]></script>
				
				<div class="formElement" id="visibleDiv">
					<div class="formFieldLabel">
						<label for="visible">{lang}wcf.acp.user.option.visible{/lang}</label>
					</div>
					<div class="formField">	
						<select name="visible" id="visible">
							<option value="0"{if $visible == 0} selected="selected"{/if}>{lang}wcf.acp.user.option.visible.0{/lang}</option>
							<option value="1"{if $visible == 1} selected="selected"{/if}>{lang}wcf.acp.user.option.visible.1{/lang}</option>
							<option value="2"{if $visible == 2} selected="selected"{/if}>{lang}wcf.acp.user.option.visible.2{/lang}</option>
							<option value="3"{if $visible == 3} selected="selected"{/if}>{lang}wcf.acp.user.option.visible.3{/lang}</option>
						</select>
					</div>
					<div class="formFieldDesc hidden" id="visibleHelpMessage">
						{lang}wcf.acp.user.option.visible.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('visible');
				//]]></script>
				
				<div class="formElement" id="validationPatternDiv">
					<div class="formFieldLabel">
						<label for="validationPattern">{lang}wcf.acp.user.option.validationPattern{/lang}</label>
					</div>
					<div class="formField">	
						<input type="text" class="inputText" name="validationPattern" id="validationPattern" value="{$validationPattern}" />
					</div>
					<div class="formFieldDesc hidden" id="validationPatternHelpMessage">
						{lang}wcf.acp.user.option.validationPattern.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('validationPattern');
				//]]></script>
				
				<div class="formElement">
					<div class="formField">
						<label><input type="checkbox" name="askDuringRegistration" id="askDuringRegistration" value="1" {if $askDuringRegistration == 1}checked="checked" {/if}/> {lang}wcf.acp.user.option.askDuringRegistration{/lang}</label>
					</div>
				</div>
				
				<div class="formElement">
					<div class="formField">
						<label><input type="checkbox" name="required" id="required" value="1" {if $required == 1}checked="checked" {/if}/> {lang}wcf.acp.user.option.required{/lang}</label>
					</div>
				</div>
				
				<div class="formElement">
					<div class="formField">
						<label><input type="checkbox" name="searchable" id="searchable" value="1" {if $searchable == 1}checked="checked" {/if}/> {lang}wcf.acp.user.option.searchable{/lang}</label>
					</div>
				</div>
			</fieldset>
			
			{if $additionalFields|isset}{@$additionalFields}{/if}
		</div>
	</div>
		
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
 		{@SID_INPUT_TAG}
 		{if $optionID|isset}<input type="hidden" name="optionID" value="{@$optionID}" />{/if}
 	</div>
</form>

{include file='footer'}