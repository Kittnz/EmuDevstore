<div class="formElement">
	<div class="formField">
		<label><input onclick="if (this.checked) enableOptions('teamPagePosition'); else disableOptions('teamPagePosition')" type="checkbox" name="showOnTeamPage" id="showOnTeamPage" value="1" {if $showOnTeamPage == 1}checked="checked" {/if}/> {lang}wcf.acp.group.showOnTeamPage{/lang}</label>
	</div>
</div>

<div class="formElement" id="teamPagePositionDiv">
	<div class="formFieldLabel">
		<label for="teamPagePosition">{lang}wcf.acp.group.teamPagePosition{/lang}</label>
	</div>
	<div class="formField">
		<input type="text" class="inputText" id="teamPagePosition" name="teamPagePosition" value="{@$teamPagePosition}" />
	</div>
	<div class="formFieldDesc hidden" id="teamPagePositionHelpMessage">
		{lang}wcf.acp.group.teamPagePosition.description{/lang}
	</div>
</div>
<script type="text/javascript">//<![CDATA[
	inlineHelp.register('teamPagePosition');
	{if $showOnTeamPage != 1}disableOptions('teamPagePosition');{/if}
//]]></script>