<section class="center" id="main">
<aside class="left user_menu" id="user">
	<?php echo $login; ?>
</aside>

<aside class="right" id="marketplace">
	<ul>
		<?php
			if($pendings)
			{
				foreach($pendings as $pending)
				{
		?>
		<li style="background-image:none;">
			<div class="price">
				<a target="_blank" href="<?php echo base_url(); ?>account/download/<?php echo $pending['id']; ?>">Download</a>
			</div>
			<img src="<?php echo cdn_url().$pending['thumbnail']; ?>" width="94" height="94">
			<h1><a target="_blank" href="<?php echo base_url().$this->product_model->getUrl($pending); ?>"><?php echo $pending['name']; ?></a></h1>
			Submitted by: <a href="<?php echo base_url(); ?>userproducts/<?php echo $pending['customer_id']; ?>"><?php echo $pending['real_name']; ?></a>
			<h2><a target="_blank" href="<?php echo base_url(); ?>admin/approve/<?php echo $pending['id']; ?>">Approve</a> / <a target="_blank" href="<?php echo base_url(); ?>admin/deny/<?php echo $pending['id']; ?>">Deny</a></h2>
			<div class="docs_text" style="margin:30px 0px">
				<a href="<?php echo cdn_url(); ?><?php echo $pending['screenshot']?>" target="_blank">Screenshot &rarr;</a>
				<?php highlight_string($pending['description']); ?>
			</div>
		</li>
		<?php } } else { ?>
			<div style="padding:20px;font-size:18px;">No pendings found</div>
		<?php } ?>
	</ul>
</aside>

<div class="clear"></div>