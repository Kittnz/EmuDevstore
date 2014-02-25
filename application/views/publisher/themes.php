<section class="center" id="main">
<aside class="left user_menu" id="user">
	<?php echo $login; ?>
</aside>

<aside class="right" id="marketplace">
	<ul>
		<?php
			if($themes)
			{
				foreach($themes as $theme)
				{
		?>
		<li>
			<div class="price">
				<a target="_blank" href="<?php echo base_url(); ?>account/download/<?php echo $theme['id']; ?>">Download</a>

				<?php if($theme['downloads'] != -1){echo $theme['downloads']; ?> <?php if($theme['price']){?>sales<?php } else { ?>downloads<?php } } ?>
			</div>
			<img src="<?php echo cdn_url().$theme['thumbnail']; ?>">
			<h1><a href="<?php echo base_url(); ?>view/<?php echo $theme['id']; ?>"><?php echo $theme['name']; ?></a></h1>
			<h2><?php echo "Validated: ".($theme['validated']? "True":"False" ) ?></h2>
			<h2><a href="<?php echo base_url(); ?>publisher/themes/delete/<?php echo $theme['id']; ?>">Delete</a></h2>
			<span><a href="<?php echo $theme['website']; ?>" target="_blank"><?php echo $theme['real_name']; ?></a></span>
			<div class="clear"></div>
		</li>
		<?php } } else { ?>
			<div style="padding:20px;font-size:18px;">No themes found</div>
		<?php } ?>
	</ul>
</aside>

<div class="clear"></div>