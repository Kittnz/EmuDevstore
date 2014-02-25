{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.messenger.jabber.title{/lang} - {lang}wcf.user.profile.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
<div class="border content messenger">
	<div class="container-1">
		<div class="messengerName">
			<p class="smallFont light">{lang}wcf.user.messenger.jabber.name{/lang}</p>
			<h1>{@$identifier}</h1>
		</div>
	</div>
</div>
</body>
</html>