<section class="center" id="main">
<aside class="left user_menu" id="user">
	<?php echo $login; ?>
</aside>

<aside class="right" id="marketplace">
	<ul>
		<?php
			if($modules)
			{
				foreach($modules as $module)
				{
		?>
		<li>
			<div class="price">
				<a target="_blank" href="<?php echo base_url(); ?>account/download/<?php echo $module['id']; ?>">Download</a>

				<?php if($module['downloads'] != -1){echo $module['downloads']; ?> <?php if($module['price']){?>sales<?php } else { ?>downloads<?php } } ?>
			</div>
			<img src="<?php echo cdn_url().$module['thumbnail']; ?>">
			<h1><a href="<?php echo base_url(); ?>view/<?php echo $module['id']; ?>"><?php echo $module['name']; ?></a></h1>
			<h2><?php echo "Validated: ".($module['validated']? "True":"False" ) ?></h2>
			<span><a href="<?php echo $module['website']; ?>" target="_blank"><?php echo $module['real_name']; ?></a></span>
			<div class="clear"></div>
		</li>
		<?php } } else { ?>
			<div style="padding:20px;font-size:18px;">No modules found</div>
		<?php } ?>
	</ul>
</aside>

<div class="clear"></div>