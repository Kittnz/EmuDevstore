<div class="formElement">
	<div class="formFieldLabel">
		<label for="searchPmFolders">{lang}wcf.pm.search.folders{/lang}</label>
	</div>
	<div class="formField">
		<select id="searchPmFolders" name="folderIDs[]" multiple="multiple" size="6">
			<option value="-10"{if $selectAllFolders} selected="selected"{/if}>{lang}wcf.pm.search.folders.all{/lang}</option>
			<option value="-11">--------------------</option>
			{htmloptions options=$folderOptions selected=$folderIDs disableEncoding=true}
		</select>
	</div>
	<div class="formFieldDesc">
		<p>{lang}wcf.global.multiSelect{/lang}</p>
	</div>
</div>