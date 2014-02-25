<fieldset>
	<legend>{lang}wcf.acp.group.autoJoin{/lang}</legend>
	
	<div class="formElement" id="neededAgeDiv">
		<div class="formFieldLabel">
			<label for="neededAge">{lang}wcf.acp.group.autoJoin.neededAge{/lang}</label>
		</div>
		<div class="formField">
			<input type="text" class="inputText" id="neededAge" name="neededAge" value="{@$neededAge}" />
		</div>
		<div class="formFieldDesc hidden" id="neededAgeHelpMessage">
			{lang}wcf.acp.group.autoJoin.neededAge.description{/lang}
		</div>
	</div>
	<script type="text/javascript">//<![CDATA[
		inlineHelp.register('neededAge');
	//]]></script>
	
	<div class="formElement" id="neededPointsDiv">
		<div class="formFieldLabel">
			<label for="neededPoints">{lang}wcf.acp.group.autoJoin.neededPoints{/lang}</label>
		</div>
		<div class="formField">
			<input type="text" class="inputText" id="neededPoints" name="neededPoints" value="{@$neededPoints}" />
		</div>
		<div class="formFieldDesc hidden" id="neededPointsHelpMessage">
			{lang}wcf.acp.group.autoJoin.neededPoints.description{/lang}
		</div>
	</div>
	<script type="text/javascript">//<![CDATA[
		inlineHelp.register('neededPoints');
	//]]></script>
</fieldset>