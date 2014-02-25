<section class="center" id="main">
<aside class="left user_menu" id="user">
	<?php echo $login; ?>
</aside>

<aside class="right" id="marketplace">
	<?php if(isset($failed) && $failed) { ?>
		<div style="padding:10px;margin:20px;font-size:18px;box-shadow:0px 0px 15px red;border-radius:5px;">Something prevented your payment from being automatically processed. Please contact our support for further assistance.</div>
	<?php } ?>
    
    <?php if (isset($back_url)) : ?>
    <div id="go-back">
        <a href="<?php echo base_url().$back_url; ?>">← Go back</a>
    </div>
    <?php endif; ?>
    
	<ul>
		<?php
			if($products)
			{
				foreach($products as $product)
				{
		?>
		<li<?php if($product['is_unique']) echo ' class="product-unique"'; ?>>
        
            <aside>
    			<div class="price">
    				<?php if($user->hasProduct($product['id']) || $product['price'] == 0) : ?>
    					<a target="_blank" href="<?php echo base_url(); ?>account/download/<?php echo $product['id']; ?>">Download</a>
    				<?php else : ?>
                        <?php if ($product['is_unique'] && $product['downloads'] != 0 && !$user->hasProduct($product['id'])) : ?>
                            <a class="sold-out">$<?php echo $product['price']; ?> Sold out</a>
                        <?php else : ?>
                           <a href="<?php echo base_url(); ?>buy/<?php echo $product['id']; ?>">Buy $<?php echo $product['price']; ?></a>
                        <?php endif; ?>
    				<?php endif;  ?>

                    <span class="sales-count">
    				<?php if($product['downloads'] != -1 && ! $product['is_unique']) : ?>
                            <?php echo $product['downloads']; ?>
                    
                        <?php if($product['price']) : ?>
                            sales
                        <?php else : ?>
                            downloads
                        <?php endif; ?>
                    <?php elseif ($product['is_unique']) : ?>
                        <span class="bubble-unique">Unique</span>
                    <?php endif;?>
                    </span>
    			</div>
            </aside>
			<a href="<?php echo base_url().$this->product_model->getUrl($product); ?>"><img class="thumbnail" src="<?php echo cdn_url().$product['thumbnail']; ?>"></a>
			<h1>
                <a href="<?php echo base_url().$this->product_model->getUrl($product); ?>"><?php echo $product['name']; ?></a>
            </h1>
            
            <?php if (isset($product['real_name'])) : ?>
			    <span class="userproducts-link">
                    <a href="<?php echo base_url(); ?>userproducts/<?php echo $product['author_id']; ?>" title="Show all products of this user"><?php echo $product['real_name']; ?></a>
                </span>
            <?php endif; ?>
            
			<div class="clear"></div>
		</li>
		<?php } } else { ?>
			<div style="padding:20px;font-size:18px;">No products found.</div>
		<?php } ?>
	</ul>
</aside>

<div class="clear"></div>