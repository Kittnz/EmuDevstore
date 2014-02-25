{include file='documentHeader'}
<head>
	<title>{lang}wcf.aboutmepage.sitetitle{/lang} - {PAGE_TITLE}</title>
	{include file='headInclude' sandbox=false}
</head>
<body{if $templateName|isset} id="tpl{$templateName|ucfirst}"{/if}>
{include file='header' sandbox=false}

<div id="main">
	<ul class="breadCrumbs">
		<li><a href="index.php?page=Index{@SID_ARG_2ND}"><img src="{icon}indexS.png{/icon}" alt="" /> <span>{lang}{PAGE_TITLE}{/lang}</span></a> &raquo;</li>
	</ul>

<div class="mainHeadline">
	<img src="{icon}{ABOUTMEPAGE_ICON}{/icon}" alt="" />
<div class="headlineContainer">
    <h2>{ABOUTMEPAGE_TITLE}</h2>
    <p> {ABOUTMEPAGE_DESCRIPTION}</p>
</div>
</div>

{if $userMessages|isset}{@$userMessages}{/if}


	<div class="border">
		<div class="layout-3">
			<div class="columnContainer">
				<div class="column second container-1">
					<div class="columnInner">
						{if ABOUTMEPAGE_ABOUTUSTITLE}	
						<h3 class="subHeadline">{ABOUTMEPAGE_ABOUTUSTITLE}</h3>
						<div class="contentBox">
						<p>{if ABOUTMEPAGE_ABOUTUSHTML != '0'}{@ABOUTMEPAGE_ABOUTUSCONTENT}{else}{@ABOUTMEPAGE_ABOUTUSCONTENT|htmlspecialchars|nl2br}{/if}</p>
					</div>
						{/if}
						
					 </div>
				</div>
			</div>
	</div>
 </div>
</div>

<p>{include file='footer' sandbox=false}</p>
</body>
</html>