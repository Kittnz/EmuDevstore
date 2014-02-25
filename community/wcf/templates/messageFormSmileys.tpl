<div class="hidden" id="smilies">
	<fieldset class="noJavaScript">
		<legend class="noJavaScript">{lang}wcf.smiley.smilies{/lang}</legend>
		
		<div id="smileyContainer">
			<ul class="smileys" id="smileyCategory-0">{foreach from=$defaultSmileys item=smiley}<li><img onmouseover="this.style.cursor='pointer'" onclick="WysiwygInsert('smiley', '{$smiley->getURL()}', '{lang}{$smiley->smileyTitle|encodeJS}{/lang}', '{$smiley->smileyCode|encodeJS}');" src="{$smiley->getURL()}" alt="" title="{lang}{$smiley->smileyTitle}{/lang}" /></li>{/foreach}</ul>
		</div>
	</fieldset>
</div>

<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/SmileyCategorySwitcher.class.js"></script>
<script type="text/javascript">
	//<![CDATA[
	tabbedPane.addTab('smilies', false);
	
	// define smiley categories
	var smileyCategories = new Hash();
	smileyCategories.set(0, '{lang}wcf.smiley.category.default{/lang} ({#$defaultSmileys|count})');
	{foreach from=$smileyCategories item=smileyCategory}
		{if !$smileyCategory->disabled}smileyCategories.set({@$smileyCategory->smileyCategoryID}, '{lang}{$smileyCategory->title}{/lang} ({#$smileyCategory->smileys})');{/if}
	{/foreach}
	
	// init smiley category switcher
	var smileyCategorySwitcher = new SmileyCategorySwitcher(smileyCategories);
	{if $activeTab == 'smilies' || $activeTab == ''}
		document.observe("dom:loaded", function() {
			smileyCategorySwitcher.showSmileyCategories();
		});
	{/if}
	//]]>
</script>