<section class="center" id="main">
<aside class="left user_menu" id="user">
	<?php echo $login; ?>
</aside>

<aside class="right" id="marketplace">
	<?php if($failed) { ?>
		<div style="padding:10px;margin:20px;font-size:18px;box-shadow:0px 0px 15px red;border-radius:5px;">Your transaction has failed to validate with the PayPal servers. We will have to manually grant you access to the product, please stand by and if nothing happens in a few hours, please contact us!</div>
	<?php } ?>

	<div style="padding:20px;font-size:18px;">We have received your payment and the product should be listed on your <a href="<?php echo base_url(); ?>account/downloads">downloads</a> page very soon.<br /><br />If you still didn't receive it after 5 minutes, do not hesistate to <a href="<?php echo base_url(); ?>support">contact us</a>!</div>
</aside>

<div class="clear"></div>