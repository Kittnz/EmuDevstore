<div id="sendPMDiv">
	<fieldset>
		<legend>{lang}wcf.acp.user.sendPM.message{/lang}</legend>
		
		<div>
			<div class="formElement{if $errorField == 'pmSubject'} formError{/if}" id="pmSubjectDiv">
				<div class="formFieldLabel">
					<label for="pmSubject">{lang}wcf.pm.subject{/lang}</label>
				</div>
				<div class="formField">
					<input type="text" class="inputText" id="pmSubject" name="pmSubject" value="{$pmSubject}" />
					{if $errorField == 'pmSubject'}
						<p class="innerError">
							{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
						</p>
					{/if}
				</div>
				<div class="formFieldDesc hidden" id="pmSubjectHelpMessage">
					<p>{lang}wcf.pm.subject.description{/lang}</p>
				</div>
			</div>
			<script type="text/javascript">//<![CDATA[
				inlineHelp.register('pmSubject');
			//]]></script>
			
			<div class="formElement{if $errorField == 'pmText'} formError{/if}" id="pmTextDiv">
				<div class="formFieldLabel">
					<label for="pmText">{lang}wcf.pm.text{/lang}</label>
				</div>
				<div class="formField">
					<textarea id="pmText" name="pmText" rows="15" cols="40">{$pmText}</textarea>
					{if $errorField == 'pmText'}
						<p class="innerError">
							{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
						</p>
					{/if}
				</div>
				<div class="formFieldDesc hidden" id="pmTextHelpMessage">
					<p>{lang}wcf.pm.text.description{/lang}</p>
				</div>
			</div>
			<script type="text/javascript">//<![CDATA[
				inlineHelp.register('pmText');
			//]]></script>
		</div>
	</fieldset>
</div>

<script type="text/javascript">
	//<![CDATA[
	// disable
	function disableSendPM() {
		hideOptions('sendPMDiv');
	}
	
	// enable
	function enableSendPM() {
		disableAll();
		showOptions('sendPMDiv');
	}
	//]]>
</script>