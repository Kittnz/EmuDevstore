<section class="center" id="main">
<aside class="left user_menu" id="user">
	<?php echo $login; ?>
</aside>
<aside class="right" id="contact">
	<form onSubmit="Main.Contact.submitForm();return false">
		<label for="real_name">Name</label>
		<input type="text" name="real_name" id="real_name" placeholder="John Doe" <?php if($customer != false){ ?>value="<?php echo $customer['real_name']; ?>"<?php } ?>/>

		<label for="email">Email address</label>
		<input type="text" name="email" id="email" placeholder="me@domain.com" <?php if($customer != false){ ?>value="<?php echo $customer['email']; ?>"<?php } ?>/>

		<label for="subject">Subject</label>
		<input type="text" name="subject" id="subject" placeholder="Summarize your problem or topic in a few words"/>

		<label for="message">Message</label>
		<textarea name="message" id="message" rows="10" placeholder="Tell us as much as possible about your problem or topic"></textarea>
	
		<input type="submit" value="Send message" />

		<div style="padding:20px;" id="email_results"></div>
	</form>
</aside>

<div class="clear"></div>