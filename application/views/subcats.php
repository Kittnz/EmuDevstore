<section class="center" id="main">
<aside class="left user_menu" id="user">
	<?php echo $login; ?>
</aside>

<aside class="right" id="marketplace">

    <?php if ($back_url) : ?>
    <div id="go-back">
        <a href="<?php echo base_url().$back_url; ?>">← Go back</a>
    </div>
    <?php endif; ?>
	
	<ul>
		<?php foreach ($subcats as $category) : ?>
		<li>
			<a href="<?php echo base_url().$category['url']; ?>"><img src="<?php echo base_url(); ?>static/images/category/<?php echo $category['image']; ?>" class="thumbnail"></a>
			<h1>
                <a href="<?php echo base_url().$category['url']; ?>">
                    <?php echo $category['title']; ?>
                    <span class="products-count"><?php echo $category['num_products']; ?></span>
                </a>
            </h1>
			<span><?php echo $category['subtitle']; ?></span>
			<div class="clear"></div>
		</li>
		<?php endforeach; ?>
	</ul>
</aside>

<div class="clear"></div>