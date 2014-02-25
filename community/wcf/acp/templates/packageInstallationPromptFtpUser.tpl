{include file='setupWindowHeader'}

{if $errorField}
	<p class="error">{lang}wcf.acp.package.ftp.error{/lang}</p>
{/if}

<form method="post" action="index.php?page=Package">
	<fieldset>
		<legend>{lang}wcf.acp.package.ftp.accessData{/lang}</legend>
		<div class="inner">
			<p>{lang}wcf.acp.package.ftp.description{/lang}</p>

			<div{if $errorField == 'ftpHost'} class="errorField"{/if}>
				<label for="ftpHost">{lang}wcf.acp.package.ftp.host{/lang}</label>
				<input type="text" class="inputText" id="ftpHost" name="ftpHost" value="{$ftpHost}" />
				{if $errorField == 'ftpHost'}
					<p>
						<img src="{@RELATIVE_WCF_DIR}icon/errorS.png" alt="" />
						{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
						{if $errorType == 'cannotConnect'}{lang}wcf.acp.package.ftp.error.cannotConnect{/lang}{/if}
					</p>
				{/if}
			</div>
			
			<div{if $errorField == 'ftpUser'} class="errorField"{/if}>
				<label for="ftpUser">{lang}wcf.acp.package.ftp.user{/lang}</label>
				<input type="text" class="inputText" id="ftpUser" name="ftpUser" value="{$ftpUser}" />
				{if $errorField == 'ftpUser'}
					<p>
						<img src="{@RELATIVE_WCF_DIR}icon/errorS.png" alt="" />
						{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
						{if $errorType == 'cannotLogin'}{lang}wcf.acp.package.ftp.error.cannotLogin{/lang}{/if}
					</p>
				{/if}
			</div>
			
			<div>
				<label for="ftpPassword">{lang}wcf.acp.package.ftp.password{/lang}</label>
				<input type="password" class="inputText" id="ftpPassword" name="ftpPassword" value="{$ftpPassword}" />
			</div>
			
			<input type="hidden" name="queueID" value="{@$queueID}" />
			<input type="hidden" name="action" value="{@$action}" />
			{@SID_INPUT_TAG}
			<input type="hidden" name="step" value="{@$step}" />
			<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
			<input type="hidden" name="send" value="1" />
		</div>
	</fieldset>
	
	<div class="nextButton">
		<input type="submit" value="{lang}wcf.global.button.next{/lang}" onclick="parent.stopAnimating();" />
	</div>
</form>

<script type="text/javascript">
	//<![CDATA[
	window.onload = function() {
	changeHeight();	
};
	parent.showWindow(true);
	parent.setCurrentStep('{lang}wcf.acp.package.step.title{/lang}{lang}wcf.acp.package.step.{if $action == 'rollback'}uninstall{else}{@$action}{/if}.{@$step}{/lang}');
	//]]>
</script>

{include file='setupWindowFooter'}