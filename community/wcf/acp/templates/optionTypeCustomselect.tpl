<ul class="formOptionsLong smallFont">
	{foreach from=$options item=option key=key}
		<li>
			<label><input type="radio" name="values[{$optionData.optionName}]" value="{$key}"{if $optionData.optionValue == $key} checked="checked"{/if} /> {lang}{@$option}{/lang}</label>
		</li>
	{/foreach}
	<li>
		<label><input type="radio" name="values[{$optionData.optionName}]" value=""{if $optionData.optionValue == $customValue} checked="checked"{/if} /></label>
		<input style="width: 400px" id="{$optionData.optionName}_custom" type="text" class="inputText" name="values[{$optionData.optionName}_custom]" value="{$customValue}" />
	</li>
</ul>