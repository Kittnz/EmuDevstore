<section class="center" id="main">
<aside class="left user_menu" id="user">
	<?php echo $login; ?>
</aside>
<?php if(!$user->isOnline()) { ?>
	<aside class="right" id="contact">
	<form onSubmit="Main.createUser();return false">
		<label for="register_real_name">Account Name</label>
		<input type="text" name="register_real_name" id="register_real_name" placeholder="Choose your Account Name" />

		<label for="register_email">Email address</label>
		<input type="text" name="register_email" id="register_email" placeholder="you@maildomain.com" />

		<label for="register_password">Password</label>
		<input type="password" name="register_password" id="register_password" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;"/>

		<input type="submit" value="Create account" />

		<div style="padding:20px;" id="register_result"></div>
	</form>
</aside>
<?php } else { ?>
<div style="padding:20px;font-size:18px;">You have already an account!</div>
<?php } ?>