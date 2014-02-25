<section class="center" id="main">
<aside class="left user_menu" id="user">
	<?php echo $login; ?>
</aside>

<aside class="right" id="contact">
	<div style="padding:20px;font-family:RobotoLight;">
		<?php if(isset($error)) { echo $error; } else { echo "Something occured"; } ?>
	</div>
</aside>

<div class="clear"></div>