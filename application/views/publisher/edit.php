<section class="center" id="main">
<aside class="left user_menu" id="user">
	<?php echo $login; ?>
</aside>

<aside class="right" id="contact">
	<div style="padding:20px;font-family:RobotoLight;">
		<form>
			<label>Delete product</label>
			<input type="submit" value="Delete product (warning, can't be undone!)" onClick="UI.confirm('Are you sure you want to delete this product?', 'Delete', function(){window.location='<?php echo base_url();?>publisher/products/delete/<?php echo $product['id']; ?>'});return false;">
		</form>
		<form method="post" action="<?php echo base_url(); ?>publisher/products/save/<?php echo $product['id']; ?>"  enctype="multipart/form-data">
			<input type="hidden" name="MAX_FILE_SIZE" value="52428800" />
			
			<label for="product_name">Product name</label>
			<input type="text" name="product_name" id="product_name" value="<?php echo $product['name']; ?>"/>
		
			<label for="paypal_email">PayPal email (required for paid products!)</label>
			<input type="text" name="paypal_email" id="paypal_email" value="<?php echo $product['paypal_email']; ?>"/>

			<label for="product_description">Description (HTML allowed!)</label>
			<textarea name="product_description" id="product_description" rows="10"><?php echo htmlspecialchars($product['description']); ?></textarea>
		
			<label for="product_price">Price (keep blank for free downloads)</label>
            <div class="number-input">
			    <input type="text" placeholder="0.00" name="product_price" id="product_price" value="<?php echo $product['price']; ?>"/>
                <span class="unit-symbol">USD</span>
            </div>
			
			<label>
				<input type="checkbox" name="update_files" id="update_files" value="1">
				Update product files
			</label>
			
			<div id="files-form">
				<br />
				<label for="product_file">File archive (.zip)</label>
				<input type="file" name="product_file" id="product_file" />

				<label for="product_screenshot">Screenshot (.jpg) <span style="color:blue;">628 &#215; anything</span></label>
				<input type="file" name="product_screenshot" id="product_screenshot" />

				<label for="product_thumbnail">Thumbail (in .jpg) <span style="color:blue;">94 &#215; 94</span></label>
				<input type="file" name="product_thumbnail" id="product_thumbnail" />
			</div>
			<br />

			<input type="submit" value="Save product" />
		</form>
	</div>
</aside>

<div class="clear"></div>

<script src="<?php echo base_url(); ?>static/js/jquery.autoNumeric.js"></script>
<script>
$(document).ready(function() {
    $('#product_price').autoNumeric('init', {
        aSep: ''
    });
	
	if ( ! $('#update_files').attr('checked'))
		$('#files-form').hide();
    
	$('#update_files').click(function() {
		$('#files-form').slideToggle();
	})
});
</script>