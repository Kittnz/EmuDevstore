<section class="center" id="main">
<aside class="left user_menu" id="user">
	<?php echo $login; ?>
</aside>
<?php if(!$user->isOnline()) { ?>
	<aside class="right" id="contact">
	<div style="padding:20px;font-size:18px;">You're not logged in!</div>
</aside>
<?php } else { ?>
<aside class="right" id="marketplace">
	<?php if($product['name'] == 'Product not found') { ?>
		<div style="padding:20px;font-size:18px;">No product found</div>
	<?php }	else { ?>
		<ul>
			<li class="no-animation">
				<div class="price">
					<?php if($user->hasProduct($product['id']) || $product['price'] == 0){?>
						<a target="_blank" href="<?php echo base_url(); ?>account/download/<?php echo $product['id']; ?>">Download</a>
					<?php } else { ?>
						<a>$<?php echo $product['price']; ?></a>
					<?php } ?>

					<?php if($product['downloads'] != -1){echo $product['downloads']; ?> <?php if($product['price']){?>sales<?php } else { ?>downloads<?php } } ?>
				</div>
				<img src="<?php echo cdn_url().$product['thumbnail']; ?>" width="94" height="94">
				<h1><?php echo $product['name']; ?></h1>
                
				<span><a href="<?php echo base_url(); ?>userproducts/<?php echo $product['author_id']; ?>"><?php echo $product['real_name']; ?></a></span>
				<div class="clear"></div>
			</li>
			
			<li style="opacity:1;">
				<div class="price">
					<form style="float:right;padding-right:0px;" action="https://www<?php if($paypal['sandbox']) { ?>.sandbox<?php } ?>.paypal.com/cgi-bin/webscr" method="post">
							<input type="hidden" name="cmd" value="_xclick" />
							<input type="hidden" name="business" value="<?php if($product['paypal_email']) { echo $product['paypal_email']; } else { echo $paypal['receiver']; } ?>" />
							<input type="hidden" name="item_name" value="<?php echo $product['name']; ?>" />
							<input type="hidden" name="quantity" value="1" />
							<input type="hidden" name="currency_code" value="USD" />
							<input type="hidden" name="notify_url" value="<?php echo $paypal['postback']; ?>" />
							<input type="hidden" name="return" value="<?php echo $paypal['return']; ?>" />
							<input type="hidden" name="custom" value="<?php echo $user->getId(); ?>-<?php echo $product['id']; ?>" />
							<input type="hidden" name="amount" value="<?php echo $product['price']; ?>" />
							<input type="image" src="<?php echo base_url(); ?>static/images/paypal.png" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
					</form>
				</div>
				<h1><a>Buy now with PayPal</a></h1>
				<span><a>Instant digital delivery</a></span>
				<div class="clear"></div>
			</li>
			
		</ul>
	<?php } ?>
</aside>
<?php } ?>

<div class="clear"></div>