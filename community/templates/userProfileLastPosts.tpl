<div class="contentBox">
	<h3 class="subHeadline"><a href="index.php?form=Search&amp;types[]=post&amp;userID={@$user->userID}{@SID_ARG_2ND}">{lang}wcf.user.profile.lastPosts{/lang}</a> <span>({#$user->posts})</span></h3>
	
	<ul class="dataList">
		{foreach from=$posts item=post}
			<li class="{cycle values='container-1,container-2'}">
				<div class="containerIcon">
					<img src="{icon}postM.png{/icon}" alt="" />
				</div>
				<div class="containerContent">
					<h4>{if $post->prefix}<span class="prefix">{lang}{$post->prefix}{/lang}</span> {/if}<a href="index.php?page=Thread&amp;postID={@$post->postID}{@SID_ARG_2ND}#post{@$post->postID}">{$post->subject}</a></h4>
					<p class="firstPost smallFont light">{@$post->time|time}</p>
				</div>
			</li>
		{/foreach}
	</ul>
	
	<div class="buttonBar">
		<div class="smallButtons">
			<ul>
				<li class="extraButton"><a href="#top" title="{lang}wcf.global.scrollUp{/lang}"><img src="{icon}upS.png{/icon}" alt="{lang}wcf.global.scrollUp{/lang}" /> <span class="hidden">{lang}wcf.global.scrollUp{/lang}</span></a></li>
				<li><a href="index.php?form=Search&amp;types[]=post&amp;userID={@$user->userID}{@SID_ARG_2ND}" title="{lang}wcf.user.profile.allPosts{/lang}"><img src="{icon}messageS.png{/icon}" alt="" /> <span>{lang}wcf.user.profile.allPosts{/lang}</span></a></li>
				<li><a href="index.php?form=Search&amp;types[]=post&amp;userID={@$user->userID}&amp;findUserThreads=1{@SID_ARG_2ND}" title="{lang}wcf.user.profile.allThreads{/lang}"><img src="{icon}threadS.png{/icon}" alt="" /> <span>{lang}wcf.user.profile.allThreads{/lang}</span></a></li>
			</ul>
		</div>
	</div>
</div>