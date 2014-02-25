<section class="center" id="main">
<aside class="left user_menu" id="user">
	<?php echo $login; ?>
</aside>

<aside class="right" id="marketplace">
	<ul>

		<li>
			<h1><a>Total earnings since release: $ <?php echo $total; ?></a></h1>
			<img style="border:1px solid #ccc;" src="https://chart.googleapis.com/chart?chf=bg,s,FFFFFF&chxl=0:|2012-08-13|<?php echo date("Y-m-d"); ?>&chxp=0,12,87&chxr=1,0,<?php echo $graph_top+500; ?>&chxs=1,676767,11.5,0,lt,676767&chxt=x,y&chs=620x190&cht=lc&chco=095a9d&chds=0,<?php echo $graph_top+500; ?>&chd=t:0,<?php echo $graph; ?>&chdlp=l&chls=2&chma=5,5,5,5" />
			<div class="clear"></div>
		</li>

	</ul>
</aside>

<div class="clear"></div>