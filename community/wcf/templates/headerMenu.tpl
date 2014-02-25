{*{if $this->getPageMenu()->getMenuItems()|count > 0}*}
	<div id="mainMenu" class="mainMenu">
		<div class="mainMenuInner">{assign var='menuItemCounter' value=0}{assign var='menuItemCount' value=$this->getPageMenu()->getMenuItems()|count}<ul>{foreach from=$this->getPageMenu()->getMenuItems() item=item}{assign var='menuItemCounter' value=$menuItemCounter+1}<li id="mainMenuItem{@$item.menuItemID}"{if $item.activeMenuItem || $menuItemCounter == 1 || $menuItemCounter == $menuItemCount} class="{if $menuItemCounter == 1}first{elseif $menuItemCounter == $menuItemCount}last{/if}{if $item.activeMenuItem}{if $menuItemCounter == 1 || $menuItemCounter == $menuItemCount}Active{else}active{/if}{/if}"{/if}><a href="{$item.menuItemLink}" title="{lang}{@$item.menuItem}{/lang}">{if $item.menuItemIconM}<img src="{$item.menuItemIconM}" alt="" /> {/if}<span>{lang}{@$item.menuItem}{/lang}</span></a></li>{/foreach}</ul>
		</div>
	</div>
{*{/if}*}