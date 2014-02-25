{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.messenger.aim.title{/lang} - {lang}wcf.user.profile.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
<div class="border content messenger">
	<div class="container-1">
		<div class="messengerName">
			<p class="smallFont light">{lang}wcf.user.messenger.aim.name{/lang}</p>
			<h1>{$identifier} <img src="http://osi.lishmirror.com:81/aim/{$identifier}" alt="" /></h1>
		</div>
		<ul class="messengerOptions">
			<li><a href="aim:goim?screenname={$identifier}">{lang}wcf.user.messenger.sendMessage{/lang}</a></li>
			<li><a href="aim:addbuddy?screenname={$identifier}">{lang}wcf.user.messenger.addToContactList{/lang}</a></li>
		</ul>
		<p class="smallFont light messengerInfo">{lang}wcf.user.messenger.aim.description{/lang}</p>
	</div>
</div>
</body>
</html>