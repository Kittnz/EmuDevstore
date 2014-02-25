{include file='setupWindowHeader'}

<div id="systemException">
	<h1>Fatal error: {$errorMessage}</h1>
	<div>
		<p>{@$errorDescription}</p>
		{if $errorCode}<p>You will get more information about the problem in our knowledge base: <a href="http://www.woltlab.com/help/?code={@$errorCode}">http://www.woltlab.com/help/?code={@$errorCode}</a></p>{/if}
		<h2>Information:</h2>
		<p>
		
		<b>error message:</b> {$errorMessage}<br />
				
		{if $dbException == true}
			<b>sql error:</b> {$sqlError}<br />
			<b>sql error number:</b> {$sqlErrorNumber}<br />
			<b>sql version:</b> {$sqlVersion}<br />
		{/if}
			<b>php version:</b> {$phpVersion}<br />
			<b>wcf version:</b> {$wcfVersion}<br />
			<b>file:</b> {$file}<br />
			<b>error code:</b> {@$errorCode}<br />
			<b>date:</b> {@$date}<br />
			<b>request:</b> {$requestUri}<br />
			<b>referer:</b> {if $httpReferer}{$httpReferer}{/if}<br />
		</p>

		<h2>Stacktrace:</h2>
		<pre>{$stackTrace}</pre>
		
		{if $cancelable && $action == 'install'}
		<p>Click the button below to undo the installation that lead to this error and to clean up your system.</p>
		{else}
		<p>Click the button below to abort the {$action} and return to the ACP.</p>
		{/if}
		<div id="rollbackButton" class="nextButton">
			<input name="rollbackButton" type="button" value="{lang}wcf.acp.package.{if $action != 'install'}uninstall.{/if}{if $cancelable && $action == 'install'}rollback{else}cancel{/if}{/lang}" onclick="{if $cancelable && $action == 'install'}if (confirm('{lang}wcf.acp.package.install.rollback.sure{/lang}')) {/if}if (parent && parent.rollback) parent.rollback(this); else document.location.href=fixURL('index.php?page=Package&amp;action={@$action}&amp;queueID={@$queueID}&amp;step={if $cancelable && $action == 'install'}rollback{else}cancel{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}'); parent.stopAnimating();" />
		</div>
		
		<script type="text/javascript">
			//<![CDATA[
			window.onload = function() {
	changeHeight();	
};
			parent.showWindow(true);
			parent.setCurrentStep('{lang}wcf.acp.package.step.title{/lang}{lang}wcf.acp.package.step.{if $action == 'rollback'}uninstall{else}{@$action}{/if}.{@$step}{/lang}');
			//]]>
		</script>
	</div>
</div>

{include file='setupWindowFooter'}