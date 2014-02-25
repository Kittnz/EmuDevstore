<script type="text/javascript">
	//<![CDATA[
	document.observe("dom:loaded", function() {
		var checkbox;
		Sortable.create('{$optionData.optionName}', {
			onChange: function(element) {
				if (Prototype.Browser.WebKit || Prototype.Browser.Gecko) {
					this.checkbox = element.select('input')[0];
				}
			}.bind(this),
			
			onUpdate: function(element) {
				if (Prototype.Browser.WebKit || Prototype.Browser.Gecko) {
					this.checkbox.addClassName('dropped');
				}
			}.bind(this)
		});
		
		if (Prototype.Browser.WebKit || Prototype.Browser.Gecko) {
			$$('#{$optionData.optionName} label').invoke('observe', 'click', function(evt) {
				if (this.checkbox && this.checkbox.hasClassName('dropped')) {
					this.checkbox.removeClassName('dropped');
					evt.stop();
				}
			}.bind(this));
			$('{$optionData.optionName}').observe('mousemove', function(evt) {
				if (this.checkbox && this.checkbox.hasClassName('dropped')) {
					this.checkbox.removeClassName('dropped');
				}
			}.bind(this));
		}
		$('{$optionData.optionName}').addClassName('dragable');
	});
	//]]>
</script>

<ul class="formOptionsLong" id="{$optionData.optionName}">
	{foreach from=$options item=optionTitle key=optionName}
		<li id="{$optionData.optionName}_{$optionName}">
			<label><input type="checkbox" name="values[{$optionData.optionName}][]" value="{$optionName}"
			{if $optionName|in_array:$selectedOptions}checked="checked" {/if}/>
			{lang}{$optionTitle}{/lang}</label>
		</li>
	{/foreach}
</ul>