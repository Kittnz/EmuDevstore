<div class="formElement{if $errorType.groupType|isset} formError{/if}" id="groupTypeDiv">
	<div class="formFieldLabel">
		<label for="groupType">{lang}wcf.acp.group.type{/lang}</label>
	</div>
	<div class="formField">
		<select name="groupType" id="groupType">
			<option value="4"{if $groupType == 4} selected="selected"{/if}>{lang}wcf.user.userGroups.groupType.4{/lang}</option>
			<option value="5"{if $groupType == 5} selected="selected"{/if}>{lang}wcf.user.userGroups.groupType.5{/lang}</option>
			<option value="6"{if $groupType == 6} selected="selected"{/if}>{lang}wcf.user.userGroups.groupType.6{/lang}</option>
			<option value="7"{if $groupType == 7} selected="selected"{/if}>{lang}wcf.user.userGroups.groupType.7{/lang}</option>
		</select>
		{if $errorType.groupType|isset}
			<p class="innerError">
				{if $errorType.groupType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
			</p>
		{/if}
	</div>
	<div class="formFieldDesc hidden" id="groupTypeHelpMessage">
		{lang}wcf.acp.group.type.description{/lang}
	</div>
</div>
<script type="text/javascript">//<![CDATA[
	inlineHelp.register('groupType');
//]]></script>

<div class="formElement" id="groupDescriptionDiv">
	<div class="formFieldLabel">
		<label for="groupDescription">{lang}wcf.acp.group.description{/lang}</label>
	</div>
	<div class="formField">
		<textarea name="groupDescription" id="groupDescription" cols="40" rows="10">{$groupDescription}</textarea>
	</div>
	<div class="formFieldDesc hidden" id="groupDescriptionHelpMessage">
		{lang}wcf.acp.group.description.description{/lang}
	</div>
</div>
<script type="text/javascript">//<![CDATA[
	inlineHelp.register('groupDescription');
//]]></script>

<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/Suggestion.class.js"></script>

<div class="formElement{if $errorType.groupLeaders|isset} formError{/if}" id="groupLeadersDiv">
	<div class="formFieldLabel">
		<label for="groupLeaders">{lang}wcf.user.userGroups.groupLeaders{/lang}</label>
	</div>
	<div class="formField">
		<input type="text" class="inputText" name="groupLeaders" id="groupLeaders" value="{$groupLeaders}" />
		<script type="text/javascript">
			//<![CDATA[
			suggestion.setSource('index.php?page=GroupLeaderObjectsSuggest{@SID_ARG_2ND_NOT_ENCODED}');
			suggestion.enableIcon(true);
			suggestion.init('groupLeaders');
			//]]>
		</script>
		{if $errorType.groupLeaders|isset}
			<div class="innerError">
				{if $errorType.groupLeaders|is_array}
					{foreach from=$errorType.groupLeaders item=error}
						<p>
							{if $error.type == 'notFound'}{lang username=$error.username}wcf.user.error.username.notFound{/lang}{/if}
						</p>
					{/foreach}
				{/if}
			</div>
		{/if}
	</div>
	<div class="formFieldDesc hidden" id="groupLeadersHelpMessage">
		{lang}wcf.user.userGroups.groupLeaders.description{/lang}
	</div>
</div>
<script type="text/javascript">//<![CDATA[
	inlineHelp.register('groupLeaders');
//]]></script>
