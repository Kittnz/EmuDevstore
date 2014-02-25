<div style="margin-top:20px;">
{if $export|isset}
	{if $success}
		<div class="success">
			{lang}wcf.acp.db.export.success{/lang}
		</div>
	{/if}
{else}
	{if $success}
		<div class="success">
			{lang}wcf.acp.db.import.success{/lang}
		</div>
	{else}
		<div class="error">
			{lang}wcf.acp.db.import.message{/lang}
		</div>
	{/if}
{/if}
</div>