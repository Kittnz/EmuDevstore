<!doctype html>
<html>
	<head>
		<title><?php echo $title; ?></title>

		<meta name="keywords" value="Devstore, Scripts, Cores, Databases, Website, World of Warcraft, Fusion CMS, Web WoW, Helper Tools, Private Server" >
		<meta name="description" value="Devstore, Scripts, Cores, Databases, Website, World of Warcraft, Fusion CMS, Web WoW, Helper Tools, Private Server">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="<?php echo base_url(); ?>static/css/main.css" type="text/css" >

		<link rel="shortcut icon" href="<?php echo base_url(); ?>static/images/favicon.png" >

		<script src="//html5shiv.googlecode.com/svn/trunk/html5.js" type="text/javascript"></script>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript" ></script>
		<script src="<?php echo base_url(); ?>static/js/require.js" type="text/javascript"></script>

		<!--[if lte IE 8]>
			<script type="text/javascript">
				var isIE = true;
			</script>
		<![endif]-->

		<script type="text/javascript">
			/* Google Analytics */
			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', 'UA-44121967-1']);
			_gaq.push(['_trackPageview']);

			(function() {
				var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			})();

			function getCookie(c_name)
			{
				var i, x, y, ARRcookies = document.cookie.split(";");

				for(i = 0; i < ARRcookies.length;i++)
				{
					x = ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
					y = ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
					x = x.replace(/^\s+|\s+$/g,"");
					
					if(x==c_name)
					{
						return unescape(y);
					}
				}
			}

			var Config = {
				URL: "<?php echo base_url(); ?>"
			};

			var scripts = [
				"<?php echo base_url(); ?>static/js/ui.js",
				"<?php echo base_url(); ?>static/js/main.js",
				"<?php echo base_url(); ?>static/js/jquery.placeholder.min.js",
				"<?php echo base_url(); ?>static/js/jquery.transit.min.js"
			];

			if(typeof JSON == "undefined")
			{
				scripts.push("<?php echo base_url(); ?>static/js/json2.js");
			}

			require(scripts, function()
			{
				$(document).ready(function()
				{
					UI.initialize();
				});
			});
		</script>
	</head>

	<body>
		<div id="popup_bg"></div>

		<!-- confirm box -->
		<div id="confirm" class="popup">
			<h1 class="popup_question" id="confirm_question"></h1>

			<div class="popup_links">
				<a href="javascript:void(0)" class="popup_button" id="confirm_button"></a>
				<a href="javascript:void(0)" class="popup_hide" id="confirm_hide" onClick="UI.hidePopup()">
					Cancel
				</a>
				<div style="clear:both;"></div>
			</div>
		</div>

		<!-- alert box -->
		<div id="alert" class="popup">
			<h1 class="popup_message" id="alert_message"></h1>

			<div class="popup_links">
				<a href="javascript:void(0)" class="popup_button" id="alert_button">Okay</a>
				<div style="clear:both;"></div>
			</div>
		</div>
		<div id="wrapper">
			<div id="fixer">
				<section id="top">
					
					<section class="center">
						<aside class="right">
							<ul>
								<li><a href="<?php echo base_url(); ?>home" <?php if($controller == "home"){ ?>class="active"<?php } ?>>Home</a></li>
								<?php foreach ($mainCategories as $category) : ?>
									<li>
										<a href="<?php echo base_url(); ?><?php echo $category['has_childs'] ? $this->category_model->getUrl($category) : $this->category_model->getProductsUrl($category); ?>">
											<?php 
											echo $category['short_title'] ? $category['short_title'] : $category['title']; ?>
										</a>
									</li>
								<?php endforeach; ?>
								
								<li><a href="<?php echo base_url(); ?>contact" <?php if($controller == "contact"){ ?>class="active"<?php } ?>>Contact</a></li>
								<li><a href="<?php echo base_url(); ?>info/guide" <?php if($this->uri->uri_string() == "info/guide"){ ?>class="active"<?php } ?>>Guide</a></li>
							</ul>
						</aside>
					
					</section>
				</section>

				<header>
					
					<section id="header_bg" <?php if(!$hasBigHeader) {?>class="header_small normal_header"<?php } ?>>
						<section class="center">
							<h1><?php echo $headline ?></h1>
							<aside class="right" <?php if(!$hasBigHeader) {?>style="display:none;"<?php } ?>>
								<h2><?php echo $this->config->item('site-slogan');?></h2>
							</aside>
							<div class="clear"></div>
						</section>
					</section>
				</header>

				<section class="center" id="main">
					<?php echo $content ?>
				</section>
			</div>
		</div>

		<div id="whole_footer">
			<div id="pre_footer"></div>

			<footer>
				<section class="center">
					</a>

					<div class="clear"></div>
				</section>
			</footer>
		</div>
	</body>
</html>