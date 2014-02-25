<ul class="tagCloud">
	{foreach from=$tags item=tag}
		<li><a href="index.php?page=TaggedObjects&amp;tagID={@$tag->getID()}{@SID_ARG_2ND}" style="font-size: {@$tag->getSize()}%;">{$tag->getName()}</a></li>
	{/foreach}
</ul>