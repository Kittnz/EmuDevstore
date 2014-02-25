{include file="documentHeader"}
<head>
	<title>{lang}wbb.thread.ipAddress.title{/lang} -  {if $thread->prefix}{lang}{$thread->prefix}{/lang} {/if}{$thread->topic} - {lang}{$board->title}{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	
	{include file="navigation" showThread=true}
	
	<div class="mainHeadline">
		<img src="{icon}ipAddressL.png{/icon}" alt="" />
		<div class="headlineContainer">
			<h2>{lang}wbb.thread.ipAddress.title{/lang}</h2>
		</div>
	</div>
	
	{if $userMessages|isset}{@$userMessages}{/if}
	
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wbb.thread.ipAddress.title{/lang}</legend>
				
				<ul>
					<li>{$ipAddress.ipAddress} ({$ipAddress.hostname})</li>
				</ul>
			</fieldset>
			
			{if $otherAddresses|count > 0}
				<fieldset>
					<legend>{lang}wbb.thread.ipAddress.otherAddresses{/lang}</legend>
					
					<ul>
						{foreach from=$otherAddresses item=otherAddress}
							<li>{$otherAddress.ipAddress} ({$otherAddress.hostname})</li>
						{/foreach}
					</ul>
				</fieldset>
			{/if}
			
			{if $otherUsers|count > 0}
				<fieldset>
					<legend>{lang}wbb.thread.ipAddress.otherUsers{/lang}</legend>
					
					<ul>
						{foreach from=$otherUsers item=user}
							<li>{$user}</li>
						{/foreach}
					</ul>
				</fieldset>
			{/if}
		</div>
	</div>

</div>

{include file='footer' sandbox=false}

</body>
</html>