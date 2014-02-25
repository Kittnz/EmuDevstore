<section class="center" id="main">
<aside class="left user_menu" id="user">
	<?php echo $login; ?>
</aside>

<script type="text/javascript">
	$(document).ready(function()
	{
		function getMainNaow()
		{
			if(typeof Main != "undefined")
			{
				Main.liveCounter();
			}
			else
			{
				setTimeout(getMainNaow, 50);
			}
		}

		getMainNaow();
	});
</script>

<aside class="right" id="marketplace">
	<div style="font-size:48px;font-family:RobotoLight;color:#2779B0;padding:30px;"><div style="float:left;margin-right:10px;">$</div> <div id="live_counter" style="float:left;">Loading...</div></div>
</aside>

<div class="clear"></div>