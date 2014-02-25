{include file='header'}

<script type="text/javascript">//<![CDATA[
	// this function disables the wysiwyg settings elements if a class was given
	// or no openhtml is specified
	function checkWysiwygAbility() {
		{if !$isCoreBBCode} 
		var classNameElement = document.getElementById('className');
		var wysiwygElement = document.getElementById('wysiwyg');
		if (classNameElement.value == '') {
			wysiwygElement.disabled = false;	
		}
		else {
			wysiwygElement.disabled = true;	
			wysiwygElement.checked = false;	
		}
		{/if}
	}				
	
	onloadEvents.push(function() { checkWysiwygAbility(); });				
//]]></script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/bbcode{@$action|ucfirst}L.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.bbcode.{@$action}{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.bbcode.{@$action}.success{/lang}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=BBCodeList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/bbcodeM.png" alt="" title="{lang}wcf.acp.menu.link.bbcode.view{/lang}" /> <span>{lang}wcf.acp.menu.link.bbcode.view{/lang}</span></a></li></ul>
	</div>
</div>
<form method="post" action="index.php?form=BBCode{@$action|ucfirst}">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.bbcode{/lang}</legend>
				<div class="formElement{if $errorField == 'bbcodeTag'} formError{/if}" id="bbcodeTagDiv">
					<div class="formFieldLabel">
						<label for="bbcodeTag">{lang}wcf.acp.bbcode.bbcodeTag{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="bbcodeTag" id="bbcodeTag" value="{$bbcodeTag}" />
						{if $errorField == 'title'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'notUnique'}{lang}wcf.acp.bbcode.error.bbcodeTag.notUnique{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="bbcodeTagHelpMessage">
						{lang}wcf.acp.bbcode.bbcodeTag.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('bbcodeTag');
				//]]></script>
				
				<div class="formElement" id="htmlOpenDiv">
					<div class="formFieldLabel">
						<label for="htmlOpen">{lang}wcf.acp.bbcode.htmlOpen{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="htmlOpen" id="htmlOpen" value="{$htmlOpen}" />
					</div>
					<div class="formFieldDesc hidden" id="htmlOpenHelpMessage">
						{lang}wcf.acp.bbcode.htmlOpen.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('htmlOpen');
				//]]></script>
				
				<div class="formElement" id="htmlCloseDiv">
					<div class="formFieldLabel">
						<label for="htmlClose">{lang}wcf.acp.bbcode.htmlClose{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="htmlClose" id="htmlClose" value="{$htmlClose}" />
					</div>
					<div class="formFieldDesc hidden" id="htmlCloseHelpMessage">
						{lang}wcf.acp.bbcode.htmlClose.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('htmlClose');
				//]]></script>
				
				<div class="formElement" id="textOpenDiv">
					<div class="formFieldLabel">
						<label for="textOpen">{lang}wcf.acp.bbcode.textOpen{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="textOpen" id="textOpen" value="{$textOpen}" />
					</div>
					<div class="formFieldDesc hidden" id="textOpenHelpMessage">
						{lang}wcf.acp.bbcode.textOpen.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('textOpen');
				//]]></script>
				
				<div class="formElement" id="textCloseDiv">
					<div class="formFieldLabel">
						<label for="textClose">{lang}wcf.acp.bbcode.textClose{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="textClose" id="textClose" value="{$textClose}" />
					</div>
					<div class="formFieldDesc hidden" id="textCloseHelpMessage">
						{lang}wcf.acp.bbcode.textClose.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('textClose');
				//]]></script>
				
				<div class="formElement" id="allowedChildrenDiv">
					<div class="formFieldLabel">
						<label for="allowedChildren">{lang}wcf.acp.bbcode.allowedChildren{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="allowedChildren" id="allowedChildren" value="{$allowedChildren}" />
					</div>
					<div class="formFieldDesc hidden" id="allowedChildrenHelpMessage">
						{lang}wcf.acp.bbcode.allowedChildren.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('allowedChildren');
				//]]></script>
				
				<div class="formElement" id="classNameDiv">
					<div class="formFieldLabel">
						<label for="className">{lang}wcf.acp.bbcode.className{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="className" id="className" value="{$className}" onkeyup="checkWysiwygAbility()" />
					</div>
					<div class="formFieldDesc hidden" id="classNameHelpMessage">
						{lang}wcf.acp.bbcode.className.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('className');
				//]]></script>
				
				<div class="formElement" id="sourceCodeDiv">
					<div class="formField">
						<label><input type="checkbox" name="sourceCode" id="sourceCode" value="1" {if $sourceCode == 1}checked="checked" {/if}/> {lang}wcf.acp.bbcode.sourceCode{/lang}</label>
					</div>
					<div class="formFieldDesc hidden" id="sourceCodeHelpMessage">
						{lang}wcf.acp.bbcode.sourceCode.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('sourceCode');
				//]]></script>
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.bbcode.wysiwygSettings{/lang}</legend>
				<div class="formElement" id="wysiwygIconDiv">
					<div class="formFieldLabel">
						<label for="wysiwygIcon">{lang}wcf.acp.bbcode.wysiwygIcon{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="wysiwygIcon" id="wysiwygIcon" value="{$wysiwygIcon}" {if $isCoreBBCode}disabled="disabled" {/if}/>
					</div>
					<div class="formFieldDesc hidden" id="wysiwygIconHelpMessage">
						{lang}wcf.acp.bbcode.wysiwygIcon.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('wysiwygIcon');
				//]]></script>
				
				<div class="formElement" id="wysiwygDiv">
					<div class="formField">
						<label><input type="checkbox" name="wysiwyg" id="wysiwyg" value="1" {if $wysiwyg == 1 && !$isCoreBBCode}checked="checked" {/if}{if $isCoreBBCode}disabled="disabled" {/if}/> {lang}wcf.acp.bbcode.wysiwyg{/lang}</label>
					</div>
					<div class="formFieldDesc hidden" id="wysiwygHelpMessage">
						{lang}wcf.acp.bbcode.wysiwyg.description{/lang}
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('wysiwyg');
				//]]></script>
			</fieldset>
			
			{if $additionalFields|isset}{@$additionalFields}{/if}
			
			<fieldset>
				<legend><input type="image" name="addAttribute[0]" value="1" title="{lang}wcf.acp.bbcode.addAttribute{/lang}" alt="{lang}wcf.acp.bbcode.addAttribute{/lang}" src="{@RELATIVE_WCF_DIR}icon/addS.png" /> {lang}wcf.acp.bbcode.attributes{/lang}</legend>
				
				{assign var=i value=0}
				{foreach from=$attributes item=attribute}
					<fieldset>
						<legend><input type="image" name="removeAttribute[{@$i}]" value="1" title="{lang}wcf.acp.bbcode.removeAttribute{/lang}" alt="{lang}wcf.acp.bbcode.addAttribute{/lang}" src="{@RELATIVE_WCF_DIR}icon/deleteS.png" /> {lang}wcf.acp.bbcode.attribute{/lang}</legend>		
						<div class="formElement" id="attributeHtml{@$i}Div">
							<div class="formFieldLabel">
								<label for="attributeHtml{@$i}">{lang}wcf.acp.bbcode.attributeHtml{/lang}</label>
							</div>
							<div class="formField">
								<input type="text" class="inputText" name="attributes[{@$i}][attributeHtml]" id="attributeHtml{@$i}" value="{$attribute.attributeHtml}" />
							</div>
							<div class="formFieldDesc hidden" id="attributeHtml{@$i}HelpMessage">
								{lang}wcf.acp.bbcode.attributeHtml.description{/lang}
							</div>
						</div>
						<script type="text/javascript">//<![CDATA[
							inlineHelp.register('attributeHtml{@$i}');
						//]]></script>
						
						<div class="formElement" id="attributeText{@$i}Div">
							<div class="formFieldLabel">
								<label for="attributeText{@$i}">{lang}wcf.acp.bbcode.attributeText{/lang}</label>
							</div>
							<div class="formField">
								<input type="text" class="inputText" name="attributes[{@$i}][attributeText]" id="attributeText{@$i}" value="{$attribute.attributeText}" />
							</div>
							<div class="formFieldDesc hidden" id="attributeText{@$i}HelpMessage">
								{lang}wcf.acp.bbcode.attributeText.description{/lang}
							</div>
						</div>
						<script type="text/javascript">//<![CDATA[
							inlineHelp.register('attributeText{@$i}');
						//]]></script>
						
						<div class="formElement" id="validationPattern{@$i}Div">
							<div class="formFieldLabel">
								<label for="validationPattern{@$i}">{lang}wcf.acp.bbcode.validationPattern{/lang}</label>
							</div>
							<div class="formField">
								<input type="text" class="inputText" name="attributes[{@$i}][validationPattern]" id="validationPattern{@$i}" value="{$attribute.validationPattern}" />
							</div>
							<div class="formFieldDesc hidden" id="validationPattern{@$i}HelpMessage">
								{lang}wcf.acp.bbcode.validationPattern.description{/lang}
							</div>
						</div>
						<script type="text/javascript">//<![CDATA[
							inlineHelp.register('validationPattern{@$i}');
						//]]></script>
						
						<div class="formElement">
							<div class="formField">
								<label><input type="checkbox" name="attributes[{@$i}][required]" id="required{@$i}" value="1" {if $attribute.required == 1}checked="checked" {/if}/> {lang}wcf.acp.bbcode.required{/lang}</label>
							</div>
						</div>
						
						{if $i == 0}
							<div class="formElement">
								<div class="formField">
									<label><input type="checkbox" name="attributes[{@$i}][useText]" id="useText{@$i}" value="1" {if $attribute.useText == 1}checked="checked" {/if}/> {lang}wcf.acp.bbcode.useText{/lang}</label>
								</div>
							</div>
						{/if}
					</fieldset>
					{assign var=i value=$i+1}
				{/foreach}
			</fieldset>
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
 		{@SID_INPUT_TAG}
 		{if $bbcodeID|isset}<input type="hidden" name="bbcodeID" value="{@$bbcodeID}" />{/if}
 	</div>
</form>

{include file='footer'}