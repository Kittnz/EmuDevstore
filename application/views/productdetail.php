<section class="center" id="main">
<aside class="left user_menu" id="user">
	<?php echo $login; ?>
</aside>

<aside class="right" id="marketplace">
	<?php if($product['name'] == 'Product not found') { ?>
		<div style="padding:20px;font-size:18px;">No product found</div>
	<?php }	else { ?>
        <div id="go-back">
            <a href="<?php echo base_url().$back_url; ?>">← Go back</a>
        </div>
		<ul>
			<li class="no-animation<?php if($product['is_unique']) echo ' product-unique'; ?>">
				<div class="price">
        				<?php if($user->hasProduct($product['id']) || $product['price'] == 0) : ?>
        					<a target="_blank" href="<?php echo base_url(); ?>account/download/<?php echo $product['id']; ?>">Download</a>
        				<?php else : ?>
                            <?php if ( ! $product['sold_out']) : ?>
        				        <a href="<?php echo base_url(); ?>buy/<?php echo $product['id']; ?>">Buy $<?php echo $product['price']; ?></a>
                            <?php else : ?>
                                <a class="sold-out">$<?php echo $product['price']; ?> Sold out</a>
                            <?php endif; ?>
        				<?php endif; ?>

                        <span class="sales-count">
            				<?php if ($product['downloads'] != -1 && ! $product['is_unique']) : ?>
                                <?php echo $product['downloads']; ?> 
                            
                                <?php if ($product['price']) : ?>
                                    sales
                                <?php else : ?>
                                    downloads
                                <?php endif; ?>
                            <?php elseif ($product['is_unique']) : ?>
                                <span class="bubble-unique">Unique</span>
                            <?php endif; ?>

                        </span>
				</div>
				<img src="<?php echo cdn_url().$product['thumbnail']; ?>" class="thumbnail">
            
				<h1><?php echo $product['name']; ?></h1>

				<span><a href="<?php echo base_url(); ?>userproducts/<?php echo $product['author_id']; ?>"><?php echo $product['real_name']; ?></a></span>
				<div class="clear"></div>
			</li>
		</ul>

		<div class="divider_small"></div>
		<div style="padding-left:20px;padding-right:20px;padding-bottom:20px;font-family:RobotoLight;">
            <?php echo $product['description']; ?>
            
            <br /><br />
            <?php if ($product['is_unique']) : ?>
            <h2>Unique Product Information</h2>
            <p>
                This is an unique product. <br>It is sold only <u>one time</u> and you'll get an exclusive copy that nobody else has.
            </p>
            <?php endif; ?>
        </div>
		<?php if($product['screenshot']){?>
		<div style="padding-left:20px;"><img src="<?php echo cdn_url()?><?php echo $product['screenshot']?>" width="628" /></div>
		<?php } ?>
	<?php } ?>
</aside>

<div class="clear"></div>