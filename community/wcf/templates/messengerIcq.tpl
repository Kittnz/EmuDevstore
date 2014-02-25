{include file="documentHeader"}
<head>
	<title>{lang}wcf.user.messenger.icq.title{/lang} - {lang}wcf.user.profile.title{/lang} - {lang}{PAGE_TITLE}{/lang}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
<div class="border content messenger">
	<div class="container-1">
		<div class="messengerName">
			<p class="smallFont light">{lang}wcf.user.messenger.icq.name{/lang}</p>
			<h1>{@$identifier} <img src="http://status.icq.com/online.gif?icq={@$identifier}&amp;img=5" alt="" /></h1>
		</div>
		<ul class="messengerOptions">
			<li><a href="http://people.icq.com/people/cmd.php?uin={@$identifier}&amp;action=message">{lang}wcf.user.messenger.sendMessage{/lang}</a></li>
			<li><a href="http://people.icq.com/people/cmd.php?uin={@$identifier}&amp;action=add">{lang}wcf.user.messenger.addToContactList{/lang}</a></li>
			<li><a href="http://people.icq.com/people/about_me.php?uin={@$identifier}">{lang}wcf.user.messenger.viewProfile{/lang}</a></li>
		</ul>
		<p class="smallFont light messengerInfo">{lang}wcf.user.messenger.icq.description{/lang}</p>
	</div>
</div>
</body>
</html>