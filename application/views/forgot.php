<section class="center" id="main">
<aside class="left user_menu" id="user">
	<?php echo $login; ?>
</aside>

<aside class="right" id="contact">
	<form onSubmit="Main.submitForgot();return false">

		<label for="email">Enter your Email address</label>
		<input type="text" name="email" id="email" placeholder="you@maildomain.com" style="width:50%;"/>&nbsp;&nbsp;&nbsp;

		<input type="submit" value="Send email" />

		<div style="padding:20px;" id="email_results"></div>
	</form>
</aside>

<div class="clear"></div>