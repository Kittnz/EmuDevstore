<?php if(!$user->isOnline()){?>
	<h1>Customer Account Panel</h1>

	<form onSubmit="Main.login(this, '<?php echo $loginPage; ?>'); return false">
		<input type="text" id="username" placeholder="Enter email" class="field_icon" style="background-image:url(<?php echo base_url(); ?>static/images/icons/email_tiny.png)">
		<input type="password" id="password" placeholder="••••••••••••••" class="field_icon" style="background-image:url(<?php echo base_url(); ?>static/images/icons/lock_tiny.png)">
		<input type="submit" value="Sign in">
		<a href="<?php echo base_url(); ?>createaccount">Create an Account</a>
		<a href="<?php echo base_url(); ?>forgot">Forgot your details?</a>
	</form>
<?php } else { ?>
	<?php if($user->isAdmin()){?>
		<h1 style="text-align:left;font-weight:bold;">Administration</h1>
		<ul>
			<li>
				<a href="<?php echo base_url(); ?>admin">
					<img src="<?php echo base_url(); ?>static/images/icons/admin_menu.png"> Admin Panel
				</a>
			</li>
			<li>
				<a href="<?php echo base_url(); ?>admin">
					<img src="<?php echo base_url(); ?>static/images/icons/graph_menu.png"> Dashboard Info
				</a>
			</li>
		</ul>
	<?php } ?>
	
	<?php if($user->isMarketManager()){?>
		<h1 style="text-align:left;font-weight:bold;">Market Manager</h1>
		<ul>
			<li>
				<a href="<?php echo base_url(); ?>manager">
					<img src="<?php echo base_url(); ?>static/images/icons/shop.png"> Pending (<?php $row = $this->db->query("SELECT COUNT(*) `pending` FROM products WHERE validated=0")->result_array(); echo $row[0]['pending']; ?>)
				</a>
			</li>
		</ul>
	<?php } ?>
	
	<?php if($user->isPublisher()){?>
		<h1 style="text-align:left;font-weight:bold;">Publisher panel</h1>
		<ul>
			<li>
				<a href="<?php echo base_url(); ?>publisher/products">
					<img src="<?php echo base_url(); ?>static/images/icons/palette_menu.png"> My products
				</a>
			</li>

			<li>
				<a href="<?php echo base_url(); ?>publisher/submit">
					<img src="<?php echo base_url(); ?>static/images/icons/submit_menu.png"> Submit
				</a>
			</li>
		</ul>
	<?php } ?>
	
	<h1 style="text-align:left;font-weight:bold;">Customer panel</h1>
	<ul>

		<li>
			<a href="<?php echo base_url(); ?>account/downloads">
				<img src="<?php echo base_url(); ?>static/images/icons/downloads_menu.png"> Downloads
			</a>
		</li>


		<li>
			<a href="<?php echo base_url(); ?>account/settings">
				<img src="<?php echo base_url(); ?>static/images/icons/settings_menu.png"> Account settings
			</a>
		</li>


		<li>
			<a href="<?php echo base_url(); ?>logout">
				<img src="<?php echo base_url(); ?>static/images/icons/arrow_menu.png"> Log out
			</a>
		</li>
	</ul>
	
<?php } ?>