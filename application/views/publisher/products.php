<section class="center" id="main">
<aside class="left user_menu" id="user">
	<?php echo $login; ?>
</aside>

<aside class="right" id="marketplace">
	<ul>
		<?php
			if($products)
			{
				foreach($products as $product)
				{
		?>
		<li>
			<div class="price">
				<a>
					<?php
					if($product['price']){
						echo 'earnings: $ ' . $product['downloads'] * $product['price'];
					}
					else
					{
						echo $product['downloads'] . ' downloads';
					}
					?>
				</a>

				<?php if($product['validated'] == 1) { ?><a href="<?php echo base_url(); ?>publisher/edit/<?php echo $product['id']; ?>" style="font-size:14px;">Modify product</a><?php } ?>
			</div>
			<img src="<?php echo cdn_url().$product['thumbnail']; ?>" width="94" height="94">
			<h1><a href="<?php echo base_url().$this->product_model->getUrl($product); ?>"><?php echo character_limiter($product['name'], 10); ?></a></h1>
			<h2><?php if($product['validated'] == 1) { echo '<span style="color:green;">Approved</span>'; } elseif($product['validated'] == 2) { echo '<span style="color:red;">Denied</span>'; } else { echo '<span style="color:orange;">Pending approval</span>'; } ?></h2>
			<div class="clear"></div>
		</li>
		<?php } } else { ?>
			<div style="padding:20px;font-size:18px;">You haven't submited any products. <a href="<?php echo base_url(); ?>publisher/submit">Click here to submit a product.</a></div>
		<?php } ?>
	</ul>
</aside>

<div class="clear"></div>