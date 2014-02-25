{foreach from=$options item=option}
	{assign var=divClass value=''}
	{if $option.divClass|isset}
		{if $divClass}
			{assign var=divClass value="$divClass "}
		{/if}
		
		{assign var=divClass value=$divClass|concat:$option.divClass}
	{/if}
	
	{if $errorType|is_array && $errorType[$option.optionName]|isset}
		{assign var=error value=$errorType[$option.optionName]}
		{if $divClass}
			{assign var=divClass value="$divClass "}
		{/if}
		
		{assign var=divClass value=$divClass|concat:'formError'}
	{else}
		{assign var=error value=''}
	{/if}
	
	
	{if $option.isOptionGroup|isset}
		<div class="{if $divClass}{$divClass} {/if}formGroup" id="{$option.optionName}Div">
			<div class="formGroupLabel">
				<label>{lang}wcf.user.option.{$option.optionName}{/lang}</label>
			</div>
			<div class="formGroupField">
				<fieldset>
					<legend>{lang}wcf.user.option.{$option.optionName}{/lang}</legend>
						
					<div class="formField">
						{@$option.html}
						{if $option.supportsExactMatch|isset}
							<label><input type="checkbox" name="matchExactly[{$option.optionName}]" value="1" {if $matchExactly[$option.optionName]|isset}checked="checked" {/if}/> {lang}wcf.global.search.matchesExactly{/lang}</label>
						{/if}
					</div>
					
					{if !$hideDescription|isset}
						<div class="formFieldDesc">
							<p>{lang}wcf.user.option.{$option.optionName}.description{/lang}</p>
						</div>
					{/if}
					
					{if $error}
						<p class="innerError">
							{if $error == 'empty'}
								{lang}wcf.global.error.empty{/lang}
							{else}	
								{lang}wcf.user.option.error.{$error}{/lang}
							{/if}
						</p>
					{/if}
					
				</fieldset>
			</div>
		</div>
		
	{else}
		<div class="{if $divClass}{$divClass} {/if}formElement" id="{$option.optionName}Div">
			{capture assign=innerError}
				{if $error}
					<p class="innerError">
						{if $error == 'empty'}
							{lang}wcf.global.error.empty{/lang}
						{else}	
							{lang}wcf.user.option.error.{$error}{/lang}
						{/if}
					</p>
				{/if}
			{/capture}
			
			{if $option.beforeLabel}
				<div class="formField">
					<label for="{$option.optionName}">{@$option.html} {lang}wcf.user.option.{$option.optionName}{/lang}</label>
					{@$innerError}
				</div>
			{else}
				<div class="formFieldLabel">
					<label for="{$option.optionName}">{lang}wcf.user.option.{$option.optionName}{/lang}</label>
				</div>
				<div class="formField">
					{@$option.html}
					{@$innerError}			
				</div>
			{/if}
			
			{if !$hideDescription|isset}
				<div class="formFieldDesc">
					<p>{lang}wcf.user.option.{$option.optionName}.description{/lang}</p>
				</div>
			{/if}
		</div>
	{/if}
	
	<script type="text/javascript">
		//<![CDATA[
		{if $option.enableOptionsJS|isset}onloadEvents.push(function() { {@$option.enableOptionsJS}; });{/if}
		//]]>
	</script>
{/foreach}