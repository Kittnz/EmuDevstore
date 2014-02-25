<div class="formElement">
	<div class="formFieldLabel">
		<label for="searchBoards">{lang}wbb.search.boards{/lang}</label>
	</div>
	<div class="formField">
		<select id="searchBoards" name="boardIDs[]" multiple="multiple" size="10">
			<option value="*"{if $selectAllBoards} selected="selected"{/if}>{lang}wbb.search.boards.all{/lang}</option>
			<option value="-">--------------------</option>
			{htmloptions options=$boardOptions selected=$boardIDs disableEncoding=true}
		</select>
		{*<input type="hidden" name="threadID" value="{@$threadID}" />*}
	</div>
	<div class="formFieldDesc">
		<p>{lang}wcf.global.multiSelect{/lang}</p>
	</div>
	
	{if MODULE_ATTACHMENT}
		<div class="formField">
			<label><input type="checkbox" name="findAttachments" value="1"{if $findAttachments} checked="checked"{/if} /> {lang}wbb.search.findAttachments{/lang}</label>
		</div>
	{/if}

	{if MODULE_POLL}
		<div class="formField">
			<label><input type="checkbox" name="findPolls" value="1"{if $findPolls} checked="checked"{/if} /> {lang}wbb.search.findPolls{/lang}</label>
		</div>
	{/if}
</div>