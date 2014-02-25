<div class="formGroup{if $errorField == 'revokePermissions'} formError{/if}">
	<div class="formGroupLabel">
		<label>{lang}wbb.acp.board.permissions{/lang}</label>
	</div>
	<div class="formGroupField">
		<fieldset>
			<legend>{lang}wbb.acp.board.permissions{/lang}</legend>
			
			<div class="formField">
				{foreach from=$availablePermissions item=availablePermission}
					<label><input type="checkbox" name="revokePermissions[]" value="{$availablePermission}" {if $availablePermission|in_array:$revokePermissions}checked="checked" {/if}/> {lang}wbb.acp.board.permissions.{$availablePermission}{/lang}</label>
				
				{/foreach}
			</div>
			{if $errorField == 'revokePermissions'}
				<p class="innerError">
					{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
				</p>
			{/if}
		</fieldset>
	</div>
</div>