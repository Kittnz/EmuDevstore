<ul class="formOptionsLong">
	{foreach from=$options item=optionTitle key=optionName}
		<li>
			<label><input type="checkbox" name="values[{$optionData.optionName}][]" value="{$optionName}"
			{if $optionName|in_array:$selectedOptions}checked="checked" {/if}/>
			{lang}{$optionTitle}{/lang}</label>
		</li>
	{/foreach}
</ul>