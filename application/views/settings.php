<section class="center" id="main">
<aside class="left user_menu" id="user">
	<?php echo $login; ?>
</aside>

<aside class="right" id="contact">
	<form onSubmit="Main.saveSettings();return false" style="padding-bottom:0px;">
		<label>Customer ID</label>
		<input type="text" value="<?php echo $user->getId() ?>" disabled="disabled"/>

		<label for="real_name">Name</label>
		<input type="text" name="real_name" id="real_name" placeholder="John Doe" value="<?php echo $user->getName() ?>"/>

		<label for="email">Email address</label>
		<input type="text" name="email" id="email" placeholder="me@domain.com" value="<?php echo $user->getEmail(); ?>"/>

		<label for="website">Website URL</label>
		<input type="text" name="website" id="website" placeholder="http://mywebsite.com" value="<?php echo $user->getWebsite(); ?>"/>

		<input type="submit" value="Save details" />

		<div style="padding:20px;" id="settings_result"></div>
	</form>

	<form onSubmit="Main.changePassword();return false" style="padding-top:0px;margin-top:0px;">
		<label for="old_password">Old password</label>
		<input type="password" name="old_password" id="old_password" placeholder="••••••••••••••" />

		<label for="new_password">New password</label>
		<input type="password" name="new_password" id="new_password" placeholder="••••••••••••••" />

		<input type="submit" value="Change password" />

		<div style="padding:20px;" id="password_result"></div>
	</form>
</aside>

<div class="clear"></div>