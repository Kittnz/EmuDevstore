{include file='setupWindowHeader'}

<div class="{if $stop}error{else}warning{/if}">
	{if $stop}{lang}wcf.acp.package.install.error.excludingPackages{/lang}{else}{lang}wcf.acp.package.install.error.excludingPackages.warning{/lang}{/if}
	<ul>
		{foreach from=$excludingPackages item=excludingPackage}
			<li>{lang}wcf.acp.package.install.error.excludingPackages.excludingPackage{/lang}</li>
		{/foreach}
	</ul>
</div>

{if $stop}
	<div id="rollbackButton" class="nextButton">
		<input name="rollbackButton" type="button" value="{lang}wcf.acp.package.{if $action != 'install'}uninstall.{/if}{if $cancelable && $action == 'install'}rollback{else}cancel{/if}{/lang}" onclick="{if $cancelable && $action == 'install'}if (confirm('{lang}wcf.acp.package.install.rollback.sure{/lang}')) {/if}if (parent && parent.rollback) parent.rollback(this); else document.location.href=fixURL('index.php?page=Package&amp;action={@$action}&amp;queueID={@$queueID}&amp;step={if $cancelable && $action == 'install'}rollback{else}cancel{/if}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}');parent.stopAnimating();" />
	</div>
{else}
	<form method="post" action="index.php?page=Package&amp;step={@$nextStep}">
		<input type="hidden" name="queueID" value="{@$queueID}" />
		<input type="hidden" name="action" value="{@$action}" />
		{@SID_INPUT_TAG}
		<input type="hidden" name="packageID" value="{@PACKAGE_ID}" />
		<input type="hidden" name="send" value="send" />
		
		<div class="nextButton">
			<input type="submit" value="{lang}wcf.global.button.next{/lang}" onclick="parent.stopAnimating();" />
		</div>
	</form>
{/if}

<script type="text/javascript">
	//<![CDATA[
	window.onload = function() {
	changeHeight();	
};
	parent.showWindow(true);
	//]]>
</script>

{include file='setupWindowFooter'}