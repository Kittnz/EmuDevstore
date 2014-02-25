{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/usersL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.user.ban{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="post" action="index.php?form=UserBan">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.user.ban.markedUsers{/lang}</legend>
				
				<div>
					{implode from=$users item=user}<a href="index.php?form=UserEdit&amp;userID={@$user->userID}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">{$user}</a>{/implode}
				</div>
			</fieldset>	
			
			<fieldset>
				<legend>{lang}wcf.acp.user.ban.reason{/lang}</legend>
				
				<div class="formElement" id="reasonDiv">
					<div class="formFieldLabel">
						<label for="reason">{lang}wcf.acp.user.ban.reason{/lang}</label>
					</div>
					<div class="formField">
						<textarea rows="20" cols="40" id="reason" name="reason">{$reason}</textarea>
					</div>
					<div class="formFieldDesc hidden" id="reasonHelpMessage">
						<p>{lang}wcf.acp.user.ban.reason.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('reason');
				//]]></script>
			</fieldset>
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
 		{@SID_INPUT_TAG}
 		<input type="hidden" name="userIDs" value="{@$userIDs}" />
 		<input type="hidden" name="url" value="{$url}" />
 	</div>
</form>

{include file='footer'}