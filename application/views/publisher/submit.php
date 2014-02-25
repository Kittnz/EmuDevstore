<section class="center" id="main">
<aside class="left user_menu" id="user">
	<?php echo $login; ?>
</aside>

<aside class="right" id="contact">
	<div style="padding:20px;font-family:RobotoLight;">
		
		<div id="upload-errors" style="padding:10px;margin:20px;font-size:18px;box-shadow:0px 0px 15px red;border-radius:5px;display:none">
		</div>

		<form id="upload-form" method="POST" action="<?php echo base_url(); ?>publisher/submit/upload" enctype="multipart/form-data">
			<!-- Max 50Mb -->
			<input type="hidden" name="MAX_FILE_SIZE" value="52428800" />
			
			<!-- inputs -->
			<label for="product_name">Product name</label>
			<input type="text" name="product_name" placeholder="Keep it short but descriptive" id="product_name" value="<?php echo $this->input->post('product_name'); ?>" />

			<label for="paypal_email">PayPal email (required for paid products!)</label>
			<input type="text" name="paypal_email" placeholder="you@domain.com" id="paypal_email" value="<?php echo $this->input->post('paypal_email'); ?>" />
	        
			<label for="product_description">Description (HTML allowed!)</label>
			<textarea name="product_description" id="product_description" rows="10"><?php echo $this->input->post('product_description'); ?></textarea>
	        
			<label for="product_price">Price (keep blank for free downloads)</label>
            <div class="number-input">
			    <input type="text" placeholder="0.00" name="product_price" id="product_price" value="<?php echo $this->input->post('product_price'); ?>"/>
                <span class="unit-symbol">USD</span>
            </div>

			<label><input type="checkbox" name="is_unique" value="1"> Unique Sale - Sell your product just <font color="red">ONE</font> time for an unique price</label><br />
			
			<?php echo form_dropdown('product_category_id', $categories); ?>

			<label for="product_file">File archive (.zip)</label>
			<input type="file" name="product_file" id="product_file" />

			<label for="product_screenshot">Screenshot (.jpg) <span style="color:blue;">628 &#215; anything</span></label>
			<input type="file" name="product_screenshot" id="product_screenshot" />

			<label for="product_thumbnail">Thumbail (in .jpg) <span style="color:blue;">94 &#215; 94</span></label>
			<input type="file" name="product_thumbnail" id="product_thumbnail" />

			<label><input type="checkbox" name="accept" value="1"> I have read and agree to the <a target="_blank" href="<?php echo base_url(); ?>info/guide">Guidelines</a></label> <br />

        
            <div class="progressbar" style="display:none">
                <div class="bar" style="width:0px">Uploading...</div>
            </div>
            <br>

			<input type="submit" value="Submit product" /><br /><br />
			<span style="color:red;">( ! )</span> All submissions require approval before being publically exposed on the marketplace!
		</form>
	</div>
</aside>

<div class="clear"></div>

<script src="<?php echo base_url(); ?>static/js/jquery.autoNumeric.js"></script>
<script src="<?php echo base_url(); ?>static/js/jquery.form.js"></script>
<script>
$('document').ready(function() {

    // init price input
    $('#product_price').autoNumeric('init', {
        aSep: ''
    });
    
    // create ajax upload form
    var progress = $('#upload-form .progressbar');
    var bar = progress.children('.bar');
    
    $('#upload-form').ajaxForm({
        beforeSend: function() {
            // reset error messages
            $('#upload-errors').html('').hide();
            
            // temporarily disable submit button
            $('#upload-form input[type="submit"]').attr('disabled', 'disabled').val('Please wait...');
            
            // only display progessbar if files are selected
            if ($('#product_file').val() && $('#product_screenshot').val() && $('#product-thumbnail')) {
                progress.show();
            }
        },
        uploadProgress: function(event, position, total, percentComplete) {
            bar.width(percentComplete + '%').html('Uploading... ' + percentComplete + '%');
        },
        complete: function(xhr) {
            // restore submit button
            $('#upload-form input[type="submit"]').removeAttr('disabled').val('Submit product');
            
	    console.log(xhr.responseText);
            var response = $.parseJSON(xhr.responseText);
            if (response.status == 'error') {
                
                // hide progessbar again & reset values
                progress.slideUp(400, function() { bar.width('0px').text('Loading...'); });
                
                // scroll to top & show errors
                $('html, body').animate({ scrollTop: 0 }, 600, function() {
                    $('#upload-errors')
                        .html(response.errors)
                        .slideDown(200);
                    return false;
                });
            }
            else {
                window.location = response.redirect;
            }
        }
    });
    
});
</script>
