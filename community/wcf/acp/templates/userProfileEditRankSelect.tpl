<ul class="formOptionsLong">
	{foreach from=$ranks item=rank}
		<li><label><input type="radio" name="rankID" value="{@$rank->rankID}" {if $rankID == $rank->rankID}checked="checked" {/if}/> {lang}{$rank->rankTitle}{/lang} {@$rank->getImage()}</label></li>
	{/foreach}
</ul>