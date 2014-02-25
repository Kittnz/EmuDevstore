<div id="settings">
	<fieldset class="noJavaScript">
		<legend class="noJavaScript">{lang}wcf.message.settings{/lang}</legend>
		
			<div class="formCheckBox">
			{if $canUseBBCodes}
				<div class="formField">
					<label><input type="checkbox" name="parseURL" value="1" {if $parseURL == 1}checked="checked" {/if}/> {lang}wcf.message.settings.parseURL{/lang}</label>
				</div>
				<div class="formFieldDesc">
					<p>{lang}wcf.message.settings.parseURL.description{/lang}</p>
				</div>
			{/if}
			{if MODULE_SMILEY == 1 && $canUseSmilies}
				<div class="formField">
					<label><input type="checkbox" name="enableSmilies" value="1" {if $enableSmilies == 1}checked="checked" {/if}/> {lang}wcf.message.settings.enableSmilies{/lang}</label>
				</div>
				<div class="formFieldDesc">
					<p>{lang}wcf.message.settings.enableSmilies.description{/lang}</p>
				</div>
			{/if}
			{if $canUseHtml}
				<div class="formField">
					<label><input type="checkbox" name="enableHtml" value="1" {if $enableHtml == 1}checked="checked" {/if}/> {lang}wcf.message.settings.enableHtml{/lang}</label>
				</div>
				<div class="formFieldDesc">
					<p>{lang}wcf.message.settings.enableHtml.description{/lang}</p>
				</div>
			{/if}
			{if $canUseBBCodes}
				<div class="formField">
					<label><input type="checkbox" name="enableBBCodes" value="1" {if $enableBBCodes == 1}checked="checked" {/if}/> {lang}wcf.message.settings.enableBBCodes{/lang}</label>
				</div>
				<div class="formFieldDesc">
					<p>{lang}wcf.message.settings.enableBBCodes.description{/lang}</p>
				</div>
			{/if}
			{if 'MODULE_USER_SIGNATURE'|defined && MODULE_USER_SIGNATURE && $showSignatureSetting && $this->user->userID}
				<div class="formField">
					<label><input type="checkbox" name="showSignature" value="1" {if $showSignature == 1}checked="checked" {/if}/> {lang}wcf.message.settings.showSignature{/lang}</label>
				</div>
				<div class="formFieldDesc">
					<p>{lang}wcf.message.settings.showSignature.description{/lang}</p>
				</div>
			{/if}
			{if $additionalSettings|isset}{@$additionalSettings}{/if}
			</div>
		
	</fieldset>
</div>

<script type="text/javascript">
	//<![CDATA[
	tabbedPane.addTab('settings', false);
	//]]>
</script>