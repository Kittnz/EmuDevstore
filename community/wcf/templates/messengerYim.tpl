{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.messenger.yim.title{/lang} - {lang}wcf.user.profile.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
<div class="border content messenger">
	<div class="container-1">
		<div class="messengerName">
			<p class="smallFont light">{lang}wcf.user.messenger.yim.name{/lang}</p>
			<h1>{$identifier} <img src="http://opi.yahoo.com/online?u={$identifier}" alt="" /></h1>
		</div>
		<ul class="messengerOptions">
			<li><a href="http://profiles.yahoo.com/{$identifier}">{lang}wcf.user.messenger.viewProfile{/lang}</a></li>
		</ul>
		<p class="smallFont light messengerInfo">{lang}wcf.user.messenger.yim.description{/lang}</p>
	</div>
</div>
</body>
</html>