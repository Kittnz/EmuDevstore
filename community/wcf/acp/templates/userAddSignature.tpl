<div class="border tabMenuContent hidden" id="signature-content">
	<div class="container-1">
		<h3 class="subHeadline">{lang}wcf.user.signature{/lang}</h3>
		<div class="formElement">
			<div class="formFieldLabel">
				<label for="signatureInput">{lang}wcf.user.signature{/lang}</label>
			</div>
			<div class="formField">
				<textarea cols="40" rows="15" name="signature" id="signatureInput">{$signature}</textarea>
			</div>
		</div>
		
		<div class="formGroup">
			<div class="formGroupLabel">
				<label>{lang}wcf.message.settings{/lang}</label>
			</div>
			<div class="formGroupField">
				<fieldset>
					<legend>{lang}wcf.message.settings{/lang}</legend>
					<div class="formCheckBox">
						<div class="formField">
							<label><input type="checkbox" name="enableSmilies" value="1" {if $enableSmilies == 1}checked="checked" {/if}/> {lang}wcf.message.settings.enableSmilies{/lang}</label>
						</div>
						<div class="formFieldDesc">
							<p>{lang}wcf.message.settings.enableSmilies.description{/lang}</p>
						</div>
					
						<div class="formField">
							<label><input type="checkbox" name="enableHtml" value="1" {if $enableHtml == 1}checked="checked" {/if}/> {lang}wcf.message.settings.enableHtml{/lang}</label>
						</div>
						<div class="formFieldDesc">
							<p>{lang}wcf.message.settings.enableHtml.description{/lang}</p>
						</div>
					
						<div class="formField">
							<label><input type="checkbox" name="enableBBCodes" value="1" {if $enableBBCodes == 1}checked="checked" {/if}/> {lang}wcf.message.settings.enableBBCodes{/lang}</label>
						</div>
						<div class="formFieldDesc">
							<p>{lang}wcf.message.settings.enableBBCodes.description{/lang}</p>
						</div>
					</div>
				</fieldset>
			</div>
		</div>
	
		<fieldset>
			<legend>
				<label><input onclick="if (this.checked) enableOptions('disableSignatureReason'); else disableOptions('disableSignatureReason');" type="checkbox" name="disableSignature" value="1"{if $disableSignature == 1} checked="checked"{/if} /> {lang}wcf.acp.user.disableSignature{/lang}</label>
			</legend>
		
			<div class="formElement" id="disableSignatureReasonDiv">
				<div class="formFieldLabel">
					<label for="disableSignatureReason">{lang}wcf.acp.user.disableSignatureReason{/lang}</label>
				</div>
				<div class="formField">
					<textarea cols="40" rows="15" name="disableSignatureReason" id="disableSignatureReason">{$disableSignatureReason}</textarea>
				</div>
			</div>
		</fieldset>
		
		<script type="text/javascript">
			//<![CDATA[
			{if $disableSignature == 1}enableOptions('disableSignatureReason');{else}disableOptions('disableSignatureReason');{/if}
			//]]>
		</script>
	</div>
</div>