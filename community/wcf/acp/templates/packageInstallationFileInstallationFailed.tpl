{include file='setupWindowHeader'}

{if $exception->getCode() == 11111}
	<p class="error">{lang}wcf.acp.package.installation.file.error.untarFailed{/lang}
		<ul>
		{foreach from=$exception->getDescription() item=file}
			<li>{$file.file} ({@$file.code})</li>
		{/foreach}
		</ul>
	</p>
{else}
	<p class="error">{lang}wcf.acp.package.installation.file.error.unknown{/lang}
		{$exception->getMessage()} ({@$exception->getCode()})
	</p>
{/if}

<form method="post" action="index.php?page=Package&amp;step={@$step}&amp;queueID={@$queueID}&amp;action={@$action}&amp;packageID={@PACKAGE_ID}{@SID_ARG_2ND}">
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
	//]]>
</script>

{include file='setupWindowFooter'}