<div class="formElement{if $errorType.userOnlineMarking|isset} formError{/if}" id="userOnlineMarkingDiv">
	<div class="formFieldLabel">
		<label for="userOnlineMarking">{lang}wcf.acp.group.userOnlineMarking{/lang}</label>
	</div>
	<div class="formField">
		<input type="text" class="inputText" id="userOnlineMarking" name="userOnlineMarking" value="{$userOnlineMarking}" />
		{if $errorType.userOnlineMarking|isset}
			<p class="innerError">
				{if $errorType.userOnlineMarking == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
			</p>
		{/if}
	</div>
	<div class="formFieldDesc hidden" id="userOnlineMarkingHelpMessage">
		{lang}wcf.acp.group.userOnlineMarking.description{/lang}
	</div>
</div>
<script type="text/javascript">//<![CDATA[
	inlineHelp.register('userOnlineMarking');
//]]></script>