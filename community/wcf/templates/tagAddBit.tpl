<div class="formElement">
	<div class="formFieldLabel">
			<label for="tags">{lang}wcf.tagging.tags.add{/lang}</label>
	</div>
	<div class="formField">
		<input type="text" class="inputText" name="tags" id="tags" value="{if $tags|isset}{$tags}{/if}" tabindex="{counter name='tabindex'}" />
		<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Suggestion.class.js"></script>
		<script type="text/javascript">
			//<![CDATA[
			suggestion.setSource('index.php?page=TagSuggest{@SID_ARG_2ND_NOT_ENCODED}');
			suggestion.setForceSuggestion(false);
			suggestion.init('tags');
			//]]>
		</script>
	</div>
	<p class="formFieldDesc">{lang}wcf.tagging.tags.add.description{/lang}</p>
</div>