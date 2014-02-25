<section class="center" id="main">
<aside class="left user_menu" id="user">
	<?php echo $login; ?>
</aside>

<aside class="right" id="contact">
	<div style="padding:20px;font-family:RobotoLight;">
		
		<?php if(isset($errors)) { ?>
			<div style="padding:10px;margin:20px;font-size:18px;box-shadow:0px 0px 15px red;border-radius:5px;">
				<?php echo $errors; ?>
			</div>
		<?php } ?>

		<form method="POST" action="<?php echo base_url(); ?>publisher/encrypt/process/<?php if(isset($process_id)) echo $process_id; ?>" enctype="multipart/form-data">			
			<!-- inputs -->
			<label for="product_name">Password</label>
			<input type="password" name="password" placeholder="Our system adds a password to your archive. Don't forget it, you need it later again!" id="password" />

			<label for="accept"><input type="checkbox" name="accept" id="accept" value="1"> I confirm that I'll remember this password and enter it again after this product has been purchased and I got the money.</label> <br />

			<input type="submit" value="Send" /><br /><br />
		</form>
	</div>
</aside>

<div class="clear"></div>