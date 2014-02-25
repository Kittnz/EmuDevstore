{capture append='specialStyles'}{literal}<style type="text/css">
#phpInfo img {float: right; border: 0;}
#phpInfo pre {margin: 0; font-family: monospace;}
#phpInfo h2 {margin-bottom: 10px}
#phpInfo .e {text-align: right; width: 200px}
#phpInfo .v {overflow: hidden; max-width: 200px}
</style>{/literal}{/capture}
{include file='header'}

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/systemL.png" alt="" />
	<div class="headlineContainer">
		<h2>PHP Version {PHP_VERSION}</h2>
	</div>
</div>

<div id="phpInfo">
{@$phpInfo}
</div>

{include file='footer'}