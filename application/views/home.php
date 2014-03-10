<br><br><div class="clear"></div>
<section class="full">
	<aside class="right" id="quotes">
		<?php 	
		
		echo '<span style="font-size:20px;font-weight:bold;float:left;margin-top:-15px;text-shadow: 1px 1px 3px purple;">Latest Submissions</span></br>';
		
		$query = $this->db->query("SELECT * FROM products WHERE validated!=0 ORDER BY id DESC LIMIT 4", array());
		
		$latestproducts = $query->result_array();
		
		if($query->num_rows() > 0) {
		foreach ($latestproducts as $product){
			echo '<a href="'.base_url().'Product/'.url_title($product['name']).'-'.$product['id'].'">
			<span style="color:purple;font-weight:bold;top:-5px;">'.$product['name'].'</span></a>
			</br><blockquote>
			<a href="'.base_url().'Product/'.url_title($product['name']).'-'.$product['id'].'"><img src="'.cdn_url().$product['thumbnail'].'"/></a>
			</span></blockquote>';
			}
		}
		else {
			echo '<span style="font-weight:bold;color:#FF0000;";>no submissions..</span>';
		}
		?>
	</aside>
	<div div="ad_banner"><a href="contact"><img src="static/images/banner_default.png"></a></div>
	<section id="gallery">
		<div id="gallery_wrapper">
			<img src="<?php echo base_url(); ?>static/images/slides/1.jpg">
			<img src="<?php echo base_url(); ?>static/images/slides/2.jpg">
			<img src="<?php echo base_url(); ?>static/images/slides/3.jpg">
		</div>
	</section>
</section>

<div class="divider"></div>

<div class="clear"></div>

<aside class="left" id="reasons">
	<h1>What can you find on <?php echo $this->config->item('site-title');?>?</h1>
	<ul>
		<li>
			<img src="<?php echo base_url(); ?>static/images/icons/cursor.png">
			<h2>Professional Databases</h2>
			<span>
				Databases from third parties are checked & approved by our developer team.</span>
		</li>
		<li>
			<img src="<?php echo base_url(); ?>static/images/icons/puzzle.png">
			<h2>Clean Coded Fun Scripts</h2>
			<span>
				The third party scripts are tested and working 100% compatible!
			</span>
		</li>
		<li>
			<img src="<?php echo base_url(); ?>static/images/icons/lock.png">
			<h2>Stable & Clean Cores</h2>
			<span>
				Uploaded third party cores that are compiled by us are running fast & stable!
			</span>
		</li>
		<li>
			<img src="<?php echo base_url(); ?>static/images/icons/palette.png">
			<h2>Tools for WoW-Websites</h2>
			<span>
				Themes/Modules for FusionCMS, Webwow, AzerCMS and many more!
			</span>
		</li>
	</ul>
</aside>

<aside class="right small_user" id="user">
	<?php echo $login; ?>
</aside>