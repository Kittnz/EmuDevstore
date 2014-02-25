<ul class="formOptionsLong">
	{foreach from=$markings item=marking}
		<li><label><input type="radio" name="userOnlineGroupID" value="{@$marking.groupID}" {if $marking.groupID == $userOnlineGroupID}checked="checked" {/if}/> {@$marking.userOnlineMarking} <span class="smallFont light">({lang}{$marking.groupName}{/lang})</span></label></li>
	{/foreach}
</ul>