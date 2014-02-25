{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/updateServer{@$action|ucfirst}L.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.updateServer.{$action}{/lang}</h2>
	</div>
</div>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.acp.updateServer.{$action}.success{/lang}</p>	
{/if}

{if $packageUpdateServer|isset && $packageUpdateServer->errorText}
	<p class="warning">{lang}wcf.acp.updateServer.lastErrorText{/lang}<br />{$packageUpdateServer->errorText}</p>	
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?page=UpdateServerList&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}" title="{lang}wcf.acp.menu.link.package.server.view{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/updateServerM.png" alt="" /> <span>{lang}wcf.acp.menu.link.package.server.view{/lang}</span></a></li></ul>
	</div>
</div>
<form method="post" action="index.php?form=UpdateServer{@$action|ucfirst}{if $packageUpdateServerID|isset}&amp;packageUpdateServerID={@$packageUpdateServerID}{/if}">
	<div class="border content">
		<div class="container-1">
	
			<fieldset>
				<legend>{lang}wcf.acp.updateServer.data{/lang}</legend>
				
				<div class="formElement{if $errorField == 'server'} formError{/if}" id="serverDiv">
					<div class="formFieldLabel">
						<label for="server">{lang}wcf.acp.updateServer.server{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="server" value="{$server}" id="server" />
						{if $errorField == 'server'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'notValid'}{lang}wcf.acp.updateServer.server.error.notValid{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="serverHelpMessage">
						<p>{lang}wcf.acp.updateServer.server.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('server');
				//]]></script>
				
				<div class="formElement" id="htUsernameDiv">
					<div class="formFieldLabel">
						<label for="htUsername">{lang}wcf.acp.updateServer.htUsername{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" name="htUsername" value="{$htUsername}" id="htUsername" />
					</div>
					<div class="formFieldDesc hidden" id="htUsernameHelpMessage">
						<p>{lang}wcf.acp.updateServer.htUsername.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('htUsername');
				//]]></script>
				
				<div class="formElement" id="htPasswordDiv">
					<div class="formFieldLabel">
						<label for="htPassword">{lang}wcf.acp.updateServer.htPassword{/lang}</label>
					</div>
					<div class="formField">
						<input type="password" class="inputText" name="htPassword" value="{$htPassword}" id="htPassword" />
					</div>
					<div class="formFieldDesc hidden" id="htPasswordHelpMessage">
						<p>{lang}wcf.acp.updateServer.htPassword.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('htPassword');
				//]]></script>
				
				{if $additionalFields|isset}{@$additionalFields}{/if}
			</fieldset>
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
 		{@SID_INPUT_TAG}
	</div>
</form>

{include file='footer'}
